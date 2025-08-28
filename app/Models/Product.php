<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'brand_id',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];

    protected $casts = [
        'is_active' => 'boolean'
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
     * Ürünün minimum fiyatını al
     */
    public function getMinPriceAttribute()
    {
        return $this->variants->min('price') ?? 0;
    }

    /**
     * Ürünün maksimum fiyatını al
     */
    public function getMaxPriceAttribute()
    {
        return $this->variants->max('price') ?? 0;
    }

    /**
     * Fiyat aralığını string olarak döndür
     */
    public function getPriceRangeAttribute(): string
    {
        $min = $this->min_price;
        $max = $this->max_price;
        
        if ($min == $max) {
            return number_format($min, 2) . ' TL';
        }
        
        return number_format($min, 2) . ' - ' . number_format($max, 2) . ' TL';
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
