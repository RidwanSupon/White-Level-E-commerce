@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-8" x-data="{ showVerifyModal: false, showRejectModal: false, presetReason: 'Transaction ID is invalid', customReason: '' }">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.payments.index') }}" class="text-xs font-bold text-slate-400 hover:text-white">← Back to Payments List</a>
            <h1 class="text-2xl font-bold text-white font-display mt-1">Review Mobile Payment #{{ $payment->id }}</h1>
        </div>
        <div>
            @if($payment->status === 'verification_pending')
                <span class="px-4 py-1.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 font-bold text-xs rounded-full inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    Verification Pending
                </span>
            @elseif($payment->status === 'verified')
                <span class="px-4 py-1.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold text-xs rounded-full inline-flex items-center gap-1.5">
                    ✓ Verified by {{ $payment->verifier?->name ?? 'Admin' }}
                </span>
            @else
                <span class="px-4 py-1.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 font-bold text-xs rounded-full inline-flex items-center gap-1.5">
                    ✕ Rejected by {{ $payment->rejecter?->name ?? 'Admin' }}
                </span>
            @endif
        </div>
    </div>

    <!-- Payment & Order Info Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Order Summary Card -->
        <div class="bg-slate-950 rounded-3xl p-6 border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
                <span>Order Information</span>
                <a href="{{ route('admin.orders.show', $payment->order_id) }}" class="text-xs text-brand-400 hover:underline">View Order Details →</a>
            </h3>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Order Number:</span>
                    <span class="font-mono font-bold text-white">#{{ $payment->order->order_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Customer Name:</span>
                    <span class="font-bold text-white">{{ $payment->user?->name ?? $payment->order->shipping_address_json['full_name'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Customer Phone:</span>
                    <span class="font-mono font-bold text-white">{{ $payment->order->shipping_address_json['phone'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Customer Email:</span>
                    <span class="font-bold text-white">{{ $payment->user?->email ?? $payment->order->shipping_address_json['email'] }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-900 pt-3">
                    <span class="text-slate-400">Total Order Amount:</span>
                    <span class="font-display font-extrabold text-brand-400 text-sm">{{ format_price($payment->order->grand_total) }}</span>
                </div>
            </div>
        </div>

        <!-- Submitted Payment Details Card -->
        <div class="bg-slate-950 rounded-3xl p-6 border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
                <span>Payment Information</span>
                @if($payment->payment_method === 'bkash')
                    <span class="px-2.5 py-0.5 bg-pink-950 border border-pink-800 text-pink-300 font-bold rounded-lg text-[11px] uppercase">bKash</span>
                @else
                    <span class="px-2.5 py-0.5 bg-orange-950 border border-orange-800 text-orange-300 font-bold rounded-lg text-[11px] uppercase">Nagad</span>
                @endif
            </h3>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Payment Type:</span>
                    @if($payment->payment_type === 'delivery_advance')
                        <span class="px-2.5 py-0.5 bg-amber-500/20 border border-amber-500/30 text-amber-400 font-extrabold rounded-lg text-[11px]">⚡ Delivery Advance Payment</span>
                    @else
                        <span class="px-2.5 py-0.5 bg-slate-800 text-slate-200 font-bold rounded-lg text-[11px]">Full Order Payment</span>
                    @endif
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Merchant/Personal Number:</span>
                    <span class="font-mono font-bold text-white">{{ $payment->merchant_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Submitted Amount:</span>
                    <span class="font-mono font-extrabold text-white text-sm">{{ format_price($payment->amount) }}</span>
                </div>
                <div class="flex justify-between bg-slate-900/80 p-3 rounded-2xl border border-slate-800">
                    <span class="text-slate-400 font-semibold">Transaction ID:</span>
                    <span class="font-mono font-black text-emerald-400 text-base select-all">{{ $payment->transaction_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Submitted At:</span>
                    <span class="text-slate-300">{{ $payment->created_at->format('d M Y, h:i A') }}</span>
                </div>
                @if($payment->rejection_reason)
                    <div class="p-3 bg-rose-950/40 border border-rose-900/50 rounded-xl text-rose-300 space-y-1">
                        <span class="font-bold block">Rejection Reason:</span>
                        <p class="text-xs">{{ $payment->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Proof Screenshot Section -->
    <div class="bg-slate-950 rounded-3xl p-6 border border-slate-800 space-y-4">
        <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Payment Screenshot Proof</h3>
        
        @if($payment->payment_proof)
            <div class="max-w-md mx-auto rounded-2xl overflow-hidden border border-slate-800 bg-slate-900 p-2">
                <a href="{{ $payment->payment_proof_url }}" target="_blank" title="Click to view full image">
                    <img src="{{ $payment->payment_proof_url }}" alt="Payment Screenshot Proof" class="w-full h-auto rounded-xl object-contain max-h-96 hover:opacity-90 transition-opacity">
                </a>
                <p class="text-[11px] text-center text-slate-400 mt-2">Click image to view in full resolution</p>
            </div>
        @else
            <div class="p-8 text-center bg-slate-900/40 rounded-2xl border border-slate-900 text-slate-500 text-xs">
                No screenshot proof was uploaded by the customer for this transaction.
            </div>
        @endif
    </div>

    <!-- Admin Actions Section -->
    @if($payment->status === 'verification_pending')
        <div class="bg-slate-950 rounded-3xl p-6 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h4 class="font-bold text-white text-sm">Action Required</h4>
                <p class="text-xs text-slate-400 mt-0.5">Verify that the Transaction ID matches your bKash/Nagad merchant SMS app log before confirming.</p>
            </div>
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <button type="button" @click="showRejectModal = true" class="w-full sm:w-auto px-6 py-3 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-xl font-bold text-xs transition-all">
                    ✕ Reject Payment
                </button>
                <button type="button" @click="showVerifyModal = true" class="w-full sm:w-auto px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold text-xs shadow-lg shadow-emerald-500/20 transition-all">
                    ✓ Verify Payment
                </button>
            </div>
        </div>
    @endif

    <!-- 1. Verify Confirmation Modal -->
    <div x-show="showVerifyModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full space-y-6 shadow-2xl">
            <h3 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Confirm Payment Verification</h3>
            <p class="text-xs text-slate-300 leading-relaxed">
                Are you sure you have verified Transaction ID <strong class="text-emerald-400 font-mono">{{ $payment->transaction_id }}</strong> for <strong class="text-white">{{ format_price($payment->amount) }}</strong> in your {{ strtoupper($payment->payment_method) }} merchant statement?
            </p>

            <form action="{{ route('admin.payments.verify', $payment->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Optional Admin Note</label>
                    <input type="text" name="admin_note" placeholder="e.g. Verified from bKash App Statement" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white outline-none focus:border-emerald-500">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showVerifyModal = false" class="px-5 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-500/20">Yes, Verify & Confirm Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Reject Confirmation Modal -->
    <div x-show="showRejectModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full space-y-6 shadow-2xl">
            <h3 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Reject Payment Request</h3>
            
            <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Select Rejection Reason *</label>
                    <select x-model="presetReason" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white outline-none focus:border-rose-500">
                        <option value="Transaction ID is invalid">Transaction ID is invalid</option>
                        <option value="Amount does not match expected order total">Amount does not match expected order total</option>
                        <option value="Payment not received in merchant account">Payment not received in merchant account</option>
                        <option value="Duplicate transaction ID submitted">Duplicate transaction ID submitted</option>
                        <option value="Other">Other (Custom Reason)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Detailed Reason sent to Customer *</label>
                    <textarea name="rejection_reason" x-bind:value="presetReason === 'Other' ? customReason : presetReason" required rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white outline-none focus:border-rose-500"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showRejectModal = false" class="px-5 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-rose-500/20">Reject Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
