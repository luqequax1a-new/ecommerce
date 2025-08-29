<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TaxRate extends Model
{
    protected $fillable = [
        'tax_class_id',
        'name',
        'code',
        'rate',
        'type',
        'country_code',
        'region',
        'is_compound',
        'priority',
        'effective_from',
        'effective_until',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'is_compound' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'metadata' => 'json'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saved(function () {
            Cache::forget('tax_rates_tr_active');
            Cache::forget('tax_rates_by_class');
        });
        
        static::deleted(function () {
            Cache::forget('tax_rates_tr_active');
            Cache::forget('tax_rates_by_class');
        });
    }

    /**
     * Tax class relationship
     */
    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    /**
     * Tax rules relationship
     */
    public function taxRules(): HasMany
    {
        return $this->hasMany(TaxRule::class);
    }

    /**
     * Scope: Active tax rates
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope: Effective tax rates (within date range)
     */
    public function scopeEffective(Builder $query, ?Carbon $date = null): Builder
    {
        $date = $date ?: now();
        $dateString = $date->toDateString();
        
        return $query->where(function ($q) use ($dateString) {
            $q->whereNull('effective_from')
              ->orWhere('effective_from', '<=', $dateString);
        })->where(function ($q) use ($dateString) {
            $q->whereNull('effective_until')
              ->orWhere('effective_until', '>=', $dateString);
        });
    }
    
    /**
     * Scope: Turkish tax rates
     */
    public function scopeTurkish(Builder $query): Builder
    {
        return $query->where('country_code', 'TR');
    }
    
    /**
     * Scope: By priority (highest first)
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get cached Turkish VAT rates
     */
    public static function getCachedTurkishRates()
    {
        return Cache::remember('tax_rates_tr_active', 3600, function () {
            return static::active()
                        ->effective()
                        ->turkish()
                        ->byPriority()
                        ->with('taxClass')
                        ->get();
        });
    }
    
    /**
     * Get rates by tax class ID
     */
    public static function getCachedByClass(int $taxClassId)
    {
        $cacheKey = "tax_rates_class_{$taxClassId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($taxClassId) {
            return static::where('tax_class_id', $taxClassId)
                        ->active()
                        ->effective()
                        ->byPriority()
                        ->get();
        });
    }

    /**
     * Calculate tax amount
     */
    public function calculateTax(float $amount): float
    {
        if ($this->type === 'percentage') {
            return $amount * $this->rate;
        }
        
        return $this->rate; // Fixed amount
    }
    
    /**
     * Calculate tax amount including previous tax if compound
     */
    public function calculateCompoundTax(float $baseAmount, float $previousTax = 0): float
    {
        $taxableAmount = $this->is_compound ? ($baseAmount + $previousTax) : $baseAmount;
        return $this->calculateTax($taxableAmount);
    }

    /**
     * Get tax percentage for display
     */
    public function getTaxPercentageAttribute(): float
    {
        return $this->type === 'percentage' ? ($this->rate * 100) : 0;
    }
    
    /**
     * Get formatted rate for display
     */
    public function getFormattedRateAttribute(): string
    {
        if ($this->type === 'percentage') {
            return number_format($this->rate * 100, 2) . '%';
        }
        
        return '₺' . number_format($this->rate, 2);
    }
    
    /**
     * Check if rate is currently effective
     */
    public function isEffective(?Carbon $date = null): bool
    {
        $date = $date ?: now();
        $dateString = $date->toDateString();
        
        if ($this->effective_from && $this->effective_from > $dateString) {
            return false;
        }
        
        if ($this->effective_until && $this->effective_until < $dateString) {
            return false;
        }
        
        return $this->is_active;
    }
    
    /**
     * Get Turkish VAT standard rates
     */
    public static function getTurkishVATRates()
    {
        return [
            ['rate' => 0.20, 'name' => 'Standard VAT 20%', 'code' => 'TR_VAT_20'],
            ['rate' => 0.10, 'name' => 'Reduced VAT 10%', 'code' => 'TR_VAT_10'],
            ['rate' => 0.01, 'name' => 'Super Reduced VAT 1%', 'code' => 'TR_VAT_1'],
            ['rate' => 0.00, 'name' => 'Exempt VAT 0%', 'code' => 'TR_VAT_0']
        ];
    }
    
    /**
     * Create Turkish VAT rates for a tax class
     */
    public static function createTurkishVATRates(TaxClass $taxClass)
    {
        $rates = static::getTurkishVATRates();
        $createdRates = [];
        
        foreach ($rates as $rateData) {
            $createdRates[] = static::create([
                'tax_class_id' => $taxClass->id,
                'name' => $rateData['name'],
                'code' => $rateData['code'],
                'rate' => $rateData['rate'],
                'type' => 'percentage',
                'country_code' => 'TR',
                'priority' => $rateData['rate'] == 0.20 ? 10 : ($rateData['rate'] * 100),
                'is_active' => true
            ]);
        }
        
        return $createdRates;
    }
}
