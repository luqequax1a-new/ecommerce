<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitsSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            // adet: tam sayı, step=1
            [
                'code' => 'adet',
                'display_name' => 'Adet',
                'is_decimal' => false,
                'decimal_places' => 0,
                'min_qty' => 1,
                'max_qty' => null,
                'qty_step' => 1,
                'multiples_of' => null,
                'allow_free_input' => true,
            ],
            // kg: ondalık, 3 basamak, step=0.100
            [
                'code' => 'kg',
                'display_name' => 'Kilogram',
                'is_decimal' => true,
                'decimal_places' => 3,
                'min_qty' => 0.100,
                'max_qty' => null,
                'qty_step' => 0.100,
                'multiples_of' => null,
                'allow_free_input' => true,
            ],
            // metre: ondalık, 2 basamak, step=0.10
            [
                'code' => 'metre',
                'display_name' => 'Metre',
                'is_decimal' => true,
                'decimal_places' => 2,
                'min_qty' => 0.10,
                'max_qty' => null,
                'qty_step' => 0.10,
                'multiples_of' => null,
                'allow_free_input' => true,
            ],
            // litre: ondalık, 2 basamak, step=0.10
            [
                'code' => 'litre',
                'display_name' => 'Litre',
                'is_decimal' => true,
                'decimal_places' => 2,
                'min_qty' => 0.10,
                'max_qty' => null,
                'qty_step' => 0.10,
                'multiples_of' => null,
                'allow_free_input' => true,
            ],
            // paket: tam sayı, step=1
            [
                'code' => 'paket',
                'display_name' => 'Paket',
                'is_decimal' => false,
                'decimal_places' => 0,
                'min_qty' => 1,
                'max_qty' => null,
                'qty_step' => 1,
                'multiples_of' => null,
                'allow_free_input' => true,
            ],
            // koli: tam sayı, 6'nın katları
            [
                'code' => 'koli',
                'display_name' => 'Koli',
                'is_decimal' => false,
                'decimal_places' => 0,
                'min_qty' => 1,
                'max_qty' => null,
                'qty_step' => 1,
                'multiples_of' => 6,
                'allow_free_input' => false,
            ],
        ];

        foreach ($units as $u) {
            Unit::updateOrCreate(['code' => $u['code']], $u);
        }
    }
}
