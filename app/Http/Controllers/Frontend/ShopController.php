<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\UrlRewrite;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    /**
     * Homepage - ana sayfa
     */
    public function home()
    {
        // Ana sayfa için cache'li veri getir
        $cacheKey = 'homepage_data';
        $data = Cache::remember($cacheKey, 3600, function () {
            return [
                'featured_products' => Product::active()
                    ->featured()
                    ->with(['brand', 'category', 'orderedImages'])
                    ->limit(8)
                    ->get(),
                'featured_categories' => Category::active()
                    ->featured()
                    ->withCount(['products' => function ($query) {
                        $query->active();
                    }])
                    ->limit(6)
                    ->get(),
                'featured_brands' => Brand::active()
                    ->featured()
                    ->withCount(['products' => function ($query) {
                        $query->active();
                    }])
                    ->limit(12)
                    ->get(),
                'new_products' => Product::active()
                    ->with(['brand', 'category', 'orderedImages'])
                    ->latest()
                    ->limit(8)
                    ->get(),
            ];
        });

        // SEO Meta bilgileri
        $seoData = [
            'title' => config('app.name') . ' - E-Ticaret Sitesi',
            'description' => 'En kaliteli ürünleri uygun fiyatlarla. Hızlı kargo, güvenilir alışveriş.',
            'keywords' => 'e-ticaret, alışveriş, ürün, kaliteli, uygun fiyat',
            'canonical_url' => url('/'),
            'og_image' => asset('images/logo-og.jpg'),
        ];

        return view('frontend.home', compact('data', 'seoData'));
    }

    /**
     * Category page with hierarchical path support
     * Route: /kategori/{path}
     */
    public function categoryShow(Request $request, string $path)
    {
        // Path'i split et (örn: "elektronik/bilgisayar/laptop")
        $pathSegments = explode('/', trim($path, '/'));
        $lastSlug = end($pathSegments);

        // Önce mevcut category'yi bul
        $category = Category::active()
            ->where('slug', $lastSlug)
            ->with(['parent', 'children' => function ($query) {
                $query->active()->ordered();
            }])
            ->first();

        if (!$category) {
            // URL rewrite kontrolü
            $rewrite = UrlRewrite::where('old_url', $request->getPathInfo())->first();
            if ($rewrite) {
                return redirect($rewrite->new_url, 301);
            }
            
            abort(404, 'Kategori bulunamadı');
        }

        // Path doğrulaması - full path oluştur
        $fullPath = $this->buildCategoryPath($category);
        if ($path !== $fullPath) {
            // Canonical URL'e yönlendir
            return redirect(route('category.show', ['path' => $fullPath]), 301);
        }

        // Filtreleme parametreleri
        $filters = $this->parseFilters($request);
        
        // Ürünleri getir
        $productsQuery = Product::active()
            ->whereHas('category', function ($query) use ($category) {
                $query->where('id', $category->id)
                      ->orWhere('path', 'like', '%,' . $category->id . ',%');
            })
            ->with(['brand', 'orderedImages', 'variants' => function ($query) {
                $query->active()->orderBy('price');
            }]);

        // Filtreleri uygula
        $this->applyProductFilters($productsQuery, $filters);

        // Sıralama
        $sort = $request->get('sort', 'name');
        $this->applySorting($productsQuery, $sort);

        // Pagination
        $products = $productsQuery->paginate(24)->withQueryString();

        // Alt kategoriler
        $subcategories = $category->children()
            ->active()
            ->withCount(['products' => function ($query) {
                $query->active();
            }])
            ->ordered()
            ->get();

        // Marka filtresi için veriler
        $brands = Brand::active()
            ->whereHas('products', function ($query) use ($category) {
                $query->active()
                      ->whereHas('category', function ($q) use ($category) {
                          $q->where('id', $category->id)
                            ->orWhere('path', 'like', '%,' . $category->id . ',%');
                      });
            })
            ->withCount(['products' => function ($query) use ($category) {
                $query->active()
                      ->whereHas('category', function ($q) use ($category) {
                          $q->where('id', $category->id)
                            ->orWhere('path', 'like', '%,' . $category->id . ',%');
                      });
            }])
            ->orderBy('name')
            ->get();

        // Breadcrumb oluştur
        $breadcrumbs = $this->buildCategoryBreadcrumbs($category);

        // SEO Meta bilgileri
        $seoData = [
            'title' => $category->meta_title ?: ($category->name . ' - ' . config('app.name')),
            'description' => $category->meta_description ?: 
                           ($category->description ? Str::limit(strip_tags($category->description), 160) : 
                            $category->name . ' kategorisinde en kaliteli ürünler'),
            'keywords' => $category->meta_keywords ?: $category->name,
            'canonical_url' => route('category.show', ['path' => $fullPath]),
            'og_image' => $category->image ? asset('storage/' . $category->image) : asset('images/logo-og.jpg'),
        ];

        return view('frontend.category', compact(
            'category', 'products', 'subcategories', 'brands', 
            'breadcrumbs', 'seoData', 'filters', 'sort'
        ));
    }

    /**
     * Brand page
     * Route: /marka/{slug}
     */
    public function brandShow(Request $request, string $slug)
    {
        $brand = Brand::active()
            ->where('slug', $slug)
            ->first();

        if (!$brand) {
            // URL rewrite kontrolü
            $rewrite = UrlRewrite::where('old_url', $request->getPathInfo())->first();
            if ($rewrite) {
                return redirect($rewrite->new_url, 301);
            }
            
            abort(404, 'Marka bulunamadı');
        }

        // Filtreleme parametreleri
        $filters = $this->parseFilters($request);
        
        // Ürünleri getir
        $productsQuery = Product::active()
            ->where('brand_id', $brand->id)
            ->with(['category', 'orderedImages', 'variants' => function ($query) {
                $query->active()->orderBy('price');
            }]);

        // Filtreleri uygula
        $this->applyProductFilters($productsQuery, $filters);

        // Sıralama
        $sort = $request->get('sort', 'name');
        $this->applySorting($productsQuery, $sort);

        // Pagination
        $products = $productsQuery->paginate(24)->withQueryString();

        // Kategoriler (bu marka için)
        $categories = Category::active()
            ->whereHas('products', function ($query) use ($brand) {
                $query->active()->where('brand_id', $brand->id);
            })
            ->withCount(['products' => function ($query) use ($brand) {
                $query->active()->where('brand_id', $brand->id);
            }])
            ->orderBy('name')
            ->get();

        // Breadcrumb
        $breadcrumbs = [
            ['name' => 'Anasayfa', 'url' => route('home')],
            ['name' => 'Markalar', 'url' => '#'],
            ['name' => $brand->name, 'url' => null],
        ];

        // SEO Meta bilgileri
        $seoData = [
            'title' => $brand->meta_title ?: ($brand->name . ' Ürünleri - ' . config('app.name')),
            'description' => $brand->meta_description ?: 
                           ($brand->description ? Str::limit(strip_tags($brand->description), 160) : 
                            $brand->name . ' markasının en kaliteli ürünleri'),
            'keywords' => $brand->meta_keywords ?: $brand->name,
            'canonical_url' => route('brand.show', ['slug' => $slug]),
            'og_image' => $brand->logo ? asset('storage/' . $brand->logo) : asset('images/logo-og.jpg'),
        ];

        return view('frontend.brand', compact(
            'brand', 'products', 'categories', 'breadcrumbs', 'seoData', 'filters', 'sort'
        ));
    }

    /**
     * Product detail page
     * Route: /urun/{slug}
     */
    public function productShow(Request $request, string $slug)
    {
        $product = Product::active()
            ->where('slug', $slug)
            ->with([
                'category',
                'brand',
                'orderedImages',
                'variants' => function ($query) {
                    $query->active()->with('unit')->orderBy('price');
                },
                'tags'
            ])
            ->first();

        if (!$product) {
            // URL rewrite kontrolü
            $rewrite = UrlRewrite::where('old_url', $request->getPathInfo())->first();
            if ($rewrite) {
                return redirect($rewrite->new_url, 301);
            }
            
            abort(404, 'Ürün bulunamadı');
        }

        // İlgili ürünler
        $relatedProducts = Product::active()
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('category_id', $product->category_id)
                      ->orWhere('brand_id', $product->brand_id);
            })
            ->with(['brand', 'orderedImages', 'variants'])
            ->limit(8)
            ->get();

        // Benzer ürünler (aynı kategoriden)
        $similarProducts = Product::active()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['brand', 'orderedImages', 'variants'])
            ->limit(4)
            ->get();

        // Breadcrumb
        $breadcrumbs = [];
        if ($product->category) {
            $breadcrumbs = $this->buildCategoryBreadcrumbs($product->category);
        }
        $breadcrumbs[] = ['name' => $product->name, 'url' => null];

        // Ürün özellikleri (varyant attributeleri)
        $attributes = [];
        if ($product->product_type === 'variable' && $product->variants->count() > 0) {
            foreach ($product->variants as $variant) {
                if ($variant->attributes) {
                    foreach ($variant->attributes as $key => $value) {
                        if (!isset($attributes[$key])) {
                            $attributes[$key] = [];
                        }
                        if (!in_array($value, $attributes[$key])) {
                            $attributes[$key][] = $value;
                        }
                    }
                }
            }
        }

        // Fiyat aralığı
        $priceRange = $product->variants->count() > 0 ? [
            'min' => $product->variants->min('price'),
            'max' => $product->variants->max('price'),
        ] : ['min' => 0, 'max' => 0];

        // Stok durumu
        $stockInfo = [
            'total_stock' => $product->variants->sum('stock_quantity'),
            'in_stock' => $product->variants->where('stock_quantity', '>', 0)->count(),
            'out_of_stock' => $product->variants->where('stock_quantity', '<=', 0)->count(),
        ];

        // SEO Meta bilgileri
        $seoData = [
            'title' => $product->meta_title ?: ($product->name . ' - ' . config('app.name')),
            'description' => $product->meta_description ?: 
                           ($product->short_description ?: Str::limit(strip_tags($product->description), 160)),
            'keywords' => $product->meta_keywords ?: 
                         ($product->name . ', ' . ($product->brand ? $product->brand->name . ', ' : '') . 
                          ($product->category ? $product->category->name : '')),
            'canonical_url' => route('product.show', ['slug' => $slug]),
            'og_image' => $product->orderedImages->first() ? 
                         asset('storage/' . $product->orderedImages->first()->path) : 
                         asset('images/logo-og.jpg'),
        ];

        return view('frontend.product', compact(
            'product', 'relatedProducts', 'similarProducts', 'breadcrumbs', 
            'seoData', 'attributes', 'priceRange', 'stockInfo'
        ));
    }

    /**
     * Build category path for URL
     */
    private function buildCategoryPath(Category $category): string
    {
        $path = [$category->slug];
        $parent = $category->parent;
        
        while ($parent) {
            array_unshift($path, $parent->slug);
            $parent = $parent->parent;
        }
        
        return implode('/', $path);
    }

    /**
     * Build breadcrumbs for category
     */
    private function buildCategoryBreadcrumbs(Category $category): array
    {
        $breadcrumbs = [
            ['name' => 'Anasayfa', 'url' => route('home')]
        ];
        
        $categories = [$category];
        $parent = $category->parent;
        
        while ($parent) {
            array_unshift($categories, $parent);
            $parent = $parent->parent;
        }
        
        foreach ($categories as $cat) {
            $path = $this->buildCategoryPath($cat);
            $breadcrumbs[] = [
                'name' => $cat->name,
                'url' => $cat->id === $category->id ? null : route('category.show', ['path' => $path])
            ];
        }
        
        return $breadcrumbs;
    }

    /**
     * Parse filter parameters from request
     */
    private function parseFilters(Request $request): array
    {
        return [
            'brands' => $request->get('brands', []),
            'categories' => $request->get('categories', []),
            'price_min' => $request->get('price_min'),
            'price_max' => $request->get('price_max'),
            'in_stock' => $request->boolean('in_stock'),
            'on_sale' => $request->boolean('on_sale'),
            'featured' => $request->boolean('featured'),
        ];
    }

    /**
     * Apply filters to product query
     */
    private function applyProductFilters($query, array $filters): void
    {
        // Marka filtresi
        if (!empty($filters['brands'])) {
            $query->whereIn('brand_id', $filters['brands']);
        }

        // Kategori filtresi
        if (!empty($filters['categories'])) {
            $query->whereIn('category_id', $filters['categories']);
        }

        // Fiyat filtresi
        if ($filters['price_min'] !== null || $filters['price_max'] !== null) {
            $query->whereHas('variants', function ($q) use ($filters) {
                if ($filters['price_min'] !== null) {
                    $q->where('price', '>=', $filters['price_min']);
                }
                if ($filters['price_max'] !== null) {
                    $q->where('price', '<=', $filters['price_max']);
                }
            });
        }

        // Stokta olan ürünler
        if ($filters['in_stock']) {
            $query->whereHas('variants', function ($q) {
                $q->where('stock_quantity', '>', 0);
            });
        }

        // İndirimli ürünler
        if ($filters['on_sale']) {
            $query->where('is_on_sale', true);
        }

        // Öne çıkan ürünler
        if ($filters['featured']) {
            $query->where('is_featured', true);
        }
    }

    /**
     * Apply sorting to product query
     */
    private function applySorting($query, string $sort): void
    {
        switch ($sort) {
            case 'price_asc':
                $query->join('product_variants as pv', 'products.id', '=', 'pv.product_id')
                      ->select('products.*', DB::raw('MIN(pv.price) as min_price'))
                      ->groupBy('products.id')
                      ->orderBy('min_price');
                break;
            case 'price_desc':
                $query->join('product_variants as pv', 'products.id', '=', 'pv.product_id')
                      ->select('products.*', DB::raw('MAX(pv.price) as max_price'))
                      ->groupBy('products.id')
                      ->orderByDesc('max_price');
                break;
            case 'newest':
                $query->latest('created_at');
                break;
            case 'oldest':
                $query->oldest('created_at');
                break;
            case 'name_asc':
                $query->orderBy('name');
                break;
            case 'name_desc':
                $query->orderByDesc('name');
                break;
            case 'popular':
                $query->orderByDesc('view_count');
                break;
            default:
                $query->orderBy('sort_order')->orderBy('name');
        }
    }
}