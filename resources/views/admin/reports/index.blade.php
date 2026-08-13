@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Analytics & Sales Reports</h1>
            <p class="text-xs text-slate-400 mt-0.5">Executive sales metrics, top selling products, and CSV exports</p>
        </div>
        <a href="{{ route('admin.reports.export') }}" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-500/20">
            Export Sales CSV 📄
        </a>
    </div>

    <!-- Executive KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-2">
            <span class="text-[11px] font-bold uppercase text-slate-400">Total Paid Revenue</span>
            <h3 class="text-2xl font-bold text-white font-display">{{ format_price($totalRevenue) }}</h3>
        </div>
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-2">
            <span class="text-[11px] font-bold uppercase text-slate-400">Total Orders Placed</span>
            <h3 class="text-2xl font-bold text-white font-display">{{ $totalOrders }}</h3>
        </div>
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-2">
            <span class="text-[11px] font-bold uppercase text-slate-400">Delivered Orders</span>
            <h3 class="text-2xl font-bold text-emerald-400 font-display">{{ $deliveredOrders }}</h3>
        </div>
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-2">
            <span class="text-[11px] font-bold uppercase text-slate-400">Pending Orders</span>
            <h3 class="text-2xl font-bold text-amber-400 font-display">{{ $pendingOrders }}</h3>
        </div>
    </div>

    <!-- Top Products & Recent Paid Orders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 font-bold text-white text-base">Top 5 Best Selling Products</div>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                        <th class="py-3 px-4">Product Name</th>
                        <th class="py-3 px-4">Units Sold</th>
                        <th class="py-3 px-4 text-right">Total Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($topSellingProducts as $item)
                        <tr class="hover:bg-slate-900/40">
                            <td class="py-3 px-4 font-bold text-white">{{ $item->product_name }}</td>
                            <td class="py-3 px-4 text-brand-400 font-bold">{{ $item->total_qty }} units</td>
                            <td class="py-3 px-4 text-right font-bold text-white">{{ format_price($item->total_sales) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 font-bold text-white text-base">Recent Confirmed Transactions</div>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                        <th class="py-3 px-4">Order #</th>
                        <th class="py-3 px-4">Gateway</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($recentPaidOrders as $order)
                        <tr class="hover:bg-slate-900/40">
                            <td class="py-3 px-4 font-bold text-white">{{ $order->order_number }}</td>
                            <td class="py-3 px-4 text-slate-400 uppercase font-bold text-[10px]">{{ $order->payment_method }}</td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-400">{{ format_price($order->grand_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tax Collected Reports & Rate Breakdown -->
    <div class="space-y-4">
        <h3 class="font-bold text-white text-base font-display">Tax Collection Summary & Breakdown</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">Total Tax Collected</span>
                <h4 class="text-xl font-bold text-emerald-400 font-display">{{ format_price($totalTaxCollected) }}</h4>
            </div>
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">Tax Collected This Month</span>
                <h4 class="text-xl font-bold text-brand-400 font-display">{{ format_price($taxCollectedThisMonth) }}</h4>
            </div>
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">Tax Collected This Year</span>
                <h4 class="text-xl font-bold text-white font-display">{{ format_price($taxCollectedThisYear) }}</h4>
            </div>
        </div>

        <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 font-bold text-white text-sm">Tax Rate Breakdown</div>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                        <th class="py-3 px-4">Tax Name / Rate</th>
                        <th class="py-3 px-4">Taxable Base Amount</th>
                        <th class="py-3 px-4">Tax Collected</th>
                        <th class="py-3 px-4 text-right">Orders Count</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($taxBreakdown as $tb)
                        <tr class="hover:bg-slate-900/40">
                            <td class="py-3 px-4 font-bold text-white">
                                {{ $tb->tax_name ?? 'Default Tax' }} ({{ number_format($tb->tax_rate ?? 0, 2) }}%)
                            </td>
                            <td class="py-3 px-4 text-slate-300 font-mono">{{ format_price($tb->taxable_amount ?? 0) }}</td>
                            <td class="py-3 px-4 font-extrabold text-emerald-400 font-mono">{{ format_price($tb->tax_collected ?? 0) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-400">{{ $tb->total_orders }} orders</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-500 text-xs italic">No tax collected yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
