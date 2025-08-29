@extends('layouts.app')

@section('title', $seoData['title'] ?? 'Ödeme ve Kargo Bilgileri')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Ödeme ve Kargo Bilgileri</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Billing and Shipping Address -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Adres Bilgileri</h2>
                
                <form id="checkout-form" class="space-y-6">
                    <!-- Billing Address -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Fatura Adresi</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="billing_first_name" class="block text-sm font-medium text-gray-700 mb-1">Ad *</label>
                                <input type="text" id="billing_first_name" name="billing_address[first_name]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="billing_last_name" class="block text-sm font-medium text-gray-700 mb-1">Soyad *</label>
                                <input type="text" id="billing_last_name" name="billing_address[last_name]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="billing_company" class="block text-sm font-medium text-gray-700 mb-1">Şirket Adı</label>
                            <input type="text" id="billing_company" name="billing_address[company]"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="billing_tax_office" class="block text-sm font-medium text-gray-700 mb-1">Vergi Dairesi</label>
                                <input type="text" id="billing_tax_office" name="billing_address[tax_office]"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="billing_tax_number" class="block text-sm font-medium text-gray-700 mb-1">Vergi Numarası</label>
                                <input type="text" id="billing_tax_number" name="billing_address[tax_number]"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="billing_phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon *</label>
                                <input type="tel" id="billing_phone" name="billing_address[phone]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="billing_email" class="block text-sm font-medium text-gray-700 mb-1">E-posta *</label>
                                <input type="email" id="billing_email" name="billing_address[email]" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="billing_province" class="block text-sm font-medium text-gray-700 mb-1">İl *</label>
                                <select id="billing_province" name="billing_address[province_id]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">İl Seçiniz</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="billing_district" class="block text-sm font-medium text-gray-700 mb-1">İlçe *</label>
                                <select id="billing_district" name="billing_address[district_id]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                                    <option value="">İlçe Seçiniz</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="billing_neighborhood" class="block text-sm font-medium text-gray-700 mb-1">Mahalle</label>
                            <select id="billing_neighborhood" name="billing_address[neighborhood_id]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                                <option value="">Mahalle Seçiniz</option>
                            </select>
                        </div>
                        
                        <div class="mt-4">
                            <label for="billing_address" class="block text-sm font-medium text-gray-700 mb-1">Adres *</label>
                            <textarea id="billing_address" name="billing_address[address]" rows="3" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div class="mt-4">
                            <label for="billing_postal_code" class="block text-sm font-medium text-gray-700 mb-1">Posta Kodu</label>
                            <input type="text" id="billing_postal_code" name="billing_address[postal_code]"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div class="border-b border-gray-200 pb-6">
                        <div class="flex items-center mb-4">
                            <input type="checkbox" id="same_as_billing" name="shipping_address[same_as_billing]" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="same_as_billing" class="ml-2 block text-sm text-gray-900">
                                Fatura adresi ile aynı
                            </label>
                        </div>
                        
                        <div id="shipping_address_section">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Teslimat Adresi</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="shipping_first_name" class="block text-sm font-medium text-gray-700 mb-1">Ad *</label>
                                    <input type="text" id="shipping_first_name" name="shipping_address[first_name]"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div>
                                    <label for="shipping_last_name" class="block text-sm font-medium text-gray-700 mb-1">Soyad *</label>
                                    <input type="text" id="shipping_last_name" name="shipping_address[last_name]"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label for="shipping_company" class="block text-sm font-medium text-gray-700 mb-1">Şirket Adı</label>
                                <input type="text" id="shipping_company" name="shipping_address[company]"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mt-4">
                                <label for="shipping_phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon *</label>
                                <input type="tel" id="shipping_phone" name="shipping_address[phone]"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label for="shipping_province" class="block text-sm font-medium text-gray-700 mb-1">İl *</label>
                                    <select id="shipping_province" name="shipping_address[province_id]"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">İl Seçiniz</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="shipping_district" class="block text-sm font-medium text-gray-700 mb-1">İlçe *</label>
                                    <select id="shipping_district" name="shipping_address[district_id]"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                                        <option value="">İlçe Seçiniz</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label for="shipping_neighborhood" class="block text-sm font-medium text-gray-700 mb-1">Mahalle</label>
                                <select id="shipping_neighborhood" name="shipping_address[neighborhood_id]"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                                    <option value="">Mahalle Seçiniz</option>
                                </select>
                            </div>
                            
                            <div class="mt-4">
                                <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Adres *</label>
                                <textarea id="shipping_address" name="shipping_address[address]" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            
                            <div class="mt-4">
                                <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700 mb-1">Posta Kodu</label>
                                <input type="text" id="shipping_postal_code" name="shipping_address[postal_code]"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Order Summary and Payment -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Sipariş Özeti</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span>Ara Toplam</span>
                        <span>₺1.250,00</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span>Kargo</span>
                        <span>₺15,00</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span>KDV (%18)</span>
                        <span>₺225,00</span>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4 flex justify-between text-lg font-bold">
                        <span>Toplam</span>
                        <span>₺1.490,00</span>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Kargo Yöntemi</h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="radio" id="shipping_standard" name="shipping_method" value="standard" checked
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="shipping_standard" class="ml-3 block text-sm text-gray-700">
                                Standart Kargo (2-3 iş günü) - ₺15,00
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="shipping_express" name="shipping_method" value="express"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="shipping_express" class="ml-3 block text-sm text-gray-700">
                                Express Kargo (1 iş günü) - ₺25,00
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Ödeme Yöntemi</h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="radio" id="payment_credit_card" name="payment_method" value="credit_card" checked
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="payment_credit_card" class="ml-3 block text-sm text-gray-700">
                                Kredi Kartı
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="payment_bank_transfer" name="payment_method" value="bank_transfer"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="payment_bank_transfer" class="ml-3 block text-sm text-gray-700">
                                Havale/EFT
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="payment_cod" name="payment_method" value="cod"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="payment_cod" class="ml-3 block text-sm text-gray-700">
                                Kapıda Ödeme (+₺5,00)
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <label for="order_notes" class="block text-sm font-medium text-gray-700 mb-2">Sipariş Notları</label>
                    <textarea id="order_notes" name="order_notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Siparişinizle ilgili özel notlarınız varsa buraya yazabilirsiniz..."></textarea>
                </div>
                
                <div class="mt-8">
                    <button type="button" id="place-order-btn"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md transition duration-300">
                        Siparişi Tamamla
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for address dropdowns -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Billing address province change
    document.getElementById('billing_province').addEventListener('change', function() {
        const provinceId = this.value;
        const districtSelect = document.getElementById('billing_district');
        const neighborhoodSelect = document.getElementById('billing_neighborhood');
        
        // Reset district and neighborhood selects
        districtSelect.innerHTML = '<option value="">İlçe Seçiniz</option>';
        neighborhoodSelect.innerHTML = '<option value="">Mahalle Seçiniz</option>';
        districtSelect.disabled = true;
        neighborhoodSelect.disabled = true;
        
        if (provinceId) {
            fetch(`/checkout/districts/${provinceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        districtSelect.disabled = false;
                        data.data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name;
                            districtSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching districts:', error);
                });
        }
    });
    
    // Billing address district change
    document.getElementById('billing_district').addEventListener('change', function() {
        const districtId = this.value;
        const neighborhoodSelect = document.getElementById('billing_neighborhood');
        
        // Reset neighborhood select
        neighborhoodSelect.innerHTML = '<option value="">Mahalle Seçiniz</option>';
        neighborhoodSelect.disabled = true;
        
        if (districtId) {
            fetch(`/checkout/neighborhoods/${districtId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        neighborhoodSelect.disabled = false;
                        data.data.forEach(neighborhood => {
                            const option = document.createElement('option');
                            option.value = neighborhood.id;
                            option.textContent = neighborhood.name;
                            neighborhoodSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching neighborhoods:', error);
                });
        }
    });
    
    // Shipping address province change
    document.getElementById('shipping_province').addEventListener('change', function() {
        const provinceId = this.value;
        const districtSelect = document.getElementById('shipping_district');
        const neighborhoodSelect = document.getElementById('shipping_neighborhood');
        
        // Reset district and neighborhood selects
        districtSelect.innerHTML = '<option value="">İlçe Seçiniz</option>';
        neighborhoodSelect.innerHTML = '<option value="">Mahalle Seçiniz</option>';
        districtSelect.disabled = true;
        neighborhoodSelect.disabled = true;
        
        if (provinceId) {
            fetch(`/checkout/districts/${provinceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        districtSelect.disabled = false;
                        data.data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name;
                            districtSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching districts:', error);
                });
        }
    });
    
    // Shipping address district change
    document.getElementById('shipping_district').addEventListener('change', function() {
        const districtId = this.value;
        const neighborhoodSelect = document.getElementById('shipping_neighborhood');
        
        // Reset neighborhood select
        neighborhoodSelect.innerHTML = '<option value="">Mahalle Seçiniz</option>';
        neighborhoodSelect.disabled = true;
        
        if (districtId) {
            fetch(`/checkout/neighborhoods/${districtId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        neighborhoodSelect.disabled = false;
                        data.data.forEach(neighborhood => {
                            const option = document.createElement('option');
                            option.value = neighborhood.id;
                            option.textContent = neighborhood.name;
                            neighborhoodSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching neighborhoods:', error);
                });
        }
    });
    
    // Same as billing checkbox
    document.getElementById('same_as_billing').addEventListener('change', function() {
        const shippingSection = document.getElementById('shipping_address_section');
        if (this.checked) {
            shippingSection.style.display = 'none';
        } else {
            shippingSection.style.display = 'block';
        }
    });
    
    // Place order button
    document.getElementById('place-order-btn').addEventListener('click', function() {
        // In a real implementation, this would submit the form via AJAX
        // For now, we'll just show an alert
        alert('Sipariş tamamlanacak. Gerçek bir uygulamada burada ödeme işlemi başlatılır.');
    });
});
</script>
@endsection