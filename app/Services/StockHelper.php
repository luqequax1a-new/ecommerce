<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;

class StockHelper
{
    /**
     * Stock status constants
     */
    const STATUS_IN_STOCK = 'in_stock';
    const STATUS_LOW_STOCK = 'low_stock';
    const STATUS_OUT_OF_STOCK = 'out_of_stock';
    const STATUS_UNAVAILABLE = 'unavailable';

    /**
     * Default low stock threshold
     */
    const DEFAULT_LOW_STOCK_THRESHOLD = 5;

    /**
     * Calculate total stock for a product (handles both simple and variable products)
     */
    public static function calculateTotalStock(Product $product): float
    {
        if ($product->isSimple()) {
            return $product->stock_quantity ?? 0;
        }
        
        // Variable product: sum of all variants
        return $product->variants->sum('stock_quantity') ?? 0;
    }

    /**
     * Get stock status for a product
     */
    public static function getStockStatus(Product $product, float $lowStockThreshold = self::DEFAULT_LOW_STOCK_THRESHOLD): string
    {
        $totalStock = self::calculateTotalStock($product);
        
        if ($totalStock <= 0) {
            return self::STATUS_OUT_OF_STOCK;
        }
        
        if ($totalStock <= $lowStockThreshold) {
            return self::STATUS_LOW_STOCK;
        }
        
        return self::STATUS_IN_STOCK;
    }

    /**
     * Get stock status for a variant
     */
    public static function getVariantStockStatus(ProductVariant $variant, float $lowStockThreshold = self::DEFAULT_LOW_STOCK_THRESHOLD): string
    {
        $stock = $variant->stock_quantity ?? 0;
        
        if ($stock <= 0) {
            return self::STATUS_OUT_OF_STOCK;
        }
        
        if ($stock <= $lowStockThreshold) {
            return self::STATUS_LOW_STOCK;
        }
        
        return self::STATUS_IN_STOCK;
    }

    /**
     * Format stock quantity with unit symbol
     */
    public static function formatStockWithUnit(float $quantity, ?Unit $unit = null): string
    {
        if (!$unit) {
            return self::formatDecimal($quantity);
        }
        
        $formatted = self::formatDecimal($quantity);
        return $formatted . ' ' . $unit->symbol;
    }

    /**
     * Format decimal quantity (removes unnecessary trailing zeros)
     */
    public static function formatDecimal(float $quantity, int $precision = 3): string
    {
        return rtrim(rtrim(number_format($quantity, $precision), '0'), '.');
    }

    /**
     * Format price with unit (for per-unit pricing)
     */
    public static function formatPriceWithUnit(float $price, ?Unit $unit = null): string
    {
        $formattedPrice = number_format($price, 2) . ' ₺';
        
        if ($unit && $unit->symbol !== 'adet') {
            $formattedPrice .= '/' . $unit->symbol;
        }
        
        return $formattedPrice;
    }

    /**
     * Get stock status badge HTML
     */
    public static function getStockStatusBadge(string $status, ?float $quantity = null, ?Unit $unit = null): string
    {
        $badges = [
            self::STATUS_IN_STOCK => [
                'class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
                'icon' => '<svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>',
                'text' => 'Stokta'
            ],
            self::STATUS_LOW_STOCK => [
                'class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
                'icon' => '<svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>',
                'text' => 'Az Stok'
            ],
            self::STATUS_OUT_OF_STOCK => [
                'class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800',
                'icon' => '<svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>',
                'text' => 'Stokta Yok'
            ],
            self::STATUS_UNAVAILABLE => [
                'class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800',
                'icon' => '<svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>',
                'text' => 'Mevcut Değil'
            ],
        ];

        $badge = $badges[$status] ?? $badges[self::STATUS_UNAVAILABLE];
        
        $quantityText = '';
        if ($quantity !== null && $quantity > 0) {
            $quantityText = ' (' . self::formatStockWithUnit($quantity, $unit) . ')';
        }
        
        return sprintf(
            '<span class="%s">%s%s%s</span>',
            $badge['class'],
            $badge['icon'],
            $badge['text'],
            $quantityText
        );
    }

