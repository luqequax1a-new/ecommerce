{{-- Images Tab - PrestaShop Style Gallery Management --}}
<div class="tab-pane fade" id="images" role="tabpanel">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-images text-primary me-2"></i>
                Ürün Görselleri
                <span class="badge bg-info ms-2">{{ $product->images->count() }}/10</span>
            </h5>
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-sm" id="upload-images-btn">
                    <i class="fas fa-upload me-1"></i>
                    Görsel Yükle
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#bulk-upload-modal">
                    <i class="fas fa-layer-group me-1"></i>
                    Toplu Yükleme
                </button>
            </div>
        </div>
        
        <div class="card-body">
            {{-- Upload Area --}}
            <div class="upload-area mb-4" id="upload-area">
                <div class="upload-zone border-2 border-dashed border-light rounded p-4 text-center bg-light">
                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Görselleri sürükleyip bırakın veya seçin</h5>
                    <p class="text-muted mb-3">Desteklenen formatlar: JPEG, PNG, GIF, WebP (Max: 10MB)</p>
                    <input type="file" id="image-upload-input" name="images[]" multiple accept="image/*" class="d-none">
                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('image-upload-input').click()">
                        <i class="fas fa-folder-open me-1"></i>
                        Dosyaları Seç
                    </button>
                </div>
            </div>
            
            {{-- Image Gallery --}}
            <div class="image-gallery">
                <div class="row g-3" id="image-gallery-container">
                    @forelse($product->orderedImages as $image)
                        <div class="col-md-3 col-sm-4 col-6" data-image-id="{{ $image->id }}">
                            <div class="image-item position-relative">
                                <div class="image-wrapper">
                                    <img src="{{ Storage::url($image->path) }}" 
                                         alt="{{ $image->alt_text }}" 
                                         class="img-fluid rounded shadow-sm">
                                    
                                    {{-- Image Overlay --}}
                                    <div class="image-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" 
                                                    onclick="editImage({{ $image->id }})" 
                                                    title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    onclick="setCoverImage({{ $image->id }})"
                                                    title="Ana Görsel Yap"
                                                    @if($image->is_cover) disabled @endif>
                                                <i class="fas fa-star"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="deleteImage({{ $image->id }})"
                                                    title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {{-- Drag Handle --}}
                                    <div class="drag-handle position-absolute top-0 end-0 m-2">
                                        <i class="fas fa-grip-vertical text-white bg-dark px-1 rounded"></i>
                                    </div>
                                    
                                    {{-- Badges --}}
                                    <div class="image-badges position-absolute bottom-0 start-0 m-2">
                                        @if($image->is_cover)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-star me-1"></i>Ana Görsel
                                            </span>
                                        @endif
                                        
                                        @if($image->is_variant_specific)
                                            <span class="badge bg-info">
                                                <i class="fas fa-layer-group me-1"></i>Varyant
                                            </span>
                                        @endif
                                        
                                        <span class="badge bg-secondary">
                                            {{ number_format($image->file_size / 1024, 1) }}KB
                                        </span>
                                    </div>
                                </div>
                                
                                {{-- Image Info --}}
                                <div class="image-info mt-2">
                                    <small class="text-muted d-block">{{ $image->original_filename }}</small>
                                    @if($image->alt_text)
                                        <small class="text-success d-block">{{ Str::limit($image->alt_text, 30) }}</small>
                                    @else
                                        <small class="text-warning d-block">Alt text eksik</small>
                                    @endif
                                    <small class="text-info">{{ $image->width }}x{{ $image->height }}px</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Henüz görsel yüklenmemiş</h5>
                                <p class="text-muted">İlk görselinizi yüklemek için yukarıdaki alanı kullanın</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
            
            {{-- Progress Bar for Upload --}}
            <div class="upload-progress mt-3 d-none">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%">
                    </div>
                </div>
                <small class="text-muted mt-1 d-block">Görseller yükleniyor...</small>
            </div>
        </div>
    </div>
</div>

