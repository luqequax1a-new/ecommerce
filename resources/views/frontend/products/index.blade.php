@extends('layouts.app')

@section('title', 'Ürünler')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Ürünler</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Sample product cards -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <div class="bg-gray-200 h-48 flex items-center justify-center">
                <span class="text-gray-500">Ürün Görseli</span>
            </div>
            <div class="p-4">
                <h3 class="text-lg font-medium text-gray-900">Örnek Ürün</h3>
                <p class="text-gray-600 text-sm mt-1">Ürün açıklaması burada</p>
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
                <h3 class="text-lg font-medium text-gray-900">Örnek Ürün</h3>
                <p class="text-gray-600 text-sm mt-1">Ürün açıklaması burada</p>
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-lg font-bold text-gray-900">₺149,90</span>
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
                <h3 class="text-lg font-medium text-gray-900">Örnek Ürün</h3>
                <p class="text-gray-600 text-sm mt-1">Ürün açıklaması burada</p>
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-lg font-bold text-gray-900">₺249,90</span>
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
                <h3 class="text-lg font-medium text-gray-900">Örnek Ürün</h3>
                <p class="text-gray-600 text-sm mt-1">Ürün açıklaması burada</p>
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-lg font-bold text-gray-900">₺199,90</span>
                    <button class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-700 transition-colors">
                        Sepete Ekle
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection