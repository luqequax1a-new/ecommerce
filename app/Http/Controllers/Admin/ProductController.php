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
use App\Http\Requests\QuickEditProductRequest;
use App\Http\Requests\CreateProductVariantsRequest;
use App\Http\Requests\UpdateProductVariantsRequest;
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
            // Generate SKU
            $baseSku = $product->slug;
            $attributeSlugs = [];
            
            foreach ($combination as $attributeId => $valueId) {
                $attribute = \App\Models\ProductAttribute::find($attributeId);
                $value = \App\Models\ProductAttributeValue::find($valueId);
                
                if ($attribute && $value) {
                    $attributeSlugs[] = Str::slug($value->value);
                }
            }
            
            $sku = $baseSku . '-' . implode('-', $attributeSlugs);
            
            // Handle SKU collision
            $originalSku = $sku;
            $counter = 1;
            while (ProductVariant::where('sku', $sku)->exists()) {
                $sku = $originalSku . '-' . $counter;
                $counter++;
            }
            
            // Create variant
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $sku,
                'price' => 0, // Will be set by admin
                'stock_quantity' => 0, // Will be set by admin
                'attributes' => $combination,
            ]);
        }
    }
    
    /**
     * Generate all possible combinations from attribute values
     */
    private function generateCombinations(array $attributeValues, array $current = [], array &$result = []): array
    {
        if (empty($attributeValues)) {
            $result[] = $current;
            return $result;
        }
        
        $keys = array_keys($attributeValues);
        $firstKey = $keys[0];
        $firstValues = $attributeValues[$firstKey];
        $remaining = array_slice($attributeValues, 1, null, true);
        
        foreach ($firstValues as $value) {
            $newCurrent = $current;
            $newCurrent[$firstKey] = $value;
            $this->generateCombinations($remaining, $newCurrent, $result);
        }
        
        return $result;
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load(['images', 'variants.attributeValues.attribute', 'category', 'brand', 'unit']);
        $units = Unit::orderBy('display_name')->get();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        
        return view('admin.products.edit', compact('product', 'units', 'categories', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
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
        ];
        
        $request->validate($rules);
        
        // Generate slug if not provided
        $slug = $request->slug ?: Str::slug($request->name);
        if ($slug !== $product->slug) {
            $originalSlug = $slug;
            $counter = 1;
            
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        // Update product
        $product->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'short_description' => $request->short_description,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'unit_id' => $request->unit_id,
            'product_type' => $request->product_type,
            'weight' => $request->weight,
            'is_active' => $request->boolean('is_active'),
            'featured' => $request->boolean('featured'),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ]);
        
        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Ürün başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Delete associated images
        foreach ($product->images as $image) {
            $this->imageService->deleteProductImage($image);
        }
        
        // Delete variants
        $product->variants()->delete();
        
        // Delete product
        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün başarıyla silindi.');
    }

    /**
     * Clone a product
     */
    public function clone(Product $product)
    {
        // Create new product with cloned data
        $newProduct = $product->replicate();
        
        // Generate unique name and slug
        $originalName = $product->name;
        $counter = 1;
        $newName = $originalName . ' (Kopya)';
        
        while (Product::where('name', $newName)->exists()) {
            $newName = $originalName . ' (Kopya ' . $counter . ')';
            $counter++;
        }
        
        $newProduct->name = $newName;
        
        // Generate unique slug
        $originalSlug = $product->slug;
        $counter = 1;
        $newSlug = $originalSlug . '-kopya';
        
        while (Product::where('slug', $newSlug)->exists()) {
            $newSlug = $originalSlug . '-kopya-' . $counter;
            $counter++;
        }
        
        $newProduct->slug = $newSlug;
        
        // Reset other fields
        $newProduct->is_active = false; // Cloned products are inactive by default
        $newProduct->featured = false;
        
        $newProduct->save();
        
        // Clone variants (without images)
        foreach ($product->variants as $variant) {
            $newVariant = $variant->replicate();
            $newVariant->product_id = $newProduct->id;
            
            // Generate unique SKU
            $originalSku = $variant->sku;
            $counter = 1;
            $newSku = $originalSku . '-COPY';
            
            while (ProductVariant::where('sku', $newSku)->exists()) {
                $newSku = $originalSku . '-COPY-' . $counter;
                $counter++;
            }
            
            $newVariant->sku = $newSku;
            $newVariant->save();
        }
        
        // NOTE: Images are NOT cloned per policy
        // This is intentional - cloned products must have ZERO images
        
        return redirect()->route('admin.products.edit', $newProduct)
            ->with('success', 'Ürün başarıyla kopyalandı. Lütfen yeni ürün bilgilerini gözden geçirin.');
    }

    /**
     * Get clone information for UI
     */
    public function getCloneInfo(Product $product)
    {
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'variant_count' => $product->variants->count(),
                'image_count' => $product->images->count()
            ]
        ]);
    }

    /**
     * Delete a product image
     */
    public function deleteImage(ProductImage $image)
    {
        $success = $this->imageService->deleteProductImage($image);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Görsel başarıyla silindi.' : 'Silme işlemi başarısız.'
        ]);
    }

    /**
     * Set image as cover
     */
    public function setCoverImage(ProductImage $image)
    {
        $success = $this->imageService->setCoverImage($image);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Ana görsel olarak ayarlandı.' : 'İşlem başarısız.'
        ]);
    }

    /**
     * Update image order
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
     * Regenerate images for a specific product
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
     * Regenerate images for all products (bulk operation)
     */
    public function regenerateAllImages()
    {
        $results = $this->imageService->regenerateAllImages();
        
        $totalProducts = count($results);
        $successfulProducts = count(array_filter($results, function($productResults) {
            return !empty(array_filter($productResults, fn($r) => $r['success']));
        }));
        
        return response()->json([
            'success' => true,
            'message' => "Tüm ürün görselleri yeniden oluşturuldu. {$successfulProducts}/{$totalProducts} ürün başarılı.",
            'results' => $results
        ]);
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

    // ==================== QUICK EDIT FUNCTIONALITY ====================

    /**
     * Quick update product fields (PrestaShop-style inline editing)
     */
    public function quickUpdate(QuickEditProductRequest $request, Product $product)
    {
        $formattedData = $request->getFormattedData();
        $field = $formattedData['field'];
        $value = $formattedData['value'];

        try {
            // Special handling for certain fields
            if ($field === 'slug') {
                $value = $this->ensureUniqueSlug($value, $product->id);
            }

            // Update the product
            $product->update([$field => $value]);

            // Get formatted display value
            $displayValue = $this->getDisplayValue($product, $field);

            return response()->json([
                'success' => true,
                'message' => 'Ürün başarıyla güncellendi.',
                'value' => $value,
                'display_value' => $displayValue,
                'field' => $field
            ]);

        } catch (\Exception $e) {
            Log::error('Quick update error', [
                'product_id' => $product->id,
                'field' => $field,
                'value' => $value,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Güncelleme sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update multiple products
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
            'action' => 'required|string|in:activate,deactivate,delete,move_category,assign_brand,adjust_price,copy_seo_title,regenerate_slug',
            'value' => 'nullable|string'
        ]);

        $productIds = $request->product_ids;
        $action = $request->action;
        $value = $request->value;

        try {
            $updatedCount = 0;
            $deletedCount = 0;

            switch ($action) {
                case 'activate':
                    Product::whereIn('id', $productIds)->update(['is_active' => true]);
                    $updatedCount = count($productIds);
                    break;

                case 'deactivate':
                    Product::whereIn('id', $productIds)->update(['is_active' => false]);
                    $updatedCount = count($productIds);
                    break;

                case 'delete':
                    $products = Product::whereIn('id', $productIds)->get();
                    foreach ($products as $product) {
                        // Delete associated images
                        foreach ($product->images as $image) {
                            $this->imageService->deleteProductImage($image);
                        }
                        
                        // Delete variants
                        $product->variants()->delete();
                        
                        // Delete product
                        $product->delete();
                        $deletedCount++;
                    }
                    break;

                case 'move_category':
                    if ($value) {
                        Product::whereIn('id', $productIds)->update(['category_id' => $value]);
                        $updatedCount = count($productIds);
                    }
                    break;

                case 'assign_brand':
                    Product::whereIn('id', $productIds)->update(['brand_id' => $value ?: null]);
                    $updatedCount = count($productIds);
                    break;

                case 'adjust_price':
                    if ($value && preg_match('/^([+-])(\d+)%?$/', $value, $matches)) {
                        $operator = $matches[1];
                        $amount = (float)$matches[2];
                        
                        foreach (ProductVariant::whereIn('product_id', $productIds)->get() as $variant) {
                            $currentPrice = $variant->price;
                            $newPrice = $operator === '+' 
                                ? $currentPrice * (1 + $amount / 100)
                                : $currentPrice * (1 - $amount / 100);
                            
                            $variant->update(['price' => round($newPrice, 2)]);
                            $updatedCount++;
                        }
                    }
                    break;

                case 'copy_seo_title':
                    $products = Product::whereIn('id', $productIds)->get();
                    foreach ($products as $product) {
                        $product->update(['meta_title' => $product->name]);
                        $updatedCount++;
                    }
                    break;

                case 'regenerate_slug':
                    $products = Product::whereIn('id', $productIds)->get();
                    foreach ($products as $product) {
                        $slug = SlugService::generateUnique($product->name, Product::class, $product->id);
                        $product->update(['slug' => $slug]);
                        $updatedCount++;
                    }
                    break;
            }

            $message = '';
            if ($updatedCount > 0) {
                $message = "{$updatedCount} ürün başarıyla güncellendi.";
            }
            if ($deletedCount > 0) {
                $message = "{$deletedCount} ürün başarıyla silindi.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk update error', [
                'action' => $action,
                'product_ids' => $productIds,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Toplu güncelleme sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get options for quick edit dropdowns
     */
    public function getQuickEditOptions()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Ensure slug is unique
     */
    private function ensureUniqueSlug(string $slug, int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;
        
        $query = Product::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            $query = Product::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }
        
        return $slug;
    }

    /**
     * Get formatted display value for a field
     */
    private function getDisplayValue(Product $product, string $field)
    {
        switch ($field) {
            case 'is_active':
                return $product->$field ? 'Aktif' : 'Pasif';
            case 'featured':
                return $product->$field ? 'Öne Çıkan' : 'Normal';
            case 'category_id':
                return $product->category ? $product->category->name : 'Kategori Yok';
            case 'brand_id':
                return $product->brand ? $product->brand->name : 'Marka Yok';
            default:
                return $product->$field;
        }
    }
}