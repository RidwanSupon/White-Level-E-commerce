<?php

use App\Services\SettingService;

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->get($key, $default);
    }
}

if (!function_exists('format_price')) {
    function format_price(float $amount): string
    {
        return app(SettingService::class)->formatPrice($amount);
    }
}

if (!function_exists('white_label_css')) {
    function white_label_css(): string
    {
        return app(SettingService::class)->generateCssVariables();
    }
}
