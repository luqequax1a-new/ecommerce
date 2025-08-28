<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories
     */
    public function index(Request $request)
    {
        $query = Category::with(['parent', 'children'])
            ->withCount('products');

        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by parent category
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->latest()->paginate(20);
        $parentCategories = Category::whereNull('parent_id')->pluck('name', 'id');

        return view('admin.categories.index', compact('categories', 'parentCategories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
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
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $validated['is_active'] = $request->has('is_active');

        Category::create($validated);

        // Clear cache
        Cache::forget('categories_menu');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori başarıyla oluşturuldu.');
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'products' => function($query) {
            $query->with(['brand', 'variants'])->latest()->limit(10);
        }]);

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the category
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->where('id', '!=', $category->id) // Exclude current category
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
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

        // Ensure unique slug (excluding current category)
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Prevent circular reference (category can't be its own parent)
        if ($validated['parent_id'] == $category->id) {
            return redirect()->back()
                ->withErrors(['parent_id' => 'Kategori kendi üst kategorisi olamaz.'])
                ->withInput();
        }

        $validated['is_active'] = $request->has('is_active');

        $category->update($validated);

        // Clear cache
        Cache::forget('categories_menu');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori başarıyla güncellendi.');
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Bu kategoride ürünler bulunuyor. Önce ürünleri silin veya başka kategoriye taşıyın.');
        }

        // Check if category has subcategories
        if ($category->children()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Bu kategorinin alt kategorileri bulunuyor. Önce alt kategorileri silin.');
        }

        $category->delete();

        // Clear cache
        Cache::forget('categories_menu');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori başarıyla silindi.');
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        // Clear cache
        Cache::forget('categories_menu');

        return response()->json([
            'success' => true,
            'status' => $category->is_active,
            'message' => $category->is_active ? 'Kategori aktifleştirildi.' : 'Kategori pasifleştirildi.'
        ]);
    }

    /**
     * Bulk actions for categories
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        $categories = Category::whereIn('id', $validated['category_ids']);

        switch ($validated['action']) {
            case 'activate':
                $categories->update(['is_active' => true]);
                $message = count($validated['category_ids']) . ' kategori aktifleştirildi.';
                break;

            case 'deactivate':
                $categories->update(['is_active' => false]);
                $message = count($validated['category_ids']) . ' kategori pasifleştirildi.';
                break;

            case 'delete':
                // Check for products and subcategories
                $hasProducts = $categories->withCount('products')->get()->sum('products_count');
                $hasSubcategories = $categories->withCount('children')->get()->sum('children_count');

                if ($hasProducts > 0 || $hasSubcategories > 0) {
                    return redirect()->back()
                        ->with('error', 'Ürünleri veya alt kategorileri olan kategoriler silinemez.');
                }

                $categories->delete();
                $message = count($validated['category_ids']) . ' kategori silindi.';
                break;
        }

        // Clear cache
        Cache::forget('categories_menu');

        return redirect()->back()->with('success', $message);
    }
}