<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Services\ManualPaymentService;
use App\Services\Payments\BkashGateway;
use App\Services\Payments\CodGateway;
use App\Services\Payments\NagadGateway;
use App\Services\Payments\SSLCommerzGateway;
use App\Services\Payments\StripeGateway;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $cart = Cart::where($userId ? ['user_id' => $userId] : ['session_id' => $request->session()->getId()])
            ->with(['items.product', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your shopping cart is empty.');
        }

        $subtotal = $cart->subtotal;
        $discount = 0.00;
        if ($cart->coupon_code) {
            $coupon = Coupon::where('code', $cart->coupon_code)->first();
            if ($coupon) {
                $discount = $coupon->calculateDiscount($subtotal);
            }
        }

        $userAddresses = auth()->check() ? auth()->user()->addresses : collect();
        $defaultCity = $userAddresses->firstWhere('is_default_shipping', true)?->city ?? 'Dhaka';

        $initialZone = ShippingZone::matchZone($defaultCity);

        $bkashEnabled = (bool) setting('bkash_enabled', true);
        $nagadEnabled = (bool) setting('nagad_enabled', true);
        $bkashNumber = setting('bkash_number', '01700000000');
        $nagadNumber = setting('nagad_number', '01800000000');
        $bkashAccountType = setting('bkash_account_type', 'Personal');
        $nagadAccountType = setting('nagad_account_type', 'Personal');
        $bkashAccountName = setting('bkash_account_name', setting('site_name', 'LuxeCart Store'));
        $nagadAccountName = setting('nagad_account_name', setting('site_name', 'LuxeCart Store'));
        $bkashInstructions = setting('bkash_instructions', "1. Open bKash App.\n2. Select Send Money.\n3. Send exact amount to the number above.\n4. Complete transaction & copy Transaction ID.");
        $nagadInstructions = setting('nagad_instructions', "1. Open Nagad App.\n2. Select Send Money.\n3. Send exact amount to the number above.\n4. Complete transaction & copy Transaction ID.");
        $proofRequired = (bool) setting('payment_proof_required', false);

        return view('customer.checkout', compact(
            'cart', 'subtotal', 'discount', 'userAddresses', 'initialZone',
            'bkashEnabled', 'nagadEnabled', 'bkashNumber', 'nagadNumber',
            'bkashAccountType', 'nagadAccountType', 'bkashAccountName', 'nagadAccountName',
            'bkashInstructions', 'nagadInstructions', 'proofRequired'
        ));
    }

    public function calculateShipping(Request $request)
    {
        $city = $request->input('city', 'Dhaka');
        $shippingZone = ShippingZone::matchZone($city);

        $userId = auth()->id();
        $cart = Cart::where($userId ? ['user_id' => $userId] : ['session_id' => $request->session()->getId()])
            ->with(['items.product', 'items.variant'])
            ->first();

        $subtotal = $cart ? (float) $cart->subtotal : 0.00;
        $discount = 0.00;
        if ($cart && $cart->coupon_code) {
            $coupon = Coupon::where('code', $cart->coupon_code)->first();
            if ($coupon) {
                $discount = (float) $coupon->calculateDiscount($subtotal);
            }
        }

        $deliveryCharge = (float) $shippingZone->delivery_charge;
        $taxCalc = app(\App\Services\TaxService::class)->calculateTax($cart ? $cart->items : [], $subtotal, $discount, $deliveryCharge);
        $tax = $taxCalc['tax_amount'];
        $grandTotal = ($subtotal - $discount) + $deliveryCharge + $tax;

        $advanceRequired = (bool) $shippingZone->advance_payment_required;
        $advanceAmount = $advanceRequired ? $deliveryCharge : 0.00;
        $remainingAmount = $advanceRequired ? ($grandTotal - $advanceAmount) : $grandTotal;

        return response()->json([
            'success' => true,
            'zone_id' => $shippingZone->id,
            'zone_name' => $shippingZone->name,
            'zone_type' => $shippingZone->zone_type,
            'delivery_charge' => $deliveryCharge,
            'formatted_delivery_charge' => format_price($deliveryCharge),
            'advance_required' => $advanceRequired,
            'advance_amount' => $advanceAmount,
            'formatted_advance_amount' => format_price($advanceAmount),
            'remaining_amount' => $remainingAmount,
            'formatted_remaining_amount' => format_price($remainingAmount),
            'tax_amount' => $tax,
            'formatted_tax_amount' => format_price($tax),
            'tax_name' => $taxCalc['tax_name'],
            'tax_rate' => $taxCalc['tax_rate'],
            'tax_enabled' => $taxCalc['tax_enabled'],
            'grand_total' => $grandTotal,
            'formatted_grand_total' => format_price($grandTotal),
        ]);
    }

    public function process(Request $request, ManualPaymentService $manualPaymentService)
    {
        $proofRequired = (bool) setting('payment_proof_required', false);

        $city = $request->input('city', 'Dhaka');
        $shippingZone = ShippingZone::matchZone($city);
        $advanceRequired = (bool) $shippingZone->advance_payment_required;

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'in:cod,stripe,sslcommerz,bkash,nagad'],
            'notes' => ['nullable', 'string'],
        ];

        // Outside Dhaka Advance Payment requires bKash/Nagad manual payment transaction submission
        if ($advanceRequired || in_array($request->input('payment_method'), ['bkash', 'nagad'])) {
            $rules['transaction_id'] = ['required', 'string', 'max:100', 'min:4'];
            if ($proofRequired) {
                $rules['payment_proof'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
            } else {
                $rules['payment_proof'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
            }
        }

        $validated = $request->validate($rules);

        $userId = auth()->id();
        $cart = Cart::where($userId ? ['user_id' => $userId] : ['session_id' => $request->session()->getId()])
            ->with(['items.product', 'items.variant'])
            ->firstOrFail();

        // Perform final pre-checkout stock validation
        foreach ($cart->items as $item) {
            $availableStock = $item->variant ? $item->variant->stock_quantity : $item->product->stock_quantity;
            if ($item->quantity > $availableStock) {
                return back()->with('error', "Stock validation failed: Only {$availableStock} units of '{$item->product->name}' are available.");
            }
        }

        $subtotal = (float) $cart->subtotal;
        $discount = 0.00;
        if ($cart->coupon_code) {
            $coupon = Coupon::where('code', $cart->coupon_code)->first();
            if ($coupon) {
                $discount = (float) $coupon->calculateDiscount($subtotal);
            }
        }

        $deliveryCharge = (float) $shippingZone->delivery_charge;
        $taxCalc = app(\App\Services\TaxService::class)->calculateTax($cart->items, $subtotal, $discount, $deliveryCharge);
        $tax = $taxCalc['tax_amount'];
        $grandTotal = ($subtotal - $discount) + $deliveryCharge + $tax;

        $advanceAmount = $advanceRequired ? $deliveryCharge : 0.00;
        $remainingAmount = $advanceRequired ? ($grandTotal - $advanceAmount) : $grandTotal;

        $addressJson = [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address_line_1'],
            'city' => $validated['city'],
        ];

        $isManualPayment = $advanceRequired || in_array($validated['payment_method'], ['bkash', 'nagad']);
        $initialOrderStatus = $isManualPayment ? 'payment_verification_pending' : 'pending';
        $initialPaymentStatus = $isManualPayment ? 'verification_pending' : ($validated['payment_method'] === 'cod' ? 'pending' : 'paid');

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'user_id' => $userId,
            'status' => $initialOrderStatus,
            'payment_status' => $initialPaymentStatus,
            'shipping_status' => 'pending',
            'shipping_zone_id' => $shippingZone->id,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'shipping_fee' => $deliveryCharge,
            'delivery_charge' => $deliveryCharge,
            'delivery_advance_required' => $advanceRequired,
            'delivery_advance_amount' => $advanceAmount,
            'delivery_advance_paid' => 0.00,
            'remaining_amount' => $remainingAmount,
            'tax_amount' => $tax,
            'tax_name' => $taxCalc['tax_name'],
            'tax_rate' => $taxCalc['tax_rate'],
            'tax_snapshot_json' => $taxCalc['snapshot'],
            'grand_total' => $grandTotal,
            'payment_method' => $validated['payment_method'],
            'shipping_address_json' => $addressJson,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product->name,
                'variant_name' => $item->variant?->sku,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total' => $item->price * $item->quantity,
            ]);

            // Decrement product stock
            if ($item->variant) {
                $item->variant->decrement('stock_quantity', $item->quantity);
                app(\App\Services\StockAlertService::class)->checkStockAndNotify($item->variant->fresh());
            } else {
                $item->product->decrement('stock_quantity', $item->quantity);
                app(\App\Services\StockAlertService::class)->checkStockAndNotify($item->product->fresh());
            }
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'status' => $initialOrderStatus,
            'notes' => $advanceRequired 
                ? "Order placed from Outside Dhaka requiring ৳{$advanceAmount} Advance Delivery Payment." 
                : 'Order placed successfully.',
        ]);

        // Process Manual Mobile Payment Submission
        if ($isManualPayment) {
            $paymentType = $advanceRequired ? 'delivery_advance' : 'full_order';

            $paymentResult = $manualPaymentService->submitPayment($order, array_merge($request->all(), [
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'],
                'payment_proof' => $request->file('payment_proof'),
                'submitted_amount' => $request->input('submitted_amount', $advanceRequired ? $advanceAmount : $grandTotal),
            ]), $paymentType);

            if (!$paymentResult['success']) {
                $order->delete(); // Rollback order creation if payment submit failed
                return back()->withInput()->withErrors(['transaction_id' => $paymentResult['message']]);
            }
        } else {
            // Process Automatic / COD Payment Gateway
            $gatewayClass = match ($validated['payment_method']) {
                'stripe' => new StripeGateway(),
                'sslcommerz' => new SSLCommerzGateway(),
                default => new CodGateway(),
            };

            $result = $gatewayClass->processPayment($order, $validated);

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $result['transaction_id'],
                'gateway' => $gatewayClass->getName(),
                'amount' => $grandTotal,
                'currency' => setting('currency_code', 'BDT'),
                'status' => $result['status'],
                'payload_json' => $result,
            ]);
        }

        // Clear cart
        $cart->items()->delete();
        $cart->update(['coupon_code' => null]);

        $successMsg = $advanceRequired 
            ? "Order placed! Your ৳{$advanceAmount} advance delivery payment has been submitted and is awaiting admin verification." 
            : 'Order placed successfully!';

        return redirect()->route('customer.orders.show', $order->id)->with('success', $successMsg);
    }
}
