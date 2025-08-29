<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    // Katalog (liste)
    public function index()
    {
        // Ürünleri varyantlarıyla birlikte alalım (fiyat min/max göstermek için)
        $products = Product::with(['variants.unit', 'images', 'brand', 'category'])
            ->where('is_active', true)
            ->latest()
            ->paginate(12);
        return view('products.index', compact('products'));
    }

    // Ürün detay (slug ile)
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'variants.unit', 
                'images' => function($query) {
                    $query->orderBy('sort_order');
                },
                'brand', 
                'category.parent',
                'category.products' => function($query) use ($slug) {
                    $query->where('slug', '!=', $slug)
                          ->where('is_active', true)
                          ->with('images', 'variants')
                          ->limit(4);
                }
            ])
            ->firstOrFail();

        return view('products.show', compact('product'));
    }

    // Kategori sayfası
    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)
            ->with('parent')
            ->firstOrFail();
            
        $products = Product::where('category_id', $category->id)
            ->where('is_active', true)
            ->with(['variants.unit', 'images', 'brand'])
            ->latest()
            ->paginate(12);
            
        return view('products.category', compact('category', 'products'));
    }
}
