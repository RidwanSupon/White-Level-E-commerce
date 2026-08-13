@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Sales & Orders Management</h1>
            <p class="text-xs text-slate-400 mt-0.5">Filter, track, process status transitions, and issue invoices</p>
        </div>
    </div>

    <!-- Status Tabs Filter -->
    <div class="flex gap-2 overflow-x-auto pb-2 text-xs">
        <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded-xl border font-bold transition-all {{ !request('status') ? 'bg-brand-500 text-white border-brand-500' : 'bg-slate-950 text-slate-400 border-slate-800' }}">All Orders</a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-xl border font-bold transition-all {{ request('status') == 'pending' ? 'bg-brand-500 text-white border-brand-500' : 'bg-slate-950 text-slate-400 border-slate-800' }}">Pending</a>
        <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}" class="px-4 py-2 rounded-xl border font-bold transition-all {{ request('status') == 'confirmed' ? 'bg-brand-500 text-white border-brand-500' : 'bg-slate-950 text-slate-400 border-slate-800' }}">Confirmed</a>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="px-4 py-2 rounded-xl border font-bold transition-all {{ request('status') == 'processing' ? 'bg-brand-500 text-white border-brand-500' : 'bg-slate-950 text-slate-400 border-slate-800' }}">Processing</a>
        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="px-4 py-2 rounded-xl border font-bold transition-all {{ request('status') == 'shipped' ? 'bg-brand-500 text-white border-brand-500' : 'bg-slate-950 text-slate-400 border-slate-800' }}">Shipped</a>
        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="px-4 py-2 rounded-xl border font-bold transition-all {{ request('status') == 'delivered' ? 'bg-brand-500 text-white border-brand-500' : 'bg-slate-950 text-slate-400 border-slate-800' }}">Delivered</a>
    </div>

    <!-- Orders Table -->
    <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                    <th class="py-3.5 px-4">Order #</th>
                    <th class="py-3.5 px-4">Customer</th>
                    <th class="py-3.5 px-4">Date</th>
                    <th class="py-3.5 px-4">Total</th>
                    <th class="py-3.5 px-4">Payment</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($orders as $order)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3.5 px-4 font-bold text-white">{{ $order->order_number }}</td>
                        <td class="py-3.5 px-4 text-slate-300">{{ $order->user?->name ?? 'Guest' }}</td>
                        <td class="py-3.5 px-4 text-slate-500">{{ $order->created_at->format('M d, Y') }}</td>
                        <td class="py-3.5 px-4 font-bold text-white">{{ format_price($order->grand_total) }}</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                                {{ $order->payment_status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase bg-slate-800 text-slate-300">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg font-bold">Process Order</a>
                            <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg">Invoice 📄</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-800">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
