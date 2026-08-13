<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }} - {{ $site_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-8">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
        
        <!-- Action Buttons -->
        <div class="no-print mb-6 flex justify-end gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold shadow">Print Invoice 🖨️</button>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-start border-b border-slate-200 pb-6 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $site_name }}</h1>
                <p class="text-xs text-slate-500">{{ setting('contact_address') }}</p>
                <p class="text-xs text-slate-500">Email: {{ setting('contact_email') }} | Phone: {{ setting('contact_phone') }}</p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-slate-900 uppercase tracking-wide">INVOICE</h2>
                <p class="text-xs text-slate-600 font-mono font-bold mt-1"># {{ $order->order_number }}</p>
                <p class="text-xs text-slate-500">Date: {{ $order->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <!-- Billing Info -->
        <div class="grid grid-cols-2 gap-6 text-xs mb-6">
            <div>
                <span class="font-bold uppercase text-slate-400 block mb-1">Billed To</span>
                <p class="font-bold text-slate-900 text-sm">{{ $order->shipping_address_json['full_name'] ?? 'Customer' }}</p>
                <p class="text-slate-600">{{ $order->shipping_address_json['address'] ?? '' }}</p>
                <p class="text-slate-600">{{ $order->shipping_address_json['city'] ?? '' }}</p>
                <p class="text-slate-600">Phone: {{ $order->shipping_address_json['phone'] ?? '' }}</p>
            </div>
            <div class="text-right">
                <span class="font-bold uppercase text-slate-400 block mb-1">Payment Method</span>
                <p class="font-bold text-slate-900 uppercase">{{ $order->payment_method }}</p>
                <p class="text-slate-600">Status: <strong class="uppercase text-emerald-600">{{ $order->payment_status }}</strong></p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left border-collapse text-xs mb-6">
            <thead>
                <tr class="border-b border-slate-200 uppercase font-bold text-slate-500 bg-slate-50">
                    <th class="py-2.5 px-3">Item</th>
                    <th class="py-2.5 px-3 text-center">Qty</th>
                    <th class="py-2.5 px-3 text-right">Price</th>
                    <th class="py-2.5 px-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-slate-900">{{ $item->product_name }}</td>
                        <td class="py-2.5 px-3 text-center">{{ $item->quantity }}</td>
                        <td class="py-2.5 px-3 text-right">{{ format_price($item->price) }}</td>
                        <td class="py-2.5 px-3 text-right font-bold">{{ format_price($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="w-64 ml-auto text-xs space-y-2 border-t border-slate-200 pt-4">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal</span>
                <span class="font-bold text-slate-900">{{ format_price($order->subtotal) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Shipping Fee</span>
                <span class="font-bold text-slate-900">{{ format_price($order->shipping_fee) }}</span>
            </div>
            @if($order->tax_amount > 0 || ($order->tax_snapshot_json['tax_enabled'] ?? true))
            <div class="flex justify-between text-slate-600">
                <span>{{ $order->tax_name ?? 'VAT' }} ({{ number_format($order->tax_rate ?? 15, 0) }}%)</span>
                <span class="font-bold text-slate-900">{{ format_price($order->tax_amount) }}</span>
            </div>
            @endif
            <div class="flex justify-between text-sm font-bold text-slate-900 pt-2 border-t border-slate-200">
                <span>Grand Total</span>
                <span>{{ format_price($order->grand_total) }}</span>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-200 text-center text-[10px] text-slate-400">
            Thank you for shopping with {{ $site_name }}! {{ setting('footer_copyright') }}
        </div>
    </div>
</body>
</html>
