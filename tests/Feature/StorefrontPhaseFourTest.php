<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPhaseFourTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->customer = User::where('email', 'customer@example.com')->first();
    }

    public function test_customer_can_toggle_wishlist()
    {
        $product = Product::first();

        // Add to wishlist
        $response = $this->actingAs($this->customer)->post(route('customer.wishlist.toggle'), [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
        ]);

        // Toggle again to remove
        $response2 = $this->actingAs($this->customer)->post(route('customer.wishlist.toggle'), [
            'product_id' => $product->id,
        ]);

        $response2->assertRedirect();
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_can_move_wishlist_item_to_cart()
    {
        $product = Product::first();
        $wishlist = Wishlist::create([
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($this->customer)->post(route('customer.wishlist.move_to_cart', $wishlist->id));

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseMissing('wishlists', ['id' => $wishlist->id]);
    }

    public function test_customer_can_submit_product_review()
    {
        $product = Product::first();

        $response = $this->actingAs($this->customer)->post(route('product.review', $product->id), [
            'rating' => 5,
            'title' => 'Outstanding quality!',
            'comment' => 'Exceeded my expectations, fast shipping.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
        ]);
    }

    public function test_admin_can_manage_promo_banners()
    {
        $admin = User::where('email', 'admin@example.com')->first();

        $response = $this->actingAs($admin)->post(route('admin.banners.store'), [
            'title' => 'Black Friday Flash Sale',
            'subtitle' => 'Up to 70% off on premium tech',
            'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('banners', ['title' => 'Black Friday Flash Sale']);
    }
}
