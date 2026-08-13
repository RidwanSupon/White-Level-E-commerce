<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::where('slug', 'electronics-gadgets')->first();
        $smartphones = Category::where('slug', 'smartphones-accessories')->first();
        $audio = Category::where('slug', 'audio-headphones')->first();
        $fashion = Category::where('slug', 'fashion-apparel')->first();
        
        $apple = Brand::where('slug', 'apple')->first();
        $sony = Brand::where('slug', 'sony')->first();
        $nike = Brand::where('slug', 'nike')->first();

        // Attributes
        $colorAttr = Attribute::firstOrCreate(['code' => 'color'], ['name' => 'Color', 'type' => 'color']);
        $black = AttributeValue::firstOrCreate(['attribute_id' => $colorAttr->id, 'value' => 'Space Black'], ['color_code' => '#111827']);
        $silver = AttributeValue::firstOrCreate(['attribute_id' => $colorAttr->id, 'value' => 'Titanium Silver'], ['color_code' => '#9CA3AF']);

        $storageAttr = Attribute::firstOrCreate(['code' => 'storage'], ['name' => 'Storage', 'type' => 'button']);
        $storage256 = AttributeValue::firstOrCreate(['attribute_id' => $storageAttr->id, 'value' => '256GB']);
        $storage512 = AttributeValue::firstOrCreate(['attribute_id' => $storageAttr->id, 'value' => '512GB']);

        // 1. Product: iPhone 16 Pro Max
        $iphone = Product::create([
            'category_id' => $smartphones?->id ?? $electronics->id,
            'brand_id' => $apple?->id,
            'name' => 'iPhone 16 Pro Max 256GB',
            'slug' => 'iphone-16-pro-max-256gb',
            'sku' => 'IPH16PM-256',
            'price' => 165000.00,
            'compare_price' => 175000.00,
            'cost_price' => 140000.00,
            'short_description' => 'Titanium design with A18 Pro Chip, 48MP Fusion Camera, and Camera Control.',
            'description' => 'iPhone 16 Pro Max forged in Grade 5 Titanium with a refined micro-blasted finish. Super Retina XDR display with ProMotion up to 120Hz.',
            'featured_image' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?auto=format&fit=crop&w=800&q=80',
            'is_active' => true,
            'is_featured' => true,
            'stock_quantity' => 15,
            'low_stock_threshold' => 3,
            'rating_cache' => 4.90,
            'reviews_count' => 28,
        ]);

        $iphone->images()->createMany([
            ['image_path' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?auto=format&fit=crop&w=800&q=80', 'sort_order' => 1, 'is_primary' => true],
            ['image_path' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80', 'sort_order' => 2, 'is_primary' => false],
        ]);

        $var1 = ProductVariant::create([
            'product_id' => $iphone->id,
            'sku' => 'IPH16PM-256-BLK',
            'price' => 165000.00,
            'compare_price' => 175000.00,
            'stock_quantity' => 10,
            'image' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?auto=format&fit=crop&w=800&q=80',
        ]);
        $var1->attributeValues()->sync([$black->id, $storage256->id]);

        $var2 = ProductVariant::create([
            'product_id' => $iphone->id,
            'sku' => 'IPH16PM-512-SLV',
            'price' => 185000.00,
            'compare_price' => 195000.00,
            'stock_quantity' => 5,
            'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80',
        ]);
        $var2->attributeValues()->sync([$silver->id, $storage512->id]);

        // 2. Product: Sony WH-1000XM5 ANC Headphones
        $sonyHeadphones = Product::create([
            'category_id' => $audio?->id ?? $electronics->id,
            'brand_id' => $sony?->id,
            'name' => 'Sony WH-1000XM5 Wireless Noise Canceling Headphones',
            'slug' => 'sony-wh-1000xm5-wireless-headphones',
            'sku' => 'SONY-WHXM5-BLK',
            'price' => 42000.00,
            'compare_price' => 46000.00,
            'cost_price' => 32000.00,
            'short_description' => 'Industry-leading noise canceling with two processors and 8 microphones.',
            'description' => 'The WH-1000XM5 headphones rewrite the rules for distraction-free listening. Auto NC Optimizer automatically optimizes noise canceling based on your wearing conditions.',
            'featured_image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
            'is_active' => true,
            'is_featured' => true,
            'stock_quantity' => 25,
            'low_stock_threshold' => 5,
            'rating_cache' => 4.85,
            'reviews_count' => 42,
        ]);

        $sonyHeadphones->images()->create([
            'image_path' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        // 3. Product: Nike Air Max 270 React
        $nikeShoes = Product::create([
            'category_id' => $fashion->id,
            'brand_id' => $nike?->id,
            'name' => 'Nike Air Max 270 React Streetwear',
            'slug' => 'nike-air-max-270-react',
            'sku' => 'NIKE-AM270-BLK',
            'price' => 185000.00 / 10,
            'compare_price' => 21000.00,
            'cost_price' => 12000.00,
            'short_description' => 'Lightweight mesh upper with iconic Max Air unit for continuous cushion comfort.',
            'description' => 'The Nike Air Max 270 React features modern art-inspired colorways and ultra-lightweight React foam cushioning.',
            'featured_image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
            'is_active' => true,
            'is_featured' => true,
            'stock_quantity' => 30,
            'low_stock_threshold' => 5,
            'rating_cache' => 4.75,
            'reviews_count' => 19,
        ]);
    }
}
