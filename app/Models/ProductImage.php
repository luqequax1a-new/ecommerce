<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'original_filename',
        'path',
        'alt_text',
        'sort_order',
        'is_cover',
        'width',
        'height',
        'file_size',
        'mime_type'
    ];

    protected $casts = [
        'is_cover' => 'boolean',
        'sort_order' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'integer'
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
}
