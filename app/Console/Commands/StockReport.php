<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StockHelper;
use App\Models\Product;

class StockReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:report {--low-threshold=5 : Low stock threshold} {--format=table : Output format (table, json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate stock report showing current inventory status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lowThreshold = (float) $this->option('low-threshold');
        $format = $this->option('format');
        
        $this->info('Generating Stock Report...');
        $this->line('');
        
        // Get overall statistics
        $stats = StockHelper::getStockStatistics();
        
        if ($format === 'json') {
            $this->line(json_encode($stats, JSON_PRETTY_PRINT));
            return 0;
        }
        
        // Display overall statistics
        $this->info('=== OVERALL STOCK STATISTICS ===');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Products', $stats['total_products']],
                ['In Stock', $stats['in_stock'] . ' (' . $stats['in_stock_percentage'] . '%%)'],
                ['Low Stock', $stats['low_stock']],
                ['Out of Stock', $stats['out_of_stock']],
                ['Total Stock Value', number_format($stats['total_value'], 2) . ' ₺'],
            ]
        );
        
        $this->line('');
        
        // Show low stock products
        $lowStockProducts = StockHelper::getLowStockProducts($lowThreshold);
        if ($lowStockProducts->count() > 0) {
            $this->warn('=== LOW STOCK PRODUCTS ===');
            $lowStockData = [];
            foreach ($lowStockProducts as $product) {
                $lowStockData[] = [
                    $product->name,
                    $product->isSimple() ? 'Simple' : 'Variable',
                    StockHelper::formatStockWithUnit($product->total_stock, $product->unit),
                    $product->unit?->symbol ?? 'adet'
                ];
            }
            $this->table(['Product Name', 'Type', 'Stock', 'Unit'], $lowStockData);
            $this->line('');
        }
        
        // Show out of stock products
        $outOfStockProducts = StockHelper::getOutOfStockProducts();
        if ($outOfStockProducts->count() > 0) {
            $this->error('=== OUT OF STOCK PRODUCTS ===');
            $outOfStockData = [];
            foreach ($outOfStockProducts as $product) {
                $outOfStockData[] = [
                    $product->name,
                    $product->isSimple() ? 'Simple' : 'Variable',
                    $product->variants->count() . ' variants'
                ];
            }
            $this->table(['Product Name', 'Type', 'Variants'], $outOfStockData);
        }
        
        $this->info('Stock report completed!');
        return 0;
    }
}