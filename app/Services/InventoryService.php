<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;

class InventoryService
{
    public function reserveStock(Product $product, ?ProductVariant $variant, int $quantity): bool
    {
        $target = $variant ?? $product;

        if ($target->stock_quantity < $quantity) {
            return false;
        }

        return true;
    }

    public function commitStock(Product $product, ?ProductVariant $variant, int $quantity, string $reference = ''): void
    {
        $target = $variant ?? $product;
        $target->decrement('stock_quantity', $quantity);

        InventoryMovement::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'user_id' => auth()->id(),
            'type' => 'OUT',
            'quantity' => $quantity,
            'reference' => $reference ?: 'ORDER-COMMIT',
            'notes' => 'Stock committed upon checkout completion',
        ]);

        app(StockAlertService::class)->checkStockAndNotify($target);
    }

    public function releaseStock(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        $target = $variant ?? $product;
        $target->increment('stock_quantity', $quantity);

        InventoryMovement::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'user_id' => auth()->id(),
            'type' => 'RETURN',
            'quantity' => $quantity,
            'reference' => 'RESERVATION-RELEASE',
            'notes' => 'Stock released from order cancellation',
        ]);

        app(StockAlertService::class)->checkStockAndNotify($target);
    }
}
