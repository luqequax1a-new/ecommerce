<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ShippingZone extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'type',
        'countries',
        'regions',
        'cities',
        'postal_codes',
        'postal_code_ranges',
        'default_tax_rate',
        'currency_code',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'countries' => 'array',
        'regions' => 'array',
        'cities' => 'array',
        'postal_codes' => 'array',
        'postal_code_ranges' => 'array',
        'default_tax_rate' => 'decimal:4',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($zone) {
            if (empty($zone->slug)) {
                $zone->slug = Str::slug($zone->name);
            }
        });
    }

    /**
     * Shipping methods relationship
     */
    public function shippingMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'zone_id');
    }

    /**
     * Active shipping methods
     */
    public function activeShippingMethods(): HasMany
    {
        return $this->shippingMethods()->active();
    }

    /**
     * Shipping blackouts relationship
     */
    public function shippingBlackouts(): HasMany
    {
        return $this->hasMany(ShippingBlackout::class, 'zone_id');
    }

    /**
     * Scope: Active zones
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordered by sort order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Check if address matches this zone
     */
    public function matchesAddress(array $address): bool
    {
        $country = $address['country'] ?? null;
        $region = $address['region'] ?? $address['state'] ?? null;
        $city = $address['city'] ?? null;
        $postalCode = $address['postal_code'] ?? null;

        // Check country
        if ($this->countries && $country) {
            if (!in_array(strtoupper($country), array_map('strtoupper', $this->countries))) {
                return false;
            }
        }

        // Check region/state
        if ($this->regions && $region) {
            if (!in_array(strtolower($region), array_map('strtolower', $this->regions))) {
                return false;
            }
        }

        // Check city
        if ($this->cities && $city) {
            if (!in_array(strtolower($city), array_map('strtolower', $this->cities))) {
                return false;
            }
        }

        // Check postal code
        if ($postalCode) {
            if (!$this->matchesPostalCode($postalCode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if postal code matches zone rules
     */
    public function matchesPostalCode(string $postalCode): bool
    {
        // Exact postal code match
        if ($this->postal_codes && in_array($postalCode, $this->postal_codes)) {
            return true;
        }

        // Postal code range match
        if ($this->postal_code_ranges) {
            foreach ($this->postal_code_ranges as $range) {
                if (isset($range['from']) && isset($range['to'])) {
                    if ($postalCode >= $range['from'] && $postalCode <= $range['to']) {
                        return true;
                    }
                }
            }
        }

        // If no postal code restrictions, allow all
        if (!$this->postal_codes && !$this->postal_code_ranges) {
            return true;
        }

        return false;
    }

    /**
     * Get available shipping methods for parameters
     */
    public function getAvailableShippingMethods(array $params = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->activeShippingMethods()
                   ->ordered()
                   ->get()
                   ->filter(function ($method) use ($params) {
                       return $method->isApplicable($params);
                   });
    }

    /**
     * Find zone for address
     */
    public static function findForAddress(array $address): ?self
    {
        return static::active()
                    ->ordered()
                    ->get()
                    ->first(function ($zone) use ($address) {
                        return $zone->matchesAddress($address);
                    });
    }

    /**
     * Get zone display name with type
     */
    public function getDisplayNameAttribute(): string
    {
        $typeLabels = [
            'country' => 'Ülke',
            'region' => 'Bölge', 
            'city' => 'Şehir',
            'postal_code' => 'Posta Kodu',
            'custom' => 'Özel'
        ];

        $typeLabel = $typeLabels[$this->type] ?? 'Özel';
        
        return "{$this->name} ({$typeLabel})";
    }

    /**
     * Check if zone has any blackouts for current date
     */
    public function hasActiveBlackouts(): bool
    {
        return $this->shippingBlackouts()
                   ->where('is_active', true)
                   ->where(function ($query) {
                       $query->where('is_permanent', true)
                             ->orWhere(function ($q) {
                                 $today = now()->toDateString();
                                 $q->where('start_date', '<=', $today)
                                   ->where('end_date', '>=', $today);
                             });
                   })
                   ->exists();
    }
}
