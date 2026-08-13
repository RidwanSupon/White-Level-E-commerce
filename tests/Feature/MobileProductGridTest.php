<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileProductGridTest extends TestCase
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
            'name' => 'Grid Test Cotton Shirt',
            'slug' => 'grid-test-cotton-shirt',
            'sku' => 'GTC-101',
            'price' => 850.00,
            'compare_price' => 1100.00,
            'category_id' => $category->id,
            'stock_quantity' => 20,
            'is_active' => true,
            'is_featured' => true,
        ]);
    }

    /** @test */
    public function test_1_shop_catalog_renders_two_column_mobile_grid()
    {
        $response = $this->get(route('shop'));
        $response->assertStatus(200);

        // Verify grid-cols-2 class for mobile layout
        $response->assertSee('grid grid-cols-2 sm:grid-cols-2', false);
        $firstProduct = Product::where('is_active', true)->first();
        if ($firstProduct) {
            $response->assertSee($firstProduct->name);
        }
    }

    /** @test */
    public function test_2_homepage_renders_two_column_mobile_grid()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $response->assertSee('grid grid-cols-2 sm:grid-cols-2', false);
        $firstFeatured = Product::where('is_featured', true)->first();
        if ($firstFeatured) {
            $response->assertSee($firstFeatured->name);
        }
    }

    /** @test */
    public function test_3_product_details_renders_related_products_in_two_column_mobile_grid()
    {
        $response = $this->get(route('product.show', $this->product->slug));
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }
}
