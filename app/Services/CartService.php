<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Services\TaxCalculationService;
use App\Services\ShippingCalculationService;
use Illuminate\Support\Collection;

class CartService
{
    protected $taxService;
    protected $shippingService;

    public function __construct(
        TaxCalculationService $taxService,
        ShippingCalculationService $shippingService
    ) {
        $this->taxService = $taxService;
        $this->shippingService = $shippingService;
    }

    /**
     * Calculate complete cart totals with taxes
     */
    public function calculateCartTotals(array $cartItems, array $options = []): array
    {
        $options = array_merge([
            'customer_type' => 'individual',
            'country_code' => 'TR',
            'shipping_method_id' => null,
            'payment_method' => null,
            'discount_amount' => 0,
            'coupon_code' => null,
            'include_shipping' => true,
            'include_payment_fees' => true
        ], $options);

        $totals = [
            'items' => [],
            'subtotal' => 0,
            'discount_amount' => $options['discount_amount'],
            'subtotal_after_discount' => 0,
            'shipping_cost' => 0,
            'shipping_tax' => 0,
            'payment_fee' => 0,
            'payment_tax' => 0,
            'total_tax' => 0,
            'total' => 0,
            'tax_breakdown' => [],
            'summary' => []
        ];

        // Process cart items
        foreach ($cartItems as $item) {
            $itemData = $this->processCartItem($item, $options);
            $totals['items'][] = $itemData;
            $totals['subtotal'] += $itemData['line_total'];
            $totals['total_tax'] += $itemData['tax_amount'];

            // Aggregate tax breakdown
            $this->aggregateTaxBreakdown($totals['tax_breakdown'], $itemData);
        }

        // Apply discount
        $totals['subtotal_after_discount'] = max(0, $totals['subtotal'] - $totals['discount_amount']);

        // Calculate shipping if requested
        if ($options['include_shipping'] && $options['shipping_method_id']) {
            $shippingData = $this->calculateShipping($options);
            $totals['shipping_cost'] = $shippingData['cost'];
            $totals['shipping_tax'] = $shippingData['tax_amount'];
            $totals['total_tax'] += $shippingData['tax_amount'];

            // Add shipping tax to breakdown
            if ($shippingData['tax_amount'] > 0) {
                $this->aggregateTaxBreakdown($totals['tax_breakdown'], [
                    'tax_class_name' => $shippingData['tax_class_name'],
                    'tax_rate' => $shippingData['tax_rate'],
                    'taxable_amount' => $shippingData['cost'],
                    'tax_amount' => $shippingData['tax_amount']
                ]);
            }
        }

        // Calculate payment fees if requested
        if ($options['include_payment_fees'] && $options['payment_method']) {
            $paymentData = $this->calculatePaymentFee($options, $totals['subtotal_after_discount']);
            $totals['payment_fee'] = $paymentData['fee'];
            $totals['payment_tax'] = $paymentData['tax_amount'];
            $totals['total_tax'] += $paymentData['tax_amount'];

            // Add payment tax to breakdown
            if ($paymentData['tax_amount'] > 0) {
                $this->aggregateTaxBreakdown($totals['tax_breakdown'], [
                    'tax_class_name' => $paymentData['tax_class_name'],
                    'tax_rate' => $paymentData['tax_rate'],
                    'taxable_amount' => $paymentData['fee'],
                    'tax_amount' => $paymentData['tax_amount']
                ]);
            }
        }

        // Calculate final total
        $totals['total'] = $totals['subtotal_after_discount'] + 
                          $totals['shipping_cost'] + 
                          $totals['shipping_tax'] + 
                          $totals['payment_fee'] + 
                          $totals['payment_tax'] + 
                          $totals['total_tax'];

        // Generate summary
        $totals['summary'] = $this->generateCartSummary($totals);

        return $totals;
    }

