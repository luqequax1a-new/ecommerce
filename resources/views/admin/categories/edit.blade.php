@extends('admin.layouts.app')

@section('title', $category->exists ? 'Kategori Düzenle: ' . $category->name : 'Yeni Kategori')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                {{ $category->exists ? 'Kategori Düzenle' : 'Yeni Kategori' }}
            </h1>
            @if($category->exists)
                <p class="text-muted">{{ $category->name }} kategorisini düzenliyorsunuz</p>
            @endif
        </div>
        <div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Geri Dön
            </a>
            @if($category->exists)
                <a href="{{ route('category.show', $category->slug) }}" class="btn btn-outline-info" target="_blank">
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

    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" 
          enctype="multipart/form-data">
        @csrf
        @if($category->exists)
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
                                    @if($category->exists && !$seoAnalysis['title_optimal'] || !$seoAnalysis['description_optimal'])
                                        <span class="badge bg-warning">!</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="associations-tab" data-bs-toggle="tab" href="#associations" role="tab">
                                    <i class="fas fa-sitemap"></i> İlişkilendirmeler
                                </a>
                            </li>
                            @if($category->exists)
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
                                            <label for="name" class="form-label">Kategori Adı *</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name', $category->name) }}" 
                                                   required onkeyup="generateSlugPreview()">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="short_description" class="form-label">Kısa Açıklama</label>
                                            <textarea class="form-control @error('short_description') is-invalid @enderror" 
                                                      id="short_description" name="short_description" rows="3" 
                                                      maxlength="500">{{ old('short_description', $category->short_description) }}</textarea>
                                            <div class="form-text">Maksimum 500 karakter</div>
                                            @error('short_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Detaylı Açıklama</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" rows="8">{{ old('description', $category->description) }}</textarea>
                                            @error('description')
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
                                                       id="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">Aktif</label>
                                            </div>
                                        </div>

                                        <!-- Parent Category -->
                                        <div class="mb-3">
                                            <label for="parent_id" class="form-label">Üst Kategori</label>
                                            <select class="form-select @error('parent_id') is-invalid @enderror" 
                                                    id="parent_id" name="parent_id">
                                                <option value="">Ana Kategori</option>
                                                @foreach($availableParents as $parent)
                                                    <option value="{{ $parent->id }}" 
                                                            {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                                        {{ str_repeat('- ', $parent->level) }}{{ $parent->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('parent_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Sort Order -->
                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label">Sıralama</label>
                                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                                   id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Display Options -->
                                        <div class="mb-3">
                                            <label class="form-label">Görünüm Seçenekleri</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="show_in_menu" 
                                                       id="show_in_menu" value="1" {{ old('show_in_menu', $category->show_in_menu) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_menu">Menüde Göster</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="show_in_footer" 
                                                       id="show_in_footer" value="1" {{ old('show_in_footer', $category->show_in_footer) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="show_in_footer">Footer'da Göster</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="featured" 
                                                       id="featured" value="1" {{ old('featured', $category->featured) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="featured">Öne Çıkan</label>
                                            </div>
                                        </div>

                                        <!-- Images -->
                                        <div class="mb-3">
                                            <label for="cover_image" class="form-label">Kapak Görseli</label>
                                            @if($category->image_path)
                                                <div class="mb-2">
                                                    <img src="{{ Storage::url($category->image_path) }}" 
                                                         class="img-thumbnail" style="max-width: 150px;">
                                                </div>
                                            @endif
                                            <input type="file" class="form-control @error('cover_image') is-invalid @enderror" 
                                                   id="cover_image" name="cover_image" accept="image/*">
                                            <div class="form-text">PNG, JPG, WEBP - Maksimum 2MB</div>
                                            @error('cover_image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="banner_image" class="form-label">Banner Görseli</label>
                                            @if($category->banner_path)
                                                <div class="mb-2">
                                                    <img src="{{ Storage::url($category->banner_path) }}" 
                                                         class="img-thumbnail" style="max-width: 150px;">
                                                </div>
                                            @endif
                                            <input type="file" class="form-control @error('banner_image') is-invalid @enderror" 
                                                   id="banner_image" name="banner_image" accept="image/*">
                                            <div class="form-text">PNG, JPG, WEBP - Maksimum 5MB</div>
                                            @error('banner_image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Tab -->
                            <div class="tab-pane fade" id="seo" role="tabpanel">
                                @include('admin.categories.partials.seo-tab')
                            </div>

                            <!-- Associations Tab -->
                            <div class="tab-pane fade" id="associations" role="tabpanel">
                                @include('admin.categories.partials.associations-tab')
                            </div>

                            @if($category->exists)
                            <!-- Advanced Tab -->
                            <div class="tab-pane fade" id="advanced" role="tabpanel">
                                @include('admin.categories.partials.advanced-tab')
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <div>
                                @if($category->exists)
                                    <small class="text-muted">
                                        Son güncelleme: {{ $category->updated_at->format('d.m.Y H:i') }}
                                    </small>
                                @endif
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ $category->exists ? 'Güncelle' : 'Kaydet' }}
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
    const parentId = document.getElementById('parent_id').value;
    const excludeId = {{ $category->id ?? 'null' }};
    
    if (name.length > 2) {
        fetch('/admin/categories/generate-slug', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                name: name,
                parent_id: parentId,
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
        preview.innerHTML = `<strong>URL:</strong> <a href="/kategori/${slug}" target="_blank">/kategori/${slug}</a>`;
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