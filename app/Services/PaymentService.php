<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\BkashGateway;
use App\Services\Payments\CodGateway;
use App\Services\Payments\NagadGateway;
use App\Services\Payments\SSLCommerzGateway;
use App\Services\Payments\StripeGateway;

class PaymentService
{
    public function processPayment(Order $order, string $gatewayName): array
    {
        $gateway = match ($gatewayName) {
            'stripe' => new StripeGateway(),
            'sslcommerz' => new SSLCommerzGateway(),
            'bkash' => new BkashGateway(),
            'nagad' => new NagadGateway(),
            default => new CodGateway(),
        };

        $result = $gateway->processPayment($order, [
            'amount' => $order->grand_total,
            'currency' => setting('currency_code', 'BDT'),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $result['transaction_id'] ?? ('TXN-' . strtoupper(uniqid())),
            'gateway' => $gatewayName,
            'amount' => $order->grand_total,
            'currency' => setting('currency_code', 'BDT'),
            'status' => ($result['success'] ?? true) ? 'successful' : 'failed',
            'payload_json' => $result,
        ]);

        if ($result['success'] ?? true) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
            ]);
        }

        return $result;
    }
}
