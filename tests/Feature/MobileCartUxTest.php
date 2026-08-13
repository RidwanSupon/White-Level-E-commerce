<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileCartUxTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->customer = User::where('email', 'customer@example.com')->first();
        $category = Category::first();
        
        $this->product = Product::create([
            'name' => 'App-Like Cotton T-Shirt',
            'slug' => 'app-like-cotton-t-shirt',
            'sku' => 'ACT-999',
            'price' => 850.00,
            'category_id' => $category->id,
            'stock_quantity' => 25,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_1_mobile_cart_renders_app_like_structure_and_sticky_checkout_cta()
    {
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->customer)->get(route('cart.index'));
        $response->assertStatus(200);

        // Verify mobile header, proceed to checkout button, and sticky CTA structure
        $response->assertSee('Your Cart');
        $response->assertSee('Proceed to Checkout');
        $response->assertSee(route('checkout.index'));
        $response->assertSee('calc(3.5rem + env(safe-area-inset-bottom, 0px))', false);
    }

    /** @test */
    public function test_2_empty_mobile_cart_hides_sticky_checkout_cta()
    {
        $response = $this->actingAs($this->customer)->get(route('cart.index'));
        $response->assertStatus(200);
        $response->assertSee('Your cart is empty');
        $response->assertSee('Start Shopping Now');
    }

    /** @test */
    public function test_3_proceed_to_checkout_cta_navigates_to_checkout()
    {
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->customer)->get(route('checkout.index'));
        $response->assertStatus(200);
        $response->assertSee('Checkout');
    }
}