{{-- Image Edit Modal --}}
<div class="modal fade" id="edit-image-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Görsel Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-image-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <img id="edit-image-preview" class="img-fluid rounded" style="max-height: 300px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Alt Text (SEO)</label>
                                <input type="text" class="form-control" id="edit-alt-text" placeholder="Görsel açıklaması">
                                <small class="form-text text-muted">SEO için önemli - görseli tanımlayan metin</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Görsel Türü</label>
                                <select class="form-select" id="edit-image-type">
                                    <option value="product">Ürün Görseli</option>
                                    <option value="detail">Detay Görseli</option>
                                    <option value="lifestyle">Lifestyle</option>
                                    <option value="variant">Varyant Görseli</option>
                                </select>
                            </div>
                            
                            <div class="mb-3" id="variant-association-section">
                                <label class="form-label">Varyant İlişkilendirme</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is-variant-specific">
                                    <label class="form-check-label" for="is-variant-specific">
                                        Sadece belirli varyantlar için
                                    </label>
                                </div>
                                
                                <div id="variant-selection" class="mt-2 d-none">
                                    @if($product->variants->count() > 0)
                                        <small class="text-muted d-block mb-2">Görselin gösterileceği varyantları seçin:</small>
                                        <div class="variant-checkboxes" style="max-height: 150px; overflow-y: auto;">
                                            @foreach($product->variants as $variant)
                                                <div class="form-check">
                                                    <input class="form-check-input variant-checkbox" 
                                                           type="checkbox" 
                                                           value="{{ $variant->id }}"
                                                           id="variant-{{ $variant->id }}">
                                                    <label class="form-check-label" for="variant-{{ $variant->id }}">
                                                        {{ $variant->sku }} 
                                                        @if($variant->attributes)
                                                            <small class="text-muted">
                                                                ({{ collect($variant->attributes)->map(fn($v, $k) => "$k: $v")->join(', ') }})
                                                            </small>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <small class="text-warning">Henüz varyant oluşturulmamış</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="saveImageChanges()">
                    <i class="fas fa-save me-1"></i>Değişiklikleri Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Upload Modal --}}
<div class="modal fade" id="bulk-upload-modal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Toplu Görsel Yükleme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="upload-zone-bulk border-2 border-dashed border-primary rounded p-4 text-center">
                            <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                            <p class="mb-2">Birden fazla görsel seçin</p>
                            <input type="file" id="bulk-upload-input" multiple accept="image/*" class="d-none">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('bulk-upload-input').click()">
                                Dosyaları Seç
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="bulk-upload-preview" class="d-none">
                            <h6>Seçilen Görseller:</h6>
                            <div id="bulk-preview-container" style="max-height: 300px; overflow-y: auto;">
                                <!-- Preview items will be added here -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="bulk-auto-alt">
                            <label class="form-check-label" for="bulk-auto-alt">
                                Dosya adından otomatik alt text oluştur
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="startBulkUpload()" id="bulk-upload-btn" disabled>
                    <i class="fas fa-upload me-1"></i>Tümünü Yükle
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.image-item {
    transition: transform 0.2s ease;
}

.image-item:hover {
    transform: translateY(-2px);
}

.image-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
}

.image-overlay {
    background: rgba(0,0,0,0.7);
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 8px;
}

.image-item:hover .image-overlay {
    opacity: 1;
}

.upload-zone {
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-zone:hover {
    border-color: #007bff !important;
    background-color: #f8f9fa !important;
}

.upload-zone.dragover {
    border-color: #007bff !important;
    background-color: #e3f2fd !important;
}

.drag-handle {
    cursor: move;
}

.sortable-ghost {
    opacity: 0.5;
}

.sortable-chosen {
    transform: scale(1.05);
}

.variant-checkboxes {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    background-color: #f8f9fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag and drop functionality
    initializeImageGallery();
    initializeUploadArea();
    initializeBulkUpload();
});

function initializeImageGallery() {
    // Make gallery sortable
    const gallery = document.getElementById('image-gallery-container');
    if (gallery && typeof Sortable !== 'undefined') {
        new Sortable(gallery, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            handle: '.drag-handle',
            onEnd: function(evt) {
                updateImageOrder();
            }
        });
    }
}

