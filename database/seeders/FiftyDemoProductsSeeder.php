<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FiftyDemoProductsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $brands = Brand::all();
        $defaultTax = TaxRate::where('is_default', true)->first() ?? TaxRate::first();

        $catFashion = Category::where('slug', 'fashion-apparel')->first() ?? Category::first();
        $catElectronics = Category::where('slug', 'electronics-gadgets')->first() ?? Category::first();
        $catSmartphones = Category::where('slug', 'smartphones-accessories')->first() ?? $catElectronics;
        $catAudio = Category::where('slug', 'audio-headphones')->first() ?? $catElectronics;
        $catHome = Category::where('slug', 'home-living')->first() ?? Category::first();

        // Create or retrieve sub-categories for rich variety
        $catMensWear = Category::firstOrCreate(['slug' => 'mens-wear'], [
            'parent_id' => $catFashion->id,
            'name' => "Men's Clothing & Wear",
            'description' => "Premium T-Shirts, Shirts, Pants, and Streetwear for Men.",
            'is_active' => true,
        ]);

        $catWomensWear = Category::firstOrCreate(['slug' => 'womens-wear'], [
            'parent_id' => $catFashion->id,
            'name' => "Women's Dresses & Apparel",
            'description' => "Elegant Ladies Dresses, Kurtis, Sarees, and Western wear.",
            'is_active' => true,
        ]);

        $catFootwear = Category::firstOrCreate(['slug' => 'footwear-shoes'], [
            'parent_id' => $catFashion->id,
            'name' => "Footwear & Shoes",
            'description' => "Running sneakers, formal leather shoes, and heels.",
            'is_active' => true,
        ]);

        // Brands fallback
        $apple = Brand::where('slug', 'apple')->first();
        $nike = Brand::where('slug', 'nike')->first();
        $sony = Brand::where('slug', 'sony')->first();
        $samsung = Brand::where('slug', 'samsung')->first();

        // Retrieve attribute option values for variant seeding
        $colorAttr = Attribute::where('code', 'color')->first();
        $sizeAttr = Attribute::where('code', 'size')->first();
        $fabricAttr = Attribute::where('code', 'fabric')->first();
        $storageAttr = Attribute::where('code', 'storage')->first();

        $colors = $colorAttr ? $colorAttr->values->pluck('id', 'value')->toArray() : [];
        $sizes = $sizeAttr ? $sizeAttr->values->pluck('id', 'value')->toArray() : [];

        $demoProducts = [
            // -------------------------------------------------------------
            // MEN'S CLOTHING (1 - 10)
            // -------------------------------------------------------------
            [
                'name' => 'Classic Crewneck Heavyweight Cotton T-Shirt',
                'category_id' => $catMensWear->id, 'brand_id' => $nike?->id,
                'price' => 850.00, 'compare_price' => 1100.00, 'cost_price' => 450.00,
                'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=600&q=80',
                'short' => '100% Organic combed cotton t-shirt with premium ribbed collar.',
                'desc' => 'Crafted for daily comfort, this heavyweight crewneck features a 220 GSM combed cotton fabric that holds shape after repeated washes.',
                'is_featured' => true,
                'variants' => [
                    ['name' => 'Black / M', 'price' => 850, 'sku' => 'TS-BLK-M', 'stock' => 15, 'attrs' => ['Black', 'M']],
                    ['name' => 'White / L', 'price' => 850, 'sku' => 'TS-WHT-L', 'stock' => 20, 'attrs' => ['White', 'L']],
                    ['name' => 'Navy Blue / XL', 'price' => 850, 'sku' => 'TS-NVY-XL', 'stock' => 10, 'attrs' => ['Navy Blue', 'XL']],
                ]
            ],
            [
                'name' => 'Pique Cotton Slim-Fit Polo Shirt',
                'category_id' => $catMensWear->id, 'brand_id' => $nike?->id,
                'price' => 1450.00, 'compare_price' => 1800.00, 'cost_price' => 750.00,
                'image' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&w=600&q=80',
                'short' => 'Breathable pique knit polo shirt with two-button placket.',
                'desc' => 'Elevate your casual smart attire with our signature polo shirt. Features breathable cotton pique and subtle sleeve embroidery.',
                'is_featured' => true,
                'variants' => [
                    ['name' => 'Maroon / M', 'price' => 1450, 'sku' => 'POLO-MRN-M', 'stock' => 12, 'attrs' => ['Maroon', 'M']],
                    ['name' => 'Olive / L', 'price' => 1450, 'sku' => 'POLO-OLV-L', 'stock' => 18, 'attrs' => ['Olive', 'L']],
                ]
            ],
            [
                'name' => 'Oxford Button-Down Formal Shirt',
                'category_id' => $catMensWear->id, 'brand_id' => null,
                'price' => 1950.00, 'compare_price' => 2400.00, 'cost_price' => 1100.00,
                'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=600&q=80',
                'short' => 'Crisp Oxford weave 100% cotton executive shirt.',
                'desc' => 'Designed for long work days, offering breathability and a wrinkle-resistant finish that stays sharp from morning meetings to evening dinners.',
                'is_featured' => false,
            ],
            [
                'name' => 'Slim Tapered Stretch Denim Jeans',
                'category_id' => $catMensWear->id, 'brand_id' => null,
                'price' => 2650.00, 'compare_price' => 3200.00, 'cost_price' => 1400.00,
                'image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=600&q=80',
                'short' => 'Deep indigo stretch denim with 5-pocket classic design.',
                'desc' => 'Modern slim fit jeans crafted with premium elastane blend denim for flex and maximum range of movement.',
                'is_featured' => true,
            ],
            [
                'name' => 'Tailored Stretch Chino Pants',
                'category_id' => $catMensWear->id, 'brand_id' => null,
                'price' => 2200.00, 'compare_price' => 2700.00, 'cost_price' => 1150.00,
                'image' => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?auto=format&fit=crop&w=600&q=80',
                'short' => 'Versatile flat-front chino trousers in soft twill.',
                'desc' => 'Transition effortlessly between formal and casual with our stretch twill chinos featuring inner waistband grip.',
                'is_featured' => false,
            ],
            [
                'name' => 'Fleece Zip-Up Urban Hoodie',
                'category_id' => $catMensWear->id, 'brand_id' => $nike?->id,
                'price' => 2850.00, 'compare_price' => 3500.00, 'cost_price' => 1500.00,
                'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?auto=format&fit=crop&w=600&q=80',
                'short' => 'Heavyweight brushed fleece hoodie with double-lined hood.',
                'desc' => 'Features kangaroo pockets, YKK full zip, and deep ribbing on cuffs for heat retention in chilly weather.',
                'is_featured' => true,
            ],
            [
                'name' => 'Vintage Trucker Denim Jacket',
                'category_id' => $catMensWear->id, 'brand_id' => null,
                'price' => 3800.00, 'compare_price' => 4500.00, 'cost_price' => 2100.00,
                'image' => 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?auto=format&fit=crop&w=600&q=80',
                'short' => 'Rugged washed denim jacket with metal shank buttons.',
                'desc' => 'Classic americana style trucker jacket with buttoned chest pockets and adjustable waist tabs.',
                'is_featured' => false,
            ],
            [
                'name' => 'Breathable Athletic Performance Shorts',
                'category_id' => $catMensWear->id, 'brand_id' => $nike?->id,
                'price' => 1200.00, 'compare_price' => 1500.00, 'cost_price' => 600.00,
                'image' => 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?auto=format&fit=crop&w=600&q=80',
                'short' => 'Lightweight quick-dry workout shorts with zipper pocket.',
                'desc' => 'Designed with moisture-wicking technology and 4-way stretch fabric for intensive workouts.',
                'is_featured' => false,
            ],
            [
                'name' => 'Linen Blend Casual Summer Shirt',
                'category_id' => $catMensWear->id, 'brand_id' => null,
                'price' => 2100.00, 'compare_price' => 2600.00, 'cost_price' => 1050.00,
                'image' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=600&q=80',
                'short' => 'Cooling linen-cotton blend short sleeve shirt.',
                'desc' => 'Relaxed fit short-sleeve shirt crafted for tropical summer heat with natural breathable weave.',
                'is_featured' => false,
            ],
            [
                'name' => 'Quilted Puffer Winter Vest',
                'category_id' => $catMensWear->id, 'brand_id' => null,
                'price' => 3200.00, 'compare_price' => 4000.00, 'cost_price' => 1700.00,
                'image' => 'https://images.unsplash.com/photo-1544441893-675973e31985?auto=format&fit=crop&w=600&q=80',
                'short' => 'Insulated lightweight bodywarmer vest with fleece lining.',
                'desc' => 'Windproof shell padded with synthetic down insulation for warm chest protection without arm constriction.',
                'is_featured' => false,
            ],

            // -------------------------------------------------------------
            // WOMEN'S CLOTHING & DRESSES (11 - 20)
            // -------------------------------------------------------------
            [
                'name' => 'Floral Printed Georgette A-Line Midi Dress',
                'category_id' => $catWomensWear->id, 'brand_id' => null,
                'price' => 2850.00, 'compare_price' => 3500.00, 'cost_price' => 1400.00,
                'image' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?auto=format&fit=crop&w=600&q=80',
                'short' => 'Elegant botanical print midi dress with elasticated waist.',
                'desc' => 'Flowy georgette fabric lined with soft crepe, featuring flare sleeves and a feminine v-neck silhouette.',
                'is_featured' => true,
            ],
            [
                'name' => 'Embroidered Cotton Anarkali Kurti Set',
                'category_id' => $catWomensWear->id, 'brand_id' => null,
                'price' => 3450.00, 'compare_price' => 4200.00, 'cost_price' => 1800.00,
                'image' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80',
                'short' => 'Intricate thread embroidery Kurti with matching Dupatta.',
                'desc' => 'Traditional ethnic wear crafted from 100% fine cotton with detailed zari thread work on neck and sleeve borders.',
                'is_featured' => true,
            ],
            [
                'name' => 'Silk Blend Designer Party Wear Saree',
                'category_id' => $catWomensWear->id, 'brand_id' => null,
                'price' => 4800.00, 'compare_price' => 6000.00, 'cost_price' => 2500.00,
                'image' => 'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?auto=format&fit=crop&w=600&q=80',
                'short' => 'Rich festive saree with unstitched blouse piece included.',
                'desc' => 'Features metallic woven pallu details and soft drapery ideal for weddings and formal festivities.',
                'is_featured' => true,
            ],
            [
                'name' => 'Ribbed Knit High-Neck Bodycon Dress',
                'category_id' => $catWomensWear->id, 'brand_id' => null,
                'price' => 2200.00, 'compare_price' => 2800.00, 'cost_price' => 1100.00,
                'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=600&q=80',
                'short' => 'Stretch ribbed knit bodycon dress for sophisticated evening wear.',
                'desc' => 'Accentuates shape while providing warm stretch comfort with long sleeves and ankle length cut.',
                'is_featured' => false,
            ],
            [
                'name' => 'Chiffon Layered Maxi Evening Gown',
                'category_id' => $catWomensWear->id, 'brand_id' => null,
                'price' => 4200.00, 'compare_price' => 5200.00, 'cost_price' => 2200.00,
                'image' => 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=600&q=80',
                'short' => 'Floor length chiffon gown with pleated waistline.',
                'desc' => 'Graceful evening gown designed with cascading chiffon layers and a discrete back zip fastening.',
                'is_featured' => true,
            ],
            [
                'name' => 'High-Waisted Wide Leg Linen Pants',
                'category_id' => $catWomensWear->id, 'brand_id' => null,
                'price' => 2100.00, 'compare_price' => 2600.00, 'cost_price' => 1000.00,
                'image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=600&q=80',
                'short' => 'Breezy wide-leg trousers with waist tie detail.',
                'desc' => 'Offers effortless chic style for warm climate casual outings, complete with side slit pockets.',
                'is_featured' => false,
            ],
            [
                'name' => 'Oversized Knit Cardigan Sweater',
                'category_id' => $catWomensWear->id, 'brand_id' => null,
                'price' => 2500.00, 'compare_price' => 3100.00, 'cost_price' => 1250.00,
                'image' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?auto=format&fit=crop&w=600&q=80',
                'short' => 'Cozy chunky cable knit open front cardigan.',
                'desc' => 'Soft acrylic yarn blend cardigan with deep front pockets and relaxed dropped shoulders.',
                'is_featured' => false,
            ],
            [
                'name' => 'Classic Double-Breasted Tailored Blazer',
                'category_id' => $catWomensWear->id, 'brand_id' => null,
                'price' => 3600.00, 'compare_price' => 4500.00, 'cost_price' => 1900.00,
                'image' => 'https://images.unsplash.com/photo-1548624313-0396c75e4b1a?auto=format&fit=crop&w=600&q=80',
                'short' => 'Structured office suit jacket with horn effect buttons.',
                'desc' => 'Fully lined professional blazer tailored for sharp shoulder structure and clean front lines.',
                'is_featured' => false,
            ],
            [
                'name' => 'Casual Denim Pinafore Overall Dress',
                'category_id' => $catWomensWear->id, 'brand_id' => null,
                'price' => 2300.00, 'compare_price' => 2900.00, 'cost_price' => 1150.00,
                'image' => 'https://images.unsplash.com/photo-1576185055363-6d7c88000919?auto=format&fit=crop&w=600&q=80',
                'short' => 'Playful washed denim overall dress with adjustable straps.',
                'desc' => 'Features front pouch pocket and side button openings for easy layering over tees and shirts.',
                'is_featured' => false,
            ],
            [
                'name' => 'Satin Wrap V-Neck Party Blouse',
                'category_id' => $catWomensWear->id, 'brand_id' => null,
                'price' => 1850.00, 'compare_price' => 2300.00, 'cost_price' => 900.00,
                'image' => 'https://images.unsplash.com/photo-1604014237800-1c9102c219da?auto=format&fit=crop&w=600&q=80',
                'short' => 'Silky lustrous wrap blouse with bishop sleeves.',
                'desc' => 'Adds touch of glamour to evening dinners with tie waist fastening and shiny liquid satin finish.',
                'is_featured' => false,
            ],

            // -------------------------------------------------------------
            // ELECTRONICS & SMARTPHONES (21 - 30)
            // -------------------------------------------------------------
            [
                'name' => 'iPhone 16 Pro Max 256GB - Natural Titanium',
                'category_id' => $catSmartphones->id, 'brand_id' => $apple?->id,
                'price' => 165000.00, 'compare_price' => 175000.00, 'cost_price' => 145000.00,
                'image' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?auto=format&fit=crop&w=600&q=80',
                'short' => 'A18 Pro chip, 48MP Fusion Camera, Grade 5 Titanium body.',
                'desc' => 'Features 6.9-inch Super Retina XDR display with ProMotion, Camera Control button, and all-day battery life.',
                'is_featured' => true,
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra 5G 512GB',
                'category_id' => $catSmartphones->id, 'brand_id' => $samsung?->id,
                'price' => 152000.00, 'compare_price' => 162000.00, 'cost_price' => 132000.00,
                'image' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?auto=format&fit=crop&w=600&q=80',
                'short' => 'Galaxy AI, 200MP Quad Telephoto Camera, Built-in S-Pen.',
                'desc' => 'Unleash new creative possibilities with Live Translate, Circle to Search with Google, and Titanium frame.',
                'is_featured' => true,
            ],
            [
                'name' => 'Sony WH-1000XM5 Noise Canceling Headphones',
                'category_id' => $catAudio->id, 'brand_id' => $sony?->id,
                'price' => 38500.00, 'compare_price' => 42000.00, 'cost_price' => 32000.00,
                'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=600&q=80',
                'short' => 'Industry leading noise canceling with 8 microphones & Auto NC Optimizer.',
                'desc' => 'Magnificent sound engineered with new Integrated Processor V1, crystal clear hands-free calling, and 30hr battery.',
                'is_featured' => true,
            ],
            [
                'name' => 'Apple AirPods Pro (2nd Gen) USB-C',
                'category_id' => $catAudio->id, 'brand_id' => $apple?->id,
                'price' => 27500.00, 'compare_price' => 31000.00, 'cost_price' => 23000.00,
                'image' => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?auto=format&fit=crop&w=600&q=80',
                'short' => 'H2 chip, Adaptive Audio, Active Noise Cancellation & Transparency Mode.',
                'desc' => 'Personalized Spatial Audio with dynamic head tracking, MagSafe Charging Case with Speaker and Lanyard loop.',
                'is_featured' => true,
            ],
            [
                'name' => 'MacBook Pro 14-inch M3 Pro Chip 512GB',
                'category_id' => $catElectronics->id, 'brand_id' => $apple?->id,
                'price' => 225000.00, 'compare_price' => 240000.00, 'cost_price' => 195000.00,
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=600&q=80',
                'short' => 'Liquid Retina XDR Display, 18GB Unified Memory, Space Black.',
                'desc' => 'Mind-blowing performance with up to 18 hours battery life, hardware-accelerated ray tracing, and studio quality mics.',
                'is_featured' => true,
            ],
            [
                'name' => 'Apple Watch Series 9 GPS 45mm Aluminum',
                'category_id' => $catElectronics->id, 'brand_id' => $apple?->id,
                'price' => 46000.00, 'compare_price' => 51000.00, 'cost_price' => 39000.00,
                'image' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=600&q=80',
                'short' => 'S9 SiP chip, Double Tap gesture control, Brighter Always-On display.',
                'desc' => 'Advanced health sensors including ECG, Blood Oxygen monitoring, and precision finding for iPhone.',
                'is_featured' => false,
            ],
            [
                'name' => 'Anker MagGo 10,000mAh Magnetic Power Bank',
                'category_id' => $catSmartphones->id, 'brand_id' => null,
                'price' => 6500.00, 'compare_price' => 7800.00, 'cost_price' => 4500.00,
                'image' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?auto=format&fit=crop&w=600&q=80',
                'short' => 'Qi2 Certified 15W wireless MagSafe charging power bank with smart display.',
                'desc' => 'Snap-on magnetic charging for iPhone with foldable kickstand and USB-C 27W bidirectional fast charging.',
                'is_featured' => false,
            ],
            [
                'name' => 'JBL Charge 5 Portable Waterproof Bluetooth Speaker',
                'category_id' => $catAudio->id, 'brand_id' => null,
                'price' => 17500.00, 'compare_price' => 19500.00, 'cost_price' => 13500.00,
                'image' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=600&q=80',
                'short' => 'IP67 waterproof and dustproof speaker with built-in powerbank.',
                'desc' => 'Delivers bold JBL Original Pro Sound with long-excursion driver, separate tweeter and dual passive bass radiators.',
                'is_featured' => false,
            ],
            [
                'name' => 'Samsung 32-inch Odyssey G7 Gaming Monitor',
                'category_id' => $catElectronics->id, 'brand_id' => $samsung?->id,
                'price' => 68000.00, 'compare_price' => 75000.00, 'cost_price' => 58000.00,
                'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=600&q=80',
                'short' => '1000R curved 240Hz QHD gaming screen with 1ms response.',
                'desc' => 'QLED technology brings realistic color hues to life while Nvidia G-Sync eliminates screen tearing during esports gaming.',
                'is_featured' => false,
            ],
            [
                'name' => 'Logitech MX Master 3S Wireless Performance Mouse',
                'category_id' => $catElectronics->id, 'brand_id' => null,
                'price' => 12500.00, 'compare_price' => 14500.00, 'cost_price' => 9500.00,
                'image' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=600&q=80',
                'short' => '8000 DPI track-on-glass sensor with Quiet Clicks.',
                'desc' => 'Ergonomic precision mouse with MagSpeed electromagnetic scrolling wheel for developers and designers.',
                'is_featured' => false,
            ],

            // -------------------------------------------------------------
            // FOOTWEAR & SHOES (31 - 38)
            // -------------------------------------------------------------
            [
                'name' => 'Nike Air Zoom Pegasus 40 Running Shoes',
                'category_id' => $catFootwear->id, 'brand_id' => $nike?->id,
                'price' => 13500.00, 'compare_price' => 15500.00, 'cost_price' => 9500.00,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80',
                'short' => 'Springy ride for every run, familiar pegasus cushioning.',
                'desc' => 'Single-layer mesh upper creates an inviting fit with dual Zoom Air units for energetic toe-offs.',
                'is_featured' => true,
            ],
            [
                'name' => 'Handcrafted Full-Grain Leather Oxford Shoes',
                'category_id' => $catFootwear->id, 'brand_id' => null,
                'price' => 6800.00, 'compare_price' => 8200.00, 'cost_price' => 3800.00,
                'image' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=600&q=80',
                'short' => 'Italian cowhide formal dress shoes with Goodyear welted sole.',
                'desc' => 'Burnished toe oxford shoes stitched by master artisans with cushioned leather lining and durable heel pad.',
                'is_featured' => true,
            ],
            [
                'name' => 'Retro Suede Low-Top Streetwear Sneakers',
                'category_id' => $catFootwear->id, 'brand_id' => null,
                'price' => 4500.00, 'compare_price' => 5500.00, 'cost_price' => 2400.00,
                'image' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=600&q=80',
                'short' => 'Vintage gum sole sneakers with premium suede overlays.',
                'desc' => 'Classic 80s court style casual sneakers featuring breathable perforated toe box and rubber waffle outsole.',
                'is_featured' => false,
            ],
            [
                'name' => 'Slip-On Genuine Leather Penny Loafers',
                'category_id' => $catFootwear->id, 'brand_id' => null,
                'price' => 5400.00, 'compare_price' => 6500.00, 'cost_price' => 2900.00,
                'image' => 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=600&q=80',
                'short' => 'Supple calfskin slip-on loafers with memory foam footbed.',
                'desc' => 'Effortless slip-on design for smart casual weekends and business casual office attire.',
                'is_featured' => false,
            ],
            [
                'name' => 'High-Top Breathable Basketball Shoes',
                'category_id' => $catFootwear->id, 'brand_id' => $nike?->id,
                'price' => 11500.00, 'compare_price' => 13500.00, 'cost_price' => 7800.00,
                'image' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=600&q=80',
                'short' => 'Ankle support basketball sneakers with responsive air cushion.',
                'desc' => 'Built for court agility with multidirectional traction rubber outsole and reinforced collar padding.',
                'is_featured' => false,
            ],
            [
                'name' => 'Womens Strap Block Heel Sandals',
                'category_id' => $catFootwear->id, 'brand_id' => null,
                'price' => 3200.00, 'compare_price' => 4000.00, 'cost_price' => 1600.00,
                'image' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=600&q=80',
                'short' => '2.5 inch comfortable block heel sandals with ankle buckle.',
                'desc' => 'Chic versatile heels suitable for evening parties and daytime events with padded arch support.',
                'is_featured' => false,
            ],
            [
                'name' => 'Waterproof Trail Hiking Boots',
                'category_id' => $catFootwear->id, 'brand_id' => null,
                'price' => 7500.00, 'compare_price' => 9000.00, 'cost_price' => 4200.00,
                'image' => 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=600&q=80',
                'short' => 'Heavy-duty outdoor boots with Vibram rubber lug sole.',
                'desc' => 'Waterproof membrane construction keeps feet dry on muddy mountain trails while providing ankle rigidity.',
                'is_featured' => false,
            ],
            [
                'name' => 'Lightweight Casual Canvas Espadrilles',
                'category_id' => $catFootwear->id, 'brand_id' => null,
                'price' => 1950.00, 'compare_price' => 2400.00, 'cost_price' => 900.00,
                'image' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?auto=format&fit=crop&w=600&q=80',
                'short' => 'Jute wrapped sole canvas slip-ons for summer beach wear.',
                'desc' => 'Breathable organic cotton canvas uppers stitched to natural jute braid soles for airy summer steps.',
                'is_featured' => false,
            ],

            // -------------------------------------------------------------
            // HOME & LIVING (39 - 45)
            // -------------------------------------------------------------
            [
                'name' => 'DeLonghi Dedica Deluxe Espresso Machine',
                'category_id' => $catHome->id, 'brand_id' => null,
                'price' => 28500.00, 'compare_price' => 32000.00, 'cost_price' => 22000.00,
                'image' => 'https://images.unsplash.com/photo-1517668808822-9e428824603b?auto=format&fit=crop&w=600&q=80',
                'short' => '15-bar professional pressure espresso and cappuccino maker.',
                'desc' => 'Compact 6-inch wide stainless steel body with adjustable milk frother wand for rich barista quality coffee.',
                'is_featured' => true,
            ],
            [
                'name' => 'Minimalist Dimmable LED Desk Lamp with Wireless Charger',
                'category_id' => $catHome->id, 'brand_id' => null,
                'price' => 3800.00, 'compare_price' => 4500.00, 'cost_price' => 2100.00,
                'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=600&q=80',
                'short' => 'Eye-caring touch control lamp with 10W MagSafe base.',
                'desc' => 'Features 5 color temperature modes and 10 brightness levels with 45-minute auto shutoff timer.',
                'is_featured' => false,
            ],
            [
                'name' => 'HEPA Air Purifier for Home & Bedroom',
                'category_id' => $catHome->id, 'brand_id' => null,
                'price' => 16500.00, 'compare_price' => 19000.00, 'cost_price' => 12000.00,
                'image' => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?auto=format&fit=crop&w=600&q=80',
                'short' => '3-stage filtration system capturing 99.97% dust & pollen.',
                'desc' => 'Quiet 24dB sleep mode, air quality sensor indicator light, and coverage up to 500 sq ft rooms.',
                'is_featured' => true,
            ],
            [
                'name' => 'Nordic Ceramic Coffee Mug Set of 4',
                'category_id' => $catHome->id, 'brand_id' => null,
                'price' => 1850.00, 'compare_price' => 2300.00, 'cost_price' => 900.00,
                'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80',
                'short' => 'Handcrafted stoneware matte glaze 350ml mugs.',
                'desc' => 'Dishwasher and microwave safe stoneware mugs featuring thick walls and heat insulating comfort handle.',
                'is_featured' => false,
            ],
            [
                'name' => 'Egyptian Cotton 600 Thread Count Bedding Set',
                'category_id' => $catHome->id, 'brand_id' => null,
                'price' => 6500.00, 'compare_price' => 8000.00, 'cost_price' => 3800.00,
                'image' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=600&q=80',
                'short' => 'Ultra soft satin weave queen size bedsheet & pillowcases.',
                'desc' => 'Made from long-staple Egyptian cotton for hotel luxury softness that gets smoother with every laundering.',
                'is_featured' => false,
            ],
            [
                'name' => 'Smart Touchless Sensor Trash Can 15L',
                'category_id' => $catHome->id, 'brand_id' => null,
                'price' => 3400.00, 'compare_price' => 4200.00, 'cost_price' => 1900.00,
                'image' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=600&q=80',
                'short' => 'Motion sensor automatic lid container with odor filter.',
                'desc' => 'Infrared motion sensor opens lid within 0.2 seconds and seals tight to trap trash odors in kitchens and bathrooms.',
                'is_featured' => false,
            ],
            [
                'name' => 'Aromatherapy Ultrasonic Essential Oil Diffuser',
                'category_id' => $catHome->id, 'brand_id' => null,
                'price' => 2400.00, 'compare_price' => 3000.00, 'cost_price' => 1200.00,
                'image' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=600&q=80',
                'short' => '500ml wood grain mist humidifier with 7 color LED lights.',
                'desc' => 'Quiet ultrasonic technology creates fine soothing aromatic mist for stress relief, yoga, and sleeping.',
                'is_featured' => false,
            ],

            // -------------------------------------------------------------
            // ACCESSORIES, WATCHES & FRAGRANCES (46 - 50)
            // -------------------------------------------------------------
            [
                'name' => 'Full Grain Leather Bifold Wallet with RFID Blocking',
                'category_id' => $catFashion->id, 'brand_id' => null,
                'price' => 1650.00, 'compare_price' => 2100.00, 'cost_price' => 750.00,
                'image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&q=80',
                'short' => 'Handcrafted vintage brown leather wallet with coin pocket.',
                'desc' => 'Features 8 credit card slots, dual currency notes divider, and built-in RFID shielding protection layer.',
                'is_featured' => true,
            ],
            [
                'name' => 'Polarized Aviator Sunglasses with UV400 Protection',
                'category_id' => $catFashion->id, 'brand_id' => null,
                'price' => 2450.00, 'compare_price' => 3000.00, 'cost_price' => 1100.00,
                'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=600&q=80',
                'short' => 'Classic metal frame anti-glare driving sunglasses.',
                'desc' => 'Lightweight stainless steel frame with scratch-resistant TAC polarized lenses that eliminate glare.',
                'is_featured' => false,
            ],
            [
                'name' => 'Eau De Parfum Woody Intense Cologne 100ml',
                'category_id' => $catFashion->id, 'brand_id' => null,
                'price' => 5200.00, 'compare_price' => 6500.00, 'cost_price' => 2800.00,
                'image' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=600&q=80',
                'short' => 'Long lasting fragrance notes of Cedarwood, Amber & Bergamot.',
                'desc' => 'An irresistible masculine fragrance opening with crisp citrus notes and settling into warm spicy amber notes.',
                'is_featured' => true,
            ],
            [
                'name' => 'Waterproof Travel Laptop Backpack 15.6 inch',
                'category_id' => $catFashion->id, 'brand_id' => null,
                'price' => 3200.00, 'compare_price' => 4000.00, 'cost_price' => 1700.00,
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80',
                'short' => 'Anti-theft commuter backpack with USB charging port.',
                'desc' => 'Features padded shockproof laptop compartment, luggage strap, and water-repellent oxford fabric.',
                'is_featured' => false,
            ],
            [
                'name' => 'Minimalist Automatic Mechanical Wrist Watch',
                'category_id' => $catFashion->id, 'brand_id' => null,
                'price' => 9500.00, 'compare_price' => 12000.00, 'cost_price' => 5200.00,
                'image' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=600&q=80',
                'short' => 'Self-winding mechanical timepiece with sapphire crystal glass.',
                'desc' => 'Transparent skeleton caseback reveals 24-jewel automatic movement with 42-hour power reserve and genuine leather strap.',
                'is_featured' => true,
            ],
        ];

        $index = 1;
        foreach ($demoProducts as $pData) {
            $slug = Str::slug($pData['name']) . '-' . rand(100, 999);
            $sku = 'DEMO-' . str_pad($index, 3, '0', STR_PAD_LEFT);

            $product = Product::firstOrCreate(
                ['sku' => $sku],
                [
                    'name' => $pData['name'],
                    'slug' => $slug,
                    'category_id' => $pData['category_id'],
                    'brand_id' => $pData['brand_id'],
                    'tax_rate_id' => $defaultTax?->id,
                    'is_tax_exempt' => false,
                    'price' => $pData['price'],
                    'compare_price' => $pData['compare_price'],
                    'cost_price' => $pData['cost_price'],
                    'featured_image' => $pData['image'],
                    'short_description' => $pData['short'],
                    'description' => $pData['desc'],
                    'stock_quantity' => rand(15, 80),
                    'low_stock_threshold' => 5,
                    'track_inventory' => true,
                    'is_active' => true,
                    'is_featured' => $pData['is_featured'] ?? false,
                ]
            );

            // Create 3 additional gallery pictures per product for interactive picture serial management
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'sort_order' => 1],
                ['image_path' => $pData['image'], 'is_primary' => true]
            );
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'sort_order' => 2],
                ['image_path' => $pData['image'] . '&v=2', 'is_primary' => false]
            );
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'sort_order' => 3],
                ['image_path' => $pData['image'] . '&v=3', 'is_primary' => false]
            );

            // Create Variants if defined
            if (isset($pData['variants']) && is_array($pData['variants'])) {
                foreach ($pData['variants'] as $vData) {
                    $variant = ProductVariant::firstOrCreate(
                        ['product_id' => $product->id, 'sku' => $vData['sku']],
                        [
                            'price' => $vData['price'],
                            'compare_price' => $pData['compare_price'],
                            'stock_quantity' => $vData['stock'],
                            'is_active' => true,
                        ]
                    );

                    // Attach attribute values
                    if (isset($vData['attrs']) && is_array($vData['attrs'])) {
                        $valIds = [];
                        foreach ($vData['attrs'] as $attrValName) {
                            if (isset($colors[$attrValName])) {
                                $valIds[] = $colors[$attrValName];
                            } elseif (isset($sizes[$attrValName])) {
                                $valIds[] = $sizes[$attrValName];
                            }
                        }
                        if (!empty($valIds)) {
                            $variant->attributeValues()->syncWithoutDetaching($valIds);
                        }
                    }
                }
            }

            $index++;
        }
    }
}
