<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Services\ImageService;
use App\Services\SlugService;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected ImageService $imageService;
    protected ProductVariantService $variantService;

    public function __construct(ImageService $imageService, ProductVariantService $variantService)
    {
        $this->imageService = $imageService;
        $this->variantService = $variantService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['variants', 'images', 'brand', 'category']);
        
        // Arama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        
        // Aktiflik durumu
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $products = $query->latest()->paginate(15);
        
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $units = Unit::orderBy('display_name')->get();
        return view('admin.products.create', compact('units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Base validation
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'product_type' => 'required|in:simple,variable',
            'is_active' => 'boolean',
            'featured' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            
            // SEO fields
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            
            // Image validation
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,gif,webp|max:10240',
            'image_alt_texts' => 'nullable|array',
            'image_alt_texts.*' => 'nullable|string|max:255',
        ];
        
        // Product type specific validation
        if ($request->product_type === 'simple') {
            $rules = array_merge($rules, [
                'simple_sku' => 'required|string|max:100|unique:product_variants,sku',
                'simple_price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|numeric|min:0',
            ]);
        } else {
            $rules = array_merge($rules, [
                'selected_attributes' => 'required|array|min:1',
                'selected_attributes.*' => 'exists:product_attributes,id',
                'attribute_values' => 'required|array',
            ]);
        }
        
        $request->validate($rules);
        
        // Generate slug
        $slug = $request->slug ?: Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        // Create product
        $product = Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'short_description' => $request->short_description,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'unit_id' => $request->unit_id,
            'product_type' => $request->product_type,
            'stock_quantity' => $request->product_type === 'simple' ? $request->stock_quantity : 0,
            'weight' => $request->weight,
            'is_active' => $request->boolean('is_active', true),
            'featured' => $request->boolean('featured', false),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ]);
        
        // Handle variants based on product type
        if ($request->product_type === 'simple') {
            // Create single variant for simple product
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $request->simple_sku,
                'price' => $request->simple_price,
                'stock_quantity' => $request->stock_quantity,
                'attributes' => null, // Simple products don't have attributes
            ]);
        } else {
            // Create combinations for variable product
            $this->createVariantCombinations($product, $request);
        }
        
        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $altText = $request->image_alt_texts[$index] ?? null;
                $isCover = $index === 0; // First image as cover
                
                $this->imageService->uploadProductImage(
                    $product, 
                    $file, 
                    $altText, 
                    $isCover, 
                    $index
                );
            }
        }
        
        $message = $request->product_type === 'simple' 
            ? 'Basit ürün başarıyla oluşturuldu.' 
            : 'Varyantlı ürün başarıyla oluşturuldu. Varyant fiyat ve stok bilgilerini düzenleyebilirsiniz.';
            
        return redirect()->route('admin.products.index')
            ->with('success', $message);
    }
    
    /**
     * Create variant combinations for variable products
     */
    private function createVariantCombinations(Product $product, Request $request)
    {
        $selectedAttributes = $request->selected_attributes;
        $attributeValues = $request->attribute_values;
        
        // Get all possible combinations
        $combinations = $this->generateCombinations($attributeValues);
        
        foreach ($combinations as $index => $combination) {
            // Create SKU from combination
            $sku = $product->slug . '-' . ($index + 1);
            
            // Make sure SKU is unique
            $originalSku = $sku;
            $counter = 1;
            while (ProductVariant::where('sku', $sku)->exists()) {
                $sku = $originalSku . '-' . $counter;
                $counter++;
            }
            
            // Create variant with default values
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $sku,
                'price' => 0, // Admin will set this later
                'stock_quantity' => 0, // Admin will set this later
                'attributes' => $combination,
            ]);
        }
    }
    
    /**
     * Generate all possible attribute combinations
     */
    private function generateCombinations(array $attributeValues)
    {
        $combinations = [];
        $keys = array_keys($attributeValues);
        
        if (empty($keys)) {
            return [];
        }
        
        // Start with first attribute
        $firstKey = $keys[0];
        foreach ($attributeValues[$firstKey] as $value) {
            $combinations[] = [$firstKey => $value];
        }
        
        // Add other attributes
        for ($i = 1; $i < count($keys); $i++) {
            $newCombinations = [];
            $currentKey = $keys[$i];
            
            foreach ($combinations as $combination) {
                foreach ($attributeValues[$currentKey] as $value) {
                    $newCombination = $combination;
                    $newCombination[$currentKey] = $value;
                    $newCombinations[] = $newCombination;
                }
            }
            
            $combinations = $newCombinations;
        }
        
        return $combinations;
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['variants.unit', 'orderedImages']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load(['variants.unit', 'orderedImages', 'category', 'brand']);
        $units = Unit::orderBy('display_name')->get();
        $categories = Category::active()->ordered()->get();
        $brands = Brand::active()->ordered()->get();
        
        // SEO Analysis
        $seoAnalysis = [
            'title_optimal' => $product->meta_title && strlen($product->meta_title) >= 30 && strlen($product->meta_title) <= 60,
            'description_optimal' => $product->meta_description && strlen($product->meta_description) >= 120 && strlen($product->meta_description) <= 160,
            'slug_optimal' => $product->slug && strlen($product->slug) <= 50,
        ];
        
        return view('admin.products.edit', compact('product', 'units', 'categories', 'brands', 'seoAnalysis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            
            // Yeni görseller
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,gif,webp|max:10240',
            'image_alt_texts' => 'nullable|array',
            'image_alt_texts.*' => 'nullable|string|max:255',
        ]);
        
        // Slug güncelle
        $slug = $request->slug ?: Str::slug($request->name);
        if ($slug !== $product->slug) {
            $originalSlug = $slug;
            $counter = 1;
            
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        // Ürünü güncelle
        $product->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true)
        ]);
        
        // Yeni görselleri yükle
        if ($request->hasFile('images')) {
            $currentImageCount = $product->images()->count();
            
            foreach ($request->file('images') as $index => $file) {
                $altText = $request->image_alt_texts[$index] ?? null;
                $sortOrder = $currentImageCount + $index;
                
                $this->imageService->uploadProductImage(
                    $product, 
                    $file, 
                    $altText, 
                    false, // Yeni eklenen görseller cover olmaz
                    $sortOrder
                );
            }
        }
        
        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Ürün başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Önce tüm görselleri sil
        foreach ($product->images as $image) {
            $this->imageService->deleteProductImage($image);
        }
        
        // Ürünü sil (varyantlar cascade ile silinecek)
        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün başarıyla silindi.');
    }
    
    /**
     * Tek bir görseli sil
     */
    public function deleteImage(ProductImage $image)
    {
        $this->imageService->deleteProductImage($image);
        
        return response()->json(['success' => true, 'message' => 'Görsel başarıyla silindi.']);
    }
    
    /**
     * Cover image'ı değiştir
     */
    public function setCoverImage(ProductImage $image)
    {
        $success = $this->imageService->setCoverImage($image);
        
        return response()->json([
            'success' => $success, 
            'message' => $success ? 'Ana görsel başarıyla değiştirildi.' : 'Hata oluştu.'
        ]);
    }
    
    /**
     * Görsel sırasını güncelle
     */
    public function updateImageOrder(Request $request)
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'integer|exists:product_images,id'
        ]);
        
        $success = $this->imageService->updateImageOrder($request->image_ids);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Görsel sırası güncellendi.' : 'Hata oluştu.'
        ]);
    }
    
    /**
     * Görselleri yeniden oluştur (Prestashop tarzı)
     */
    public function regenerateImages(Product $product)
    {
        $results = $this->imageService->regenerateProductImages($product);
        
        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);
        
        return redirect()->back()->with('success', 
            "Görsel yeniden oluşturma tamamlandı. {$successCount}/{$totalCount} başarılı.");
    }

    /**
     * Generate slug for product
     */
    public function generateSlug(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'exclude_id' => 'nullable|integer'
        ]);

        $slug = SlugService::generateUnique(
            $request->name,
            Product::class,
            $request->exclude_id
        );

        return response()->json(['slug' => $slug]);
    }

    /**
     * Generate variant combinations for a product
     */
    public function generateVariantCombinations(Request $request, Product $product)
    {
        $request->validate([
            'attributes' => 'required|array|min:1',
            'attributes.*' => 'integer|exists:product_attributes,id'
        ]);

        $combinations = $this->variantService->generateVariantCombinations(
            $product,
            $request->attributes
        );

        return response()->json([
            'success' => true,
            'combinations' => $combinations,
            'count' => count($combinations)
        ]);
    }

    /**
     * Create variants from combinations
     */
    public function createVariants(Request $request, Product $product)
    {
        $request->validate([
            'combinations' => 'required|array|min:1',
            'combinations.*.sku' => 'required|string|max:100',
            'combinations.*.price' => 'required|numeric|min:0',
            'combinations.*.stock_quantity' => 'required|numeric|min:0',
            'combinations.*.attributes' => 'required|array'
        ]);

        $result = $this->variantService->createVariantsFromCombinations(
            $product,
            $request->combinations
        );

        if ($result['error_count'] > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bazı varyantlar oluşturulamadı.',
                'errors' => $result['errors'],
                'created_count' => $result['success_count']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "{$result['success_count']} varyant başarıyla oluşturuldu.",
            'created_count' => $result['success_count']
        ]);
    }

    /**
     * Update multiple variants
     */
    public function updateVariants(Request $request, Product $product)
    {
        $request->validate([
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'required|integer|exists:product_variants,id',
            'variants.*.sku' => 'required|string|max:100',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock_quantity' => 'required|numeric|min:0'
        ]);

        // Prepare update data
        $variantUpdates = [];
        foreach ($request->variants as $variantData) {
            $variantUpdates[$variantData['id']] = [
                'sku' => $variantData['sku'],
                'price' => $variantData['price'],
                'stock_quantity' => $variantData['stock_quantity']
            ];
        }

        $result = $this->variantService->updateVariants($product, $variantUpdates);

        if ($result['error_count'] > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bazı varyantlar güncellenemedi.',
                'errors' => $result['errors'],
                'updated_count' => $result['success_count']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "{$result['success_count']} varyant başarıyla güncellendi.",
            'updated_count' => $result['success_count']
        ]);
    }

    /**
     * Delete variants
     */
    public function deleteVariants(Request $request, Product $product)
    {
        $request->validate([
            'variant_ids' => 'required|array|min:1',
            'variant_ids.*' => 'integer|exists:product_variants,id'
        ]);

        $result = $this->variantService->deleteVariants($product, $request->variant_ids);

        if ($result['error_count'] > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bazı varyantlar silinemedi.',
                'errors' => $result['errors'],
                'deleted_count' => $result['success_count']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "{$result['success_count']} varyant başarıyla silindi.",
            'deleted_count' => $result['success_count']
        ]);
    }

    /**
     * Get available attributes for variant creation
     */
    public function getAvailableAttributes()
    {
        $attributes = $this->variantService->getAvailableAttributes();

        return response()->json(
            $attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'type' => $attribute->type,
                    'values' => $attribute->activeValues->map(function ($value) {
                        return [
                            'id' => $value->id,
                            'value' => $value->value,
                            'color_code' => $value->color_code,
                            'image_url' => $value->image_url
                        ];
                    })
                ];
            })
        );
    }

    /**
     * Get variant statistics for a product
     */
    public function getVariantStatistics(Product $product)
    {
        $statistics = $this->variantService->getVariantStatistics($product);

        return response()->json($statistics);
    }

    /**
     * Bulk upload images for product
     */
    public function bulkUploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:20',
            'images.*' => 'image|mimes:jpeg,png,gif,webp|max:10240',
            'metadata' => 'nullable|array',
            'metadata.*.alt_text' => 'nullable|string|max:255',
            'metadata.*.description' => 'nullable|string|max:1000',
            'metadata.*.variant_ids' => 'nullable|array',
            'metadata.*.variant_ids.*' => 'integer|exists:product_variants,id',
            'metadata.*.image_type' => 'nullable|in:product,variant,gallery'
        ]);

        $result = $this->imageService->bulkUploadImages(
            $product,
            $request->file('images'),
            $request->input('metadata', [])
        );

        if ($result['error_count'] > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bazı görseller yüklenemedi.',
                'errors' => $result['errors'],
                'uploaded_count' => $result['success_count']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "{$result['success_count']} görsel başarıyla yüklendi.",
            'uploaded_count' => $result['success_count'],
            'images' => $result['success']
        ]);
    }

    /**
     * Get product gallery images
     */
    public function getGalleryImages(Request $request, Product $product)
    {
        $variantId = $request->input('variant_id');
        $images = $this->imageService->getVariantImages($product, $variantId);

        return response()->json([
            'success' => true,
            'images' => $images,
            'statistics' => $this->imageService->getGalleryStatistics($product)
        ]);
    }

    /**
     * Update image metadata
     */
    public function updateImageMetadata(Request $request, Product $product, ProductImage $image)
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_cover' => 'boolean',
            'variant_ids' => 'nullable|array',
            'variant_ids.*' => 'integer|exists:product_variants,id',
            'is_variant_specific' => 'boolean',
            'image_type' => 'nullable|in:product,variant,gallery'
        ]);

        $success = $this->imageService->updateImageMetadata($image, $request->all());

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Görsel bilgileri güncellendi.' : 'Güncelleme başarısız.'
        ]);
    }

    /**
     * Associate images with variants
     */
    public function associateImagesWithVariants(Request $request, Product $product)
    {
        $request->validate([
            'image_ids' => 'required|array|min:1',
            'image_ids.*' => 'integer|exists:product_images,id',
            'variant_ids' => 'required|array|min:1',
            'variant_ids.*' => 'integer|exists:product_variants,id',
            'is_variant_specific' => 'boolean'
        ]);

        $success = $this->imageService->associateImagesWithVariants(
            $request->image_ids,
            $request->variant_ids,
            $request->boolean('is_variant_specific', true)
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Görsel-varyant ilişkilendirmesi tamamlandı.' : 'İlişkilendirme başarısız.'
        ]);
    }

    /**
     * Bulk delete images
     */
    public function bulkDeleteImages(Request $request, Product $product)
    {
        $request->validate([
            'image_ids' => 'required|array|min:1',
            'image_ids.*' => 'integer|exists:product_images,id'
        ]);

        $result = $this->imageService->bulkDeleteImages($request->image_ids);

        if ($result['error_count'] > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bazı görseller silinemedi.',
                'errors' => $result['errors'],
                'deleted_count' => $result['success_count']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "{$result['success_count']} görsel başarıyla silindi.",
            'deleted_count' => $result['success_count']
        ]);
    }

    /**
     * Optimize product images
     */
    public function optimizeImages(Product $product)
    {
        $results = $this->imageService->optimizeProductImages($product);
        
        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);
        
        return response()->json([
            'success' => true,
            'message' => "Görsel optimizasyonu tamamlandı. {$successCount}/{$totalCount} başarılı.",
            'results' => $results
        ]);
    }
}
