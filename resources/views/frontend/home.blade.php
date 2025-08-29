@extends('layouts.app')

@section('title', 'Anasayfa')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Hoş Geldiniz</h1>
        <p class="text-xl text-gray-600">En kaliteli ürünleri en uygun fiyatlarla sunuyoruz</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <div class="text-blue-600 text-4xl mb-4">🚚</div>
            <h3 class="text-xl font-semibold mb-2">Hızlı Kargo</h3>
            <p class="text-gray-600">Siparişleriniz 24 saat içinde kargoda</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <div class="text-blue-600 text-4xl mb-4">🔒</div>
            <h3 class="text-xl font-semibold mb-2">Güvenli Alışveriş</h3>
            <p class="text-gray-600">100% güvenli ödeme sistemleri</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <div class="text-blue-600 text-4xl mb-4">↩️</div>
            <h3 class="text-xl font-semibold mb-2">Kolay İade</h3>
            <p class="text-gray-600">14 gün içinde ücretsiz iade</p>
        </div>
    </div>
    
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Öne Çıkan Ürünler</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Sample product cards -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="bg-gray-200 h-48 flex items-center justify-center">
                    <span class="text-gray-500">Ürün Görseli</span>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900">Öne Çıkan Ürün</h3>
                    <p class="text-gray-600 text-sm mt-1">Ürün açıklaması</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-gray-900">₺129,90</span>
                        <button class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-700 transition-colors">
                            Sepete Ekle
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="bg-gray-200 h-48 flex items-center justify-center">
                    <span class="text-gray-500">Ürün Görseli</span>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900">Öne Çıkan Ürün</h3>
                    <p class="text-gray-600 text-sm mt-1">Ürün açıklaması</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-gray-900">₺199,90</span>
                        <button class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-700 transition-colors">
                            Sepete Ekle
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="bg-gray-200 h-48 flex items-center justify-center">
                    <span class="text-gray-500">Ürün Görseli</span>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900">Öne Çıkan Ürün</h3>
                    <p class="text-gray-600 text-sm mt-1">Ürün açıklaması</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-gray-900">₺89,90</span>
                        <button class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-700 transition-colors">
                            Sepete Ekle
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="bg-gray-200 h-48 flex items-center justify-center">
                    <span class="text-gray-500">Ürün Görseli</span>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900">Öne Çıkan Ürün</h3>
                    <p class="text-gray-600 text-sm mt-1">Ürün açıklaması</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-gray-900">₺149,90</span>
                        <button class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-700 transition-colors">
                            Sepete Ekle
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection