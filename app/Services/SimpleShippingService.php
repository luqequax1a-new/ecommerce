<?php

namespace App\Services;

use App\Models\ShippingSettings;
use Illuminate\Support\Facades\Cache;

/**
 * Simple Shipping Service for Turkish E-commerce
 * 
 * Features:
 * - Flat rate shipping
 * - Free shipping threshold
 * - Cash on Delivery (COD) extra fee
 * - Turkish localization
 */
class SimpleShippingService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = ShippingSettings::current();
    }

    /**
     * Calculate total shipping cost including COD fee
     */
    public function calculateTotal(float $subtotal, bool $useCOD = false): array
    {
        $shippingCost = $this->calculateShippingCost($subtotal);
        $codFee = $useCOD ? $this->getCODFee() : 0.00;
        
        return [
            'shipping_cost' => $shippingCost,
            'cod_fee' => $codFee,
            'total_shipping' => $shippingCost + $codFee,
            'is_free_shipping' => $shippingCost === 0.00,
            'is_cod_available' => $this->isCODAvailable(),
            'currency' => 'TRY',
        ];
    }

    /**
     * Calculate shipping cost based on subtotal
     */
    public function calculateShippingCost(float $subtotal): float
    {
        // Check if free shipping threshold is met
        if ($this->settings->free_enabled && $subtotal >= $this->settings->free_threshold) {
            return 0.00;
        }
        
        // Return flat rate if enabled
        if ($this->settings->flat_rate_enabled) {
            return $this->settings->flat_rate_fee;
        }
        
        return 0.00;
    }

    /**
     * Get remaining amount for free shipping
     */
    public function getRemainingForFreeShipping(float $subtotal): float
    {
        return $this->settings->getRemainingForFreeShipping($subtotal);
    }

    /**
     * Get free shipping message with Turkish formatting
     */
    public function getFreeShippingMessage(float $subtotal): ?string
    {
        return $this->settings->getFreeShippingMessage($subtotal);
    }

    /**
     * Get COD extra fee
     */
    public function getCODFee(): float
    {
        return $this->settings->getCODFee();
    }

    /**
     * Check if COD is available
     */
    public function isCODAvailable(): bool
    {
        return $this->settings->isCODAvailable();
    }

    /**
     * Format currency amount with Turkish locale
     */
    public function formatCurrency(float $amount): string
    {
        return $this->settings->formatCurrency($amount);
    }

    /**
     * Get shipping breakdown for display
     */
    public function getShippingBreakdown(float $subtotal, bool $useCOD = false): array
    {
        $breakdown = $this->calculateTotal($subtotal, $useCOD);
        
        $items = [];
        
        // Shipping cost breakdown
        if ($breakdown['shipping_cost'] > 0) {
            $items[] = [
                'label' => 'Kargo Ücreti',
                'amount' => $breakdown['shipping_cost'],
                'formatted' => $this->formatCurrency($breakdown['shipping_cost']),
                'type' => 'shipping'
            ];
        } else if ($breakdown['is_free_shipping']) {
            $items[] = [
                'label' => 'Kargo Ücreti',
                'amount' => 0.00,
                'formatted' => 'Ücretsiz',
                'type' => 'shipping_free'
            ];
        }
        
        // COD fee breakdown
        if ($breakdown['cod_fee'] > 0) {
            $items[] = [
                'label' => 'Kapıda Ödeme Ücreti',
                'amount' => $breakdown['cod_fee'],
                'formatted' => $this->formatCurrency($breakdown['cod_fee']),
                'type' => 'cod_fee'
            ];
        }
        
        return [
            'items' => $items,
            'total' => $breakdown['total_shipping'],
            'total_formatted' => $this->formatCurrency($breakdown['total_shipping']),
            'summary' => $this->getShippingSummary($subtotal, $useCOD)
        ];
    }

    /**
     * Get shipping summary text
     */
    public function getShippingSummary(float $subtotal, bool $useCOD = false): string
    {
        $breakdown = $this->calculateTotal($subtotal, $useCOD);
        
        if ($breakdown['is_free_shipping'] && !$useCOD) {
            return 'Kargo ücretsiz';
        }
        
        if ($breakdown['is_free_shipping'] && $useCOD) {
            return 'Kargo ücretsiz, kapıda ödeme ücreti: ' . $this->formatCurrency($breakdown['cod_fee']);
        }
        
        if ($useCOD) {
            return sprintf(
                'Kargo: %s + Kapıda ödeme: %s = %s',
                $this->formatCurrency($breakdown['shipping_cost']),
                $this->formatCurrency($breakdown['cod_fee']),
                $this->formatCurrency($breakdown['total_shipping'])
            );
        }
        
        return 'Kargo ücreti: ' . $this->formatCurrency($breakdown['shipping_cost']);
    }

    /**
     * Check if shipping is required for cart
     */
    public function isShippingRequired(array $cartItems = []): bool
    {
        // In this simplified system, shipping is always required unless all items are virtual
        // This can be enhanced later to check for virtual/digital products
        return true;
    }

    /**
     * Get available payment methods based on shipping
     */
    public function getAvailablePaymentMethods(): array
    {
        $methods = [
            [
                'code' => 'credit_card',
                'name' => 'Kredi Kartı',
                'description' => 'Visa, MasterCard ile güvenli ödeme',
                'extra_fee' => 0.00,
                'icon' => 'credit-card'
            ],
            [
                'code' => 'bank_transfer',
                'name' => 'Havale/EFT',
                'description' => 'Banka havalesi ile ödeme',
                'extra_fee' => 0.00,
                'icon' => 'bank'
            ]
        ];
        
        // Add COD if available
        if ($this->isCODAvailable()) {
            $methods[] = [
                'code' => 'cod',
                'name' => 'Kapıda Ödeme',
                'description' => 'Teslimat sırasında nakit ödeme',
                'extra_fee' => $this->getCODFee(),
                'extra_fee_formatted' => $this->formatCurrency($this->getCODFee()),
                'icon' => 'cash'
            ];
        }
        
        return $methods;
    }

    /**
     * Validate shipping configuration
     */
    public function validateConfiguration(): array
    {
        $errors = [];
        
        if ($this->settings->free_enabled && $this->settings->free_threshold <= 0) {
            $errors[] = 'Ücretsiz kargo eşiği 0\'dan büyük olmalıdır';
        }
        
        if ($this->settings->flat_rate_enabled && $this->settings->flat_rate_fee < 0) {
            $errors[] = 'Sabit kargo ücreti negatif olamaz';
        }
        
        if ($this->settings->cod_enabled && $this->settings->cod_extra_fee < 0) {
            $errors[] = 'Kapıda ödeme ücreti negatif olamaz';
        }
        
        if (!$this->settings->flat_rate_enabled && !$this->settings->free_enabled) {
            $errors[] = 'En az bir kargo seçeneği aktif olmalıdır';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get shipping configuration for frontend
     */
    public function getConfiguration(): array
    {
        return [
            'free_shipping' => [
                'enabled' => $this->settings->free_enabled,
                'threshold' => $this->settings->free_threshold,
                'threshold_formatted' => $this->formatCurrency($this->settings->free_threshold),
                'message' => $this->settings->free_shipping_message,
            ],
            'flat_rate' => [
                'enabled' => $this->settings->flat_rate_enabled,
                'fee' => $this->settings->flat_rate_fee,
                'fee_formatted' => $this->formatCurrency($this->settings->flat_rate_fee),
            ],
            'cod' => [
                'enabled' => $this->settings->cod_enabled,
                'fee' => $this->settings->cod_extra_fee,
                'fee_formatted' => $this->formatCurrency($this->settings->cod_extra_fee),
            ],
            'currency' => $this->settings->currency,
            'description' => $this->settings->shipping_description,
        ];
    }

    /**
     * Calculate cart recommendations
     */
    public function getCartRecommendations(float $subtotal): array
    {
        $recommendations = [];
        
        $remaining = $this->getRemainingForFreeShipping($subtotal);
        
        if ($remaining > 0) {
            $recommendations[] = [
                'type' => 'free_shipping',
                'message' => $this->getFreeShippingMessage($subtotal),
                'amount_needed' => $remaining,
                'amount_formatted' => $this->formatCurrency($remaining),
                'priority' => 'high'
            ];
        }
        
        return $recommendations;
    }

    /**
     * Refresh settings cache
     */
    public function refreshSettings(): void
    {
        Cache::forget('shipping_settings_current');
        $this->settings = ShippingSettings::current();
    }

    /**
     * Get shipping statistics for admin dashboard
     */
    public function getShippingStatistics(): array
    {
        // This would typically query order data
        // For now, return configuration-based stats
        return [
            'free_shipping_enabled' => $this->settings->free_enabled,
            'free_shipping_threshold' => $this->settings->free_threshold,
            'flat_rate_fee' => $this->settings->flat_rate_fee,
            'cod_enabled' => $this->settings->cod_enabled,
            'cod_fee' => $this->settings->cod_extra_fee,
            'configuration_valid' => $this->validateConfiguration()['valid']
        ];
    }
}