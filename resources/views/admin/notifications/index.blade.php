@extends('layouts.admin')

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Notification Center</h1>
            <p class="text-xs text-slate-400 mt-0.5">Real-time alerts for low inventory stock, out-of-stock items, payments, and orders</p>
        </div>
        <span class="px-3.5 py-1.5 bg-slate-950 border border-slate-800 text-slate-300 font-mono text-xs font-bold rounded-xl">
            Unread Notifications: {{ auth()->user()->unreadNotifications->count() }}
        </span>
    </div>

    <!-- Active System Notifications List -->
    <div class="bg-slate-950 rounded-3xl border border-slate-800 p-6 space-y-4">
        <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
            <span>System Alerts & Notifications</span>
            <span class="text-xs text-slate-400 font-semibold">{{ $notifications->count() }} Total</span>
        </h3>

        @if($notifications->isEmpty())
            <div class="text-center py-8 text-slate-500 text-xs italic">
                No notifications present in system inbox.
            </div>
        @else
            <div class="space-y-3">
                @foreach($notifications as $notif)
                    @php
                        $raw = $notif->data;
                        if (is_string($raw)) {
                            $data = json_decode($raw, true) ?? [];
                        } elseif (is_array($raw)) {
                            $data = $raw;
                        } else {
                            $data = [];
                        }

                        $alertType = $data['alert_type'] ?? ($data['type'] ?? 'general');
                        $isUnread = is_null($notif->read_at);
                        $targetUrl = !empty($data['url']) ? $data['url'] : (!empty($data['product_id']) ? route('admin.products.edit', $data['product_id']) : null);
                        $title = $data['title'] ?? ($alertType === 'low_stock' ? '⚠ Low Stock Alert' : ($alertType === 'out_of_stock' ? '🔴 Out of Stock Alert' : 'System Notification'));
                        $message = $data['message'] ?? ($data['product_name'] ?? '');
                    @endphp
                    <div class="p-4 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $isUnread ? 'bg-slate-900 border-slate-700 shadow-md ring-1 ring-brand-500/20' : 'bg-slate-950/60 border-slate-800/80 opacity-80' }}">
                        <div class="flex items-start gap-3.5">
                            @if($alertType === 'out_of_stock')
                                <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 font-extrabold flex items-center justify-center text-lg shrink-0">
                                    🔴
                                </div>
                            @elseif($alertType === 'low_stock')
                                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 font-extrabold flex items-center justify-center text-lg shrink-0">
                                    ⚠
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-xl bg-brand-500/20 text-brand-400 font-extrabold flex items-center justify-center text-lg shrink-0">
                                    🔔
                                </div>
                            @endif

                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-white text-sm">{{ $title }}</h4>
                                    @if($isUnread)
                                        <span class="px-2 py-0.5 bg-brand-500 text-white font-extrabold text-[9px] rounded-full uppercase">New</span>
                                    @endif
                                </div>
                                @if(!empty($message))
                                    <p class="text-xs text-slate-300">{{ $message }}</p>
                                @endif
                                <div class="flex items-center gap-4 text-[10px] text-slate-500 font-mono pt-1">
                                    @if(!empty($data['sku']))
                                        <span>SKU: {{ $data['sku'] }}</span>
                                    @endif
                                    <span>{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            @if(!empty($targetUrl))
                                <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl text-xs shadow-md transition-all">
                                        View Product →
                                    </button>
                                </form>
                            @else
                                @if($isUnread)
                                    <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-slate-800 text-slate-300 hover:text-white rounded-xl text-xs font-semibold">
                                            Mark as Read
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Inventory Overview Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Low Stock Summary Card -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-3">
            <h3 class="font-bold text-amber-400 text-sm border-b border-slate-800 pb-2 flex items-center justify-between">
                <span>🟡 Low Stock Warnings ({{ $lowStockProducts->count() }})</span>
                <span class="text-[10px] text-slate-500 font-normal">Stock &le; Threshold</span>
            </h3>
            <div class="space-y-2 text-xs">
                @forelse($lowStockProducts as $prod)
                    <a href="{{ route('admin.products.edit', $prod->id) }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-900 hover:bg-slate-850 border border-slate-800 transition-all">
                        <div>
                            <span class="font-bold text-white block">{{ $prod->name }}</span>
                            <span class="text-slate-500 font-mono text-[10px]">SKU: {{ $prod->sku }} | Threshold: {{ $prod->low_stock_threshold }}</span>
                        </div>
                        <span class="px-2.5 py-1 bg-amber-500/10 text-amber-400 rounded-full font-bold text-[10px]">
                            {{ $prod->stock_quantity }} left
                        </span>
                    </a>
                @empty
                    <p class="text-slate-500 text-xs italic py-2">No products currently in low stock state.</p>
                @endforelse
            </div>
        </div>

        <!-- Out of Stock Summary Card -->
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-3">
            <h3 class="font-bold text-rose-400 text-sm border-b border-slate-800 pb-2 flex items-center justify-between">
                <span>🔴 Out of Stock Items ({{ $outOfStockProducts->count() }})</span>
                <span class="text-[10px] text-slate-500 font-normal">Stock = 0</span>
            </h3>
            <div class="space-y-2 text-xs">
                @forelse($outOfStockProducts as $prod)
                    <a href="{{ route('admin.products.edit', $prod->id) }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-900 hover:bg-slate-850 border border-slate-800 transition-all">
                        <div>
                            <span class="font-bold text-white block">{{ $prod->name }}</span>
                            <span class="text-slate-500 font-mono text-[10px]">SKU: {{ $prod->sku }}</span>
                        </div>
                        <span class="px-2.5 py-1 bg-rose-500/10 text-rose-400 rounded-full font-bold text-[10px]">
                            0 Stock
                        </span>
                    </a>
                @empty
                    <p class="text-slate-500 text-xs italic py-2">No products currently out of stock.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
