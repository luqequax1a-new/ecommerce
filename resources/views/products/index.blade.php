@extends('layouts.app')

@section('title', 'Ürünler - Ecommerce')

@section('content')
<div class="bg-white">
    <!-- Page Header -->
    <div class="border-b border-gray-200 pb-6 mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Ürünler</h1>
        <p class="mt-2 text-gray-600">Kategorilerimizde yer alan tüm ürünleri keşfedin</p>
    </div>

    <!-- Products Grid -->
    @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="group bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                    <!-- Product Image -->
                    <div class="aspect-square bg-gray-100 rounded-t-lg overflow-hidden">
                        <a href="{{ route('product.show', $product->slug) }}">
                            @if($product->images->first())
                                <img src="{{ $product->images->first()->medium_url }}" 
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                    </svg>
                                </div>
                            @endif
                        </a>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4 space-y-3">
                        <!-- Brand -->
                        @if($product->brand)
                            <div class="flex items-center space-x-2">
                                @if($product->brand->logo_url)
                                    <img src="{{ $product->brand->logo_url }}" alt="{{ $product->brand->name }}" class="h-4 w-auto">
                                @endif
                                <span class="text-xs text-gray-500 uppercase font-medium">{{ $product->brand->name }}</span>
                            </div>
                        @endif

                        <!-- Product Name -->
                        <h3 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors">
                            <a href="{{ route('product.show', $product->slug) }}">
                                {{ $product->name }}
                            </a>
                        </h3>

                        <!-- Category -->
                        @if($product->category)
                            <div class="text-xs text-gray-500">
                                <a href="{{ route('category.show', $product->category->slug) }}" class="hover:text-blue-600">
                                    {{ $product->category->name }}
                                </a>
                            </div>
                        @endif

                        <!-- Price -->
                        @if($product->variants->count() > 0)
                            @php
                                $minPrice = $product->variants->min('price');
                                $maxPrice = $product->variants->max('price');
                                $totalStock = $product->variants->sum('stock_quantity');
                            @endphp
                            <div class="space-y-2">
                                @if($minPrice == $maxPrice)
                                    <p class="text-lg font-bold text-green-600">{{ number_format($minPrice, 2) }} ₺</p>
                                @else
                                    <p class="text-lg font-bold text-green-600">{{ number_format($minPrice, 2) }} - {{ number_format($maxPrice, 2) }} ₺</p>
                                @endif
                                
                                <!-- Stock Status -->
                                @if($totalStock > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        Stokta
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        Stokta Yok
                                    </span>
                                @endif
                            </div>
                        @endif

                        <!-- Quick Action Button -->
                        <div class="pt-2">
                            <a href="{{ route('product.show', $product->slug) }}" 
                               class="w-full bg-blue-600 text-white py-2 px-4 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>Detayları Gör</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="mt-12 flex justify-center">
                {{ $products->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Henüz ürün bulunmuyor</h3>
            <p class="mt-2 text-gray-500">Yakında yeni ürünler eklenecek.</p>
        </div>
    @endif
</div>

<!-- Quick Filters (Future Enhancement) -->
@push('scripts')
<script>
// Future: Add AJAX filtering functionality here
console.log('Product listing page loaded with {{ $products->count() }} products');
</script>
@endpush
@endsection