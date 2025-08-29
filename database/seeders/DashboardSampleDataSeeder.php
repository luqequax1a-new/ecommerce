<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Cart;
use Carbon\Carbon;

class DashboardSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample orders for the last 30 days
        $statuses = ['pending', 'processing', 'shipped', 'delivered'];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $orderCount = rand(2, 8);
            
            for ($j = 0; $j < $orderCount; $j++) {
                $subtotal = rand(100, 1000);
                $shipping = rand(10, 50);
                $tax = rand(18, 180);
                
                Order::create([
                    'order_number' => 'ORD-' . $date->format('Ymd') . '-' . str_pad($j + 1, 3, '0', STR_PAD_LEFT),
                    'customer_id' => null,
                    'status' => $statuses[array_rand($statuses)],
                    'subtotal' => $subtotal,
                    'shipping_total' => $shipping,
                    'tax_total' => $tax,
                    'grand_total' => $subtotal + $shipping + $tax,
                    'currency' => 'TRY',
                    'created_at' => $date->copy()->addHours(rand(9, 20))->addMinutes(rand(0, 59)),
                    'updated_at' => $date->copy(),
                ]);
            }
        }
        
        // Create sample active carts
        for ($i = 0; $i < 15; $i++) {
            $subtotal = rand(50, 500);
            $tax = rand(9, 90);
            
            Cart::create([
                'session_id' => 'sess_' . uniqid(),
                'user_id' => null,
                'total_items' => rand(1, 5),
                'subtotal' => $subtotal,
                'tax_total' => $tax,
                'grand_total' => $subtotal + $tax,
                'currency' => 'TRY',
                'status' => 'active',
                'updated_at' => Carbon::now()->subMinutes(rand(5, 30)),
            ]);
        }
    }
}