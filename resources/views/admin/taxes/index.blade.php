@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Tax Management & Rate Settings</h1>
            <p class="text-xs text-slate-400 mt-0.5">Configure global tax calculation switches, default tax rates, and product tax rules</p>
        </div>
        <a href="{{ route('admin.taxes.create') }}" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-500/20">
            + Add Tax Rate
        </a>
    </div>

    <!-- Global Tax System Configuration Card -->
    <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
        <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
            <span>Global Tax System Configuration</span>
            <span class="px-3 py-1 rounded-full font-bold text-xs {{ $isTaxEnabled ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                Tax System: {{ $isTaxEnabled ? '● Enabled (ON)' : '○ Disabled (OFF)' }}
            </span>
        </h3>

        <form action="{{ route('admin.taxes.settings') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-900/60 p-5 rounded-2xl border border-slate-800">
                <!-- Global Tax System Switch -->
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="tax_system_enabled" value="1" {{ $isTaxEnabled ? 'checked' : '' }} class="w-5 h-5 accent-emerald-500 rounded mt-0.5">
                    <div>
                        <span class="text-xs font-bold text-white block">Tax System Enabled</span>
                        <span class="text-[10px] text-slate-400">When OFF, no tax is charged at checkout or added to orders.</span>
                    </div>
                </label>

                <!-- Tax Applies to Delivery Switch -->
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="tax_applies_to_delivery" value="1" {{ $taxAppliesToDelivery ? 'checked' : '' }} class="w-5 h-5 accent-brand-500 rounded mt-0.5">
                    <div>
                        <span class="text-xs font-bold text-white block">Tax Applies to Delivery</span>
                        <span class="text-[10px] text-slate-400">When ON, shipping charges are included in taxable calculations.</span>
                    </div>
                </label>

                <!-- Default Tax Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Default System Tax Rate</label>
                    <select name="default_tax_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:border-brand-500">
                        <option value="">No Default Tax</option>
                        @foreach($taxRates->where('is_active', true) as $rate)
                            <option value="{{ $rate->id }}" {{ $defaultTaxRate?->id === $rate->id ? 'selected' : '' }}>
                                {{ $rate->name }} ({{ $rate->rate }}%) {{ $rate->is_default ? '— Default' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                Save System Tax Settings
            </button>
        </form>
    </div>

    <!-- Tax Rates Table -->
    <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">Configured Tax Rates</h3>
            <span class="text-xs text-slate-400 font-mono">{{ $taxRates->count() }} Total</span>
        </div>

        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                    <th class="py-3.5 px-4">Tax Name</th>
                    <th class="py-3.5 px-4">Tax Code</th>
                    <th class="py-3.5 px-4">Tax Rate (%)</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4">Default</th>
                    <th class="py-3.5 px-4">Created At</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($taxRates as $tax)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3.5 px-4 font-bold text-white">{{ $tax->name }}</td>
                        <td class="py-3.5 px-4 font-mono text-brand-400">{{ $tax->code }}</td>
                        <td class="py-3.5 px-4 font-extrabold text-white text-sm font-mono">{{ number_format($tax->rate, 2) }}%</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full font-bold text-[10px] {{ $tax->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                {{ $tax->is_active ? '✓ Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($tax->is_default)
                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-brand-500/10 text-brand-400 border border-brand-500/20">
                                    ★ Default Tax
                                </span>
                            @else
                                <span class="text-slate-600 text-[11px]">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 font-mono text-[11px]">{{ $tax->created_at->format('M d, Y') }}</td>
                        <td class="py-3.5 px-4 text-right space-x-2">
                            <a href="{{ route('admin.taxes.edit', $tax->id) }}" class="px-3 py-1.5 bg-slate-800 text-slate-200 hover:text-white rounded-lg text-xs font-semibold">Edit</a>
                            
                            <form action="{{ route('admin.taxes.toggle_status', $tax->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Change status for {{ $tax->name }}?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $tax->is_active ? 'bg-amber-500/10 text-amber-400 hover:bg-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' }}">
                                    {{ $tax->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                            <form action="{{ route('admin.taxes.destroy', $tax->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete tax rate {{ $tax->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 rounded-lg text-xs font-semibold">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 text-xs italic">No tax rates configured yet. Click "+ Add Tax Rate" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
