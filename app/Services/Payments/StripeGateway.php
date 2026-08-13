<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class StripeGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'Stripe';
    }

    public function processPayment(Order $order, array $payload = []): array
    {
        // Stripe integration architecture abstraction
        $transactionId = 'STRIPE-' . strtoupper(uniqid());

        return [
            'success' => true,
            'status' => 'successful',
            'transaction_id' => $transactionId,
            'message' => 'Stripe payment processed successfully.',
            'redirect_url' => route('customer.orders.show', $order->id),
        ];
    }

    public function verifyPayment(Request $request): bool
    {
        return $request->has('payment_intent');
    }

    public function refund(Payment $payment, float $amount, string $reason = ''): bool
    {
        return true;
    }
}
