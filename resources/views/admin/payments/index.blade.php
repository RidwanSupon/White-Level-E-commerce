@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Manual Mobile Payments</h1>
            <p class="text-slate-400 text-xs mt-1">Review, verify, or reject customer bKash and Nagad transaction submissions</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <span>✕</span> {{ session('error') }}
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <a href="{{ route('admin.payments.index', ['status' => 'verification_pending']) }}" class="bg-amber-950/20 border border-amber-800/40 rounded-3xl p-6 hover:border-amber-500 transition-all group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Pending Verification</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
            </div>
            <span class="font-display text-3xl font-extrabold text-white mt-3 block group-hover:scale-105 transition-transform">{{ $counts['verification_pending'] }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Awaiting admin review</span>
        </a>

        <a href="{{ route('admin.payments.index', ['status' => 'verified']) }}" class="bg-emerald-950/20 border border-emerald-800/40 rounded-3xl p-6 hover:border-emerald-500 transition-all group">
            <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Verified Payments</span>
            <span class="font-display text-3xl font-extrabold text-white mt-3 block group-hover:scale-105 transition-transform">{{ $counts['verified'] }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Approved transactions</span>
        </a>

        <a href="{{ route('admin.payments.index', ['status' => 'rejected']) }}" class="bg-rose-950/20 border border-rose-800/40 rounded-3xl p-6 hover:border-rose-500 transition-all group">
            <span class="text-xs font-bold text-rose-400 uppercase tracking-wider">Rejected Payments</span>
            <span class="font-display text-3xl font-extrabold text-white mt-3 block group-hover:scale-105 transition-transform">{{ $counts['rejected'] }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Declined submissions</span>
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-3 overflow-x-auto">
        <a href="{{ route('admin.payments.index', ['status' => 'all']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'all' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : 'text-slate-400 hover:text-white bg-slate-900' }}">
            All ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.payments.index', ['status' => 'verification_pending']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'verification_pending' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'text-slate-400 hover:text-white bg-slate-900' }}">
            Pending Verification ({{ $counts['verification_pending'] }})
        </a>
        <a href="{{ route('admin.payments.index', ['status' => 'verified']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'verified' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'text-slate-400 hover:text-white bg-slate-900' }}">
            Verified ({{ $counts['verified'] }})
        </a>
        <a href="{{ route('admin.payments.index', ['status' => 'rejected']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $status === 'rejected' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/20' : 'text-slate-400 hover:text-white bg-slate-900' }}">
            Rejected ({{ $counts['rejected'] }})
        </a>
    </div>

    <!-- Payments Table -->
    <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="py-4 px-6">Order ID</th>
                        <th class="py-4 px-6">Customer</th>
                        <th class="py-4 px-6">Method</th>
                        <th class="py-4 px-6">Amount</th>
                        <th class="py-4 px-6">Transaction ID</th>
                        <th class="py-4 px-6">Proof</th>
                        <th class="py-4 px-6">Submitted At</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900 text-slate-300">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-white">
                                <a href="{{ route('admin.orders.show', $payment->order_id) }}" class="hover:text-brand-400">
                                    #{{ $payment->order->order_number }}
                                </a>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-white block">{{ $payment->user?->name ?? $payment->order->shipping_address_json['full_name'] ?? 'Guest Customer' }}</span>
                                <span class="text-[11px] text-slate-500">{{ $payment->user?->email ?? $payment->order->shipping_address_json['email'] ?? '' }}</span>
                            </td>
                            <td class="py-4 px-6">
                                @if($payment->payment_method === 'bkash')
                                    <span class="px-2.5 py-1 bg-pink-950/60 border border-pink-800/60 text-pink-300 font-extrabold rounded-lg uppercase text-[10px]">bKash</span>
                                @else
                                    <span class="px-2.5 py-1 bg-orange-950/60 border border-orange-800/60 text-orange-300 font-extrabold rounded-lg uppercase text-[10px]">Nagad</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-extrabold text-white font-mono">
                                {{ format_price($payment->amount) }}
                            </td>
                            <td class="py-4 px-6 font-mono font-bold text-slate-200">
                                {{ $payment->transaction_id }}
                            </td>
                            <td class="py-4 px-6">
                                @if($payment->payment_proof)
                                    <a href="{{ $payment->payment_proof_url }}" target="_blank" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                        🖼 View Image
                                    </a>
                                @else
                                    <span class="text-slate-500 text-[11px]">No proof</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-400 text-[11px]">
                                {{ $payment->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-4 px-6">
                                @if($payment->status === 'verification_pending')
                                    <span class="px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 font-bold rounded-full text-[11px] inline-flex items-center gap-1">
                                        ⏳ Pending Review
                                    </span>
                                @elseif($payment->status === 'verified')
                                    <span class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold rounded-full text-[11px] inline-flex items-center gap-1">
                                        ✓ Verified
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-400 font-bold rounded-full text-[11px] inline-flex items-center gap-1">
                                        ✕ Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('admin.payments.show', $payment->id) }}" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl text-xs shadow-md shadow-brand-500/20 transition-colors inline-block">
                                    Review Payment →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-500">
                                No payment verification records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-900">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
