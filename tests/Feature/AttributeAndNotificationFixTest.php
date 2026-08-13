<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttributeAndNotificationFixTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->customer = User::where('email', 'customer@example.com')->first();

        $category = Category::first();
        $this->product = Product::create([
            'name' => 'Premium Cotton T-Shirt',
            'slug' => 'premium-cotton-t-shirt',
            'sku' => 'PCT-001',
            'price' => 850,
            'category_id' => $category->id,
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_1_reusable_attributes_exist_in_database()
    {
        $size = Attribute::where('code', 'size')->first();
        $color = Attribute::where('code', 'color')->first();
        $fabric = Attribute::where('code', 'fabric')->first();
        $fit = Attribute::where('code', 'fit')->first();

        $this->assertNotNull($size);
        $this->assertNotNull($color);
        $this->assertNotNull($fabric);
        $this->assertNotNull($fit);

        $this->assertTrue($size->values()->where('value', 'M')->exists());
        $this->assertTrue($color->values()->where('value', 'Navy Blue')->exists());
        $this->assertTrue($fabric->values()->where('value', 'Cotton')->exists());
        $this->assertTrue($fit->values()->where('value', 'Regular Fit')->exists());
    }

    /** @test */
    public function test_2_admin_can_create_new_attribute_and_value()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.attributes.store'), [
            'name' => 'Neckline',
            'type' => 'select',
        ]);
        $response->assertRedirect();

        $attr = Attribute::where('code', 'neckline')->first();
        $this->assertNotNull($attr);

        $valResponse = $this->actingAs($this->admin)->post(route('admin.attributes.values.store', $attr->id), [
            'value' => 'V-Neck',
        ]);
        $valResponse->assertRedirect();

        $this->assertTrue($attr->values()->where('value', 'V-Neck')->exists());
    }

    /** @test */
    public function test_3_notification_mark_as_read_handles_array_data_without_type_error()
    {
        $notifId = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $notifId,
            'type' => 'App\\Notifications\\LowStockNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->admin->id,
            'data' => json_encode([
                'alert_type' => 'low_stock',
                'product_id' => $this->product->id,
                'url' => route('admin.products.edit', $this->product->id),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.notifications.read', $notifId));
        $response->assertRedirect(route('admin.products.edit', $this->product->id));

        $this->assertNotNull(DB::table('notifications')->where('id', $notifId)->value('read_at'));
    }

    /** @test */
    public function test_4_notification_mark_as_read_handles_string_data_safely()
    {
        $notifId = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $notifId,
            'type' => 'App\\Notifications\\LowStockNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->admin->id,
            'data' => json_encode([
                'alert_type' => 'out_of_stock',
                'product_id' => $this->product->id,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.notifications.read', $notifId));
        $response->assertRedirect(route('admin.products.edit', $this->product->id));
    }

    /** @test */
    public function test_5_notification_mark_as_read_handles_malformed_old_data_without_crash()
    {
        $notifId = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $notifId,
            'type' => 'App\\Notifications\\LegacyNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->admin->id,
            'data' => 'raw_plain_text_non_json',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.notifications.read', $notifId));
        $response->assertRedirect();
    }

    /** @test */
    public function test_6_mobile_cart_data_endpoint_returns_structured_summary()
    {
        // Add item to cart for customer
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->customer)->get(route('cart.data'));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json('summary');
        $this->assertEquals(2, $data['count']);
        $this->assertEquals(1700, $data['subtotal']);
    }

    /** @test */
    public function test_7_mobile_cart_quantity_update_recalculates_totals()
    {
        // Add item to cart for customer
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $cartData = $this->actingAs($this->customer)->get(route('cart.data'))->json('summary');
        $itemId = $cartData['items'][0]['id'];

        $updateResponse = $this->actingAs($this->customer)->patchJson(route('cart.update', $itemId), [
            'quantity' => 3,
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson([
            'success' => true,
        ]);

        $updatedSummary = $updateResponse->json('summary');
        $this->assertEquals(3, $updatedSummary['count']);
        $this->assertEquals(2550, $updatedSummary['subtotal']);
    }
}
