<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'description' => 'Premium technology products and innovative design solutions.',
                'website_url' => 'https://www.apple.com',
                'email' => 'info@apple.com',
                'meta_title' => 'Apple Products - Innovation at Its Best',
                'meta_description' => 'Discover Apple\'s range of innovative products including iPhone, iPad, Mac, and accessories.',
                'meta_keywords' => 'apple, iphone, ipad, mac, technology, innovation',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Samsung',
                'slug' => 'samsung',
                'description' => 'Leading electronics manufacturer with cutting-edge mobile and home appliances.',
                'website_url' => 'https://www.samsung.com',
                'email' => 'contact@samsung.com',
                'meta_title' => 'Samsung Electronics - Mobile, TV & Home Appliances',
                'meta_description' => 'Shop Samsung\'s latest smartphones, TVs, home appliances and innovative technology solutions.',
                'meta_keywords' => 'samsung, galaxy, smartphone, tv, electronics, appliances',
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'description' => 'Just Do It. Leading sports brand for athletic footwear, apparel, and equipment.',
                'website_url' => 'https://www.nike.com',
                'email' => 'customerservice@nike.com',
                'meta_title' => 'Nike - Just Do It | Athletic Shoes & Sportswear',
                'meta_description' => 'Shop Nike\'s latest athletic shoes, sportswear, and equipment for all your fitness needs.',
                'meta_keywords' => 'nike, shoes, sportswear, athletics, fitness, running',
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'description' => 'Impossible is Nothing. Premium sports brand with innovative athletic gear.',
                'website_url' => 'https://www.adidas.com',
                'email' => 'support@adidas.com',
                'meta_title' => 'Adidas - Impossible is Nothing | Sports & Lifestyle',
                'meta_description' => 'Discover Adidas\'s collection of athletic shoes, clothing, and accessories for sports and lifestyle.',
                'meta_keywords' => 'adidas, sports, shoes, athletic, football, basketball',
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Sony',
                'slug' => 'sony',
                'description' => 'Entertainment and technology company with audio, gaming, and electronics.',
                'website_url' => 'https://www.sony.com',
                'email' => 'info@sony.com',
                'meta_title' => 'Sony - Audio, Gaming & Electronics',
                'meta_description' => 'Explore Sony\'s range of audio equipment, gaming consoles, cameras, and entertainment technology.',
                'meta_keywords' => 'sony, playstation, audio, gaming, electronics, camera',
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'LG',
                'slug' => 'lg',
                'description' => 'Life\'s Good. Innovative home appliances, mobile devices, and technology solutions.',
                'website_url' => 'https://www.lg.com',
                'email' => 'contact@lg.com',
                'meta_title' => 'LG - Life\'s Good | Home Appliances & Technology',
                'meta_description' => 'Shop LG\'s innovative home appliances, smartphones, TVs, and technology solutions.',
                'meta_keywords' => 'lg, appliances, smartphone, tv, technology, home',
                'is_active' => true,
                'sort_order' => 6
            ],
            [
                'name' => 'Generic Brand',
                'slug' => 'generic-brand',
                'description' => 'Quality products at affordable prices for everyday needs.',
                'website_url' => null,
                'email' => null,
                'meta_title' => 'Generic Brand - Quality & Affordable Products',
                'meta_description' => 'Discover our range of quality, affordable products for all your everyday needs.',
                'meta_keywords' => 'generic, affordable, quality, everyday, products',
                'is_active' => true,
                'sort_order' => 99
            ]
        ];

        foreach ($brands as $brandData) {
            Brand::create($brandData);
        }
    }
}
