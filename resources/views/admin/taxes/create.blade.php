@extends('layouts.admin')

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Add Tax Rate</h1>
            <p class="text-xs text-slate-400 mt-0.5">Create a new tax rate (e.g., Standard VAT 15%, Reduced VAT 5%)</p>
        </div>
        <a href="{{ route('admin.taxes.index') }}" class="text-xs font-bold text-slate-400 hover:text-white">← Back to Tax Rates</a>
    </div>

    <form action="{{ route('admin.taxes.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Tax Rate Details</h3>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Tax Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. VAT, Sales Tax" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-semibold">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tax Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. VAT-15, TAX-05" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-mono">
                    <p class="text-[10px] text-slate-500 mt-1">Unique identifier code</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tax Rate (%) *</label>
                    <input type="number" step="0.01" min="0" max="100" name="rate" value="{{ old('rate', 15.00) }}" required placeholder="15.00" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-mono font-bold">
                    <p class="text-[10px] text-slate-500 mt-1">Supports decimals (e.g. 5, 7.5, 10, 15)</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Description of tax rate scope..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Status & Default Settings</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-emerald-500 rounded">
                    <div>
                        <span class="text-xs font-bold text-emerald-400 block">Active Status</span>
                        <span class="text-[10px] text-slate-500">Allow tax rate to be applied</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" class="w-4 h-4 accent-brand-500 rounded">
                    <div>
                        <span class="text-xs font-bold text-brand-400 block">Set as Default Tax</span>
                        <span class="text-[10px] text-slate-500">Automatically removes default from previous tax rate</span>
                    </div>
                </label>
            </div>
        </div>

        <button type="submit" class="px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/20 transition-all">
            Save & Create Tax Rate
        </button>
    </form>
</div>
@endsection
