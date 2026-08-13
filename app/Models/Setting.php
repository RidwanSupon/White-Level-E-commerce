<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group', 'type', 'is_public'];

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('app_white_label_settings');
        });

        static::deleted(function () {
            Cache::forget('app_white_label_settings');
        });
    }

    public static function getAllCached(): array
    {
        return Cache::rememberForever('app_white_label_settings', function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::getAllCached();
        return $settings[$key] ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string)$value, 'group' => $group, 'type' => $type]
        );
        Cache::forget('app_white_label_settings');
    }
}
