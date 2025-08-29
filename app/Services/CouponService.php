<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CouponService
{
    /**
     * Validate a coupon code
     */
    public function validateCoupon(string $code, $user = null, float $cartAmount = 0): array
    {
        $coupon = Coupon::where('code', $code)->valid()->first();
        
        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Geçersiz kupon kodu.'
            ];
        }
        
        // Check minimum cart amount
        if ($cartAmount < $coupon->minimum_cart_amount) {
            return [
                'valid' => false,
                'message' => "Bu kupon en az ₺" . number_format($coupon->minimum_cart_amount, 2, ',', '.') . " alışveriş için geçerlidir."
            ];
        }
        
        // Check user-specific usage limit
        if ($user && !$coupon->isUsableByUser($user->id)) {
            return [
                'valid' => false,
                'message' => 'Bu kuponu kullanma hakkınız dolmuştur.'
            ];
        }
        
        return [
            'valid' => true,
            'coupon' => $coupon
        ];
    }

    /**
     * Calculate discount amount for a coupon
     */
    public function calculateDiscount(Coupon $coupon, float $cartAmount, array $cartItems = []): float
    {
        switch ($coupon->type) {
            case 'percentage':
                return $cartAmount * ($coupon->value / 100);
                
            case 'fixed_amount':
                return min($coupon->value, $cartAmount);
                
            case 'free_shipping':
                // This would be handled separately in shipping calculation
                return 0;
                
            case 'first_order':
                // This would require checking if it's the user's first order
                return $cartAmount * 0.10; // 10% discount as example
                
            default:
                return 0;
        }
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon(Coupon $coupon, $user = null, float $cartAmount = 0, array $cartItems = []): array
    {
        // Validate coupon
        $validation = $this->validateCoupon($coupon->code, $user, $cartAmount);
        
        if (!$validation['valid']) {
            return $validation;
        }
        
        // Calculate discount
        $discountAmount = $this->calculateDiscount($coupon, $cartAmount, $cartItems);
        
        // Record usage
        $usageData = [
            'coupon_id' => $coupon->id,
            'user_id' => $user ? $user->id : null,
            'discount_amount' => $discountAmount,
            'cart_amount' => $cartAmount,
            'used_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];
        
        try {
            DB::transaction(function () use ($coupon, $usageData) {
                // Increment usage count
                $coupon->increment('used_count');
                
                // Record usage
                CouponUsage::create($usageData);
            });
            
            return [
                'success' => true,
                'discount_amount' => $discountAmount,
                'formatted_discount' => '₺' . number_format($discountAmount, 2, ',', '.'),
                'coupon' => $coupon
            ];
        } catch (\Exception $e) {
            Log::error('Coupon application error', [
                'coupon_id' => $coupon->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Kupon uygulanırken bir hata oluştu.'
            ];
        }
    }

    /**
     * Get applicable coupons for a cart
     */
    public function getApplicableCoupons($user = null, float $cartAmount = 0, array $cartItems = []): array
    {
        $coupons = Coupon::valid()->get();
        $applicable = [];
        
        foreach ($coupons as $coupon) {
            $validation = $this->validateCoupon($coupon->code, $user, $cartAmount);
            
            if ($validation['valid']) {
                $discountAmount = $this->calculateDiscount($coupon, $cartAmount, $cartItems);
                
                $applicable[] = [
                    'coupon' => $coupon,
                    'discount_amount' => $discountAmount,
                    'formatted_discount' => '₺' . number_format($discountAmount, 2, ',', '.')
                ];
            }
        }
        
        // Sort by discount amount (highest first)
        usort($applicable, function ($a, $b) {
            return $b['discount_amount'] <=> $a['discount_amount'];
        });
        
        return $applicable;
    }

    /**
     * Get coupon usage statistics
     */
    public function getUsageStatistics(Coupon $coupon): array
    {
        $totalUsages = $coupon->usages()->count();
        $totalDiscount = $coupon->usages()->sum('discount_amount');
        $averageDiscount = $totalUsages > 0 ? $totalDiscount / $totalUsages : 0;
        
        // Get usage by date (last 30 days)
        $usageByDate = $coupon->usages()
            ->selectRaw('DATE(used_at) as date, COUNT(*) as count, SUM(discount_amount) as total_discount')
            ->where('used_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return [
            'total_usages' => $totalUsages,
            'total_discount' => $totalDiscount,
            'average_discount' => $averageDiscount,
            'usage_by_date' => $usageByDate
        ];
    }

    /**
     * Get comprehensive coupon reporting data
     */
    public function getReportingData($period = '30_days'): array
    {
        $startDate = match($period) {
            '7_days' => now()->subDays(7),
            '30_days' => now()->subDays(30),
            '90_days' => now()->subDays(90),
            '1_year' => now()->subYear(),
            default => now()->subDays(30)
        };

        // Get all coupons with usage data
        $coupons = Coupon::withCount([
            'usages as total_usages',
            'usages as period_usages' => function($query) use ($startDate) {
                $query->where('used_at', '>=', $startDate);
            }
        ])->withSum([
            'usages as total_discount' => 'discount_amount',
            'usages as period_discount' => function($query) use ($startDate) {
                $query->where('used_at', '>=', $startDate);
            }
        ], 'discount_amount')->get();

        // Calculate overall statistics
        $totalCoupons = $coupons->count();
        $activeCoupons = $coupons->where('is_active', true)->count();
        $periodUsageCount = $coupons->sum('period_usages');
        $periodDiscountTotal = $coupons->sum('period_discount');

        // Get top coupons by usage
        $topCouponsByUsage = $coupons->sortByDesc('period_usages')->take(10);

        // Get top coupons by discount value
        $topCouponsByDiscount = $coupons->sortByDesc('period_discount')->take(10);

        // Get usage by coupon type
        $usageByType = $coupons->groupBy('type')->map(function($group) {
            return [
                'count' => $group->count(),
                'usages' => $group->sum('period_usages'),
                'discount' => $group->sum('period_discount')
            ];
        });

        return [
            'overview' => [
                'total_coupons' => $totalCoupons,
                'active_coupons' => $activeCoupons,
                'period_usage_count' => $periodUsageCount,
                'period_discount_total' => $periodDiscountTotal,
                'average_discount_per_usage' => $periodUsageCount > 0 ? $periodDiscountTotal / $periodUsageCount : 0
            ],
            'top_coupons_by_usage' => $topCouponsByUsage,
            'top_coupons_by_discount' => $topCouponsByDiscount,
            'usage_by_type' => $usageByType,
            'period' => $period
        ];
    }

    /**
     * Get brand effect analysis for coupons
     */
    public function getBrandEffectAnalysis(): array
    {
        // This would require joining with order items and products to get brand information
        // For now, returning a placeholder structure
        return [
            'brands' => [],
            'brand_coupon_performance' => [],
            'top_performing_brands' => []
        ];
    }
}