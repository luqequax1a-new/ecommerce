<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ShippingSettings;
use Illuminate\Support\Facades\DB;

class ShippingSettingsSeeder extends Seeder
{
    /**
     * Seed default shipping settings for Turkish e-commerce
     */
    public function run(): void
    {
        // Clear existing settings
        ShippingSettings::truncate();
        
        // Create default shipping configuration
        ShippingSettings::create([
            'free_enabled' => true,
            'free_threshold' => 300.00, // ₺300 free shipping threshold
            'flat_rate_enabled' => true,
            'flat_rate_fee' => 15.00, // ₺15 flat shipping rate
            'cod_enabled' => true,
            'cod_extra_fee' => 5.00, // ₺5 extra fee for cash on delivery
            'currency' => 'TRY',
            'free_shipping_message' => 'Kargo ücretsiz için ₺{remaining} daha alışveriş yapın.',
            'shipping_description' => 'Standart kargo - 1-3 iş günü içinde teslimat',
            'is_active' => true,
            'metadata' => [
                'created_by' => 'seeder',
                'version' => '1.0',
                'last_updated' => now()->toISOString(),
                'notes' => 'Default Turkish e-commerce shipping configuration'
            ]
        ]);
        
        $this->command->info('✅ Default shipping settings created successfully');
        $this->command->info('   - Free shipping threshold: ₺300.00');
        $this->command->info('   - Flat rate fee: ₺15.00');
        $this->command->info('   - COD extra fee: ₺5.00');
        $this->command->info('   - All features enabled');
    }
}
