<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_path',
        'website_url',
        'email',
        'phone',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Boot model - Auto generate slug
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($brand) {
            if (!$brand->slug) {
                $brand->slug = Str::slug($brand->name);
            }
        });
        
        static::updating(function ($brand) {
            if ($brand->isDirty('name') && !$brand->isDirty('slug')) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    /**
     * Products ilişkisi
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Aktif ürünler
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    /**
     * Logo URL'i döndür
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }
        
        return Storage::url($this->logo_path);
    }

    /**
     * Meta title - fallback to name
     */
    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->name;
    }

    /**
     * Meta description - fallback to description
     */
    public function getMetaDescriptionAttribute($value): ?string
    {
        return $value ?: $this->description;
    }

    /**
     * Scope: Sadece aktif markalar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Sıralı markalar
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Shared hosting uyumlu marka listesi (cache)
     */
    public static function getActiveForSelect(): array
    {
        // Shared hosting'de cache kullanımı sınırlı olabilir
        // Bu yüzden basit query kullanıyoruz
        return static::active()
            ->ordered()
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Ürün sayısını al (shared hosting optimize)
     */
    public function getProductCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Route key name for SEO friendly URLs
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