    /**
     * Get price range for variable products
     */
    public static function getPriceRange(Product $product): array
    {
        if ($product->isSimple()) {
            $price = $product->variants->first()?->price ?? 0;
            return ['min' => $price, 'max' => $price];
        }
        
        $prices = $product->variants->pluck('price')->filter();
        
        return [
            'min' => $prices->min() ?? 0,
            'max' => $prices->max() ?? 0
        ];
    }

    /**
     * Format price range as string
     */
    public static function formatPriceRange(Product $product, ?Unit $unit = null): string
    {
        $range = self::getPriceRange($product);
        
        if ($range['min'] == $range['max']) {
            return self::formatPriceWithUnit($range['min'], $unit);
        }
        
        return number_format($range['min'], 2) . ' - ' . number_format($range['max'], 2) . ' ₺' . 
               ($unit && $unit->symbol !== 'adet' ? '/' . $unit->symbol : '');
    }

    /**
     * Check if product/variant has sufficient stock for quantity
     */
    public static function hasSufficientStock(Product|ProductVariant $item, float $requestedQuantity): bool
    {
        if ($item instanceof Product) {
            $availableStock = self::calculateTotalStock($item);
        } else {
            $availableStock = $item->stock_quantity ?? 0;
        }
        
        return $availableStock >= $requestedQuantity;
    }

    /**
     * Get available stock for a product/variant
     */
    public static function getAvailableStock(Product|ProductVariant $item): float
    {
        if ($item instanceof Product) {
            return self::calculateTotalStock($item);
        }
        
        return $item->stock_quantity ?? 0;
    }

    /**
     * Get low stock products (for admin alerts)
     */
    public static function getLowStockProducts(float $threshold = self::DEFAULT_LOW_STOCK_THRESHOLD): \Illuminate\Database\Eloquent\Collection
    {
        return Product::with(['variants', 'unit'])
            ->where('is_active', true)
            ->get()
            ->filter(function ($product) use ($threshold) {
                return self::getStockStatus($product, $threshold) === self::STATUS_LOW_STOCK;
            });
    }

    /**
     * Get out of stock products (for admin alerts)
     */
    public static function getOutOfStockProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::with(['variants', 'unit'])
            ->where('is_active', true)
            ->get()
            ->filter(function ($product) {
                return self::getStockStatus($product) === self::STATUS_OUT_OF_STOCK;
            });
    }

    /**
     * Calculate stock value (quantity * price) for a product
     */
    public static function calculateStockValue(Product $product): float
    {
        if ($product->isSimple()) {
            $variant = $product->variants->first();
            return ($product->stock_quantity ?? 0) * ($variant?->price ?? 0);
        }
        
        return $product->variants->sum(function ($variant) {
            return ($variant->stock_quantity ?? 0) * ($variant->price ?? 0);
        });
    }

    /**
     * Get stock statistics for dashboard
     */
    public static function getStockStatistics(): array
    {
        $products = Product::with(['variants', 'unit'])->where('is_active', true)->get();
        
        $totalProducts = $products->count();
        $inStockCount = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $totalValue = 0;
        
        foreach ($products as $product) {
            $status = self::getStockStatus($product);
            
            switch ($status) {
                case self::STATUS_IN_STOCK:
                    $inStockCount++;
                    break;
                case self::STATUS_LOW_STOCK:
                    $lowStockCount++;
                    break;
                case self::STATUS_OUT_OF_STOCK:
                    $outOfStockCount++;
                    break;
            }
            
            $totalValue += self::calculateStockValue($product);
        }
        
        return [
            'total_products' => $totalProducts,
            'in_stock' => $inStockCount,
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStockCount,
            'total_value' => $totalValue,
            'in_stock_percentage' => $totalProducts > 0 ? round(($inStockCount / $totalProducts) * 100, 1) : 0,
        ];
    }
}