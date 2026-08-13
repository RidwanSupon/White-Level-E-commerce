@extends('layouts.admin')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Order {{ $order->order_number }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold">Print Invoice 📄</a>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-slate-400 hover:text-white flex items-center">← Back to Orders</a>
        </div>
    </div>

    <!-- Status Change Action Bar -->
    <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
        <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Update Fulfillment Status</h3>
        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="flex flex-col sm:flex-row gap-4 items-end">
            @csrf
            @method('PATCH')
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-300 mb-1">Target Status</label>
                <select name="status" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-white outline-none focus:border-brand-500">
                    @foreach(['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'returned', 'refunded'] as $st)
                        <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-300 mb-1">Status Notes</label>
                <input type="text" name="notes" placeholder="Optional internal notes..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-white outline-none focus:border-brand-500">
            </div>
            <button type="submit" class="px-6 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md">Update Status</button>
        </form>
    </div>

    <!-- Items & Shipping Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-3 text-xs">
            <h4 class="font-bold text-white text-sm border-b border-slate-800 pb-2">Customer & Delivery Info</h4>
            <p><strong class="text-slate-400">Name:</strong> <span class="text-white">{{ $order->shipping_address_json['full_name'] ?? 'N/A' }}</span></p>
            <p><strong class="text-slate-400">Email:</strong> <span class="text-white">{{ $order->shipping_address_json['email'] ?? 'N/A' }}</span></p>
            <p><strong class="text-slate-400">Phone:</strong> <span class="text-white">{{ $order->shipping_address_json['phone'] ?? 'N/A' }}</span></p>
            <p><strong class="text-slate-400">Address:</strong> <span class="text-white">{{ $order->shipping_address_json['address'] ?? 'N/A' }}, {{ $order->shipping_address_json['city'] ?? '' }}</span></p>
        </div>

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-3 text-xs">
            <h4 class="font-bold text-white text-sm border-b border-slate-800 pb-2">Payment & Delivery Charges</h4>
            <p><strong class="text-slate-400">Payment Gateway:</strong> <span class="text-white uppercase font-bold">{{ $order->payment_method }}</span></p>
            <p><strong class="text-slate-400">Payment Status:</strong> <span class="text-emerald-400 uppercase font-bold">{{ $order->payment_status }}</span></p>
            @if($order->delivery_advance_required)
                <p><strong class="text-slate-400">Advance Delivery Paid:</strong> <span class="text-amber-400 font-extrabold font-mono">{{ format_price($order->delivery_advance_paid) }} {{ $order->delivery_advance_paid > 0 ? '✓' : '(Pending Verification)' }}</span></p>
                <p><strong class="text-slate-400">Remaining Amount Due:</strong> <span class="text-white font-extrabold font-mono text-sm">{{ format_price($order->remaining_amount) }} (COD)</span></p>
            @endif
            <p class="pt-2 border-t border-slate-900"><strong class="text-slate-400">Grand Total:</strong> <span class="text-white font-bold text-base">{{ format_price($order->grand_total) }}</span></p>
        </div>
    </div>
</div>
@endsection
