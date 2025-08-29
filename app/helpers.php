<?php

if (!function_exists('format_stock')) {
    /**
     * Format stock quantity with unit
     */
    function format_stock(float $quantity, $unit = null): string
    {
        return \App\Services\StockHelper::formatStockWithUnit($quantity, $unit);
    }
}

if (!function_exists('format_price_with_unit')) {
    /**
     * Format price with unit
     */
    function format_price_with_unit(float $price, $unit = null): string
    {
        return \App\Services\StockHelper::formatPriceWithUnit($price, $unit);
    }
}

if (!function_exists('stock_status_badge')) {
    /**
     * Get stock status badge HTML
     */
    function stock_status_badge(string $status, ?float $quantity = null, $unit = null): string
    {
        return \App\Services\StockHelper::getStockStatusBadge($status, $quantity, $unit);
    }
}

if (!function_exists('product_stock_status')) {
    /**
     * Get product stock status
     */
    function product_stock_status($product, float $lowThreshold = 5): string
    {
        return \App\Services\StockHelper::getStockStatus($product, $lowThreshold);
    }
}

if (!function_exists('format_decimal')) {
    /**
     * Format decimal number removing unnecessary zeros
     */
    function format_decimal(float $number, int $precision = 3): string
    {
        return \App\Services\StockHelper::formatDecimal($number, $precision);
    }
}

if (!function_exists('currency_format')) {
    /**
     * Format price in Turkish Lira
     */
    function currency_format(float $price): string
    {
        return number_format($price, 2) . ' ₺';
    }
}

if (!function_exists('percentage_format')) {
    /**
     * Format percentage
     */
    function percentage_format(float $percentage, int $decimals = 1): string
    {
        return number_format($percentage, $decimals) . '%';
    }
}