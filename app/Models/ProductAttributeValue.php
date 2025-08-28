<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductAttributeValue extends Model
{
    protected $fillable = [
        'product_attribute_id',
        'value',
        'slug',
        'color_code',
        'image_path',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Attribute relationship
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    /**
     * Scope: Active values
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('value');
    }

    /**
     * Get display value (considers type and formatting)
     */
    public function getDisplayValueAttribute(): string
    {
        return $this->value;
    }

    /**
     * Check if this is a color attribute
     */
    public function isColor(): bool
    {
        return $this->attribute->type === 'color';
    }

    /**
     * Check if this is an image attribute
     */
    public function isImage(): bool
    {
        return $this->attribute->type === 'image';
    }

    /**
     * Get the image URL for image-type attributes
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->isImage() && $this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    /**
     * Auto-generate slug from value
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->value);
            }
        });
        
        static::updating(function ($model) {
            if ($model->isDirty('value') && empty($model->slug)) {
                $model->slug = Str::slug($model->value);
            }
        });
    }
}
