<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\District;
use App\Models\Neighborhood;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    /**
     * Show checkout page with Turkey address system
     */
    public function index()
    {
        // Get provinces for dropdown
        $provinces = Province::getCachedList();
        
        // SEO Meta bilgileri
        $seoData = [
            'title' => 'Ödeme ve Kargo Bilgileri - ' . config('app.name'),
            'description' => 'Güvenli ödeme ve hızlı kargo ile alışverişin keyfini çıkarın.',
            'keywords' => 'ödeme, kargo, teslimat, alışveriş, güvenli ödeme',
            'canonical_url' => route('checkout.index'),
        ];

        return view('frontend.checkout.index', compact('provinces', 'seoData'));
    }

    /**
     * Get districts for a province (AJAX)
     */
    public function getDistricts(int $provinceId): JsonResponse
    {
        try {
            $districts = District::getCachedListByProvince($provinceId);
            
            return response()->json([
                'success' => true,
                'data' => $districts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'İlçeler alınamadı'
            ], 500);
        }
    }

    /**
     * Get neighborhoods for a district (AJAX)
     */
    public function getNeighborhoods(int $districtId): JsonResponse
    {
        try {
            $neighborhoods = Neighborhood::getCachedListByDistrict($districtId);
            
            return response()->json([
                'success' => true,
                'data' => $neighborhoods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mahalleler alınamadı'
            ], 500);
        }
    }

    /**
     * Process checkout form submission
     */
    public function process(Request $request)
    {
        // Validate checkout data
        $validated = $request->validate([
            // Billing address
            'billing_address.first_name' => 'required|string|max:50',
            'billing_address.last_name' => 'required|string|max:50',
            'billing_address.company' => 'nullable|string|max:100',
            'billing_address.tax_office' => 'nullable|string|max:50',
            'billing_address.tax_number' => 'nullable|string|max:20',
            'billing_address.phone' => 'required|string|max:20',
            'billing_address.email' => 'required|email|max:100',
            'billing_address.province_id' => 'required|exists:provinces,id',
            'billing_address.district_id' => 'required|exists:districts,id',
            'billing_address.neighborhood_id' => 'nullable|exists:neighborhoods,id',
            'billing_address.address' => 'required|string|max:500',
            'billing_address.postal_code' => 'nullable|string|max:10',
            
            // Shipping address (optional - same as billing)
            'shipping_address.same_as_billing' => 'boolean',
            'shipping_address.first_name' => 'required_if:same_as_billing,false|string|max:50',
            'shipping_address.last_name' => 'required_if:same_as_billing,false|string|max:50',
            'shipping_address.company' => 'nullable|string|max:100',
            'shipping_address.phone' => 'required_if:same_as_billing,false|string|max:20',
            'shipping_address.province_id' => 'required_if:same_as_billing,false|exists:provinces,id',
            'shipping_address.district_id' => 'required_if:same_as_billing,false|exists:districts,id',
            'shipping_address.neighborhood_id' => 'nullable|exists:neighborhoods,id',
            'shipping_address.address' => 'required_if:same_as_billing,false|string|max:500',
            'shipping_address.postal_code' => 'nullable|string|max:10',
            
            // Shipping method
            'shipping_method' => 'required|string|in:standard,express',
            
            // Payment method
            'payment_method' => 'required|string|in:credit_card,bank_transfer,cod',
            
            // Order notes
            'order_notes' => 'nullable|string|max:1000',
        ], [
            // Turkish validation messages
            'billing_address.first_name.required' => 'Ad alanı zorunludur',
            'billing_address.last_name.required' => 'Soyad alanı zorunludur',
            'billing_address.phone.required' => 'Telefon alanı zorunludur',
            'billing_address.email.required' => 'E-posta alanı zorunludur',
            'billing_address.email.email' => 'Geçerli bir e-posta adresi giriniz',
            'billing_address.province_id.required' => 'İl seçimi zorunludur',
            'billing_address.province_id.exists' => 'Geçerli bir il seçiniz',
            'billing_address.district_id.required' => 'İlçe seçimi zorunludur',
            'billing_address.district_id.exists' => 'Geçerli bir ilçe seçiniz',
            'billing_address.address.required' => 'Adres alanı zorunludur',
            'shipping_address.first_name.required_if' => 'Ad alanı zorunludur',
            'shipping_address.last_name.required_if' => 'Soyad alanı zorunludur',
            'shipping_address.phone.required_if' => 'Telefon alanı zorunludur',
            'shipping_address.province_id.required_if' => 'İl seçimi zorunludur',
            'shipping_address.province_id.exists' => 'Geçerli bir il seçiniz',
            'shipping_address.district_id.required_if' => 'İlçe seçimi zorunludur',
            'shipping_address.district_id.exists' => 'Geçerli bir ilçe seçiniz',
            'shipping_address.address.required_if' => 'Adres alanı zorunludur',
        ]);

        // Process checkout data
        // This would typically integrate with an order processing system
        // For now, we'll just return a success response
        
        return response()->json([
            'success' => true,
            'message' => 'Siparişiniz başarıyla oluşturuldu',
            'order_id' => uniqid('ORD-'),
            'redirect_url' => route('checkout.success')
        ]);
    }

    /**
     * Show checkout success page
     */
    public function success()
    {
        // SEO Meta bilgileri
        $seoData = [
            'title' => 'Sipariş Tamamlandı - ' . config('app.name'),
            'description' => 'Siparişiniz başarıyla oluşturuldu. En kısa sürede kargoya verilecektir.',
            'keywords' => 'sipariş, tamamlandı, kargo, teslimat',
            'canonical_url' => route('checkout.success'),
        ];

        return view('frontend.checkout.success', compact('seoData'));
    }

    /**
     * Show checkout failure page
     */
    public function failure()
    {
        // SEO Meta bilgileri
        $seoData = [
            'title' => 'Sipariş Başarısız - ' . config('app.name'),
            'description' => 'Siparişiniz oluşturulurken bir hata oluştu. Lütfen tekrar deneyiniz.',
            'keywords' => 'sipariş, başarısız, hata, ödeme',
            'canonical_url' => route('checkout.failure'),
        ];

        return view('frontend.checkout.failure', compact('seoData'));
    }
}