function initializeUploadArea() {
    const uploadArea = document.getElementById('upload-area');
    const uploadInput = document.getElementById('image-upload-input');
    
    // Drag and drop handlers
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.querySelector('.upload-zone').classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.querySelector('.upload-zone').classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.querySelector('.upload-zone').classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        handleFileUpload(files);
    });
    
    // File input change handler
    uploadInput.addEventListener('change', function(e) {
        handleFileUpload(e.target.files);
    });
    
    // Upload button click handler
    document.getElementById('upload-images-btn').addEventListener('click', function() {
        uploadInput.click();
    });
}

function handleFileUpload(files) {
    if (files.length === 0) return;
    
    // Validate files
    const validFiles = Array.from(files).filter(file => {
        if (!file.type.startsWith('image/')) {
            showAlert('Sadece resim dosyaları desteklenir: ' + file.name, 'warning');
            return false;
        }
        if (file.size > 10 * 1024 * 1024) { // 10MB
            showAlert('Dosya boyutu çok büyük (Max: 10MB): ' + file.name, 'warning');
            return false;
        }
        return true;
    });
    
    if (validFiles.length === 0) return;
    
    // Show progress
    const progressContainer = document.querySelector('.upload-progress');
    const progressBar = progressContainer.querySelector('.progress-bar');
    progressContainer.classList.remove('d-none');
    
    // Create FormData
    const formData = new FormData();
    validFiles.forEach(file => {
        formData.append('images[]', file);
    });
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    // Upload files
    fetch(`/admin/products/{{ $product->id }}/images/bulk-upload`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        progressContainer.classList.add('d-none');
        
        if (data.success) {
            showAlert(data.message, 'success');
            // Reload the page to show new images
            location.reload();
        } else {
            showAlert(data.message || 'Yükleme sırasında hata oluştu', 'danger');
        }
    })
    .catch(error => {
        progressContainer.classList.add('d-none');
        showAlert('Yükleme sırasında hata oluştu', 'danger');
        console.error('Upload error:', error);
    });
}

function editImage(imageId) {
    // Fetch image data and show edit modal
    fetch(`/admin/product-images/${imageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const image = data.image;
                
                // Populate modal
                document.getElementById('edit-image-preview').src = image.url;
                document.getElementById('edit-alt-text').value = image.alt_text || '';
                document.getElementById('edit-image-type').value = image.image_type || 'product';
                document.getElementById('is-variant-specific').checked = image.is_variant_specific;
                
                // Handle variant selection
                toggleVariantSelection();
                if (image.is_variant_specific && image.variant_ids) {
                    image.variant_ids.forEach(id => {
                        const checkbox = document.getElementById(`variant-${id}`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
                
                // Store image ID for saving
                document.getElementById('edit-image-form').dataset.imageId = imageId;
                
                // Show modal
                new bootstrap.Modal(document.getElementById('edit-image-modal')).show();
            }
        })
        .catch(error => {
            showAlert('Görsel bilgileri alınamadı', 'danger');
            console.error('Error:', error);
        });
}

function saveImageChanges() {
    const form = document.getElementById('edit-image-form');
    const imageId = form.dataset.imageId;
    
    const formData = {
        alt_text: document.getElementById('edit-alt-text').value,
        image_type: document.getElementById('edit-image-type').value,
        is_variant_specific: document.getElementById('is-variant-specific').checked,
        variant_ids: []
    };
    
    // Get selected variants
    if (formData.is_variant_specific) {
        document.querySelectorAll('.variant-checkbox:checked').forEach(checkbox => {
            formData.variant_ids.push(parseInt(checkbox.value));
        });
    }
    
    fetch(`/admin/product-images/${imageId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('edit-image-modal')).hide();
            // Refresh the gallery
            location.reload();
        } else {
            showAlert(data.message || 'Kaydetme sırasında hata oluştu', 'danger');
        }
    })
    .catch(error => {
        showAlert('Kaydetme sırasında hata oluştu', 'danger');
        console.error('Error:', error);
    });
}

