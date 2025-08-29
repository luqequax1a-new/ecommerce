<div class="row">
    <div class="col-md-6">
        <!-- Category Tree Position -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-sitemap"></i> Kategori Ağacı Konumu
                </h6>
            </div>
            <div class="card-body">
                @if($category->exists)
                    <!-- Breadcrumb -->
                    <div class="mb-3">
                        <label class="form-label">Mevcut Konum</label>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                @if($category->parent)
                                    @foreach($category->getBreadcrumbAttribute() as $crumb)
                                        @if($loop->last)
                                            <li class="breadcrumb-item active">{{ $crumb['name'] }}</li>
                                        @else
                                            <li class="breadcrumb-item">
                                                <a href="{{ route('admin.categories.edit', ['category' => $crumb['slug']]) }}">
                                                    {{ $crumb['name'] }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                @else
                                    <li class="breadcrumb-item active">{{ $category->name }} (Ana Kategori)</li>
                                @endif
                            </ol>
                        </nav>
                    </div>
                @endif

                <!-- Level Info -->
                @if($category->exists)
                    <div class="mb-3">
                        <label class="form-label">Seviye</label>
                        <div class="form-control-plaintext">
                            Seviye {{ $category->level }} ({{ $category->level === 0 ? 'Ana kategori' : 'Alt kategori' }})
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Child Categories -->
        @if($category->exists && $category->children->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="fas fa-folder"></i> Alt Kategoriler ({{ $category->children->count() }})
                </h6>
                <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}" 
                   class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Yeni Alt Kategori
                </a>
            </div>
            <div class="card-body">
                @foreach($category->children->take(10) as $child)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <a href="{{ route('admin.categories.edit', $child) }}" class="text-decoration-none">
                                {{ $child->name }}
                            </a>
                            @if(!$child->is_active)
                                <span class="badge bg-secondary">Pasif</span>
                            @endif
                        </div>
                        <small class="text-muted">
                            {{ $child->products_count ?? 0 }} ürün
                        </small>
                    </div>
                @endforeach
                @if($category->children->count() > 10)
                    <div class="text-muted small">
                        ... ve {{ $category->children->count() - 10 }} kategori daha
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-6">
        <!-- Products in Category -->
        @if($category->exists)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="fas fa-box"></i> Ürünler ({{ $category->products_count ?? $category->products->count() }})
                </h6>
                @if($category->products_count ?? $category->products->count() > 0)
                    <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-filter"></i> Filtrele
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if($category->products_count ?? $category->products->count() > 0)
                    @php
                        $products = $category->products()->latest()->limit(5)->get();
                    @endphp
                    @foreach($products as $product)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-decoration-none">
                                    {{ $product->name }}
                                </a>
                                @if(!$product->is_active)
                                    <span class="badge bg-secondary">Pasif</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                ₺{{ number_format($product->price ?? 0, 2) }}
                            </small>
                        </div>
                    @endforeach
                    @if($category->products->count() > 5)
                        <div class="text-muted small">
                            ... ve {{ $category->products->count() - 5 }} ürün daha
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Bu kategoride henüz ürün bulunmuyor</p>
                        <a href="{{ route('admin.products.create', ['category' => $category->id]) }}" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> İlk Ürünü Ekle
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Statistics -->
        @if($category->exists)
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-bar"></i> İstatistikler
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h5 class="text-primary mb-0">{{ $category->children_count ?? $category->children->count() }}</h5>
                            <small class="text-muted">Alt Kategori</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h5 class="text-success mb-0">{{ $category->products_count ?? $category->products->count() }}</h5>
                        <small class="text-muted">Toplam Ürün</small>
                    </div>
                </div>
                
                @if($category->getTotalProductCountAttribute() > ($category->products_count ?? $category->products->count()))
                    <hr>
                    <div class="text-center">
                        <h6 class="text-info mb-0">{{ $category->getTotalProductCountAttribute() }}</h6>
                        <small class="text-muted">Alt kategoriler dahil toplam ürün</small>
                    </div>
                @endif
                
                <hr>
                <div class="small text-muted">
                    <div class="d-flex justify-content-between">
                        <span>Oluşturulma:</span>
                        <span>{{ $category->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Son güncelleme:</span>
                        <span>{{ $category->updated_at->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>