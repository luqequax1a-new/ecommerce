{{-- Advanced Tab for Brand Management --}}
<div class="row">
    <div class="col-md-8">
        <!-- Custom Fields & Technical Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-cogs"></i> Teknik Ayarlar
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Template -->
                        <div class="mb-3">
                            <label for="template" class="form-label">Özel Şablon</label>
                            <select class="form-select @error('template') is-invalid @enderror" id="template" name="template">
                                <option value="">Varsayılan Şablon</option>
                                <option value="premium" {{ old('template', $brand->template) === 'premium' ? 'selected' : '' }}>Premium Marka</option>
                                <option value="minimal" {{ old('template', $brand->template) === 'minimal' ? 'selected' : '' }}>Minimal Görünüm</option>
                                <option value="showcase" {{ old('template', $brand->template) === 'showcase' ? 'selected' : '' }}>Vitrin Modu</option>
                                <option value="custom" {{ old('template', $brand->template) === 'custom' ? 'selected' : '' }}>Özel Şablon</option>
                            </select>
                            <div class="form-text">Marka sayfası için kullanılacak şablon</div>
                            @error('template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Custom CSS Class -->
                        <div class="mb-3">
                            <label for="css_class" class="form-label">Özel CSS Sınıfı</label>
                            <input type="text" class="form-control @error('css_class') is-invalid @enderror" 
                                   id="css_class" name="css_class" value="{{ old('css_class', $brand->css_class) }}" 
                                   placeholder="brand-premium, custom-layout">
                            <div class="form-text">Marka sayfasına eklenecek CSS sınıfları</div>
                            @error('css_class')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- External ID -->
                        <div class="mb-3">
                            <label for="external_id" class="form-label">Harici ID</label>
                            <input type="text" class="form-control @error('external_id') is-invalid @enderror" 
                                   id="external_id" name="external_id" value="{{ old('external_id', $brand->external_id) }}" 
                                   placeholder="ERP-BRAND-001">
                            <div class="form-text">ERP veya başka sistemdeki marka ID'si</div>
                            @error('external_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- API Key -->
                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Anahtarı</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('api_key') is-invalid @enderror" 
                                       id="api_key" name="api_key" value="{{ old('api_key', $brand->api_key) }}" 
                                       placeholder="Brand API integration key">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateApiKey()">
                                    <i class="fas fa-refresh"></i>
                                </button>
                            </div>
                            <div class="form-text">Marka API entegrasyonu için anahtar</div>
                            @error('api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Attributes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="fas fa-tags"></i> Özel Özellikler
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCustomAttribute()">
                    <i class="fas fa-plus"></i> Özellik Ekle
                </button>
            </div>
            <div class="card-body">
                <div id="customAttributes">
                    @php
                        $customAttributes = old('custom_attributes', $brand->custom_attributes ?? []);
                        if (!is_array($customAttributes)) {
                            $customAttributes = [];
                        }
                    @endphp
                    
                    @forelse($customAttributes as $index => $attribute)
                        <div class="custom-attribute-row mb-3" data-index="{{ $index }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" 
                                           name="custom_attributes[{{ $index }}][key]" 
                                           value="{{ $attribute['key'] ?? '' }}" 
                                           placeholder="Özellik adı">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" 
                                           name="custom_attributes[{{ $index }}][value]" 
                                           value="{{ $attribute['value'] ?? '' }}" 
                                           placeholder="Özellik değeri">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger w-100" 
                                            onclick="removeCustomAttribute({{ $index }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-info-circle"></i> Henüz özel özellik eklenmemiş.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Integration Settings -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-link"></i> Entegrasyon Ayarları
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Social Media -->
                        <div class="mb-3">
                            <label for="social_facebook" class="form-label">Facebook</label>
                            <input type="url" class="form-control @error('social_facebook') is-invalid @enderror" 
                                   id="social_facebook" name="social_facebook" 
                                   value="{{ old('social_facebook', $brand->social_facebook) }}" 
                                   placeholder="https://facebook.com/marka">
                            @error('social_facebook')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="social_instagram" class="form-label">Instagram</label>
                            <input type="url" class="form-control @error('social_instagram') is-invalid @enderror" 
                                   id="social_instagram" name="social_instagram" 
                                   value="{{ old('social_instagram', $brand->social_instagram) }}" 
                                   placeholder="https://instagram.com/marka">
                            @error('social_instagram')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="social_twitter" class="form-label">Twitter</label>
                            <input type="url" class="form-control @error('social_twitter') is-invalid @enderror" 
                                   id="social_twitter" name="social_twitter" 
                                   value="{{ old('social_twitter', $brand->social_twitter) }}" 
                                   placeholder="https://twitter.com/marka">
                            @error('social_twitter')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Analytics -->
                        <div class="mb-3">
                            <label for="google_analytics_id" class="form-label">Google Analytics ID</label>
                            <input type="text" class="form-control @error('google_analytics_id') is-invalid @enderror" 
                                   id="google_analytics_id" name="google_analytics_id" 
                                   value="{{ old('google_analytics_id', $brand->google_analytics_id) }}" 
                                   placeholder="GA-XXXXX-X">
                            @error('google_analytics_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="facebook_pixel_id" class="form-label">Facebook Pixel ID</label>
                            <input type="text" class="form-control @error('facebook_pixel_id') is-invalid @enderror" 
                                   id="facebook_pixel_id" name="facebook_pixel_id" 
                                   value="{{ old('facebook_pixel_id', $brand->facebook_pixel_id) }}" 
                                   placeholder="1234567890123456">
                            @error('facebook_pixel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="gtm_id" class="form-label">Google Tag Manager ID</label>
                            <input type="text" class="form-control @error('gtm_id') is-invalid @enderror" 
                                   id="gtm_id" name="gtm_id" 
                                   value="{{ old('gtm_id', $brand->gtm_id) }}" 
                                   placeholder="GTM-XXXXXXX">
                            @error('gtm_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- System Information -->
        @if($brand->exists)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Sistem Bilgileri
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>{{ $brand->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Oluşturulma:</strong></td>
                            <td>{{ $brand->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Son Güncelleme:</strong></td>
                            <td>{{ $brand->updated_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Slug:</strong></td>
                            <td><code>{{ $brand->slug }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>UUID:</strong></td>
                            <td><code>{{ $brand->uuid ?? 'N/A' }}</code></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Hızlı İstatistikler
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Toplam Ürün:</span>
                        <strong>{{ $brand->products()->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Aktif Ürün:</span>
                        <strong>{{ $brand->activeProducts()->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ortalama Fiyat:</span>
                        <strong>{{ number_format($brand->products()->avg('price') ?? 0, 2) }} ₺</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>SEO Skoru:</span>
                        <strong id="seoScore">-</strong>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history"></i> Son Aktiviteler
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <small class="text-muted">{{ $brand->updated_at->diffForHumans() }}</small>
                                <p class="mb-0">Marka bilgileri güncellendi</p>
                            </div>
                        </div>
                        @if($brand->created_at != $brand->updated_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $brand->created_at->diffForHumans() }}</small>
                                    <p class="mb-0">Marka oluşturuldu</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- New Brand Notice -->
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-save fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Marka Kaydedildikten Sonra</h6>
                    <p class="text-muted small mb-0">
                        Sistem bilgileri ve istatistikler marka kaydedildikten sonra görüntülenecektir.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: absolute;
    left: -24px;
    top: 2px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 10px;
    bottom: -15px;
    width: 1px;
    background: #e9ecef;
}

.timeline-item:last-child::before {
    display: none;
}
</style>

<script>
let attributeIndex = {{ count($customAttributes ?? []) }};

function addCustomAttribute() {
    const container = document.getElementById('customAttributes');
    
    // Remove empty message if exists
    const emptyMessage = container.querySelector('.text-muted');
    if (emptyMessage) {
        emptyMessage.remove();
    }
    
    const row = document.createElement('div');
    row.className = 'custom-attribute-row mb-3';
    row.dataset.index = attributeIndex;
    
    row.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <input type="text" class="form-control" 
                       name="custom_attributes[${attributeIndex}][key]" 
                       placeholder="Özellik adı">
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" 
                       name="custom_attributes[${attributeIndex}][value]" 
                       placeholder="Özellik değeri">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100" 
                        onclick="removeCustomAttribute(${attributeIndex})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(row);
    attributeIndex++;
}

function removeCustomAttribute(index) {
    const row = document.querySelector(`[data-index="${index}"]`);
    if (row) {
        row.remove();
        
        // Show empty message if no attributes left
        const container = document.getElementById('customAttributes');
        if (container.children.length === 0) {
            container.innerHTML = `
                <div class="text-muted text-center py-3">
                    <i class="fas fa-info-circle"></i> Henüz özel özellik eklenmemiş.
                </div>
            `;
        }
    }
}

function generateApiKey() {
    const apiKey = 'brand_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now().toString(36);
    document.getElementById('api_key').value = apiKey;
}

@if($brand->exists)
function calculateSEOScore() {
    let score = 0;
    let maxScore = 10;
    
    // Check meta title
    const metaTitle = '{{ $brand->meta_title }}';
    if (metaTitle && metaTitle.length >= 30 && metaTitle.length <= 60) {
        score += 2;
    } else if (metaTitle && metaTitle.length > 0) {
        score += 1;
    }
    
    // Check meta description
    const metaDesc = '{{ $brand->meta_description }}';
    if (metaDesc && metaDesc.length >= 120 && metaDesc.length <= 160) {
        score += 2;
    } else if (metaDesc && metaDesc.length > 0) {
        score += 1;
    }
    
    // Check slug
    const slug = '{{ $brand->slug }}';
    if (slug && slug.length > 0 && slug.length <= 50) {
        score += 2;
    }
    
    // Check logo
    @if($brand->logo_path)
        score += 1;
    @endif
    
    // Check description
    @if($brand->description)
        score += 1;
    @endif
    
    // Check website URL
    @if($brand->website_url)
        score += 1;
    @endif
    
    // Check social media
    @if($brand->social_facebook || $brand->social_instagram || $brand->social_twitter)
        score += 1;
    @endif
    
    const percentage = Math.round((score / maxScore) * 100);
    const scoreElement = document.getElementById('seoScore');
    
    if (scoreElement) {
        scoreElement.textContent = percentage + '%';
        
        if (percentage >= 80) {
            scoreElement.className = 'text-success';
        } else if (percentage >= 60) {
            scoreElement.className = 'text-warning';
        } else {
            scoreElement.className = 'text-danger';
        }
    }
}

// Calculate SEO score on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateSEOScore();
});
@endif
</script>