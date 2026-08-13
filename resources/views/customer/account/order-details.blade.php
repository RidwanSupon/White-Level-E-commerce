@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900">Order #{{ $order->order_number }}</h1>
            <p class="text-xs text-slate-500 mt-1">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
        </div>
        <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="px-4 py-2 bg-slate-900 text-white font-bold text-xs rounded-full shadow hover:bg-slate-800 transition-colors">
            Print Invoice 📄
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- Manual Payment Verification Card (if bKash / Nagad) -->
    @if($order->manualPayment)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3 flex items-center justify-between">
                <span>Payment Verification Status</span>
                <span class="text-xs text-slate-500 font-normal">Method: {{ strtoupper($order->manualPayment->payment_method) }}</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div>
                    <span class="text-[11px] text-slate-500 font-semibold block">Payment Method:</span>
                    <span class="font-extrabold text-slate-900 text-sm uppercase mt-0.5 block">{{ $order->manualPayment->payment_method }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 font-semibold block">Submitted Transaction ID:</span>
                    <span class="font-mono font-bold text-slate-900 text-sm mt-0.5 block select-all">{{ $order->manualPayment->transaction_id }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 font-semibold block">Payable Amount:</span>
                    <span class="font-extrabold text-brand-600 text-sm mt-0.5 block">{{ format_price($order->manualPayment->amount) }}</span>
                </div>
            </div>

            <!-- Status Banner -->
            @if($order->manualPayment->status === 'verification_pending')
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl text-xs space-y-1">
                    <div class="flex items-center gap-2 font-bold text-amber-900 text-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                        ⏳ Verification Pending
                    </div>
                    <p class="text-slate-600">Our finance team is currently verifying your {{ strtoupper($order->manualPayment->payment_method) }} transaction. Once verified, your order status will automatically update to Confirmed.</p>
                </div>
            @elseif($order->manualPayment->status === 'verified')
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs space-y-1">
                    <div class="flex items-center gap-2 font-bold text-emerald-900 text-sm">
                        <span>✓</span> Payment Verified & Confirmed
                    </div>
                    <p class="text-slate-600">Verified on {{ $order->manualPayment->verified_at?->format('d M Y, h:i A') }}. Your order is now being processed for packing and shipping.</p>
                </div>
            @else
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs space-y-2">
                    <div class="flex items-center gap-2 font-bold text-rose-900 text-sm">
                        <span>✕</span> Payment Verification Rejected
                    </div>
                    <p class="text-slate-600 font-medium">Rejection Reason: <strong class="text-rose-900">{{ $order->manualPayment->rejection_reason ?? 'Transaction could not be verified.' }}</strong></p>
                    <p class="text-[11px] text-slate-500">Please contact support or re-check your Transaction ID if you believe this was in error.</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Visual Tracking Progress Steps -->
    @php
        $statuses = ['pending' => 'Order Placed', 'payment_verification_pending' => 'Verification Pending', 'confirmed' => 'Confirmed', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
        $statusKeys = array_keys($statuses);
        $currentIndex = array_search($order->status, $statusKeys);
        if ($currentIndex === false) $currentIndex = 0;
    @endphp

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <h3 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">Delivery & Payment Tracking Timeline</h3>
        
        <div class="relative flex items-center justify-between max-w-2xl mx-auto">
            <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-slate-100 -z-0"></div>
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-brand-500 transition-all duration-500 -z-0" style="width: {{ ($currentIndex / (count($statuses) - 1)) * 100 }}%;"></div>

            @foreach($statuses as $key => $label)
                @php $stepIdx = array_search($key, $statusKeys); @endphp
                <div class="flex flex-col items-center gap-2 relative z-10 bg-white px-2">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs transition-all {{ $stepIdx <= $currentIndex ? 'bg-brand-500 text-white shadow-md shadow-brand-500/30' : 'bg-slate-100 text-slate-400' }}">
                        @if($stepIdx < $currentIndex) ✓ @else {{ $stepIdx + 1 }} @endif
                    </div>
                    <span class="text-[11px] font-bold {{ $stepIdx <= $currentIndex ? 'text-slate-900' : 'text-slate-400' }}">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Items Summary -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
        <h3 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">Purchased Items</h3>
        <div class="divide-y divide-slate-100">
            @foreach($order->items as $item)
                <div class="py-3 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <img src="{{ $item->product?->featured_image }}" onerror="this.onerror=null;this.src='/images/placeholder.png';" class="w-12 h-12 rounded-xl object-cover border border-slate-100">
                        <div>
                            <span class="font-bold text-slate-900 block text-sm">{{ $item->product_name }}</span>
                            <span class="text-slate-500">Qty: {{ $item->quantity }} × {{ format_price($item->price) }}</span>
                        </div>
                    </div>
                    <span class="font-bold text-slate-900 text-sm">{{ format_price($item->total) }}</span>
                </div>
            @endforeach
        </div>

        <div class="pt-4 border-t border-slate-100 text-xs space-y-1.5 text-right">
            <p class="text-slate-500">Subtotal: <strong class="text-slate-900">{{ format_price($order->subtotal) }}</strong></p>
            @if($order->discount_amount > 0)
                <p class="text-emerald-600">Discount: <strong class="font-bold">-{{ format_price($order->discount_amount) }}</strong></p>
            @endif
            <p class="text-slate-500">Shipping Fee: <strong class="text-slate-900">{{ format_price($order->shipping_fee) }}</strong></p>
            @if($order->tax_amount > 0 || ($order->tax_snapshot_json['tax_enabled'] ?? true))
                <p class="text-slate-500">{{ $order->tax_name ?? 'VAT' }} ({{ number_format($order->tax_rate ?? 15, 0) }}%): <strong class="text-slate-900">{{ format_price($order->tax_amount) }}</strong></p>
            @endif
            <p class="text-sm font-bold text-slate-900 pt-2 border-t border-slate-100">Grand Total: {{ format_price($order->grand_total) }}</p>
        </div>
    </div>
</div>
@endsection
