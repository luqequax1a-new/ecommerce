<?php

namespace App\Services;

use App\Models\MailConfiguration;
use App\Models\MailTemplate;
use App\Models\MailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MailService
{
    /**
     * Send email using template
     */
    public function sendTemplatedEmail(
        string $templateName,
        string $toEmail,
        array $variables = [],
        string $toName = null,
        MailConfiguration $config = null
    ): bool {
        try {
            // Get template
            $template = MailTemplate::getByName($templateName);
            if (!$template) {
                throw new \Exception("Template '{$templateName}' not found");
            }

            // Get mail configuration
            if (!$config) {
                $config = MailConfiguration::getAvailable();
                if (!$config) {
                    throw new \Exception('No available mail configuration');
                }
            }

            // Check if we can send mail (rate limiting)
            if (!$config->canSendMail()) {
                throw new \Exception('Mail configuration has reached sending limits');
            }

            // Process template
            $processedTemplate = $template->processTemplate($variables);

            // Create mail log entry
            $mailLog = MailLog::create([
                'mail_configuration_id' => $config->id,
                'to_email' => $toEmail,
                'to_name' => $toName,
                'subject' => $processedTemplate['subject'],
                'template_name' => $templateName,
                'status' => 'pending',
                'metadata' => [
                    'variables' => $variables,
                    'sent_via' => $config->name
                ]
            ]);

            // Configure Laravel Mail with our settings
            $this->configureMailer($config);

            // Send email
            Mail::html(
                $processedTemplate['html_content'],
                function ($message) use ($processedTemplate, $toEmail, $toName, $config) {
                    $message->to($toEmail, $toName)
                           ->subject($processedTemplate['subject'])
                           ->from($config->from_address, $config->from_name);
                    
                    if ($config->reply_to_address) {
                        $message->replyTo($config->reply_to_address, $config->reply_to_name);
                    }
                }
            );

            // Mark as sent and update counters
            $mailLog->markAsSent();
            $config->incrementSentCounters();

            return true;

        } catch (\Exception $e) {
            // Log error
            Log::error('Failed to send email', [
                'template' => $templateName,
                'to_email' => $toEmail,
                'error' => $e->getMessage()
            ]);

            // Mark mail log as failed if it exists
            if (isset($mailLog)) {
                $mailLog->markAsFailed($e->getMessage());
            }

            return false;
        }
    }

    /**
     * Send bulk emails (with rate limiting for shared hosting)
     */
    public function sendBulkEmails(
        string $templateName,
        array $recipients, // [['email' => 'test@test.com', 'name' => 'Test', 'variables' => []]]
        int $batchSize = 10,
        int $delayBetweenBatches = 60 // seconds
    ): array {
        $results = [
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $batches = array_chunk($recipients, $batchSize);
        
        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $recipient) {
                $success = $this->sendTemplatedEmail(
                    $templateName,
                    $recipient['email'],
                    $recipient['variables'] ?? [],
                    $recipient['name'] ?? null
                );

                if ($success) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to send to {$recipient['email']}";
                }
            }

            // Delay between batches to avoid overwhelming shared hosting
            if ($batchIndex < count($batches) - 1) {
                sleep($delayBetweenBatches);
            }
        }

        return $results;
    }

    /**
     * Send simple email without template
     */
    public function sendSimpleEmail(
        string $toEmail,
        string $subject,
        string $content,
        string $toName = null,
        bool $isHtml = true,
        MailConfiguration $config = null
    ): bool {
        try {
            // Get mail configuration
            if (!$config) {
                $config = MailConfiguration::getAvailable();
                if (!$config) {
                    throw new \Exception('No available mail configuration');
                }
            }

            // Check rate limits
            if (!$config->canSendMail()) {
                throw new \Exception('Mail configuration has reached sending limits');
            }

            // Create mail log
            $mailLog = MailLog::create([
                'mail_configuration_id' => $config->id,
                'to_email' => $toEmail,
                'to_name' => $toName,
                'subject' => $subject,
                'status' => 'pending',
                'metadata' => [
                    'is_html' => $isHtml,
                    'sent_via' => $config->name
                ]
            ]);

            // Configure mailer
            $this->configureMailer($config);

            // Send email
            if ($isHtml) {
                Mail::html($content, function ($message) use ($subject, $toEmail, $toName, $config) {
                    $message->to($toEmail, $toName)
                           ->subject($subject)
                           ->from($config->from_address, $config->from_name);
                    
                    if ($config->reply_to_address) {
                        $message->replyTo($config->reply_to_address, $config->reply_to_name);
                    }
                });
            } else {
                Mail::raw($content, function ($message) use ($subject, $toEmail, $toName, $config) {
                    $message->to($toEmail, $toName)
                           ->subject($subject)
                           ->from($config->from_address, $config->from_name);
                    
                    if ($config->reply_to_address) {
                        $message->replyTo($config->reply_to_address, $config->reply_to_name);
                    }
                });
            }

            // Mark as sent
            $mailLog->markAsSent();
            $config->incrementSentCounters();

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send simple email', [
                'to_email' => $toEmail,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);

            if (isset($mailLog)) {
                $mailLog->markAsFailed($e->getMessage());
            }

            return false;
        }
    }

    /**
     * Configure Laravel Mail with specific configuration
     */
    private function configureMailer(MailConfiguration $config): void
    {
        $mailConfig = $config->toMailConfig();
        
        // Set mail configuration dynamically
        Config::set('mail.default', 'custom');
        Config::set('mail.mailers.custom', $mailConfig);
    }

    /**
     * Get sending statistics
     */
    public function getStatistics(): array
    {
        return [
            'today' => MailLog::getTodayStats(),
            'configurations' => MailConfiguration::active()->get()->map(function ($config) {
                return [
                    'name' => $config->name,
                    'sent_today' => $config->sent_today,
                    'daily_limit' => $config->daily_limit,
                    'sent_this_hour' => $config->sent_this_hour,
                    'hourly_limit' => $config->hourly_limit,
                    'can_send' => $config->canSendMail()
                ];
            })
        ];
    }

    /**
     * Test mail configuration
     */
    public function testConfiguration(MailConfiguration $config, string $testEmail): array
    {
        try {
            $this->configureMailer($config);
            
            Mail::raw(
                'This is a test email to verify your mail configuration is working correctly.',
                function ($message) use ($testEmail, $config) {
                    $message->to($testEmail)
                           ->subject('Mail Configuration Test')
                           ->from($config->from_address, $config->from_name);
                }
            );

            return [
                'success' => true,
                'message' => 'Test email sent successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ];
        }
    }
}