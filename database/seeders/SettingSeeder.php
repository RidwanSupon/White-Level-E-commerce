<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'LuxeCart', 'group' => 'branding', 'type' => 'string'],
            ['key' => 'site_tagline', 'value' => 'Premium E-Commerce Platform', 'group' => 'branding', 'type' => 'string'],
            ['key' => 'site_logo', 'value' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=300&q=80', 'group' => 'branding', 'type' => 'image'],
            ['key' => 'site_favicon', 'value' => '/favicon.ico', 'group' => 'branding', 'type' => 'image'],
            
            // HSL Theme Variables (Indigo / Dark Slate / Emerald)
            ['key' => 'color_primary_hsl', 'value' => '221 83% 53%', 'group' => 'colors', 'type' => 'string'],
            ['key' => 'color_secondary_hsl', 'value' => '215 28% 17%', 'group' => 'colors', 'type' => 'string'],
            ['key' => 'color_accent_hsl', 'value' => '142 71% 45%', 'group' => 'colors', 'type' => 'string'],

            ['key' => 'currency_code', 'value' => 'BDT', 'group' => 'currency', 'type' => 'string'],
            ['key' => 'currency_symbol', 'value' => '৳', 'group' => 'currency', 'type' => 'string'],
            ['key' => 'timezone', 'value' => 'Asia/Dhaka', 'group' => 'general', 'type' => 'string'],
            ['key' => 'language', 'value' => 'en', 'group' => 'general', 'type' => 'string'],
            
            ['key' => 'contact_email', 'value' => 'support@luxecart.com', 'group' => 'general', 'type' => 'string'],
            ['key' => 'contact_phone', 'value' => '+880 1700-000000', 'group' => 'general', 'type' => 'string'],
            ['key' => 'contact_address', 'value' => 'Gulshan Avenue, Dhaka, Bangladesh', 'group' => 'general', 'type' => 'string'],
            
            ['key' => 'tax_system_enabled', 'value' => '1', 'group' => 'tax', 'type' => 'boolean'],
            ['key' => 'tax_applies_to_delivery', 'value' => '0', 'group' => 'tax', 'type' => 'boolean'],
            ['key' => 'meta_title', 'value' => 'LuxeCart - Premium E-Commerce Store', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'meta_description', 'value' => 'Discover luxury electronics, modern fashion, and premium accessories.', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'footer_copyright', 'value' => '© 2026 LuxeCart White-Label Platform. All rights reserved.', 'group' => 'general', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // Seed default tax rate
        \App\Models\TaxRate::firstOrCreate(
            ['code' => 'VAT-15'],
            [
                'name' => 'VAT',
                'rate' => 15.00,
                'description' => 'Standard Value Added Tax (15%)',
                'is_active' => true,
                'is_default' => true,
            ]
        );
    }
}
