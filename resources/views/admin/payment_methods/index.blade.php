@extends('layouts.admin')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div>
        <h1 class="text-2xl font-bold text-white font-display">Payment Gateway Configurator</h1>
        <p class="text-xs text-slate-400 mt-0.5">Enable/disable payment channels, manage merchant credentials, and toggle sandbox testing</p>
    </div>

    <form action="{{ route('admin.payment_methods.update') }}" method="POST" class="space-y-6">
        @csrf

        @foreach($gateways as $code => $gw)
            <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full {{ $gw['enabled'] ? 'bg-emerald-500' : 'bg-slate-600' }}"></span>
                        {{ $gw['name'] }}
                    </h3>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-300 cursor-pointer">
                        <input type="hidden" name="gateway_{{ $code }}_enabled" value="0">
                        <input type="checkbox" name="gateway_{{ $code }}_enabled" value="1" {{ $gw['enabled'] ? 'checked' : '' }} class="rounded text-brand-500">
                        Active
                    </label>
                </div>

                @if($code !== 'cod')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Environment Mode</label>
                            <select name="gateway_{{ $code }}_mode" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white outline-none">
                                <option value="sandbox" {{ ($gw['mode'] ?? '') === 'sandbox' ? 'selected' : '' }}>Sandbox / Test</option>
                                <option value="live" {{ ($gw['mode'] ?? '') === 'live' ? 'selected' : '' }}>Production / Live</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Merchant / API Credential</label>
                            <input type="text" name="gateway_{{ $code }}_secret_key" value="{{ $gw['secret_key'] ?? ($gw['store_id'] ?? ($gw['app_key'] ?? '')) }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white outline-none">
                        </div>
                    </div>
                @else
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Checkout Instructions</label>
                        <input type="text" name="gateway_cod_instructions" value="{{ $gw['instructions'] }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none">
                    </div>
                @endif
            </div>
        @endforeach

        <button type="submit" class="px-8 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/20">
            Save Gateway Configuration
        </button>
    </form>
</div>
@endsection
