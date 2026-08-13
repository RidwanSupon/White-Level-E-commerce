<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoryAndBrandSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $electronics = Category::create([
            'name' => 'Electronics & Gadgets',
            'slug' => 'electronics-gadgets',
            'description' => 'Latest flagship smartphones, laptops, audio systems and wearable tech.',
            'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'parent_id' => $electronics->id,
            'name' => 'Smartphones & Accessories',
            'slug' => 'smartphones-accessories',
            'description' => 'Next-gen mobile devices and wireless chargers.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'parent_id' => $electronics->id,
            'name' => 'Audio & Headphones',
            'slug' => 'audio-headphones',
            'description' => 'Noise canceling headphones and high-fidelity earbuds.',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $fashion = Category::create([
            'name' => 'Fashion & Apparel',
            'slug' => 'fashion-apparel',
            'description' => 'Curated designer streetwear, luxury timepieces, and accessories.',
            'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $home = Category::create([
            'name' => 'Home & Living',
            'slug' => 'home-living',
            'description' => 'Minimalist modern furniture, smart lighting, and espresso machines.',
            'image' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=600&q=80',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // 2. Brands
        $brands = [
            ['name' => 'Apple', 'slug' => 'apple', 'logo' => 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?auto=format&fit=crop&w=200&q=80'],
            ['name' => 'Sony', 'slug' => 'sony', 'logo' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=200&q=80'],
            ['name' => 'Samsung', 'slug' => 'samsung', 'logo' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?auto=format&fit=crop&w=200&q=80'],
            ['name' => 'Nike', 'slug' => 'nike', 'logo' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=200&q=80'],
        ];

        foreach ($brands as $brand) {
            Brand::create(array_merge($brand, ['is_active' => true]));
        }
    }
}
