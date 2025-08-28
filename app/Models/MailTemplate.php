<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailTemplate extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'html_content',
        'text_content',
        'variables',
        'category',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array'
    ];

    /**
     * Mail logs relationship
     */
    public function mailLogs(): HasMany
    {
        return $this->hasMany(MailLog::class, 'template_name', 'name');
    }

    /**
     * Process template with variables
     */
    public function processTemplate(array $variables = []): array
    {
        $subject = $this->replaceVariables($this->subject, $variables);
        $htmlContent = $this->replaceVariables($this->html_content, $variables);
        $textContent = $this->text_content ? $this->replaceVariables($this->text_content, $variables) : null;
        
        return [
            'subject' => $subject,
            'html_content' => $htmlContent,
            'text_content' => $textContent
        ];
    }

    /**
     * Replace variables in content
     */
    private function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Get template by name
     */
    public static function getByName(string $name)
    {
        return static::where('name', $name)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Get available variables for this template
     */
    public function getAvailableVariables(): array
    {
        return $this->variables ?: [];
    }

    /**
     * Scope: Active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
