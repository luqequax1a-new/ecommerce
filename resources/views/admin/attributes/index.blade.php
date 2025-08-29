@extends('admin.layouts.app')

@section('title', 'Ürün Özellikleri')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Ürün Özellikleri</h1>
            <p class="text-muted">Ürün varyantları için kullanılacak özellikleri yönetin</p>
        </div>
        <div>
            <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Özellik Ekle
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Attributes Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-tags"></i> Özellikler
            </h5>
        </div>
        <div class="card-body">
            @if($attributes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="attributesTable">
                        <thead>
                            <tr>
                                <th width="50">
                                    <i class="fas fa-sort text-muted" title="Sürükleyerek sıralayın"></i>
                                </th>
                                <th>Özellik Adı</th>
                                <th width="100">Tip</th>
                                <th width="100">Değer Sayısı</th>
                                <th width="80">Gerekli</th>
                                <th width="80">Varyant</th>
                                <th width="80">Durum</th>
                                <th width="150">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="sortableAttributes">
                            @foreach($attributes as $attribute)
                                <tr data-attribute-id="{{ $attribute->id }}">
                                    <td class="text-center">
                                        <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: move;"></i>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ $attribute->name }}</h6>
                                                <small class="text-muted">{{ $attribute->slug }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @switch($attribute->type)
                                            @case('text')
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-font"></i> Metin
                                                </span>
                                                @break
                                            @case('color')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-palette"></i> Renk
                                                </span>
                                                @break
                                            @case('image')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-image"></i> Resim
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $attribute->values_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($attribute->is_required)
                                            <i class="fas fa-check text-success" title="Gerekli"></i>
                                        @else
                                            <i class="fas fa-times text-muted" title="İsteğe bağlı"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($attribute->is_variation)
                                            <i class="fas fa-check text-success" title="Varyant özelliği"></i>
                                        @else
                                            <i class="fas fa-times text-muted" title="Varyant özelliği değil"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attribute->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Pasif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.attributes.edit', $attribute) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    title="Değerleri Görüntüle" onclick="showAttributeValues({{ $attribute->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    title="Sil" onclick="confirmDelete({{ $attribute->id }}, '{{ $attribute->name }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            {{ $attributes->firstItem() }}-{{ $attributes->lastItem() }} / {{ $attributes->total() }} özellik gösteriliyor
                        </small>
                    </div>
                    <div>
                        {{ $attributes->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Henüz Özellik Bulunmuyor</h5>
                    <p class="text-muted">Ürün varyantları oluşturmak için önce özellikler tanımlamanız gerekir.</p>
                    <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> İlk Özelliği Ekle
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Attribute Values Modal -->
<div class="modal fade" id="attributeValuesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Özellik Değerleri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="attributeValuesContent">
                    <!-- Values will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Özelliği Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bu özelliği silmek istediğinizden emin misiniz?</p>
                <p class="text-danger"><strong id="deleteAttributeName"></strong> özelliği ve tüm değerleri silinecektir.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sortable
    const sortable = Sortable.create(document.getElementById('sortableAttributes'), {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function(evt) {
            updateAttributeOrder();
        }
    });
});

function updateAttributeOrder() {
    const rows = document.querySelectorAll('#sortableAttributes tr');
    const attributeIds = Array.from(rows).map(row => row.dataset.attributeId);
    
    fetch('/admin/attributes/update-order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            attribute_ids: attributeIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('Özellik sırası güncellendi', 'success');
        }
    })
    .catch(error => {
        console.error('Error updating order:', error);
        showToast('Sıra güncellenirken hata oluştu', 'error');
    });
}

function showAttributeValues(attributeId) {
    fetch(`/admin/attributes/${attributeId}/values`)
        .then(response => response.json())
        .then(values => {
            let content = '<div class="row">';
            
            if (values.length === 0) {
                content += '<div class="col-12 text-center"><p class="text-muted">Bu özelliğin henüz değeri bulunmuyor.</p></div>';
            } else {
                values.forEach(value => {
                    content += `
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center">
                    `;
                    
                    if (value.color_code) {
                        content += `<div class="me-2" style="width: 20px; height: 20px; background-color: ${value.color_code}; border: 1px solid #dee2e6; border-radius: 3px;"></div>`;
                    } else if (value.image_url) {
                        content += `<img src="${value.image_url}" class="me-2" style="width: 20px; height: 20px; object-fit: cover; border-radius: 3px;">`;
                    } else {
                        content += `<i class="fas fa-tag me-2 text-muted"></i>`;
                    }
                    
                    content += `
                                        <span class="small">${value.value}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            content += '</div>';
            
            document.getElementById('attributeValuesContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('attributeValuesModal')).show();
        })
        .catch(error => {
            console.error('Error loading values:', error);
            showToast('Değerler yüklenirken hata oluştu', 'error');
        });
}

function confirmDelete(attributeId, attributeName) {
    document.getElementById('deleteAttributeName').textContent = attributeName;
    document.getElementById('deleteForm').action = `/admin/attributes/${attributeId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function showToast(message, type = 'info') {
    // Simple toast implementation
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