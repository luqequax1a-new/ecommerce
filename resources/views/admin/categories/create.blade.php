@php
    // Initialize empty category for create form
    $category = new \App\Models\Category();
    $category->exists = false;
    
    // Set parent if provided
    if (request('parent_id')) {
        $category->parent_id = request('parent_id');
    }
    
    // Initialize empty collections
    $seoAnalysis = [];
    $urlRewrites = collect();
@endphp

@extends('admin.categories.edit')

@section('title', 'Yeni Kategori')

@section('content')
<div class=\"space-y-6\">
    <!-- Header -->
    <div class=\"md:flex md:items-center md:justify-between\">
        <div class=\"flex-1 min-w-0\">
            <h2 class=\"text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate\">
                Yeni Kategori
            </h2>
        </div>
        <div class=\"mt-4 flex md:mt-0 md:ml-4\">
            <a href=\"{{ route('admin.categories.index') }}\" 
               class=\"bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50\">
                Geri Dön
            </a>
        </div>
    </div>

    <form method=\"POST\" action=\"{{ route('admin.categories.store') }}\" class=\"space-y-6\">
        @csrf
        
        <div class=\"grid grid-cols-1 lg:grid-cols-3 gap-6\">
            <!-- Main Content -->
            <div class=\"lg:col-span-2 space-y-6\">
                <!-- Basic Information -->
                <div class=\"bg-white shadow rounded-lg p-6\">
                    <h3 class=\"text-lg font-medium text-gray-900 mb-4\">Temel Bilgiler</h3>
                    
                    <div class=\"space-y-4\">
                        <div>
                            <label for=\"name\" class=\"block text-sm font-medium text-gray-700\">Kategori Adı *</label>
                            <input type=\"text\" name=\"name\" id=\"name\" 
                                   value=\"{{ old('name') }}\"
                                   required
                                   class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror\">
                            @error('name')
                                <p class=\"mt-1 text-sm text-red-600\">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for=\"slug\" class=\"block text-sm font-medium text-gray-700\">URL Slug</label>
                            <input type=\"text\" name=\"slug\" id=\"slug\" 
                                   value=\"{{ old('slug') }}\"
                                   placeholder=\"Otomatik oluşturulacak\"
                                   class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('slug') border-red-300 @enderror\">
                            @error('slug')
                                <p class=\"mt-1 text-sm text-red-600\">{{ $message }}</p>
                            @enderror
                            <p class=\"mt-1 text-sm text-gray-500\">Boş bırakılırsa kategori adından otomatik oluşturulur.</p>
                        </div>
                        
                        <div>
                            <label for=\"description\" class=\"block text-sm font-medium text-gray-700\">Açıklama</label>
                            <textarea name=\"description\" id=\"description\" rows=\"4\"
                                      class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror\">{{ old('description') }}</textarea>
                            @error('description')
                                <p class=\"mt-1 text-sm text-red-600\">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- SEO Settings -->
                <div class=\"bg-white shadow rounded-lg p-6\">
                    <h3 class=\"text-lg font-medium text-gray-900 mb-4\">SEO Ayarları</h3>
                    
                    <div class=\"space-y-4\">
                        <div>
                            <label for=\"meta_title\" class=\"block text-sm font-medium text-gray-700\">Meta Başlık</label>
                            <input type=\"text\" name=\"meta_title\" id=\"meta_title\" 
                                   value=\"{{ old('meta_title') }}\"
                                   class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
                        </div>
                        
                        <div>
                            <label for=\"meta_description\" class=\"block text-sm font-medium text-gray-700\">Meta Açıklama</label>
                            <textarea name=\"meta_description\" id=\"meta_description\" rows=\"3\"
                                      class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">{{ old('meta_description') }}</textarea>
                        </div>
                        
                        <div>
                            <label for=\"meta_keywords\" class=\"block text-sm font-medium text-gray-700\">Meta Anahtar Kelimeler</label>
                            <input type=\"text\" name=\"meta_keywords\" id=\"meta_keywords\" 
                                   value=\"{{ old('meta_keywords') }}\"
                                   placeholder=\"virgülle ayırın\"
                                   class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class=\"space-y-6\">
                <!-- Category Settings -->
                <div class=\"bg-white shadow rounded-lg p-6\">
                    <h3 class=\"text-lg font-medium text-gray-900 mb-4\">Kategori Ayarları</h3>
                    
                    <div class=\"space-y-4\">
                        <div>
                            <label for=\"parent_id\" class=\"block text-sm font-medium text-gray-700\">Üst Kategori</label>
                            <select name=\"parent_id\" id=\"parent_id\" 
                                    class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
                                <option value=\"\">Ana Kategori</option>
                                @foreach($parentCategories as $id => $name)
                                    <option value=\"{{ $id }}\" {{ old('parent_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <p class=\"mt-1 text-sm text-red-600\">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for=\"sort_order\" class=\"block text-sm font-medium text-gray-700\">Sıralama</label>
                            <input type=\"number\" name=\"sort_order\" id=\"sort_order\" 
                                   value=\"{{ old('sort_order', 0) }}\"
                                   min=\"0\"
                                   class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\">
                            <p class=\"mt-1 text-sm text-gray-500\">Küçük değerler önce gösterilir.</p>
                        </div>
                        
                        <div class=\"flex items-center\">
                            <input id=\"is_active\" name=\"is_active\" type=\"checkbox\" 
                                   value=\"1\" {{ old('is_active', 1) ? 'checked' : '' }}
                                   class=\"h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded\">
                            <label for=\"is_active\" class=\"ml-2 block text-sm text-gray-900\">
                                Aktif
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class=\"bg-white shadow rounded-lg p-6\">
                    <button type=\"submit\" 
                            class=\"w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500\">
                        Kategori Oluştur
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function(e) {
    const name = e.target.value;
    const slug = name.toLowerCase()
        .replace(/ğ/g, 'g')
        .replace(/ü/g, 'u')
        .replace(/ş/g, 's')
        .replace(/ı/g, 'i')
        .replace(/ö/g, 'o')
        .replace(/ç/g, 'c')
        .replace(/[^a-z0-9 -]/g, '')
        .replace(/\\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    
    document.getElementById('slug').value = slug;
});
</script>
@endpush
@endsection