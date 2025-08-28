<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;

class ProductAttributesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Color attribute
        $colorAttr = ProductAttribute::create([
            'name' => 'Renk',
            'slug' => 'renk',
            'type' => 'color',
            'is_required' => false,
            'is_variation' => true,
            'sort_order' => 1,
            'is_active' => true
        ]);

        $colors = [
            ['value' => 'Kırmızı', 'slug' => 'kirmizi', 'color_code' => '#FF0000'],
            ['value' => 'Mavi', 'slug' => 'mavi', 'color_code' => '#0000FF'],
            ['value' => 'Yeşil', 'slug' => 'yesil', 'color_code' => '#008000'],
            ['value' => 'Siyah', 'slug' => 'siyah', 'color_code' => '#000000'],
            ['value' => 'Beyaz', 'slug' => 'beyaz', 'color_code' => '#FFFFFF'],
            ['value' => 'Sarı', 'slug' => 'sari', 'color_code' => '#FFFF00'],
            ['value' => 'Mor', 'slug' => 'mor', 'color_code' => '#800080'],
        ];

        foreach ($colors as $index => $color) {
            ProductAttributeValue::create([
                'product_attribute_id' => $colorAttr->id,
                'value' => $color['value'],
                'slug' => $color['slug'],
                'color_code' => $color['color_code'],
                'sort_order' => $index + 1,
                'is_active' => true
            ]);
        }

        // Size attribute
        $sizeAttr = ProductAttribute::create([
            'name' => 'Beden',
            'slug' => 'beden',
            'type' => 'text',
            'is_required' => false,
            'is_variation' => true,
            'sort_order' => 2,
            'is_active' => true
        ]);

        $sizes = [
            ['value' => 'XS', 'slug' => 'xs'],
            ['value' => 'S', 'slug' => 's'],
            ['value' => 'M', 'slug' => 'm'],
            ['value' => 'L', 'slug' => 'l'],
            ['value' => 'XL', 'slug' => 'xl'],
            ['value' => 'XXL', 'slug' => 'xxl'],
        ];

        foreach ($sizes as $index => $size) {
            ProductAttributeValue::create([
                'product_attribute_id' => $sizeAttr->id,
                'value' => $size['value'],
                'slug' => $size['slug'],
                'sort_order' => $index + 1,
                'is_active' => true
            ]);
        }

        // Material attribute
        $materialAttr = ProductAttribute::create([
            'name' => 'Malzeme',
            'slug' => 'malzeme',
            'type' => 'text',
            'is_required' => false,
            'is_variation' => true,
            'sort_order' => 3,
            'is_active' => true
        ]);

        $materials = [
            ['value' => 'Pamuk', 'slug' => 'pamuk'],
            ['value' => 'Polyester', 'slug' => 'polyester'],
            ['value' => 'Deri', 'slug' => 'deri'],
            ['value' => 'Yün', 'slug' => 'yun'],
        ];

        foreach ($materials as $index => $material) {
            ProductAttributeValue::create([
                'product_attribute_id' => $materialAttr->id,
                'value' => $material['value'],
                'slug' => $material['slug'],
                'sort_order' => $index + 1,
                'is_active' => true
            ]);
        }
    }
}
