<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\StockHelper;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'price', 'stock_quantity', 'attributes'
    ];

    protected $casts = [
        'attributes'   => 'array',
        'price'        => 'decimal:2',
        'stock_quantity' => 'decimal:3',
    ];

    /**
     * Product relationship
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Stock movements relationship
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_variant_id');
    }

    /**
     * Get unit through product relationship (variants inherit unit from product)
     */
    public function getUnitAttribute()
    {
        return $this->product->unit;
    }

    /**
     * Format stock quantity with unit
     */
    public function getFormattedStockAttribute(): string
    {
        return StockHelper::formatStockWithUnit($this->stock_quantity, $this->unit);
    }

    /**
     * Format price with unit
     */
    public function getFormattedPriceAttribute(): string
    {
        return StockHelper::formatPriceWithUnit($this->price, $this->unit);
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Scope: In stock variants
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope: Out of stock variants
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    /**
     * Get variant display name (combines product name with attributes)
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product->name;
        
        if ($this->attributes && is_array($this->attributes)) {
            $attributes = [];
            foreach ($this->attributes as $key => $value) {
                $attributes[] = $value;
            }
            if (!empty($attributes)) {
                $name .= ' (' . implode(', ', $attributes) . ')';
            }
        }
        
        return $name;
    }

    /**
     * Get stock status for this variant
     */
    public function getStockStatus(float $lowStockThreshold = 5): string
    {
        return StockHelper::getVariantStockStatus($this, $lowStockThreshold);
    }

    /**
     * Get stock status badge HTML
     */
    public function getStockStatusBadge(float $lowStockThreshold = 5): string
    {
        $status = $this->getStockStatus($lowStockThreshold);
        return StockHelper::getStockStatusBadge($status, $this->stock_quantity, $this->unit);
    }

    /**
     * Check if variant has sufficient stock
     */
    public function hasSufficientStock(float $requestedQuantity): bool
    {
        return StockHelper::hasSufficientStock($this, $requestedQuantity);
    }

    /**
     * Check if variant is low on stock
     */
    public function isLowStock(float $threshold = 5): bool
    {
        return $this->getStockStatus($threshold) === StockHelper::STATUS_LOW_STOCK;
    }

    /**
     * Check if variant is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->getStockStatus() === StockHelper::STATUS_OUT_OF_STOCK;
    }
}