    /**
     * Process individual cart item
     */
    protected function processCartItem(array $item, array $options): array
    {
        $product = $item['product'];
        $variant = $item['variant'] ?? null;
        $quantity = $item['quantity'];
        
        // Determine price and product for tax calculation
        if ($variant) {
            $unitPrice = $variant->price;
            $taxableProduct = $product; // Tax class comes from product
            $sku = $variant->sku;
            $name = $variant->display_name;
        } else {
            $unitPrice = $product->price;
            $taxableProduct = $product;
            $sku = $product->sku;
            $name = $product->name;
        }

        $lineTotal = $unitPrice * $quantity;

        // Calculate tax for this line item
        $taxConditions = [
            'entity_type' => 'product',
            'entity_id' => $product->id,
            'country_code' => $options['country_code'],
            'customer_type' => $options['customer_type'],
            'order_amount' => $lineTotal
        ];

        $taxResult = $this->taxService->calculateProductTax($taxableProduct, $lineTotal, $taxConditions);

        return [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'sku' => $sku,
            'name' => $name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'tax_amount' => $taxResult['tax_amount'],
            'tax_rate' => $taxResult['effective_rate'],
            'tax_class_name' => $taxResult['tax_class_name'],
            'total_with_tax' => $lineTotal + $taxResult['tax_amount'],
            'formatted' => [
                'unit_price' => '₺' . number_format($unitPrice, 2),
                'line_total' => '₺' . number_format($lineTotal, 2),
                'tax_amount' => '₺' . number_format($taxResult['tax_amount'], 2),
                'total_with_tax' => '₺' . number_format($lineTotal + $taxResult['tax_amount'], 2)
            ]
        ];
    }

    /**
     * Calculate shipping cost and tax
     */
    protected function calculateShipping(array $options): array
    {
        $shippingCost = 0;
        $taxAmount = 0;
        $taxRate = 0;
        $taxClassName = null;

        // Use shipping service to calculate base cost
        if (isset($options['shipping_method_id'])) {
            $shippingMethod = ShippingMethod::find($options['shipping_method_id']);
            if ($shippingMethod) {
                // This would use the ShippingCalculationService
                $shippingCost = $this->shippingService->calculateShippingCost($shippingMethod, $options);
            }
        }

        // Calculate tax on shipping if there's a cost
        if ($shippingCost > 0) {
            $taxConditions = [
                'entity_type' => 'shipping',
                'country_code' => $options['country_code'],
                'customer_type' => $options['customer_type']
            ];

            $taxResult = $this->taxService->calculateShippingTax($shippingCost, $taxConditions);
            $taxAmount = $taxResult['tax_amount'];
            $taxRate = $taxResult['effective_rate'];
            $taxClassName = $taxResult['tax_class_name'];
        }

        return [
            'cost' => $shippingCost,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'tax_class_name' => $taxClassName,
            'total_with_tax' => $shippingCost + $taxAmount
        ];
    }

    /**
     * Calculate payment fee and tax
     */
    protected function calculatePaymentFee(array $options, float $orderSubtotal): array
    {
        $paymentFee = 0;
        $taxAmount = 0;
        $taxRate = 0;
        $taxClassName = null;

        // Calculate payment fee based on method
        if ($options['payment_method'] === 'cod') {
            // COD fee calculation - could be percentage or fixed
            $paymentFee = $this->calculateCODFee($orderSubtotal);
        } elseif ($options['payment_method'] === 'credit_card') {
            // Credit card processing fee
            $paymentFee = $this->calculateCreditCardFee($orderSubtotal);
        }

        // Calculate tax on payment fee if there's a fee
        if ($paymentFee > 0) {
            $taxConditions = [
                'entity_type' => 'payment',
                'country_code' => $options['country_code'],
                'customer_type' => $options['customer_type']
            ];

            $taxResult = $this->taxService->calculatePaymentTax($paymentFee, $taxConditions);
            $taxAmount = $taxResult['tax_amount'];
            $taxRate = $taxResult['effective_rate'];
            $taxClassName = $taxResult['tax_class_name'];
        }

        return [
            'fee' => $paymentFee,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'tax_class_name' => $taxClassName,
            'total_with_tax' => $paymentFee + $taxAmount
        ];
    }

