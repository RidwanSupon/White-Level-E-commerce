<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ManualPayment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\ManualPaymentService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManualPaymentSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Product $product;
    protected ShippingMethod $shippingMethod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->customer = User::where('email', 'customer@example.com')->first();
        $this->admin = User::where('email', 'admin@example.com')->first();

        $category = Category::first();
        $this->product = Product::create([
            'name' => 'Payment Test Phone',
            'slug' => 'payment-test-phone',
            'sku' => 'PTP-001',
            'price' => 20000,
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $this->shippingMethod = ShippingMethod::create([
            'name' => 'Standard Courier',
            'charge' => 100,
            'estimated_days' => '2-3 Days',
            'is_active' => true,
        ]);

        // Configure Admin White-Label Payment Settings
        $settingService = app(SettingService::class);
        $settingService->set('bkash_enabled', '1');
        $settingService->set('bkash_number', '01711223344');
        $settingService->set('bkash_account_type', 'Personal');
        $settingService->set('bkash_account_name', 'LuxeCart Store');
        $settingService->set('nagad_enabled', '1');
        $settingService->set('nagad_number', '01855667788');
        $settingService->set('nagad_account_type', 'Merchant');
        $settingService->set('nagad_account_name', 'LuxeCart Store');
    }

    private function prepareCustomerCart(): void
    {
        $cart = Cart::create(['user_id' => $this->customer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 20000,
        ]);
    }

    /** @test */
    public function test_1_customer_checkout_displays_configured_bkash_and_nagad_merchant_numbers()
    {
        $this->prepareCustomerCart();

        $response = $this->actingAs($this->customer)->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('01711223344'); // bKash configured number
        $response->assertSee('01855667788'); // Nagad configured number
    }

    /** @test */
    public function test_3_customer_submits_valid_bkash_transaction_id_and_creates_verification_pending_order()
    {
        $this->prepareCustomerCart();

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Customer Test',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => '123 Test St',
            'city' => 'Dhaka',
            'shipping_method_id' => $this->shippingMethod->id,
            'payment_method' => 'bkash',
            'transaction_id' => 'BKASH-TRX-1001',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('payment_verification_pending', $order->status);
        $this->assertEquals('verification_pending', $order->payment_status);

        $manualPayment = ManualPayment::where('order_id', $order->id)->first();
        $this->assertNotNull($manualPayment);
        $this->assertEquals('bkash', $manualPayment->payment_method);
        $this->assertEquals('01711223344', $manualPayment->merchant_number);
        $this->assertEquals('BKASH-TRX-1001', $manualPayment->transaction_id);
        $this->assertEquals('verification_pending', $manualPayment->status);

        $response->assertRedirect(route('customer.orders.show', $order->id));
    }

    /** @test */
    public function test_4_submitting_duplicate_transaction_id_is_strictly_rejected()
    {
        $this->prepareCustomerCart();

        // Submit first transaction
        $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Customer Test',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => '123 Test St',
            'city' => 'Dhaka',
            'shipping_method_id' => $this->shippingMethod->id,
            'payment_method' => 'bkash',
            'transaction_id' => 'DUP-TRX-999',
        ]);

        // Attempt second order with SAME transaction ID
        $this->prepareCustomerCart();

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Customer Test 2',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => '456 Second St',
            'city' => 'Dhaka',
            'shipping_method_id' => $this->shippingMethod->id,
            'payment_method' => 'bkash',
            'transaction_id' => 'DUP-TRX-999',
        ]);

        $response->assertSessionHasErrors('transaction_id');
        $this->assertEquals(1, ManualPayment::where('transaction_id', 'DUP-TRX-999')->count());
    }

    /** @test */
    public function test_6_admin_verifies_payment_updates_payment_to_verified_and_order_to_confirmed()
    {
        $this->prepareCustomerCart();

        $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Customer Test',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => '123 Test St',
            'city' => 'Dhaka',
            'shipping_method_id' => $this->shippingMethod->id,
            'payment_method' => 'nagad',
            'transaction_id' => 'NAGAD-TRX-500',
        ]);

        $payment = ManualPayment::latest()->first();

        // Admin verifies payment
        $response = $this->actingAs($this->admin)->post(route('admin.payments.verify', $payment->id), [
            'admin_note' => 'Checked against Nagad Merchant App log.',
        ]);

        $response->assertRedirect(route('admin.payments.index'));

        $payment->refresh();
        $this->assertEquals('verified', $payment->status);
        $this->assertEquals($this->admin->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);

        $order = $payment->order->fresh();
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals('verified', $order->payment_status);
    }

    /** @test */
    public function test_7_admin_rejects_payment_updates_payment_to_rejected_and_saves_rejection_reason()
    {
        $this->prepareCustomerCart();

        $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Customer Test',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => '123 Test St',
            'city' => 'Dhaka',
            'shipping_method_id' => $this->shippingMethod->id,
            'payment_method' => 'bkash',
            'transaction_id' => 'INVALID-TRX-000',
        ]);

        $payment = ManualPayment::latest()->first();

        // Admin rejects payment
        $response = $this->actingAs($this->admin)->post(route('admin.payments.reject', $payment->id), [
            'rejection_reason' => 'Transaction ID not found in bKash merchant log.',
            'admin_note' => 'Verified with bank statement.',
        ]);

        $response->assertRedirect(route('admin.payments.index'));

        $payment->refresh();
        $this->assertEquals('rejected', $payment->status);
        $this->assertEquals($this->admin->id, $payment->rejected_by);
        $this->assertEquals('Transaction ID not found in bKash merchant log.', $payment->rejection_reason);

        $order = $payment->order->fresh();
        $this->assertEquals('payment_rejected', $order->status);
        $this->assertEquals('rejected', $order->payment_status);
    }

    /** @test */
    public function test_8_and_9_customer_order_details_displays_verification_status_and_rejection_reason()
    {
        $this->prepareCustomerCart();

        $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Customer Test',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => '123 Test St',
            'city' => 'Dhaka',
            'shipping_method_id' => $this->shippingMethod->id,
            'payment_method' => 'bkash',
            'transaction_id' => 'DISPLAY-TRX-123',
        ]);

        $order = Order::latest()->first();
        $payment = ManualPayment::latest()->first();

        // Customer views pending verification order details
        $response = $this->actingAs($this->customer)->get(route('customer.orders.show', $order->id));
        $response->assertStatus(200);
        $response->assertSee('Verification Pending');
        $response->assertSee('DISPLAY-TRX-123');

        // Admin rejects
        $this->actingAs($this->admin)->post(route('admin.payments.reject', $payment->id), [
            'rejection_reason' => 'Amount does not match statement.',
        ]);

        // Customer views rejected order details
        $response = $this->actingAs($this->customer)->get(route('customer.orders.show', $order->id));
        $response->assertStatus(200);
        $response->assertSee('Payment Verification Rejected');
        $response->assertSee('Amount does not match statement.');
    }

    /** @test */
    public function test_11_payment_screenshot_proof_file_upload_stores_file_securely()
    {
        $this->prepareCustomerCart();
        Storage::fake('public');

        $file = UploadedFile::fake()->image('payment_proof.png', 600, 600);

        $this->actingAs($this->customer)->post(route('checkout.process'), [
            'full_name' => 'Customer Test',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'address_line_1' => '123 Test St',
            'city' => 'Dhaka',
            'shipping_method_id' => $this->shippingMethod->id,
            'payment_method' => 'bkash',
            'transaction_id' => 'FILE-TRX-888',
            'payment_proof' => $file,
        ]);

        $payment = ManualPayment::latest()->first();
        $this->assertNotNull($payment->payment_proof);
        $this->assertStringContainsString('/uploads/payment_proofs/', $payment->payment_proof);
    }
}
