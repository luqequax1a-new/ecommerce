<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ShippingBlackout extends Model
{
    protected $fillable = [
        'carrier_id',
        'zone_id',
        'restriction_type',
        'restriction_value',
        'reason',
        'start_date',
        'end_date',
        'is_permanent',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_permanent' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Carrier relationship
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Zone relationship
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    /**
     * Scope: Active blackouts
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Current blackouts
     */
    public function scopeCurrent(Builder $query): Builder
    {
        $today = Carbon::now()->toDateString();
        
        return $query->where(function ($q) use ($today) {
            $q->where('is_permanent', true)
              ->orWhere(function ($sub) use ($today) {
                  $sub->where('start_date', '<=', $today)
                      ->where('end_date', '>=', $today);
              });
        });
    }

    /**
     * Check if blackout affects address
     */
    public function affectsAddress(array $address): bool
    {
        $value = $this->getAddressValue($address);
        
        if (!$value) {
            return false;
        }

        return strtolower($value) === strtolower($this->restriction_value);
    }

    /**
     * Get address value for restriction type
     */
    protected function getAddressValue(array $address): ?string
    {
        switch ($this->restriction_type) {
            case 'postal_code':
                return $address['postal_code'] ?? null;
            case 'city':
                return $address['city'] ?? null;
            case 'region':
                return $address['region'] ?? $address['state'] ?? null;
            case 'country':
                return $address['country'] ?? null;
            default:
                return null;
        }
    }

    /**
     * Check if blackout is currently active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->is_permanent) {
            return true;
        }

        $today = Carbon::now();
        
        return $today->between($this->start_date, $this->end_date);
    }
}