    /**
     * Calculate COD (Cash on Delivery) fee
     */
    protected function calculateCODFee(float $orderSubtotal): float
    {
        // COD fee could be percentage or fixed amount
        // This could be configurable in database
        
        $codFeePercentage = 0.02; // 2%
        $codFeeFixed = 5.00; // ₺5 fixed fee
        $codFeeMax = 20.00; // Maximum ₺20

        $percentageFee = $orderSubtotal * $codFeePercentage;
        $fee = max($codFeeFixed, $percentageFee);
        
        return min($fee, $codFeeMax);
    }

    /**
     * Calculate credit card processing fee
     */
    protected function calculateCreditCardFee(float $orderSubtotal): float
    {
        // Usually credit card fees are absorbed by merchant
        // But some businesses pass small fees to customers
        
        if ($orderSubtotal < 100) {
            return 2.00; // ₺2 for small orders
        }
        
        return 0; // Free for orders above ₺100
    }

    /**
     * Aggregate tax breakdown by tax class
     */
    protected function aggregateTaxBreakdown(array &$breakdown, array $itemData): void
    {
        $className = $itemData['tax_class_name'] ?? 'No Tax';
        
        if (!isset($breakdown[$className])) {
            $breakdown[$className] = [
                'tax_class' => $className,
                'rate' => $itemData['tax_rate'] ?? 0,
                'taxable_amount' => 0,
                'tax_amount' => 0
            ];
        }
        
        $breakdown[$className]['taxable_amount'] += $itemData['taxable_amount'] ?? $itemData['line_total'] ?? 0;
        $breakdown[$className]['tax_amount'] += $itemData['tax_amount'] ?? 0;
    }

    /**
     * Generate cart summary for display
     */
    protected function generateCartSummary(array $totals): array
    {
        $summary = [
            'subtotal' => '₺' . number_format($totals['subtotal'], 2),
            'total_tax' => '₺' . number_format($totals['total_tax'], 2),
            'total' => '₺' . number_format($totals['total'], 2),
            'item_count' => count($totals['items']),
            'has_tax' => $totals['total_tax'] > 0,
            'has_shipping' => $totals['shipping_cost'] > 0,
            'has_payment_fee' => $totals['payment_fee'] > 0
        ];

        if ($totals['discount_amount'] > 0) {
            $summary['discount'] = '₺' . number_format($totals['discount_amount'], 2);
            $summary['subtotal_after_discount'] = '₺' . number_format($totals['subtotal_after_discount'], 2);
        }

        if ($totals['shipping_cost'] > 0) {
            $summary['shipping'] = '₺' . number_format($totals['shipping_cost'], 2);
            if ($totals['shipping_tax'] > 0) {
                $summary['shipping_tax'] = '₺' . number_format($totals['shipping_tax'], 2);
            }
        }

        if ($totals['payment_fee'] > 0) {
            $summary['payment_fee'] = '₺' . number_format($totals['payment_fee'], 2);
            if ($totals['payment_tax'] > 0) {
                $summary['payment_tax'] = '₺' . number_format($totals['payment_tax'], 2);
            }
        }

        return $summary;
    }

    /**
     * Quick calculate for product with quantity
     */
    public function calculateProductTotal(Product $product, int $quantity, ProductVariant $variant = null, array $options = []): array
    {
        $cartItem = [
            'product' => $product,
            'variant' => $variant,
            'quantity' => $quantity
        ];

        return $this->calculateCartTotals([$cartItem], $options);
    }

    /**
     * Format tax breakdown for display
     */
    public function formatTaxBreakdown(array $taxBreakdown): array
    {
        $formatted = [];
        
        foreach ($taxBreakdown as $className => $data) {
            $formatted[] = [
                'name' => $className,
                'rate' => number_format($data['rate'] * 100, 2) . '%',
                'taxable_amount' => '₺' . number_format($data['taxable_amount'], 2),
                'tax_amount' => '₺' . number_format($data['tax_amount'], 2)
            ];
        }
        
        return $formatted;
    }
}