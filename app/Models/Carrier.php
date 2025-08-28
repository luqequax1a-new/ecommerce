<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Carrier extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'logo_path',
        'website_url',
        'contact_phone',
        'contact_email',
        'tracking_url_template',
        'api_endpoint',
        'api_credentials',
        'supports_cod',
        'supports_return',
        'supports_international',
        'estimated_delivery_time',
        'max_weight',
        'max_dimensions_length',
        'max_dimensions_width',
        'max_dimensions_height',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'api_credentials' => 'array',
        'supports_cod' => 'boolean',
        'supports_return' => 'boolean',
        'supports_international' => 'boolean',
        'is_active' => 'boolean',
        'max_weight' => 'decimal:3',
        'max_dimensions_length' => 'decimal:2',
        'max_dimensions_width' => 'decimal:2',
        'max_dimensions_height' => 'decimal:2',
        'sort_order' => 'integer'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($carrier) {
            if (empty($carrier->slug)) {
                $carrier->slug = Str::slug($carrier->name);
            }
            if (empty($carrier->code)) {
                $carrier->code = Str::slug($carrier->name, '_');
            }
        });
    }

    /**
     * Shipping methods relationship
     */
    public function shippingMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class);
    }

    /**
     * Active shipping methods
     */
    public function activeShippingMethods(): HasMany
    {
        return $this->shippingMethods()->where('is_active', true);
    }

    /**
     * Shipping blackouts relationship
     */
    public function shippingBlackouts(): HasMany
    {
        return $this->hasMany(ShippingBlackout::class);
    }

    /**
     * Order shipments relationship
     */
    public function orderShipments(): HasMany
    {
        return $this->hasMany(OrderShipment::class);
    }

    /**
     * Scope: Active carriers
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
     * Get tracking URL for a tracking number
     */
    public function getTrackingUrl(string $trackingNumber): ?string
    {
        if (empty($this->tracking_url_template) || empty($trackingNumber)) {
            return null;
        }
        
        return str_replace('{tracking}', $trackingNumber, $this->tracking_url_template);
    }

    /**
     * Check if carrier supports COD for a zone
     */
    public function supportsCodForZone(int $zoneId): bool
    {
        return $this->supports_cod && 
               $this->activeShippingMethods()
                    ->where('zone_id', $zoneId)
                    ->where('supports_cod', true)
                    ->exists();
    }

    /**
     * Get logo URL
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    /**
     * Get formatted dimensions
     */
    public function getMaxDimensionsAttribute(): ?string
    {
        if (!$this->max_dimensions_length || !$this->max_dimensions_width || !$this->max_dimensions_height) {
            return null;
        }
        
        return "{$this->max_dimensions_length} x {$this->max_dimensions_width} x {$this->max_dimensions_height} cm";
    }
}
