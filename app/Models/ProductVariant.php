<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id','unit_id','sku','price','stock_qty',
        'main_image','extra_images','attributes'
    ];

    protected $casts = [
        'extra_images' => 'array',
        'attributes'   => 'array',
        'price'        => 'decimal:2',
        'stock_qty'    => 'decimal:3',
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function unit() {
        return $this->belongsTo(Unit::class);
    }

    public function stockMovements() {
        return $this->hasMany(StockMovement::class, 'product_variant_id');
    }
}
