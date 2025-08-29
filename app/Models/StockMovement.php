<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_variant_id','qty_change','reason','ref_type','ref_id'
    ];

    protected $casts = [
        'qty_change' => 'decimal:3',
    ];

    public function variant() {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
