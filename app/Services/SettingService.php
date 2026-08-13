<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    protected array $defaults = [
        'site_name' => 'LuxeCart',
        'site_tagline' => 'Premium E-Commerce Platform',
        'site_logo' => '/images/logo.png',
        'site_favicon' => '/favicon.ico',
        
        // White-Label Theme HSL Color System
        'color_primary_hsl' => '221 83% 53%',      // Electric Blue / Indigo
        'color_secondary_hsl' => '215 28% 17%',    // Slate Dark
        'color_accent_hsl' => '142 71% 45%',       // Emerald Accent
        
        'currency_code' => 'BDT',
        'currency_symbol' => '৳',
        'timezone' => 'Asia/Dhaka',
        'language' => 'en',
        
        'contact_email' => 'support@luxecart.com',
        'contact_phone' => '+880 1700-000000',
        'contact_address' => 'Dhaka, Bangladesh',
        
        'meta_title' => 'LuxeCart - Premium E-Commerce Store',
        'meta_description' => 'Discover luxury electronics, apparel, and modern goods with instant delivery.',
        
        'footer_copyright' => '© 2026 LuxeCart White-Label Platform. All rights reserved.',
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        $value = Setting::get($key);
        return $value !== null ? $value : ($this->defaults[$key] ?? $default);
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        Setting::set($key, $value, $group, $type);
    }

    public function all(): array
    {
        $saved = Setting::getAllCached();
        return array_merge($this->defaults, $saved);
    }

    public function formatPrice(float $amount): string
    {
        $symbol = $this->get('currency_symbol', '৳');
        return $symbol . ' ' . number_format($amount, 2);
    }

    public function generateCssVariables(): string
    {
        $primary = $this->get('color_primary_hsl', '221 83% 53%');
        $secondary = $this->get('color_secondary_hsl', '215 28% 17%');
        $accent = $this->get('color_accent_hsl', '142 71% 45%');

        return ":root {
            --primary: {$primary};
            --secondary: {$secondary};
            --accent: {$accent};
        }";
    }
}
