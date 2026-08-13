<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'total_sales' => Order::where('payment_status', 'paid')->sum('grand_total'),
            'today_sales' => Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('grand_total'),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_customers' => User::where('is_admin', false)->count(),
            'total_products' => Product::count(),
            'low_stock_count' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
        ];

        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->take(5)->get();

        return view('admin.dashboard', compact('metrics', 'recentOrders', 'lowStockProducts'));
    }
}
