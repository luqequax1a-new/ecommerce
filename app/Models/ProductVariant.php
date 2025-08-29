<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\StockHelper;
use App\Services\TaxCalculationService;
use App\Models\Unit;

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
     * Unit relationship (through product)
     * Variants inherit unit from their parent product
     */
    public function unit()
    {
        return $this->hasOneThrough(
            Unit::class,
            Product::class,
            'id', // Foreign key on Product table
            'id', // Foreign key on Unit table
            'product_id', // Local key on ProductVariant table
            'unit_id' // Local key on Product table
        );
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

    // ===== TAX CALCULATION METHODS =====

    /**
     * Calculate tax for this variant (inherits from product)
     */
    public function calculateTax(float $basePrice = null, array $conditions = []): array
    {
        $price = $basePrice ?? $this->price ?? 0;
        
        if ($price <= 0) {
            return [
                'tax_amount' => 0,
                'effective_rate' => 0,
                'total_with_tax' => 0,
                'base_amount' => 0
            ];
        }

        // Use product's tax class and entity ID for rule matching
        $conditions = array_merge([
            'entity_type' => 'product',
            'entity_id' => $this->product_id
        ], $conditions);

        $taxService = app(TaxCalculationService::class);
        
        return $taxService->calculateProductTax($this->product, $price, $conditions);
    }

    /**
     * Get variant price including tax
     */
    public function getPriceWithTax(array $conditions = []): float
    {
        $taxResult = $this->calculateTax(null, $conditions);
        return $taxResult['total_with_tax'] ?? $this->price;
    }

    /**
     * Get tax amount for variant price
     */
    public function getTaxAmount(array $conditions = []): float
    {
        $taxResult = $this->calculateTax(null, $conditions);
        return $taxResult['tax_amount'] ?? 0;
    }

    /**
     * Get effective tax rate as percentage for this variant
     */
    public function getEffectiveTaxRate(array $conditions = []): float
    {
        $taxResult = $this->calculateTax(null, $conditions);
        return ($taxResult['effective_rate'] ?? 0) * 100;
    }

    /**
     * Format variant price with tax info for display
     */
    public function getFormattedPriceWithTax(array $conditions = []): array
    {
        $taxResult = $this->calculateTax(null, $conditions);
        
        return [
            'base_price' => '₺' . number_format($this->price, 2),
            'tax_amount' => '₺' . number_format($taxResult['tax_amount'] ?? 0, 2),
            'total_price' => '₺' . number_format($taxResult['total_with_tax'] ?? $this->price, 2),
            'tax_rate' => number_format(($taxResult['effective_rate'] ?? 0) * 100, 2) . '%',
            'tax_class' => $taxResult['tax_class_name'] ?? 'No Tax'
        ];
    }

    /**
     * Get tax class from parent product
     */
    public function getTaxClass()
    {
        return $this->product->taxClass;
    }

    /**
     * Get tax class name from parent product
     */
    public function getTaxClassName(): ?string
    {
        return $this->product->getTaxClassName();
    }

    /**
     * Check if variant has tax configured (through product)
     */
    public function hasTax(): bool
    {
        return $this->product->hasTax();
    }

    /**
     * Check if this variant uses Turkish VAT (through product)
     */
    public function usesTurkishVAT(): bool
    {
        return $this->product->usesTurkishVAT();
    }

    /**
     * Scope: Variants with tax class (through product)
     */
    public function scopeWithTax($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->whereNotNull('tax_class_id');
        });
    }

    /**
     * Scope: Variants using Turkish VAT (through product)
     */
    public function scopeTurkishVAT($query)
    {
        return $query->whereHas('product.taxClass', function ($q) {
            $q->where('code', 'like', 'TR_VAT_%')
              ->orWhere('code', 'like', 'TR_KDV_%');
        });
    }
}
