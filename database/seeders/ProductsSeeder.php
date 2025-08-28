<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        // Önce "adet" birimini bul
        $unit = Unit::where('code','adet')->first();

        // Ürün oluştur
        $product = Product::updateOrCreate(
            ['slug' => 'klasik-tisort'],
            [
                'name' => 'Klasik Tişört',
                'description' => 'Günlük kullanım için rahat pamuklu tişört',
                'is_active' => true,
            ]
        );

        // Varyantlar
        $variants = [
            [
                'sku' => 'TS-RED-S',
                'price' => 299.90,
                'stock_qty' => 12,
                'attributes' => ['color'=>'Kırmızı','size'=>'S'],
            ],
            [
                'sku' => 'TS-RED-M',
                'price' => 299.90,
                'stock_qty' => 7,
                'attributes' => ['color'=>'Kırmızı','size'=>'M'],
            ],
            [
                'sku' => 'TS-BLK-S',
                'price' => 299.90,
                'stock_qty' => 5,
                'attributes' => ['color'=>'Siyah','size'=>'S'],
            ],
        ];

        foreach ($variants as $v) {
            ProductVariant::updateOrCreate(
                ['sku' => $v['sku']],
                [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'price' => $v['price'],
                    'stock_qty' => $v['stock_qty'],
                    'attributes' => $v['attributes'],
                ]
            );
        }
    }
}
