@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Adjustment Form -->
    <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 h-fit space-y-4">
        <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Record Stock Adjustment</h3>
        <form action="{{ route('admin.inventory.adjust') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Target Product</label>
                <select name="product_id" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-white outline-none focus:border-brand-500">
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }} (Current: {{ $prod->stock_quantity }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Movement Type</label>
                    <select name="type" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:border-brand-500">
                        <option value="IN">Stock IN (+)</option>
                        <option value="OUT">Stock OUT (-)</option>
                        <option value="ADJUSTMENT">Adjustment (-)</option>
                        <option value="RETURN">Customer Return (+)</option>
                        <option value="DAMAGE">Damaged Stock (-)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Quantity</label>
                    <input type="number" name="quantity" min="1" value="1" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:border-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Reference / Invoice #</label>
                <input type="text" name="reference" placeholder="PO-2026-001" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white outline-none focus:border-brand-500"></textarea>
            </div>

            <button type="submit" class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md">Submit Adjustment</button>
        </form>
    </div>

    <!-- Live Inventory Table -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 font-bold text-white text-base">Live Product Stock Counts</div>
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                        <th class="py-3 px-4">Product</th>
                        <th class="py-3 px-4">SKU</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Stock Count</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($products as $prod)
                        <tr class="hover:bg-slate-900/40">
                            <td class="py-3 px-4 font-bold text-white">{{ $prod->name }}</td>
                            <td class="py-3 px-4 text-slate-400 font-mono">{{ $prod->sku }}</td>
                            <td class="py-3 px-4 text-slate-400">{{ $prod->category?->name }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] {{ $prod->stock_quantity <= $prod->low_stock_threshold ? 'bg-rose-500/10 text-rose-400' : 'bg-emerald-500/10 text-emerald-400' }}">
                                    {{ $prod->stock_quantity }} units
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Recent Stock Movements Audit Trail -->
        <div class="bg-slate-950 rounded-3xl border border-slate-800 p-6 space-y-3">
            <h4 class="font-bold text-white text-sm border-b border-slate-800 pb-2">Recent Inventory Movement Log</h4>
            <div class="space-y-2 text-xs">
                @foreach($movements as $mv)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-900 border border-slate-800/80">
                        <div>
                            <span class="font-bold text-white block">{{ $mv->product?->name }}</span>
                            <span class="text-slate-500 text-[11px]">Type: <strong class="text-brand-400 uppercase">{{ $mv->type }}</strong> ({{ $mv->quantity }} qty) | Ref: {{ $mv->reference }}</span>
                        </div>
                        <span class="text-slate-500 text-[10px]">{{ $mv->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
