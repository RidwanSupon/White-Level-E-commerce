<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_page_loads_successfully()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee(setting('site_name', 'LuxeCart'));
    }

    public function test_shop_page_lists_products()
    {
        $response = $this->get('/shop');
        $response->assertStatus(200);
        $response->assertSee('iPhone 16 Pro Max 256GB');
    }

    public function test_customer_can_login_with_demo_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs(User::where('email', 'customer@example.com')->first());
    }

    public function test_admin_can_login_and_access_settings()
    {
        $admin = User::where('email', 'admin@example.com')->first();
        
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin);

        $settingsResponse = $this->get('/admin/settings');
        $settingsResponse->assertStatus(200);
        $settingsResponse->assertSee('White-Label System Settings');
    }

    public function test_customer_can_add_product_to_cart_and_checkout()
    {
        $customer = User::where('email', 'customer@example.com')->first();
        $this->actingAs($customer);

        $product = Product::first();
        $shippingMethod = \App\Models\ShippingMethod::first();

        $cartResponse = $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cartResponse->assertRedirect();

        $checkoutResponse = $this->post('/checkout/process', [
            'full_name' => 'John Customer',
            'email' => 'customer@example.com',
            'phone' => '+880 1700000000',
            'address_line_1' => 'Gulshan 2, Dhaka',
            'city' => 'Dhaka',
            'shipping_method_id' => $shippingMethod->id,
            'payment_method' => 'cod',
        ]);

        $checkoutResponse->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'payment_method' => 'cod',
        ]);
    }
}
