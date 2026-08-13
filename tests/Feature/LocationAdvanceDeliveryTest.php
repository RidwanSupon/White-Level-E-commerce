<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ManualPayment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationAdvanceDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Product $product;
    protected ShippingZone $dhakaZone;
    protected ShippingZone $outsideZone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->customer = User::where('email', 'customer@example.com')->first();
        $this->admin = User::where('email', 'admin@example.com')->first();

        $category = Category::first();
        $this->product = Product::create([
            'name' => 'Advance Delivery Smartphone',
            'slug' => 'advance-delivery-smartphone',
            'sku' => 'ADS-001',
            'price' => 2000,
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $this->dhakaZone = ShippingZone::where('zone_type', 'dhaka')->first();
        $this->outsideZone = ShippingZone::where('zone_type', 'outside_dhaka')->first();

        // Configure Admin White-Label Mobile Payment Numbers & 5% VAT
        $settingService = app(SettingService::class);
        $settingService->set('bkash_enabled', '1');
        $settingService->set('bkash_number', '01711223344');
        $settingService->set('nagad_enabled', '1');
        $settingService->set('nagad_number', '01855667788');

        \App\Models\TaxRate::query()->update(['is_default' => false]);
        \App\Models\TaxRate::updateOrCreate(
            ['code' => 'VAT-05'],
            ['name' => 'VAT', 'rate' => 5.00, 'is_active' => true, 'is_default' => true]
        );
    }

    private function prepareCustomerCart(): void
    {
        $cart = Cart::create(['user_id' => $this->customer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 2000,
        ]);
    }

    /** @test */
    public function test_1_customer_selects_dhaka_normal_delivery_flow()
    {
        $this->prepareCustomerCart();

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Dhaka Customer',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => 'Dhanmondi Road 2',
            'city' => 'Dhaka',
            'payment_method' => 'cod',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $this->assertFalse((bool) $order->delivery_advance_required);
        $this->assertEquals(60, $order->delivery_charge);
        $this->assertEquals(2160, $order->grand_total);
    }

    /** @test */
    public function test_2_and_3_customer_selects_outside_dhaka_advance_delivery_payment_required()
    {
        $this->prepareCustomerCart();

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Chattogram Customer',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => 'GEC Circle',
            'city' => 'Chattogram',
            'payment_method' => 'bkash',
            'transaction_id' => 'ADV-TRX-150',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('payment_verification_pending', $order->status);
        $this->assertTrue((bool) $order->delivery_advance_required);
        $this->assertEquals(150, $order->delivery_charge);
        $this->assertEquals(150, $order->delivery_advance_amount);
        $this->assertEquals(2100, $order->remaining_amount); // 2000 product + 150 shipping + 100 tax - 150 advance = 2100 remaining

        $manualPayment = ManualPayment::where('order_id', $order->id)->first();
        $this->assertNotNull($manualPayment);
        $this->assertEquals('delivery_advance', $manualPayment->payment_type);
        $this->assertEquals(150, $manualPayment->amount);
    }

    /** @test */
    public function test_4_submitting_incorrect_advance_payment_amount_is_rejected_by_backend()
    {
        $this->prepareCustomerCart();

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Chattogram Customer',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => 'GEC Circle',
            'city' => 'Chattogram',
            'payment_method' => 'bkash',
            'transaction_id' => 'WRONG-AMT-TRX',
            'submitted_amount' => 2000, // Invalid: expected 150
        ]);

        $response->assertSessionHasErrors('transaction_id');
        $this->assertNull(Order::where('notes', 'LIKE', '%WRONG-AMT-TRX%')->first());
    }

    /** @test */
    public function test_6_admin_verifies_150_advance_payment_confirms_order_and_sets_remaining_cod_due()
    {
        $this->prepareCustomerCart();

        $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Chattogram Customer',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => 'GEC Circle',
            'city' => 'Chattogram',
            'payment_method' => 'bkash',
            'transaction_id' => 'VERIFY-ADV-150',
        ]);

        $payment = ManualPayment::latest()->first();

        // Admin verifies advance payment
        $this->actingAs($this->admin)->post(route('admin.payments.verify', $payment->id));

        $payment->refresh();
        $this->assertEquals('verified', $payment->status);

        $order = $payment->order->fresh();
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals(150, $order->delivery_advance_paid);
        $this->assertEquals(2100, $order->remaining_amount); // 2000 + 100 VAT tax
    }

    /** @test */
    public function test_8_and_9_address_city_recalculation_api_toggles_advance_required()
    {
        $this->prepareCustomerCart();

        // 1. Calculate for Dhaka
        $responseDhaka = $this->actingAs($this->customer)->postJson(route('checkout.calculate_shipping'), [
            'city' => 'Dhaka',
        ]);

        $responseDhaka->assertStatus(200);
        $responseDhaka->assertJsonPath('advance_required', false);
        $responseDhaka->assertJsonPath('delivery_charge', 60);

        // 2. Calculate for Chattogram
        $responseCtg = $this->actingAs($this->customer)->postJson(route('checkout.calculate_shipping'), [
            'city' => 'Chattogram',
        ]);

        $responseCtg->assertStatus(200);
        $responseCtg->assertJsonPath('advance_required', true);
        $responseCtg->assertJsonPath('delivery_charge', 150);
        $responseCtg->assertJsonPath('advance_amount', 150);
    }

    /** @test */
    public function test_11_updating_shipping_zone_delivery_charge_preserves_historical_order_snapshot()
    {
        $this->prepareCustomerCart();

        $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Historical Customer',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => 'Sylhet Town',
            'city' => 'Sylhet',
            'payment_method' => 'bkash',
            'transaction_id' => 'HIST-TRX-100',
        ]);

        $order = Order::latest()->first();
        $this->assertEquals(150, $order->delivery_charge);

        // Admin changes Outside Dhaka Delivery Charge to 200.00 in database
        $this->outsideZone->update(['delivery_charge' => 200.00]);

        // Pre-existing order's delivery charge remains snapshot at 150.00
        $order->refresh();
        $this->assertEquals(150, $order->delivery_charge);
    }
}
