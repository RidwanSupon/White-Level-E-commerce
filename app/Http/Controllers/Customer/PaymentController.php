<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function callback(Request $request, string $gateway)
    {
        $orderId = $request->input('order_id');
        $order = Order::findOrFail($orderId);

        $paymentService = new PaymentService();
        $result = $paymentService->processPayment($order, $gateway);

        if ($result['success']) {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Payment processed successfully! Your order is confirmed.');
        }

        return redirect()->route('checkout.index')
            ->with('error', 'Payment failed or was cancelled. Please try again.');
    }
}
