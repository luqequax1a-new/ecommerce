<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the brands
     */
    public function index(Request $request)
    {
        $query = Brand::withCount('products');

        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $brands = $query->latest()->paginate(20);

        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new brand
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created brand
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Brand::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $validated['is_active'] = $request->has('is_active');

        Brand::create($validated);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka başarıyla oluşturuldu.');
    }

    /**
     * Display the specified brand
     */
    public function show(Brand $brand)
    {
        $brand->load(['products' => function($query) {
            $query->with(['category', 'variants'])->latest()->limit(10);
        }]);

        return view('admin.brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the brand
     */
    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified brand
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brand->id,
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure unique slug (excluding current brand)
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Brand::where('slug', $validated['slug'])->where('id', '!=', $brand->id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $validated['is_active'] = $request->has('is_active');

        $brand->update($validated);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka başarıyla güncellendi.');
    }

    /**
     * Remove the specified brand
     */
    public function destroy(Brand $brand)
    {
        // Check if brand has products
        if ($brand->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Bu markada ürünler bulunuyor. Önce ürünleri silin veya başka markaya taşıyın.');
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka başarıyla silindi.');
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus(Brand $brand)
    {
        $brand->update(['is_active' => !$brand->is_active]);

        return response()->json([
            'success' => true,
            'status' => $brand->is_active,
            'message' => $brand->is_active ? 'Marka aktifleştirildi.' : 'Marka pasifleştirildi.'
        ]);
    }

    /**
     * Bulk actions for brands
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'brand_ids' => 'required|array',
            'brand_ids.*' => 'exists:brands,id'
        ]);

        $brands = Brand::whereIn('id', $validated['brand_ids']);

        switch ($validated['action']) {
            case 'activate':
                $brands->update(['is_active' => true]);
                $message = count($validated['brand_ids']) . ' marka aktifleştirildi.';
                break;

            case 'deactivate':
                $brands->update(['is_active' => false]);
                $message = count($validated['brand_ids']) . ' marka pasifleştirildi.';
                break;

            case 'delete':
                // Check for products
                $hasProducts = $brands->withCount('products')->get()->sum('products_count');

                if ($hasProducts > 0) {
                    return redirect()->back()
                        ->with('error', 'Ürünleri olan markalar silinemez.');
                }

                $brands->delete();
                $message = count($validated['brand_ids']) . ' marka silindi.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}