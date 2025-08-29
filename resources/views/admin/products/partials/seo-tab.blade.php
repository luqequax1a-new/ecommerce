{{-- SEO & URL Tab - PrestaShop Style --}}
<div class="tab-pane fade" id="seo" role="tabpanel">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-search text-primary me-2"></i>
                        SEO Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="slug" class="form-label required">URL Slug</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                           id="slug" name="slug" value="{{ old('slug', $product->slug) }}" 
                                           maxlength="255">
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateSlug()">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <strong>Önizleme:</strong> 
                                    <span class="text-info">{{ url('/urun') }}/</span><span id="slug-preview">{{ $product->slug }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="canonical_url" class="form-label">Canonical URL</label>
                                <input type="url" class="form-control @error('canonical_url') is-invalid @enderror" 
                                       id="canonical_url" name="canonical_url" 
                                       value="{{ old('canonical_url', $product->canonical_url) }}" 
                                       placeholder="https://example.com/product">
                                @error('canonical_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Boş bırakılırsa otomatik oluşturulur</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control @error('meta_title') is-invalid @enderror" 
                               id="meta_title" name="meta_title" 
                               value="{{ old('meta_title', $product->meta_title) }}" 
                               maxlength="60" onkeyup="updateCharCount('meta_title', 60)">
                        @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <span id="meta_title_count">{{ strlen($product->meta_title ?? '') }}</span>/60 karakter
                            <span class="ms-2" id="meta_title_status"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                  id="meta_description" name="meta_description" rows="3" 
                                  maxlength="160" onkeyup="updateCharCount('meta_description', 160)">{{ old('meta_description', $product->meta_description) }}</textarea>
                        @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <span id="meta_description_count">{{ strlen($product->meta_description ?? '') }}</span>/160 karakter
                            <span class="ms-2" id="meta_description_status"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                               id="meta_keywords" name="meta_keywords" 
                               value="{{ old('meta_keywords', $product->meta_keywords) }}" 
                               placeholder="anahtar kelime, ürün, kategori">
                        @error('meta_keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Virgülle ayırarak yazın (maksimum 10 anahtar kelime)</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="focus_keyword" class="form-label">Odak Anahtar Kelime</label>
                                <input type="text" class="form-control @error('focus_keyword') is-invalid @enderror" 
                                       id="focus_keyword" name="focus_keyword" 
                                       value="{{ old('focus_keyword', $product->focus_keyword) }}" 
                                       placeholder="ana anahtar kelime">
                                @error('focus_keyword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">SEO optimizasyonu için ana anahtar kelime</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="schema_type" class="form-label">Schema.org Türü</label>
                                <select class="form-select @error('schema_type') is-invalid @enderror" 
                                        id="schema_type" name="schema_type">
                                    <option value="Product" {{ old('schema_type', $product->schema_type) == 'Product' ? 'selected' : '' }}>Product</option>
                                    <option value="Book" {{ old('schema_type', $product->schema_type) == 'Book' ? 'selected' : '' }}>Book</option>
                                    <option value="SoftwareApplication" {{ old('schema_type', $product->schema_type) == 'SoftwareApplication' ? 'selected' : '' }}>Software</option>
                                    <option value="CreativeWork" {{ old('schema_type', $product->schema_type) == 'CreativeWork' ? 'selected' : '' }}>Creative Work</option>
                                </select>
                                @error('schema_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- SEO Preview --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-eye text-info me-2"></i>
                        Google Önizleme
                    </h5>
                </div>
                <div class="card-body">
                    <div class="seo-preview p-3 bg-light rounded">
                        <div class="seo-title text-primary fw-bold" id="seo-preview-title">
                            {{ $product->meta_title ?: $product->name }}
                        </div>
                        <div class="seo-url text-success small" id="seo-preview-url">
                            {{ url('/urun/' . $product->slug) }}
                        </div>
                        <div class="seo-description text-muted small mt-1" id="seo-preview-description">
                            {{ $product->meta_description ?: Str::limit(strip_tags($product->description), 160) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO Analysis --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-success me-2"></i>
                        SEO Analizi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="seo-check-item d-flex justify-content-between align-items-center mb-2">
                        <span>Meta Title</span>
                        <span class="badge {{ isset($seoAnalysis['title_optimal']) && $seoAnalysis['title_optimal'] ? 'bg-success' : 'bg-warning' }}">
                            {{ isset($seoAnalysis['title_optimal']) && $seoAnalysis['title_optimal'] ? 'İyi' : 'Geliştirilmeli' }}
                        </span>
                    </div>
                    <div class="seo-check-item d-flex justify-content-between align-items-center mb-2">
                        <span>Meta Description</span>
                        <span class="badge {{ isset($seoAnalysis['description_optimal']) && $seoAnalysis['description_optimal'] ? 'bg-success' : 'bg-warning' }}">
                            {{ isset($seoAnalysis['description_optimal']) && $seoAnalysis['description_optimal'] ? 'İyi' : 'Geliştirilmeli' }}
                        </span>
                    </div>
                    <div class="seo-check-item d-flex justify-content-between align-items-center mb-2">
                        <span>URL Slug</span>
                        <span class="badge {{ isset($seoAnalysis['slug_optimal']) && $seoAnalysis['slug_optimal'] ? 'bg-success' : 'bg-warning' }}">
                            {{ isset($seoAnalysis['slug_optimal']) && $seoAnalysis['slug_optimal'] ? 'İyi' : 'Geliştirilmeli' }}
                        </span>
                    </div>
                    <div class="seo-check-item d-flex justify-content-between align-items-center">
                        <span>Focus Keyword</span>
                        <span class="badge {{ $product->focus_keyword ? 'bg-success' : 'bg-secondary' }}">
                            {{ $product->focus_keyword ? 'Tanımlı' : 'Eksik' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- URL Redirects --}}
            @if($product->urlRewrites && $product->urlRewrites->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-redirect text-warning me-2"></i>
                        URL Yönlendirmeleri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        @foreach($product->urlRewrites()->latest()->take(5)->get() as $rewrite)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted">{{ $rewrite->old_url }}</span>
                                <span class="badge bg-info">301</span>
                            </div>
                        @endforeach
                        @if($product->urlRewrites->count() > 5)
                            <div class="text-muted small">
                                +{{ $product->urlRewrites->count() - 5 }} daha...
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Update character counts and SEO preview
function updateCharCount(fieldId, maxLength) {
    const field = document.getElementById(fieldId);
    const countSpan = document.getElementById(fieldId + '_count');
    const statusSpan = document.getElementById(fieldId + '_status');
    
    const currentLength = field.value.length;
    countSpan.textContent = currentLength;
    
    // Update status
    if (fieldId === 'meta_title') {
        if (currentLength >= 30 && currentLength <= 60) {
            statusSpan.innerHTML = '<i class="fas fa-check text-success"></i>';
        } else {
            statusSpan.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i>';
        }
    } else if (fieldId === 'meta_description') {
        if (currentLength >= 120 && currentLength <= 160) {
            statusSpan.innerHTML = '<i class="fas fa-check text-success"></i>';
        } else {
            statusSpan.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i>';
        }
    }
    
    // Update SEO preview
    updateSeoPreview();
}

// Generate slug from product name
function generateSlug() {
    const name = document.getElementById('name').value;
    if (!name) {
        alert('Önce ürün adını girin');
        return;
    }
    
    // Simple Turkish transliteration
    const slug = name.toLowerCase()
        .replace(/ç/g, 'c')
        .replace(/ğ/g, 'g')
        .replace(/ı/g, 'i')
        .replace(/ş/g, 's')
        .replace(/ü/g, 'u')
        .replace(/ö/g, 'o')
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-')      // Replace spaces with hyphens
        .replace(/-+/g, '-')       // Replace multiple hyphens with single
        .trim('-');                // Remove leading/trailing hyphens
    
    document.getElementById('slug').value = slug;
    document.getElementById('slug-preview').textContent = slug;
    updateSeoPreview();
}

// Update SEO preview
function updateSeoPreview() {
    const title = document.getElementById('meta_title').value || document.getElementById('name').value;
    const description = document.getElementById('meta_description').value || document.getElementById('description').value.substring(0, 160);
    const slug = document.getElementById('slug').value;
    
    document.getElementById('seo-preview-title').textContent = title;
    document.getElementById('seo-preview-url').textContent = '{{ url("/urun") }}/' + slug;
    document.getElementById('seo-preview-description').textContent = description;
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Auto-update slug when name changes
    document.getElementById('name').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (!slugField.value || slugField.value === '') {
            generateSlug();
        }
        updateSeoPreview();
    });
    
    // Update preview when fields change
    ['meta_title', 'meta_description', 'slug'].forEach(fieldId => {
        document.getElementById(fieldId).addEventListener('input', updateSeoPreview);
    });
    
    // Update slug preview when slug changes
    document.getElementById('slug').addEventListener('input', function() {
        document.getElementById('slug-preview').textContent = this.value;
    });
    
    // Initialize character counts
    updateCharCount('meta_title', 60);
    updateCharCount('meta_description', 160);
});
</script>