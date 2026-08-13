<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getOrCreateCart(Request $request): Cart
    {
        $userId = auth()->id();
        $sessionId = $request->session()->getId();

        if ($userId) {
            $cart = Cart::firstOrCreate(['user_id' => $userId]);
        } else {
            $cart = Cart::firstOrCreate(['session_id' => $sessionId]);
        }

        return $cart->load(['items.product', 'items.variant']);
    }

    public function addItem(Cart $cart, int $productId, ?int $variantId, int $quantity): array
    {
        $product = Product::find($productId);
        if (!$product || !$product->is_active) {
            return ['success' => false, 'message' => 'Product is unavailable.'];
        }

        // Validate variants requirement
        if ($product->variants()->exists() && !$variantId) {
            return ['success' => false, 'message' => 'Please select a product option (variant).'];
        }

        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::where('product_id', $productId)->where('id', $variantId)->first();
            if (!$variant) {
                return ['success' => false, 'message' => 'Selected product variant is invalid.'];
            }
        }

        // Available stock validation
        $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
        if ($availableStock <= 0) {
            return ['success' => false, 'message' => "Sorry, '{$product->name}' is out of stock."];
        }

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        $currentQty = $existingItem ? $existingItem->quantity : 0;
        $newQty = $currentQty + $quantity;

        if ($newQty > $availableStock) {
            return [
                'success' => false,
                'message' => "Only {$availableStock} items are available in stock."
            ];
        }

        $price = $variant ? (float)$variant->price : (float)$product->price;

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $newQty,
                'price' => $price,
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        $cart->touch();

        return [
            'success' => true,
            'message' => "{$product->name} added to cart!",
            'summary' => $this->getCartSummary($cart->fresh())
        ];
    }

    public function updateQuantity(Cart $cart, int $cartItemId, int $quantity): array
    {
        $item = CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->first();
        if (!$item) {
            return ['success' => false, 'message' => 'Cart item not found.'];
        }

        if ($quantity < 1) {
            return ['success' => false, 'message' => 'Quantity must be at least 1.'];
        }

        $availableStock = $item->variant ? $item->variant->stock_quantity : $item->product->stock_quantity;
        if ($quantity > $availableStock) {
            return [
                'success' => false,
                'message' => "Only {$availableStock} items are available in stock."
            ];
        }

        $price = $item->variant ? (float)$item->variant->price : (float)$item->product->price;
        $item->update([
            'quantity' => $quantity,
            'price' => $price,
        ]);

        return [
            'success' => true,
            'message' => 'Cart updated successfully.',
            'summary' => $this->getCartSummary($cart->fresh())
        ];
    }

    public function removeItem(Cart $cart, int $cartItemId): array
    {
        $item = CartItem::where('cart_id', $cart->id)->where('id', $cartItemId)->first();
        if ($item) {
            $item->delete();
        }

        return [
            'success' => true,
            'message' => 'Product removed from cart.',
            'summary' => $this->getCartSummary($cart->fresh())
        ];
    }

    public function mergeGuestCart(User $user, string $sessionId): void
    {
        $guestCart = Cart::where('session_id', $sessionId)->with('items')->first();
        if (!$guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($user, $guestCart) {
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $guestItem) {
                $product = $guestItem->product;
                if (!$product || !$product->is_active) continue;

                $variant = $guestItem->variant;
                $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;

                $existingUserItem = CartItem::where('cart_id', $userCart->id)
                    ->where('product_id', $guestItem->product_id)
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();

                if ($existingUserItem) {
                    $mergedQty = min($availableStock, $existingUserItem->quantity + $guestItem->quantity);
                    $existingUserItem->update(['quantity' => $mergedQty]);
                } else {
                    $qty = min($availableStock, $guestItem->quantity);
                    if ($qty > 0) {
                        CartItem::create([
                            'cart_id' => $userCart->id,
                            'product_id' => $guestItem->product_id,
                            'product_variant_id' => $guestItem->product_variant_id,
                            'quantity' => $qty,
                            'price' => $guestItem->price,
                        ]);
                    }
                }
            }

            $guestCart->items()->delete();
            $guestCart->delete();
        });
    }

    public function getCartSummary(Cart $cart): array
    {
        $cart->load(['items.product', 'items.variant']);

        $itemsList = [];
        $subtotal = 0.0;
        $totalCount = 0;

        foreach ($cart->items as $item) {
            if (!$item->product) continue;

            $unitPrice = $item->variant ? (float)$item->variant->price : (float)$item->product->price;
            $lineTotal = $unitPrice * $item->quantity;
            $subtotal += $lineTotal;
            $totalCount += $item->quantity;

            $itemsList[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->product_variant_id,
                'name' => $item->product->name,
                'variant_name' => $item->variant ? $item->variant->sku : null,
                'image_url' => $item->product->featured_image_url,
                'quantity' => $item->quantity,
                'available_stock' => $item->variant ? $item->variant->stock_quantity : $item->product->stock_quantity,
                'unit_price' => $unitPrice,
                'formatted_unit_price' => format_price($unitPrice),
                'line_total' => $lineTotal,
                'formatted_line_total' => format_price($lineTotal),
                'url' => route('product.show', $item->product->slug),
            ];
        }

        $discount = 0.0;
        if ($cart->coupon_code) {
            $coupon = Coupon::where('code', $cart->coupon_code)->first();
            if ($coupon) {
                $discount = (float)$coupon->calculateDiscount($subtotal);
            }
        }

        $freeShippingThreshold = 5000.00;
        $shippingFee = ($subtotal >= $freeShippingThreshold) ? 0.0 : 100.0;

        $taxCalc = app(\App\Services\TaxService::class)->calculateTax($cart->items, $subtotal, $discount, $shippingFee);
        $tax = $taxCalc['tax_amount'];
        $grandTotal = ($subtotal - $discount) + $tax + $shippingFee;
        $shippingProgress = min(100, round(($subtotal / $freeShippingThreshold) * 100));

        return [
            'items' => $itemsList,
            'count' => $totalCount,
            'subtotal' => $subtotal,
            'formatted_subtotal' => format_price($subtotal),
            'discount' => $discount,
            'formatted_discount' => format_price($discount),
            'tax' => $tax,
            'formatted_tax' => format_price($tax),
            'tax_name' => $taxCalc['tax_name'],
            'tax_rate' => $taxCalc['tax_rate'],
            'tax_enabled' => $taxCalc['tax_enabled'],
            'shipping_fee' => $shippingFee,
            'formatted_shipping_fee' => format_price($shippingFee),
            'grand_total' => $grandTotal,
            'formatted_grand_total' => format_price($grandTotal),
            'coupon_code' => $cart->coupon_code,
            'shipping_progress' => $shippingProgress,
            'free_shipping_threshold' => $freeShippingThreshold,
        ];
    }
}
