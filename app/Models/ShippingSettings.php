<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ShippingSettings extends Model
{
    protected $fillable = [
        'free_enabled',
        'free_threshold',
        'flat_rate_enabled',
        'flat_rate_fee',
        'cod_enabled',
        'cod_extra_fee',
        'currency',
        'free_shipping_message',
        'shipping_description',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'free_enabled' => 'boolean',
        'free_threshold' => 'decimal:2',
        'flat_rate_enabled' => 'boolean',
        'flat_rate_fee' => 'decimal:2',
        'cod_enabled' => 'boolean',
        'cod_extra_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the current active shipping settings (singleton pattern)
     */
    public static function current(): self
    {
        return Cache::remember('shipping_settings_current', 3600, function () {
            $settings = self::where('is_active', true)->first();
            
            // Create default settings if none exist
            if (!$settings) {
                $settings = self::create([
                    'free_enabled' => true,
                    'free_threshold' => 300.00,
                    'flat_rate_enabled' => true,
                    'flat_rate_fee' => 15.00,
                    'cod_enabled' => true,
                    'cod_extra_fee' => 5.00,
                    'currency' => 'TRY',
                    'free_shipping_message' => 'Kargo ücretsiz için ₺{remaining} daha alışveriş yapın.',
                    'shipping_description' => 'Standart kargo hızlı teslimat',
                    'is_active' => true,
                ]);
            }
            
            return $settings;
        });
    }

    /**
     * Update settings and clear cache
     */
    public function updateSettings(array $data): bool
    {
        DB::beginTransaction();
        
        try {
            // Deactivate other settings if this one is being activated
            if (isset($data['is_active']) && $data['is_active']) {
                self::where('id', '!=', $this->id)->update(['is_active' => false]);
            }
            
            $updated = $this->update($data);
            
            DB::commit();
            
            // Clear cache
            $this->clearCache();
            
            return $updated;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Calculate shipping cost based on cart subtotal
     */
    public function calculateShippingCost(float $subtotal): float
    {
        // Check if free shipping threshold is met
        if ($this->free_enabled && $subtotal >= $this->free_threshold) {
            return 0.00;
        }
        
        // Return flat rate if enabled
        if ($this->flat_rate_enabled) {
            return $this->flat_rate_fee;
        }
        
        return 0.00;
    }

    /**
     * Calculate remaining amount for free shipping
     */
    public function getRemainingForFreeShipping(float $subtotal): float
    {
        if (!$this->free_enabled || $subtotal >= $this->free_threshold) {
            return 0.00;
        }
        
        return max(0, $this->free_threshold - $subtotal);
    }

    /**
     * Get free shipping message with dynamic content
     */
    public function getFreeShippingMessage(float $subtotal): ?string
    {
        if (!$this->free_enabled || $this->free_threshold <= 0) {
            return null;
        }
        
        $remaining = $this->getRemainingForFreeShipping($subtotal);
        
        if ($remaining > 0) {
            $message = $this->free_shipping_message ?: 'Kargo ücretsiz için ₺{remaining} daha alışveriş yapın.';
            return str_replace('{remaining}', number_format($remaining, 2, ',', '.'), $message);
        }
        
        return 'Kargo ücretsiz.';
    }

    /**
     * Calculate COD fee if applicable
     */
    public function getCODFee(): float
    {
        return $this->cod_enabled ? $this->cod_extra_fee : 0.00;
    }

    /**
     * Check if COD is available
     */
    public function isCODAvailable(): bool
    {
        return $this->cod_enabled && $this->is_active;
    }

    /**
     * Format currency amount with Turkish locale
     */
    public function formatCurrency(float $amount): string
    {
        return '₺' . number_format($amount, 2, ',', '.');
    }

    /**
     * Get validation rules for admin form
     */
    public static function getValidationRules(): array
    {
        return [
            'free_enabled' => 'boolean',
            'free_threshold' => 'nullable|numeric|min:0|max:999999.99',
            'flat_rate_enabled' => 'boolean',
            'flat_rate_fee' => 'nullable|numeric|min:0|max:9999.99',
            'cod_enabled' => 'boolean',
            'cod_extra_fee' => 'nullable|numeric|min:0|max:999.99',
            'currency' => 'string|in:TRY',
            'free_shipping_message' => 'nullable|string|max:500',
            'shipping_description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get Turkish field labels for admin interface
     */
    public static function getFieldLabels(): array
    {
        return [
            'free_enabled' => 'Ücretsiz Kargo Etkin',
            'free_threshold' => 'Ücretsiz Kargo Eşiği (₺)',
            'flat_rate_enabled' => 'Sabit Ücret Etkin',
            'flat_rate_fee' => 'Sabit Kargo Ücreti (₺)',
            'cod_enabled' => 'Kapıda Ödeme Etkin',
            'cod_extra_fee' => 'Kapıda Ödeme Ekstra Ücreti (₺)',
            'currency' => 'Para Birimi',
            'free_shipping_message' => 'Ücretsiz Kargo Mesajı',
            'shipping_description' => 'Kargo Açıklaması',
            'is_active' => 'Aktif',
        ];
    }

    /**
     * Clear related caches
     */
    public function clearCache(): void
    {
        Cache::forget('shipping_settings_current');
        // Remove cache tagging for compatibility
        Cache::forget('shipping_configuration');
        Cache::forget('shipping_statistics');
    }

    /**
     * Model events
     */
    protected static function booted()
    {
        static::saved(function ($settings) {
            $settings->clearCache();
        });
        
        static::deleted(function ($settings) {
            $settings->clearCache();
        });
    }
}
