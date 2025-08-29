<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Coupon;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CouponService $couponService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->couponService = new CouponService();
    }

    /** @test */
    public function it_can_validate_a_valid_coupon()
    {
        $coupon = Coupon::factory()->create([
            'code' => 'VALID10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'usage_limit' => 100,
            'used_count' => 5
        ]);

        $result = $this->couponService->validateCoupon('VALID10', null, 100);

        $this->assertTrue($result['valid']);
        $this->assertEquals($coupon->id, $result['coupon']->id);
    }

    /** @test */
    public function it_rejects_an_invalid_coupon_code()
    {
        $result = $this->couponService->validateCoupon('INVALID', null, 100);

        $this->assertFalse($result['valid']);
        $this->assertEquals('Geçersiz kupon kodu.', $result['message']);
    }

    /** @test */
    public function it_rejects_an_expired_coupon()
    {
        Coupon::factory()->create([
            'code' => 'EXPIRED',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'valid_from' => now()->subDays(10),
            'valid_until' => now()->subDay(),
            'usage_limit' => 100,
            'used_count' => 5
        ]);

        $result = $this->couponService->validateCoupon('EXPIRED', null, 100);

        $this->assertFalse($result['valid']);
    }

    /** @test */
    public function it_calculates_percentage_discount_correctly()
    {
        $coupon = new Coupon([
            'type' => 'percentage',
            'value' => 10
        ]);

        $discount = $this->couponService->calculateDiscount($coupon, 100);

        $this->assertEquals(10, $discount);
    }

    /** @test */
    public function it_calculates_fixed_amount_discount_correctly()
    {
        $coupon = new Coupon([
            'type' => 'fixed_amount',
            'value' => 20
        ]);

        $discount = $this->couponService->calculateDiscount($coupon, 100);

        $this->assertEquals(20, $discount);
    }

    /** @test */
    public function it_limits_fixed_amount_discount_to_cart_total()
    {
        $coupon = new Coupon([
            'type' => 'fixed_amount',
            'value' => 150
        ]);

        $discount = $this->couponService->calculateDiscount($coupon, 100);

        $this->assertEquals(100, $discount); // Should not exceed cart total
    }
}