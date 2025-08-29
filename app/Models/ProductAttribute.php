<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductAttribute extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_required',
        'is_variation',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_variation' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Attribute values relationship
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /**
     * Active attribute values
     */
    public function activeValues(): HasMany
    {
        return $this->values()->active()->ordered();
    }

    /**
     * Scope: Active attributes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Variation attributes
     */
    public function scopeVariation($query)
    {
        return $query->where('is_variation', true);
    }

    /**
     * Scope: Ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Auto-generate slug from name
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
        
        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }
}
