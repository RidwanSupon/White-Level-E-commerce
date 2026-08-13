@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 h-fit space-y-4">
        <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Create Promo Coupon</h3>
        <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Coupon Code</label>
                <input type="text" name="code" required placeholder="SUMMER20" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-white uppercase outline-none focus:border-brand-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Discount Type</label>
                    <select name="type" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:border-brand-500">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (৳)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Value</label>
                    <input type="number" step="0.01" name="value" required placeholder="10" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:border-brand-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Minimum Spend (৳)</label>
                <input type="number" step="0.01" name="min_spend" value="1000" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white outline-none focus:border-brand-500">
            </div>

            <button type="submit" class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md">Create Coupon</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800 font-bold text-white text-base">Active Discount Coupons</div>
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                    <th class="py-3 px-4">Code</th>
                    <th class="py-3 px-4">Type & Value</th>
                    <th class="py-3 px-4">Min Spend</th>
                    <th class="py-3 px-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($coupons as $coupon)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3 px-4 font-bold text-brand-400 font-mono text-sm">{{ $coupon->code }}</td>
                        <td class="py-3 px-4 text-white">
                            {{ $coupon->type === 'percentage' ? $coupon->value . '%' : format_price($coupon->value) }}
                        </td>
                        <td class="py-3 px-4 text-slate-400">{{ format_price($coupon->min_spend) }}</td>
                        <td class="py-3 px-4 text-right">
                            <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" onsubmit="return confirm('Delete coupon?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:underline text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
