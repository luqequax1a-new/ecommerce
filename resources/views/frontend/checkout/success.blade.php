@extends('layouts.app')

@section('title', $seoData['title'] ?? 'Sipariş Tamamlandı')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto text-center">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Siparişiniz Başarıyla Oluşturuldu!</h1>
            
            <p class="text-gray-600 mb-6">
                Sipariş numaranız: <span class="font-semibold">ORD-{{ uniqid() }}</span><br>
                En kısa sürede kargoya verilecektir. Sipariş durumunuzu hesabınızdan takip edebilirsiniz.
            </p>
            
            <div class="bg-blue-50 rounded-lg p-6 mb-6 text-left">
                <h3 class="font-semibold text-blue-800 mb-2">Sipariş Detayı</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Siparişiniz onaylandı ve işleme alındı</li>
                    <li>• Ödeme başarıyla gerçekleşti</li>
                    <li>• Kargo işlemi başlatıldı</li>
                    <li>• Teslimat süresi 2-3 iş günüdür</li>
                </ul>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('home') }}" 
                   class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition duration-300">
                    Alışverişe Devam Et
                </a>
                <a href="#" 
                   class="px-6 py-3 border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-md transition duration-300">
                    Sipariş Detayı
                </a>
            </div>
        </div>
    </div>
</div>
@endsection