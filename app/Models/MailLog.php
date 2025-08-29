<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MailLog extends Model
{
    protected $fillable = [
        'mail_configuration_id',
        'to_email',
        'to_name',
        'subject',
        'template_name',
        'status',
        'error_message',
        'sent_at',
        'metadata'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Mail configuration relationship
     */
    public function mailConfiguration(): BelongsTo
    {
        return $this->belongsTo(MailConfiguration::class);
    }

    /**
     * Mail template relationship
     */
    public function mailTemplate(): BelongsTo
    {
        return $this->belongsTo(MailTemplate::class, 'template_name', 'name');
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => Carbon::now()
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Get sending statistics for today
     */
    public static function getTodayStats(): array
    {
        $today = Carbon::today();
        
        return [
            'total_sent' => static::where('status', 'sent')
                                ->whereDate('sent_at', $today)
                                ->count(),
            'total_failed' => static::where('status', 'failed')
                                  ->whereDate('created_at', $today)
                                  ->count(),
            'total_pending' => static::where('status', 'pending')
                                   ->whereDate('created_at', $today)
                                   ->count()
        ];
    }

    /**
     * Scope: Sent emails
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope: Failed emails
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Pending emails
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Today's emails
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }
}