function setCoverImage(imageId) {
    fetch(`/admin/product-images/${imageId}/set-cover`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            location.reload();
        } else {
            showAlert(data.message || 'Hata oluştu', 'danger');
        }
    })
    .catch(error => {
        showAlert('Hata oluştu', 'danger');
        console.error('Error:', error);
    });
}

function deleteImage(imageId) {
    if (confirm('Bu görseli silmek istediğinizden emin misiniz?')) {
        fetch(`/admin/product-images/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                // Remove image from DOM
                document.querySelector(`[data-image-id="${imageId}"]`).remove();
                
                // Update counter
                const counter = document.querySelector('.badge.bg-info');
                if (counter) {
                    const currentCount = parseInt(counter.textContent.split('/')[0]);
                    counter.textContent = `${currentCount - 1}/10`;
                }
            } else {
                showAlert(data.message || 'Silme sırasında hata oluştu', 'danger');
            }
        })
        .catch(error => {
            showAlert('Silme sırasında hata oluştu', 'danger');
            console.error('Error:', error);
        });
    }
}

function updateImageOrder() {
    const imageIds = Array.from(document.querySelectorAll('[data-image-id]'))
                           .map(el => parseInt(el.dataset.imageId));
    
    fetch('/admin/product-images/update-order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ image_ids: imageIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Görsel sıralaması güncellendi', 'success');
        }
    })
    .catch(error => {
        console.error('Order update error:', error);
    });
}

function toggleVariantSelection() {
    const isVariantSpecific = document.getElementById('is-variant-specific').checked;
    const variantSelection = document.getElementById('variant-selection');
    
    if (isVariantSpecific) {
        variantSelection.classList.remove('d-none');
    } else {
        variantSelection.classList.add('d-none');
        // Uncheck all variant checkboxes
        document.querySelectorAll('.variant-checkbox').forEach(cb => cb.checked = false);
    }
}

// Event listener for variant specific checkbox
document.getElementById('is-variant-specific').addEventListener('change', toggleVariantSelection);

function initializeBulkUpload() {
    const bulkInput = document.getElementById('bulk-upload-input');
    const previewContainer = document.getElementById('bulk-preview-container');
    const uploadBtn = document.getElementById('bulk-upload-btn');
    
    bulkInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files).filter(file => file.type.startsWith('image/'));
        
        if (files.length > 0) {
            document.getElementById('bulk-upload-preview').classList.remove('d-none');
            previewContainer.innerHTML = '';
            
            files.forEach((file, index) => {
                const div = document.createElement('div');
                div.className = 'bulk-preview-item d-flex align-items-center mb-2 p-2 border rounded';
                div.innerHTML = `
                    <div class="me-2">
                        <i class="fas fa-image text-muted"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">${file.name}</div>
                        <small class="text-muted">${(file.size / 1024).toFixed(1)} KB</small>
                    </div>
                `;
                previewContainer.appendChild(div);
            });
            
            uploadBtn.disabled = false;
        } else {
            document.getElementById('bulk-upload-preview').classList.add('d-none');
            uploadBtn.disabled = true;
        }
    });
}

function startBulkUpload() {
    const files = document.getElementById('bulk-upload-input').files;
    const autoAlt = document.getElementById('bulk-auto-alt').checked;
    
    if (files.length === 0) return;
    
    const formData = new FormData();
    Array.from(files).forEach(file => {
        formData.append('images[]', file);
    });
    formData.append('auto_alt', autoAlt);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    // Disable button and show loading
    document.getElementById('bulk-upload-btn').disabled = true;
    document.getElementById('bulk-upload-btn').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Yükleniyor...';
    
    fetch(`/admin/products/{{ $product->id }}/images/bulk-upload`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('bulk-upload-modal')).hide();
            location.reload();
        } else {
            showAlert(data.message || 'Yükleme sırasında hata oluştu', 'danger');
        }
    })
    .catch(error => {
        showAlert('Yükleme sırasında hata oluştu', 'danger');
        console.error('Error:', error);
    })
    .finally(() => {
        document.getElementById('bulk-upload-btn').disabled = false;
        document.getElementById('bulk-upload-btn').innerHTML = '<i class="fas fa-upload me-1"></i>Tümünü Yükle';
    });
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
</script>