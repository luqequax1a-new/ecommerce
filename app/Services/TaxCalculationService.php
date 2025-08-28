<?php

namespace App\Services;

use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\TaxRule;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TaxCalculationService
{
    /**
     * Calculate tax for a product
     */
    public function calculateProductTax(
        Product $product,
        float $basePrice,
        array $conditions = []
    ): array {
        $conditions = array_merge([
            'entity_type' => 'product',
            'entity_id' => $product->id,
            'country_code' => 'TR'
        ], $conditions);

        return $this->calculateTax($basePrice, $conditions, $product->taxClass);
    }

    /**
     * Calculate tax for shipping
     */
    public function calculateShippingTax(
        float $shippingCost,
        array $conditions = []
    ): array {
        $conditions = array_merge([
            'entity_type' => 'shipping',
            'country_code' => 'TR'
        ], $conditions);

        // Get default shipping tax class (usually standard VAT)
        $taxClass = TaxClass::where('code', 'TR_VAT_20')->first();
        
        return $this->calculateTax($shippingCost, $conditions, $taxClass);
    }

    /**
     * Calculate tax for payment fees
     */
    public function calculatePaymentTax(
        float $paymentFee,
        array $conditions = []
    ): array {
        $conditions = array_merge([
            'entity_type' => 'payment',
            'country_code' => 'TR'
        ], $conditions);

        // Get default payment tax class (usually standard VAT)
        $taxClass = TaxClass::where('code', 'TR_VAT_20')->first();
        
        return $this->calculateTax($paymentFee, $conditions, $taxClass);
    }

    /**
     * Calculate cart totals with taxes
     */
    public function calculateCartTotals(array $cartItems, array $conditions = []): array
    {
        $totals = [
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'tax_breakdown' => [],
            'items' => []
        ];

        foreach ($cartItems as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];
            $unitPrice = $item['price'];
            $lineTotal = $unitPrice * $quantity;

            // Calculate tax for this line item
            $taxResult = $this->calculateProductTax(
                $product, 
                $lineTotal, 
                array_merge($conditions, [
                    'order_amount' => $lineTotal,
                    'customer_type' => $conditions['customer_type'] ?? 'individual'
                ])
            );

            $itemData = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'tax_amount' => $taxResult['tax_amount'],
                'total_with_tax' => $lineTotal + $taxResult['tax_amount'],
                'tax_rate' => $taxResult['effective_rate'],
                'tax_class' => $taxResult['tax_class_name'] ?? null
            ];

            $totals['items'][] = $itemData;
            $totals['subtotal'] += $lineTotal;
            $totals['tax_amount'] += $taxResult['tax_amount'];

            // Aggregate tax breakdown
            if (isset($taxResult['tax_class_name'])) {
                $className = $taxResult['tax_class_name'];
                if (!isset($totals['tax_breakdown'][$className])) {
                    $totals['tax_breakdown'][$className] = [
                        'rate' => $taxResult['effective_rate'],
                        'taxable_amount' => 0,
                        'tax_amount' => 0
                    ];
                }
                $totals['tax_breakdown'][$className]['taxable_amount'] += $lineTotal;
                $totals['tax_breakdown'][$className]['tax_amount'] += $taxResult['tax_amount'];
            }
        }

        $totals['total'] = $totals['subtotal'] + $totals['tax_amount'];

        return $totals;
    }

    /**
     * Main tax calculation method
     */
    public function calculateTax(
        float $amount,
        array $conditions = [],
        ?TaxClass $taxClass = null
    ): array {
        if ($amount <= 0) {
            return $this->getZeroTaxResult();
        }

        // Find applicable tax rules
        $rules = TaxRule::findApplicableRules($conditions);
        
        if ($rules->isEmpty()) {
            // Fallback to tax class default rate
            if ($taxClass) {
                return $this->calculateWithTaxClass($amount, $taxClass);
            }
            
            // No applicable rules and no tax class
            return $this->getZeroTaxResult();
        }

        // Process rules by priority
        return $this->processTaxRules($amount, $rules, $conditions);
    }

    /**
     * Process tax rules with proper priority and compounding
     */
    private function processTaxRules(float $amount, Collection $rules, array $conditions): array
    {
        $totalTax = 0;
        $appliedRates = [];
        $stopProcessing = false;
        $primaryTaxClass = null;
        $currentAmount = $amount;

        foreach ($rules as $rule) {
            if ($stopProcessing) {
                break;
            }

            if (!$rule->matches($conditions)) {
                continue;
            }

            $taxRate = $rule->taxRate;
            $taxClass = $taxRate->taxClass;

            if (!$primaryTaxClass) {
                $primaryTaxClass = $taxClass;
            }

            // Calculate tax amount for this rule
            $ruleAmount = $taxRate->calculateCompoundTax($currentAmount, $taxRate->is_compound ? $totalTax : 0);
            $totalTax += $ruleAmount;

            $appliedRates[] = [
                'rule_id' => $rule->id,
                'tax_rate_id' => $taxRate->id,
                'tax_class_id' => $taxClass->id,
                'rate' => $taxRate->rate,
                'amount' => $ruleAmount,
                'description' => $rule->description
            ];

            // Update current amount for compound calculations
            if ($taxRate->is_compound) {
                $currentAmount += $ruleAmount;
            }

            if ($rule->stop_processing) {
                $stopProcessing = true;
            }
        }

        // Calculate effective rate
        $effectiveRate = $amount > 0 ? ($totalTax / $amount) : 0;

        return [
            'tax_amount' => round($totalTax, 2),
            'effective_rate' => round($effectiveRate, 4),
            'applied_rules' => $appliedRates,
            'tax_class_name' => $primaryTaxClass?->name,
            'tax_class_code' => $primaryTaxClass?->code,
            'base_amount' => $amount,
            'total_with_tax' => $amount + $totalTax
        ];
    }

    /**
     * Calculate tax using tax class default rate
     */
    private function calculateWithTaxClass(float $amount, TaxClass $taxClass): array
    {
        $taxAmount = $taxClass->calculateDefaultTax($amount);
        
        return [
            'tax_amount' => round($taxAmount, 2),
            'effective_rate' => $taxClass->default_rate,
            'applied_rules' => [],
            'tax_class_name' => $taxClass->name,
            'tax_class_code' => $taxClass->code,
            'base_amount' => $amount,
            'total_with_tax' => $amount + $taxAmount
        ];
    }

    /**
     * Get zero tax result
     */
    private function getZeroTaxResult(): array
    {
        return [
            'tax_amount' => 0.00,
            'effective_rate' => 0.0000,
            'applied_rules' => [],
            'tax_class_name' => null,
            'tax_class_code' => null,
            'base_amount' => 0,
            'total_with_tax' => 0
        ];
    }

    /**
     * Get Turkish VAT rates with caching
     */
    public function getTurkishVATRates(): Collection
    {
        return Cache::remember('turkish_vat_rates', 3600, function () {
            return TaxRate::turkish()
                         ->active()
                         ->effective()
                         ->byPriority()
                         ->with('taxClass')
                         ->get();
        });
    }

    /**
     * Validate Turkish tax number (Vergi Kimlik Numarası)
     */
    public function validateTurkishTaxNumber(string $taxNumber): bool
    {
        // Remove any non-numeric characters
        $taxNumber = preg_replace('/[^0-9]/', '', $taxNumber);
        
        // Must be exactly 10 or 11 digits
        if (!in_array(strlen($taxNumber), [10, 11])) {
            return false;
        }
        
        // 11-digit personal tax number validation
        if (strlen($taxNumber) === 11) {
            return $this->validateTurkishPersonalTaxNumber($taxNumber);
        }
        
        // 10-digit company tax number validation
        return $this->validateTurkishCompanyTaxNumber($taxNumber);
    }

    /**
     * Validate Turkish personal tax number (TC Kimlik No)
     */
    private function validateTurkishPersonalTaxNumber(string $taxNumber): bool
    {
        if (strlen($taxNumber) !== 11) {
            return false;
        }

        // First digit cannot be 0
        if ($taxNumber[0] === '0') {
            return false;
        }

        // Calculate checksum
        $sum1 = 0;
        $sum2 = 0;
        
        for ($i = 0; $i < 9; $i++) {
            if ($i % 2 === 0) {
                $sum1 += (int)$taxNumber[$i];
            } else {
                $sum2 += (int)$taxNumber[$i];
            }
        }
        
        $checksum1 = ($sum1 * 7 - $sum2) % 10;
        $checksum2 = ($sum1 + $sum2 + (int)$taxNumber[9]) % 10;
        
        return $checksum1 == (int)$taxNumber[9] && $checksum2 == (int)$taxNumber[10];
    }

    /**
     * Validate Turkish company tax number (Vergi Numarası)
     */
    private function validateTurkishCompanyTaxNumber(string $taxNumber): bool
    {
        if (strlen($taxNumber) !== 10) {
            return false;
        }

        // Calculate checksum using Luhn-like algorithm
        $sum = 0;
        $weights = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        
        for ($i = 0; $i < 9; $i++) {
            $product = (int)$taxNumber[$i] * $weights[$i];
            $sum += $product % 10 + intval($product / 10);
        }
        
        $checksum = (10 - ($sum % 10)) % 10;
        
        return $checksum == (int)$taxNumber[9];
    }

    /**
     * Format Turkish tax number for display
     */
    public function formatTurkishTaxNumber(string $taxNumber): string
    {
        $taxNumber = preg_replace('/[^0-9]/', '', $taxNumber);
        
        if (strlen($taxNumber) === 11) {
            // Format as TC: XXX XX XXX XX XX
            return substr($taxNumber, 0, 3) . ' ' . 
                   substr($taxNumber, 3, 2) . ' ' . 
                   substr($taxNumber, 5, 3) . ' ' . 
                   substr($taxNumber, 8, 2) . ' ' . 
                   substr($taxNumber, 10, 2);
        }
        
        if (strlen($taxNumber) === 10) {
            // Format as VKN: XXX XXX XX XX
            return substr($taxNumber, 0, 3) . ' ' . 
                   substr($taxNumber, 3, 3) . ' ' . 
                   substr($taxNumber, 6, 2) . ' ' . 
                   substr($taxNumber, 8, 2);
        }
        
        return $taxNumber;
    }

    /**
     * Get tax summary for display
     */
    public function getTaxSummary(array $taxResult): array
    {
        return [
            'display_rate' => number_format($taxResult['effective_rate'] * 100, 2) . '%',
            'display_amount' => '₺' . number_format($taxResult['tax_amount'], 2),
            'display_total' => '₺' . number_format($taxResult['total_with_tax'], 2),
            'tax_class' => $taxResult['tax_class_name'],
            'is_taxed' => $taxResult['tax_amount'] > 0
        ];
    }
}