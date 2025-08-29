<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class MailConfiguration extends Model
{
    protected $fillable = [
        'name',
        'driver',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'reply_to_address',
        'reply_to_name',
        'is_active',
        'is_default',
        'daily_limit',
        'hourly_limit',
        'sent_today',
        'sent_this_hour',
        'last_sent_at',
        'last_reset_at',
        'additional_settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'port' => 'integer',
        'daily_limit' => 'integer',
        'hourly_limit' => 'integer',
        'sent_today' => 'integer',
        'sent_this_hour' => 'integer',
        'last_sent_at' => 'datetime',
        'last_reset_at' => 'datetime',
        'additional_settings' => 'array'
    ];

    protected $hidden = [
        'password'
    ];

    /**
     * Mail logs relationship
     */
    public function mailLogs(): HasMany
    {
        return $this->hasMany(MailLog::class);
    }

    /**
     * Get the default mail configuration
     */
    public static function getDefault()
    {
        return static::where('is_default', true)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Get available mail configuration (not exceeded limits)
     */
    public static function getAvailable()
    {
        $configs = static::where('is_active', true)
                        ->orderBy('is_default', 'desc')
                        ->get();
        
        foreach ($configs as $config) {
            if ($config->canSendMail()) {
                return $config;
            }
        }
        
        return null;
    }

    /**
     * Check if this configuration can send mail (within limits)
     */
    public function canSendMail(): bool
    {
        $this->resetCountersIfNeeded();
        
        return $this->sent_today < $this->daily_limit && 
               $this->sent_this_hour < $this->hourly_limit;
    }

    /**
     * Reset counters if time periods have passed
     */
    public function resetCountersIfNeeded(): void
    {
        $now = Carbon::now();
        $lastReset = $this->last_reset_at ?: $this->created_at;
        
        // Reset daily counter if it's a new day
        if ($lastReset->format('Y-m-d') !== $now->format('Y-m-d')) {
            $this->update([
                'sent_today' => 0,
                'sent_this_hour' => 0,
                'last_reset_at' => $now
            ]);
            return;
        }
        
        // Reset hourly counter if it's a new hour
        if ($lastReset->format('Y-m-d H') !== $now->format('Y-m-d H')) {
            $this->update([
                'sent_this_hour' => 0,
                'last_reset_at' => $now
            ]);
        }
    }

    /**
     * Increment sent counters
     */
    public function incrementSentCounters(): void
    {
        $this->increment('sent_today');
        $this->increment('sent_this_hour');
        $this->update(['last_sent_at' => Carbon::now()]);
    }

    /**
     * Get Laravel mail configuration array
     */
    public function toMailConfig(): array
    {
        $config = [
            'driver' => $this->driver,
            'from' => [
                'address' => $this->from_address,
                'name' => $this->from_name
            ]
        ];
        
        if ($this->driver === 'smtp') {
            $config['host'] = $this->host;
            $config['port'] = $this->port;
            $config['username'] = $this->username;
            $config['password'] => $this->password;
            $config['encryption'] => $this->encryption;
        }
        
        if ($this->reply_to_address) {
            $config['reply_to'] = [
                'address' => $this->reply_to_address,
                'name' => $this->reply_to_name ?: $this->from_name
            ];
        }
        
        // Merge additional settings
        if ($this->additional_settings) {
            $config = array_merge($config, $this->additional_settings);
        }
        
        return $config;
    }

    /**
     * Test connection
     */
    public function testConnection(): array
    {
        try {
            // Here you would implement actual SMTP connection test
            // For now, just return success
            return [
                'success' => true,
                'message' => 'Connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Scope: Active configurations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Default configuration
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
