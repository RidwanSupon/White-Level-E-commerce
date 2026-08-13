<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\CourierService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPhaseFiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->customer = User::where('email', 'customer@example.com')->first();
    }

    public function test_payment_service_processes_cod_charge()
    {
        $order = Order::create([
            'order_number' => 'ORD-PAY-001',
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 5000.00,
            'discount_amount' => 0.00,
            'shipping_fee' => 70.00,
            'tax_amount' => 250.00,
            'grand_total' => 5320.00,
            'payment_method' => 'cod',
            'shipping_address_json' => ['full_name' => 'John Doe', 'city' => 'Dhaka'],
        ]);

        $service = new PaymentService();
        $result = $service->processPayment($order, 'cod');

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway' => 'cod',
        ]);
    }

    public function test_courier_service_dispatches_order()
    {
        $order = Order::create([
            'order_number' => 'ORD-COUR-001',
            'user_id' => $this->customer->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'subtotal' => 3000.00,
            'discount_amount' => 0.00,
            'shipping_fee' => 70.00,
            'tax_amount' => 150.00,
            'grand_total' => 3220.00,
            'payment_method' => 'bkash',
            'shipping_address_json' => ['full_name' => 'Jane Doe', 'city' => 'Dhaka'],
        ]);

        $courierService = new CourierService();
        $result = $courierService->dispatchOrder($order, 'pathao');

        $this->assertNotEmpty($result['consignment_id']);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'shipping_status' => 'shipped',
        ]);
    }

    public function test_payment_callback_route_handles_webhook()
    {
        $order = Order::create([
            'order_number' => 'ORD-IPN-001',
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 2000.00,
            'discount_amount' => 0.00,
            'shipping_fee' => 70.00,
            'tax_amount' => 100.00,
            'grand_total' => 2170.00,
            'payment_method' => 'stripe',
            'shipping_address_json' => ['full_name' => 'Test Customer', 'city' => 'Dhaka'],
        ]);

        $response = $this->get('/payment/callback/stripe?order_id=' . $order->id);

        $response->assertRedirect(route('customer.orders.show', $order->id));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
        ]);
    }
}
