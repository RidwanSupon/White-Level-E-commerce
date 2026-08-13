<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class BkashGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'bKash';
    }

    public function processPayment(Order $order, array $payload = []): array
    {
        return [
            'success' => true,
            'status' => 'successful',
            'transaction_id' => 'BKASH-' . strtoupper(uniqid()),
            'message' => 'bKash payment executed successfully.',
            'redirect_url' => route('customer.orders.show', $order->id),
        ];
    }

    public function verifyPayment(Request $request): bool
    {
        return true;
    }

    public function refund(Payment $payment, float $amount, string $reason = ''): bool
    {
        return true;
    }
}
