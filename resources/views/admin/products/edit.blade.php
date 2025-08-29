@extends('layouts.admin')

@section('title', 'Ürün Düzenle: ' . $product->name)

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Ürün Düzenle: {{ $product->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Ürünler</a></li>
                    <li class="breadcrumb-item active">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>Geri Dön
            </a>
            <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="btn btn-outline-info">
                <i class="fas fa-external-link-alt me-1"></i>Önizle
            </a>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Lütfen aşağıdaki hataları düzeltin:</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="product-form">
        @csrf
        @method('PUT')
        
        {{-- PrestaShop Style Tabs --}}
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs" id="product-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="fas fa-info-circle me-2"></i>Genel Bilgiler
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab">
                            <i class="fas fa-search me-2"></i>SEO & URL
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images" type="button" role="tab">
                            <i class="fas fa-images me-2"></i>Görseller
                            <span class="badge bg-info ms-1">{{ $product->images->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button" role="tab">
                            <i class="fas fa-layer-group me-2"></i>Varyantlar
                            <span class="badge bg-secondary ms-1">{{ $product->variants->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="associations-tab" data-bs-toggle="tab" data-bs-target="#associations" type="button" role="tab">
                            <i class="fas fa-link me-2"></i>İlişkiler
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="product-tab-content">
                    {{-- General Tab --}}
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-edit text-primary me-2"></i>Temel Bilgiler</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label required">Ürün Adı</label>
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                           id="name" name="name" value="{{ old('name', $product->name) }}" 
                                                           maxlength="255" required>
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Ürününüzün müşterilere gösterilecek adı</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sku" class="form-label">SKU (Stok Kodu)</label>
                                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                                           id="sku" name="sku" value="{{ old('sku', $product->sku) }}" 
                                                           maxlength="100">
                                                    @error('sku')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Benzersiz ürün kodu (boş bırakılırsa otomatik oluşturulur)</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Açıklama</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Ürünün detaylı açıklaması</div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="short_description" class="form-label">Kısa Açıklama</label>
                                                    <textarea class="form-control @error('short_description') is-invalid @enderror" 
                                                              id="short_description" name="short_description" rows="3" 
                                                              maxlength="500">{{ old('short_description', $product->short_description) }}</textarea>
                                                    @error('short_description')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Ürün listelerinde gösterilecek kısa açıklama</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="product_type" class="form-label">Ürün Türü</label>
                                                    <select class="form-select @error('product_type') is-invalid @enderror" 
                                                            id="product_type" name="product_type">
                                                        <option value="simple" {{ old('product_type', $product->product_type) == 'simple' ? 'selected' : '' }}>Basit Ürün</option>
                                                        <option value="variable" {{ old('product_type', $product->product_type) == 'variable' ? 'selected' : '' }}>Varyantlı Ürün</option>
                                                    </select>
                                                    @error('product_type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Ürünün türünü belirler (basit veya varyantlı)</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                {{-- Settings Card --}}
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-cog text-primary me-2"></i>Ayarlar</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                                       value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    <span class="fw-semibold">Aktif</span>
                                                    <div class="text-muted small">Ürün sitede gösterilsin mi?</div>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                                       value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_featured">
                                                    <span class="fw-semibold">Öne Çıkan</span>
                                                    <div class="text-muted small">Ana sayfada öne çıkarılsın mı?</div>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">Kategori</label>
                                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                                    id="category_id" name="category_id">
                                                <option value="">Kategori Seçin</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" 
                                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                        {{ str_repeat('— ', $category->depth ?? 0) }}{{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="brand_id" class="form-label">Marka</label>
                                            <select class="form-select @error('brand_id') is-invalid @enderror" 
                                                    id="brand_id" name="brand_id">
                                                <option value="">Marka Seçin</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" 
                                                            {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                                        {{ $brand->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('brand_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label">Sıralama</label>
                                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                                   id="sort_order" name="sort_order" 
                                                   value="{{ old('sort_order', $product->sort_order ?? 0) }}" 
                                                   min="0" max="9999">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Listeleme sırası (düşük numara önce gelir)</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Actions --}}
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i>Hızlı İşlemler</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary" id="save-product-btn">
                                                <i class="fas fa-save me-1"></i>Değişiklikleri Kaydet
                                            </button>
                                            <button type="button" class="btn btn-outline-success" onclick="duplicateProduct()">
                                                <i class="fas fa-copy me-1"></i>Ürünü Kopyala
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteProduct()">
                                                <i class="fas fa-trash me-1"></i>Ürünü Sil
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SEO Tab --}}
                    @include('admin.products.partials.seo-tab')

                    {{-- Images Tab --}}
                    @include('admin.products.partials.images-tab')

                    {{-- Variants Tab --}}
                    @include('admin.products.partials.variants-tab')

                    {{-- Associations Tab --}}
                    @include('admin.products.partials.associations-tab')
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function duplicateProduct() {
    if (confirm('Bu ürünün kopyasını oluşturmak istediğinizden emin misiniz?')) {
        window.location.href = '/admin/products/{{ $product->id }}/duplicate';
    }
}

function deleteProduct() {
    if (confirm('Bu ürünü silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/products/{{ $product->id }}';
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-save draft functionality
let autoSaveTimeout;
function scheduleAutoSave() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(function() {
        saveDraft();
    }, 30000); // Auto-save every 30 seconds
}

function saveDraft() {
    const formData = new FormData(document.getElementById('product-form'));
    
    fetch('/admin/products/{{ $product->id }}/save-draft', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Draft saved automatically');
        }
    })
    .catch(error => {
        console.error('Auto-save error:', error);
    });
}

// Listen for form changes to trigger auto-save
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('product-form');
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('input', scheduleAutoSave);
        input.addEventListener('change', scheduleAutoSave);
    });
});
</script>

@endsection
</div>

@push('scripts')
<script>
// Slug generation
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.getElementById('slug').value = slug;
});

// Image upload preview
document.getElementById('images').addEventListener('change', function() {
    const previewContainer = document.getElementById('image-previews');
    previewContainer.innerHTML = '';
    
    Array.from(this.files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full aspect-square object-cover rounded-lg border">
                    <button type="button" onclick="this.parentElement.remove()" 
                            class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700">
                        ×
                    </button>
                    <input type="text" name="new_image_alts[]" placeholder="Alt text" 
                           class="mt-2 w-full px-2 py-1 text-xs border border-gray-300 rounded">
                `;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    });
});

// Image management functions
function deleteImage(imageId) {
    if (confirm('Are you sure you want to delete this image?')) {
        fetch(`/admin/products/images/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-image-id="${imageId}"]`).remove();
            } else {
                alert('Error deleting image');
            }
        })
        .catch(() => alert('Error deleting image'));
    }
}

function setCoverImage(imageId) {
    fetch(`/admin/products/images/${imageId}/cover`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update all buttons
            document.querySelectorAll('[data-image-id] button').forEach(btn => {
                if (btn.textContent.includes('Cover')) {
                    btn.textContent = 'Set Cover';
                    btn.classList.remove('bg-green-600');
                    btn.classList.add('bg-blue-600');
                }
            });
            // Update the clicked button
            const button = document.querySelector(`[data-image-id="${imageId}"] button`);
            button.textContent = 'Cover';
            button.classList.remove('bg-blue-600');
            button.classList.add('bg-green-600');
        } else {
            alert('Error setting cover image');
        }
    })
    .catch(() => alert('Error setting cover image'));
}

// Drag and drop for image upload
const uploadArea = document.getElementById('image-upload-area');
const fileInput = document.getElementById('images');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    uploadArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, unhighlight, false);
});

function highlight() {
    uploadArea.classList.add('border-blue-500', 'bg-blue-50');
}

function unhighlight() {
    uploadArea.classList.remove('border-blue-500', 'bg-blue-50');
}

uploadArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    fileInput.files = files;
    fileInput.dispatchEvent(new Event('change'));
}
</script>
@endpush
@endsection