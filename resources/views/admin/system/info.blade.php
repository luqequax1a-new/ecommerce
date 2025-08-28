@extends('admin.layouts.app')

@section('title', 'Sistem Bilgisi')

@section('content')
<div class=\"space-y-6\">
    <!-- Header -->
    <div class=\"md:flex md:items-center md:justify-between\">
        <div class=\"flex-1 min-w-0\">
            <h2 class=\"text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate\">
                Sistem Bilgisi
            </h2>
            <p class=\"mt-1 text-sm text-gray-500\">
                Paylaşımlı hosting optimizasyonu için sistem durumu
            </p>
        </div>
        <div class=\"mt-4 flex space-x-3 md:mt-0 md:ml-4\">
            <a href=\"{{ route('admin.cache.clear') }}\" 
               class=\"inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50\">
                <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15\"></path>
                </svg>
                Cache Temizle
            </a>
            <a href=\"{{ route('admin.optimize') }}\" 
               class=\"inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700\">
                <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 10V3L4 14h7v7l9-11h-7z\"></path>
                </svg>
                Optimize Et
            </a>
        </div>
    </div>

    <!-- PHP Information -->
    <div class=\"bg-white shadow rounded-lg\">
        <div class=\"px-4 py-5 sm:p-6\">
            <h3 class=\"text-lg leading-6 font-medium text-gray-900 mb-4\">
                PHP Bilgileri
            </h3>
            
            <div class=\"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6\">
                <div>
                    <dt class=\"text-sm font-medium text-gray-500\">PHP Versiyonu</dt>
                    <dd class=\"mt-1 text-sm text-gray-900\">{{ $systemInfo['php_version'] }}</dd>
                </div>
                <div>
                    <dt class=\"text-sm font-medium text-gray-500\">Laravel Versiyonu</dt>
                    <dd class=\"mt-1 text-sm text-gray-900\">{{ $systemInfo['laravel_version'] }}</dd>
                </div>
                <div>
                    <dt class=\"text-sm font-medium text-gray-500\">Bellek Limiti</dt>
                    <dd class=\"mt-1 text-sm text-gray-900\">{{ $systemInfo['memory_usage']['limit'] }}</dd>
                </div>
            </div>
            
            <div class=\"mt-6\">
                <h4 class=\"text-md font-medium text-gray-900 mb-3\">Bellek Kullanımı</h4>
                <div class=\"grid grid-cols-1 sm:grid-cols-2 gap-4\">
                    <div>
                        <dt class=\"text-sm font-medium text-gray-500\">Şu anki kullanım</dt>
                        <dd class=\"mt-1 text-sm text-gray-900\">
                            {{ number_format($systemInfo['memory_usage']['current'] / 1024 / 1024, 2) }} MB
                        </dd>
                    </div>
                    <div>
                        <dt class=\"text-sm font-medium text-gray-500\">En yüksek kullanım</dt>
                        <dd class=\"mt-1 text-sm text-gray-900\">
                            {{ number_format($systemInfo['memory_usage']['peak'] / 1024 / 1024, 2) }} MB
                        </dd>
                    </div>
                </div>
            </div>
            
            <div class=\"mt-6\">
                <h4 class=\"text-md font-medium text-gray-900 mb-3\">PHP Eklentileri</h4>
                <div class=\"grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3\">
                    @foreach($systemInfo['php_extensions'] as $extension => $loaded)
                        <div class=\"flex items-center\">
                            @if($loaded)
                                <svg class=\"h-4 w-4 text-green-500 mr-2\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                                    <path fill-rule=\"evenodd\" d=\"M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\" clip-rule=\"evenodd\"></path>
                                </svg>
                            @else
                                <svg class=\"h-4 w-4 text-red-500 mr-2\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                                    <path fill-rule=\"evenodd\" d=\"M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z\" clip-rule=\"evenodd\"></path>
                                </svg>
                            @endif
                            <span class=\"text-sm text-gray-900 capitalize\">{{ $extension }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Database Information -->
    <div class=\"bg-white shadow rounded-lg\">
        <div class=\"px-4 py-5 sm:p-6\">
            <h3 class=\"text-lg leading-6 font-medium text-gray-900 mb-4\">
                Veritabanı Bilgileri
            </h3>
            
            <div class=\"grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6\">
                <div>
                    <dt class=\"text-sm font-medium text-gray-500\">Bağlantı Türü</dt>
                    <dd class=\"mt-1 text-sm text-gray-900 capitalize\">{{ $systemInfo['database']['connection'] }}</dd>
                </div>
                <div>
                    <dt class=\"text-sm font-medium text-gray-500\">Toplam Boyut</dt>
                    <dd class=\"mt-1 text-sm text-gray-900\">{{ $systemInfo['database']['size'] }}</dd>
                </div>
            </div>
            
            @if(count($systemInfo['database']['tables']) > 0)
                <div>
                    <h4 class=\"text-md font-medium text-gray-900 mb-3\">En Büyük Tablolar</h4>
                    <div class=\"overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg\">
                        <table class=\"min-w-full divide-y divide-gray-300\">
                            <thead class=\"bg-gray-50\">
                                <tr>
                                    <th class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">Tablo Adı</th>
                                    <th class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">Boyut</th>
                                </tr>
                            </thead>
                            <tbody class=\"bg-white divide-y divide-gray-200\">
                                @foreach($systemInfo['database']['tables'] as $table)
                                    <tr>
                                        <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900\">{{ $table['name'] }}</td>
                                        <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-500\">{{ $table['size'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Storage Information -->
    <div class=\"bg-white shadow rounded-lg\">
        <div class=\"px-4 py-5 sm:p-6\">
            <h3 class=\"text-lg leading-6 font-medium text-gray-900 mb-4\">
                Depolama Bilgileri
            </h3>
            
            <div class=\"grid grid-cols-1 sm:grid-cols-3 gap-6\">
                <div>
                    <dt class=\"text-sm font-medium text-gray-500\">Yüklenen Dosyalar</dt>
                    <dd class=\"mt-1 text-sm text-gray-900\">
                        {{ number_format($systemInfo['storage']['uploads'] / 1024 / 1024, 2) }} MB
                    </dd>
                </div>
                <div>
                    <dt class=\"text-sm font-medium text-gray-500\">Log Dosyaları</dt>
                    <dd class=\"mt-1 text-sm text-gray-900\">
                        {{ number_format($systemInfo['storage']['logs'] / 1024 / 1024, 2) }} MB
                    </dd>
                </div>
                <div>
                    <dt class=\"text-sm font-medium text-gray-500\">Cache Boyutu</dt>
                    <dd class=\"mt-1 text-sm text-gray-900\">{{ $systemInfo['cache']['size'] }}</dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Cache Management -->
    <div class=\"bg-white shadow rounded-lg\">
        <div class=\"px-4 py-5 sm:p-6\">
            <h3 class=\"text-lg leading-6 font-medium text-gray-900 mb-4\">
                Cache Yönetimi
            </h3>
            
            <div class=\"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4\">
                <a href=\"{{ route('admin.cache.clear', ['type' => 'config']) }}\" 
                   class=\"inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50\">
                    <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z\"></path>
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 12a3 3 0 11-6 0 3 3 0 016 0z\"></path>
                    </svg>
                    Config Cache
                </a>
                
                <a href=\"{{ route('admin.cache.clear', ['type' => 'route']) }}\" 
                   class=\"inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50\">
                    <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\"></path>
                    </svg>
                    Route Cache
                </a>
                
                <a href=\"{{ route('admin.cache.clear', ['type' => 'view']) }}\" 
                   class=\"inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50\">
                    <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 12a3 3 0 11-6 0 3 3 0 016 0z\"></path>
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\"></path>
                    </svg>
                    View Cache
                </a>
                
                <a href=\"{{ route('admin.cache.clear', ['type' => 'application']) }}\" 
                   class=\"inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50\">
                    <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4\"></path>
                    </svg>
                    App Cache
                </a>
            </div>
        </div>
    </div>

    <!-- Performance Tips -->
    <div class=\"bg-blue-50 border-l-4 border-blue-400 p-4\">
        <div class=\"flex\">
            <div class=\"flex-shrink-0\">
                <svg class=\"h-5 w-5 text-blue-400\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                    <path fill-rule=\"evenodd\" d=\"M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z\" clip-rule=\"evenodd\"></path>
                </svg>
            </div>
            <div class=\"ml-3\">
                <h3 class=\"text-sm font-medium text-blue-800\">Paylaşımlı Hosting Optimizasyon İpuçları</h3>
                <div class=\"mt-2 text-sm text-blue-700\">
                    <ul class=\"list-disc pl-5 space-y-1\">
                        <li>Cache'leri düzenli olarak temizleyin ve optimize edin</li>
                        <li>Gereksiz log dosyalarını silin</li>
                        <li>Görsel dosyalarını compress edin</li>
                        <li>Veritabanını düzenli olarak optimize edin</li>
                        <li>PHP memory limit'ini kontrol edin</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection