<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_cart_amount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
        'priority',
        'is_combinable'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'is_combinable' => 'boolean',
        'value' => 'decimal:2',
        'minimum_cart_amount' => 'decimal:2'
    ];

    protected $appends = [
        'is_valid'
    ];

    /**
     * Get the rules for the coupon.
     */
    public function rules()
    {
        return $this->hasMany(CouponRule::class);
    }

    /**
     * Get the usages for the coupon.
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Check if coupon is currently valid
     */
    public function getIsValidAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->valid_from && $now < $this->valid_from) {
            return false;
        }
        
        if ($this->valid_until && $now > $this->valid_until) {
            return false;
        }
        
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if coupon is usable by a specific user
     */
    public function isUsableByUser($userId): bool
    {
        if (!$this->is_valid) {
            return false;
        }
        
        if ($this->usage_limit_per_user) {
            $userUsageCount = $this->usages()
                ->where('user_id', $userId)
                ->count();
                
            if ($userUsageCount >= $this->usage_limit_per_user) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get formatted value for display
     */
    public function getFormattedValueAttribute(): string
    {
        switch ($this->type) {
            case 'percentage':
                return "%{$this->value}";
            case 'fixed_amount':
                return "₺" . number_format($this->value, 2, ',', '.');
            case 'free_shipping':
                return "Ücretsiz Kargo";
            case 'first_order':
                return "İlk Sipariş İndirimi";
            default:
                return $this->value;
        }
    }

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for valid coupons
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhereColumn('used_count', '<', 'usage_limit');
            });
    }
}