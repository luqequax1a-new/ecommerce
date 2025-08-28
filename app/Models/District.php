<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class District extends Model
{
    protected $fillable = [
        'province_id',
        'name',
        'is_active'
    ];

    protected $casts = [
        'province_id' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the province this district belongs to
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Scope to get only active districts
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by name
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    /**
     * Scope to filter by province
     */
    public function scopeByProvince(Builder $query, int $provinceId): Builder
    {
        return $query->where('province_id', $provinceId);
    }

    /**
     * Get cached list of districts for a province
     */
    public static function getCachedListByProvince(int $provinceId): Collection
    {
        return Cache::remember("districts_province_{$provinceId}", 24 * 60 * 60, function () use ($provinceId) {
            return static::byProvince($provinceId)
                         ->active()
                         ->ordered()
                         ->select('id', 'name')
                         ->get();
        });
    }

    /**
     * Get district with province information
     */
    public function scopeWithProvince(Builder $query): Builder
    {
        return $query->with('province:id,name');
    }
    
    /**
     * Clear location cache
     */
    public static function clearCache(): void
    {
        Cache::forget('provinces_list');
        // Clear all district cache keys that might exist
        for ($i = 1; $i <= 81; $i++) {
            Cache::forget("districts_province_{$i}");
        }
    }
    
    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();
        
        // Clear cache when model changes
        static::saved(function () {
            static::clearCache();
        });
        
        static::deleted(function () {
            static::clearCache();
        });
    }

}
