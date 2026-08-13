<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->where('stock_quantity', '>', 0)->get();
        $outOfStockProducts = Product::where('stock_quantity', 0)->get();
        $notifications = auth()->user()->notifications()->latest()->get();

        return view('admin.notifications.index', compact('lowStockProducts', 'outOfStockProducts', 'notifications'));
    }

    public function markAsRead(string $id)
    {
        try {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->markAsRead();

            $data = $notification->data;
            if (is_string($data)) {
                $data = json_decode($data, true) ?? [];
            } elseif (!is_array($data)) {
                $data = [];
            }

            if (!empty($data['url'])) {
                return redirect($data['url'])->with('success', 'Notification marked as read.');
            }

            if (!empty($data['product_id'])) {
                return redirect()->route('admin.products.edit', $data['product_id'])->with('success', 'Notification marked as read.');
            }

            return back()->with('success', 'Notification marked as read.');
        } catch (\Throwable $e) {
            logger()->error("Notification markAsRead error: {$e->getMessage()}", ['notification_id' => $id]);
            return back()->with('success', 'Notification processed.');
        }
    }
}
