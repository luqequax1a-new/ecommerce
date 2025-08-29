<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailConfiguration;
use App\Models\MailTemplate;
use App\Models\MailLog;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MailController extends Controller
{
    protected MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * Mail management dashboard
     */
    public function index()
    {
        $statistics = $this->mailService->getStatistics();
        $recentLogs = MailLog::with(['mailConfiguration', 'mailTemplate'])
                            ->latest()
                            ->limit(10)
                            ->get();
        
        return view('admin.mail.index', compact('statistics', 'recentLogs'));
    }

    /**
     * Mail configurations management
     */
    public function configurations()
    {
        $configurations = MailConfiguration::latest()->paginate(10);
        return view('admin.mail.configurations', compact('configurations'));
    }

    /**
     * Store new mail configuration
     */
    public function storeConfiguration(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:mail_configurations',
            'driver' => 'required|in:smtp,sendmail,mailgun',
            'host' => 'required_if:driver,smtp',
            'port' => 'required_if:driver,smtp|integer',
            'username' => 'required_if:driver,smtp',
            'password' => 'required_if:driver,smtp',
            'encryption' => 'nullable|in:tls,ssl',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
            'reply_to_address' => 'nullable|email',
            'reply_to_name' => 'nullable|string',
            'daily_limit' => 'required|integer|min:1|max:5000',
            'hourly_limit' => 'required|integer|min:1|max:500',
            'is_default' => 'boolean'
        ]);

        // If setting as default, remove default from others
        if ($request->boolean('is_default')) {
            MailConfiguration::where('is_default', true)->update(['is_default' => false]);
        }

        MailConfiguration::create($request->all());

        return redirect()->route('admin.mail.configurations')
                        ->with('success', 'Mail configuration created successfully.');
    }

    /**
     * Test mail configuration
     */
    public function testConfiguration(Request $request, MailConfiguration $configuration)
    {
        $request->validate([
            'test_email' => 'required|email'
        ]);

        $result = $this->mailService->testConfiguration($configuration, $request->test_email);

        return response()->json($result);
    }

    /**
     * Mail templates management
     */
    public function templates()
    {
        $templates = MailTemplate::latest()->paginate(15);
        return view('admin.mail.templates', compact('templates'));
    }

    /**
     * Store new mail template
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:mail_templates',
            'subject' => 'required|string',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
            'category' => 'required|in:general,marketing,transactional',
            'variables' => 'nullable|array'
        ]);

        MailTemplate::create($request->all());

        return redirect()->route('admin.mail.templates')
                        ->with('success', 'Mail template created successfully.');
    }

    /**
     * Show send email form
     */
    public function showSendForm()
    {
        $templates = MailTemplate::active()->get();
        $configurations = MailConfiguration::active()->get();
        
        return view('admin.mail.send', compact('templates', 'configurations'));
    }

    /**
     * Send single email
     */
    public function sendSingle(Request $request)
    {
        $request->validate([
            'to_email' => 'required|email',
            'to_name' => 'nullable|string',
            'template_id' => 'nullable|exists:mail_templates,id',
            'subject' => 'required_without:template_id|string',
            'content' => 'required_without:template_id|string',
            'variables' => 'nullable|array',
            'configuration_id' => 'nullable|exists:mail_configurations,id'
        ]);

        $config = $request->configuration_id ? 
                 MailConfiguration::find($request->configuration_id) : null;

        if ($request->template_id) {
            // Send templated email
            $template = MailTemplate::find($request->template_id);
            $success = $this->mailService->sendTemplatedEmail(
                $template->name,
                $request->to_email,
                $request->variables ?? [],
                $request->to_name,
                $config
            );
        } else {
            // Send simple email
            $success = $this->mailService->sendSimpleEmail(
                $request->to_email,
                $request->subject,
                $request->content,
                $request->to_name,
                true, // HTML
                $config
            );
        }

        if ($success) {
            return redirect()->back()->with('success', 'Email sent successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to send email.');
        }
    }

    /**
     * Show bulk email form
     */
    public function showBulkForm()
    {
        $templates = MailTemplate::active()->get();
        $configurations = MailConfiguration::active()->get();
        
        return view('admin.mail.bulk', compact('templates', 'configurations'));
    }

    /**
     * Send bulk emails
     */
    public function sendBulk(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:mail_templates,id',
            'recipients' => 'required|string', // CSV format: email,name\n
            'batch_size' => 'required|integer|min:1|max:50',
            'delay_between_batches' => 'required|integer|min:30|max:300',
            'variables' => 'nullable|array'
        ]);

        // Parse recipients
        $recipientLines = explode("\n", trim($request->recipients));
        $recipients = [];
        
        foreach ($recipientLines as $line) {
            $parts = str_getcsv($line);
            if (count($parts) >= 1 && filter_var($parts[0], FILTER_VALIDATE_EMAIL)) {
                $recipients[] = [
                    'email' => $parts[0],
                    'name' => $parts[1] ?? null,
                    'variables' => $request->variables ?? []
                ];
            }
        }

        if (empty($recipients)) {
            return redirect()->back()->with('error', 'No valid recipients found.');
        }

        $template = MailTemplate::find($request->template_id);
        
        // Send bulk emails in background (for shared hosting, we'll do it synchronously but with delays)
        $results = $this->mailService->sendBulkEmails(
            $template->name,
            $recipients,
            $request->batch_size,
            $request->delay_between_batches
        );

        return redirect()->back()->with('success', 
            "Bulk email completed. Sent: {$results['sent']}, Failed: {$results['failed']}");
    }

    /**
     * Show mail logs
     */
    public function logs(Request $request)
    {
        $query = MailLog::with(['mailConfiguration', 'mailTemplate'])
                       ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by email
        if ($request->filled('email')) {
            $query->where('to_email', 'like', '%' . $request->email . '%');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20);
        
        return view('admin.mail.logs', compact('logs'));
    }
}
