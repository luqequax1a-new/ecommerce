@extends('admin.layouts.app')

@section('title', $attribute->exists ? 'Özellik Düzenle: ' . $attribute->name : 'Yeni Özellik')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                {{ $attribute->exists ? 'Özellik Düzenle' : 'Yeni Özellik' }}
            </h1>
            @if($attribute->exists)
                <p class="text-muted">{{ $attribute->name }} özelliğini düzenliyorsunuz</p>
            @endif
        </div>
        <div>
            <a href="{{ route('admin.attributes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Geri Dön
            </a>
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

    <form method="POST" action="{{ $attribute->exists ? route('admin.attributes.update', $attribute) : route('admin.attributes.store') }}">
        @csrf
        @if($attribute->exists)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-8">
                <!-- Attribute Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i> Özellik Bilgileri
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Özellik Adı *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $attribute->name) }}" 
                                           required onkeyup="generateSlugPreview()">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="slug" class="form-label">URL Slug</label>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                           id="slug" name="slug" value="{{ old('slug', $attribute->slug) }}">
                                    <div class="form-text">Boş bırakılırsa otomatik oluşturulur</div>
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Özellik Tipi *</label>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" name="type" required onchange="handleTypeChange()">
                                        <option value="">Özellik tipini seçin</option>
                                        <option value="text" {{ old('type', $attribute->type) === 'text' ? 'selected' : '' }}>
                                            Metin
                                        </option>
                                        <option value="color" {{ old('type', $attribute->type) === 'color' ? 'selected' : '' }}>
                                            Renk
                                        </option>
                                        <option value="image" {{ old('type', $attribute->type) === 'image' ? 'selected' : '' }}>
                                            Resim
                                        </option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sıralama</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $attribute->sort_order ?? 0) }}" min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($attribute->exists && $attribute->values->count() > 0)
                <!-- Attribute Values -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list"></i> Özellik Değerleri
                        </h5>
                        <button type="button" class="btn btn-sm btn-primary" onclick="showAddValueModal()">
                            <i class="fas fa-plus"></i> Değer Ekle
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <i class="fas fa-sort text-muted" title="Sürükleyerek sıralayın"></i>
                                        </th>
                                        <th>Değer</th>
                                        <th width="100">Önizleme</th>
                                        <th width="80">Durum</th>
                                        <th width="120">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody id="sortableValues">
                                    @foreach($attribute->values as $value)
                                        <tr data-value-id="{{ $value->id }}">
                                            <td class="text-center">
                                                <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: move;"></i>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $value->value }}</strong>
                                                    <br><small class="text-muted">{{ $value->slug }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($value->color_code)
                                                    <div style="width: 30px; height: 30px; background-color: {{ $value->color_code }}; border: 1px solid #dee2e6; border-radius: 4px;"></div>
                                                @elseif($value->image_path)
                                                    <img src="{{ Storage::url($value->image_path) }}" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;">
                                                @else
                                                    <i class="fas fa-font text-muted"></i>
                                                @endif
                                            </td>
                                            <td>
                                                @if($value->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Pasif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            title="Düzenle" onclick="editValue({{ $value->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            title="Sil" onclick="deleteValue({{ $value->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-4">
                <!-- Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs"></i> Ayarlar
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       id="is_active" value="1" {{ old('is_active', $attribute->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_variation" 
                                       id="is_variation" value="1" {{ old('is_variation', $attribute->is_variation ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_variation">Varyant Özelliği</label>
                            </div>
                            <div class="form-text">Bu özellik ürün varyantları oluşturmak için kullanılsın mı?</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_required" 
                                       id="is_required" value="1" {{ old('is_required', $attribute->is_required) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_required">Zorunlu</label>
                            </div>
                            <div class="form-text">Bu özellik ürün oluştururken zorunlu mu?</div>
                        </div>
                    </div>
                </div>

                @if($attribute->exists)
                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar"></i> İstatistikler
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Toplam Değer:</span>
                            <strong>{{ $attribute->values->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Aktif Değer:</span>
                            <strong>{{ $attribute->values->where('is_active', true)->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Son Güncelleme:</span>
                            <strong>{{ $attribute->updated_at->format('d.m.Y') }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Add Value Quick Action -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus"></i> Hızlı Değer Ekle
                        </h5>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary w-100" onclick="showAddValueModal()">
                            <i class="fas fa-plus"></i> Yeni Değer Ekle
                        </button>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="card mt-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save"></i> {{ $attribute->exists ? 'Güncelle' : 'Kaydet' }}
                        </button>
                        @if($attribute->exists)
                            <a href="{{ route('admin.attributes.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times"></i> İptal
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@if($attribute->exists)
<!-- Add/Edit Value Modal -->
<div class="modal fade" id="valueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="valueModalTitle">Yeni Değer Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="valueForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="value_value" class="form-label">Değer *</label>
                        <input type="text" class="form-control" id="value_value" name="value" required>
                    </div>

                    <div class="mb-3">
                        <label for="value_slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="value_slug" name="slug">
                        <div class="form-text">Boş bırakılırsa otomatik oluşturulur</div>
                    </div>

                    @if($attribute->type === 'color')
                    <div class="mb-3">
                        <label for="value_color_code" class="form-label">Renk Kodu *</label>
                        <input type="color" class="form-control form-control-color" id="value_color_code" name="color_code">
                    </div>
                    @endif

                    @if($attribute->type === 'image')
                    <div class="mb-3">
                        <label for="value_image" class="form-label">Resim *</label>
                        <input type="file" class="form-control" id="value_image" name="image" accept="image/*">
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="value_sort_order" class="form-label">Sıralama</label>
                        <input type="number" class="form-control" id="value_sort_order" name="sort_order" value="0" min="0">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="value_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="value_is_active">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($attribute->exists && $attribute->values->count() > 0)
    // Initialize sortable for values
    const sortable = Sortable.create(document.getElementById('sortableValues'), {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function(evt) {
            updateValueOrder();
        }
    });
    @endif
});

function generateSlugPreview() {
    const name = document.getElementById('name').value;
    if (name.length > 2) {
        const slug = name.toLowerCase()
            .replace(/ç/g, 'c').replace(/ğ/g, 'g').replace(/ı/g, 'i')
            .replace(/ö/g, 'o').replace(/ş/g, 's').replace(/ü/g, 'u')
            .replace(/[^a-z0-9]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        document.getElementById('slug').value = slug;
    }
}

function handleTypeChange() {
    // Type change handling can be added here if needed
}

@if($attribute->exists)
function updateValueOrder() {
    const rows = document.querySelectorAll('#sortableValues tr');
    const valueIds = Array.from(rows).map(row => row.dataset.valueId);
    
    fetch(`/admin/attributes/{{ $attribute->id }}/values/update-order`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            value_ids: valueIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Değer sırası güncellendi', 'success');
        }
    })
    .catch(error => {
        console.error('Error updating order:', error);
        showToast('Sıra güncellenirken hata oluştu', 'error');
    });
}

function showAddValueModal() {
    document.getElementById('valueModalTitle').textContent = 'Yeni Değer Ekle';
    document.getElementById('valueForm').action = `/admin/attributes/{{ $attribute->id }}/values`;
    document.getElementById('valueForm').method = 'POST';
    
    // Clear form
    document.getElementById('valueForm').reset();
    document.getElementById('value_is_active').checked = true;
    
    new bootstrap.Modal(document.getElementById('valueModal')).show();
}

function editValue(valueId) {
    // Implementation for editing values would go here
    // For now, we'll just show the add modal
    showAddValueModal();
}

function deleteValue(valueId) {
    if (confirm('Bu değeri silmek istediğinizden emin misiniz?')) {
        fetch(`/admin/attributes/{{ $attribute->id }}/values/${valueId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting value:', error);
            showToast('Silme işlemi başarısız', 'error');
        });
    }
}
@endif

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 5000);
}
</script>
@endpush
@endsection