<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingAndCouponSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Shipping Zones & Methods
        $dhakaZone = ShippingZone::create([
            'name' => 'Inside Dhaka',
            'zone_type' => 'dhaka',
            'regions_json' => ['Dhaka City', 'Gulshan', 'Banani', 'Dhanmondi', 'Uttara', 'Mirpur'],
            'districts_json' => ['Dhaka'],
            'areas_json' => ['Dhaka City', 'Gulshan', 'Banani', 'Dhanmondi', 'Uttara', 'Mirpur'],
            'delivery_charge' => 60.00,
            'advance_payment_required' => false,
            'is_active' => true,
        ]);

        ShippingMethod::create([
            'zone_id' => $dhakaZone->id,
            'name' => 'Standard Express Delivery (Inside Dhaka)',
            'charge' => 60.00,
            'free_shipping_threshold' => 5000.00,
            'estimated_days' => '24-48 Hours',
            'is_active' => true,
        ]);

        $outsideZone = ShippingZone::create([
            'name' => 'Outside Dhaka (All Districts)',
            'zone_type' => 'outside_dhaka',
            'regions_json' => ['Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur', 'Mymensingh', 'Comilla'],
            'districts_json' => ['Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur', 'Mymensingh', 'Comilla', 'Bogura', 'Gazipur', 'Narayanganj'],
            'areas_json' => [],
            'delivery_charge' => 150.00,
            'advance_payment_required' => true,
            'is_active' => true,
        ]);

        ShippingMethod::create([
            'zone_id' => $outsideZone->id,
            'name' => 'Courier Express Delivery (Outside Dhaka)',
            'charge' => 150.00,
            'free_shipping_threshold' => 8000.00,
            'estimated_days' => '2-4 Business Days',
            'is_active' => true,
        ]);

        // 2. Coupons
        Coupon::create([
            'code' => 'WELCOME10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_spend' => 1000.00,
            'max_discount' => 2000.00,
            'usage_limit' => 500,
            'usage_per_user' => 1,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(6),
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'LUXURY500',
            'type' => 'fixed',
            'value' => 500.00,
            'min_spend' => 5000.00,
            'usage_limit' => 100,
            'usage_per_user' => 1,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
        ]);
    }
}
