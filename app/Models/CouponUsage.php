<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'discount_amount',
        'cart_amount',
        'applied_rules',
        'used_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'applied_rules' => 'array',
        'used_at' => 'datetime',
        'discount_amount' => 'decimal:2',
        'cart_amount' => 'decimal:2'
    ];

    /**
     * Get the coupon that was used.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the user who used the coupon.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with the coupon usage.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}