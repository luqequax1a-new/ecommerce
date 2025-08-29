{{-- Associations Tab - Product Relationships --}}
<div class="tab-pane fade" id="associations" role="tabpanel">
    <div class="row">
        <div class="col-lg-6">
            {{-- Related Products --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-link text-primary me-2"></i>
                        İlgili Ürünler
                    </h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRelatedProducts()">
                        <i class="fas fa-plus me-1"></i>Ürün Ekle
                    </button>
                </div>
                <div class="card-body">
                    <div id="related-products-list">
                        {{-- Related products will be loaded here --}}
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-link fa-2x mb-2"></i>
                            <div>İlgili ürün eklenmemiş</div>
                            <small>Müşterilere önerilebilecek benzer ürünleri ekleyin</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cross-sell Products --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart text-success me-2"></i>
                        Çapraz Satış Ürünleri
                    </h5>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="addCrossSellProducts()">
                        <i class="fas fa-plus me-1"></i>Ürün Ekle
                    </button>
                </div>
                <div class="card-body">
                    <div id="cross-sell-products-list">
                        {{-- Cross-sell products will be loaded here --}}
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <div>Çapraz satış ürünü eklenmemiş</div>
                            <small>Sepete eklendiğinde önerilecek ürünleri ekleyin</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            {{-- Up-sell Products --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-arrow-up text-warning me-2"></i>
                        Üst Satış Ürünleri
                    </h5>
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="addUpSellProducts()">
                        <i class="fas fa-plus me-1"></i>Ürün Ekle
                    </button>
                </div>
                <div class="card-body">
                    <div id="up-sell-products-list">
                        {{-- Up-sell products will be loaded here --}}
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-arrow-up fa-2x mb-2"></i>
                            <div>Üst satış ürünü eklenmemiş</div>
                            <small>Daha pahalı alternatif ürünleri ekleyin</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Accessories --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-puzzle-piece text-info me-2"></i>
                        Aksesuarlar
                    </h5>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="addAccessories()">
                        <i class="fas fa-plus me-1"></i>Aksesuar Ekle
                    </button>
                </div>
                <div class="card-body">
                    <div id="accessories-list">
                        {{-- Accessories will be loaded here --}}
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-puzzle-piece fa-2x mb-2"></i>
                            <div>Aksesuar eklenmemiş</div>
                            <small>Bu ürünle birlikte satılabilecek aksesuarları ekleyin</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tags Section --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tags text-secondary me-2"></i>
                        Ürün Etiketleri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="product-tags" class="form-label">Etiketler</label>
                        <input type="text" class="form-control" id="product-tags" name="tags" 
                               value="{{ old('tags', $product->tags ? $product->tags->pluck('name')->join(', ') : '') }}"
                               placeholder="etiket1, etiket2, etiket3">
                        <div class="form-text">
                            Virgülle ayırarak etiket ekleyin. Etiketler ürün arama ve filtreleme için kullanılır.
                        </div>
                    </div>
                    
                    {{-- Popular Tags --}}
                    <div class="popular-tags">
                        <small class="text-muted mb-2 d-block">Popüler Etiketler:</small>
                        <div id="popular-tags-container">
                            {{-- Popular tags will be loaded here --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Product Selection Modal --}}
<div class="modal fade" id="product-selection-modal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="product-selection-title">Ürün Seç</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Search and Filter --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="product-search" placeholder="Ürün ara...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="category-filter">
                            <option value="">Tüm Kategoriler</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="brand-filter">
                            <option value="">Tüm Markalar</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                {{-- Products Grid --}}
                <div id="products-grid" class="row g-3" style="max-height: 400px; overflow-y: auto;">
                    {{-- Products will be loaded here --}}
                </div>
                
                {{-- Loading State --}}
                <div id="products-loading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="addSelectedProducts()" id="add-selected-btn" disabled>
                    <i class="fas fa-plus me-1"></i>Seçilenleri Ekle
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
    cursor: pointer;
}

.product-card:hover {
    border-color: #007bff;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 123, 255, 0.075);
}

.product-card.selected {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.product-checkbox {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 2;
}

.association-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.association-item:hover {
    background-color: #f8f9fa;
}

.tag-badge {
    background-color: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tag-badge:hover {
    background-color: #007bff;
    color: white;
}
</style>

<script>
let currentAssociationType = '';
let selectedProducts = [];

function addRelatedProducts() {
    currentAssociationType = 'related';
    openProductSelectionModal('İlgili Ürün Seç');
}

function addCrossSellProducts() {
    currentAssociationType = 'cross_sell';
    openProductSelectionModal('Çapraz Satış Ürünü Seç');
}

function addUpSellProducts() {
    currentAssociationType = 'up_sell';
    openProductSelectionModal('Üst Satış Ürünü Seç');
}

function addAccessories() {
    currentAssociationType = 'accessories';
    openProductSelectionModal('Aksesuar Seç');
}

function openProductSelectionModal(title) {
    document.getElementById('product-selection-title').textContent = title;
    selectedProducts = [];
    loadProducts();
    new bootstrap.Modal(document.getElementById('product-selection-modal')).show();
}

function loadProducts(search = '', categoryId = '', brandId = '') {
    const loading = document.getElementById('products-loading');
    const grid = document.getElementById('products-grid');
    
    loading.classList.remove('d-none');
    grid.innerHTML = '';
    
    const params = new URLSearchParams({
        search: search,
        category_id: categoryId,
        brand_id: brandId,
        exclude_id: {{ $product->id }},
        per_page: 20
    });
    
    fetch(`/admin/products/for-association?${params}`)
        .then(response => response.json())
        .then(data => {
            loading.classList.add('d-none');
            
            if (data.products.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center text-muted py-4">Ürün bulunamadı</div>';
                return;
            }
            
            data.products.forEach(product => {
                const productCard = createProductCard(product);
                grid.appendChild(productCard);
            });
        })
        .catch(error => {
            loading.classList.add('d-none');
            console.error('Error loading products:', error);
            grid.innerHTML = '<div class="col-12 text-center text-danger py-4">Ürünler yüklenirken hata oluştu</div>';
        });
}

function createProductCard(product) {
    const col = document.createElement('div');
    col.className = 'col-md-4 col-lg-3';
    
    col.innerHTML = `
        <div class="product-card p-2 position-relative" onclick="toggleProductSelection(${product.id}, this)">
            <input type="checkbox" class="form-check-input product-checkbox" value="${product.id}">
            <div class="text-center">
                <img src="${product.image_url || '/images/no-image.png'}" 
                     alt="${product.name}" class="img-fluid rounded mb-2" style="height: 80px; object-fit: cover;">
                <h6 class="mb-1 small">${product.name}</h6>
                <small class="text-muted">${product.sku || ''}</small>
                <div class="mt-1">
                    <small class="text-primary fw-bold">${product.price ? product.price + ' ₺' : 'Fiyat belirsiz'}</small>
                </div>
            </div>
        </div>
    `;
    
    return col;
}

function toggleProductSelection(productId, cardElement) {
    const checkbox = cardElement.querySelector('.product-checkbox');
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        cardElement.classList.add('selected');
        selectedProducts.push(productId);
    } else {
        cardElement.classList.remove('selected');
        selectedProducts = selectedProducts.filter(id => id !== productId);
    }
    
    // Update add button state
    document.getElementById('add-selected-btn').disabled = selectedProducts.length === 0;
}

function addSelectedProducts() {
    if (selectedProducts.length === 0) return;
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('type', currentAssociationType);
    selectedProducts.forEach(id => formData.append('product_ids[]', id));
    
    fetch(`/admin/products/{{ $product->id }}/associations`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('product-selection-modal')).hide();
            loadAssociations(currentAssociationType);
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Ürün ekleme sırasında hata oluştu', 'danger');
        }
    })
    .catch(error => {
        console.error('Error adding products:', error);
        showAlert('Ürün ekleme sırasında hata oluştu', 'danger');
    });
}

function loadAssociations(type) {
    const containerId = type.replace('_', '-') + '-products-list';
    const container = document.getElementById(containerId);
    
    fetch(`/admin/products/{{ $product->id }}/associations/${type}`)
        .then(response => response.json())
        .then(data => {
            if (data.associations.length === 0) {
                container.innerHTML = getEmptyStateHTML(type);
                return;
            }
            
            let html = '';
            data.associations.forEach(association => {
                html += createAssociationItemHTML(association, type);
            });
            
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading associations:', error);
        });
}

function createAssociationItemHTML(association, type) {
    return `
        <div class="association-item p-2 mb-2 d-flex align-items-center">
            <img src="${association.image_url || '/images/no-image.png'}" 
                 alt="${association.name}" class="me-3 rounded" style="width: 50px; height: 50px; object-fit: cover;">
            <div class="flex-grow-1">
                <h6 class="mb-1">${association.name}</h6>
                <small class="text-muted">${association.sku || ''}</small>
                <div class="mt-1">
                    <small class="text-primary fw-bold">${association.price ? association.price + ' ₺' : ''}</small>
                </div>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm" 
                    onclick="removeAssociation(${association.id}, '${type}', this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
}

function getEmptyStateHTML(type) {
    const typeNames = {
        'related': 'İlgili ürün',
        'cross_sell': 'Çapraz satış ürünü',
        'up_sell': 'Üst satış ürünü',
        'accessories': 'Aksesuar'
    };
    
    const icons = {
        'related': 'link',
        'cross_sell': 'shopping-cart',
        'up_sell': 'arrow-up',
        'accessories': 'puzzle-piece'
    };
    
    return `
        <div class="text-center text-muted py-3">
            <i class="fas fa-${icons[type]} fa-2x mb-2"></i>
            <div>${typeNames[type]} eklenmemiş</div>
        </div>
    `;
}

function removeAssociation(productId, type, button) {
    if (confirm('Bu ilişkiyi kaldırmak istediğinizden emin misiniz?')) {
        fetch(`/admin/products/{{ $product->id }}/associations/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ type: type })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.closest('.association-item').remove();
                showAlert('İlişki kaldırıldı', 'success');
            } else {
                showAlert(data.message || 'Kaldırma sırasında hata oluştu', 'danger');
            }
        })
        .catch(error => {
            console.error('Error removing association:', error);
            showAlert('Kaldırma sırasında hata oluştu', 'danger');
        });
    }
}

