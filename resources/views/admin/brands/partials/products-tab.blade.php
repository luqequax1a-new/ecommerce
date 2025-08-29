{{-- Products Tab for Brand Management --}}
<div class="row">
    <div class="col-12">
        @if($brand->exists)
            <!-- Brand Products Management -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Marka Ürünleri</h5>
                <div>
                    <a href="{{ route('admin.products.create', ['brand_id' => $brand->id]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Yeni Ürün Ekle
                    </a>
                </div>
            </div>

            <!-- Product Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="productStatus" class="form-label">Durum</label>
                            <select class="form-select" id="productStatus" onchange="filterProducts()">
                                <option value="">Tümü</option>
                                <option value="1">Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="productCategory" class="form-label">Kategori</label>
                            <select class="form-select" id="productCategory" onchange="filterProducts()">
                                <option value="">Tüm Kategoriler</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">
                                        {{ str_repeat('- ', $category->level ?? 0) }}{{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="productSearch" class="form-label">Ürün Ara</label>
                            <input type="text" class="form-control" id="productSearch" 
                                   placeholder="Ürün adı veya SKU..." onkeyup="filterProducts()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Temizle
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="productsTable">
                            <thead>
                                <tr>
                                    <th width="60">
                                        <input type="checkbox" id="selectAllProducts" onchange="toggleAllProducts()">
                                    </th>
                                    <th width="80">Görsel</th>
                                    <th>Ürün Adı</th>
                                    <th width="120">SKU</th>
                                    <th width="120">Kategori</th>
                                    <th width="100">Fiyat</th>
                                    <th width="80">Stok</th>
                                    <th width="80">Durum</th>
                                    <th width="120">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                @forelse($brand->products ?? [] as $product)
                                    <tr data-product-id="{{ $product->id }}" 
                                        data-status="{{ $product->is_active ? 1 : 0 }}"
                                        data-category="{{ $product->category_id ?? '' }}"
                                        data-name="{{ strtolower($product->name) }}"
                                        data-sku="{{ strtolower($product->sku ?? '') }}">
                                        <td>
                                            <input type="checkbox" class="product-checkbox" value="{{ $product->id }}">
                                        </td>
                                        <td>
                                            @if($product->main_image)
                                                <img src="{{ Storage::url($product->main_image) }}" 
                                                     class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.products.edit', $product) }}" class="text-decoration-none">
                                                {{ $product->name }}
                                            </a>
                                            @if($product->short_description)
                                                <br><small class="text-muted">{{ Str::limit($product->short_description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $product->sku ?? '-' }}</code>
                                        </td>
                                        <td>
                                            @if($product->category)
                                                <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->price)
                                                <strong>{{ number_format($product->price, 2) }} ₺</strong>
                                                @if($product->compare_price && $product->compare_price > $product->price)
                                                    <br><small class="text-muted text-decoration-line-through">
                                                        {{ number_format($product->compare_price, 2) }} ₺
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->track_quantity)
                                                @if($product->quantity > 0)
                                                    <span class="badge bg-success">{{ $product->quantity }}</span>
                                                @else
                                                    <span class="badge bg-danger">Tükendi</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">∞</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Pasif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.products.edit', $product) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Düzenle">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('product.show', $product->slug) }}" 
                                                   class="btn btn-sm btn-outline-info" title="Önizle" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                                <p class="mb-0">Bu markanın henüz ürünü bulunmuyor.</p>
                                                <a href="{{ route('admin.products.create', ['brand_id' => $brand->id]) }}" 
                                                   class="btn btn-primary mt-2">
                                                    <i class="fas fa-plus"></i> İlk Ürünü Ekle
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(($brand->products ?? collect())->count() > 0)
                        <!-- Bulk Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <select class="form-select d-inline-block w-auto me-2" id="bulkAction">
                                    <option value="">Toplu İşlemler</option>
                                    <option value="activate">Aktif Yap</option>
                                    <option value="deactivate">Pasif Yap</option>
                                    <option value="delete">Sil</option>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="applyBulkAction()">
                                    Uygula
                                </button>
                            </div>
                            <div>
                                <small class="text-muted">
                                    Toplam {{ ($brand->products ?? collect())->count() }} ürün
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Product Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary">{{ ($brand->products ?? collect())->count() }}</h3>
                            <p class="text-muted mb-0">Toplam Ürün</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success">{{ ($brand->products ?? collect())->where('is_active', true)->count() }}</h3>
                            <p class="text-muted mb-0">Aktif Ürün</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning">{{ ($brand->products ?? collect())->where('quantity', '<=', 5)->where('track_quantity', true)->count() }}</h3>
                            <p class="text-muted mb-0">Düşük Stok</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger">{{ ($brand->products ?? collect())->where('quantity', 0)->where('track_quantity', true)->count() }}</h3>
                            <p class="text-muted mb-0">Stok Yok</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- New Brand Message -->
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Marka Kaydedildikten Sonra</h5>
                <p class="text-muted">Markayı kaydettikten sonra ürün yönetimi sekmesi aktif hale gelecektir.</p>
            </div>
        @endif
    </div>
