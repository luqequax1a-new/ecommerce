@extends('admin.layouts.app')

@section('title', 'Kategori Yönetimi')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Kategori Yönetimi</h1>
            <p class="text-muted">Kategorileri ağaç yapısında yönetin</p>
        </div>
        <div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Kategori
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

    <!-- Category Tree -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0">Kategori Ağacı</h5>
                </div>
                <div class="col-auto">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="expandAll">
                            <i class="fas fa-expand-arrows-alt"></i> Tümünü Aç
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="collapseAll">
                            <i class="fas fa-compress-arrows-alt"></i> Tümünü Kapat
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Bulk Actions -->
            <form id="bulkActionForm" action="{{ route('admin.categories.bulk-action') }}" method="POST">
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
                        <span id="selectedCount" class="text-muted">0 kategori seçildi</span>
                    </div>
                </div>

                <!-- Category Tree -->
                <div id="categoryTree" class="sortable-tree">
                    @include('admin.categories.partials.tree-node', ['categories' => $rootCategories, 'level' => 0])
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">{{ $categories->total() }}</h5>
                    <p class="card-text">Toplam Kategori</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-success">{{ $rootCategories->count() }}</h5>
                    <p class="card-text">Ana Kategori</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-info">{{ $categories->where('is_active', true)->count() }}</h5>
                    <p class="card-text">Aktif Kategori</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-warning">{{ $categories->where('show_in_menu', true)->count() }}</h5>
                    <p class="card-text">Menüde Görünen</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category tree sortable
    const tree = document.getElementById('categoryTree');
    if (tree) {
        new Sortable(tree, {
            group: 'nested',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onEnd: function(evt) {
                updateCategoryOrder();
            }
        });
        
        // Make nested lists sortable too
        tree.querySelectorAll('.nested-sortable').forEach(function(el) {
            new Sortable(el, {
                group: 'nested',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                onEnd: function(evt) {
                    updateCategoryOrder();
                }
            });
        });
    }

    // Expand/Collapse all
    document.getElementById('expandAll')?.addEventListener('click', function() {
        document.querySelectorAll('.collapse').forEach(el => {
            el.classList.add('show');
        });
    });

    document.getElementById('collapseAll')?.addEventListener('click', function() {
        document.querySelectorAll('.collapse').forEach(el => {
            el.classList.remove('show');
        });
    });

    // Bulk selection
    const selectAllCheckbox = document.getElementById('selectAll');
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const selectedCount = document.getElementById('selectedCount');

    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.category-checkbox:checked');
        selectedCount.textContent = `${checkedBoxes.length} kategori seçildi`;
    }

    selectAllCheckbox?.addEventListener('change', function() {
        categoryCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    updateSelectedCount();
});

function updateCategoryOrder() {
    // Collect order data from DOM
    const orders = [];
    document.querySelectorAll('[data-category-id]').forEach((el, index) => {
        orders.push({
            id: el.dataset.categoryId,
            position: index,
            parent_id: el.closest('.nested-sortable')?.dataset.parentId || null
        });
    });

    // Send AJAX request to update order
    fetch('{{ route("admin.categories.update-order") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ orders: orders })
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              // Show success message
              console.log('Sıralama güncellendi');
          }
      }).catch(error => {
          console.error('Sıralama güncellenemedi:', error);
      });
}

function toggleCategoryStatus(categoryId) {
    fetch(`/admin/categories/${categoryId}/toggle-status`, {
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