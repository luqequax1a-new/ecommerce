@extends('admin.layouts.app')

@section('title', 'Marka Yönetimi')

@section('content')
<div class=\"space-y-6\">
    <!-- Header -->
    <div class=\"md:flex md:items-center md:justify-between\">
        <div class=\"flex-1 min-w-0\">
            <h2 class=\"text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate\">
                Markalar
            </h2>
            <p class=\"mt-1 text-sm text-gray-500\">
                Toplam {{ $brands->total() }} marka
            </p>
        </div>
        <div class=\"mt-4 flex md:mt-0 md:ml-4\">
            <a href=\"{{ route('admin.brands.create') }}\" 
               class=\"ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700\">
                <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 6v6m0 0v6m0-6h6m-6 0H6\"></path>
                </svg>
                Yeni Marka
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class=\"bg-white shadow rounded-lg p-4\">
        <form method=\"GET\" class=\"grid grid-cols-1 md:grid-cols-3 gap-4\">
            <div>
                <label for=\"search\" class=\"block text-sm font-medium text-gray-700\">Ara</label>
                <input type=\"text\" name=\"search\" id=\"search\" 
                       value=\"{{ request('search') }}\"
                       placeholder=\"Marka adı...\"
                       class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
            </div>
            <div>
                <label for=\"status\" class=\"block text-sm font-medium text-gray-700\">Durum</label>
                <select name=\"status\" id=\"status\" 
                        class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
                    <option value=\"\">Tümü</option>
                    <option value=\"active\" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value=\"inactive\" {{ request('status') === 'inactive' ? 'selected' : '' }}>Pasif</option>
                </select>
            </div>
            <div class=\"flex items-end\">
                <button type=\"submit\" 
                        class=\"w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500\">
                    Filtrele
                </button>
            </div>
        </form>
    </div>

    <!-- Brands Table -->
    <div class=\"bg-white shadow overflow-hidden sm:rounded-md\">
        @if($brands->count() > 0)
            <ul class=\"divide-y divide-gray-200\">
                @foreach($brands as $brand)
                    <li>
                        <div class=\"px-4 py-4 flex items-center justify-between\">
                            <div class=\"flex items-center min-w-0 flex-1\">
                                <div class=\"min-w-0 flex-1\">
                                    <div class=\"flex items-center\">
                                        <p class=\"text-sm font-medium text-gray-900 truncate\">
                                            {{ $brand->name }}
                                        </p>
                                        @if($brand->website)
                                            <a href=\"{{ $brand->website }}\" target=\"_blank\" 
                                               class=\"ml-2 text-blue-600 hover:text-blue-500\">
                                                <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14\"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                    <div class=\"mt-1 flex items-center space-x-4\">
                                        <p class=\"text-sm text-gray-500\">
                                            {{ $brand->products_count }} ürün
                                        </p>
                                        <p class=\"text-sm text-gray-500\">
                                            {{ $brand->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    @if($brand->description)
                                        <p class=\"mt-1 text-sm text-gray-500 truncate\">
                                            {{ Str::limit($brand->description, 100) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class=\"flex items-center space-x-4\">
                                <!-- Status Badge -->
                                <span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $brand->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}\">
                                    {{ $brand->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                                
                                <!-- Actions -->
                                <div class=\"flex items-center space-x-2\">
                                    <a href=\"{{ route('admin.brands.edit', $brand) }}\" 
                                       class=\"text-blue-600 hover:text-blue-500 text-sm\">
                                        Düzenle
                                    </a>
                                    <form method=\"POST\" action=\"{{ route('admin.brands.destroy', $brand) }}\" 
                                          class=\"inline\" 
                                          onsubmit=\"return confirm('Bu markayı silmek istediğinizden emin misiniz?')\">
                                        @csrf
                                        @method('DELETE')
                                        <button type=\"submit\" class=\"text-red-600 hover:text-red-500 text-sm\">
                                            Sil
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            
            <!-- Pagination -->
            <div class=\"bg-white px-4 py-3 border-t border-gray-200\">
                {{ $brands->links() }}
            </div>
        @else
            <div class=\"text-center py-12\">
                <svg class=\"mx-auto h-12 w-12 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z\"></path>
                </svg>
                <h3 class=\"mt-2 text-sm font-medium text-gray-900\">Marka bulunamadı</h3>
                <p class=\"mt-1 text-sm text-gray-500\">İlk markayı oluşturarak başlayın.</p>
                <div class=\"mt-6\">
                    <a href=\"{{ route('admin.brands.create') }}\" 
                       class=\"inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700\">
                        <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 6v6m0 0v6m0-6h6m-6 0H6\"></path>
                        </svg>
                        Marka Ekle
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection