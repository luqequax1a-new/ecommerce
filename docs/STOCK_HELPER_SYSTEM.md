# Stock Helper System Documentation

## Overview

The Stock Helper system provides comprehensive utilities for stock calculation, formatting, and display across the Laravel e-commerce application. It centralizes all stock-related logic and provides consistent formatting and calculation methods.

## Files Created

1. **`app/Services/StockHelper.php`** - Main service class with stock calculation and formatting utilities
2. **`app/helpers.php`** - Global helper functions for use in views
3. **`app/Console/Commands/StockReport.php`** - Console command for generating stock reports
4. **`resources/views/components/stock-status.blade.php`** - Blade component for stock status display
5. **`resources/views/admin/dashboard/stock-widget.blade.php`** - Admin dashboard widget for stock statistics

## Key Features

### 1. Stock Calculation
- **Total Stock**: Calculates total stock for both simple and variable products
- **Stock Status**: Determines if product is in stock, low stock, or out of stock
- **Stock Value**: Calculates monetary value of stock (quantity × price)

### 2. Formatting Utilities
- **Decimal Formatting**: Removes unnecessary trailing zeros from decimal quantities
- **Unit Formatting**: Displays quantities with their respective units (kg, m, litre, etc.)
- **Price Formatting**: Formats prices with units for per-unit pricing
- **Currency Formatting**: Turkish Lira formatting with proper symbols

### 3. Status Management
- **Stock Status Constants**: Predefined status levels (in_stock, low_stock, out_of_stock, unavailable)
- **Status Badges**: HTML badges with appropriate colors and icons
- **Threshold Management**: Configurable low stock thresholds

## Usage Examples

### In Controllers

```php
use App\\Services\\StockHelper;

// Get product stock status
$status = StockHelper::getStockStatus($product, 10); // 10 is low stock threshold

// Calculate total stock
$totalStock = StockHelper::calculateTotalStock($product);

// Get stock statistics for dashboard
$stats = StockHelper::getStockStatistics();

// Get low stock products
$lowStockProducts = StockHelper::getLowStockProducts(5);
```

### In Models

```php
// Product model methods (already implemented)
$product->getStockStatus(); // Returns stock status
$product->getStockStatusBadge(); // Returns HTML badge
$product->hasSufficientStock(10); // Check if 10 units available
$product->isInStock(); // Boolean check
$product->isLowStock(); // Boolean check with threshold
$product->getStockValue(); // Monetary value of stock

// ProductVariant model methods (already implemented)
$variant->getStockStatus();
$variant->getStockStatusBadge();
$variant->hasSufficientStock(5);
$variant->formatted_stock; // \"15.5 kg\" format
$variant->formatted_price; // \"25.50 ₺/kg\" format
```

### In Blade Views

```blade
{{-- Using global helper functions --}}
{{ format_stock($quantity, $unit) }}
{{ format_price_with_unit($price, $unit) }}
{{ currency_format($price) }}
{{ percentage_format($percentage) }}

{{-- Using the stock status component --}}
<x-stock-status :product=\"$product\" :show-quantity=\"true\" />

{{-- Using model methods --}}
{!! $product->getStockStatusBadge() !!}
{{ $product->total_stock }}
{{ $product->formatted_stock_with_unit }}
```

### Console Commands

```bash
# Generate stock report
php artisan stock:report

# With custom low stock threshold
php artisan stock:report --low-threshold=10

# JSON output format
php artisan stock:report --format=json
```

## Stock Status Levels

- **`in_stock`**: Product has sufficient stock (above threshold)
- **`low_stock`**: Product stock is below threshold but above 0
- **`out_of_stock`**: Product stock is 0 or negative
- **`unavailable`**: Product is inactive or has other availability issues

## Configuration

### Low Stock Threshold
The default low stock threshold is 5 units, but can be customized:

```php
// Custom threshold for specific checks
$status = StockHelper::getStockStatus($product, 10);
$isLowStock = $product->isLowStock(15);
```

### Decimal Precision
Stock quantities support up to 3 decimal places (0.001 precision):

```php
// Will display as \"15.5 kg\" (removes trailing zeros)
StockHelper::formatStockWithUnit(15.500, $unit);

// Will display as \"15.125 kg\" (preserves significant decimals)
StockHelper::formatStockWithUnit(15.125, $unit);
```

## Integration Points

### Database
- Products table: `stock_quantity` (decimal 12,3)
- Product variants table: `stock_quantity` (decimal 12,3)
- Units table: Provides unit symbols and formatting rules

### Views Updated
- `products/index.blade.php`: Uses helper for stock display
- `products/show.blade.php`: Uses helper for variant stock display
- Admin product forms: Support decimal stock input

### Models Enhanced
- `Product`: Added stock helper methods and status checks
- `ProductVariant`: Added stock helper methods and formatting

## Performance Considerations

- Helper methods are static for optimal performance
- Statistics calculation loads all products once for batch processing
- Scopes available for filtering products by stock status
- Decimal formatting removes unnecessary calculations

## Future Enhancements

- Stock movement tracking integration
- Automated low stock alerts
- Inventory forecasting based on sales velocity
- Multi-warehouse stock management
- Real-time stock level WebSocket updates

## Error Handling

- Graceful handling of null units (defaults to plain number formatting)
- Safe decimal calculations with proper precision
- Fallback values for missing stock data (defaults to 0)
- Type safety with strict parameter typing

This system provides a solid foundation for stock management in the e-commerce platform while maintaining flexibility for future enhancements.