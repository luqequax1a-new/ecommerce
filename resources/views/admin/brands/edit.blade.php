@extends('admin.layouts.app')

@section('title', $brand->exists ? 'Marka Düzenle: ' . $brand->name : 'Yeni Marka')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                {{ $brand->exists ? 'Marka Düzenle' : 'Yeni Marka' }}
            </h1>
            @if($brand->exists)
                <p class="text-muted">{{ $brand->name }} markasını düzenliyorsunuz</p>
            @endif
        </div>
        <div>
            <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Geri Dön
            </a>
            @if($brand->exists)
                <a href="{{ route('brand.show', $brand->slug) }}" class="btn btn-outline-info" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Önizle
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" 
          enctype="multipart/form-data">
        @csrf
        @if($brand->exists)
            @method('PUT')
        @endif

        <!-- PrestaShop Style Tabs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab">
                                    <i class="fas fa-info-circle"></i> Genel
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="seo-tab" data-bs-toggle="tab" href="#seo" role="tab">
                                    <i class="fas fa-search"></i> SEO & URL
                                    @if($brand->exists && !empty($seoAnalysis) && (!$seoAnalysis['title_optimal'] || !$seoAnalysis['description_optimal']))
                                        <span class="badge bg-warning">!</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="products-tab" data-bs-toggle="tab" href="#products" role="tab">
                                    <i class="fas fa-box"></i> Ürünler
                                    @if($brand->exists)
                                        <span class="badge bg-primary">{{ $brand->products_count ?? 0 }}</span>
                                    @endif
                                </a>
                            </li>
                            @if($brand->exists)
                            <li class="nav-item">
                                <a class="nav-link" id="advanced-tab" data-bs-toggle="tab" href="#advanced" role="tab">
                                    <i class="fas fa-cogs"></i> Gelişmiş
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">
                            <!-- General Tab -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Marka Adı *</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name', $brand->name) }}" 
                                                   required onkeyup="generateSlugPreview()">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="short_description" class="form-label">Kısa Açıklama</label>
                                            <textarea class="form-control @error('short_description') is-invalid @enderror" 
                                                      id="short_description" name="short_description" rows="3" 
                                                      maxlength="500">{{ old('short_description', $brand->short_description) }}</textarea>
                                            <div class="form-text">Maksimum 500 karakter</div>
                                            @error('short_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Detaylı Açıklama</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" rows="8">{{ old('description', $brand->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Contact Information -->
                                        <h6 class="border-bottom pb-2 mb-3">İletişim Bilgileri</h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="website_url" class="form-label">Web Sitesi</label>
                                                    <input type="url" class="form-control @error('website_url') is-invalid @enderror" 
                                                           id="website_url" name="website_url" 
                                                           value="{{ old('website_url', $brand->website_url) }}"
                                                           placeholder="https://example.com">
                                                    @error('website_url')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">E-posta</label>
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                           id="email" name="email" 
                                                           value="{{ old('email', $brand->email) }}"
                                                           placeholder="info@brand.com">
                                                    @error('email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Telefon</label>
                                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" name="phone" 
                                                   value="{{ old('phone', $brand->phone) }}"
                                                   placeholder="+90 212 555 0000">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <!-- Status -->
                                        <div class="mb-3">
                                            <label class="form-label">Durum</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active" 
                                                       id="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">Aktif</label>
                                            </div>
                                        </div>

                                        <!-- Sort Order -->
                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label">Sıralama</label>
                                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                                   id="sort_order" name="sort_order" value="{{ old('sort_order', $brand->sort_order ?? 0) }}" min="0">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Logo Upload -->
                                        <div class="mb-3">
                                            <label for="logo" class="form-label">Marka Logosu</label>
                                            @if($brand->logo_path)
                                                <div class="mb-3 text-center">
                                                    <img src="{{ Storage::url($brand->logo_path) }}" 
                                                         class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                                                    <div class="mt-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLogo()">
                                                            <i class="fas fa-trash"></i> Logoyu Sil
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                            <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                                   id="logo" name="logo" accept="image/*" onchange="previewLogo(this)">
                                            <div class="form-text">PNG, JPG, WEBP, SVG - Maksimum 2MB, 50x50 - 1000x1000 piksel</div>
                                            @error('logo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            
                                            <!-- Logo Preview -->
                                            <div id="logoPreview" class="mt-2" style="display: none;">
                                                <img id="logoPreviewImage" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Tab -->
                            <div class="tab-pane fade" id="seo" role="tabpanel">
                                @include('admin.brands.partials.seo-tab')
                            </div>

                            <!-- Products Tab -->
                            <div class="tab-pane fade" id="products" role="tabpanel">
                                @include('admin.brands.partials.products-tab')
                            </div>

                            @if($brand->exists)
                            <!-- Advanced Tab -->
                            <div class="tab-pane fade" id="advanced" role="tabpanel">
                                @include('admin.brands.partials.advanced-tab')
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div>
                                @if($brand->exists)
                                    <small class="text-muted">
                                        Son güncelleme: {{ $brand->updated_at->format('d.m.Y H:i') }}
                                    </small>
                                @endif
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ $brand->exists ? 'Güncelle' : 'Kaydet' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function generateSlugPreview() {
    const name = document.getElementById('name').value;
    const excludeId = {{ $brand->id ?? 'null' }};
    
    if (name.length > 2) {
        fetch('/admin/brands/generate-slug', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                name: name,
                exclude_id: excludeId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.slug) {
                document.getElementById('slug').value = data.slug;
                updateUrlPreview();
            }
        });
    }
}

function updateUrlPreview() {
    const slug = document.getElementById('slug').value;
    const preview = document.getElementById('urlPreview');
    if (preview && slug) {
        preview.innerHTML = `<strong>URL:</strong> <a href="/marka/${slug}" target="_blank">/marka/${slug}</a>`;
    }
}

function previewLogo(input) {
    const preview = document.getElementById('logoPreview');
    const previewImage = document.getElementById('logoPreviewImage');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeLogo() {
    if (confirm('Bu logoyu silmek istediğinizden emin misiniz?')) {
        // Add hidden input to mark for deletion
        const form = document.querySelector('form');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_logo';
        input.value = '1';
        form.appendChild(input);
        
        // Hide the logo container
        event.target.closest('.mb-3').querySelector('img').closest('div').style.display = 'none';
    }
}

// Auto update slug checkbox handler
document.addEventListener('DOMContentLoaded', function() {
    const autoUpdateSlug = document.getElementById('auto_update_slug');
    const slugField = document.getElementById('slug');
    
    if (autoUpdateSlug && slugField) {
        function toggleSlugField() {
            slugField.readOnly = autoUpdateSlug.checked;
            if (autoUpdateSlug.checked) {
                slugField.classList.add('bg-light');
            } else {
                slugField.classList.remove('bg-light');
            }
        }
        
        autoUpdateSlug.addEventListener('change', toggleSlugField);
        toggleSlugField(); // Initial state
    }
    
    updateUrlPreview();
});
</script>
@endpush
@endsection