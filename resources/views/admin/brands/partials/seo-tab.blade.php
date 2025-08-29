{{-- SEO & URL Tab for Brand Management --}}
<div class="row">
    <div class="col-md-8">
        <!-- URL Settings -->
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="slug" class="form-label">URL Slug *</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                           id="slug" name="slug" value="{{ old('slug', $brand->slug) }}" 
                           onkeyup="updateUrlPreview()" {{ $brand->auto_update_slug ? 'readonly' : '' }}>
                    <div id="urlPreview" class="form-text text-info mt-1"></div>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="auto_update_slug" 
                               id="auto_update_slug" value="1" {{ old('auto_update_slug', $brand->auto_update_slug ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="auto_update_slug">Otomatik URL</label>
                    </div>
                    <div class="form-text">İsim değiştiğinde URL'i otomatik güncelle</div>
                </div>
            </div>
        </div>

        <!-- Meta Title -->
        <div class="mb-3">
            <label for="meta_title" class="form-label">Meta Başlık</label>
            <input type="text" class="form-control @error('meta_title') is-invalid @enderror" 
                   id="meta_title" name="meta_title" value="{{ old('meta_title', $brand->meta_title) }}" 
                   maxlength="60" onkeyup="updateMetaPreview()">
            <div class="form-text">
                <span id="metaTitleCount">{{ strlen(old('meta_title', $brand->meta_title ?? '')) }}</span>/60 karakter
                <span class="text-muted">(Boş bırakılırsa marka adı kullanılır)</span>
            </div>
            @error('meta_title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Meta Description -->
        <div class="mb-3">
            <label for="meta_description" class="form-label">Meta Açıklama</label>
            <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                      id="meta_description" name="meta_description" rows="3" 
                      maxlength="160" onkeyup="updateMetaPreview()">{{ old('meta_description', $brand->meta_description) }}</textarea>
            <div class="form-text">
                <span id="metaDescCount">{{ strlen(old('meta_description', $brand->meta_description ?? '')) }}</span>/160 karakter
                <span class="text-muted">(Boş bırakılırsa kısa açıklama kullanılır)</span>
            </div>
            @error('meta_description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Meta Keywords -->
        <div class="mb-3">
            <label for="meta_keywords" class="form-label">Meta Anahtar Kelimeler</label>
            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                   id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $brand->meta_keywords) }}" 
                   placeholder="anahtar kelime, marka adı, kategori">
            <div class="form-text">Virgül ile ayırarak yazın (modern SEO'da çok önemli değil)</div>
            @error('meta_keywords')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Canonical URL -->
        <div class="mb-3">
            <label for="canonical_url" class="form-label">Canonical URL</label>
            <input type="url" class="form-control @error('canonical_url') is-invalid @enderror" 
                   id="canonical_url" name="canonical_url" value="{{ old('canonical_url', $brand->canonical_url) }}" 
                   placeholder="https://example.com/marka/{{ $brand->slug }}">
            <div class="form-text">Boş bırakılırsa otomatik oluşturulur</div>
            @error('canonical_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Robots -->
        <div class="mb-3">
            <label for="robots" class="form-label">Robots Meta</label>
            <select class="form-select @error('robots') is-invalid @enderror" id="robots" name="robots">
                <option value="">Varsayılan (index, follow)</option>
                <option value="index,follow" {{ old('robots', $brand->robots) === 'index,follow' ? 'selected' : '' }}>Index, Follow</option>
                <option value="noindex,follow" {{ old('robots', $brand->robots) === 'noindex,follow' ? 'selected' : '' }}>No Index, Follow</option>
                <option value="index,nofollow" {{ old('robots', $brand->robots) === 'index,nofollow' ? 'selected' : '' }}>Index, No Follow</option>
                <option value="noindex,nofollow" {{ old('robots', $brand->robots) === 'noindex,nofollow' ? 'selected' : '' }}>No Index, No Follow</option>
            </select>
            @error('robots')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Schema Markup -->
        <div class="mb-3">
            <label for="schema_markup" class="form-label">Schema.org İşaretleme</label>
            <textarea class="form-control @error('schema_markup') is-invalid @enderror" 
                      id="schema_markup" name="schema_markup" rows="8" 
                      placeholder='{"@context": "https://schema.org", "@type": "Brand", "name": "{{ $brand->name }}"}'
                      >{{ old('schema_markup', is_array($brand->schema_markup) ? json_encode($brand->schema_markup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $brand->schema_markup) }}</textarea>
            <div class="form-text">
                JSON-LD formatında yapılandırılmış veri (isteğe bağlı)
                <a href="https://schema.org/Brand" target="_blank" class="text-decoration-none">
                    <i class="fas fa-external-link-alt"></i> Schema.org Brand dökümanı
                </a>
            </div>
            @error('schema_markup')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <!-- Google Snippet Preview -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fab fa-google"></i> Google Önizleme
                </h6>
            </div>
            <div class="card-body">
                <div class="google-snippet">
                    <div class="snippet-url text-success" id="snippetUrl">
                        {{ config('app.url') }}/marka/{{ $brand->slug ?? 'marka-adi' }}
                    </div>
                    <div class="snippet-title text-primary fw-bold" id="snippetTitle">
                        {{ $brand->meta_title ?: $brand->name ?: 'Marka Adı' }}
                    </div>
                    <div class="snippet-description text-muted small" id="snippetDescription">
                        {{ $brand->meta_description ?: $brand->short_description ?: 'Marka açıklaması buraya gelecek...' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Analysis -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-line"></i> SEO Analizi
                </h6>
            </div>
            <div class="card-body">
                <div class="seo-check">
                    <div class="seo-item d-flex justify-content-between align-items-center mb-2">
                        <span>Meta Başlık</span>
                        <span class="badge" id="titleBadge">-</span>
                    </div>
                    <div class="seo-item d-flex justify-content-between align-items-center mb-2">
                        <span>Meta Açıklama</span>
                        <span class="badge" id="descBadge">-</span>
                    </div>
                    <div class="seo-item d-flex justify-content-between align-items-center mb-2">
                        <span>URL Slug</span>
                        <span class="badge" id="slugBadge">-</span>
                    </div>
                    <div class="seo-item d-flex justify-content-between align-items-center">
                        <span>Canonical URL</span>
                        <span class="badge" id="canonicalBadge">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Tips -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-lightbulb"></i> SEO İpuçları
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Meta başlık 50-60 karakter arası olmalı</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Meta açıklama 150-160 karakter arası ideal</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> URL slug kısa ve anlamlı olmalı</li>
                    <li class="mb-0"><i class="fas fa-check text-success"></i> Canonical URL tekrarlanan içerik sorununu önler</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.google-snippet {
    font-family: arial, sans-serif;
    font-size: 14px;
    line-height: 1.4;
}

.snippet-url {
    font-size: 12px;
    text-decoration: none;
}

.snippet-title {
    font-size: 16px;
    cursor: pointer;
    text-decoration: none;
}

.snippet-title:hover {
    text-decoration: underline;
}

.snippet-description {
    margin-top: 4px;
    word-wrap: break-word;
}

.seo-check .badge {
    font-size: 10px;
    padding: 4px 6px;
}
</style>

<script>
function updateMetaPreview() {
    // Update character counts
    const metaTitle = document.getElementById('meta_title').value;
    const metaDesc = document.getElementById('meta_description').value;
    
    document.getElementById('metaTitleCount').textContent = metaTitle.length;
    document.getElementById('metaDescCount').textContent = metaDesc.length;
    
    // Update snippet preview
    const brandName = document.getElementById('name')?.value || '{{ $brand->name }}' || 'Marka Adı';
    const shortDesc = '{{ $brand->short_description }}';
    
    document.getElementById('snippetTitle').textContent = metaTitle || brandName;
    document.getElementById('snippetDescription').textContent = metaDesc || shortDesc || 'Marka açıklaması buraya gelecek...';
    
    // Update SEO analysis
    updateSEOAnalysis();
}

function updateSEOAnalysis() {
    const metaTitle = document.getElementById('meta_title').value;
    const metaDesc = document.getElementById('meta_description').value;
    const slug = document.getElementById('slug').value;
    const canonical = document.getElementById('canonical_url').value;
    
    // Title analysis
    const titleBadge = document.getElementById('titleBadge');
    if (metaTitle.length === 0) {
        titleBadge.className = 'badge bg-warning';
        titleBadge.textContent = 'Varsayılan';
    } else if (metaTitle.length >= 50 && metaTitle.length <= 60) {
        titleBadge.className = 'badge bg-success';
        titleBadge.textContent = 'İdeal';
    } else if (metaTitle.length > 60) {
        titleBadge.className = 'badge bg-danger';
        titleBadge.textContent = 'Uzun';
    } else {
        titleBadge.className = 'badge bg-warning';
        titleBadge.textContent = 'Kısa';
    }
    
    // Description analysis
    const descBadge = document.getElementById('descBadge');
    if (metaDesc.length === 0) {
        descBadge.className = 'badge bg-warning';
        descBadge.textContent = 'Varsayılan';
    } else if (metaDesc.length >= 150 && metaDesc.length <= 160) {
        descBadge.className = 'badge bg-success';
        descBadge.textContent = 'İdeal';
    } else if (metaDesc.length > 160) {
        descBadge.className = 'badge bg-danger';
        descBadge.textContent = 'Uzun';
    } else {
        descBadge.className = 'badge bg-warning';
        descBadge.textContent = 'Kısa';
    }
    
    // Slug analysis
    const slugBadge = document.getElementById('slugBadge');
    if (slug && slug.length > 0 && slug.length <= 50) {
        slugBadge.className = 'badge bg-success';
        slugBadge.textContent = 'İyi';
    } else if (slug.length > 50) {
        slugBadge.className = 'badge bg-warning';
        slugBadge.textContent = 'Uzun';
    } else {
        slugBadge.className = 'badge bg-danger';
        slugBadge.textContent = 'Eksik';
    }
    
    // Canonical analysis
    const canonicalBadge = document.getElementById('canonicalBadge');
    if (canonical && canonical.length > 0) {
        canonicalBadge.className = 'badge bg-success';
        canonicalBadge.textContent = 'Özel';
    } else {
        canonicalBadge.className = 'badge bg-info';
        canonicalBadge.textContent = 'Otomatik';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateMetaPreview();
});
</script>