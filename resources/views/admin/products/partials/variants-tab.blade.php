{{-- Variants Tab - Product Variant Management --}}
<div class="tab-pane fade" id="variants" role="tabpanel">
    <div class="row">
        <div class="col-12">
            {{-- Variant Type Selection --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cog text-primary me-2"></i>
                        Varyant Ayarları
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ürün Türü</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="product_type" id="simple_product" 
                                           value="simple" {{ old('product_type', $product->product_type) == 'simple' ? 'checked' : '' }}
                                           onchange="toggleVariantSection()">
                                    <label class="form-check-label" for="simple_product">
                                        <strong>Basit Ürün</strong>
                                        <div class="text-muted small">Tek varyant, direkt fiyat ve stok</div>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="product_type" id="variable_product" 
                                           value="variable" {{ old('product_type', $product->product_type) == 'variable' ? 'checked' : '' }}
                                           onchange="toggleVariantSection()">
                                    <label class="form-check-label" for="variable_product">
                                        <strong>Varyantlı Ürün</strong>
                                        <div class="text-muted small">Renk, beden gibi farklı seçenekler</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="variant-attributes-section" style="display: {{ $product->product_type == 'variable' ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="form-label">Varyant Özelikleri</label>
                                <button type="button" class="btn btn-outline-primary btn-sm float-end" onclick="showAttributeModal()">
                                    <i class="fas fa-plus me-1"></i>Özellik Ekle
                                </button>
                                <div id="selected-attributes" class="mt-2">
                                    {{-- Selected attributes will be loaded here --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Simple Product Section --}}
            <div id="simple-product-section" style="display: {{ $product->product_type == 'simple' ? 'block' : 'none' }};">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tag text-success me-2"></i>
                            Fiyat ve Stok Bilgileri
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($product->variants->first())
                            @php $mainVariant = $product->variants->first(); @endphp
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="variant_sku" class="form-label">SKU</label>
                                        <input type="text" class="form-control" id="variant_sku" name="variants[0][sku]" 
                                               value="{{ old('variants.0.sku', $mainVariant->sku) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="variant_price" class="form-label required">Fiyat (₺)</label>
                                        <input type="number" step="0.01" min="0" class="form-control" 
                                               id="variant_price" name="variants[0][price]" 
                                               value="{{ old('variants.0.price', $mainVariant->price) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="variant_stock" class="form-label">Stok Miktarı</label>
                                        <input type="number" step="0.001" min="0" class="form-control" 
                                               id="variant_stock" name="variants[0][stock_quantity]" 
                                               value="{{ old('variants.0.stock_quantity', $mainVariant->stock_quantity) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="variant_unit" class="form-label">Birim</label>
                                        <select class="form-select" id="variant_unit" name="variants[0][unit_id]">
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" 
                                                        {{ old('variants.0.unit_id', $mainVariant->unit_id) == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->display_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="variants[0][id]" value="{{ $mainVariant->id }}">
                        @else
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="new_variant_sku" class="form-label">SKU</label>
                                        <input type="text" class="form-control" id="new_variant_sku" name="variants[0][sku]" 
                                               placeholder="Otomatik oluşturulacak">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="new_variant_price" class="form-label required">Fiyat (₺)</label>
                                        <input type="number" step="0.01" min="0" class="form-control" 
                                               id="new_variant_price" name="variants[0][price]" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="new_variant_stock" class="form-label">Stok Miktarı</label>
                                        <input type="number" step="0.001" min="0" class="form-control" 
                                               id="new_variant_stock" name="variants[0][stock_quantity]" value="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="new_variant_unit" class="form-label">Birim</label>
                                        <select class="form-select" id="new_variant_unit" name="variants[0][unit_id]" required>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" {{ $unit->is_default ? 'selected' : '' }}>
                                                    {{ $unit->display_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Variable Product Section --}}
            <div id="variable-product-section" style="display: {{ $product->product_type == 'variable' ? 'block' : 'none' }};">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-layer-group text-info me-2"></i>
                            Ürün Varyantları
                            <span class="badge bg-secondary ms-2">{{ $product->variants->count() }}</span>
                        </h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm" onclick="generateVariants()">
                                <i class="fas fa-magic me-1"></i>Varyant Oluştur
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="bulkEditVariants()">
                                <i class="fas fa-edit me-1"></i>Toplu Düzenle
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Existing Variants --}}
                        <div id="variants-list">
                            @if($product->variants->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>SKU</th>
                                                <th>Özellikler</th>
                                                <th>Fiyat</th>
                                                <th>Stok</th>
                                                <th>Birim</th>
                                                <th>Durum</th>
                                                <th>İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($product->variants as $index => $variant)
                                                <tr data-variant-id="{{ $variant->id }}">
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm" 
                                                               name="variants[{{ $index }}][sku]" 
                                                               value="{{ $variant->sku }}" style="min-width: 120px;">
                                                        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                                    </td>
                                                    <td>
                                                        <small>
                                                            @if($variant->attributes)
                                                                @foreach($variant->attributes as $key => $value)
                                                                    <span class="badge bg-light text-dark me-1">{{ $key }}: {{ $value }}</span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted">Özellik yok</span>
                                                            @endif
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" step="0.01" min="0" class="form-control" 
                                                                   name="variants[{{ $index }}][price]" 
                                                                   value="{{ $variant->price }}" style="width: 100px;">
                                                            <span class="input-group-text">₺</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.001" min="0" class="form-control form-control-sm" 
                                                               name="variants[{{ $index }}][stock_quantity]" 
                                                               value="{{ $variant->stock_quantity }}" style="width: 80px;">
                                                    </td>
                                                    <td>
                                                        <select class="form-select form-select-sm" 
                                                                name="variants[{{ $index }}][unit_id]" style="width: 100px;">
                                                            @foreach($units as $unit)
                                                                <option value="{{ $unit->id }}" 
                                                                        {{ $variant->unit_id == $unit->id ? 'selected' : '' }}>
                                                                    {{ $unit->symbol }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="variants[{{ $index }}][is_active]" 
                                                                   value="1" {{ $variant->is_active ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                onclick="deleteVariant({{ $variant->id }}, this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Henüz varyant oluşturulmamış</h5>
                                    <p class="text-muted">Önce ürün özelliklerini seçin, sonra varyantları oluşturun</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Attribute Selection Modal --}}
<div class="modal fade" id="attribute-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Varyant Özelikleri Seç</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="attribute-selection">
                    {{-- Will be loaded via AJAX --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="saveSelectedAttributes()">Seçimleri Kaydet</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleVariantSection() {
    const productType = document.querySelector('input[name="product_type"]:checked').value;
    const simpleSection = document.getElementById('simple-product-section');
    const variableSection = document.getElementById('variable-product-section');
    const attributesSection = document.getElementById('variant-attributes-section');
    
    if (productType === 'simple') {
        simpleSection.style.display = 'block';
        variableSection.style.display = 'none';
        attributesSection.style.display = 'none';
    } else {
        simpleSection.style.display = 'none';
        variableSection.style.display = 'block';
        attributesSection.style.display = 'block';
    }
}

function showAttributeModal() {
    // Load available attributes
    fetch('/admin/product-attributes/for-selection')
        .then(response => response.json())
        .then(data => {
            document.getElementById('attribute-selection').innerHTML = data.html;
            new bootstrap.Modal(document.getElementById('attribute-modal')).show();
        })
        .catch(error => {
            console.error('Error loading attributes:', error);
            alert('Özellikler yüklenirken hata oluştu');
        });
}

function saveSelectedAttributes() {
    const selectedAttributes = {};
    document.querySelectorAll('.attribute-checkbox:checked').forEach(checkbox => {
        const attributeId = checkbox.value;
        const attributeName = checkbox.dataset.name;
        selectedAttributes[attributeId] = attributeName;
    });
    
    // Update the selected attributes display
    updateSelectedAttributesDisplay(selectedAttributes);
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('attribute-modal')).hide();
}

function updateSelectedAttributesDisplay(attributes) {
    const container = document.getElementById('selected-attributes');
    let html = '';
    
    Object.entries(attributes).forEach(([id, name]) => {
        html += `
            <div class="badge bg-primary me-2 mb-2">
                ${name}
                <button type="button" class="btn-close btn-close-white ms-1" 
                        onclick="removeAttribute(${id})" style="font-size: 0.6rem;"></button>
                <input type="hidden" name="selected_attributes[]" value="${id}">
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function removeAttribute(attributeId) {
    const badge = event.target.closest('.badge');
    badge.remove();
}

function generateVariants() {
    const selectedAttributes = Array.from(document.querySelectorAll('input[name="selected_attributes[]"]')).map(input => input.value);
    
    if (selectedAttributes.length === 0) {
        alert('Önce varyant özelliklerini seçin');
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    selectedAttributes.forEach(id => formData.append('attribute_ids[]', id));
    
    fetch(`/admin/products/{{ $product->id }}/generate-variants`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload(); // Reload to show new variants
        } else {
            alert(data.message || 'Varyant oluşturma sırasında hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error generating variants:', error);
        alert('Varyant oluşturma sırasında hata oluştu');
    });
}

function deleteVariant(variantId, button) {
    if (confirm('Bu varyantı silmek istediğinizden emin misiniz?')) {
        fetch(`/admin/product-variants/${variantId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.closest('tr').remove();
                alert('Varyant silindi');
            } else {
                alert(data.message || 'Silme sırasında hata oluştu');
            }
        })
        .catch(error => {
            console.error('Error deleting variant:', error);
            alert('Silme sırasında hata oluştu');
        });
    }
}

function bulkEditVariants() {
    // Implementation for bulk editing variants
    alert('Toplu düzenleme özelliği yakında eklenecek');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleVariantSection();
});
</script>