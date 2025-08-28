<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class TaxClass extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'default_rate',
        'is_active'
    ];

    protected $casts = [
        'default_rate' => 'decimal:4',
        'is_active' => 'boolean'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($taxClass) {
            if (empty($taxClass->code)) {
                $taxClass->code = Str::slug($taxClass->name, '_');
            }
        });
        
        static::saved(function () {
            Cache::forget('tax_classes_active');
        });
        
        static::deleted(function () {
            Cache::forget('tax_classes_active');
        });
    }

    /**
     * Tax rates relationship
     */
    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }
    
    /**
     * Active tax rates relationship
     */
    public function activeTaxRates(): HasMany
    {
        return $this->taxRates()->where('is_active', true)
                    ->where(function ($query) {
                        $query->whereNull('effective_until')
                              ->orWhere('effective_until', '>=', now()->toDateString());
                    })
                    ->where(function ($query) {
                        $query->whereNull('effective_from')
                              ->orWhere('effective_from', '<=', now()->toDateString());
                    });
    }

    /**
     * Products relationship
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope: Active tax classes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Get cached active tax classes
     */
    public static function getCachedActive()
    {
        return Cache::remember('tax_classes_active', 3600, function () {
            return static::active()->orderBy('name')->get();
        });
    }

    /**
     * Calculate tax amount using default rate
     */
    public function calculateDefaultTax(float $amount): float
    {
        return $amount * $this->default_rate;
    }
    
    /**
     * Get applicable tax rate for specific conditions
     */
    public function getApplicableTaxRate(array $conditions = []): ?TaxRate
    {
        $query = $this->activeTaxRates();
        
        if (isset($conditions['country_code'])) {
            $query->where('country_code', $conditions['country_code']);
        }
        
        if (isset($conditions['region'])) {
            $query->where('region', $conditions['region']);
        }
        
        return $query->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
    }
    
    /**
     * Calculate tax amount using applicable rate
     */
    public function calculateTax(float $amount, array $conditions = []): float
    {
        $taxRate = $this->getApplicableTaxRate($conditions);
        
        if ($taxRate) {
            return $taxRate->type === 'percentage' 
                ? $amount * $taxRate->rate 
                : $taxRate->rate;
        }
        
        return $this->calculateDefaultTax($amount);
    }

    /**
     * Get tax percentage
     */
    public function getTaxPercentageAttribute(): float
    {
        return $this->default_rate * 100;
    }
    
    /**
     * Check if this is a Turkish VAT class
     */
    public function isTurkishVAT(): bool
    {
        return Str::contains(strtolower($this->code), ['tr_', 'vat', 'kdv']);
    }
    
    /**
     * Get Turkish VAT rates (20%, 10%, 1%, 0%)
     */
    public static function getTurkishVATClasses()
    {
        return static::where('code', 'like', 'TR_VAT_%')
                    ->orWhere('code', 'like', 'TR_KDV_%')
                    ->active()
                    ->orderBy('default_rate', 'desc')
                    ->get();
    }
}
