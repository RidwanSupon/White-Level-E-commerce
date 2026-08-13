<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function index()
    {
        $totalRevenue = Order::where('payment_status', 'paid')->sum('grand_total');
        $totalOrders = Order::count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $pendingOrders = Order::where('status', 'pending')->count();

        $topSellingProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total) as total_sales'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $totalTaxCollected = Order::sum('tax_amount');
        $taxCollectedThisMonth = Order::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('tax_amount');
        $taxCollectedThisYear = Order::whereYear('created_at', now()->year)->sum('tax_amount');

        $taxBreakdown = Order::select('tax_name', 'tax_rate', DB::raw('SUM(subtotal - discount_amount) as taxable_amount'), DB::raw('SUM(tax_amount) as tax_collected'), DB::raw('COUNT(id) as total_orders'))
            ->groupBy('tax_name', 'tax_rate')
            ->get();

        $recentPaidOrders = Order::where('payment_status', 'paid')->latest()->take(5)->get();

        return view('admin.reports.index', compact(
            'totalRevenue', 'totalOrders', 'deliveredOrders', 'pendingOrders',
            'topSellingProducts', 'recentPaidOrders', 'totalTaxCollected',
            'taxCollectedThisMonth', 'taxCollectedThisYear', 'taxBreakdown'
        ));
    }

    public function export()
    {
        $orders = Order::with('user')->get();
        $output = "order_number,customer,total,payment_status,fulfillment_status,date\n";

        foreach ($orders as $order) {
            $customer = str_replace('"', '""', $order->user?->name ?? 'Guest');
            $output .= "\"{$order->order_number}\",\"{$customer}\",\"{$order->grand_total}\",\"{$order->payment_status}\",\"{$order->status}\",\"{$order->created_at->format('Y-m-d H:i:s')}\"\n";
        }

        $fileName = 'sales_report_' . date('Y_m_d_His') . '.csv';

        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
