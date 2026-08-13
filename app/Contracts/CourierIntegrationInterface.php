<?php

namespace App\Contracts;

use App\Models\Order;

interface CourierIntegrationInterface
{
    public function getProviderName(): string;

    public function createConsignment(Order $order): array;

    public function getTrackingStatus(string $consignmentId): array;
}
