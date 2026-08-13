<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(int $id)
    {
        $order = Order::where('user_id', auth()->id())
            ->where('id', $id)
            ->with(['items.product', 'statusHistories'])
            ->firstOrFail();

        return response()->json($order);
    }
}
