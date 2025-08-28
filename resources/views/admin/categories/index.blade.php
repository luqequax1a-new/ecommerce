@extends('admin.layouts.app')

@section('title', 'Kategori Yönetimi')

@section('content')
<div class=\"space-y-6\">
    <!-- Header -->
    <div class=\"md:flex md:items-center md:justify-between\">
        <div class=\"flex-1 min-w-0\">
            <h2 class=\"text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate\">
                Kategoriler
            </h2>
            <p class=\"mt-1 text-sm text-gray-500\">
                Toplam {{ $categories->total() }} kategori
            </p>
        </div>
        <div class=\"mt-4 flex md:mt-0 md:ml-4\">
            <a href=\"{{ route('admin.categories.create') }}\" 
               class=\"ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700\">
                <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 6v6m0 0v6m0-6h6m-6 0H6\"></path>
                </svg>
                Yeni Kategori
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class=\"bg-white shadow rounded-lg p-4\">
        <form method=\"GET\" class=\"grid grid-cols-1 md:grid-cols-4 gap-4\">
            <div>
                <label for=\"search\" class=\"block text-sm font-medium text-gray-700\">Ara</label>
                <input type=\"text\" name=\"search\" id=\"search\" 
                       value=\"{{ request('search') }}\"
                       placeholder=\"Kategori adı...\"
                       class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
            </div>
            <div>
                <label for=\"parent_id\" class=\"block text-sm font-medium text-gray-700\">Üst Kategori</label>
                <select name=\"parent_id\" id=\"parent_id\" 
                        class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
                    <option value=\"\">Tümü</option>
                    @foreach($parentCategories as $id => $name)
                        <option value=\"{{ $id }}\" {{ request('parent_id') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
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

    <!-- Categories Table -->
    <div class=\"bg-white shadow overflow-hidden sm:rounded-md\">
        @if($categories->count() > 0)
            <ul class=\"divide-y divide-gray-200\">
                @foreach($categories as $category)
                    <li>
                        <div class=\"px-4 py-4 flex items-center justify-between\">
                            <div class=\"flex items-center min-w-0 flex-1\">
                                <div class=\"min-w-0 flex-1\">
                                    <div class=\"flex items-center\">
                                        <p class=\"text-sm font-medium text-gray-900 truncate\">
                                            {{ $category->name }}
                                        </p>
                                        @if($category->parent)
                                            <span class=\"ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800\">
                                                {{ $category->parent->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class=\"mt-1 flex items-center space-x-4\">
                                        <p class=\"text-sm text-gray-500\">
                                            {{ $category->products_count }} ürün
                                        </p>
                                        @if($category->children_count > 0)
                                            <p class=\"text-sm text-gray-500\">
                                                {{ $category->children_count }} alt kategori
                                            </p>
                                        @endif
                                        <p class=\"text-sm text-gray-500\">
                                            {{ $category->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class=\"flex items-center space-x-4\">
                                <!-- Status Badge -->
                                <span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}\">
                                    {{ $category->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                                
                                <!-- Actions -->
                                <div class=\"flex items-center space-x-2\">
                                    <a href=\"{{ route('admin.categories.edit', $category) }}\" 
                                       class=\"text-blue-600 hover:text-blue-500 text-sm\">
                                        Düzenle
                                    </a>
                                    <form method=\"POST\" action=\"{{ route('admin.categories.destroy', $category) }}\" 
                                          class=\"inline\" 
                                          onsubmit=\"return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')\">
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
                {{ $categories->links() }}
            </div>
        @else
            <div class=\"text-center py-12\">
                <svg class=\"mx-auto h-12 w-12 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10\"></path>
                </svg>
                <h3 class=\"mt-2 text-sm font-medium text-gray-900\">Kategori bulunamadı</h3>
                <p class=\"mt-1 text-sm text-gray-500\">İlk kategoriyi oluşturarak başlayın.</p>
                <div class=\"mt-6\">
                    <a href=\"{{ route('admin.categories.create') }}\" 
                       class=\"inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700\">
                        <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 6v6m0 0v6m0-6h6m-6 0H6\"></path>
                        </svg>
                        Kategori Ekle
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection