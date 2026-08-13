<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::where('user_id', auth()->id())->with('product')->get();
        return view('customer.wishlist', compact('wishlists'));
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $existing = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            $status = 'removed';
            $msg = 'Item removed from your wishlist.';
        } else {
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $validated['product_id'],
            ]);
            $status = 'added';
            $msg = 'Item added to your wishlist!';
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => $status, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    public function moveToCart(int $id)
    {
        $wishlist = Wishlist::where('user_id', auth()->id())->findOrFail($id);

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        
        CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $wishlist->product_id],
            ['quantity' => 1, 'price' => $wishlist->product->price]
        );

        $wishlist->delete();

        return redirect()->route('cart.index')->with('success', 'Item moved to shopping cart!');
    }

    public function destroy(int $id)
    {
        $wishlist = Wishlist::where('user_id', auth()->id())->findOrFail($id);
        $wishlist->delete();

        return back()->with('success', 'Item removed from wishlist.');
    }
}
