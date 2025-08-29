@php
use App\\Services\\StockHelper;
$stats = StockHelper::getStockStatistics();
@endphp

<div class=\"bg-white overflow-hidden shadow-sm sm:rounded-lg\">
    <div class=\"p-6 bg-white border-b border-gray-200\">
        <h3 class=\"text-lg leading-6 font-medium text-gray-900 mb-4\">
            Stok Durumu
        </h3>
        
        <!-- Overview Cards -->
        <div class=\"grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6\">
            <!-- Total Products -->
            <div class=\"bg-blue-50 p-4 rounded-lg\">
                <div class=\"flex items-center\">
                    <div class=\"flex-shrink-0\">
                        <svg class=\"h-8 w-8 text-blue-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4\"></path>
                        </svg>
                    </div>
                    <div class=\"ml-4\">
                        <div class=\"text-2xl font-bold text-blue-900\">{{ $stats['total_products'] }}</div>
                        <div class=\"text-sm font-medium text-blue-700\">Toplam Ürün</div>
                    </div>
                </div>
            </div>
            
            <!-- In Stock -->
            <div class=\"bg-green-50 p-4 rounded-lg\">
                <div class=\"flex items-center\">
                    <div class=\"flex-shrink-0\">
                        <svg class=\"h-8 w-8 text-green-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 13l4 4L19 7\"></path>
                        </svg>
                    </div>
                    <div class=\"ml-4\">
                        <div class=\"text-2xl font-bold text-green-900\">{{ $stats['in_stock'] }}</div>
                        <div class=\"text-sm font-medium text-green-700\">Stokta ({{ percentage_format($stats['in_stock_percentage']) }})</div>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock -->
            <div class=\"bg-yellow-50 p-4 rounded-lg\">
                <div class=\"flex items-center\">
                    <div class=\"flex-shrink-0\">
                        <svg class=\"h-8 w-8 text-yellow-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z\"></path>
                        </svg>
                    </div>
                    <div class=\"ml-4\">
                        <div class=\"text-2xl font-bold text-yellow-900\">{{ $stats['low_stock'] }}</div>
                        <div class=\"text-sm font-medium text-yellow-700\">Az Stok</div>
                    </div>
                </div>
            </div>
            
            <!-- Out of Stock -->
            <div class=\"bg-red-50 p-4 rounded-lg\">
                <div class=\"flex items-center\">
                    <div class=\"flex-shrink-0\">
                        <svg class=\"h-8 w-8 text-red-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M6 18L18 6M6 6l12 12\"></path>
                        </svg>
                    </div>
                    <div class=\"ml-4\">
                        <div class=\"text-2xl font-bold text-red-900\">{{ $stats['out_of_stock'] }}</div>
                        <div class=\"text-sm font-medium text-red-700\">Stokta Yok</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stock Value -->
        <div class=\"bg-gray-50 p-4 rounded-lg mb-6\">
            <div class=\"flex items-center justify-between\">
                <div>
                    <div class=\"text-sm font-medium text-gray-700\">Toplam Stok Değeri</div>
                    <div class=\"text-3xl font-bold text-gray-900\">{{ currency_format($stats['total_value']) }}</div>
                </div>
                <div class=\"text-right\">
                    <div class=\"text-sm text-gray-500\">Tüm ürünlerin stok değeri</div>
                    <div class=\"text-xs text-gray-400\">miktar × fiyat toplamı</div>
                </div>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class=\"mb-4\">
            <div class=\"flex justify-between text-sm font-medium text-gray-700 mb-2\">
                <span>Stok Durumu</span>
                <span>{{ percentage_format($stats['in_stock_percentage']) }} stokta</span>
            </div>
            <div class=\"w-full bg-gray-200 rounded-full h-2\">
                <div class=\"bg-green-500 h-2 rounded-full\" style=\"width: {{ $stats['in_stock_percentage'] }}%\"></div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class=\"flex flex-wrap gap-2\">
            <a href=\"{{ route('admin.products.index', ['filter' => 'low_stock']) }}\" 
               class=\"inline-flex items-center px-3 py-2 border border-yellow-300 shadow-sm text-sm leading-4 font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100\">
                <svg class=\"-ml-0.5 mr-2 h-4 w-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z\"></path>
                </svg>
                Az Stok Ürünler
            </a>
            
            <a href=\"{{ route('admin.products.index', ['filter' => 'out_of_stock']) }}\" 
               class=\"inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100\">
                <svg class=\"-ml-0.5 mr-2 h-4 w-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M6 18L18 6M6 6l12 12\"></path>
                </svg>
                Stokta Yok
            </a>
            
            <a href=\"{{ route('admin.reports.stock') }}\" 
               class=\"inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100\">
                <svg class=\"-ml-0.5 mr-2 h-4 w-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\"></path>
                </svg>
                Detaylı Rapor
            </a>
        </div>
    </div>
</div>