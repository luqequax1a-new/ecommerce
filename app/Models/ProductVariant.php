<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'price', 'stock_quantity',
        'main_image', 'extra_images', 'attributes'
    ];

    protected $casts = [
        'extra_images' => 'array',
        'attributes'   => 'array',
        'price'        => 'decimal:2',
        'stock_quantity' => 'decimal:3',
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements() {
        return $this->hasMany(StockMovement::class, 'product_variant_id');
    }

    /**
     * Get unit through product relationship
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
        return $this->product->formatStockWithUnit($this->stock_quantity);
    }

    /**
     * Format price with unit
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->product->formatPriceWithUnit($this->price);
    }
}
