<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Unit;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
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
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            
            // Varyant bilgileri
            'sku' => 'required|string|max:100|unique:product_variants',
            'price' => 'required|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'unit_id' => 'required|exists:units,id',
            'attributes' => 'nullable|string',
            
            // Görsel bilgileri
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,gif,webp|max:10240', // 10MB
            'image_alt_texts' => 'nullable|array',
            'image_alt_texts.*' => 'nullable|string|max:255',
        ]);
        
        // Slug oluştur
        $slug = $request->slug ?: Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        // Ürün oluştur
        $product = Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true)
        ]);
        
        // Ana varyant oluştur
        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $request->sku,
            'price' => $request->price,
            'stock_qty' => $request->stock_qty,
            'unit_id' => $request->unit_id,
            'attributes' => $request->attributes
        ]);
        
        // Görselleri yükle
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $altText = $request->image_alt_texts[$index] ?? null;
                $isCover = $index === 0; // İlk görsel cover
                
                $this->imageService->uploadProductImage(
                    $product, 
                    $file, 
                    $altText, 
                    $isCover, 
                    $index
                );
            }
        }
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün başarıyla oluşturuldu.');
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
        $product->load(['variants.unit', 'orderedImages']);
        $units = Unit::orderBy('display_name')->get();
        
        return view('admin.products.edit', compact('product', 'units'));
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
}
