<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'variant_ids',
        'original_filename',
        'path',
        'alt_text',
        'description',
        'sort_order',
        'is_cover',
        'is_variant_specific',
        'image_type',
        'width',
        'height',
        'file_size',
        'mime_type'
    ];

    protected $casts = [
        'is_cover' => 'boolean',
        'is_variant_specific' => 'boolean',
        'sort_order' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'integer',
        'variant_ids' => 'array'
    ];

    /**
     * Product ile ilişki
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Tam URL döndür
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    /**
     * Belirli boyut için URL döndür (Prestashop tarzı)
     * @param string $size thumbnail|small|medium|large|xlarge
     */
    public function getResizedUrl(string $size = 'medium'): string
    {
        $pathInfo = pathinfo($this->path);
        $resizedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];
        
        if (Storage::exists($resizedPath)) {
            return Storage::url($resizedPath);
        }
        
        // Eğer resize edilmiş görsel yoksa orijinali döndür
        return $this->url;
    }

    /**
     * Dosya boyutunu insan okuyabilir formatta döndür
     */
    public function getHumanFileSizeAttribute(): string
    {
        if (!$this->file_size) return 'Unknown';
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope: Sadece cover görselleri
     */
    public function scopeCover($query)
    {
        return $query->where('is_cover', true);
    }

    /**
     * Scope: Sıralı görseller
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Scope: Image type'a göre filtrele
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('image_type', $type);
    }

    /**
     * Scope: Variant-specific images
     */
    public function scopeVariantSpecific($query)
    {
        return $query->where('is_variant_specific', true);
    }

    /**
     * Scope: General product images (not variant-specific)
     */
    public function scopeGeneral($query)
    {
        return $query->where('is_variant_specific', false);
    }

    /**
     * Scope: Images for specific variant
     */
    public function scopeForVariant($query, int $variantId)
    {
        return $query->where(function($q) use ($variantId) {
            $q->where('is_variant_specific', false)
              ->orWhereJsonContains('variant_ids', $variantId);
        });
    }

    /**
     * Get variants associated with this image
     */
    public function getAssociatedVariants(): Collection
    {
        if (!$this->is_variant_specific || !$this->variant_ids) {
            return collect();
        }

        return ProductVariant::whereIn('id', $this->variant_ids)
            ->where('product_id', $this->product_id)
            ->get();
    }

    /**
     * Check if image is associated with a specific variant
     */
    public function isAssociatedWithVariant(int $variantId): bool
    {
        if (!$this->is_variant_specific) {
            return true; // General images are available for all variants
        }

        return in_array($variantId, $this->variant_ids ?? []);
    }

    /**
     * Associate image with variants
     */
    public function associateWithVariants(array $variantIds): void
    {
        $this->update([
            'variant_ids' => array_unique($variantIds),
            'is_variant_specific' => !empty($variantIds)
        ]);
    }

    /**
     * Remove variant associations
     */
    public function removeVariantAssociations(): void
    {
        $this->update([
            'variant_ids' => null,
            'is_variant_specific' => false
        ]);
    }

    /**
     * Get image dimensions as string
     */
    public function getDimensionsAttribute(): string
    {
        if ($this->width && $this->height) {
            return "{$this->width} x {$this->height}";
        }
        return 'Unknown';
    }

    /**
     * Get all available sizes for this image
     */
    public function getAvailableSizes(): array
    {
        $sizes = ['original' => $this->url];
        $pathInfo = pathinfo($this->path);
        
        $resizeProfiles = [
            'thumbnail' => [80, 80],
            'small' => [200, 200],
            'medium' => [400, 400],
            'large' => [800, 800],
            'xlarge' => [1200, 1200]
        ];

        foreach ($resizeProfiles as $sizeName => $dimensions) {
            $resizedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $sizeName . '.' . $pathInfo['extension'];
            
            if (Storage::disk('public')->exists($resizedPath)) {
                $sizes[$sizeName] = Storage::url($resizedPath);
            }
        }

        return $sizes;
    }

    /**
     * Check if image has all resize profiles
     */
    public function hasAllResizeProfiles(): bool
    {
        $pathInfo = pathinfo($this->path);
        $profiles = ['thumbnail', 'small', 'medium', 'large', 'xlarge'];
        
        foreach ($profiles as $profile) {
            $resizedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $profile . '.' . $pathInfo['extension'];
            if (!Storage::disk('public')->exists($resizedPath)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get image usage statistics
     */
    public function getUsageStats(): array
    {
        return [
            'is_cover' => $this->is_cover,
            'is_variant_specific' => $this->is_variant_specific,
            'variant_count' => $this->is_variant_specific ? count($this->variant_ids ?? []) : 0,
            'image_type' => $this->image_type,
            'has_alt_text' => !empty($this->alt_text),
            'has_description' => !empty($this->description),
            'file_size_mb' => round($this->file_size / 1024 / 1024, 2)
        ];
    }
}