function loadPopularTags() {
    fetch('/admin/products/popular-tags')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('popular-tags-container');
            let html = '';
            
            data.tags.forEach(tag => {
                html += `<span class="tag-badge me-1 mb-1" onclick="addTag('${tag.name}')">${tag.name}</span>`;
            });
            
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading popular tags:', error);
        });
}

function addTag(tagName) {
    const tagsInput = document.getElementById('product-tags');
    const currentTags = tagsInput.value.split(',').map(tag => tag.trim()).filter(tag => tag);
    
    if (!currentTags.includes(tagName)) {
        currentTags.push(tagName);
        tagsInput.value = currentTags.join(', ');
    }
}

function showAlert(message, type) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load existing associations
    ['related', 'cross_sell', 'up_sell', 'accessories'].forEach(type => {
        loadAssociations(type);
    });
    
    // Load popular tags
    loadPopularTags();
    
    // Add search functionality
    document.getElementById('product-search').addEventListener('input', function() {
        const search = this.value;
        const categoryId = document.getElementById('category-filter').value;
        const brandId = document.getElementById('brand-filter').value;
        loadProducts(search, categoryId, brandId);
    });
    
    document.getElementById('category-filter').addEventListener('change', function() {
        const search = document.getElementById('product-search').value;
        const categoryId = this.value;
        const brandId = document.getElementById('brand-filter').value;
        loadProducts(search, categoryId, brandId);
    });
    
    document.getElementById('brand-filter').addEventListener('change', function() {
        const search = document.getElementById('product-search').value;
        const categoryId = document.getElementById('category-filter').value;
        const brandId = this.value;
        loadProducts(search, categoryId, brandId);
    });
});
</script>