<?php

namespace App\Services\Couriers;

use App\Contracts\CourierIntegrationInterface;
use App\Models\Order;

class SteadfastCourier implements CourierIntegrationInterface
{
    public function getProviderName(): string
    {
        return 'Steadfast Courier';
    }

    public function createConsignment(Order $order): array
    {
        return [
            'success' => true,
            'consignment_id' => 'STDF-' . strtoupper(uniqid()),
            'status' => 'Parcel Created',
            'message' => 'Steadfast consignment created successfully.',
        ];
    }

    public function getTrackingStatus(string $consignmentId): array
    {
        return [
            'consignment_id' => $consignmentId,
            'status' => 'Dispatched',
            'estimated_delivery' => now()->addDay()->toFormattedDateString(),
        ];
    }
}
