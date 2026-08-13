<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function getName(): string;

    public function processPayment(Order $order, array $payload = []): array;

    public function verifyPayment(Request $request): bool;

    public function refund(Payment $payment, float $amount, string $reason = ''): bool;
}
