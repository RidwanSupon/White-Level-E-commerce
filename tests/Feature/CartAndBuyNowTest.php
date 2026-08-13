<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndBuyNowTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Product $product;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'customer@example.com')->first();
        $this->category = Category::first();
        $this->product = Product::create([
            'name' => 'Cart Test Smartphone',
            'slug' => 'cart-test-smartphone',
            'sku' => 'CTS-001',
            'price' => 25000,
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_1_user_adds_product_to_cart_increases_cart_count()
    {
        $response = $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('summary.count', 2);
    }

    /** @test */
    public function test_2_adds_same_product_and_variant_increments_quantity_without_duplicate_row()
    {
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertCount(1, $cart->items);
        $this->assertEquals(5, $cart->items->first()->quantity);
    }

    /** @test */
    public function test_3_adds_same_product_with_different_variants_creates_separate_rows()
    {
        $variant1 = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'CTS-VAR-RED',
            'price' => 26000,
            'stock_quantity' => 5,
        ]);

        $variant2 = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'CTS-VAR-BLUE',
            'price' => 26000,
            'stock_quantity' => 5,
        ]);

        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'product_variant_id' => $variant1->id,
            'quantity' => 1,
        ]);

        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'product_variant_id' => $variant2->id,
            'quantity' => 1,
        ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertCount(2, $cart->items);
    }

    /** @test */
    public function test_4_user_removes_item_from_cart()
    {
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $cartItem = CartItem::first();
        $response = $this->actingAs($this->user)->deleteJson(route('cart.remove', $cartItem->id));

        $response->assertStatus(200);
        $response->assertJsonPath('summary.count', 0);
        $this->assertCount(0, CartItem::all());
    }

    /** @test */
    public function test_5_user_updates_quantity_recalculates_totals()
    {
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $cartItem = CartItem::first();
        $response = $this->actingAs($this->user)->patchJson(route('cart.update', $cartItem->id), [
            'quantity' => 4,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('summary.count', 4);
        $response->assertJsonPath('summary.subtotal', 100000);
    }

    /** @test */
    public function test_6_buy_now_adds_item_and_redirects_directly_to_checkout()
    {
        $response = $this->actingAs($this->user)->postJson(route('buy_now'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('redirect_url', route('checkout.index'));

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertCount(1, $cart->items);
    }

    /** @test */
    public function test_7_buy_now_preserves_existing_cart_items()
    {
        $otherProduct = Product::create([
            'name' => 'Other Item',
            'slug' => 'other-item',
            'sku' => 'OTH-001',
            'price' => 5000,
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
            'low_stock_threshold' => 1,
            'is_active' => true,
        ]);

        // Add Product 1 to cart
        $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $otherProduct->id,
            'quantity' => 1,
        ]);

        // Execute Buy Now on Product 2
        $this->actingAs($this->user)->postJson(route('buy_now'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        // Cart must contain BOTH products (2 items total)
        $this->assertCount(2, $cart->items);
    }

    /** @test */
    public function test_8_product_with_variants_requires_variant_selection()
    {
        $variantProduct = Product::create([
            'name' => 'Variant Laptop',
            'slug' => 'variant-laptop',
            'sku' => 'VL-001',
            'price' => 50000,
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
            'low_stock_threshold' => 1,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $variantProduct->id,
            'sku' => 'VL-16GB',
            'price' => 55000,
            'stock_quantity' => 5,
        ]);

        // Attempt Add to Cart without specifying variant_id
        $response = $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $variantProduct->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Please select a product option (variant).');
    }

    /** @test */
    public function test_9_exceeding_available_stock_is_rejected()
    {
        $lowStockProduct = Product::create([
            'name' => 'Low Stock Headphone',
            'slug' => 'low-stock-headphone',
            'sku' => 'LSH-001',
            'price' => 3000,
            'category_id' => $this->category->id,
            'stock_quantity' => 3,
            'low_stock_threshold' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('cart.add'), [
            'product_id' => $lowStockProduct->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Only 3 items are available in stock.');
    }

    /** @test */
    public function test_11_guest_cart_merges_into_user_cart_upon_login()
    {
        $cartService = new CartService();
        $guestSessionId = 'guest_session_123456';

        $guestCart = Cart::create(['session_id' => $guestSessionId]);
        CartItem::create([
            'cart_id' => $guestCart->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
            'price' => 25000,
        ]);

        $cartService->mergeGuestCart($this->user, $guestSessionId);

        $userCart = Cart::where('user_id', $this->user->id)->first();
        $this->assertNotNull($userCart);
        $this->assertCount(1, $userCart->items);
        $this->assertEquals(3, $userCart->items->first()->quantity);
        $this->assertDatabaseMissing('carts', ['session_id' => $guestSessionId]);
    }
}
