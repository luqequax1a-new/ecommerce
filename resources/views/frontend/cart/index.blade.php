@extends('layouts.app')

@section('title', 'Sepetim')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Alışveriş Sepetim</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Sepet İçeriği</h2>
                        
                        <div class="space-y-6" id="cart-items">
                            <!-- Sample cart items -->
                            <div class="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-24 h-24 bg-gray-200 rounded-md overflow-hidden">
                                        <img src="https://placehold.co/100x100" alt="Ürün" class="w-full h-full object-cover">
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex justify-between">
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900">Klasik Tişört</h3>
                                                <p class="mt-1 text-sm text-gray-500">Beyaz, M</p>
                                            </div>
                                            <p class="text-lg font-medium text-gray-900">₺89,90</p>
                                        </div>
                                        <div class="mt-2 flex items-center">
                                            <div class="flex items-center border border-gray-300 rounded">
                                                <button class="px-3 py-1 text-gray-600 hover:bg-gray-100">-</button>
                                                <input type="number" min="1" value="1" class="w-12 text-center border-x border-gray-300">
                                                <button class="px-3 py-1 text-gray-600 hover:bg-gray-100">+</button>
                                            </div>
                                            <button class="ml-4 text-red-600 hover:text-red-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tax Information -->
                                <div class="mt-3 bg-blue-50 rounded-md p-3">
                                    <div class="flex justify-between text-sm">
                                        <span>Vergi Dahil Fiyat:</span>
                                        <span class="font-medium">₺89,90</span>
                                    </div>
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>KDV (%18):</span>
                                        <span>₺13,77</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-24 h-24 bg-gray-200 rounded-md overflow-hidden">
                                        <img src="https://placehold.co/100x100" alt="Ürün" class="w-full h-full object-cover">
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex justify-between">
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900">Spor Ayakkabı</h3>
                                                <p class="mt-1 text-sm text-gray-500">Siyah, 42</p>
                                            </div>
                                            <p class="text-lg font-medium text-gray-900">₺249,90</p>
                                        </div>
                                        <div class="mt-2 flex items-center">
                                            <div class="flex items-center border border-gray-300 rounded">
                                                <button class="px-3 py-1 text-gray-600 hover:bg-gray-100">-</button>
                                                <input type="number" min="1" value="1" class="w-12 text-center border-x border-gray-300">
                                                <button class="px-3 py-1 text-gray-600 hover:bg-gray-100">+</button>
                                            </div>
                                            <button class="ml-4 text-red-600 hover:text-red-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tax Information -->
                                <div class="mt-3 bg-blue-50 rounded-md p-3">
                                    <div class="flex justify-between text-sm">
                                        <span>Vergi Dahil Fiyat:</span>
                                        <span class="font-medium">₺249,90</span>
                                    </div>
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>KDV (%18):</span>
                                        <span>₺38,18</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <a href="{{ route('frontend.products.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Alışverişe Devam Et
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Tax Scenario Examples -->
                <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6">Vergi Senaryoları</h2>
                        
                        <div class="space-y-6">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="font-medium text-gray-900 mb-2">Bireysel Müşteri - Standart KDV</h3>
                                <p class="text-sm text-gray-600 mb-3">Bireysel müşteriler için standart %18 KDV uygulanır.</p>
                                <div class="bg-gray-50 rounded-md p-3 text-sm">
                                    <div class="flex justify-between">
                                        <span>Ürün Tutarı:</span>
                                        <span>₺100,00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>KDV (%18):</span>
                                        <span>₺18,00</span>
                                    </div>
                                    <div class="flex justify-between font-medium mt-2 pt-2 border-t border-gray-200">
                                        <span>Toplam:</span>
                                        <span>₺118,00</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="font-medium text-gray-900 mb-2">Kurumsal Müşteri - Ters Yüklemeli KDV</h3>
                                <p class="text-sm text-gray-600 mb-3">Kurumsal müşteriler için ters yüklemeli KDV uygulanabilir.</p>
                                <div class="bg-gray-50 rounded-md p-3 text-sm">
                                    <div class="flex justify-between">
                                        <span>Ürün Tutarı:</span>
                                        <span>₺100,00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>KDV (%18):</span>
                                        <span>₺0,00</span>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span colspan="2">* KDV müşteri tarafından ödenecektir</span>
                                    </div>
                                    <div class="flex justify-between font-medium mt-2 pt-2 border-t border-gray-200">
                                        <span>Toplam:</span>
                                        <span>₺100,00</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="font-medium text-gray-900 mb-2">İhracat - KDV Hariç</h3>
                                <p class="text-sm text-gray-600 mb-3">İhracat işlemleri için KDV uygulanmaz.</p>
                                <div class="bg-gray-50 rounded-md p-3 text-sm">
                                    <div class="flex justify-between">
                                        <span>Ürün Tutarı:</span>
                                        <span>₺100,00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>KDV (%0):</span>
                                        <span>₺0,00</span>
                                    </div>
                                    <div class="flex justify-between font-medium mt-2 pt-2 border-t border-gray-200">
                                        <span>Toplam:</span>
                                        <span>₺100,00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div>
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Sipariş Özeti</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span>Ara Toplam</span>
                            <span>₺339,80</span>
                        </div>
                        
                        <!-- Tax Breakdown -->
                        <div class="border-t border-gray-200 pt-4">
                            <h3 class="font-medium text-gray-900 mb-2">Vergi Dağılımı</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>KDV (%18)</span>
                                    <span>₺51,95</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Shipping -->
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between">
                                <span>Kargo Ücreti</span>
                                <span>₺14,90</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600 mt-1">
                                <span>Kargo KDV (%18)</span>
                                <span>₺2,68</span>
                            </div>
                        </div>
                        
                        <!-- Total -->
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between text-lg font-medium">
                                <span>Toplam</span>
                                <span>₺409,33</span>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                Ödeme Yap
                            </button>
                        </div>
                        
                        <!-- Tax Settings Toggle -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-3">Vergi Görünümü</h3>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input type="radio" id="tax-inclusive" name="tax-display" value="inclusive" checked class="h-4 w-4 text-blue-600">
                                    <label for="tax-inclusive" class="ml-2 text-sm text-gray-700">Vergi Dahil Fiyatlar</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" id="tax-exclusive" name="tax-display" value="exclusive" class="h-4 w-4 text-blue-600">
                                    <label for="tax-exclusive" class="ml-2 text-sm text-gray-700">Vergi Hariç Fiyatlar</label>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-sm text-gray-600">
                                <p>* Vergi görünümü tercihiniz sepet ve ödeme sayfalarında uygulanacaktır.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Free Shipping Message -->
                <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="flex-shrink-0 w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Ücretsiz Kargo Fırsatı!</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>₺160,20 daha alışveriş yaparak ücretsiz kargo fırsatından yararlanabilirsiniz.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tax display toggle functionality
    const taxDisplayRadios = document.querySelectorAll('input[name="tax-display"]');
    taxDisplayRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'inclusive') {
                // Show prices with tax included
                console.log('Switching to tax inclusive display');
            } else {
                // Show prices with tax excluded
                console.log('Switching to tax exclusive display');
            }
        });
    });
});
</script>
@endsection