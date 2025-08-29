<?php

namespace App\Services;

use App\Models\ShippingZone;
use App\Models\ShippingMethod;
use App\Models\Product;
use Illuminate\Support\Collection;

class ShippingCalculationService
{
    /**
     * Calculate shipping options for cart
     */
    public function calculateShippingOptions(array $cartItems, array $shippingAddress): Collection
    {
        // Find shipping zone for address
        $zone = ShippingZone::findForAddress($shippingAddress);
        
        if (!$zone) {
            return collect();
        }

        // Calculate cart totals
        $cartTotals = $this->calculateCartTotals($cartItems);

        // Get available shipping methods for zone
        $shippingMethods = $zone->getAvailableShippingMethods($cartTotals);

        // Calculate cost for each method
        return $shippingMethods->map(function ($method) use ($cartTotals) {
            $calculation = $method->calculateCost($cartTotals);
            
            return [
                'id' => $method->id,
                'carrier_id' => $method->carrier_id,
                'carrier_name' => $method->carrier->name,
                'method_name' => $method->name,
                'method_code' => $method->code,
                'cost' => $calculation['cost'],
                'is_free' => $calculation['is_free'] ?? false,
                'applicable' => $calculation['applicable'],
                'reason' => $calculation['reason'] ?? null,
                'delivery_time' => $method->delivery_time,
                'supports_cod' => $method->supports_cod,
                'require_signature' => $method->require_signature,
                'calculation_method' => $calculation['calculation_method'] ?? null,
                'carrier_logo' => $method->carrier->logo_url,
                'zone_name' => $method->zone->name
            ];
        })->filter(function ($option) {
            return $option['applicable'];
        })->values();
    }

    /**
     * Calculate single shipping method cost
     */
    public function calculateShippingCost(int $methodId, array $cartItems): array
    {
        $method = ShippingMethod::with(['carrier', 'zone'])->find($methodId);
        
        if (!$method || !$method->is_active) {
            return [
                'success' => false,
                'message' => 'Shipping method not found or inactive'
            ];
        }

        $cartTotals = $this->calculateCartTotals($cartItems);
        $calculation = $method->calculateCost($cartTotals);

        if (!$calculation['applicable']) {
            return [
                'success' => false,
                'message' => $calculation['reason'],
                'cost' => 0
            ];
        }

        return [
            'success' => true,
            'cost' => $calculation['cost'],
            'is_free' => $calculation['is_free'] ?? false,
            'method' => [
                'id' => $method->id,
                'name' => $method->name,
                'carrier' => $method->carrier->name,
                'delivery_time' => $method->delivery_time
            ]
        ];
    }

    /**
     * Calculate cart totals for shipping calculation
     */
    protected function calculateCartTotals(array $cartItems): array
    {
        $totalWeight = 0;
        $totalPrice = 0;
        $totalQuantity = 0;
        $hasVirtualProducts = false;
        $productCategories = [];
        $productTypes = [];

        foreach ($cartItems as $item) {
            $product = $this->getProductFromItem($item);
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? $product->price ?? 0;

            // Weight calculation
            $weight = $product->weight ?? 0;
            $totalWeight += $weight * $quantity;

            // Price calculation
            $totalPrice += $price * $quantity;

            // Quantity
            $totalQuantity += $quantity;

            // Check for virtual products
            if ($product->type === 'virtual' || $product->is_virtual ?? false) {
                $hasVirtualProducts = true;
            }

            // Collect categories and types
            if ($product->category_id) {
                $productCategories[] = $product->category_id;
            }
            
            if ($product->type) {
                $productTypes[] = $product->type;
            }
        }

        return [
            'weight' => $totalWeight,
            'price' => $totalPrice,
            'quantity' => $totalQuantity,
            'has_virtual_products' => $hasVirtualProducts,
            'product_categories' => array_unique($productCategories),
            'product_types' => array_unique($productTypes),
            'includes_tax' => false // Default, can be overridden
        ];
    }

