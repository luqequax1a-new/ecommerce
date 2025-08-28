<div class="row">
    <div class="col-md-8">
        <!-- URL & Slug -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-link"></i> Friendly URL
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="slug" class="form-label">URL Slug</label>
                    <div class="input-group">
                        <span class="input-group-text">/kategori/</span>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                               id="slug" name="slug" value="{{ old('slug', $category->slug) }}" 
                               onkeyup="updateUrlPreview()">
                        <button type="button" class="btn btn-outline-secondary" onclick="generateSlugPreview()">
                            <i class="fas fa-magic"></i> Oluştur
                        </button>
                    </div>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="auto_update_slug" 
                           id="auto_update_slug" value="1" {{ old('auto_update_slug', $category->auto_update_slug ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="auto_update_slug">
                        İsim değiştiğinde URL'yi otomatik güncelle
                    </label>
                    <div class="form-text">Kapalı olduğunda URL manuel olarak kontrol edilir</div>
                </div>

                <!-- URL Preview -->
                <div id="urlPreview" class="alert alert-info">
                    <strong>URL Önizleme:</strong> 
                    <a href="/kategori/{{ $category->generateCategoryPath() }}" target="_blank" id="previewLink">
                        /kategori/{{ $category->generateCategoryPath() }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Meta Tags -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-tags"></i> Meta Etiketleri
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="meta_title" class="form-label">
                        Meta Title 
                        <span class="text-muted">(Maks. 60 karakter)</span>
                    </label>
                    <input type="text" class="form-control @error('meta_title') is-invalid @enderror" 
                           id="meta_title" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" 
                           maxlength="60" onkeyup="updateCharCount('meta_title', 60)">
                    <div class="form-text">
                        <span id="meta_title_count">{{ strlen($category->meta_title ?? '') }}</span>/60 karakter
                        @if($category->exists && isset($seoAnalysis['title_optimal']) && !$seoAnalysis['title_optimal'])
                            <span class="text-warning ms-2">⚠️ Önerilen uzunluğu aşıyor</span>
                        @endif
                    </div>
                    @error('meta_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="meta_description" class="form-label">
                        Meta Description 
                        <span class="text-muted">(Maks. 160 karakter)</span>
                    </label>
                    <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                              id="meta_description" name="meta_description" rows="3" 
                              maxlength="160" onkeyup="updateCharCount('meta_description', 160)">{{ old('meta_description', $category->meta_description) }}</textarea>
                    <div class="form-text">
                        <span id="meta_description_count">{{ strlen($category->meta_description ?? '') }}</span>/160 karakter
                        @if($category->exists && isset($seoAnalysis['description_optimal']) && !$seoAnalysis['description_optimal'])
                            <span class="text-warning ms-2">⚠️ Önerilen uzunluğu aşıyor</span>
                        @endif
                    </div>
                    @error('meta_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                    <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                           id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $category->meta_keywords) }}">
                    <div class="form-text">Virgülle ayrılmış anahtar kelimeler</div>
                    @error('meta_keywords')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Canonical & Robots -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-robot"></i> SEO Ayarları
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="canonical_url" class="form-label">Canonical URL</label>
                    <input type="url" class="form-control @error('canonical_url') is-invalid @enderror" 
                           id="canonical_url" name="canonical_url" value="{{ old('canonical_url', $category->canonical_url) }}">
                    <div class="form-text">Boş bırakılırsa otomatik oluşturulur</div>
                    @error('canonical_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="robots" class="form-label">Robots Meta</label>
                    <select class="form-select @error('robots') is-invalid @enderror" id="robots" name="robots">
                        <option value="index,follow" {{ old('robots', $category->robots ?? 'index,follow') === 'index,follow' ? 'selected' : '' }}>
                            Index, Follow (Varsayılan)
                        </option>
                        <option value="noindex,nofollow" {{ old('robots', $category->robots) === 'noindex,nofollow' ? 'selected' : '' }}>
                            No Index, No Follow
                        </option>
                        <option value="index,nofollow" {{ old('robots', $category->robots) === 'index,nofollow' ? 'selected' : '' }}>
                            Index, No Follow
                        </option>
                        <option value="noindex,follow" {{ old('robots', $category->robots) === 'noindex,follow' ? 'selected' : '' }}>
                            No Index, Follow
                        </option>
                    </select>
                    @error('robots')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- SEO Preview -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-search"></i> Google Önizleme
                </h6>
            </div>
            <div class="card-body">
                <div class="seo-preview border rounded p-3" style="font-family: arial, sans-serif;">
                    <div id="seo-preview-title" class="text-primary" style="font-size: 18px; line-height: 1.3;">
                        {{ $category->meta_title ?: $category->name ?: 'Kategori Başlığı' }}
                    </div>
                    <div id="seo-preview-url" class="text-success" style="font-size: 14px;">
                        {{ config('app.url') }}/kategori/{{ $category->generateCategoryPath() }}
                    </div>
                    <div id="seo-preview-description" class="text-muted" style="font-size: 13px; line-height: 1.4;">
                        {{ $category->meta_description ?: $category->short_description ?: 'Bu kategori hakkında açıklama burada görünecektir.' }}
                    </div>
                </div>
            </div>
        </div>

        @if($category->exists && isset($seoAnalysis))
        <!-- SEO Analysis -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-line"></i> SEO Analizi
                </h6>
            </div>
            <div class="card-body">
                <div class="seo-check mb-2">
                    <i class="fas fa-{{ $seoAnalysis['title_optimal'] ? 'check text-success' : 'exclamation-triangle text-warning' }}"></i>
                    Meta Title ({{ $seoAnalysis['title_length'] }}/60)
                </div>
                <div class="seo-check mb-2">
                    <i class="fas fa-{{ $seoAnalysis['description_optimal'] ? 'check text-success' : 'exclamation-triangle text-warning' }}"></i>
                    Meta Description ({{ $seoAnalysis['description_length'] }}/160)
                </div>
                <div class="seo-check mb-2">
                    <i class="fas fa-{{ $seoAnalysis['has_canonical'] ? 'check text-success' : 'times text-danger' }}"></i>
                    Canonical URL
                </div>
                <div class="seo-check">
                    <i class="fas fa-{{ $seoAnalysis['slug_valid'] ? 'check text-success' : 'times text-danger' }}"></i>
                    Geçerli Slug Format
                </div>
            </div>
        </div>
        @endif

        @if($category->exists && !empty($urlRewrites))
        <!-- URL Rewrites History -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-history"></i> URL Geçmişi
                </h6>
            </div>
            <div class="card-body">
                @foreach($urlRewrites->take(5) as $rewrite)
                    <div class="small text-muted mb-1">
                        <i class="fas fa-arrow-right"></i>
                        <code>{{ $rewrite->old_path }}</code>
                        <br>
                        <span class="text-muted">{{ $rewrite->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                @endforeach
                @if($urlRewrites->count() > 5)
                    <div class="small text-muted">
                        ... ve {{ $urlRewrites->count() - 5 }} tane daha
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function updateCharCount(fieldId, maxLength) {
    const field = document.getElementById(fieldId);
    const counter = document.getElementById(fieldId + '_count');
    if (field && counter) {
        counter.textContent = field.value.length;
        
        // Update SEO preview
        updateSeoPreview();
    }
}

function updateSeoPreview() {
    const title = document.getElementById('meta_title').value || document.getElementById('name').value || 'Kategori Başlığı';
    const description = document.getElementById('meta_description').value || 'Bu kategori hakkında açıklama burada görünecektir.';
    const slug = document.getElementById('slug').value || 'kategori-adi';
    
    document.getElementById('seo-preview-title').textContent = title;
    document.getElementById('seo-preview-description').textContent = description;
    document.getElementById('seo-preview-url').textContent = '{{ config("app.url") }}/kategori/' + slug;
}

// Update preview when fields change
document.addEventListener('DOMContentLoaded', function() {
    ['meta_title', 'meta_description', 'slug'].forEach(function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', updateSeoPreview);
        }
    });
    
    updateSeoPreview();
});
</script>