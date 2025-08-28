@foreach($categories as $category)
<div class="tree-item border rounded mb-2 p-3" data-category-id="{{ $category->id }}">
    <div class="d-flex align-items-center">
        <div class="form-check me-3">
            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" value="{{ $category->id }}">
        </div>
        
        @if($category->children->count() > 0)
            <button class="btn btn-sm btn-outline-secondary me-2" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#children-{{ $category->id }}">
                <i class="fas fa-chevron-down"></i>
            </button>
        @else
            <span class="me-4"></span>
        @endif

        <div class="flex-grow-1">
            <div class="d-flex align-items-center">
                @if($category->image_path)
                    <img src="{{ Storage::url($category->image_path) }}" alt="{{ $category->name }}" 
                         class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                @else
                    <div class="bg-light border rounded me-2 d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px;">
                        <i class="fas fa-folder text-muted"></i>
                    </div>
                @endif

                <div>
                    <h6 class="mb-0">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="text-decoration-none">
                            {{ $category->name }}
                        </a>
                        @if(!$category->is_active)
                            <span class="badge bg-secondary">Pasif</span>
                        @endif
                        @if($category->featured)
                            <span class="badge bg-warning">Öne Çıkan</span>
                        @endif
                    </h6>
                    <small class="text-muted">
                        <i class="fas fa-link"></i> /kategori/{{ $category->getFullPath() }}
                        @if($category->products_count ?? 0 > 0)
                            • {{ $category->products_count }} ürün
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <button class="btn btn-sm {{ $category->is_active ? 'btn-success' : 'btn-secondary' }}" 
                    onclick="toggleCategoryStatus({{ $category->id }})" 
                    title="{{ $category->is_active ? 'Aktif' : 'Pasif' }}">
                <i class="fas fa-{{ $category->is_active ? 'eye' : 'eye-slash' }}"></i>
            </button>
            
            <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}" 
               class="btn btn-sm btn-primary" title="Alt Kategori Ekle">
                <i class="fas fa-plus"></i>
            </a>
            
            <a href="{{ route('admin.categories.edit', $category) }}" 
               class="btn btn-sm btn-outline-primary" title="Düzenle">
                <i class="fas fa-edit"></i>
            </a>
            
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" 
                  class="d-inline" onsubmit="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil"
                        {{ $category->children->count() > 0 || $category->products_count ?? 0 > 0 ? 'disabled' : '' }}>
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>

    @if($category->children->count() > 0)
        <div class="collapse mt-3" id="children-{{ $category->id }}">
            <div class="nested-sortable border-start border-primary ms-4 ps-3" data-parent-id="{{ $category->id }}">
                @include('admin.categories.partials.tree-node', ['categories' => $category->children, 'level' => $level + 1])
            </div>
        </div>
    @endif
</div>
@endforeach