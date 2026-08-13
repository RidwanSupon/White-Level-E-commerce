@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Dashboard Overview</h1>
            <p class="text-slate-400 text-xs mt-1">Real-time enterprise platform metrics and live store performance</p>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20">
            Configure White-Label Branding
        </a>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Sales Revenue</span>
            <div class="text-2xl font-bold text-white font-display mt-2">{{ format_price($metrics['total_sales']) }}</div>
            <span class="text-[11px] text-emerald-400 mt-1 block">Today: {{ format_price($metrics['today_sales']) }}</span>
        </div>

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Orders</span>
            <div class="text-2xl font-bold text-white font-display mt-2">{{ $metrics['total_orders'] }}</div>
            <span class="text-[11px] text-amber-400 mt-1 block">{{ $metrics['pending_orders'] }} Orders Pending</span>
        </div>

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Customers</span>
            <div class="text-2xl font-bold text-white font-display mt-2">{{ $metrics['total_customers'] }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Registered Users</span>
        </div>

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Catalog Inventory</span>
            <div class="text-2xl font-bold text-white font-display mt-2">{{ $metrics['total_products'] }}</div>
            <span class="text-[11px] text-rose-400 mt-1 block">{{ $metrics['low_stock_count'] }} Low Stock Warnings</span>
        </div>
    </div>

    <!-- Recent Orders & Inventory Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-slate-950 rounded-3xl border border-slate-800/80 p-6">
            <h3 class="font-bold text-white text-base mb-4">Recent Orders</h3>
            @if($recentOrders->isEmpty())
                <p class="text-xs text-slate-500">No orders placed yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($recentOrders as $order)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-900 border border-slate-800 text-xs">
                            <div>
                                <span class="font-bold text-white block">{{ $order->order_number }}</span>
                                <span class="text-slate-400">{{ $order->user?->name ?? 'Guest' }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-white block">{{ format_price($order->grand_total) }}</span>
                                <span class="text-amber-400 uppercase text-[10px] font-bold">{{ $order->status }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-slate-950 rounded-3xl border border-slate-800/80 p-6">
            <h3 class="font-bold text-white text-base mb-4">Low Stock Warnings</h3>
            @if($lowStockProducts->isEmpty())
                <p class="text-xs text-emerald-400">All inventory items are sufficiently stocked.</p>
            @else
                <div class="space-y-3">
                    @foreach($lowStockProducts as $prod)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-900 border border-slate-800 text-xs">
                            <div>
                                <span class="font-bold text-white block line-clamp-1">{{ $prod->name }}</span>
                                <span class="text-slate-500">SKU: {{ $prod->sku }}</span>
                            </div>
                            <span class="px-2.5 py-1 bg-rose-500/10 text-rose-400 rounded-full font-bold text-[11px]">
                                {{ $prod->stock_quantity }} remaining
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
