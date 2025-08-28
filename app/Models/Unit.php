<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'code','display_name','is_decimal','decimal_places',
        'min_qty','max_qty','qty_step','multiples_of','allow_free_input'
    ];

    public function productVariants() {
        return $this->hasMany(ProductVariant::class);
    }
}
