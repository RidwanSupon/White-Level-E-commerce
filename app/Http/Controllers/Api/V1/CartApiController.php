<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::where('user_id', auth()->id())->with(['items.product', 'items.variant'])->first();

        if (!$cart) {
            return response()->json(['items' => [], 'subtotal' => '0.00']);
        }

        return response()->json([
            'id' => $cart->id,
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'price' => format_price($item->price),
                    'quantity' => $item->quantity,
                    'total' => format_price($item->price * $item->quantity),
                ];
            }),
            'subtotal' => format_price($cart->subtotal),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $product->id],
            ['quantity' => $validated['quantity'], 'price' => $product->price]
        );

        return response()->json(['message' => 'Product added to cart successfully.']);
    }

    public function destroy(int $itemId)
    {
        $cart = Cart::where('user_id', auth()->id())->firstOrFail();
        $cart->items()->where('id', $itemId)->delete();

        return response()->json(['message' => 'Item removed from cart.']);
    }
}