    /**
     * Get product from cart item
     */
    protected function getProductFromItem(array $item): ?Product
    {
        // If product object is already provided
        if (isset($item['product']) && $item['product'] instanceof Product) {
            return $item['product'];
        }

        // If product ID is provided
        if (isset($item['product_id'])) {
            return Product::find($item['product_id']);
        }

        // If SKU is provided
        if (isset($item['sku'])) {
            return Product::where('sku', $item['sku'])->first();
        }

        return null;
    }

    /**
     * Validate shipping address for zone detection
     */
    public function validateShippingAddress(array $address): array
    {
        $errors = [];

        if (empty($address['country'])) {
            $errors[] = 'Country is required';
        }

        if (empty($address['city'])) {
            $errors[] = 'City is required';
        }

        if (empty($address['postal_code'])) {
            $errors[] = 'Postal code is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get shipping zones for country
     */
    public function getZonesForCountry(string $country): Collection
    {
        return ShippingZone::active()
                          ->where(function ($query) use ($country) {
                              $query->whereJsonContains('countries', strtoupper($country))
                                    ->orWhereNull('countries');
                          })
                          ->ordered()
                          ->get();
    }

    /**
     * Calculate shipping for specific zone and parameters
     */
    public function calculateForZone(int $zoneId, array $params): Collection
    {
        $zone = ShippingZone::find($zoneId);
        
        if (!$zone || !$zone->is_active) {
            return collect();
        }

        return $zone->getAvailableShippingMethods($params)
                   ->map(function ($method) use ($params) {
                       $calculation = $method->calculateCost($params);
                       
                       return array_merge($calculation, [
                           'method_id' => $method->id,
                           'method_name' => $method->name,
                           'carrier_name' => $method->carrier->name,
                           'delivery_time' => $method->delivery_time
                       ]);
                   })
                   ->filter(function ($result) {
                       return $result['applicable'];
                   });
    }

    /**
     * Get cheapest shipping option
     */
    public function getCheapestOption(array $cartItems, array $shippingAddress): ?array
    {
        $options = $this->calculateShippingOptions($cartItems, $shippingAddress);
        
        if ($options->isEmpty()) {
            return null;
        }

        return $options->sortBy('cost')->first();
    }

    /**
     * Get fastest shipping option
     */
    public function getFastestOption(array $cartItems, array $shippingAddress): ?array
    {
        $options = $this->calculateShippingOptions($cartItems, $shippingAddress);
        
        if ($options->isEmpty()) {
            return null;
        }

        // Sort by minimum delivery days (extracted from delivery_time text)
        return $options->map(function ($option) {
            $deliveryTime = $option['delivery_time'] ?? '';
            // Extract minimum days from text like "1-3 iş günü"
            preg_match('/(\d+)/', $deliveryTime, $matches);
            $option['min_delivery_days'] = isset($matches[1]) ? (int)$matches[1] : 999;
            return $option;
        })->sortBy('min_delivery_days')->first();
    }

    /**
     * Test shipping calculation with sample data
     */
    public function testCalculation(array $testData): array
    {
        $defaultData = [
            'weight' => 1.0,
            'price' => 100.0,
            'quantity' => 1,
            'has_virtual_products' => false,
            'includes_tax' => false
        ];

        $params = array_merge($defaultData, $testData);

        $allMethods = ShippingMethod::with(['carrier', 'zone'])
                                   ->active()
                                   ->get();

        $results = [];

        foreach ($allMethods as $method) {
            $calculation = $method->calculateCost($params);
            
            $results[] = [
                'method_id' => $method->id,
                'method_name' => $method->name,
                'carrier_name' => $method->carrier->name,
                'zone_name' => $method->zone->name,
                'calculation_method' => $method->calc_method,
                'base_fee' => $method->base_fee,
                'step_fee' => $method->step_fee,
                'free_threshold' => $method->free_threshold,
                'result' => $calculation
            ];
        }

        return $results;
    }
}