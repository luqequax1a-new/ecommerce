<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\StockHelper;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'brand_id',
        'unit_id',
        'product_type',
        'stock_quantity',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'stock_quantity' => 'decimal:3'
    ];

    /**
     * Category ilişkisi
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Brand ilişkisi
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Unit ilişkisi
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Product variants ilişkisi
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Product images ilişkisi
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Sıralı görseller
     */
    public function orderedImages(): HasMany
    {
        return $this->images()->ordered();
    }

    /**
     * Ana görsel (cover image)
     */
    public function coverImage()
    {
        return $this->images()->cover()->first();
    }

    /**
     * Ana görsel URL'i döndür
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        $coverImage = $this->coverImage();
        return $coverImage ? $coverImage->url : null;
    }

    /**
     * Belirli boyutta ana görsel URL'i
     */
    public function getCoverImageResizedUrl(string $size = 'medium'): ?string
    {
        $coverImage = $this->coverImage();
        return $coverImage ? $coverImage->getResizedUrl($size) : null;
    }

    /**
     * Toplam stok miktarını al - product type'a göre
     */
    public function getTotalStockAttribute()
    {
        return StockHelper::calculateTotalStock($this);
    }

    /**
     * Stok miktarını birim ile format la
     */
    public function formatStockWithUnit($quantity): string
    {
        return StockHelper::formatStockWithUnit($quantity, $this->unit);
    }

    /**
     * Fiyatı birim ile formatla
     */
    public function formatPriceWithUnit($price): string
    {
        return StockHelper::formatPriceWithUnit($price, $this->unit);
    }

    /**
     * Product type helper methods
     */
    public function isSimple(): bool
    {
        return $this->product_type === 'simple';
    }

    public function isVariable(): bool
    {
        return $this->product_type === 'variable';
    }

    /**
     * Ürünün minimum fiyatını al - product type'a göre
     */
    public function getMinPriceAttribute()
    {
        $range = StockHelper::getPriceRange($this);
        return $range['min'];
    }

    /**
     * Ürünün maksimum fiyatını al - product type'a göre
     */
    public function getMaxPriceAttribute()
    {
        $range = StockHelper::getPriceRange($this);
        return $range['max'];
    }

    /**
     * Fiyat aralığını string olarak döndür
     */
    public function getPriceRangeAttribute(): string
    {
        return StockHelper::formatPriceRange($this, $this->unit);
    }

    /**
     * Get stock status for this product
     */
    public function getStockStatus(float $lowStockThreshold = 5): string
    {
        return StockHelper::getStockStatus($this, $lowStockThreshold);
    }

    /**
     * Get stock status badge HTML
     */
    public function getStockStatusBadge(float $lowStockThreshold = 5): string
    {
        $status = $this->getStockStatus($lowStockThreshold);
        $totalStock = $this->total_stock;
        return StockHelper::getStockStatusBadge($status, $totalStock, $this->unit);
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasSufficientStock(float $requestedQuantity): bool
    {
        return StockHelper::hasSufficientStock($this, $requestedQuantity);
    }

    /**
     * Get stock value (quantity * price)
     */
    public function getStockValue(): float
    {
        return StockHelper::calculateStockValue($this);
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        return $this->total_stock > 0;
    }

    /**
     * Check if product is low on stock
     */
    public function isLowStock(float $threshold = 5): bool
    {
        return $this->getStockStatus($threshold) === StockHelper::STATUS_LOW_STOCK;
    }

    /**
     * Check if product is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->getStockStatus() === StockHelper::STATUS_OUT_OF_STOCK;
    }

    /**
     * Meta title with fallback
     */
    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->name;
    }

    /**
     * Meta description with fallback
     */
    public function getMetaDescriptionAttribute($value): ?string
    {
        return $value ?: $this->description;
    }

    /**
     * Breadcrumb with category
     */
    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        
        if ($this->category) {
            $breadcrumb = $this->category->breadcrumb;
        }
        
        $breadcrumb[] = [
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => route('product.show', $this->slug)
        ];
        
        return $breadcrumb;
    }

    /**
     * Scope: Aktif ürünler
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Kategoriye göre
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Markaya göre
     */
    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    /**
     * Scope: Basit ürünler
     */
    public function scopeSimple($query)
    {
        return $query->where('product_type', 'simple');
    }

    /**
     * Scope: Varyantlı ürünler
     */
    public function scopeVariable($query)
    {
        return $query->where('product_type', 'variable');
    }

    /**
     * Scope: Stokta olan ürünler
     */
    public function scopeInStock($query)
    {
        return $query->whereHas('variants', function ($q) {
            $q->where('stock_quantity', '>', 0);
        })->orWhere(function ($q) {
            $q->where('product_type', 'simple')
              ->where('stock_quantity', '>', 0);
        });
    }

    /**
     * Ürün URL'i
     */
    public function getUrlAttribute(): string
    {
        return route('product.show', $this->slug);
    }

    /**
     * Route key name for SEO URLs
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
