<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ShippingMethod extends Model
{
    protected $fillable = [
        'carrier_id',
        'zone_id',
        'name',
        'code',
        'description',
        'calc_method',
        'base_fee',
        'step_fee',
        'step_size',
        'free_threshold',
        'free_threshold_includes_tax',
        'min_weight',
        'max_weight',
        'min_price',
        'max_price',
        'min_quantity',
        'max_quantity',
        'min_delivery_days',
        'max_delivery_days',
        'delivery_time_text',
        'exclude_virtual_products',
        'require_signature',
        'supports_cod',
        'excluded_product_categories',
        'excluded_product_types',
        'tax_class_id',
        'is_taxable',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'step_fee' => 'decimal:2',
        'step_size' => 'decimal:3',
        'free_threshold' => 'decimal:2',
        'free_threshold_includes_tax' => 'boolean',
        'min_weight' => 'decimal:3',
        'max_weight' => 'decimal:3',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'min_delivery_days' => 'integer',
        'max_delivery_days' => 'integer',
        'exclude_virtual_products' => 'boolean',
        'require_signature' => 'boolean',
        'supports_cod' => 'boolean',
        'excluded_product_categories' => 'array',
        'excluded_product_types' => 'array',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Carrier relationship
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Shipping zone relationship
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    /**
     * Tax class relationship
     */
    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    /**
     * Scope: Active methods
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->whereHas('carrier', function($q) {
                        $q->where('is_active', true);
                    });
    }

    /**
     * Scope: For specific zone
     */
    public function scopeForZone(Builder $query, int $zoneId): Builder
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Scope: Ordered by sort order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Calculate shipping cost for given parameters
     */
    public function calculateCost(array $params): array
    {
        $weight = $params['weight'] ?? 0;
        $price = $params['price'] ?? 0;
        $quantity = $params['quantity'] ?? 0;
        $includesTax = $params['includes_tax'] ?? false;
        
        // Check if method is applicable
        if (!$this->isApplicable($params)) {
            return [
                'applicable' => false,
                'cost' => 0,
                'reason' => $this->getNotApplicableReason($params)
            ];
        }

        // Check free threshold
        if ($this->isFreeShipping($price, $includesTax)) {
            return [
                'applicable' => true,
                'cost' => 0,
                'is_free' => true,
                'reason' => 'Free shipping threshold met'
            ];
        }

        // Calculate cost based on method
        $cost = $this->calculateBaseCost($weight, $price, $quantity);

        return [
            'applicable' => true,
            'cost' => $cost,
            'is_free' => false,
            'base_fee' => $this->base_fee,
            'calculation_method' => $this->calc_method
        ];
    }

    /**
     * Check if shipping method is applicable
     */
    public function isApplicable(array $params): bool
    {
        $weight = $params['weight'] ?? 0;
        $price = $params['price'] ?? 0;
        $quantity = $params['quantity'] ?? 0;
        $hasVirtualProducts = $params['has_virtual_products'] ?? false;

        // Check virtual products
        if ($hasVirtualProducts && $this->exclude_virtual_products) {
            return false;
        }

        // Check weight constraints
        if ($this->min_weight && $weight < $this->min_weight) {
            return false;
        }
        if ($this->max_weight && $weight > $this->max_weight) {
            return false;
        }

        // Check price constraints
        if ($this->min_price && $price < $this->min_price) {
            return false;
        }
        if ($this->max_price && $price > $this->max_price) {
            return false;
        }

        // Check quantity constraints
        if ($this->min_quantity && $quantity < $this->min_quantity) {
            return false;
        }
        if ($this->max_quantity && $quantity > $this->max_quantity) {
            return false;
        }

        return true;
    }

    /**
     * Check if free shipping applies
     */
    public function isFreeShipping(float $price, bool $includesTax = false): bool
    {
        if (!$this->free_threshold) {
            return false;
        }

        $comparePrice = $price;
        if ($this->free_threshold_includes_tax !== $includesTax) {
            // Handle tax inclusion mismatch if needed
            // For now, use the price as-is
        }

        return $comparePrice >= $this->free_threshold;
    }

    /**
     * Calculate base cost based on calculation method
     */
    protected function calculateBaseCost(float $weight, float $price, int $quantity): float
    {
        switch ($this->calc_method) {
            case 'flat':
                return $this->base_fee;

            case 'by_weight':
                if ($weight <= 0) {
                    return $this->base_fee;
                }
                $steps = ceil($weight / $this->step_size);
                return $this->base_fee + ($steps * $this->step_fee);

            case 'by_price':
                if ($price <= 0) {
                    return $this->base_fee;
                }
                $steps = ceil($price / $this->step_size);
                return $this->base_fee + ($steps * $this->step_fee);

            case 'by_quantity':
                if ($quantity <= 0) {
                    return $this->base_fee;
                }
                $steps = ceil($quantity / $this->step_size);
                return $this->base_fee + ($steps * $this->step_fee);

            case 'table_rate':
                // Table rate calculation would require additional table
                // For now, fall back to flat rate
                return $this->base_fee;

            default:
                return $this->base_fee;
        }
    }

    /**
     * Get reason why method is not applicable
     */
    protected function getNotApplicableReason(array $params): string
    {
        $weight = $params['weight'] ?? 0;
        $price = $params['price'] ?? 0;
        $quantity = $params['quantity'] ?? 0;
        $hasVirtualProducts = $params['has_virtual_products'] ?? false;

        if ($hasVirtualProducts && $this->exclude_virtual_products) {
            return 'Not available for virtual products';
        }

        if ($this->min_weight && $weight < $this->min_weight) {
            return "Minimum weight: {$this->min_weight} kg";
        }
        if ($this->max_weight && $weight > $this->max_weight) {
            return "Maximum weight: {$this->max_weight} kg";
        }

        if ($this->min_price && $price < $this->min_price) {
            return "Minimum order: {$this->min_price} TL";
        }
        if ($this->max_price && $price > $this->max_price) {
            return "Maximum order: {$this->max_price} TL";
        }

        if ($this->min_quantity && $quantity < $this->min_quantity) {
            return "Minimum quantity: {$this->min_quantity}";
        }
        if ($this->max_quantity && $quantity > $this->max_quantity) {
            return "Maximum quantity: {$this->max_quantity}";
        }

        return 'Not available';
    }

    /**
     * Get delivery time estimate
     */
    public function getDeliveryTimeAttribute(): ?string
    {
        if ($this->delivery_time_text) {
            return $this->delivery_time_text;
        }

        if ($this->min_delivery_days && $this->max_delivery_days) {
            if ($this->min_delivery_days === $this->max_delivery_days) {
                return "{$this->min_delivery_days} iş günü";
            }
            return "{$this->min_delivery_days}-{$this->max_delivery_days} iş günü";
        }

        return $this->carrier->estimated_delivery_time;
    }
}
