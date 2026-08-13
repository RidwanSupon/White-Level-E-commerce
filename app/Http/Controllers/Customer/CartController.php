<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $cart = $this->cartService->getOrCreateCart($request);
        $summary = $this->cartService->getCartSummary($cart);

        return view('customer.cart', compact('cart', 'summary'));
    }

    public function data(Request $request)
    {
        $cart = $this->cartService->getOrCreateCart($request);
        $summary = $this->cartService->getCartSummary($cart);

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cartService->getOrCreateCart($request);
        $result = $this->cartService->addItem(
            $cart,
            $validated['product_id'],
            $validated['product_variant_id'] ?? null,
            $validated['quantity']
        );

        if (!$result['success']) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $result['message']], 422);
            }
            return back()->with('error', $result['message']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'summary' => $result['summary'],
            ]);
        }

        return back()->with('success', $result['message']);
    }

    public function buyNow(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cartService->getOrCreateCart($request);
        $result = $this->cartService->addItem(
            $cart,
            $validated['product_id'],
            $validated['product_variant_id'] ?? null,
            $validated['quantity']
        );

        if (!$result['success']) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $result['message']], 422);
            }
            return back()->with('error', $result['message']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('checkout.index'),
            ]);
        }

        return redirect()->route('checkout.index');
    }

    public function update(Request $request, int $id)
    {
        $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $cart = $this->cartService->getOrCreateCart($request);

        $result = $this->cartService->updateQuantity($cart, $id, (int)$request->input('quantity'));

        if (!$result['success']) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $result['message']], 422);
            }
            return back()->with('error', $result['message']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'summary' => $result['summary'],
            ]);
        }

        return back()->with('success', $result['message']);
    }

    public function destroy(Request $request, int $id)
    {
        $cart = $this->cartService->getOrCreateCart($request);
        $result = $this->cartService->removeItem($cart, $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'summary' => $result['summary'],
            ]);
        }

        return back()->with('success', $result['message']);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['coupon_code' => ['required', 'string']]);
        $code = strtoupper(trim($request->input('coupon_code')));
        $cart = $this->cartService->getOrCreateCart($request);

        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon || !$coupon->isValid($cart->subtotal)) {
            return back()->with('error', 'Invalid or expired promo coupon code.');
        }

        $cart->update(['coupon_code' => $code]);
        return back()->with('success', "Coupon '{$code}' applied successfully!");
    }
}
