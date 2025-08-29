<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'rule_type',
        'rule_data'
    ];

    protected $casts = [
        'rule_data' => 'array'
    ];

    /**
     * Get the coupon that owns the rule.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Check if the rule applies to a product
     */
    public function appliesToProduct(Product $product): bool
    {
        switch ($this->rule_type) {
            case 'general':
                return true;
                
            case 'brand':
                $brandIds = $this->rule_data['brand_ids'] ?? [];
                return in_array($product->brand_id, $brandIds);
                
            case 'category':
                $categoryIds = $this->rule_data['category_ids'] ?? [];
                return in_array($product->category_id, $categoryIds);
                
            case 'product':
                $productIds = $this->rule_data['product_ids'] ?? [];
                return in_array($product->id, $productIds);
                
            default:
                return false;
        }
    }

    /**
     * Check if the rule applies to a customer
     */
    public function appliesToCustomer($user): bool
    {
        switch ($this->rule_type) {
            case 'general':
                return true;
                
            case 'customer_group':
                $groupIds = $this->rule_data['group_ids'] ?? [];
                // Assuming user has groups relationship
                return $user && $user->groups()->whereIn('id', $groupIds)->exists();
                
            case 'customer':
                $customerIds = $this->rule_data['customer_ids'] ?? [];
                return $user && in_array($user->id, $customerIds);
                
            default:
                return false;
        }
    }
}