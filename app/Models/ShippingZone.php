<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'zone_type', 'regions_json', 'districts_json', 'areas_json',
        'delivery_charge', 'advance_payment_required', 'is_active'
    ];

    protected $casts = [
        'regions_json' => 'array',
        'districts_json' => 'array',
        'areas_json' => 'array',
        'delivery_charge' => 'decimal:2',
        'advance_payment_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function shippingMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'zone_id');
    }

    public static function matchZone(?string $location): self
    {
        $cleanLocation = strtolower(trim($location ?? ''));

        $zones = self::where('is_active', true)->get();

        // 1. Check if location contains "dhaka"
        if (str_contains($cleanLocation, 'dhaka')) {
            $dhakaZone = $zones->firstWhere('zone_type', 'dhaka') 
                ?? $zones->first(fn($z) => str_contains(strtolower($z->name), 'dhaka'));

            if ($dhakaZone) {
                return $dhakaZone;
            }
        }

        // 2. Search by matching districts or areas list
        foreach ($zones as $zone) {
            $districts = array_map('strtolower', $zone->districts_json ?? []);
            $areas = array_map('strtolower', $zone->areas_json ?? []);

            if (in_array($cleanLocation, $districts) || in_array($cleanLocation, $areas)) {
                return $zone;
            }
        }

        // 3. Fallback to Outside Dhaka zone
        $outsideDhakaZone = $zones->firstWhere('zone_type', 'outside_dhaka')
            ?? $zones->first(fn($z) => !str_contains(strtolower($z->name), 'dhaka'));

        if ($outsideDhakaZone) {
            return $outsideDhakaZone;
        }

        // 4. In-memory fallback if no database record exists
        $fallback = new self();
        $fallback->id = null;
        $fallback->name = 'Outside Dhaka';
        $fallback->zone_type = 'outside_dhaka';
        $fallback->delivery_charge = 150.00;
        $fallback->advance_payment_required = true;
        $fallback->is_active = true;
        return $fallback;
    }
}
