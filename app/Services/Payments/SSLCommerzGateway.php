<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class SSLCommerzGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'SSLCommerz';
    }

    public function processPayment(Order $order, array $payload = []): array
    {
        $transactionId = 'SSLC-' . strtoupper(uniqid());

        return [
            'success' => true,
            'status' => 'successful',
            'transaction_id' => $transactionId,
            'message' => 'SSLCommerz payment gateway session initiated.',
            'redirect_url' => route('customer.orders.show', $order->id),
        ];
    }

    public function verifyPayment(Request $request): bool
    {
        return $request->input('status') === 'VALID';
    }

    public function refund(Payment $payment, float $amount, string $reason = ''): bool
    {
        return true;
    }
}
