<div class="row">
    <div class="col-md-6">
        <!-- URL Rewrites Management -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-exchange-alt"></i> URL Yönlendirmeleri
                </h6>
            </div>
            <div class="card-body">
                @if(!empty($urlRewrites) && $urlRewrites->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Eski URL</th>
                                    <th>Durum</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($urlRewrites->take(10) as $rewrite)
                                    <tr>
                                        <td>
                                            <code class="small">{{ $rewrite->old_path }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $rewrite->is_active ? 'success' : 'secondary' }}">
                                                {{ $rewrite->status_code }}
                                            </span>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $rewrite->created_at->format('d.m.Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($urlRewrites->count() > 10)
                        <div class="text-muted small">
                            Toplam {{ $urlRewrites->count() }} yönlendirme kaydı
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-route fa-2x mb-2"></i>
                        <p>Henüz URL yönlendirme kaydı bulunmuyor</p>
                        <small>URL değiştiğinde otomatik olarak 301 yönlendirmeleri oluşturulacak</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Schema Markup -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-code"></i> Yapılandırılmış Veri (Schema)
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="schema_markup" class="form-label">JSON-LD Schema</label>
                    <textarea class="form-control @error('schema_markup') is-invalid @enderror" 
                              id="schema_markup" name="schema_markup" rows="6" 
                              style="font-family: monospace; font-size: 12px;">{{ old('schema_markup', $category->schema_markup ? json_encode($category->schema_markup, JSON_PRETTY_PRINT) : '') }}</textarea>
                    <div class="form-text">
                        Geçerli JSON formatında schema.org yapılandırılmış verisi
                        <a href="https://schema.org/Category" target="_blank" class="text-primary">
                            <i class="fas fa-external-link-alt"></i> Schema.org Category
                        </a>
                    </div>
                    @error('schema_markup')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="generateDefaultSchema()">
                    <i class="fas fa-magic"></i> Varsayılan Schema Oluştur
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <!-- Image Management -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-images"></i> Görsel Yönetimi
                </h6>
            </div>
            <div class="card-body">
                @if($category->image_path || $category->banner_path)
                    <!-- Current Images -->
                    @if($category->image_path)
                        <div class="mb-3">
                            <label class="form-label">Mevcut Kapak Görseli</label>
                            <div class="position-relative d-inline-block">
                                <img src="{{ Storage::url($category->image_path) }}" 
                                     class="img-thumbnail" style="max-width: 200px;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                        onclick="removeImage('cover')" title="Görseli Sil">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    @if($category->banner_path)
                        <div class="mb-3">
                            <label class="form-label">Mevcut Banner Görseli</label>
                            <div class="position-relative d-inline-block">
                                <img src="{{ Storage::url($category->banner_path) }}" 
                                     class="img-thumbnail" style="max-width: 200px;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                        onclick="removeImage('banner')" title="Görseli Sil">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-image fa-2x mb-2"></i>
                        <p>Henüz görsel eklenmemiş</p>
                        <small>Genel sekmesinden görsel ekleyebilirsiniz</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Template & Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-cogs"></i> Gelişmiş Ayarlar
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="template" class="form-label">Özel Şablon</label>
                    <select class="form-select" id="template" name="template">
                        <option value="">Varsayılan Şablon</option>
                        <option value="grid" {{ old('template', $category->template) === 'grid' ? 'selected' : '' }}>
                            Grid Görünüm
                        </option>
                        <option value="list" {{ old('template', $category->template) === 'list' ? 'selected' : '' }}>
                            Liste Görünüm
                        </option>
                        <option value="showcase" {{ old('template', $category->template) === 'showcase' ? 'selected' : '' }}>
                            Vitrin Görünüm
                        </option>
                    </select>
                    <div class="form-text">Kategoriye özel görünüm şablonu</div>
                </div>

                <div class="mb-3">
                    <label for="icon_class" class="form-label">İkon Sınıfı</label>
                    <input type="text" class="form-control" id="icon_class" name="icon_class" 
                           value="{{ old('icon_class', $category->icon_class) }}" 
                           placeholder="fas fa-shopping-bag">
                    <div class="form-text">
                        FontAwesome icon sınıfı 
                        <a href="https://fontawesome.com/icons" target="_blank" class="text-primary">
                            <i class="fas fa-external-link-alt"></i> İkon Arama
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cache & Performance -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-bolt"></i> Performans
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <strong>Kategori Cache</strong>
                        <br>
                        <small class="text-muted">Kategori ağacı ve menü önbelleği</small>
                    </div>
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="clearCategoryCache()">
                        <i class="fas fa-trash"></i> Temizle
                    </button>
                </div>
                
                <div class="alert alert-info small">
                    <i class="fas fa-info-circle"></i>
                    Bu kategori güncellendiğinde ilgili önbellekler otomatik olarak temizlenir.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateDefaultSchema() {
    const name = document.getElementById('name').value || 'Kategori Adı';
    const description = document.getElementById('meta_description').value || document.getElementById('description').value || '';
    const url = '{{ config("app.url") }}/kategori/' + (document.getElementById('slug').value || 'kategori-adi');
    
    const schema = {
        "@context": "https://schema.org",
        "@type": "Thing", 
        "name": name,
        "description": description,
        "url": url
    };
    
    document.getElementById('schema_markup').value = JSON.stringify(schema, null, 2);
}

function removeImage(type) {
    if (confirm('Bu görseli silmek istediğinizden emin misiniz?')) {
        // Add hidden input to mark for deletion
        const form = document.querySelector('form');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_' + type + '_image';
        input.value = '1';
        form.appendChild(input);
        
        // Hide the image container
        event.target.closest('.position-relative').style.display = 'none';
    }
}

function clearCategoryCache() {
    if (confirm('Kategori önbelleğini temizlemek istediğinizden emin misiniz?')) {
        fetch('/admin/categories/clear-cache', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('Önbellek başarıyla temizlendi.');
              }
          }).catch(error => {
              alert('Önbellek temizlenirken hata oluştu.');
          });
    }
}
</script>