@extends('admin.layouts.app')

@section('title', 'Yeni Ürün Ekle')

@section('content')
<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <h1 class="text-xl font-semibold text-gray-900">Yeni Ürün Ekle</h1>
        <p class="mt-2 text-sm text-gray-700">Mağazanıza yeni bir ürün ekleyin.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="{{ route('admin.products.index') }}" 
           class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            Geri Dön
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" 
      x-data="productForm()" class="mt-6">
    @csrf
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Ana Bilgiler -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Temel Bilgiler -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Temel Bilgiler</h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Ürün Adı *</label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               x-model="form.name"
                               @input="updateSlug()"
                               value="{{ old('name') }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700">URL (Slug)</label>
                        <input type="text" 
                               name="slug" 
                               id="slug" 
                               x-model="form.slug"
                               value="{{ old('slug') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('slug') border-red-300 @enderror">
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Boş bırakılırsa otomatik oluşturulur.</p>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Açıklama</label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="short_description" class="block text-sm font-medium text-gray-700">Kısa Açıklama</label>
                        <textarea name="short_description" 
                                  id="short_description" 
                                  rows="2"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('short_description') border-red-300 @enderror">{{ old('short_description') }}</textarea>
                        @error('short_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Kategori ve Marka -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Kategori ve Marka</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="category_id" 
                                id="category_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('category_id') border-red-300 @enderror">
                            <option value="">Kategori Seçin</option>
                            @php
                                $categories = \App\Models\Category::with('children')
                                    ->whereNull('parent_id')
                                    ->active()
                                    ->ordered()
                                    ->get();
                            @endphp
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @foreach($category->children as $child)
                                    <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                        &nbsp;&nbsp;{{ $child->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="brand_id" class="block text-sm font-medium text-gray-700">Marka</label>
                        <select name="brand_id" 
                                id="brand_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('brand_id') border-red-300 @enderror">
                            <option value="">Marka Seçin</option>
                            @php
                                $brands = \App\Models\Brand::active()->ordered()->get();
                            @endphp
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Ürün Tipi Seçimi -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ürün Tipi ve Birim</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Ürün Tipi *</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input id="product_type_simple" 
                                       name="product_type" 
                                       type="radio" 
                                       value="simple"
                                       x-model="form.productType"
                                       {{ old('product_type', 'simple') == 'simple' ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="product_type_simple" class="ml-2 block text-sm text-gray-900">
                                    <span class="font-medium">Basit Ürün</span>
                                    <span class="block text-xs text-gray-500">Tek varyant, direkt stok yönetimi</span>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="product_type_variable" 
                                       name="product_type" 
                                       type="radio" 
                                       value="variable"
                                       x-model="form.productType"
                                       {{ old('product_type') == 'variable' ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="product_type_variable" class="ml-2 block text-sm text-gray-900">
                                    <span class="font-medium">Varyantlı Ürün</span>
                                    <span class="block text-xs text-gray-500">Farklı özellikler (renk, beden vb.)</span>
                                </label>
                            </div>
                        </div>
                        @error('product_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="unit_id" class="block text-sm font-medium text-gray-700">Birim *</label>
                        <select name="unit_id" 
                                id="unit_id" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('unit_id') border-red-300 @enderror">
                            <option value="">Birim Seçin</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->display_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Tüm varyantlar bu birimi kullanacak</p>
                    </div>
                </div>
            </div>
            
            <!-- Basit Ürün - Stok ve Fiyat -->
            <div x-show="form.productType === 'simple'" class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Stok ve Fiyat Bilgileri</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="simple_sku" class="block text-sm font-medium text-gray-700">SKU *</label>
                        <input type="text" 
                               name="simple_sku" 
                               id="simple_sku" 
                               value="{{ old('simple_sku') }}"
                               x-bind:required="form.productType === 'simple'"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('simple_sku') border-red-300 @enderror">
                        @error('simple_sku')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="simple_price" class="block text-sm font-medium text-gray-700">Fiyat (TL) *</label>
                        <input type="number" 
                               name="simple_price" 
                               id="simple_price" 
                               step="0.01"
                               min="0"
                               value="{{ old('simple_price') }}"
                               x-bind:required="form.productType === 'simple'"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('simple_price') border-red-300 @enderror">
                        @error('simple_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Stok Miktarı *</label>
                        <input type="number" 
                               name="stock_quantity" 
                               id="stock_quantity" 
                               step="0.001"
                               min="0"
                               value="{{ old('stock_quantity', 0) }}"
                               x-bind:required="form.productType === 'simple'"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('stock_quantity') border-red-300 @enderror">
                        @error('stock_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Ondalık değerler desteklenir (0.001 hassasiyet)</p>
                    </div>
                </div>
            </div>

            <!-- Varyantlı Ürün - Özellik Seçimi -->
            <div x-show="form.productType === 'variable'" class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Varyant Özellikleri</h3>
                
                <div class="space-y-4">
                    <p class="text-sm text-gray-600">
                        Ürününüz için hangi özelliklerin varyant oluşturacağını seçin (renk, beden, stil vb.).
                    </p>
                    
                    @php
                        $attributes = \App\Models\ProductAttribute::active()->variation()->ordered()->with('activeValues')->get();
                    @endphp
                    
                    @foreach($attributes as $attribute)
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" 
                                       name="selected_attributes[]" 
                                       value="{{ $attribute->id }}"
                                       id="attr_{{ $attribute->id }}"
                                       x-model="form.selectedAttributes"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="attr_{{ $attribute->id }}" class="ml-2 block text-sm font-medium text-gray-700">
                                    {{ $attribute->name }}
                                </label>
                            </div>
                            
                            <div x-show="form.selectedAttributes.includes('{{ $attribute->id }}')"
                                 class="grid grid-cols-2 md:grid-cols-4 gap-2 ml-6">
                                @foreach($attribute->activeValues as $value)
                                    <label class="flex items-center space-x-2 text-sm">
                                        <input type="checkbox" 
                                               name="attribute_values[{{ $attribute->id }}][]" 
                                               value="{{ $value->id }}"
                                               class="h-3 w-3 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        @if($attribute->type === 'color')
                                            <span class="w-4 h-4 rounded border" style="background-color: {{ $value->color_code }}"></span>
                                        @endif
                                        <span>{{ $value->value }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Bilgi</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Özellik seçtikten sonra, bir sonraki adımda her kombinasyon için fiyat ve stok bilgilerini girebileceksiniz.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Görsel Yükleme -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ürün Görselleri</h3>
                
                <div x-data="imageUploader()" class="space-y-4">
                    <!-- Dosya Seçici -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Görseller Seç (En fazla 10 adet)</label>
                        <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="images" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                        <span>Dosya seç</span>
                                        <input id="images" 
                                               name="images[]" 
                                               type="file" 
                                               multiple 
                                               accept="image/jpeg,image/png,image/gif,image/webp"
                                               @change="handleFileSelect($event)"
                                               class="sr-only">
                                    </label>
                                    <p class="pl-1">veya sürükle bırak</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF, WebP (Max: 10MB)</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seçilen Görseller Önizlemesi -->
                    <div x-show="selectedImages.length > 0" class="space-y-2">
                        <h4 class="text-sm font-medium text-gray-700">Seçilen Görseller:</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <template x-for="(image, index) in selectedImages" :key="index">
                                <div class="relative group">
                                    <img :src="image.preview" 
                                         :alt="`Görsel ${index + 1}`"
                                         class="w-full h-24 object-cover rounded-lg border">
                                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                        <button type="button" 
                                                @click="removeImage(index)"
                                                class="text-white hover:text-red-300">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="mt-1">
                                        <input type="text" 
                                               :name="`image_alt_texts[${index}]`"
                                               placeholder="Alt text"
                                               class="text-xs w-full rounded border-gray-300">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                
                @error('images')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('images.*')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <!-- Yan Panel -->
        <div class="space-y-6">
            <!-- Durum -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Durum</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input id="is_active" 
                               name="is_active" 
                               type="checkbox" 
                               value="1"
                               {{ old('is_active', 1) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Aktif
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input id="featured" 
                               name="featured" 
                               type="checkbox" 
                               value="1"
                               {{ old('featured') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="featured" class="ml-2 block text-sm text-gray-900">
                            Öne Çıkan Ürün
                        </label>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Ayarları</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="meta_title" class="block text-sm font-medium text-gray-700">Meta Başlık</label>
                        <input type="text" 
                               name="meta_title" 
                               id="meta_title" 
                               value="{{ old('meta_title') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="meta_description" class="block text-sm font-medium text-gray-700">Meta Açıklama</label>
                        <textarea name="meta_description" 
                                  id="meta_description" 
                                  rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('meta_description') }}</textarea>
                    </div>
                    
                    <div>
                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700">Meta Anahtar Kelimeler</label>
                        <input type="text" 
                               name="meta_keywords" 
                               id="meta_keywords" 
                               value="{{ old('meta_keywords') }}"
                               placeholder="virgülle ayırın"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="bg-white shadow rounded-lg p-6">
                <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Ürünü Kaydet
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function productForm() {
    return {
        form: {
            name: '',
            slug: '',
            productType: 'simple',
            selectedAttributes: []
        },
        updateSlug() {
            if (this.form.name && !this.form.slug) {
                this.form.slug = this.form.name
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim('-');
            }
        },
        init() {
            // Initialize product type from old value if exists
            const oldProductType = '{{ old("product_type", "simple") }}';
            this.form.productType = oldProductType;
            
            // Initialize selected attributes from old values if exists
            @if(old('selected_attributes'))
                this.form.selectedAttributes = @json(old('selected_attributes'));
            @endif
        }
    }
}

function imageUploader() {
    return {
        selectedImages: [],
        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            
            // Maksimum 10 dosya kontrolü
            if (files.length > 10) {
                alert('En fazla 10 görsel seçebilirsiniz.');
                return;
            }
            
            this.selectedImages = [];
            
            files.forEach((file, index) => {
                // Dosya türü kontrolü
                if (!file.type.startsWith('image/')) {
                    alert(`${file.name} geçerli bir görsel dosyası değil.`);
                    return;
                }
                
                // Dosya boyutu kontrolü (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert(`${file.name} çok büyük. Maksimum 10MB olmalı.`);
                    return;
                }
                
                // Önizleme oluştur
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.selectedImages.push({
                        file: file,
                        preview: e.target.result,
                        name: file.name
                    });
                };
                reader.readAsDataURL(file);
            });
        },
        removeImage(index) {
            this.selectedImages.splice(index, 1);
            
            // Input'u temizle ve yeniden doldur
            const input = document.getElementById('images');
            const dt = new DataTransfer();
            
            this.selectedImages.forEach(img => {
                dt.items.add(img.file);
            });
            
            input.files = dt.files;
        }
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        const productType = document.querySelector('input[name="product_type"]:checked')?.value;
        
        if (productType === 'simple') {
            // Simple product validation
            const requiredFields = ['simple_sku', 'simple_price', 'stock_quantity'];
            let hasError = false;
            
            requiredFields.forEach(field => {
                const input = document.querySelector(`input[name="${field}"]`);
                if (!input || !input.value.trim()) {
                    hasError = true;
                    if (input) {
                        input.classList.add('border-red-300');
                    }
                }
            });
            
            if (hasError) {
                e.preventDefault();
                alert('Lütfen tüm gerekli alanları doldurun.');
                return;
            }
        } else if (productType === 'variable') {
            // Variable product validation
            const selectedAttributes = document.querySelectorAll('input[name="selected_attributes[]"]:checked');
            
            if (selectedAttributes.length === 0) {
                e.preventDefault();
                alert('Varyantlı ürün için en az bir özellik seçmelisiniz.');
                return;
            }
            
            // Check if at least one value is selected for each attribute
            let hasAllValues = true;
            selectedAttributes.forEach(attr => {
                const attrId = attr.value;
                const values = document.querySelectorAll(`input[name="attribute_values[${attrId}][]"]:checked`);
                if (values.length === 0) {
                    hasAllValues = false;
                }
            });
            
            if (!hasAllValues) {
                e.preventDefault();
                alert('Seçilen her özellik için en az bir değer seçmelisiniz.');
                return;
            }
        }
    });
});
</script>
@endpush