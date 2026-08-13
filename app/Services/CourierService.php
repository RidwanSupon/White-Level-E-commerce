<?php

namespace App\Services;

use App\Models\Order;
use App\Services\Couriers\PathaoCourier;
use App\Services\Couriers\SteadfastCourier;

class CourierService
{
    public function dispatchOrder(Order $order, string $courierName = 'pathao'): array
    {
        $courier = match ($courierName) {
            'steadfast' => new SteadfastCourier(),
            default => new PathaoCourier(),
        };

        $result = $courier->createConsignment($order);

        if (!empty($result['consignment_id'])) {
            $order->update([
                'tracking_number' => $result['consignment_id'],
                'shipping_status' => 'shipped',
            ]);
        }

        return $result;
    }
}
