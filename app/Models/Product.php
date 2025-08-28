<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name','slug','description','category_id','brand_id','is_active'];

    public function variants() {
        return $this->hasMany(ProductVariant::class);
    }
}
