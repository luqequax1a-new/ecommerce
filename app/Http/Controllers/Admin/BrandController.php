<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class BrandController extends Controller
{
    /**
     * Display a listing of brands
     */
    public function index()
    {
        $brands = Brand::withCount(['products', 'activeProducts'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new brand
     */
    public function create()
    {
        $brand = new Brand();
        $seoAnalysis = [];
        $urlRewrites = collect();
        
        return view('admin.brands.create', compact('brand', 'seoAnalysis', 'urlRewrites'));
    }

    /**
     * Store a newly created brand
     */
    public function store(Request $request)
    {
        $validator = $this->validateBrand($request);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        
        return DB::transaction(function () use ($data, $request) {
            // Handle slug generation
            if (empty($data['slug'])) {
                $data['slug'] = SlugService::generateUnique(
                    $data['name'],
                    Brand::class
                );
            }

            // Sanitize and validate data
            $data = $this->sanitizeBrandData($data);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $data['logo_path'] = $this->handleLogoUpload($request, 'logo');
            }

            $brand = Brand::create($data);
            
            // Clear cache
            $this->clearBrandCache();
            
            return redirect()
                ->route('admin.brands.edit', $brand)
                ->with('success', 'Marka başarıyla oluşturuldu.');
        });
    }

    /**
     * Display the specified brand
     */
    public function show(Brand $brand)
    {
        $brand->load(['products' => function($query) {
            $query->latest()->limit(10);
        }]);
        
        return view('admin.brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified brand
     */
    public function edit(Brand $brand)
    {
        $seoAnalysis = $brand->getSEOAnalysis();
        $urlRewrites = $brand->urlRewrites();

        return view('admin.brands.edit', compact('brand', 'seoAnalysis', 'urlRewrites'));
    }

    /**
     * Update the specified brand
     */
    public function update(Request $request, Brand $brand)
    {
        $validator = $this->validateBrand($request, $brand->id);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        
        return DB::transaction(function () use ($data, $request, $brand) {
            $originalSlug = $brand->slug;
            $nameChanged = isset($data['name']) && $data['name'] !== $brand->name;
            $userProvidedSlug = $request->has('slug') && $request->slug !== $brand->slug;
            $autoUpdateSlug = $data['auto_update_slug'] ?? $brand->auto_update_slug ?? true;
            
            // Handle slug auto-update logic
            if ($nameChanged && $autoUpdateSlug && !$userProvidedSlug) {
                $newSlug = SlugService::generateUnique(
                    $data['name'],
                    Brand::class,
                    $brand->id
                );
                $data['slug'] = $newSlug;
                
                if ($originalSlug !== $newSlug) {
                    $brand->setSlug($newSlug);
                }
            } elseif ($userProvidedSlug) {
                if (!SlugService::validate($request->slug)) {
                    throw new \Exception('Geçersiz slug formatı.');
                }
                $brand->setSlug($request->slug);
            }

            // Handle logo upload with cleanup
            if ($request->hasFile('logo')) {
                if ($brand->logo_path) {
                    Storage::disk('public')->delete($brand->logo_path);
                    // Also delete thumbnails if they exist
                    $this->deleteLogoThumbnails($brand->logo_path);
                }
                $data['logo_path'] = $this->handleLogoUpload($request, 'logo');
            }

            // Sanitize and validate data
            $data = $this->sanitizeBrandData($data);

            $brand->update($data);
            
            // Clear cache
            $this->clearBrandCache();

            return redirect()
                ->route('admin.brands.edit', $brand)
                ->with('success', 'Marka başarıyla güncellendi.');
        });
    }

    /**
     * Remove the specified brand
     */
    public function destroy(Brand $brand)
    {
        return DB::transaction(function () use ($brand) {
            // Check if brand has products
            if ($brand->products()->count() > 0) {
                return back()->withErrors(['error' => 'Ürünleri olan marka silinemez.']);
            }

            // Delete logo and thumbnails
            if ($brand->logo_path) {
                Storage::disk('public')->delete($brand->logo_path);
                $this->deleteLogoThumbnails($brand->logo_path);
            }

            $brand->delete();
            
            // Clear cache
            $this->clearBrandCache();

            return redirect()
                ->route('admin.brands.index')
                ->with('success', 'Marka başarıyla silindi.');
        });
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus(Brand $brand)
    {
        $brand->update(['is_active' => !$brand->is_active]);
        
        // Clear cache
        $this->clearBrandCache();
        
        $status = $brand->is_active ? 'aktif' : 'pasif';
        return back()->with('success', "Marka {$status} olarak işaretlendi.");
    }

    /**
     * Bulk actions for brands
     */
    public function bulkAction(Request $request)
    {
        $action = $request->get('action');
        $brandIds = $request->get('brands', []);
        
        if (empty($brandIds)) {
            return back()->withErrors(['error' => 'Marka seçilmedi.']);
        }

        return DB::transaction(function () use ($action, $brandIds) {
            switch ($action) {
                case 'activate':
                    Brand::whereIn('id', $brandIds)->update(['is_active' => true]);
                    $this->clearBrandCache();
                    return back()->with('success', 'Seçilen markalar aktif olarak işaretlendi.');
                    
                case 'deactivate':
                    Brand::whereIn('id', $brandIds)->update(['is_active' => false]);
                    $this->clearBrandCache();
                    return back()->with('success', 'Seçilen markalar pasif olarak işaretlendi.');
                    
                case 'delete':
                    $brands = Brand::whereIn('id', $brandIds)
                        ->withCount('products')
                        ->get();
                    
                    $deletedCount = 0;
                    $skippedCount = 0;
                    $skippedReasons = [];
                    
                    foreach ($brands as $brand) {
                        if ($brand->products_count > 0) {
                            $skippedCount++;
                            $skippedReasons[] = "{$brand->name} (ürünler var)";
                            continue;
                        }
                        
                        // Delete logo and thumbnails
                        if ($brand->logo_path) {
                            Storage::disk('public')->delete($brand->logo_path);
                            $this->deleteLogoThumbnails($brand->logo_path);
                        }
                        
                        $brand->delete();
                        $deletedCount++;
                    }
                    
                    $this->clearBrandCache();
                    
                    $message = "{$deletedCount} marka silindi.";
                    if ($skippedCount > 0) {
                        $reasons = implode(', ', array_slice($skippedReasons, 0, 3));
                        if ($skippedCount > 3) {
                            $reasons .= " ve {$skippedCount} marka daha";
                        }
                        $message .= " {$skippedCount} marka atlandı: {$reasons}";
                    }
                    
                    return back()->with('success', $message);
                    
                default:
                    return back()->withErrors(['error' => 'Geçersiz işlem.']);
            }
        });
    }

    /**
     * Update brands sort order
     */
    public function updateOrder(Request $request)
    {
        $orders = $request->get('orders', []);
        
        if (empty($orders)) {
            return response()->json(['error' => 'Sıralama verisi bulunamadı.'], 400);
        }
        
        return DB::transaction(function () use ($orders) {
            $updatedCount = 0;
            
            foreach ($orders as $order) {
                $brandId = $order['id'] ?? null;
                $position = $order['position'] ?? 0;
                
                if (!$brandId) {
                    continue;
                }
                
                Brand::where('id', $brandId)->update(['sort_order' => $position]);
                $updatedCount++;
            }
            
            // Clear cache after all updates
            $this->clearBrandCache();
            
            return response()->json([
                'success' => true,
                'updated_count' => $updatedCount,
                'message' => "{$updatedCount} marka güncellendi."
            ]);
        });
    }

    /**
     * Generate slug preview
     */
    public function generateSlug(Request $request)
    {
        $name = $request->get('name');
        $excludeId = $request->get('exclude_id');
        
        if (!$name) {
            return response()->json(['error' => 'İsim gerekli.'], 400);
        }
        
        $slug = SlugService::generateUnique(
            $name,
            Brand::class,
            $excludeId
        );
        
        return response()->json(['slug' => $slug]);
    }

    /**
     * Get brands for select dropdown
     */
    public function getForSelect(Request $request)
    {
        $search = $request->get('search');
        
        $query = Brand::active()->orderBy('name');
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        $brands = $query->limit(50)->get(['id', 'name', 'logo_path']);
        
        return response()->json([
            'brands' => $brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'logo_url' => $brand->logo_path ? Storage::url($brand->logo_path) : null
                ];
            })
        ]);
    }

    /**
     * Validate brand data
     */
    protected function validateBrand(Request $request, ?int $excludeId = null): \Illuminate\Validation\Validator
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('brands')->ignore($excludeId)
            ],
            'description' => 'nullable|string|max:10000',
            'short_description' => 'nullable|string|max:500',
            'website_url' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:1000',
            'canonical_url' => 'nullable|url|max:255',
            'robots' => 'nullable|string|max:50|in:index\,follow,noindex\,nofollow,index\,nofollow,noindex\,follow',
            'schema_markup' => 'nullable|json',
            'is_active' => 'boolean',
            'auto_update_slug' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp,svg',
                'max:2048', // 2MB
                'dimensions:min_width=50,min_height=50,max_width=1000,max_height=1000'
            ],
        ];

        $messages = [
            'name.required' => 'Marka adı zorunludur.',
            'slug.regex' => 'Slug sadece küçük harf, rakam ve tire içerebilir.',
            'slug.unique' => 'Bu slug zaten kullanılıyor.',
            'website_url.url' => 'Geçerli bir web sitesi URL\'i girin.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'meta_title.max' => 'Meta başlık 60 karakteri geçemez.',
            'meta_description.max' => 'Meta açıklama 160 karakteri geçemez.',
            'canonical_url.url' => 'Geçerli bir canonical URL girin.',
            'robots.in' => 'Geçersiz robots değeri.',
            'logo.dimensions' => 'Logo 50x50 ile 1000x1000 piksel arasında olmalıdır.',
            'logo.max' => 'Logo dosyası maksimum 2MB olabilir.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Handle logo upload with thumbnail generation
     */
    protected function handleLogoUpload(Request $request, string $field): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $path = $file->store('brands/logos', 'public');
        
        // Generate thumbnails for different sizes
        $this->generateLogoThumbnails($path);
        
        return $path;
    }

    /**
     * Generate logo thumbnails
     */
    protected function generateLogoThumbnails(string $logoPath): void
    {
        try {
            $fullPath = Storage::disk('public')->path($logoPath);
            $pathInfo = pathinfo($logoPath);
            
            $sizes = [
                'thumb' => [100, 100],
                'small' => [200, 200],
                'medium' => [400, 400]
            ];
            
            foreach ($sizes as $sizeName => [$width, $height]) {
                $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $sizeName . '.' . $pathInfo['extension'];
                $fullThumbnailPath = Storage::disk('public')->path($thumbnailPath);
                
                Image::make($fullPath)
                    ->fit($width, $height, function ($constraint) {
                        $constraint->upsize();
                    })
                    ->save($fullThumbnailPath, 90);
            }
        } catch (\Exception $e) {
            Log::warning('Logo thumbnail generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete logo thumbnails
     */
    protected function deleteLogoThumbnails(string $logoPath): void
    {
        $pathInfo = pathinfo($logoPath);
        $sizes = ['thumb', 'small', 'medium'];
        
        foreach ($sizes as $sizeName) {
            $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $sizeName . '.' . $pathInfo['extension'];
            Storage::disk('public')->delete($thumbnailPath);
        }
    }

    /**
     * Sanitize brand data
     */
    protected function sanitizeBrandData(array $data): array
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
        
        // Clean website URL
        if (isset($data['website_url']) && !empty($data['website_url'])) {
            $data['website_url'] = filter_var($data['website_url'], FILTER_SANITIZE_URL);
        }
        
        // Ensure boolean fields are properly cast
        $booleanFields = ['is_active', 'auto_update_slug'];
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
     * Clear brand related cache
     */
    protected function clearBrandCache(): void
    {
        Cache::tags(['brands', 'menu'])->flush();
        
        // Also clear specific cache keys
        Cache::forget('brands.active');
        Cache::forget('brands.for_select');
    }
}