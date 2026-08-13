<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_admin_can_view_product_management_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));
        $response->assertStatus(200);
        $response->assertSee('Product Catalog');
    }

    public function test_admin_can_create_new_product()
    {
        $category = Category::first();

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Flagship Tablet 11 Inch',
            'sku' => 'TAB-11-PRO',
            'price' => 75000.00,
            'category_id' => $category->id,
            'stock_quantity' => 20,
            'low_stock_threshold' => 3,
            'featured_image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=600&q=80',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['sku' => 'TAB-11-PRO']);
    }

    public function test_admin_can_update_order_status()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'user_id' => $this->admin->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'shipping_status' => 'pending',
            'subtotal' => 1000.00,
            'discount_amount' => 0.00,
            'shipping_fee' => 70.00,
            'tax_amount' => 50.00,
            'grand_total' => 1120.00,
            'payment_method' => 'cod',
            'shipping_address_json' => ['full_name' => 'Test User', 'city' => 'Dhaka'],
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.orders.status', $order->id), [
            'status' => 'shipped',
            'notes' => 'Dispatched via Pathao Courier',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
    }

    public function test_admin_can_record_inventory_adjustment()
    {
        $product = Product::first();
        $initialStock = $product->stock_quantity;

        $response = $this->actingAs($this->admin)->post(route('admin.inventory.adjust'), [
            'product_id' => $product->id,
            'type' => 'IN',
            'quantity' => 15,
            'notes' => 'Received new stock shipment',
        ]);

        $response->assertRedirect();
        $this->assertEquals($initialStock + 15, $product->fresh()->stock_quantity);
    }

    public function test_admin_can_create_role_and_assign_to_staff()
    {
        $roleResponse = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'name' => 'Catalog Manager',
            'description' => 'Manages products and categories',
        ]);

        $roleResponse->assertRedirect();
        $role = Role::where('slug', 'catalog-manager')->first();

        $staffUser = User::factory()->create(['is_admin' => true]);

        $assignResponse = $this->actingAs($this->admin)->post(route('admin.users.assign_role', $staffUser->id), [
            'role_id' => $role->id,
        ]);

        $assignResponse->assertRedirect();
        $this->assertTrue($staffUser->fresh()->hasRole('catalog-manager'));
    }
}
