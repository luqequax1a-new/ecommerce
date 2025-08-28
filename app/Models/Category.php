<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Traits\HasSEO;
use App\Services\SlugService;

class Category extends Model
{
    use HasSEO;
    
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'parent_id',
        'image_path',
        'banner_path',
        'icon_class',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'robots',
        'schema_markup',
        'auto_update_slug',
        'is_active',
        'show_in_menu',
        'show_in_footer',
        'sort_order',
        'level',
        'featured',
        'template',
        'filters'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_footer' => 'boolean',
        'featured' => 'boolean',
        'auto_update_slug' => 'boolean',
        'sort_order' => 'integer',
        'level' => 'integer',
        'filters' => 'array',
        'schema_markup' => 'array'
    ];

    /**
     * Override SEO trait methods for category-specific behavior
     */
    public function getSEOEntityType(): string
    {
        return 'category';
    }

    /**
     * Categories need parent-scoped slug uniqueness
     */
    protected function getSlugScopeConditions(): array
    {
        return ['parent_id' => $this->parent_id];
    }

    /**
     * Generate category path for URLs
     */
    public function generateCategoryPath(): string
    {
        return SlugService::generateCategoryPath($this);
    }
    /**
     * Boot model with enhanced SEO and tree functionality
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            // Level'i otomatik hesapla
            if ($category->parent_id) {
                $parent = static::find($category->parent_id);
                $category->level = $parent ? $parent->level + 1 : 0;
            } else {
                $category->level = 0;
            }
        });
        
        static::updating(function ($category) {
            // Parent değiştiyse level'i güncelle
            if ($category->isDirty('parent_id')) {
                if ($category->parent_id) {
                    $parent = static::find($category->parent_id);
                    $category->level = $parent ? $parent->level + 1 : 0;
                } else {
                    $category->level = 0;
                }
                
                // Update all children levels recursively
                $category->updateChildrenLevels();
            }
        });
    }

    /**
     * Parent category ilişkisi
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Child categories ilişkisi
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->ordered();
    }

    /**
     * Tüm alt kategoriler (recursive)
     */
    public function descendants(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->with('descendants');
    }

    /**
     * Update children levels recursively
     */
    public function updateChildrenLevels(): void
    {
        $this->children()->each(function ($child) {
            $child->level = $this->level + 1;
            $child->saveQuietly(); // Avoid triggering events
            $child->updateChildrenLevels();
        });
    }

    /**
     * Prevent circular references in category tree
     */
    public function canHaveParent($parentId): bool
    {
        if (!$parentId || $parentId == $this->id) {
            return false;
        }
        
        // Check if the proposed parent is a descendant
        $descendants = $this->getAllDescendantIds();
        return !in_array($parentId, $descendants);
    }

    /**
     * Get all descendant IDs (for circular reference check)
     */
    public function getAllDescendantIds(): array
    {
        $ids = [];
        
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        
        return $ids;
    }

    /**
     * Get full category path (parent/child format)
     */
    public function getFullPath(): string
    {
        return $this->generateCategoryPath();
    }
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
     * Category image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    /**
     * Category banner URL
     */
    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner_path ? Storage::url($this->banner_path) : null;
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
        return $value ?: $this->short_description ?: $this->description;
    }

    /**
     * Breadcrumb trail
     */
    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $category = $this;
        
        while ($category) {
            array_unshift($breadcrumb, [
                'name' => $category->name,
                'slug' => $category->slug,
                'url' => route('category.show', $category->slug)
            ]);
            $category = $category->parent;
        }
        
        return $breadcrumb;
    }

    /**
     * Scope: Sadece aktif kategoriler
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Menüde gösterilen kategoriler
     */
    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Scope: Footer'da gösterilen kategoriler
     */
    public function scopeInFooter($query)
    {
        return $query->where('show_in_footer', true);
    }

    /**
     * Scope: Öne çıkan kategoriler
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope: Ana kategoriler (parent_id null)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Sıralı kategoriler
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Shared hosting uyumlu kategori ağacı
     */
    public static function getMenuTree(): array
    {
        // Shared hosting'de cache kısıtlı olabilir
        // Basit query ile menü ağacı oluştur
        $categories = static::active()
            ->inMenu()
            ->with(['children' => function($query) {
                $query->active()->inMenu()->ordered();
            }])
            ->roots()
            ->ordered()
            ->get();
            
        return $categories->toArray();
    }

    /**
     * Ürün sayısı (alt kategoriler dahil)
     */
    public function getTotalProductCountAttribute(): int
    {
        $count = $this->products()->count();
        
        foreach ($this->children as $child) {
            $count += $child->total_product_count;
        }
        
        return $count;
    }

    /**
     * Route key name for SEO URLs
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * URL oluştur
     */
    public function getUrlAttribute(): string
    {
        return route('category.show', $this->slug);
    }

    /**
     * Canonical URL with fallback
     */
    public function getCanonicalUrlAttribute($value): string
    {
        return $value ?: $this->url;
    }
}
