@extends('layouts.app')

@section('title', $product->meta_title ?: $product->name . ' - Ecommerce')

@push('meta')
    <meta name="description" content="{{ $product->meta_description ?: Str::limit(strip_tags($product->description), 160) }}">
    <meta name="keywords" content="{{ $product->meta_keywords }}">
    <meta property="og:title" content="{{ $product->meta_title ?: $product->name }}">
    <meta property="og:description" content="{{ $product->meta_description ?: Str::limit(strip_tags($product->description), 160) }}">
    <meta property="og:image" content="{{ $product->images->first()?->thumbnail_url ?? asset('images/no-image.jpg') }}">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:type" content="product">
@endpush

@section('content')
<div class="bg-white">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-3 h-3 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                    </svg>
                    Ana Sayfa
                </a>
            </li>
            @if($product->category)
                @foreach($product->category->breadcrumb as $breadcrumb)
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"></path>
                            </svg>
                            <a href="{{ route('category.show', $breadcrumb['slug']) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600">
                                {{ $breadcrumb['name'] }}
                            </a>
                        </div>
                    </li>
                @endforeach
            @endif
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"></path>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500">{{ $product->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Product Images -->
        <div class="space-y-4">
            <!-- Main Image -->
            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                @if($product->images->count() > 0)
                    <img id="main-image" 
                         src="{{ $product->images->first()->large_url }}" 
                         alt="{{ $product->images->first()->alt_text ?: $product->name }}"
                         class="w-full h-full object-cover cursor-zoom-in"
                         onclick="openImageModal(this.src)">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Desktop Thumbnail Images -->
            @if($product->images->count() > 1)
                <div class="hidden md:grid grid-cols-4 gap-2">
                    @foreach($product->images as $image)
                        <div class="aspect-square bg-gray-100 rounded-md overflow-hidden cursor-pointer border-2 {{ $loop->first ? 'border-blue-500' : 'border-transparent' }} hover:border-blue-300 transition-colors"
                             onclick="changeMainImage('{{ $image->large_url }}', '{{ $image->alt_text ?: $product->name }}', this)">
                            <img src="{{ $image->small_url }}" 
                                 alt="{{ $image->alt_text ?: $product->name }}"
                                 class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Mobile Swiper Gallery -->
            @if($product->images->count() > 1)
                <div class="md:hidden">
                    <div class="swiper mobile-gallery">
                        <div class="swiper-wrapper">
                            @foreach($product->images as $image)
                                <div class="swiper-slide">
                                    <div class="aspect-square bg-gray-100 rounded-md overflow-hidden cursor-pointer border-2 {{ $loop->first ? 'border-blue-500' : 'border-transparent' }}"
                                         onclick="changeMainImageMobile('{{ $image->large_url }}', '{{ $image->alt_text ?: $product->name }}', this)">
                                        <img src="{{ $image->small_url }}" 
                                             alt="{{ $image->alt_text ?: $product->name }}"
                                             class="w-full h-full object-cover">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Product Info -->
        <div class="space-y-6">
            <!-- Brand -->
            @if($product->brand)
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    @if($product->brand->logo_url)
                        <img src="{{ $product->brand->logo_url }}" alt="{{ $product->brand->name }}" class="h-6 w-auto">
                    @endif
                    <span>{{ $product->brand->name }}</span>
                </div>
            @endif

            <!-- Product Name -->
            <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>

            <!-- Price -->
            @if($product->variants->count() > 0)
                <div class="space-y-2">
                    @php
                        $minPrice = $product->variants->min('price');
                        $maxPrice = $product->variants->max('price');
                    @endphp
                    @if($minPrice == $maxPrice)
                        <p class="text-3xl font-bold text-green-600">{{ number_format($minPrice, 2) }} ₺</p>
                    @else
                        <p class="text-3xl font-bold text-green-600">{{ number_format($minPrice, 2) }} - {{ number_format($maxPrice, 2) }} ₺</p>
                    @endif
                </div>
            @endif

            <!-- Stock Status -->
            @php
                $totalStock = $product->variants->sum('stock_quantity');
            @endphp
            <div class="flex items-center space-x-2">
                @if($totalStock > 0)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3"/>
                        </svg>
                        Stokta ({{ $totalStock }} adet)
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3"/>
                        </svg>
                        Stokta Yok
                    </span>
                @endif
            </div>

            <!-- Variants -->
            @if($product->variants->count() > 0)
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Seçenekler</h3>
                    <div class="space-y-3">
                        @foreach($product->variants as $variant)
                            <div class="border rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer variant-option {{ $loop->first ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                                 onclick="selectVariant(this, {{ $variant->id }}, {{ $variant->price }}, {{ $variant->stock_quantity }})">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $variant->name }}</p>
                                        @if($variant->description)
                                            <p class="text-sm text-gray-600">{{ $variant->description }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-green-600">{{ number_format($variant->price, 2) }} ₺</p>
                                        <p class="text-xs text-gray-500">Stok: {{ $variant->stock_quantity }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Add to Cart Button -->
            <div class="space-y-3">
                @if($totalStock > 0)
                    <button type="button" 
                            id="add-to-cart-btn"
                            onclick="addToCart()"
                            class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6.05"></path>
                        </svg>
                        <span>Sepete Ekle</span>
                    </button>
                @else
                    <button type="button" disabled class="w-full bg-gray-400 text-white py-3 px-6 rounded-lg font-medium cursor-not-allowed">
                        Stokta Yok
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Product Description -->
    @if($product->description)
        <div class="mt-12 border-t pt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Ürün Açıklaması</h2>
            <div class="prose max-w-none text-gray-700">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>
    @endif

    <!-- Related Products -->
    @if($product->category && $product->category->products->where('id', '!=', $product->id)->count() > 0)
        <div class="mt-12 border-t pt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Benzer Ürünler</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($product->category->products->where('id', '!=', $product->id)->take(4) as $relatedProduct)
                    <a href="{{ route('product.show', $relatedProduct->slug) }}" class="group">
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-3">
                            @if($relatedProduct->images->first())
                                <img src="{{ $relatedProduct->images->first()->medium_url }}" 
                                     alt="{{ $relatedProduct->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <h3 class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors">{{ $relatedProduct->name }}</h3>
                        @if($relatedProduct->variants->count() > 0)
                            @php
                                $minPrice = $relatedProduct->variants->min('price');
                                $maxPrice = $relatedProduct->variants->max('price');
                            @endphp
                            @if($minPrice == $maxPrice)
                                <p class="text-green-600 font-bold">{{ number_format($minPrice, 2) }} ₺</p>
                            @else
                                <p class="text-green-600 font-bold">{{ number_format($minPrice, 2) }} - {{ number_format($maxPrice, 2) }} ₺</p>
                            @endif
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Image Modal -->
<div id="image-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="max-w-4xl max-h-full">
        <img id="modal-image" src="" alt="" class="max-w-full max-h-full object-contain">
    </div>
</div>

@push('scripts')
<script>
let selectedVariantId = {{ $product->variants->first()?->id ?? 'null' }};
let selectedPrice = {{ $product->variants->first()?->price ?? 0 }};
let selectedStock = {{ $product->variants->first()?->stock_quantity ?? 0 }};

// Initialize Swiper for mobile gallery
@if($product->images->count() > 1)
const mobileGallerySwiper = new Swiper('.mobile-gallery', {
    slidesPerView: 3,
    spaceBetween: 10,
    centeredSlides: false,
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    breakpoints: {
        320: {
            slidesPerView: 2.5,
            spaceBetween: 8,
        },
        480: {
            slidesPerView: 3.5,
            spaceBetween: 10,
        }
    }
});
@endif

function changeMainImage(src, alt, element) {
    document.getElementById('main-image').src = src;
    document.getElementById('main-image').alt = alt;
    
    // Update desktop thumbnail borders
    document.querySelectorAll('.hidden.md\\:grid .border-2').forEach(thumb => {
        thumb.classList.remove('border-blue-500');
        thumb.classList.add('border-transparent');
    });
    element.classList.remove('border-transparent');
    element.classList.add('border-blue-500');
}

function changeMainImageMobile(src, alt, element) {
    document.getElementById('main-image').src = src;
    document.getElementById('main-image').alt = alt;
    
    // Update mobile thumbnail borders
    document.querySelectorAll('.md\\:hidden .border-2').forEach(thumb => {
        thumb.classList.remove('border-blue-500');
        thumb.classList.add('border-transparent');
    });
    element.classList.remove('border-transparent');
    element.classList.add('border-blue-500');
}

function selectVariant(element, variantId, price, stock) {
    // Update selection
    document.querySelectorAll('.variant-option').forEach(option => {
        option.classList.remove('border-blue-500', 'bg-blue-50');
        option.classList.add('border-gray-200');
    });
    element.classList.remove('border-gray-200');
    element.classList.add('border-blue-500', 'bg-blue-50');
    
    // Update selected values
    selectedVariantId = variantId;
    selectedPrice = price;
    selectedStock = stock;
    
    // Update add to cart button
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    if (stock > 0) {
        addToCartBtn.disabled = false;
        addToCartBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
        addToCartBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        addToCartBtn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6.05"></path>
            </svg>
            <span>Sepete Ekle</span>
        `;
    } else {
        addToCartBtn.disabled = true;
        addToCartBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        addToCartBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        addToCartBtn.innerHTML = 'Stokta Yok';
    }
}

function addToCart() {
    if (!selectedVariantId || selectedStock <= 0) {
        alert('Lütfen bir seçenek belirleyin.');
        return;
    }
    
    // Here you would typically make an AJAX request to add the item to cart
    alert(`Ürün sepete eklendi!\nVaryant ID: ${selectedVariantId}\nFiyat: ${selectedPrice.toFixed(2)} ₺`);
}

function openImageModal(src) {
    document.getElementById('modal-image').src = src;
    document.getElementById('image-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('image-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});

// Touch events for mobile image swiping on main image
let touchStartX = 0;
let touchEndX = 0;

@if($product->images->count() > 1)
const mainImage = document.getElementById('main-image');
let currentImageIndex = 0;
const images = @json($product->images->map(fn($img) => ['url' => $img->large_url, 'alt' => $img->alt_text ?: $product->name]));

mainImage.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
});

mainImage.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            // Swipe left - next image
            currentImageIndex = (currentImageIndex + 1) % images.length;
        } else {
            // Swipe right - previous image
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
        }
        
        // Update main image
        mainImage.src = images[currentImageIndex].url;
        mainImage.alt = images[currentImageIndex].alt;
        
        // Update thumbnail highlights
        updateThumbnailHighlight(currentImageIndex);
    }
}

function updateThumbnailHighlight(index) {
    // Update desktop thumbnails
    const desktopThumbs = document.querySelectorAll('.hidden.md\\:grid .border-2');
    desktopThumbs.forEach((thumb, i) => {
        if (i === index) {
            thumb.classList.remove('border-transparent');
            thumb.classList.add('border-blue-500');
        } else {
            thumb.classList.remove('border-blue-500');
            thumb.classList.add('border-transparent');
        }
    });
    
    // Update mobile thumbnails
    const mobileThumbs = document.querySelectorAll('.md\\:hidden .border-2');
    mobileThumbs.forEach((thumb, i) => {
        if (i === index) {
            thumb.classList.remove('border-transparent');
            thumb.classList.add('border-blue-500');
        } else {
            thumb.classList.remove('border-blue-500');
            thumb.classList.add('border-transparent');
        }
    });
}
@endif

// Add custom styles for Swiper pagination
const style = document.createElement('style');
style.textContent = `
    .mobile-gallery .swiper-pagination {
        bottom: -25px !important;
    }
    .mobile-gallery .swiper-pagination-bullet {
        background: #cbd5e1 !important;
        opacity: 1 !important;
    }
    .mobile-gallery .swiper-pagination-bullet-active {
        background: #3b82f6 !important;
    }
`;
document.head.appendChild(style);
</script>
@endpush
@endsection