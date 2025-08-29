@extends('admin.layouts.app')

@section('title', 'Marka Yönetimi')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Marka Yönetimi</h1>
            <p class="text-muted">Markaları yönetin ve düzenleyin</p>
        </div>
        <div>
            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Marka
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

    <!-- Brands List -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0">Markalar ({{ $brands->total() }})</h5>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Marka ara..." id="searchBrands">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Bulk Actions -->
            <form id="bulkActionForm" action="{{ route('admin.brands.bulk-action') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select name="action" class="form-select" required>
                            <option value="">Toplu İşlem Seçin</option>
                            <option value="activate">Aktif Yap</option>
                            <option value="deactivate">Pasif Yap</option>
                            <option value="delete">Sil</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Bu işlemi gerçekleştirmek istediğinizden emin misiniz?')">
                            <i class="fas fa-play"></i> Uygula
                        </button>
                    </div>
                    <div class="col-md-4 text-end">
                        <span id="selectedCount" class="text-muted">0 marka seçildi</span>
                    </div>
                </div>

                <!-- Brands Grid -->
                <div class="row" id="brandsGrid">
                    @forelse($brands as $brand)
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-4" data-brand-id="{{ $brand->id }}">
                            <div class="card h-100 brand-card">
                                <div class="card-body text-center">
                                    <!-- Selection Checkbox -->
                                    <div class="form-check position-absolute top-0 start-0 m-2">
                                        <input class="form-check-input brand-checkbox" type="checkbox" 
                                               name="brands[]" value="{{ $brand->id }}">
                                    </div>
                                    
                                    <!-- Status Badge -->
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-{{ $brand->is_active ? 'success' : 'secondary' }}">
                                            {{ $brand->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </div>

                                    <!-- Logo -->
                                    <div class="mb-3">
                                        @if($brand->logo_path)
                                            <img src="{{ Storage::url($brand->logo_path) }}" 
                                                 alt="{{ $brand->name }}" 
                                                 class="img-fluid rounded border"
                                                 style="max-height: 80px; max-width: 120px;">
                                        @else
                                            <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                                 style="height: 80px; width: 120px; margin: 0 auto;">
                                                <i class="fas fa-image text-muted fa-2x"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Brand Info -->
                                    <h6 class="card-title mb-1">
                                        <a href="{{ route('admin.brands.edit', $brand) }}" 
                                           class="text-decoration-none">
                                            {{ $brand->name }}
                                        </a>
                                    </h6>
                                    
                                    @if($brand->short_description)
                                        <p class="card-text small text-muted mb-2">
                                            {{ Str::limit($brand->short_description, 60) }}
                                        </p>
                                    @endif

                                    <!-- Statistics -->
                                    <div class="row text-center small mb-3">
                                        <div class="col-6">
                                            <div class="text-primary fw-bold">{{ $brand->products_count ?? 0 }}</div>
                                            <div class="text-muted">Ürün</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-success fw-bold">{{ $brand->active_products_count ?? 0 }}</div>
                                            <div class="text-muted">Aktif</div>
                                        </div>
                                    </div>

                                    <!-- URL -->
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-link"></i> /marka/{{ $brand->slug }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="card-footer bg-transparent">
                                    <div class="btn-group w-100" role="group">
                                        <button class="btn btn-sm {{ $brand->is_active ? 'btn-success' : 'btn-secondary' }}" 
                                                onclick="toggleBrandStatus({{ $brand->id }})" 
                                                title="{{ $brand->is_active ? 'Aktif' : 'Pasif' }}">
                                            <i class="fas fa-{{ $brand->is_active ? 'eye' : 'eye-slash' }}"></i>
                                        </button>
                                        
                                        <a href="{{ route('admin.brands.edit', $brand) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if($brand->website_url)
                                            <a href="{{ $brand->website_url }}" target="_blank"
                                               class="btn btn-sm btn-outline-info" title="Web Sitesi">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                        
                                        <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" 
                                              class="d-inline" onsubmit="return confirm('Bu markayı silmek istediğinizden emin misiniz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil"
                                                    {{ $brand->products_count > 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Henüz marka eklenmemiş</h5>
                                <p class="text-muted">İlk markayı eklemek için yukarıdaki butonu kullanın.</p>
                                <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> İlk Markayı Ekle
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </form>

            <!-- Pagination -->
            @if($brands->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $brands->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">{{ $brands->total() }}</h5>
                    <p class="card-text">Toplam Marka</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-success">{{ $brands->where('is_active', true)->count() }}</h5>
                    <p class="card-text">Aktif Marka</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-info">{{ $brands->sum('products_count') }}</h5>
                    <p class="card-text">Toplam Ürün</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-warning">{{ $brands->where('logo_path', '!=', null)->count() }}</h5>
                    <p class="card-text">Logolu Marka</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make brands grid sortable
    const grid = document.getElementById('brandsGrid');
    if (grid) {
        new Sortable(grid, {
            animation: 150,
            onEnd: function(evt) {
                updateBrandOrder();
            }
        });
    }

    // Bulk selection
    const brandCheckboxes = document.querySelectorAll('.brand-checkbox');
    const selectedCount = document.getElementById('selectedCount');

    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.brand-checkbox:checked');
        selectedCount.textContent = `${checkedBoxes.length} marka seçildi`;
    }

    brandCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Search functionality
    const searchInput = document.getElementById('searchBrands');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const brandCards = document.querySelectorAll('.brand-card');
        
        brandCards.forEach(card => {
            const brandName = card.querySelector('.card-title a').textContent.toLowerCase();
            const brandDesc = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
            
            if (brandName.includes(searchTerm) || brandDesc.includes(searchTerm)) {
                card.closest('.col-md-6').style.display = 'block';
            } else {
                card.closest('.col-md-6').style.display = 'none';
            }
        });
    });

    updateSelectedCount();
});

function updateBrandOrder() {
    const orders = [];
    document.querySelectorAll('[data-brand-id]').forEach((el, index) => {
        orders.push({
            id: el.dataset.brandId,
            position: index
        });
    });

    fetch('{{ route("admin.brands.update-order") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ orders: orders })
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              console.log('Sıralama güncellendi');
          }
      }).catch(error => {
          console.error('Sıralama güncellenemedi:', error);
      });
}

function toggleBrandStatus(brandId) {
    fetch(`/admin/brands/${brandId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).then(response => {
        if (response.ok) {
            location.reload();
        }
    });
}
</script>
@endpush
@endsection