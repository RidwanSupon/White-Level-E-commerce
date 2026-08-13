<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingZone;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\TaxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Product $product;
    protected TaxRate $defaultTax;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->customer = User::where('email', 'customer@example.com')->first();

        // Enable Tax System & Create Default VAT 15%
        Setting::updateOrCreate(['key' => 'tax_system_enabled'], ['value' => '1', 'group' => 'tax', 'type' => 'boolean']);
        Setting::updateOrCreate(['key' => 'tax_applies_to_delivery'], ['value' => '0', 'group' => 'tax', 'type' => 'boolean']);

        TaxRate::query()->update(['is_default' => false]);
        $this->defaultTax = TaxRate::updateOrCreate(
            ['code' => 'VAT-15'],
            [
                'name' => 'VAT',
                'rate' => 15.00,
                'description' => 'Standard Value Added Tax 15%',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        $category = Category::first();
        $this->product = Product::create([
            'name' => 'Premium T-Shirt',
            'slug' => 'premium-t-shirt',
            'sku' => 'TS-100',
            'price' => 2000.00,
            'category_id' => $category->id,
            'stock_quantity' => 50,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_1_tax_system_off_no_tax_on_checkout()
    {
        Setting::updateOrCreate(['key' => 'tax_system_enabled'], ['value' => '0']);

        $taxService = app(TaxService::class);
        $this->assertFalse($taxService->isTaxEnabled());

        $calc = $taxService->calculateTax([$this->product], 2000.00, 100.00, 150.00);
        $this->assertEquals(0.00, $calc['tax_amount']);
        $this->assertFalse($calc['tax_enabled']);
    }

    /** @test */
    public function test_2_tax_system_on_default_vat_15()
    {
        $taxService = app(TaxService::class);
        $this->assertTrue($taxService->isTaxEnabled());

        // Subtotal = 2000, Discount = 100, Net Taxable = 1900. 1900 * 15% = 285.00
        $calc = $taxService->calculateTax([$this->product], 2000.00, 100.00, 150.00);
        $this->assertEquals(285.00, $calc['tax_amount']);
        $this->assertEquals('VAT', $calc['tax_name']);
        $this->assertEquals(15.00, $calc['tax_rate']);
    }

    /** @test */
    public function test_3_admin_can_create_vat_5_percent()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.taxes.store'), [
            'name' => 'Reduced VAT',
            'code' => 'VAT-05',
            'rate' => 5.00,
            'description' => 'Reduced VAT for essential goods',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.taxes.index'));
        $this->assertDatabaseHas('tax_rates', [
            'code' => 'VAT-05',
            'rate' => 5.00,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_4_deactivate_tax_rate()
    {
        $vat05 = TaxRate::create([
            'name' => 'Reduced VAT',
            'code' => 'VAT-05',
            'rate' => 5.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.taxes.toggle_status', $vat05->id));
        $response->assertRedirect();

        $this->assertFalse($vat05->fresh()->is_active);
    }

    /** @test */
    public function test_5_set_new_tax_as_default_clears_previous_default()
    {
        $vat05 = TaxRate::create([
            'name' => 'Reduced VAT',
            'code' => 'VAT-05',
            'rate' => 5.00,
            'is_active' => true,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.taxes.update', $vat05->id), [
            'name' => 'Reduced VAT',
            'code' => 'VAT-05',
            'rate' => 5.00,
            'is_active' => 1,
            'is_default' => 1,
        ]);

        $response->assertRedirect(route('admin.taxes.index'));

        $this->assertTrue($vat05->fresh()->is_default);
        $this->assertFalse($this->defaultTax->fresh()->is_default);
    }

    /** @test */
    public function test_6_product_specific_tax_applies()
    {
        $vat05 = TaxRate::create([
            'name' => 'Reduced VAT',
            'code' => 'VAT-05',
            'rate' => 5.00,
            'is_active' => true,
        ]);

        $this->product->update(['tax_rate_id' => $vat05->id]);

        $taxService = app(TaxService::class);
        $effectiveRate = $taxService->getEffectiveTaxRateForProduct($this->product);

        $this->assertNotNull($effectiveRate);
        $this->assertEquals(5.00, (float)$effectiveRate->rate);

        $calc = $taxService->calculateTax([$this->product], 2000.00, 0.00, 0.00);
        // 2000 * 5% = 100.00
        $this->assertEquals(100.00, $calc['tax_amount']);
    }

    /** @test */
    public function test_7_product_has_no_specific_tax_inherits_default()
    {
        $taxService = app(TaxService::class);
        $effectiveRate = $taxService->getEffectiveTaxRateForProduct($this->product);

        $this->assertNotNull($effectiveRate);
        $this->assertEquals(15.00, (float)$effectiveRate->rate);
    }

    /** @test */
    public function test_8_order_creation_stores_tax_snapshot()
    {
        $dhakaZone = ShippingZone::where('name', 'Inside Dhaka')->first();

        // Add product to cart
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'John Customer',
            'email' => 'customer@example.com',
            'phone' => '01711111111',
            'address_line_1' => 'House 10, Road 5, Gulshan',
            'city' => 'Dhaka',
            'shipping_zone_id' => $dhakaZone->id,
            'payment_method' => 'cod',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('VAT', $order->tax_name);
        $this->assertEquals(15.00, (float)$order->tax_rate);
        $this->assertEquals(300.00, (float)$order->tax_amount); // 2000 * 15% = 300
        $this->assertNotNull($order->tax_snapshot_json);
    }

    /** @test */
    public function test_9_admin_changes_tax_rate_existing_order_remains_unchanged()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-999',
            'user_id' => $this->customer->id,
            'subtotal' => 2000.00,
            'discount_amount' => 0.00,
            'shipping_fee' => 60.00,
            'delivery_charge' => 60.00,
            'tax_amount' => 300.00,
            'tax_name' => 'VAT',
            'tax_rate' => 15.00,
            'tax_snapshot_json' => ['tax_name' => 'VAT', 'tax_rate' => 15.00, 'tax_amount' => 300.00],
            'grand_total' => 2360.00,
            'payment_method' => 'cod',
            'shipping_address_json' => ['full_name' => 'Test Customer'],
        ]);

        // Admin updates tax rate to 10%
        $this->defaultTax->update(['rate' => 10.00]);

        $freshOrder = Order::find($order->id);
        $this->assertEquals(300.00, (float)$freshOrder->tax_amount);
        $this->assertEquals(15.00, (float)$freshOrder->tax_rate);
        $this->assertEquals('VAT', $freshOrder->tax_name);
    }

    /** @test */
    public function test_10_mobile_checkout_totals_accurate()
    {
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->customer)->postJson(route('checkout.calculate_shipping'), [
            'city' => 'Chittagong',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'tax_amount' => 300.00,
            'tax_name' => 'VAT',
            'tax_rate' => 15.00,
            'tax_enabled' => true,
        ]);
    }

    /** @test */
    public function test_11_disabling_tax_globally_preserves_historical_orders()
    {
        $order = Order::create([
            'order_number' => 'ORD-HIST-001',
            'user_id' => $this->customer->id,
            'subtotal' => 2000.00,
            'discount_amount' => 0.00,
            'shipping_fee' => 60.00,
            'delivery_charge' => 60.00,
            'tax_amount' => 300.00,
            'tax_name' => 'VAT',
            'tax_rate' => 15.00,
            'tax_snapshot_json' => ['tax_name' => 'VAT', 'tax_rate' => 15.00, 'tax_amount' => 300.00, 'tax_enabled' => true],
            'grand_total' => 2360.00,
            'payment_method' => 'cod',
            'shipping_address_json' => ['full_name' => 'Test Customer'],
        ]);

        // Disable Tax System globally
        Setting::updateOrCreate(['key' => 'tax_system_enabled'], ['value' => '0']);

        // Historical order retains 300.00 tax amount and snapshot
        $freshOrder = Order::find($order->id);
        $this->assertEquals(300.00, (float)$freshOrder->tax_amount);

        $viewResponse = $this->actingAs($this->customer)->get(route('customer.orders.show', $order->id));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('VAT (15%)');
    }

    /** @test */
    public function test_12_outside_dhaka_delivery_advance_separate_from_tax()
    {
        $outsideZone = ShippingZone::where('zone_type', 'outside_dhaka')->first();

        // Add product to cart
        $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Rahim Outside',
            'email' => 'rahim@example.com',
            'phone' => '01799999999',
            'address_line_1' => 'Station Road',
            'city' => 'Sylhet',
            'shipping_zone_id' => $outsideZone->id,
            'payment_method' => 'bkash',
            'transaction_id' => 'TRX88776655',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertTrue((bool)$order->delivery_advance_required);
        $this->assertEquals(150.00, (float)$order->delivery_advance_amount);
        $this->assertEquals(300.00, (float)$order->tax_amount);
        $this->assertEquals(2450.00, (float)$order->grand_total); // 2000 subtotal + 150 shipping + 300 tax = 2450
        $this->assertEquals(2300.00, (float)$order->remaining_amount); // 2450 - 150 advance = 2300 remaining
    }
}