</div>

@if($brand->exists)
<script>
function filterProducts() {
    const status = document.getElementById('productStatus').value;
    const category = document.getElementById('productCategory').value;
    const search = document.getElementById('productSearch').value.toLowerCase();
    
    const rows = document.querySelectorAll('#productsTableBody tr[data-product-id]');
    
    rows.forEach(row => {
        let show = true;
        
        // Status filter
        if (status !== '' && row.dataset.status !== status) {
            show = false;
        }
        
        // Category filter
        if (category !== '' && row.dataset.category !== category) {
            show = false;
        }
        
        // Search filter
        if (search !== '') {
            const name = row.dataset.name;
            const sku = row.dataset.sku;
            if (!name.includes(search) && !sku.includes(search)) {
                show = false;
            }
        }
        
        row.style.display = show ? '' : 'none';
    });
    
    updateVisibleCount();
}

function clearFilters() {
    document.getElementById('productStatus').value = '';
    document.getElementById('productCategory').value = '';
    document.getElementById('productSearch').value = '';
    filterProducts();
}

function updateVisibleCount() {
    const allRows = document.querySelectorAll('#productsTableBody tr[data-product-id]');
    const visibleRows = document.querySelectorAll('#productsTableBody tr[data-product-id]:not([style*="display: none"])');
    
    // Update count display if exists
    const countElement = document.querySelector('.products-count');
    if (countElement) {
        countElement.textContent = `${visibleRows.length} / ${allRows.length} ürün gösteriliyor`;
    }
}

function toggleAllProducts() {
    const selectAll = document.getElementById('selectAllProducts');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    
    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        if (row.style.display !== 'none') {
            checkbox.checked = selectAll.checked;
        }
    });
}

function applyBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    
    if (!action) {
        alert('Lütfen bir işlem seçin.');
        return;
    }
    
    if (checkedBoxes.length === 0) {
        alert('Lütfen en az bir ürün seçin.');
        return;
    }
    
    const productIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    let confirmMessage = '';
    switch (action) {
        case 'activate':
            confirmMessage = `${productIds.length} ürünü aktif yapmak istediğinizden emin misiniz?`;
            break;
        case 'deactivate':
            confirmMessage = `${productIds.length} ürünü pasif yapmak istediğinizden emin misiniz?`;
            break;
        case 'delete':
            confirmMessage = `${productIds.length} ürünü silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.`;
            break;
    }
    
    if (confirm(confirmMessage)) {
        // Submit bulk action form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/products/bulk-action';
        
        // CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Action
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        form.appendChild(actionInput);
        
        // Product IDs
        productIds.forEach(id => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'product_ids[]';
            idInput.value = id;
            form.appendChild(idInput);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize filters on page load
document.addEventListener('DOMContentLoaded', function() {
    updateVisibleCount();
});
</script>
@endif