<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TaxRule extends Model
{
    protected $fillable = [
        'tax_rate_id',
        'entity_type',
        'entity_id',
        'country_code',
        'region',
        'postal_code_from',
        'postal_code_to',
        'customer_group_id',
        'customer_type',
        'order_amount_from',
        'order_amount_to',
        'priority',
        'stop_processing',
        'date_from',
        'date_to',
        'is_active',
        'conditions',
        'description'
    ];

    protected $casts = [
        'entity_id' => 'integer',
        'customer_group_id' => 'integer',
        'order_amount_from' => 'decimal:2',
        'order_amount_to' => 'decimal:2',
        'priority' => 'integer',
        'stop_processing' => 'boolean',
        'date_from' => 'date',
        'date_to' => 'date',
        'is_active' => 'boolean',
        'conditions' => 'json'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saved(function () {
            Cache::forget('tax_rules_active');
            Cache::forget('tax_rules_by_entity');
        });
        
        static::deleted(function () {
            Cache::forget('tax_rules_active');
            Cache::forget('tax_rules_by_entity');
        });
    }

    /**
     * Tax rate relationship
     */
    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    /**
     * Get the related entity (polymorphic)
     */
    public function entity()
    {
        switch ($this->entity_type) {
            case 'product':
                return $this->belongsTo(Product::class, 'entity_id');
            case 'category':
                return $this->belongsTo(Category::class, 'entity_id');
            case 'customer':
                return $this->belongsTo(User::class, 'entity_id');
            default:
                return null;
        }
    }

    /**
     * Scope: Active tax rules
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope: Effective tax rules (within date range)
     */
    public function scopeEffective(Builder $query, ?Carbon $date = null): Builder
    {
        $date = $date ?: now();
        $dateString = $date->toDateString();
        
        return $query->where(function ($q) use ($dateString) {
            $q->whereNull('date_from')
              ->orWhere('date_from', '<=', $dateString);
        })->where(function ($q) use ($dateString) {
            $q->whereNull('date_to')
              ->orWhere('date_to', '>=', $dateString);
        });
    }
    
    /**
     * Scope: By entity type
     */
    public function scopeForEntity(Builder $query, string $entityType, ?int $entityId = null): Builder
    {
        $query->where('entity_type', $entityType);
        
        if ($entityId !== null) {
            $query->where('entity_id', $entityId);
        }
        
        return $query;
    }
    
    /**
     * Scope: By country
     */
    public function scopeForCountry(Builder $query, string $countryCode): Builder
    {
        return $query->where('country_code', $countryCode);
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
     * Find applicable tax rules for given conditions
     */
    public static function findApplicableRules(array $conditions = [])
    {
        $query = static::active()->effective()->byPriority();
        
        // Entity-based filtering
        if (isset($conditions['entity_type'])) {
            $query->where(function ($q) use ($conditions) {
                $q->where('entity_type', $conditions['entity_type']);
                if (isset($conditions['entity_id'])) {
                    $q->where(function ($subQ) use ($conditions) {
                        $subQ->whereNull('entity_id')
                             ->orWhere('entity_id', $conditions['entity_id']);
                    });
                }
            });
        }
        
        // Geographic filtering
        if (isset($conditions['country_code'])) {
            $query->where('country_code', $conditions['country_code']);
        }
        
        if (isset($conditions['region'])) {
            $query->where(function ($q) use ($conditions) {
                $q->whereNull('region')
                  ->orWhere('region', $conditions['region']);
            });
        }
        
        // Postal code filtering
        if (isset($conditions['postal_code'])) {
            $postalCode = $conditions['postal_code'];
            $query->where(function ($q) use ($postalCode) {
                $q->where(function ($subQ) use ($postalCode) {
                    $subQ->whereNull('postal_code_from')
                         ->whereNull('postal_code_to');
                })->orWhere(function ($subQ) use ($postalCode) {
                    $subQ->where('postal_code_from', '<=', $postalCode)
                         ->where('postal_code_to', '>=', $postalCode);
                });
            });
        }
        
        // Customer type filtering
        if (isset($conditions['customer_type'])) {
            $query->where(function ($q) use ($conditions) {
                $q->whereNull('customer_type')
                  ->orWhere('customer_type', $conditions['customer_type']);
            });
        }
        
        // Order amount filtering
        if (isset($conditions['order_amount'])) {
            $amount = $conditions['order_amount'];
            $query->where(function ($q) use ($amount) {
                $q->where(function ($subQ) use ($amount) {
                    $subQ->whereNull('order_amount_from')
                         ->whereNull('order_amount_to');
                })->orWhere(function ($subQ) use ($amount) {
                    $subQ->where(function ($innerQ) use ($amount) {
                        $innerQ->whereNull('order_amount_from')
                               ->orWhere('order_amount_from', '<=', $amount);
                    })->where(function ($innerQ) use ($amount) {
                        $innerQ->whereNull('order_amount_to')
                               ->orWhere('order_amount_to', '>=', $amount);
                    });
                });
            });
        }
        
        // Filter out export rules unless explicitly exporting
        if (!isset($conditions['is_export']) || !$conditions['is_export']) {
            $query->where(function ($q) {
                $q->whereNull('conditions')
                  ->orWhereRaw('JSON_EXTRACT(conditions, "$.is_export") IS NULL')
                  ->orWhereRaw('JSON_EXTRACT(conditions, "$.is_export") = false');
            });
        }
        
        return $query->with('taxRate.taxClass')->get();
    }
    
    /**
     * Check if rule matches given conditions
     */
    public function matches(array $conditions = []): bool
    {
        // Check if rule is active and effective
        if (!$this->is_active || !$this->isEffective()) {
            return false;
        }
        
        // Check entity type and ID
        if (isset($conditions['entity_type']) && $this->entity_type !== $conditions['entity_type']) {
            return false;
        }
        
        if (isset($conditions['entity_id']) && $this->entity_id && $this->entity_id != $conditions['entity_id']) {
            return false;
        }
        
        // Check geographic constraints
        if (isset($conditions['country_code']) && $this->country_code !== $conditions['country_code']) {
            return false;
        }
        
        if (isset($conditions['region']) && $this->region && $this->region !== $conditions['region']) {
            return false;
        }
        
        // Check postal code range
        if (isset($conditions['postal_code']) && ($this->postal_code_from || $this->postal_code_to)) {
            $postalCode = $conditions['postal_code'];
            if ($this->postal_code_from && $postalCode < $this->postal_code_from) {
                return false;
            }
            if ($this->postal_code_to && $postalCode > $this->postal_code_to) {
                return false;
            }
        }
        
        // Check customer type
        if (isset($conditions['customer_type']) && $this->customer_type && $this->customer_type !== $conditions['customer_type']) {
            return false;
        }
        
        // Check order amount range
        if (isset($conditions['order_amount'])) {
            $amount = $conditions['order_amount'];
            if ($this->order_amount_from && $amount < $this->order_amount_from) {
                return false;
            }
            if ($this->order_amount_to && $amount > $this->order_amount_to) {
                return false;
            }
        }
        
        // Check custom conditions
        if ($this->conditions && isset($conditions['custom'])) {
            foreach ($this->conditions as $key => $value) {
                if (isset($conditions['custom'][$key]) && $conditions['custom'][$key] != $value) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Check if rule is currently effective
     */
    public function isEffective(?Carbon $date = null): bool
    {
        $date = $date ?: now();
        $dateString = $date->toDateString();
        
        if ($this->date_from && $this->date_from > $dateString) {
            return false;
        }
        
        if ($this->date_to && $this->date_to < $dateString) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get rule description or generate one
     */
    public function getDescriptionAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        
        $parts = [];
        
        if ($this->entity_type) {
            $parts[] = ucfirst($this->entity_type);
            if ($this->entity_id) {
                $parts[] = "ID: {$this->entity_id}";
            }
        }
        
        if ($this->country_code) {
            $parts[] = "Country: {$this->country_code}";
        }
        
        if ($this->customer_type) {
            $parts[] = "Customer: {$this->customer_type}";
        }
        
        if ($this->order_amount_from || $this->order_amount_to) {
            $from = $this->order_amount_from ? "₺{$this->order_amount_from}" : '0';
            $to = $this->order_amount_to ? "₺{$this->order_amount_to}" : '∞';
            $parts[] = "Amount: {$from} - {$to}";
        }
        
        return implode(', ', $parts) ?: 'General Rule';
    }
    
    /**
     * Create standard Turkish VAT rules
     */
    public static function createTurkishVATRules()
    {
        $rules = [
            [
                'entity_type' => 'product',
                'country_code' => 'TR',
                'customer_type' => 'individual',
                'priority' => 10,
                'description' => 'Standard Turkish VAT for individual customers'
            ],
            [
                'entity_type' => 'product',
                'country_code' => 'TR',
                'customer_type' => 'company',
                'priority' => 10,
                'description' => 'Standard Turkish VAT for company customers'
            ],
            [
                'entity_type' => 'shipping',
                'country_code' => 'TR',
                'priority' => 8,
                'description' => 'Turkish VAT for shipping costs'
            ],
            [
                'entity_type' => 'payment',
                'country_code' => 'TR',
                'priority' => 5,
                'description' => 'Turkish VAT for payment fees'
            ]
        ];
        
        return $rules;
    }
}
