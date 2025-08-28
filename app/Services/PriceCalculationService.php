<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\TaxCalculationService;

class PriceCalculationService
{
    protected $taxService;

    public function __construct(TaxCalculationService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Calculate display price based on tax configuration
     */
    public function getDisplayPrice(
        Product $product, 
        ?ProductVariant $variant = null,
        array $options = []
    ): array {
        $options = array_merge([
            'include_tax' => true,
            'customer_type' => 'individual',
            'country_code' => 'TR',
            'rounding_mode' => 'round', // round, ceil, floor
            'decimals' => 2
        ], $options);

        $basePrice = $variant ? $variant->price : $product->price;
        
        if (!$options['include_tax'] || $basePrice <= 0) {
            return [
                'base_price' => $basePrice,
                'tax_amount' => 0,
                'final_price' => $basePrice,
                'display_price' => $this->formatPrice($basePrice, $options['decimals']),
                'tax_info' => null
            ];
        }

        // Calculate tax
        $taxConditions = [
            'entity_type' => 'product',
            'entity_id' => $product->id,
            'country_code' => $options['country_code'],
            'customer_type' => $options['customer_type']
        ];

        $taxResult = $this->taxService->calculateProductTax($product, $basePrice, $taxConditions);
        
        $finalPrice = $basePrice + $taxResult['tax_amount'];
        $finalPrice = $this->applyRounding($finalPrice, $options['rounding_mode'], $options['decimals']);

        return [
            'base_price' => $basePrice,
            'tax_amount' => $taxResult['tax_amount'],
            'final_price' => $finalPrice,
            'display_price' => $this->formatPrice($finalPrice, $options['decimals']),
            'tax_info' => [
                'rate' => $taxResult['effective_rate'] * 100,
                'class' => $taxResult['tax_class_name'],
                'formatted_rate' => number_format($taxResult['effective_rate'] * 100, 2) . '%'
            ]
        ];
    }

    /**
     * Calculate price ranges for variable products
     */
    public function getPriceRange(Product $product, array $options = []): array
    {
        if (!$product->isVariable()) {
            $priceData = $this->getDisplayPrice($product, null, $options);
            return [
                'min_price' => $priceData['final_price'],
                'max_price' => $priceData['final_price'],
                'display_range' => $priceData['display_price'],
                'is_range' => false
            ];
        }

        $variants = $product->variants()->orderBy('price')->get();
        
        if ($variants->isEmpty()) {
            return [
                'min_price' => 0,
                'max_price' => 0,
                'display_range' => $this->formatPrice(0, $options['decimals'] ?? 2),
                'is_range' => false
            ];
        }

        $minVariant = $variants->first();
        $maxVariant = $variants->last();

        $minPriceData = $this->getDisplayPrice($product, $minVariant, $options);
        $maxPriceData = $this->getDisplayPrice($product, $maxVariant, $options);

        $isRange = $minPriceData['final_price'] != $maxPriceData['final_price'];

        return [
            'min_price' => $minPriceData['final_price'],
            'max_price' => $maxPriceData['final_price'],
            'display_range' => $isRange 
                ? $minPriceData['display_price'] . ' - ' . $maxPriceData['display_price']
                : $minPriceData['display_price'],
            'is_range' => $isRange,
            'min_price_data' => $minPriceData,
            'max_price_data' => $maxPriceData
        ];
    }

    /**
     * Calculate bulk pricing (quantity discounts)
     */
    public function calculateBulkPrice(
        Product $product,
        int $quantity,
        ?ProductVariant $variant = null,
        array $options = []
    ): array {
        $basePrice = $variant ? $variant->price : $product->price;
        
        // Apply quantity discounts
        $discountedPrice = $this->applyQuantityDiscount($basePrice, $quantity);
        
        // Calculate with tax if needed
        if ($options['include_tax'] ?? true) {
            $taxConditions = [
                'entity_type' => 'product',
                'entity_id' => $product->id,
                'country_code' => $options['country_code'] ?? 'TR',
                'customer_type' => $options['customer_type'] ?? 'individual',
                'order_amount' => $discountedPrice * $quantity
            ];

            $taxResult = $this->taxService->calculateProductTax($product, $discountedPrice, $taxConditions);
            $finalPrice = $discountedPrice + $taxResult['tax_amount'];
        } else {
            $finalPrice = $discountedPrice;
            $taxResult = ['tax_amount' => 0, 'effective_rate' => 0];
        }

        $totalAmount = $finalPrice * $quantity;

        return [
            'original_unit_price' => $basePrice,
            'discounted_unit_price' => $discountedPrice,
            'final_unit_price' => $finalPrice,
            'quantity' => $quantity,
            'total_amount' => $totalAmount,
            'total_savings' => ($basePrice - $discountedPrice) * $quantity,
            'tax_amount_per_unit' => $taxResult['tax_amount'],
            'total_tax_amount' => $taxResult['tax_amount'] * $quantity,
            'formatted' => [
                'unit_price' => $this->formatPrice($finalPrice),
                'total_amount' => $this->formatPrice($totalAmount),
                'savings' => $this->formatPrice(($basePrice - $discountedPrice) * $quantity)
            ]
        ];
    }

    /**
     * Apply quantity discount based on tiers
     */
    protected function applyQuantityDiscount(float $basePrice, int $quantity): float
    {
        // Quantity discount tiers
        $discountTiers = [
            50 => 0.15,  // 15% off for 50+ items
            20 => 0.10,  // 10% off for 20+ items
            10 => 0.05,  // 5% off for 10+ items
        ];

        $discount = 0;
        
        foreach ($discountTiers as $minQuantity => $discountRate) {
            if ($quantity >= $minQuantity) {
                $discount = $discountRate;
                break;
            }
        }

        return $basePrice * (1 - $discount);
    }

    /**
     * Calculate competitor price comparison
     */
    public function getCompetitorComparison(Product $product, ?ProductVariant $variant = null): array
    {
        $currentPrice = $variant ? $variant->price : $product->price;
        $comparePrice = $product->compare_price;

        if (!$comparePrice || $comparePrice <= $currentPrice) {
            return [
                'has_comparison' => false,
                'current_price' => $currentPrice,
                'compare_price' => null,
                'savings' => 0,
                'discount_percentage' => 0
            ];
        }

        $savings = $comparePrice - $currentPrice;
        $discountPercentage = ($savings / $comparePrice) * 100;

        return [
            'has_comparison' => true,
            'current_price' => $currentPrice,
            'compare_price' => $comparePrice,
            'savings' => $savings,
            'discount_percentage' => $discountPercentage,
            'formatted' => [
                'current_price' => $this->formatPrice($currentPrice),
                'compare_price' => $this->formatPrice($comparePrice),
                'savings' => $this->formatPrice($savings),
                'discount_percentage' => number_format($discountPercentage, 0) . '%'
            ]
        ];
    }

    /**
     * Calculate price with custom discount
     */
    public function applyDiscount(
        float $price,
        float $discountAmount = 0,
        float $discountPercentage = 0,
        string $discountType = 'amount'
    ): array {
        $originalPrice = $price;
        
        if ($discountType === 'percentage' && $discountPercentage > 0) {
            $discountAmount = $price * ($discountPercentage / 100);
        }
        
        $discountedPrice = max(0, $price - $discountAmount);
        $actualSavings = $originalPrice - $discountedPrice;
        $actualPercentage = $originalPrice > 0 ? ($actualSavings / $originalPrice) * 100 : 0;

        return [
            'original_price' => $originalPrice,
            'discount_amount' => $discountAmount,
            'discounted_price' => $discountedPrice,
            'savings' => $actualSavings,
            'discount_percentage' => $actualPercentage,
            'formatted' => [
                'original_price' => $this->formatPrice($originalPrice),
                'discounted_price' => $this->formatPrice($discountedPrice),
                'savings' => $this->formatPrice($actualSavings)
            ]
        ];
    }

    /**
     * Apply rounding based on mode
     */
    protected function applyRounding(float $price, string $mode, int $decimals): float
    {
        $multiplier = pow(10, $decimals);
        
        switch ($mode) {
            case 'ceil':
                return ceil($price * $multiplier) / $multiplier;
            case 'floor':
                return floor($price * $multiplier) / $multiplier;
            case 'round':
            default:
                return round($price, $decimals);
        }
    }

    /**
     * Format price for display
     */
    protected function formatPrice(float $price, int $decimals = 2): string
    {
        return '₺' . number_format($price, $decimals);
    }

    /**
     * Calculate profit margin
     */
    public function calculateProfitMargin(Product $product, ?ProductVariant $variant = null): array
    {
        $sellingPrice = $variant ? $variant->price : $product->price;
        $costPrice = $product->cost_price ?? 0;

        if ($costPrice <= 0) {
            return [
                'has_cost_data' => false,
                'selling_price' => $sellingPrice,
                'cost_price' => 0,
                'profit' => 0,
                'margin_percentage' => 0,
                'markup_percentage' => 0
            ];
        }

        $profit = $sellingPrice - $costPrice;
        $marginPercentage = ($profit / $sellingPrice) * 100;
        $markupPercentage = ($profit / $costPrice) * 100;

        return [
            'has_cost_data' => true,
            'selling_price' => $sellingPrice,
            'cost_price' => $costPrice,
            'profit' => $profit,
            'margin_percentage' => $marginPercentage,
            'markup_percentage' => $markupPercentage,
            'formatted' => [
                'selling_price' => $this->formatPrice($sellingPrice),
                'cost_price' => $this->formatPrice($costPrice),
                'profit' => $this->formatPrice($profit),
                'margin_percentage' => number_format($marginPercentage, 2) . '%',
                'markup_percentage' => number_format($markupPercentage, 2) . '%'
            ]
        ];
    }

    /**
     * Get price tiers for wholesale customers
     */
    public function getWholesalePricing(Product $product, ?ProductVariant $variant = null): array
    {
        $basePrice = $variant ? $variant->price : $product->price;
        
        $wholesaleTiers = [
            'retail' => ['min_qty' => 1, 'discount' => 0],
            'wholesale' => ['min_qty' => 50, 'discount' => 0.15],
            'distributor' => ['min_qty' => 200, 'discount' => 0.25],
            'reseller' => ['min_qty' => 500, 'discount' => 0.35]
        ];

        $pricing = [];
        
        foreach ($wholesaleTiers as $tier => $data) {
            $discountedPrice = $basePrice * (1 - $data['discount']);
            
            $pricing[$tier] = [
                'name' => ucfirst($tier),
                'min_quantity' => $data['min_qty'],
                'discount_percentage' => $data['discount'] * 100,
                'unit_price' => $discountedPrice,
                'formatted_price' => $this->formatPrice($discountedPrice),
                'savings_per_unit' => $basePrice - $discountedPrice,
                'formatted_savings' => $this->formatPrice($basePrice - $discountedPrice)
            ];
        }

        return $pricing;
    }
}