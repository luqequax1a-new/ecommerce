<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main categories (parent_id = null)
        $electronics = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Latest electronic devices and gadgets including smartphones, laptops, and accessories.',
            'short_description' => 'Electronic devices and gadgets',
            'icon_class' => 'fas fa-laptop',
            'meta_title' => 'Electronics - Smartphones, Laptops & Gadgets',
            'meta_description' => 'Shop the latest electronics including smartphones, laptops, tablets, and electronic accessories.',
            'meta_keywords' => 'electronics, smartphone, laptop, tablet, gadgets, technology',
            'is_active' => true,
            'show_in_menu' => true,
            'featured' => true,
            'sort_order' => 1
        ]);

        $clothing = Category::create([
            'name' => 'Clothing & Fashion',
            'slug' => 'clothing-fashion',
            'description' => 'Trendy clothing and fashion accessories for men, women, and children.',
            'short_description' => 'Fashion and clothing',
            'icon_class' => 'fas fa-tshirt',
            'meta_title' => 'Clothing & Fashion - Trendy Apparel & Accessories',
            'meta_description' => 'Discover the latest fashion trends with our collection of clothing and accessories.',
            'meta_keywords' => 'clothing, fashion, apparel, accessories, trendy, style',
            'is_active' => true,
            'show_in_menu' => true,
            'featured' => true,
            'sort_order' => 2
        ]);

        $home = Category::create([
            'name' => 'Home & Garden',
            'slug' => 'home-garden',
            'description' => 'Everything for your home including furniture, decor, kitchen appliances, and garden tools.',
            'short_description' => 'Home and garden essentials',
            'icon_class' => 'fas fa-home',
            'meta_title' => 'Home & Garden - Furniture, Decor & Appliances',
            'meta_description' => 'Transform your home with our selection of furniture, decor, and garden essentials.',
            'meta_keywords' => 'home, garden, furniture, decor, appliances, kitchen',
            'is_active' => true,
            'show_in_menu' => true,
            'featured' => false,
            'sort_order' => 3
        ]);

        $sports = Category::create([
            'name' => 'Sports & Outdoors',
            'slug' => 'sports-outdoors',
            'description' => 'Sports equipment, outdoor gear, fitness accessories, and athletic apparel.',
            'short_description' => 'Sports and outdoor equipment',
            'icon_class' => 'fas fa-running',
            'meta_title' => 'Sports & Outdoors - Equipment & Athletic Gear',
            'meta_description' => 'Get active with our sports equipment, outdoor gear, and fitness accessories.',
            'meta_keywords' => 'sports, outdoors, fitness, equipment, athletic, gear',
            'is_active' => true,
            'show_in_menu' => true,
            'featured' => false,
            'sort_order' => 4
        ]);

        // Electronics subcategories
        Category::create([
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'description' => 'Latest smartphones from top brands with cutting-edge features.',
            'short_description' => 'Mobile phones and smartphones',
            'parent_id' => $electronics->id,
            'meta_title' => 'Smartphones - Latest Mobile Phones',
            'meta_description' => 'Shop the latest smartphones with advanced features and technology.',
            'meta_keywords' => 'smartphone, mobile phone, android, iphone, cell phone',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 1
        ]);

        Category::create([
            'name' => 'Laptops & Computers',
            'slug' => 'laptops-computers',
            'description' => 'High-performance laptops, desktops, and computer accessories.',
            'short_description' => 'Computers and laptops',
            'parent_id' => $electronics->id,
            'meta_title' => 'Laptops & Computers - High Performance PCs',
            'meta_description' => 'Find the perfect laptop or desktop computer for work, gaming, or personal use.',
            'meta_keywords' => 'laptop, computer, desktop, pc, gaming, workstation',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 2
        ]);

        Category::create([
            'name' => 'Audio & Headphones',
            'slug' => 'audio-headphones',
            'description' => 'Premium audio equipment including headphones, speakers, and sound systems.',
            'short_description' => 'Audio equipment and headphones',
            'parent_id' => $electronics->id,
            'meta_title' => 'Audio & Headphones - Premium Sound Equipment',
            'meta_description' => 'Experience superior sound quality with our audio equipment and headphones.',
            'meta_keywords' => 'audio, headphones, speakers, sound, music, wireless',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 3
        ]);

        // Clothing subcategories
        Category::create([
            'name' => 'Men\'s Clothing',
            'slug' => 'mens-clothing',
            'description' => 'Stylish clothing for men including shirts, pants, jackets, and accessories.',
            'short_description' => 'Men\'s fashion and apparel',
            'parent_id' => $clothing->id,
            'meta_title' => 'Men\'s Clothing - Stylish Apparel for Men',
            'meta_description' => 'Discover our collection of men\'s clothing including shirts, pants, and accessories.',
            'meta_keywords' => 'mens clothing, mens fashion, shirts, pants, jackets, men',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 1
        ]);

        Category::create([
            'name' => 'Women\'s Clothing',
            'slug' => 'womens-clothing',
            'description' => 'Trendy women\'s fashion including dresses, tops, bottoms, and accessories.',
            'short_description' => 'Women\'s fashion and apparel',
            'parent_id' => $clothing->id,
            'meta_title' => 'Women\'s Clothing - Trendy Fashion for Women',
            'meta_description' => 'Shop our stylish collection of women\'s clothing and fashion accessories.',
            'meta_keywords' => 'womens clothing, womens fashion, dresses, tops, women',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 2
        ]);

        Category::create([
            'name' => 'Shoes & Footwear',
            'slug' => 'shoes-footwear',
            'description' => 'Comfortable and stylish footwear for all occasions.',
            'short_description' => 'Shoes and footwear',
            'parent_id' => $clothing->id,
            'meta_title' => 'Shoes & Footwear - Comfortable & Stylish',
            'meta_description' => 'Find the perfect shoes for any occasion from our footwear collection.',
            'meta_keywords' => 'shoes, footwear, sneakers, boots, sandals, comfort',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 3
        ]);

        // Home subcategories
        Category::create([
            'name' => 'Furniture',
            'slug' => 'furniture',
            'description' => 'Quality furniture for every room in your home.',
            'short_description' => 'Home furniture',
            'parent_id' => $home->id,
            'meta_title' => 'Furniture - Quality Home Furnishings',
            'meta_description' => 'Transform your space with our quality furniture collection.',
            'meta_keywords' => 'furniture, home furnishing, chairs, tables, sofas',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 1
        ]);

        Category::create([
            'name' => 'Kitchen & Dining',
            'slug' => 'kitchen-dining',
            'description' => 'Kitchen appliances, cookware, and dining essentials.',
            'short_description' => 'Kitchen and dining essentials',
            'parent_id' => $home->id,
            'meta_title' => 'Kitchen & Dining - Appliances & Cookware',
            'meta_description' => 'Equip your kitchen with our selection of appliances and cookware.',
            'meta_keywords' => 'kitchen, dining, appliances, cookware, kitchenware',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 2
        ]);

        // Sports subcategories
        Category::create([
            'name' => 'Fitness Equipment',
            'slug' => 'fitness-equipment',
            'description' => 'Home fitness equipment and workout accessories.',
            'short_description' => 'Fitness and workout equipment',
            'parent_id' => $sports->id,
            'meta_title' => 'Fitness Equipment - Home Workout Gear',
            'meta_description' => 'Get fit at home with our selection of fitness equipment and accessories.',
            'meta_keywords' => 'fitness, equipment, workout, exercise, gym, training',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 1
        ]);

        Category::create([
            'name' => 'Outdoor Gear',
            'slug' => 'outdoor-gear',
            'description' => 'Camping, hiking, and outdoor adventure equipment.',
            'short_description' => 'Outdoor and camping gear',
            'parent_id' => $sports->id,
            'meta_title' => 'Outdoor Gear - Camping & Adventure Equipment',
            'meta_description' => 'Gear up for your next outdoor adventure with our camping and hiking equipment.',
            'meta_keywords' => 'outdoor, camping, hiking, adventure, gear, equipment',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => 2
        ]);
    }
}
