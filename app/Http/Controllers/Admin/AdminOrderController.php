<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where('order_number', 'like', "%{$search}%");
        }

        $orders = $query->latest()->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = Order::with(['items.product', 'statusHistories.user', 'payment', 'user'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,processing,packed,shipped,out_for_delivery,delivered,cancelled,returned,refunded'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $order->update([
            'status' => $validated['status'],
            'payment_status' => $validated['status'] === 'delivered' ? 'paid' : $order->payment_status,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? "Status updated from {$oldStatus} to {$validated['status']}",
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'order.status_updated',
            'module' => 'orders',
            'record_id' => $order->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $validated['status']],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Order #{$order->order_number} status updated to {$validated['status']}!");
    }

    public function invoice(int $id)
    {
        $order = Order::with(['items.product', 'payment', 'user'])->findOrFail($id);
        return view('admin.orders.invoice', compact('order'));
    }
}
