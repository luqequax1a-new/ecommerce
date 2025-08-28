@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class=\"space-y-6\">
    <!-- Header -->
    <div class=\"md:flex md:items-center md:justify-between\">
        <div class=\"flex-1 min-w-0\">
            <h2 class=\"text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate\">
                Dashboard
            </h2>
            <p class=\"mt-1 text-sm text-gray-500\">
                E-ticaret yönetim paneli - {{ now()->format('d.m.Y H:i') }}
            </p>
        </div>
        <div class=\"mt-4 flex md:mt-0 md:ml-4\">
            <a href=\"{{ route('admin.system.info') }}\" 
               class=\"ml-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50\">
                <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\"></path>
                </svg>
                Sistem Bilgisi
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class=\"grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4\">
        <!-- Total Products -->
        <div class=\"bg-white overflow-hidden shadow rounded-lg\">
            <div class=\"p-5\">
                <div class=\"flex items-center\">
                    <div class=\"flex-shrink-0\">
                        <svg class=\"h-6 w-6 text-blue-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4\"></path>
                        </svg>
                    </div>
                    <div class=\"ml-5 w-0 flex-1\">
                        <dl>
                            <dt class=\"text-sm font-medium text-gray-500 truncate\">
                                Toplam Ürün
                            </dt>
                            <dd class=\"text-lg font-medium text-gray-900\">
                                {{ $dashboardData['products']['total'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class=\"bg-gray-50 px-5 py-3\">
                <div class=\"text-sm\">
                    <span class=\"text-green-600 font-medium\">{{ $dashboardData['products']['active'] }}</span>
                    <span class=\"text-gray-500\">aktif</span>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class=\"bg-white overflow-hidden shadow rounded-lg\">
            <div class=\"p-5\">
                <div class=\"flex items-center\">
                    <div class=\"flex-shrink-0\">
                        <svg class=\"h-6 w-6 text-green-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10\"></path>
                        </svg>
                    </div>
                    <div class=\"ml-5 w-0 flex-1\">
                        <dl>
                            <dt class=\"text-sm font-medium text-gray-500 truncate\">
                                Kategoriler
                            </dt>
                            <dd class=\"text-lg font-medium text-gray-900\">
                                {{ $dashboardData['categories'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class=\"bg-gray-50 px-5 py-3\">
                <div class=\"text-sm\">
                    <a href=\"{{ route('admin.categories.index') }}\" class=\"text-blue-600 hover:text-blue-500\">Yönet</a>
                </div>
            </div>
        </div>

        <!-- Brands -->
        <div class=\"bg-white overflow-hidden shadow rounded-lg\">
            <div class=\"p-5\">
                <div class=\"flex items-center\">
                    <div class=\"flex-shrink-0\">
                        <svg class=\"h-6 w-6 text-purple-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z\"></path>
                        </svg>
                    </div>
                    <div class=\"ml-5 w-0 flex-1\">
                        <dl>
                            <dt class=\"text-sm font-medium text-gray-500 truncate\">
                                Markalar
                            </dt>
                            <dd class=\"text-lg font-medium text-gray-900\">
                                {{ $dashboardData['brands'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class=\"bg-gray-50 px-5 py-3\">
                <div class=\"text-sm\">
                    <a href=\"{{ route('admin.brands.index') }}\" class=\"text-blue-600 hover:text-blue-500\">Yönet</a>
                </div>
            </div>
        </div>

        <!-- Stock Alert -->
        <div class=\"bg-white overflow-hidden shadow rounded-lg\">
            <div class=\"p-5\">
                <div class=\"flex items-center\">
                    <div class=\"flex-shrink-0\">
                        @if($dashboardData['out_of_stock_count'] > 0)
                            <svg class=\"h-6 w-6 text-red-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z\"></path>
                            </svg>
                        @elseif($dashboardData['low_stock_count'] > 0)
                            <svg class=\"h-6 w-6 text-yellow-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z\"></path>
                            </svg>
                        @else
                            <svg class=\"h-6 w-6 text-green-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 13l4 4L19 7\"></path>
                            </svg>
                        @endif
                    </div>
                    <div class=\"ml-5 w-0 flex-1\">
                        <dl>
                            <dt class=\"text-sm font-medium text-gray-500 truncate\">
                                Stok Durumu
                            </dt>
                            <dd class=\"text-lg font-medium text-gray-900\">
                                @if($dashboardData['out_of_stock_count'] > 0)
                                    <span class=\"text-red-600\">{{ $dashboardData['out_of_stock_count'] }} tükendi</span>
                                @elseif($dashboardData['low_stock_count'] > 0)
                                    <span class=\"text-yellow-600\">{{ $dashboardData['low_stock_count'] }} az stok</span>
                                @else
                                    <span class=\"text-green-600\">İyi durumda</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class=\"bg-gray-50 px-5 py-3\">
                <div class=\"text-sm\">
                    <a href=\"{{ route('admin.products.index', ['filter' => 'stock_issues']) }}\" class=\"text-blue-600 hover:text-blue-500\">Detaylar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class=\"grid grid-cols-1 lg:grid-cols-2 gap-6\">
        <!-- Stock Statistics Widget -->
        <div class=\"lg:col-span-1\">
            @include('admin.dashboard.stock-widget')
        </div>

        <!-- Recent Products -->
        <div class=\"bg-white shadow rounded-lg\">
            <div class=\"px-4 py-5 sm:p-6\">
                <h3 class=\"text-lg leading-6 font-medium text-gray-900 mb-4\">
                    Son Eklenen Ürünler
                </h3>
                
                @if($dashboardData['recent_products']->count() > 0)
                    <div class=\"space-y-4\">
                        @foreach($dashboardData['recent_products'] as $product)
                            <div class=\"flex items-center justify-between\">
                                <div class=\"flex-1 min-w-0\">
                                    <p class=\"text-sm font-medium text-gray-900 truncate\">
                                        {{ $product->name }}
                                    </p>
                                    <p class=\"text-sm text-gray-500\">
                                        {{ $product->category?->name }} • {{ $product->brand?->name }}
                                    </p>
                                    <p class=\"text-xs text-gray-400\">
                                        {{ $product->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class=\"flex items-center space-x-2\">
                                    <span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}\">
                                        {{ $product->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                    <a href=\"{{ route('admin.products.edit', $product) }}\" 
                                       class=\"text-blue-600 hover:text-blue-500 text-sm\">
                                        Düzenle
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class=\"mt-6\">
                        <a href=\"{{ route('admin.products.index') }}\" 
                           class=\"w-full flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50\">
                            Tüm Ürünleri Görüntüle
                        </a>
                    </div>
                @else
                    <div class=\"text-center py-6\">
                        <svg class=\"mx-auto h-12 w-12 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4\"></path>
                        </svg>
                        <h3 class=\"mt-2 text-sm font-medium text-gray-900\">Henüz ürün yok</h3>
                        <p class=\"mt-1 text-sm text-gray-500\">İlk ürününüzü ekleyerek başlayın.</p>
                        <div class=\"mt-6\">
                            <a href=\"{{ route('admin.products.create') }}\" 
                               class=\"inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700\">
                                <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 6v6m0 0v6m0-6h6m-6 0H6\"></path>
                                </svg>
                                Ürün Ekle
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class=\"bg-white shadow rounded-lg\">
        <div class=\"px-4 py-5 sm:p-6\">
            <h3 class=\"text-lg leading-6 font-medium text-gray-900 mb-4\">
                Hızlı İşlemler
            </h3>
            
            <div class=\"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4\">
                <a href=\"{{ route('admin.products.create') }}\" 
                   class=\"inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700\">
                    <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 6v6m0 0v6m0-6h6m-6 0H6\"></path>
                    </svg>
                    Yeni Ürün
                </a>
                
                <a href=\"{{ route('admin.categories.create') }}\" 
                   class=\"inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50\">
                    <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10\"></path>
                    </svg>
                    Yeni Kategori
                </a>
                
                <a href=\"{{ route('admin.brands.create') }}\" 
                   class=\"inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50\">
                    <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z\"></path>
                    </svg>
                    Yeni Marka
                </a>
                
                <a href=\"{{ route('admin.cache.clear') }}\" 
                   class=\"inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50\">
                    <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15\"></path>
                    </svg>
                    Cache Temizle
                </a>
            </div>
        </div>
    </div>
</div>
@endsection