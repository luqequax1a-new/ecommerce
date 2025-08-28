<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Province extends Model
{
    protected $fillable = [
        'id', // plate code as primary key
        'name',
        'region',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // No auto-incrementing since we use plate code as ID
    public $incrementing = false;
    protected $keyType = 'int';

    /**
     * Get all districts belonging to this province
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    /**
     * Get active districts ordered by name
     */
    public function activeDistricts(): HasMany
    {
        return $this->districts()
                    ->where('is_active', true)
                    ->orderBy('name');
    }

    /**
     * Scope to get only active provinces
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
     * Scope to filter by region
     */
    public function scopeByRegion(Builder $query, string $region): Builder
    {
        return $query->where('region', $region);
    }

    /**
     * Get cached list of active provinces for dropdowns
     */
    public static function getCachedList(): Collection
    {
        return Cache::remember('provinces_list', 24 * 60 * 60, function () {
            return static::active()
                         ->ordered()
                         ->select('id', 'name')
                         ->get();
        });
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
