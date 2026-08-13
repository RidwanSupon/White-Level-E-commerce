<?php

namespace App\Services\Couriers;

use App\Contracts\CourierIntegrationInterface;
use App\Models\Order;

class PathaoCourier implements CourierIntegrationInterface
{
    public function getProviderName(): string
    {
        return 'Pathao Courier';
    }

    public function createConsignment(Order $order): array
    {
        return [
            'success' => true,
            'consignment_id' => 'PTH-' . strtoupper(uniqid()),
            'status' => 'Pending Pick-up',
            'message' => 'Pathao consignment created successfully.',
        ];
    }

    public function getTrackingStatus(string $consignmentId): array
    {
        return [
            'consignment_id' => $consignmentId,
            'status' => 'In Transit',
            'estimated_delivery' => now()->addDays(2)->toFormattedDateString(),
        ];
    }
}
