<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\TaxRule;
use Illuminate\Support\Facades\DB;

class TurkishTaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->createTaxClasses();
            $this->createTaxRates();
            $this->createTaxRules();
        });
        
        $this->command->info('Turkish VAT system seeded successfully!');
    }
    
    /**
     * Create Turkish VAT tax classes
     */
    private function createTaxClasses()
    {
        $taxClasses = [
            [
                'name' => 'Standard VAT 20%',
                'code' => 'TR_VAT_20',
                'description' => 'Standard Turkish VAT rate of 20% - applies to most goods and services',
                'default_rate' => 0.2000,
                'is_active' => true
            ],
            [
                'name' => 'Reduced VAT 10%',
                'code' => 'TR_VAT_10',
                'description' => 'Reduced Turkish VAT rate of 10% - for specific categories like food, medicine',
                'default_rate' => 0.1000,
                'is_active' => true
            ],
            [
                'name' => 'Super Reduced VAT 1%',
                'code' => 'TR_VAT_1',
                'description' => 'Super reduced Turkish VAT rate of 1% - for essential goods and necessities',
                'default_rate' => 0.0100,
                'is_active' => true
            ],
            [
                'name' => 'VAT Exempt 0%',
                'code' => 'TR_VAT_0',
                'description' => 'VAT exempt - 0% rate for exempt goods and services',
                'default_rate' => 0.0000,
                'is_active' => true
            ],
            [
                'name' => 'No Tax',
                'code' => 'NO_TAX',
                'description' => 'No tax applied - for tax-free products or special circumstances',
                'default_rate' => 0.0000,
                'is_active' => true
            ]
        ];
        
        foreach ($taxClasses as $classData) {
            TaxClass::updateOrCreate(
                ['code' => $classData['code']],
                $classData
            );
        }
        
        $this->command->info('Tax classes created successfully.');
    }
    
    /**
     * Create Turkish VAT tax rates
     */
    private function createTaxRates()
    {
        $taxClasses = TaxClass::whereIn('code', [
            'TR_VAT_20', 'TR_VAT_10', 'TR_VAT_1', 'TR_VAT_0', 'NO_TAX'
        ])->get()->keyBy('code');
        
        $taxRates = [
            // Standard VAT 20%
            [
                'tax_class_id' => $taxClasses['TR_VAT_20']->id,
                'name' => 'Standard VAT 20%',
                'code' => 'TR_VAT_20_STANDARD',
                'rate' => 0.200000,
                'type' => 'percentage',
                'country_code' => 'TR',
                'priority' => 10,
                'effective_from' => '2023-01-01',
                'is_active' => true
            ],
            // Reduced VAT 10%
            [
                'tax_class_id' => $taxClasses['TR_VAT_10']->id,
                'name' => 'Reduced VAT 10%',
                'code' => 'TR_VAT_10_REDUCED',
                'rate' => 0.100000,
                'type' => 'percentage',
                'country_code' => 'TR',
                'priority' => 8,
                'effective_from' => '2023-01-01',
                'is_active' => true
            ],
            // Super Reduced VAT 1%
            [
                'tax_class_id' => $taxClasses['TR_VAT_1']->id,
                'name' => 'Super Reduced VAT 1%',
                'code' => 'TR_VAT_1_SUPER_REDUCED',
                'rate' => 0.010000,
                'type' => 'percentage',
                'country_code' => 'TR',
                'priority' => 6,
                'effective_from' => '2023-01-01',
                'is_active' => true
            ],
            // VAT Exempt 0%
            [
                'tax_class_id' => $taxClasses['TR_VAT_0']->id,
                'name' => 'VAT Exempt 0%',
                'code' => 'TR_VAT_0_EXEMPT',
                'rate' => 0.000000,
                'type' => 'percentage',
                'country_code' => 'TR',
                'priority' => 4,
                'effective_from' => '2023-01-01',
                'is_active' => true
            ],
            // No Tax
            [
                'tax_class_id' => $taxClasses['NO_TAX']->id,
                'name' => 'No Tax Applied',
                'code' => 'NO_TAX_RATE',
                'rate' => 0.000000,
                'type' => 'percentage',
                'country_code' => 'TR',
                'priority' => 1,
                'effective_from' => '2023-01-01',
                'is_active' => true
            ]
        ];
        
        foreach ($taxRates as $rateData) {
            TaxRate::updateOrCreate(
                ['code' => $rateData['code']],
                $rateData
            );
        }
        
        $this->command->info('Tax rates created successfully.');
    }
    
    /**
     * Create Turkish VAT tax rules
     */
    private function createTaxRules()
    {
        $taxRates = TaxRate::whereIn('code', [
            'TR_VAT_20_STANDARD', 'TR_VAT_10_REDUCED', 'TR_VAT_1_SUPER_REDUCED', 
            'TR_VAT_0_EXEMPT', 'NO_TAX_RATE'
        ])->get()->keyBy('code');
        
        $taxRules = [
            // Export rules (0% VAT) - Highest priority
            [
                'tax_rate_id' => $taxRates['TR_VAT_0_EXEMPT']->id,
                'entity_type' => 'product',
                'country_code' => 'TR',
                'priority' => 100,
                'stop_processing' => true,
                'description' => 'Export VAT exemption for international sales',
                'conditions' => [
                    'is_export' => true,
                    'shipping_country' => 'international'
                ],
                'is_active' => true
            ],
            
            // Super reduced VAT for essentials - Very high priority
            [
                'tax_rate_id' => $taxRates['TR_VAT_1_SUPER_REDUCED']->id,
                'entity_type' => 'category',
                'country_code' => 'TR',
                'priority' => 90,
                'stop_processing' => true,
                'description' => 'Super reduced VAT 1% for essential goods',
                'conditions' => [
                    'category_type' => ['essentials', 'baby_products', 'medical_equipment']
                ],
                'is_active' => true
            ],
            
            // Category-specific rules for reduced VAT - High priority
            [
                'tax_rate_id' => $taxRates['TR_VAT_10_REDUCED']->id,
                'entity_type' => 'category',
                'country_code' => 'TR',
                'priority' => 80,
                'stop_processing' => true,
                'description' => 'Reduced VAT 10% for food and medicine categories',
                'conditions' => [
                    'category_type' => ['food', 'medicine', 'books']
                ],
                'is_active' => true
            ],
            
            // Product rules - Standard VAT 20% - Normal priority
            [
                'tax_rate_id' => $taxRates['TR_VAT_20_STANDARD']->id,
                'entity_type' => 'product',
                'country_code' => 'TR',
                'customer_type' => 'individual',
                'priority' => 50,
                'stop_processing' => true,
                'description' => 'Standard VAT 20% for products sold to individual customers in Turkey',
                'is_active' => true
            ],
            [
                'tax_rate_id' => $taxRates['TR_VAT_20_STANDARD']->id,
                'entity_type' => 'product',
                'country_code' => 'TR',
                'customer_type' => 'company',
                'priority' => 50,
                'stop_processing' => true,
                'description' => 'Standard VAT 20% for products sold to companies in Turkey',
                'is_active' => true
            ],
            
            // Shipping rules - Lower priority
            [
                'tax_rate_id' => $taxRates['TR_VAT_20_STANDARD']->id,
                'entity_type' => 'shipping',
                'country_code' => 'TR',
                'priority' => 30,
                'stop_processing' => true,
                'description' => 'Standard VAT 20% for shipping costs in Turkey',
                'is_active' => true
            ],
            
            // Payment fee rules - Lower priority
            [
                'tax_rate_id' => $taxRates['TR_VAT_20_STANDARD']->id,
                'entity_type' => 'payment',
                'country_code' => 'TR',
                'priority' => 20,
                'stop_processing' => true,
                'description' => 'Standard VAT 20% for payment processing fees',
                'is_active' => true
            ]
        ];
        
        foreach ($taxRules as $ruleData) {
            TaxRule::create($ruleData);
        }
        
        $this->command->info('Tax rules created successfully.');
    }
}
