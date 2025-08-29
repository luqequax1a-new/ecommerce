<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CustomerAddress extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'first_name',
        'last_name',
        'company',
        'tax_number',
        'province_id',
        'district_id',
        'address_line',
        'postal_code',
        'phone',
        'email',
        'is_default_billing',
        'is_default_shipping',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'is_default_billing' => 'boolean',
        'is_default_shipping' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected $appends = [
        'full_name',
        'formatted_address',
        'formatted_phone'
    ];

    /**
     * Get the user that owns this address
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the province
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the district
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Scope for active addresses
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific address type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where(function ($q) use ($type) {
            $q->where('type', $type)->orWhere('type', 'both');
        });
    }

    /**
     * Scope for billing addresses
     */
    public function scopeBilling(Builder $query): Builder
    {
        return $query->ofType('billing');
    }

    /**
     * Scope for shipping addresses
     */
    public function scopeShipping(Builder $query): Builder
    {
        return $query->ofType('shipping');
    }

    /**
     * Scope for default billing address
     */
    public function scopeDefaultBilling(Builder $query): Builder
    {
        return $query->where('is_default_billing', true);
    }

    /**
     * Scope for default shipping address
     */
    public function scopeDefaultShipping(Builder $query): Builder
    {
        return $query->where('is_default_shipping', true);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get formatted address attribute
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = [];
        
        if ($this->address_line) {
            $parts[] = $this->address_line;
        }
        
        if ($this->relationLoaded('district') && $this->district) {
            $parts[] = $this->district->name;
        }
        
        if ($this->relationLoaded('province') && $this->province) {
            $parts[] = $this->province->name;
        }
        
        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }
        
        $parts[] = 'Türkiye';
        
        return implode(', ', $parts);
    }

    /**
     * Get formatted phone attribute
     */
    public function getFormattedPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        
        // Turkish phone format: +90 5XX XXX XX XX
        if (strlen($phone) === 11 && str_starts_with($phone, '05')) {
            return '+90 ' . substr($phone, 1, 3) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7, 2) . ' ' . substr($phone, 9, 2);
        }
        
        if (strlen($phone) === 10 && str_starts_with($phone, '5')) {
            return '+90 ' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6, 2) . ' ' . substr($phone, 8, 2);
        }
        
        return $this->phone;
    }

    /**
     * Validate Turkish phone number
     */
    public static function validateTurkishPhone(string $phone): bool
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Turkish mobile: 05XX XXX XX XX or 5XX XXX XX XX
        if (strlen($phone) === 11 && str_starts_with($phone, '05')) {
            return in_array(substr($phone, 2, 1), ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']);
        }
        
        if (strlen($phone) === 10 && str_starts_with($phone, '5')) {
            return in_array(substr($phone, 1, 1), ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']);
        }
        
        return false;
    }

    /**
     * Validate Turkish postal code
     */
    public static function validateTurkishPostalCode(?string $postalCode): bool
    {
        if (empty($postalCode)) {
            return true; // Postal code is optional
        }
        
        return preg_match('/^[0-9]{5}$/', $postalCode) === 1;
    }

    /**
     * Set as default billing address
     */
    public function setAsDefaultBilling(): void
    {
        // Remove default from other addresses
        static::where('user_id', $this->user_id)
              ->where('id', '!=', $this->id)
              ->update(['is_default_billing' => false]);
        
        $this->update(['is_default_billing' => true]);
    }

    /**
     * Set as default shipping address
     */
    public function setAsDefaultShipping(): void
    {
        // Remove default from other addresses
        static::where('user_id', $this->user_id)
              ->where('id', '!=', $this->id)
              ->update(['is_default_shipping' => false]);
        
        $this->update(['is_default_shipping' => true]);
    }

    /**
     * Get user's default billing address
     */
    public static function getDefaultBilling(int $userId): ?self
    {
        return static::where('user_id', $userId)
                    ->active()
                    ->billing()
                    ->defaultBilling()
                    ->with(['province', 'district'])
                    ->first();
    }

    /**
     * Get user's default shipping address
     */
    public static function getDefaultShipping(int $userId): ?self
    {
        return static::where('user_id', $userId)
                    ->active()
                    ->shipping()
                    ->defaultShipping()
                    ->with(['province', 'district'])
                    ->first();
    }

    /**
     * Get user's address book
     */
    public static function getAddressBook(int $userId): Collection
    {
        return static::where('user_id', $userId)
                    ->active()
                    ->with(['province', 'district'])
                    ->orderBy('is_default_billing', 'desc')
                    ->orderBy('is_default_shipping', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();
        
        // Ensure only one default billing address per user
        static::creating(function ($address) {
            if ($address->is_default_billing) {
                static::where('user_id', $address->user_id)
                      ->update(['is_default_billing' => false]);
            }
            
            if ($address->is_default_shipping) {
                static::where('user_id', $address->user_id)
                      ->update(['is_default_shipping' => false]);
            }
        });
        
        static::updating(function ($address) {
            if ($address->isDirty('is_default_billing') && $address->is_default_billing) {
                static::where('user_id', $address->user_id)
                      ->where('id', '!=', $address->id)
                      ->update(['is_default_billing' => false]);
            }
            
            if ($address->isDirty('is_default_shipping') && $address->is_default_shipping) {
                static::where('user_id', $address->user_id)
                      ->where('id', '!=', $address->id)
                      ->update(['is_default_shipping' => false]);
            }
        });
    }
}
