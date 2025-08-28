<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    // Katalog (liste)
    public function index()
    {
        // Ürünleri varyantlarıyla birlikte alalım (fiyat min/max göstermek için)
        $products = Product::with(['variants.unit'])->latest()->paginate(12);
        return view('products.index', compact('products'));
    }

    // Ürün detay (slug ile)
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->with(['variants.unit'])
            ->firstOrFail();

        return view('products.show', compact('product'));
    }
}
