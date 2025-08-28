<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Carrier;
use App\Models\ShippingZone;
use App\Models\ShippingMethod;
use App\Models\TaxClass;
use Illuminate\Support\Facades\DB;

class ShippingSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedTaxClasses();
            $this->seedCarriers();
            $this->seedShippingZones();
            $this->seedShippingMethods();
        });
    }

    /**
     * Seed tax classes
     */
    private function seedTaxClasses(): void
    {
        $taxClasses = [
            [
                'name' => 'Standard Tax',
                'code' => 'standard-tax',
                'default_rate' => 0.2000,
                'description' => 'Standard 20% VAT rate for most products'
            ],
            [
                'name' => 'Reduced Tax',
                'code' => 'reduced-tax',
                'default_rate' => 0.0800,
                'description' => 'Reduced 8% VAT rate for specific product categories'
            ],
            [
                'name' => 'Zero Tax',
                'code' => 'zero-tax',
                'default_rate' => 0.0000,
                'description' => 'Zero tax for exempt products'
            ]
        ];

        foreach ($taxClasses as $taxClass) {
            TaxClass::firstOrCreate(
                ['code' => $taxClass['code']],
                $taxClass
            );
        }
    }

    /**
     * Seed Turkish carriers
     */
    private function seedCarriers(): void
    {
        $carriers = [
            [
                'name' => 'Aras Kargo',
                'slug' => 'aras-kargo',
                'code' => 'ARAS',
                'description' => 'Türkiye\'nin önde gelen kargo şirketlerinden biri',
                'logo_path' => 'carriers/aras-logo.png',
                'website_url' => 'https://www.araskargo.com.tr',
                'contact_phone' => '444 25 52',
                'tracking_url_template' => 'https://www.araskargo.com.tr/tools/track/{tracking_number}',
                'supports_cod' => true,
                'supports_return' => true,
                'supports_international' => false,
                'estimated_delivery_time' => '1-3 iş günü',
                'max_weight' => 30.000,
                'max_dimensions_length' => 120.00,
                'max_dimensions_width' => 60.00,
                'max_dimensions_height' => 60.00,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'MNG Kargo',
                'slug' => 'mng-kargo',
                'code' => 'MNG',
                'description' => 'Hızlı ve güvenilir kargo hizmeti',
                'logo_path' => 'carriers/mng-logo.png',
                'website_url' => 'https://www.mngkargo.com.tr',
                'contact_phone' => '444 06 06',
                'tracking_url_template' => 'https://www.mngkargo.com.tr/track/{tracking_number}',
                'supports_cod' => true,
                'supports_return' => true,
                'supports_international' => false,
                'estimated_delivery_time' => '1-3 iş günü',
                'max_weight' => 30.000,
                'max_dimensions_length' => 100.00,
                'max_dimensions_width' => 50.00,
                'max_dimensions_height' => 50.00,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Yurtiçi Kargo',
                'slug' => 'yurtici-kargo',
                'code' => 'YURTICI',
                'description' => 'Türkiye genelinde kargo hizmeti',
                'logo_path' => 'carriers/yurtici-logo.png',
                'website_url' => 'https://www.yurticikargo.com',
                'contact_phone' => '444 99 99',
                'tracking_url_template' => 'https://www.yurticikargo.com/tr/tracking/{tracking_number}',
                'supports_cod' => true,
                'supports_return' => true,
                'supports_international' => false,
                'estimated_delivery_time' => '1-4 iş günü',
                'max_weight' => 30.000,
                'max_dimensions_length' => 120.00,
                'max_dimensions_width' => 60.00,
                'max_dimensions_height' => 60.00,
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'PTT Kargo',
                'slug' => 'ptt-kargo',
                'code' => 'PTT',
                'description' => 'Devlet güvencesiyle kargo hizmeti',
                'logo_path' => 'carriers/ptt-logo.png',
                'website_url' => 'https://www.ptt.gov.tr',
                'contact_phone' => '444 17 89',
                'tracking_url_template' => 'https://gonderitakip.ptt.gov.tr/{tracking_number}',
                'supports_cod' => true,
                'supports_return' => true,
                'supports_international' => true,
                'estimated_delivery_time' => '2-5 iş günü',
                'max_weight' => 30.000,
                'max_dimensions_length' => 120.00,
                'max_dimensions_width' => 60.00,
                'max_dimensions_height' => 60.00,
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'UPS',
                'slug' => 'ups',
                'code' => 'UPS',
                'description' => 'International express shipping services',
                'logo_path' => 'carriers/ups-logo.png',
                'website_url' => 'https://www.ups.com/tr',
                'contact_phone' => '+90 212 444 0 877',
                'tracking_url_template' => 'https://www.ups.com/track?tracknum={tracking_number}',
                'supports_cod' => false,
                'supports_return' => true,
                'supports_international' => true,
                'estimated_delivery_time' => '3-7 iş günü',
                'max_weight' => 70.000,
                'max_dimensions_length' => 330.00,
                'max_dimensions_width' => 270.00,
                'max_dimensions_height' => 190.00,
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'DHL',
                'slug' => 'dhl',
                'code' => 'DHL',
                'description' => 'Global express and logistics services',
                'logo_path' => 'carriers/dhl-logo.png',
                'website_url' => 'https://www.dhl.com.tr',
                'contact_phone' => '+90 212 444 0 345',
                'tracking_url_template' => 'https://www.dhl.com/tr-tr/home/tracking/tracking-express.html?submit=1&tracking-id={tracking_number}',
                'supports_cod' => false,
                'supports_return' => true,
                'supports_international' => true,
                'estimated_delivery_time' => '2-5 iş günü',
                'max_weight' => 70.000,
                'max_dimensions_length' => 175.00,
                'max_dimensions_width' => 120.00,
                'max_dimensions_height' => 80.00,
                'is_active' => true,
                'sort_order' => 6
            ]
        ];

        foreach ($carriers as $carrierData) {
            Carrier::firstOrCreate(
                ['code' => $carrierData['code']],
                $carrierData
            );
        }
    }

    /**
     * Seed shipping zones
     */
    private function seedShippingZones(): void
    {
        $zones = [
            [
                'name' => 'Türkiye - İstanbul',
                'slug' => 'turkiye-istanbul',
                'code' => 'TR-IST',
                'type' => 'city',
                'description' => 'İstanbul ili tüm ilçeler',
                'countries' => json_encode(['TR']),
                'regions' => json_encode(['İstanbul']),
                'cities' => json_encode([
                    'Adalar', 'Arnavutköy', 'Ataşehir', 'Avcılar', 'Bağcılar', 'Bahçelievler',
                    'Bakırköy', 'Başakşehir', 'Bayrampaşa', 'Beşiktaş', 'Beykoz', 'Beylikdüzü',
                    'Beyoğlu', 'Büyükçekmece', 'Çatalca', 'Çekmeköy', 'Esenler', 'Esenyurt',
                    'Eyüpsultan', 'Fatih', 'Gaziosmanpaşa', 'Güngören', 'Kadıköy', 'Kağıthane',
                    'Kartal', 'Küçükçekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sarıyer',
                    'Silivri', 'Sultangazi', 'Sultanbeyli', 'Şile', 'Şişli', 'Tuzla', 'Ümraniye',
                    'Üsküdar', 'Zeytinburnu'
                ]),
                'postal_codes' => null,
                'postal_code_ranges' => json_encode([
                    ['min' => '34000', 'max' => '34999']
                ]),
                'default_tax_rate' => 0.2000,
                'currency_code' => 'TRY',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Türkiye - Ankara',
                'slug' => 'turkiye-ankara',
                'code' => 'TR-ANK',
                'type' => 'city',
                'description' => 'Ankara ili tüm ilçeler',
                'countries' => json_encode(['TR']),
                'regions' => json_encode(['Ankara']),
                'cities' => json_encode([
                    'Akyurt', 'Altındağ', 'Ayaş', 'Bala', 'Beypazarı', 'Çamlıdere', 'Çankaya',
                    'Çubuk', 'Elmadağ', 'Etimesgut', 'Evren', 'Gölbaşı', 'Güdül', 'Haymana',
                    'Kahramankazan', 'Kalecik', 'Keçiören', 'Kızılcahamam', 'Mamak', 'Nallıhan',
                    'Polatlı', 'Pursaklar', 'Sincan', 'Şereflikoçhisar', 'Yenimahalle'
                ]),
                'postal_codes' => null,
                'postal_code_ranges' => json_encode([
                    ['min' => '06000', 'max' => '06999']
                ]),
                'default_tax_rate' => 0.2000,
                'currency_code' => 'TRY',
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Türkiye - İzmir',
                'slug' => 'turkiye-izmir',
                'code' => 'TR-IZM',
                'type' => 'city',
                'description' => 'İzmir ili tüm ilçeler',
                'countries' => json_encode(['TR']),
                'regions' => json_encode(['İzmir']),
                'cities' => json_encode([
                    'Aliağa', 'Balçova', 'Bayındır', 'Bayraklı', 'Bergama', 'Beydağ', 'Bornova',
                    'Buca', 'Çeşme', 'Çiğli', 'Dikili', 'Foça', 'Gaziemir', 'Güzelbahçe',
                    'Karabağlar', 'Karaburun', 'Karşıyaka', 'Kemalpaşa', 'Kınık', 'Kiraz',
                    'Konak', 'Menderes', 'Menemen', 'Narlıdere', 'Ödemiş', 'Seferihisar',
                    'Selçuk', 'Tire', 'Torbalı', 'Urla'
                ]),
                'postal_codes' => null,
                'postal_code_ranges' => json_encode([
                    ['min' => '35000', 'max' => '35999']
                ]),
                'default_tax_rate' => 0.2000,
                'currency_code' => 'TRY',
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Türkiye - Diğer İller',
                'slug' => 'turkiye-diger-iller',
                'code' => 'TR-OTHER',
                'type' => 'country',
                'description' => 'Türkiye geneli diğer tüm iller',
                'countries' => json_encode(['TR']),
                'regions' => null,
                'cities' => null,
                'postal_codes' => null,
                'postal_code_ranges' => json_encode([
                    ['min' => '01000', 'max' => '81999']
                ]),
                'default_tax_rate' => 0.2000,
                'currency_code' => 'TRY',
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Avrupa Birliği',
                'slug' => 'avrupa-birligi',
                'code' => 'EU',
                'type' => 'region',
                'description' => 'AB ülkeleri (27 ülke)',
                'countries' => json_encode([
                    'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
                    'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
                    'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
                ]),
                'regions' => null,
                'cities' => null,
                'postal_codes' => null,
                'postal_code_ranges' => null,
                'default_tax_rate' => 0.0000,
                'currency_code' => 'EUR',
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'Dünya Geneli',
                'slug' => 'dunya-geneli',
                'code' => 'WORLD',
                'type' => 'custom',
                'description' => 'Diğer tüm ülkeler',
                'countries' => null,
                'regions' => null,
                'cities' => null,
                'postal_codes' => null,
                'postal_code_ranges' => null,
                'default_tax_rate' => 0.0000,
                'currency_code' => 'USD',
                'is_active' => true,
                'sort_order' => 6
            ]
        ];

        foreach ($zones as $zoneData) {
            ShippingZone::firstOrCreate(
                ['slug' => $zoneData['slug']],
                $zoneData
            );
        }
    }

    /**
     * Seed shipping methods for each zone and carrier
     */
    private function seedShippingMethods(): void
    {
        $carriers = Carrier::all()->keyBy('code');
        $zones = ShippingZone::all()->keyBy('slug');
        $taxClass = TaxClass::where('code', 'standard-tax')->first();

        $shippingMethods = [
            // İstanbul Zone Methods
            [
                'zone_slug' => 'turkiye-istanbul',
                'carrier_code' => 'ARAS',
                'name' => 'Aras Kargo - İstanbul İçi',
                'code' => 'aras-istanbul',
                'calc_method' => 'flat',
                'base_fee' => 15.00,
                'step_fee' => 0,
                'step_size' => 1,
                'min_weight' => 0,
                'max_weight' => 30,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 300.00,
                'delivery_time' => '1-2 iş günü',
                'supports_cod' => true,
                'cod_fee' => 5.00,
                'require_signature' => false
            ],
            [
                'zone_slug' => 'turkiye-istanbul',
                'carrier_code' => 'MNG',
                'name' => 'MNG Kargo - İstanbul İçi',
                'code' => 'mng-istanbul',
                'calc_method' => 'flat',
                'base_fee' => 12.00,
                'step_fee' => 0,
                'step_size' => 1,
                'min_weight' => 0,
                'max_weight' => 30,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 250.00,
                'delivery_time' => '1-2 iş günü',
                'supports_cod' => true,
                'cod_fee' => 4.00,
                'require_signature' => false
            ],
            
            // Ankara Zone Methods
            [
                'zone_slug' => 'turkiye-ankara',
                'carrier_code' => 'ARAS',
                'name' => 'Aras Kargo - Ankara',
                'code' => 'aras-ankara',
                'calc_method' => 'by_weight',
                'base_fee' => 18.00,
                'step_fee' => 3.00,
                'step_size' => 1,
                'min_weight' => 0,
                'max_weight' => 30,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 400.00,
                'delivery_time' => '2-3 iş günü',
                'supports_cod' => true,
                'cod_fee' => 6.00,
                'require_signature' => false
            ],
            [
                'zone_slug' => 'turkiye-ankara',
                'carrier_code' => 'YURTICI',
                'name' => 'Yurtiçi Kargo - Ankara',
                'code' => 'yurtici-ankara',
                'calc_method' => 'by_price',
                'base_fee' => 20.00,
                'step_fee' => 0.02,
                'step_size' => 100,
                'min_weight' => 0,
                'max_weight' => 30,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 500.00,
                'delivery_time' => '2-3 iş günü',
                'supports_cod' => true,
                'cod_fee' => 7.00,
                'require_signature' => false
            ],
            
            // İzmir Zone Methods
            [
                'zone_slug' => 'turkiye-izmir',
                'carrier_code' => 'PTT',
                'name' => 'PTT Kargo - İzmir',
                'code' => 'ptt-izmir',
                'calc_method' => 'flat',
                'base_fee' => 16.00,
                'step_fee' => 0,
                'step_size' => 1,
                'min_weight' => 0,
                'max_weight' => 30,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 350.00,
                'delivery_time' => '2-4 iş günü',
                'supports_cod' => true,
                'cod_fee' => 5.50,
                'require_signature' => false
            ],
            
            // Türkiye Diğer İller Methods
            [
                'zone_slug' => 'turkiye-diger-iller',
                'carrier_code' => 'ARAS',
                'name' => 'Aras Kargo - Türkiye Geneli',
                'code' => 'aras-turkiye',
                'calc_method' => 'by_weight',
                'base_fee' => 25.00,
                'step_fee' => 4.00,
                'step_size' => 1,
                'min_weight' => 0,
                'max_weight' => 30,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 500.00,
                'delivery_time' => '3-5 iş günü',
                'supports_cod' => true,
                'cod_fee' => 8.00,
                'require_signature' => false
            ],
            [
                'zone_slug' => 'turkiye-diger-iller',
                'carrier_code' => 'MNG',
                'name' => 'MNG Kargo - Türkiye Geneli',
                'code' => 'mng-turkiye',
                'calc_method' => 'by_weight',
                'base_fee' => 22.00,
                'step_fee' => 3.50,
                'step_size' => 1,
                'min_weight' => 0,
                'max_weight' => 30,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 450.00,
                'delivery_time' => '3-5 iş günü',
                'supports_cod' => true,
                'cod_fee' => 7.50,
                'require_signature' => false
            ],
            
            // Avrupa Birliği Methods
            [
                'zone_slug' => 'avrupa-birligi',
                'carrier_code' => 'DHL',
                'name' => 'DHL Express - Avrupa Birliği',
                'code' => 'dhl-eu',
                'calc_method' => 'by_weight',
                'base_fee' => 80.00,
                'step_fee' => 15.00,
                'step_size' => 1,
                'min_weight' => 0,
                'max_weight' => 70,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 2000.00,
                'delivery_time' => '3-7 iş günü',
                'supports_cod' => false,
                'cod_fee' => 0,
                'require_signature' => true
            ],
            [
                'zone_slug' => 'avrupa-birligi',
                'carrier_code' => 'UPS',
                'name' => 'UPS Standard - Avrupa Birliği',
                'code' => 'ups-eu',
                'calc_method' => 'by_weight',
                'base_fee' => 75.00,
                'step_fee' => 12.00,
                'step_size' => 1,
                'min_weight' => 0,
                'max_weight' => 70,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 1500.00,
                'delivery_time' => '5-8 iş günü',
                'supports_cod' => false,
                'cod_fee' => 0,
                'require_signature' => true
            ],
            
            // Dünya Geneli Methods
            [
                'zone_slug' => 'dunya-geneli',
                'carrier_code' => 'DHL',
                'name' => 'DHL Express - Dünya Geneli',
                'code' => 'dhl-worldwide',
                'calc_method' => 'by_weight',
                'base_fee' => 120.00,
                'step_fee' => 25.00,
                'step_size' => 1,
                'min_weight' => 0,
                'max_weight' => 70,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 3000.00,
                'delivery_time' => '5-12 iş günü',
                'supports_cod' => false,
                'cod_fee' => 0,
                'require_signature' => true
            ],
            [
                'zone_slug' => 'dunya-geneli',
                'carrier_code' => 'UPS',
                'name' => 'UPS Worldwide Express',
                'code' => 'ups-worldwide',
                'calc_method' => 'by_weight',
                'base_fee' => 110.00,
                'step_fee' => 20.00,
                'step_size' => 1,
                'min_weight' => 0,
                'max_weight' => 70,
                'min_price' => 0,
                'max_price' => 0,
                'free_threshold' => 2500.00,
                'delivery_time' => '7-15 iş günü',
                'supports_cod' => false,
                'cod_fee' => 0,
                'require_signature' => true
            ]
        ];

        foreach ($shippingMethods as $methodData) {
            $zone = $zones[$methodData['zone_slug']] ?? null;
            $carrier = $carriers[$methodData['carrier_code']] ?? null;

            if (!$zone || !$carrier) {
                continue;
            }

            $data = array_merge($methodData, [
                'zone_id' => $zone->id,
                'carrier_id' => $carrier->id,
                'tax_class_id' => $taxClass?->id,
                'is_active' => true,
                'sort_order' => 1
            ]);

            // Remove helper fields
            unset($data['zone_slug'], $data['carrier_code']);

            ShippingMethod::firstOrCreate(
                ['code' => $methodData['code']],
                $data
            );
        }
    }
}
