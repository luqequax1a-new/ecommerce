<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\CouponRule;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample coupons
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Hoşgeldin İndirimi',
                'description' => 'Yeni müşteriler için %10 indirim',
                'type' => 'percentage',
                'value' => 10.00,
                'minimum_cart_amount' => 0,
                'usage_limit' => 1000,
                'usage_limit_per_user' => 1,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
                'is_active' => true,
                'priority' => 0,
                'is_combinable' => false,
                'rules' => [
                    ['type' => 'customer', 'data' => ['customer_ids' => []]] // First order only
                ]
            ],
            [
                'code' => 'SUMMER20',
                'name' => 'Yaz İndirimi',
                'description' => 'Yaz sezonu özel %20 indirim',
                'type' => 'percentage',
                'value' => 20.00,
                'minimum_cart_amount' => 100,
                'usage_limit' => 500,
                'usage_limit_per_user' => 2,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(2),
                'is_active' => true,
                'priority' => 1,
                'is_combinable' => false,
                'rules' => [
                    ['type' => 'general', 'data' => []]
                ]
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'Ücretsiz Kargo',
                'description' => '150 TL ve üzeri alışverişlerde ücretsiz kargo',
                'type' => 'free_shipping',
                'value' => null,
                'minimum_cart_amount' => 150,
                'usage_limit' => 200,
                'usage_limit_per_user' => 1,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(1),
                'is_active' => true,
                'priority' => 2,
                'is_combinable' => true,
                'rules' => [
                    ['type' => 'general', 'data' => []]
                ]
            ],
            [
                'code' => 'NEWYEAR50',
                'name' => 'Yeni Yıl İndirimi',
                'description' => 'Yeni yıl özel 50 TL indirim',
                'type' => 'fixed_amount',
                'value' => 50.00,
                'minimum_cart_amount' => 200,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(1),
                'is_active' => true,
                'priority' => 3,
                'is_combinable' => false,
                'rules' => [
                    ['type' => 'general', 'data' => []]
                ]
            ]
        ];

        foreach ($coupons as $couponData) {
            $rules = $couponData['rules'];
            unset($couponData['rules']);
            
            $coupon = Coupon::create($couponData);
            
            foreach ($rules as $rule) {
                CouponRule::create([
                    'coupon_id' => $coupon->id,
                    'rule_type' => $rule['type'],
                    'rule_data' => $rule['data']
                ]);
            }
        }
    }
}