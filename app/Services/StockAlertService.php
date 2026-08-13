<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockAlertService
{
    public function checkStockAndNotify(Product|ProductVariant $target): void
    {
        $isVariant = ($target instanceof ProductVariant);
        $product = $isVariant ? $target->product : $target;
        $variant = $isVariant ? $target : null;

        if (!$product) {
            return;
        }

        $stock = (int) $target->stock_quantity;
        $threshold = (int) ($target->low_stock_threshold ?? $product->low_stock_threshold ?? 5);
        $title = $isVariant ? "{$product->name} (SKU: {$variant->sku})" : $product->name;
        $sku = $target->sku ?: $product->sku;
        $editUrl = route('admin.products.edit', $product->id);

        $adminUsers = User::where('is_admin', true)->get();

        // 1. OUT OF STOCK CONDITION (Stock == 0)
        if ($stock === 0) {
            $hasUnreadOutAlert = $this->hasActiveAlert($target->id, $isVariant, 'out_of_stock');

            if (!$hasUnreadOutAlert) {
                foreach ($adminUsers as $admin) {
                    $this->dispatchNotification(
                        admin: $admin,
                        alertType: 'out_of_stock',
                        title: "🔴 Out of Stock Alert",
                        message: "Product '{$title}' is now completely out of stock (0 remaining).",
                        targetId: $target->id,
                        isVariant: $isVariant,
                        productId: $product->id,
                        sku: $sku,
                        stock: 0,
                        threshold: $threshold,
                        url: $editUrl
                    );
                }
            }
            return;
        }

        // 2. LOW STOCK CONDITION (0 < Stock <= Threshold)
        if ($stock <= $threshold && $stock > 0) {
            $hasUnreadLowAlert = $this->hasActiveAlert($target->id, $isVariant, 'low_stock');

            if (!$hasUnreadLowAlert) {
                foreach ($adminUsers as $admin) {
                    $this->dispatchNotification(
                        admin: $admin,
                        alertType: 'low_stock',
                        title: "⚠ Low Stock Alert",
                        message: "Product '{$title}' stock is down to {$stock} units (Threshold: {$threshold}).",
                        targetId: $target->id,
                        isVariant: $isVariant,
                        productId: $product->id,
                        sku: $sku,
                        stock: $stock,
                        threshold: $threshold,
                        url: $editUrl
                    );
                }
            }
            return;
        }

        // 3. HEALTHY / RESTOCKED CONDITION (Stock > Threshold)
        // Auto-resolve / mark read previous unread alerts for this item so future drop below threshold creates fresh alert
        if ($stock > $threshold) {
            $this->resolveAlertsForItem($target->id, $isVariant);
        }
    }

    private function hasActiveAlert(int $targetId, bool $isVariant, string $alertType): bool
    {
        return DB::table('notifications')
            ->whereNull('read_at')
            ->where('type', 'App\\Notifications\\LowStockNotification')
            ->get()
            ->contains(function ($n) use ($targetId, $isVariant, $alertType) {
                $data = json_decode($n->data, true) ?? [];
                return ($data['alert_type'] ?? '') === $alertType
                    && ($data['target_id'] ?? null) == $targetId
                    && ($data['is_variant'] ?? false) == $isVariant;
            });
    }

    private function resolveAlertsForItem(int $targetId, bool $isVariant): void
    {
        $notifications = DB::table('notifications')
            ->whereNull('read_at')
            ->where('type', 'App\\Notifications\\LowStockNotification')
            ->get();

        foreach ($notifications as $n) {
            $data = json_decode($n->data, true) ?? [];
            if (($data['target_id'] ?? null) == $targetId && ($data['is_variant'] ?? false) == $isVariant) {
                DB::table('notifications')
                    ->where('id', $n->id)
                    ->update(['read_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    private function dispatchNotification(
        User $admin,
        string $alertType,
        string $title,
        string $message,
        int $targetId,
        bool $isVariant,
        int $productId,
        string $sku,
        int $stock,
        int $threshold,
        string $url
    ): void {
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\LowStockNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $admin->id,
            'data' => json_encode([
                'alert_type' => $alertType,
                'title' => $title,
                'message' => $message,
                'target_id' => $targetId,
                'is_variant' => $isVariant,
                'product_id' => $productId,
                'sku' => $sku,
                'stock_quantity' => $stock,
                'threshold' => $threshold,
                'url' => $url,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
