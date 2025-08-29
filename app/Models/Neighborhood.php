<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Neighborhood extends Model
{
    protected $fillable = [
        'district_id',
        'name',
        'slug',
        'neighborhood_code',
        'postal_code',
        'latitude',
        'longitude',
        'type',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'district_id' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get the district this neighborhood belongs to
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the province through district
     */
    public function province()
    {
        return $this->hasOneThrough(Province::class, District::class, 'id', 'id', 'district_id', 'province_id');
    }

    /**
     * Scope to get only active neighborhoods
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order and name
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope to filter by district
     */
    public function scopeByDistrict(Builder $query, int $districtId): Builder
    {
        return $query->where('district_id', $districtId);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to search by postal code
     */
    public function scopeByPostalCode(Builder $query, string $postalCode): Builder
    {
        return $query->where('postal_code', $postalCode);
    }

    /**
     * Get cached list of neighborhoods for a district
     */
    public static function getCachedListByDistrict(int $districtId): Collection
    {
        return Cache::remember("neighborhoods_district_{$districtId}", 24 * 60 * 60, function () use ($districtId) {
            return static::byDistrict($districtId)
                         ->active()
                         ->ordered()
                         ->select('id', 'name', 'slug', 'postal_code', 'type')
                         ->get();
        });
    }

    /**
     * Get neighborhood with district and province information
     */
    public function scopeWithLocation(Builder $query): Builder
    {
        return $query->with(['district:id,name,province_id', 'district.province:id,name,plate_code']);
    }

    /**
     * Get full address hierarchy
     */
    public function getFullAddressAttribute(): string
    {
        $district = $this->district;
        $province = $district ? $district->province : null;
        
        $parts = [$this->name];
        if ($district) {
            $parts[] = $district->name;
        }
        if ($province) {
            $parts[] = $province->name;
        }
        
        return implode(' / ', $parts);
    }

    /**
     * Get neighborhood types
     */
    public static function getTypes(): array
    {
        return [
            'mahalle' => 'Mahalle',
            'koye_bagli' => 'Köye Bağlı',
            'bucak' => 'Bucak',
            'belde' => 'Belde'
        ];
    }

    /**
     * Generate slug from name and district
     */
    public function generateSlug(): string
    {
        $district = $this->district;
        $districtName = $district ? $district->name : '';
        return \Str::slug($districtName . '-' . $this->name);
    }

    /**
     * Find by postal code
     */
    public static function findByPostalCode(string $postalCode): ?self
    {
        return static::where('postal_code', $postalCode)
                    ->active()
                    ->first();
    }

    /**
     * Search neighborhoods by name
     */
    public static function searchByName(string $query, int $districtId = null): Collection
    {
        $search = static::where('name', 'LIKE', "%{$query}%")
                        ->active();
        
        if ($districtId) {
            $search->byDistrict($districtId);
        }
        
        return $search->ordered()
                     ->limit(20)
                     ->get();
    }

    /**
     * Clear location cache
     */
    public static function clearCache(): void
    {
        Cache::forget('provinces_list');
        // Clear location caches - simplified for file cache driver
        for ($i = 1; $i <= 1000; $i++) {
            Cache::forget("districts_province_{$i}");
            Cache::forget("neighborhoods_district_{$i}");
        }
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug if not provided
        static::creating(function ($neighborhood) {
            if (empty($neighborhood->slug)) {
                $neighborhood->load('district');
                $neighborhood->slug = $neighborhood->generateSlug();
            }
        });

        // Clear cache when model changes
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }
}
