<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $orders = Order::where('user_id', $user->id)->with('items')->latest()->get();
        $recentOrder = $orders->first();

        return view('customer.account.dashboard', compact('user', 'orders', 'recentOrder'));
    }

    public function showOrder(int $id)
    {
        $order = Order::where('id', $id)
            ->where(function ($q) {
                if (auth()->check()) {
                    $q->where('user_id', auth()->id());
                }
            })
            ->with(['items.product', 'statusHistories', 'payment'])
            ->firstOrFail();

        return view('customer.account.order-details', compact('order'));
    }
}
