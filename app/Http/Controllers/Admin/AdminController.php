<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductVariant;
use App\Services\StockHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with key metrics
     */
    public function dashboard()
    {
        // Cache dashboard data for 5 minutes to reduce database load
        $dashboardData = Cache::remember('admin_dashboard_stats', 300, function () {
            return [
                'products' => [
                    'total' => Product::count(),
                    'active' => Product::where('is_active', true)->count(),
                    'simple' => Product::where('product_type', 'simple')->count(),
                    'variable' => Product::where('product_type', 'variable')->count(),
                ],
                'categories' => Category::count(),
                'brands' => Brand::count(),
                'variants' => ProductVariant::count(),
                'stock_stats' => StockHelper::getStockStatistics(),
                'recent_products' => Product::with(['category', 'brand'])
                    ->latest()
                    ->limit(5)
                    ->get(),
                'low_stock_count' => StockHelper::getLowStockProducts()->count(),
                'out_of_stock_count' => StockHelper::getOutOfStockProducts()->count(),
            ];
        });

        return view('admin.dashboard.index', compact('dashboardData'));
    }

    /**
     * Display system information for shared hosting optimization
     */
    public function systemInfo()
    {
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit'),
            ],
            'database' => [
                'connection' => config('database.default'),
                'size' => $this->getDatabaseSize(),
                'tables' => $this->getTableSizes(),
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'size' => $this->getCacheSize(),
            ],
            'storage' => [
                'uploads' => $this->getDirectorySize(storage_path('app/public/uploads')),
                'logs' => $this->getDirectorySize(storage_path('logs')),
            ],
            'php_extensions' => [
                'gd' => extension_loaded('gd'),
                'imagick' => extension_loaded('imagick'),
                'curl' => extension_loaded('curl'),
                'zip' => extension_loaded('zip'),
                'pdo' => extension_loaded('pdo'),
            ],
        ];

        return view('admin.system.info', compact('systemInfo'));
    }

    /**
     * Clear application cache for performance optimization
     */
    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'all');
        
        try {
            switch ($type) {
                case 'config':
                    Artisan::call('config:clear');
                    $message = 'Configuration cache cleared successfully.';
                    break;
                    
                case 'route':
                    Artisan::call('route:clear');
                    $message = 'Route cache cleared successfully.';
                    break;
                    
                case 'view':
                    Artisan::call('view:clear');
                    $message = 'View cache cleared successfully.';
                    break;
                    
                case 'application':
                    Cache::flush();
                    $message = 'Application cache cleared successfully.';
                    break;
                    
                default:
                    Artisan::call('optimize:clear');
                    $message = 'All caches cleared successfully.';
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error clearing cache: ' . $e->getMessage());
        }
    }

    /**
     * Optimize application for shared hosting
     */
    public function optimize(Request $request)
    {
        try {
            // Cache configurations
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            return redirect()->back()->with('success', 'Application optimized for production successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error optimizing application: ' . $e->getMessage());
        }
    }

    /**
     * Get database size (approximation for shared hosting)
     */
    private function getDatabaseSize(): string
    {
        try {
            $size = DB::select("SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = ?", [
                config('database.connections.mysql.database')
            ]);
            
            return $this->formatBytes($size[0]->size ?? 0);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get table sizes for optimization analysis
     */
    private function getTableSizes(): array
    {
        try {
            $tables = DB::select("SELECT table_name, (data_length + index_length) as size FROM information_schema.tables WHERE table_schema = ? ORDER BY size DESC LIMIT 10", [
                config('database.connections.mysql.database')
            ]);
            
            return collect($tables)->map(function ($table) {
                return [
                    'name' => $table->table_name,
                    'size' => $this->formatBytes($table->size),
                ];
            })->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get cache size (if possible)
     */
    private function getCacheSize(): string
    {
        try {
            $driver = config('cache.default');
            
            if ($driver === 'file') {
                return $this->formatBytes($this->getDirectorySize(storage_path('framework/cache')));
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get directory size
     */
    private function getDirectorySize(string $directory): int
    {
        if (!is_dir($directory)) {
            return 0;
        }

        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}