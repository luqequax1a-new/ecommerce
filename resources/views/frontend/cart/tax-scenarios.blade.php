@extends('layouts.app')

@section('title', 'Vergi Senaryoları Testi')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Vergi Senaryoları Testi</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Farklı Müşteri Türlerine Göre Vergi Hesaplama</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Input Section -->
                <div>
                    <div class="mb-6">
                        <label for="tax-amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Tutar (₺)
                        </label>
                        <input type="number" id="tax-amount" min="0" step="0.01" value="100" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Müşteri Türü
                        </label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="radio" id="scenario-individual" name="tax-scenario" value="individual" checked 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                <label for="scenario-individual" class="ml-2 text-sm text-gray-700">
                                    Bireysel Müşteri
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="scenario-company" name="tax-scenario" value="company" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                <label for="scenario-company" class="ml-2 text-sm text-gray-700">
                                    Kurumsal Müşteri
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="scenario-export" name="tax-scenario" value="export" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                <label for="scenario-export" class="ml-2 text-sm text-gray-700">
                                    İhracat
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button id="calculate-tax" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                        Vergi Hesapla
                    </button>
                </div>
                
                <!-- Result Section -->
                <div>
                    <div id="tax-result" class="hidden">
                        <!-- Results will be displayed here -->
                    </div>
                    
                    <!-- Scenario Explanations -->
                    <div class="mt-8 space-y-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900 mb-2">Bireysel Müşteri</h3>
                            <p class="text-sm text-gray-600">
                                Bireysel müşteriler için standart KDV oranları uygulanır. 
                                Ürün fiyatı vergi dahil olarak gösterilir.
                            </p>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900 mb-2">Kurumsal Müşteri</h3>
                            <p class="text-sm text-gray-600">
                                Kurumsal müşteriler için ters yüklemeli KDV uygulanabilir. 
                                Ürün fiyatı vergi hariç olarak gösterilir ve KDV faturada ayrı gösterilir.
                            </p>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900 mb-2">İhracat</h3>
                            <p class="text-sm text-gray-600">
                                İhracat işlemleri için %0 KDV uygulanır. 
                                Ürün fiyatı vergi hariç olarak gösterilir.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tax Display Settings -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Vergi Görünüm Ayarları</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-medium text-gray-900 mb-3">Fiyat Gösterimi</h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="radio" id="display-inclusive" name="tax-display" value="inclusive" checked 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                            <label for="display-inclusive" class="ml-2 text-sm text-gray-700">
                                Vergi Dahil Fiyatlar
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="display-exclusive" name="tax-display" value="exclusive" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                            <label for="display-exclusive" class="ml-2 text-sm text-gray-700">
                                Vergi Hariç Fiyatlar
                            </label>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button id="save-tax-display" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                            Ayarları Kaydet
                        </button>
                    </div>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-900 mb-3">Açıklamalar</h3>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>
                            <strong>Vergi Dahil:</strong> Ürün fiyatlarında KDV dahil edilir ve bu şekilde gösterilir.
                        </p>
                        <p>
                            <strong>Vergi Hariç:</strong> Ürün fiyatlarında KDV hariçtir ve KDV tutarı ayrı gösterilir.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/frontend/cart-tax-calculator.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tax display toggle functionality
    const saveTaxDisplayButton = document.getElementById('save-tax-display');
    if (saveTaxDisplayButton) {
        saveTaxDisplayButton.addEventListener('click', function() {
            const displayType = document.querySelector('input[name="tax-display"]:checked').value;
            
            fetch('/cart/toggle-tax-display', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    display_type: displayType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('Vergi görünüm ayarı güncellendi');
                } else {
                    // Show error message
                    alert('Ayar güncellenirken bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                alert('Ayar güncellenirken bir hata oluştu: ' + error.message);
            });
        });
    }
});
</script>
@endsection