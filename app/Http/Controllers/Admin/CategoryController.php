<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories in tree format
     */
    public function index()
    {
        // Enhanced loading with counts for performance
        $categories = Category::with(['parent'])
            ->withCount(['children', 'products', 'activeProducts'])
            ->orderBy('level')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(50);

        // Root categories with optimized eager loading
        $rootCategories = Cache::tags(['categories', 'menu'])->remember('categories.tree', 300, function () {
            return Category::with([
                'children' => function($query) {
                    $query->with(['children.children'])
                          ->withCount(['children', 'products'])
                          ->ordered();
                }
            ])
            ->withCount(['children', 'products'])
            ->whereNull('parent_id')
            ->ordered()
            ->get();
        });

        return view('admin.categories.index', compact('categories', 'rootCategories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create(Request $request)
    {
        $parentId = $request->get('parent_id');
        $parentCategory = $parentId ? Category::find($parentId) : null;
        
        // Get all categories for parent selection (excluding current to prevent loops)
        $availableParents = Category::orderBy('level')->orderBy('name')->get();
        
        return view('admin.categories.create', compact('parentCategory', 'availableParents'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validator = $this->validateCategory($request);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        
        return DB::transaction(function () use ($data, $request) {
            // Handle slug generation
            if (empty($data['slug'])) {
                $data['slug'] = SlugService::generateUnique(
                    $data['name'],
                    Category::class,
                    null,
                    ['parent_id' => $data['parent_id'] ?? null]
                );
            }

            // Validate parent to prevent circular references
            if (isset($data['parent_id'])) {
                $parent = Category::find($data['parent_id']);
                if (!$parent) {
                    throw new \Exception('Geçersiz üst kategori seçimi.');
                }
            }

            // Sanitize and validate data
            $data = $this->sanitizeCategoryData($data);

            // Handle file uploads
            if ($request->hasFile('cover_image')) {
                $data['image_path'] = $this->handleImageUpload($request, 'cover_image', 'categories/covers');
            }

            if ($request->hasFile('banner_image')) {
                $data['banner_path'] = $this->handleImageUpload($request, 'banner_image', 'categories/banners');
            }

            $category = Category::create($data);
            
            // Clear cache
            $this->clearCategoryCache();
            
            return redirect()
                ->route('admin.categories.edit', $category)
                ->with('success', 'Kategori başarıyla oluşturuldu.');
        });
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'products']);
        
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(Category $category)
    {
        $availableParents = Category::where('id', '!=', $category->id)
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->filter(function ($item) use ($category) {
                return $category->canHaveParent($item->id);
            });

        $seoAnalysis = $category->getSEOAnalysis();
        $urlRewrites = $category->urlRewrites();

        return view('admin.categories.edit', compact('category', 'availableParents', 'seoAnalysis', 'urlRewrites'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $validator = $this->validateCategory($request, $category->id);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        
        return DB::transaction(function () use ($data, $request, $category) {
            $originalSlug = $category->slug;
            $nameChanged = isset($data['name']) && $data['name'] !== $category->name;
            $userProvidedSlug = $request->has('slug') && $request->slug !== $category->slug;
            $autoUpdateSlug = $data['auto_update_slug'] ?? $category->auto_update_slug ?? true;
            
            // Handle slug auto-update logic
            if ($nameChanged && $autoUpdateSlug && !$userProvidedSlug) {
                // Auto-generate new slug from name
                $newSlug = SlugService::generateUnique(
                    $data['name'],
                    Category::class,
                    $category->id,
                    ['parent_id' => $data['parent_id'] ?? $category->parent_id]
                );
                $data['slug'] = $newSlug;
                
                // Create URL rewrite if slug changed
                if ($originalSlug !== $newSlug) {
                    $category->setSlug($newSlug);
                }
            } elseif ($userProvidedSlug) {
                // User manually provided slug
                if (!SlugService::validate($request->slug)) {
                    throw new \Exception('Geçersiz slug formatı.');
                }
                $category->setSlug($request->slug);
            }

            // Handle file uploads with cleanup
            if ($request->hasFile('cover_image')) {
                if ($category->image_path) {
                    Storage::disk('public')->delete($category->image_path);
                }
                $data['image_path'] = $this->handleImageUpload($request, 'cover_image', 'categories/covers');
            }

            if ($request->hasFile('banner_image')) {
                if ($category->banner_path) {
                    Storage::disk('public')->delete($category->banner_path);
                }
                $data['banner_path'] = $this->handleImageUpload($request, 'banner_image', 'categories/banners');
            }

            // Validate parent change to prevent circular references
            if (isset($data['parent_id']) && $data['parent_id'] !== $category->parent_id) {
                if (!$category->canHaveParent($data['parent_id'])) {
                    throw new \Exception('Bu ebeveyn kategori seçimi döngüsel referans oluşturacaktır.');
                }
            }

            // Sanitize and validate data
            $data = $this->sanitizeCategoryData($data);

            $category->update($data);
            
            // Clear cache
            $this->clearCategoryCache();

            return redirect()
                ->route('admin.categories.edit', $category)
                ->with('success', 'Kategori başarıyla güncellendi.');
        });
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        return DB::transaction(function () use ($category) {
            // Check if category has children
            if ($category->children()->count() > 0) {
                return back()->withErrors(['error' => 'Alt kategorileri olan kategori silinemez.']);
            }

            // Check if category has products
            if ($category->products()->count() > 0) {
                return back()->withErrors(['error' => 'Ürünleri olan kategori silinemez.']);
            }

            // Delete images from public disk
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            if ($category->banner_path) {
                Storage::disk('public')->delete($category->banner_path);
            }

            $category->delete();
            
            // Clear cache
            $this->clearCategoryCache();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Kategori başarıyla silindi.');
        });
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        
        // Clear cache
        $this->clearCategoryCache();
        
        $status = $category->is_active ? 'aktif' : 'pasif';
        return back()->with('success', "Kategori {$status} olarak işaretlendi.");
    }

    /**
     * Bulk actions for categories
     */
    public function bulkAction(Request $request)
    {
        $action = $request->get('action');
        $categoryIds = $request->get('categories', []);
        
        if (empty($categoryIds)) {
            return back()->withErrors(['error' => 'Kategori seçilmedi.']);
        }

        return DB::transaction(function () use ($action, $categoryIds) {
            switch ($action) {
                case 'activate':
                    Category::whereIn('id', $categoryIds)->update(['is_active' => true]);
                    $this->clearCategoryCache();
                    return back()->with('success', 'Seçilen kategoriler aktif olarak işaretlendi.');
                    
                case 'deactivate':
                    Category::whereIn('id', $categoryIds)->update(['is_active' => false]);
                    $this->clearCategoryCache();
                    return back()->with('success', 'Seçilen kategoriler pasif olarak işaretlendi.');
                    
                case 'delete':
                    $categories = Category::whereIn('id', $categoryIds)
                        ->with(['children', 'products'])
                        ->get();
                    
                    $deletedCount = 0;
                    $skippedCount = 0;
                    $skippedReasons = [];
                    
                    foreach ($categories as $category) {
                        $childrenCount = $category->children->count();
                        $productsCount = $category->products->count();
                        
                        if ($childrenCount > 0) {
                            $skippedCount++;
                            $skippedReasons[] = "{$category->name} (alt kategoriler var)";
                            continue;
                        }
                        
                        if ($productsCount > 0) {
                            $skippedCount++;
                            $skippedReasons[] = "{$category->name} (ürünler var)";
                            continue;
                        }
                        
                        // Delete images with proper disk
                        if ($category->image_path) {
                            Storage::disk('public')->delete($category->image_path);
                        }
                        if ($category->banner_path) {
                            Storage::disk('public')->delete($category->banner_path);
                        }
                        
                        $category->delete();
                        $deletedCount++;
                    }
                    
                    $this->clearCategoryCache();
                    
                    $message = "{$deletedCount} kategori silindi.";
                    if ($skippedCount > 0) {
                        $reasons = implode(', ', array_slice($skippedReasons, 0, 3));
                        if ($skippedCount > 3) {
                            $reasons .= " ve {$skippedCount} kategori daha";
                        }
                        $message .= " {$skippedCount} kategori atlandı: {$reasons}";
                    }
                    
                    return back()->with('success', $message);
                    
                default:
                    return back()->withErrors(['error' => 'Geçersiz işlem.']);
            }
        });
    }

    /**
     * Update categories sort order via drag & drop
     */
    public function updateOrder(Request $request)
    {
        $orders = $request->get('orders', []);
        
        if (empty($orders)) {
            return response()->json(['error' => 'Sipariş verisi bulunamadı.'], 400);
        }
        
        return DB::transaction(function () use ($orders) {
            $updatedCategories = [];
            
            foreach ($orders as $order) {
                $categoryId = $order['id'] ?? null;
                $position = $order['position'] ?? 0;
                $parentId = $order['parent_id'] ?? null;
                
                if (!$categoryId) {
                    continue;
                }
                
                $category = Category::find($categoryId);
                if (!$category) {
                    continue;
                }
                
                // Validate parent change to prevent circular references
                if ($parentId !== $category->parent_id) {
                    if ($parentId && !$category->canHaveParent($parentId)) {
                        Log::warning("Circular reference prevented for category {$categoryId} with parent {$parentId}");
                        continue;
                    }
                }
                
                // Update category
                $category->update([
                    'sort_order' => $position,
                    'parent_id' => $parentId
                ]);
                
                $updatedCategories[] = $categoryId;
                
                // If parent changed, update children levels efficiently
                if ($parentId !== $category->getOriginal('parent_id')) {
                    $this->updateChildrenLevelsEfficiently($category);
                }
            }
            
            // Clear cache after all updates
            $this->clearCategoryCache();
            
            return response()->json([
                'success' => true,
                'updated_count' => count($updatedCategories),
                'message' => count($updatedCategories) . ' kategori güncellendi.'
            ]);
        });
    }

    /**
     * Generate slug preview
     */
    public function generateSlug(Request $request)
    {
        $name = $request->get('name');
        $parentId = $request->get('parent_id');
        $excludeId = $request->get('exclude_id');
        
        if (!$name) {
            return response()->json(['error' => 'İsim gerekli.'], 400);
        }
        
        $slug = SlugService::generateUnique(
            $name,
            Category::class,
            $excludeId,
            ['parent_id' => $parentId]
        );
        
        return response()->json(['slug' => $slug]);
    }

    /**
     * Get category tree for AJAX
     */
    public function getTree(Request $request)
    {
        $categories = Category::with(['children' => function($query) {
                $query->ordered();
            }])
            ->whereNull('parent_id')
            ->ordered()
            ->get();
            
        return response()->json($categories);
    }

    /**
     * Validate category data
     */
    protected function validateCategory(Request $request, ?int $excludeId = null): \Illuminate\Validation\Validator
    {
        $parentId = $request->get('parent_id');
        
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                // Parent-scoped uniqueness for slug
                Rule::unique('categories')->where(function ($query) use ($parentId) {
                    return $query->where('parent_id', $parentId);
                })->ignore($excludeId)
            ],
            'description' => 'nullable|string|max:10000',
            'short_description' => 'nullable|string|max:500',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                // Prevent self-parent and circular references
                function ($attribute, $value, $fail) use ($excludeId) {
                    if ($value && $excludeId && $value == $excludeId) {
                        $fail('Kategori kendisinin ebeveyni olamaz.');
                    }
                }
            ],
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:1000',
            'canonical_url' => 'nullable|url|max:255',
            'robots' => 'nullable|string|max:50|in:index\,follow,noindex\,nofollow,index\,nofollow,noindex\,follow',
            'schema_markup' => 'nullable|json',
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
            'show_in_footer' => 'boolean',
            'featured' => 'boolean',
            'auto_update_slug' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048', // 2MB
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
            ],
            'banner_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120', // 5MB
                'dimensions:min_width=200,min_height=100,max_width=3000,max_height=1500'
            ],
        ];

        $messages = [
            'name.required' => 'Kategori adı zorunludur.',
            'slug.regex' => 'Slug sadece küçük harf, rakam ve tire içerebilir.',
            'slug.unique' => 'Bu slug aynı ebeveyn kategoride zaten kullanılıyor.',
            'parent_id.exists' => 'Geçersiz ebeveyn kategori.',
            'meta_title.max' => 'Meta başlık 60 karakteri geçemez.',
            'meta_description.max' => 'Meta açıklama 160 karakteri geçemez.',
            'canonical_url.url' => 'Geçerli bir URL girin.',
            'robots.in' => 'Geçersiz robots değeri.',
            'cover_image.dimensions' => 'Kapak görseli 100x100 ile 2000x2000 piksel arasında olmalıdır.',
            'banner_image.dimensions' => 'Banner görseli 200x100 ile 3000x1500 piksel arasında olmalıdır.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Handle image upload
     */
    protected function handleImageUpload(Request $request, string $field, string $path): ?string
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store($path, 'public');
        }
        
        return null;
    }

    /**
     * Sanitize category data
     */
    protected function sanitizeCategoryData(array $data): array
    {
        // Robots whitelist
        $allowedRobots = ['index,follow', 'noindex,nofollow', 'index,nofollow', 'noindex,follow'];
        if (isset($data['robots']) && !in_array($data['robots'], $allowedRobots)) {
            $data['robots'] = 'index,follow';
        }
        
        // Clean canonical URL
        if (isset($data['canonical_url'])) {
            $data['canonical_url'] = $this->sanitizeCanonicalUrl($data['canonical_url']);
        }
        
        // Ensure boolean fields are properly cast
        $booleanFields = ['is_active', 'show_in_menu', 'show_in_footer', 'featured', 'auto_update_slug'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = (bool) $data[$field];
            }
        }
        
        return $data;
    }
    
    /**
     * Sanitize canonical URL
     */
    protected function sanitizeCanonicalUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }
        
        // If it's a relative URL, prepend base URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $url = rtrim(config('app.url'), '/') . '/' . ltrim($url, '/');
        }
        
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
    
    /**
     * Clear category related cache
     */
    protected function clearCategoryCache(): void
    {
        Cache::tags(['categories', 'menu'])->flush();
        
        // Also clear specific cache keys if using different caching strategy
        Cache::forget('categories.tree');
        Cache::forget('categories.menu');
    }
    
    /**
     * Update children levels efficiently to avoid N+1
     */
    protected function updateChildrenLevelsEfficiently(Category $category): void
    {
        // Get all descendants in a single query
        $descendants = Category::where('parent_id', $category->id)
            ->with('children')
            ->get();
            
        foreach ($descendants as $child) {
            $child->update(['level' => $category->level + 1]);
            
            // Recursively update grandchildren
            if ($child->children->count() > 0) {
                $this->updateChildrenLevelsEfficiently($child);
            }
        }
    }
}