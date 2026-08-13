<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            CategoryAndBrandSeeder::class,
            AttributeOptionSeeder::class,
            ProductSeeder::class,
            FiftyDemoProductsSeeder::class,
            ShippingAndCouponSeeder::class,
        ]);
    }
}
