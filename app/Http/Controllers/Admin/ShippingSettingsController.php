<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingSettings;
use App\Services\SimpleShippingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ShippingSettingsController extends Controller
{
    protected $shippingService;

    public function __construct(SimpleShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Display shipping settings
     */
    public function index(): View
    {
        $settings = ShippingSettings::current();
        $configuration = $this->shippingService->getConfiguration();
        $validation = $this->shippingService->validateConfiguration();
        $statistics = $this->shippingService->getShippingStatistics();
        
        return view('admin.shipping.settings.index', compact(
            'settings',
            'configuration',
            'validation',
            'statistics'
        ));
    }

    /**
     * Show the form for editing shipping settings
     */
    public function edit(): View
    {
        $settings = ShippingSettings::current();
        $fieldLabels = ShippingSettings::getFieldLabels();
        
        return view('admin.shipping.settings.edit', compact('settings', 'fieldLabels'));
    }

    /**
     * Update shipping settings
     */
    public function update(Request $request): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), ShippingSettings::getValidationRules(), [
                'free_threshold.min' => 'Ücretsiz kargo eşiği en az 0 olmalıdır',
                'free_threshold.max' => 'Ücretsiz kargo eşiği çok yüksek',
                'flat_rate_fee.min' => 'Kargo ücreti en az 0 olmalıdır',
                'flat_rate_fee.max' => 'Kargo ücreti çok yüksek',
                'cod_extra_fee.min' => 'Kapıda ödeme ücreti en az 0 olmalıdır',
                'cod_extra_fee.max' => 'Kapıda ödeme ücreti çok yüksek',
                'currency.in' => 'Sadece TRY para birimi desteklenmektedir',
                'free_shipping_message.max' => 'Ücretsiz kargo mesajı en fazla 500 karakter olabilir',
                'shipping_description.max' => 'Kargo açıklaması en fazla 1000 karakter olabilir',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Doğrulama hataları düzeltilmelidir');
            }

            $data = $validator->validated();
            
            // Ensure at least one shipping option is enabled
            if (!$data['flat_rate_enabled'] && !$data['free_enabled']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'En az bir kargo seçeneği aktif olmalıdır');
            }
            
            // Get current settings and update
            $settings = ShippingSettings::current();
            $settings->updateSettings($data);
            
            // Refresh service settings
            $this->shippingService->refreshSettings();
            
            return redirect()->route('admin.shipping.settings.index')
                ->with('success', 'Kargo ayarları başarıyla güncellendi');
                
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Doğrulama hatası oluştu');
        } catch (\Exception $e) {
            \Log::error('Shipping settings update error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Kargo ayarları güncellenirken bir hata oluştu');
        }
    }

    /**
     * Test shipping calculation with sample data
     */
    public function testCalculation(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subtotal' => 'required|numeric|min:0|max:999999',
                'use_cod' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz test verileri',
                    'errors' => $validator->errors()
                ], 422);
            }

            $subtotal = $request->input('subtotal');
            $useCOD = $request->boolean('use_cod');
            
            $calculation = $this->shippingService->calculateTotal($subtotal, $useCOD);
            $breakdown = $this->shippingService->getShippingBreakdown($subtotal, $useCOD);
            $message = $this->shippingService->getFreeShippingMessage($subtotal);
            $recommendations = $this->shippingService->getCartRecommendations($subtotal);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'subtotal' => $subtotal,
                    'subtotal_formatted' => $this->shippingService->formatCurrency($subtotal),
                    'use_cod' => $useCOD,
                    'calculation' => $calculation,
                    'breakdown' => $breakdown,
                    'free_shipping_message' => $message,
                    'recommendations' => $recommendations
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Shipping calculation test error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Hesaplama testi sırasında hata oluştu'
            ], 500);
        }
    }

    /**
     * Get shipping configuration for API
     */
    public function getConfiguration(): JsonResponse
    {
        try {
            $configuration = $this->shippingService->getConfiguration();
            $validation = $this->shippingService->validateConfiguration();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'configuration' => $configuration,
                    'validation' => $validation
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kargo konfigürasyonu alınamadı'
            ], 500);
        }
    }

    /**
     * Reset to default settings
     */
    public function resetToDefaults(): RedirectResponse
    {
        try {
            $settings = ShippingSettings::current();
            
            $settings->updateSettings([
                'free_enabled' => true,
                'free_threshold' => 300.00,
                'flat_rate_enabled' => true,
                'flat_rate_fee' => 15.00,
                'cod_enabled' => true,
                'cod_extra_fee' => 5.00,
                'currency' => 'TRY',
                'free_shipping_message' => 'Kargo ücretsiz için ₺{remaining} daha alışveriş yapın.',
                'shipping_description' => 'Standart kargo hızlı teslimat',
                'is_active' => true,
            ]);
            
            $this->shippingService->refreshSettings();
            
            return redirect()->route('admin.shipping.settings.index')
                ->with('success', 'Kargo ayarları varsayılan değerlere sıfırlandı');
                
        } catch (\Exception $e) {
            \Log::error('Reset shipping settings error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Ayarlar sıfırlanırken hata oluştu');
        }
    }

    /**
     * Toggle specific setting
     */
    public function toggleSetting(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'setting' => 'required|string|in:free_enabled,flat_rate_enabled,cod_enabled',
                'value' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz ayar parametresi'
                ], 422);
            }

            $setting = $request->input('setting');
            $value = $request->boolean('value');
            
            $settings = ShippingSettings::current();
            
            // Prevent disabling all shipping options
            if (!$value && $setting === 'flat_rate_enabled' && !$settings->free_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'En az bir kargo seçeneği aktif kalmalıdır'
                ], 422);
            }
            
            if (!$value && $setting === 'free_enabled' && !$settings->flat_rate_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'En az bir kargo seçeneği aktif kalmalıdır'
                ], 422);
            }
            
            $settings->updateSettings([$setting => $value]);
            $this->shippingService->refreshSettings();
            
            return response()->json([
                'success' => true,
                'message' => 'Ayar başarıyla güncellendi',
                'data' => [
                    'setting' => $setting,
                    'value' => $value
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Toggle shipping setting error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Ayar güncellenirken hata oluştu'
            ], 500);
        }
    }

    /**
     * Get shipping statistics for dashboard
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->shippingService->getShippingStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'İstatistikler alınamadı'
            ], 500);
        }
    }
}
