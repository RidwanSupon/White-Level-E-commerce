<?php

namespace App\Services;

use App\Models\ShippingMethod;

class ShippingCalculatorService
{
    public function calculateFee(int $shippingMethodId, float $subtotal): float
    {
        $method = ShippingMethod::find($shippingMethodId);
        if (!$method) {
            return 70.00; // default shipping fee
        }

        if ($method->free_shipping_threshold && $subtotal >= $method->free_shipping_threshold) {
            return 0.00;
        }

        return (float) $method->charge;
    }
}
