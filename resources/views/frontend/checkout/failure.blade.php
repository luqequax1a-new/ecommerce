@extends('layouts.app')

@section('title', $seoData['title'] ?? 'Sipariş Başarısız')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto text-center">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Sipariş İşleminiz Başarısız Oldu!</h1>
            
            <p class="text-gray-600 mb-6">
                Siparişiniz oluşturulurken bir hata oluştu. Lütfen bilgilerinizi kontrol edip tekrar deneyiniz.
            </p>
            
            <div class="bg-yellow-50 rounded-lg p-6 mb-6 text-left">
                <h3 class="font-semibold text-yellow-800 mb-2">Olası Nedenler</h3>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• Ödeme bilgilerinizde hata olabilir</li>
                    <li>• Stok yetersizliği olabilir</li>
                    <li>• Sistemsel bir sorun oluşmuş olabilir</li>
                    <li>• Ağ bağlantınızda sorun olabilir</li>
                </ul>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('checkout.index') }}" 
                   class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition duration-300">
                    Tekrar Dene
                </a>
                <a href="{{ route('home') }}" 
                   class="px-6 py-3 border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-md transition duration-300">
                    Alışverişe Devam Et
                </a>
            </div>
        </div>
    </div>
</div>
@endsection