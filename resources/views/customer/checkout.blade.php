@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="checkoutEngine({
    initialCity: '{{ old('city', 'Dhaka') }}',
    initialZoneName: '{{ $initialZone->name }}',
    initialDeliveryCharge: {{ $initialZone->delivery_charge }},
    initialAdvanceRequired: {{ $initialZone->advance_payment_required ? 'true' : 'false' }},
    subtotal: {{ $subtotal }},
    discount: {{ $discount }}
})">
    <!-- Toast Notification for Copy -->
    <div x-show="copiedToast" x-transition class="fixed bottom-6 right-6 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl z-50 text-xs font-bold flex items-center gap-2 border border-slate-700" style="display: none;">
        <span class="text-emerald-400 text-base">✓</span> Number copied successfully.
    </div>

    <h1 class="font-display text-3xl font-extrabold text-slate-900 mb-8">Checkout</h1>

    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-600 font-semibold space-y-1">
            <p class="font-bold">Please correct the following errors:</p>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- 1. Customer Shipping Details & Location -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
                    <h3 class="font-display font-bold text-lg text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
                        <span>1. Shipping Information</span>
                        <span class="text-xs font-mono font-bold text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-200" x-text="'Zone: ' + zoneName"></span>
                    </h3>

                    @if($userAddresses->isNotEmpty())
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-slate-700 mb-2">Saved Addresses</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($userAddresses as $address)
                                    <button type="button" @click="selectSavedAddress({{ json_encode($address) }})" class="p-3 border rounded-2xl text-left hover:border-brand-500 transition-all text-xs space-y-1" :class="city === '{{ $address->city }}' ? 'border-brand-500 bg-brand-50/40 ring-1 ring-brand-500/30' : 'border-slate-200'">
                                        <span class="font-bold text-slate-900 block">{{ $address->full_name }} ({{ $address->city }})</span>
                                        <span class="text-slate-500 truncate block">{{ $address->address_line_1 }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                            <input type="text" name="full_name" x-model="fullName" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-900 outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address *</label>
                            <input type="email" name="email" x-model="email" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-900 outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number *</label>
                            <input type="text" name="phone" x-model="phone" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-900 outline-none focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">District / City *</label>
                            <input type="text" name="city" x-model="city" @input.debounce.400ms="recalculateShipping()" required placeholder="e.g. Dhaka, Chattogram, Sylhet" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-900 outline-none focus:border-brand-500 font-bold">
                            <span class="text-[10px] text-slate-500 mt-1 block">Type your city (e.g. Dhaka vs Chattogram) to calculate delivery rules</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Street Address *</label>
                        <textarea name="address_line_1" x-model="addressLine1" rows="2" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-900 outline-none focus:border-brand-500"></textarea>
                    </div>
                </div>

                <!-- Outside Dhaka Advance Payment Warning Notice -->
                <div x-show="advanceRequired" x-transition class="p-6 bg-amber-50 border-2 border-amber-400 rounded-3xl space-y-3 shadow-md" style="display: none;">
                    <div class="flex items-center gap-2 text-amber-900 font-extrabold text-sm">
                        <span class="w-3 h-3 rounded-full bg-amber-500 animate-ping"></span>
                        ⚡ OUTSIDE DHAKA ADVANCE DELIVERY PAYMENT REQUIRED
                    </div>
                    <p class="text-xs font-medium text-amber-800 leading-relaxed">
                        For orders outside Dhaka, customer MUST pay the delivery charge of <strong class="font-mono text-base text-amber-950 font-black" x-text="formatMoney(advanceAmount)"></strong> in advance via bKash or Nagad.
                    </p>
                    <div class="grid grid-cols-2 gap-4 bg-white p-3 rounded-2xl border border-amber-200 text-xs font-semibold">
                        <div>
                            <span class="text-[11px] text-slate-500 block">Pay Now (Advance Delivery):</span>
                            <span class="font-display font-black text-amber-600 text-base" x-text="formatMoney(advanceAmount)"></span>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 block">Remaining Due (COD on Delivery):</span>
                            <span class="font-display font-black text-slate-900 text-base" x-text="formatMoney(remainingAmount)"></span>
                        </div>
                    </div>
                </div>

                <!-- 2. Payment Options -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
                    <h3 class="font-display font-bold text-lg text-slate-900 border-b border-slate-100 pb-3">2. Select Payment Method</h3>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <label x-show="!advanceRequired" class="p-4 border rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-brand-500 text-center transition-all" :class="paymentMethod === 'cod' ? 'border-brand-500 bg-brand-50/50 ring-2 ring-brand-500/20' : 'border-slate-200'">
                            <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" class="mb-2 accent-brand-500">
                            <span class="font-bold text-xs text-slate-900">Cash on Delivery</span>
                        </label>

                        <label class="p-4 border rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-pink-500 text-center transition-all" :class="paymentMethod === 'bkash' ? 'border-pink-500 bg-pink-50/50 ring-2 ring-pink-500/20' : 'border-slate-200'">
                            <input type="radio" name="payment_method" value="bkash" x-model="paymentMethod" class="mb-2 accent-pink-500">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-pink-500 inline-block"></span>
                                <span class="font-extrabold text-xs text-pink-600">bKash</span>
                            </div>
                        </label>

                        <label class="p-4 border rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 text-center transition-all" :class="paymentMethod === 'nagad' ? 'border-orange-500 bg-orange-50/50 ring-2 ring-orange-500/20' : 'border-slate-200'">
                            <input type="radio" name="payment_method" value="nagad" x-model="paymentMethod" class="mb-2 accent-orange-500">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-orange-500 inline-block"></span>
                                <span class="font-extrabold text-xs text-orange-600">Nagad</span>
                            </div>
                        </label>
                    </div>

                    <!-- bKash Instruction Card -->
                    <div x-show="paymentMethod === 'bkash'" x-transition class="p-6 bg-pink-50/70 border border-pink-200 rounded-3xl space-y-5" style="display: none;">
                        <div class="flex items-center justify-between border-b border-pink-200 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 bg-pink-500 text-white rounded-full text-xs font-black uppercase">bKash</span>
                                <h4 class="font-extrabold text-pink-900 text-sm">Pay with bKash</h4>
                            </div>
                            <span class="text-xs font-bold text-pink-700">Send Money</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-white p-4 rounded-2xl border border-pink-200 shadow-sm">
                            <div>
                                <span class="text-[11px] text-slate-500 font-semibold block">Send Money to:</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono font-extrabold text-slate-900 text-base">{{ $bkashNumber }}</span>
                                    <button type="button" @click="copyToClipboard('{{ $bkashNumber }}')" class="px-2.5 py-1 bg-pink-100 text-pink-700 hover:bg-pink-200 rounded-lg text-[11px] font-bold transition-colors">
                                        Copy
                                    </button>
                                </div>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-500 font-semibold block" x-text="advanceRequired ? 'Send EXACT Delivery Charge:' : 'Exact Order Amount:'"></span>
                                <span class="font-display font-black text-pink-600 text-base mt-0.5 block" x-text="formatMoney(advanceRequired ? advanceAmount : grandTotal)"></span>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-500 font-semibold block">Account Name:</span>
                                <span class="font-bold text-slate-900 text-xs mt-1 block">{{ $bkashAccountName }} ({{ $bkashAccountType }})</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h5 class="text-xs font-bold text-pink-900">Instructions:</h5>
                            <pre class="bg-white p-4 rounded-2xl border border-pink-200 text-xs font-medium text-slate-700 font-mono whitespace-pre-wrap leading-relaxed">{{ $bkashInstructions }}</pre>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white p-4 rounded-2xl border border-pink-200">
                            <div>
                                <label class="block text-xs font-bold text-pink-900 mb-1">Transaction ID *</label>
                                <input type="text" name="transaction_id" placeholder="e.g. TRX98765432" value="{{ old('transaction_id') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-900 uppercase outline-none focus:border-pink-500">
                                <p class="text-[10px] text-slate-500 mt-1">Copy the TRX ID received in your bKash SMS</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-pink-900 mb-1">
                                    Payment Screenshot Proof {{ $proofRequired ? '*' : '(Optional)' }}
                                </label>
                                <input type="file" name="payment_proof" accept="image/png,image/jpeg,image/jpg,image/webp" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-pink-100 file:text-pink-700 hover:file:bg-pink-200">
                            </div>
                        </div>
                    </div>

                    <!-- Nagad Instruction Card -->
                    <div x-show="paymentMethod === 'nagad'" x-transition class="p-6 bg-orange-50/70 border border-orange-200 rounded-3xl space-y-5" style="display: none;">
                        <div class="flex items-center justify-between border-b border-orange-200 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 bg-orange-500 text-white rounded-full text-xs font-black uppercase">Nagad</span>
                                <h4 class="font-extrabold text-orange-900 text-sm">Pay with Nagad</h4>
                            </div>
                            <span class="text-xs font-bold text-orange-700">Send Money</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-white p-4 rounded-2xl border border-orange-200 shadow-sm">
                            <div>
                                <span class="text-[11px] text-slate-500 font-semibold block">Send Money to:</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono font-extrabold text-slate-900 text-base">{{ $nagadNumber }}</span>
                                    <button type="button" @click="copyToClipboard('{{ $nagadNumber }}')" class="px-2.5 py-1 bg-orange-100 text-orange-700 hover:bg-orange-200 rounded-lg text-[11px] font-bold transition-colors">
                                        Copy
                                    </button>
                                </div>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-500 font-semibold block" x-text="advanceRequired ? 'Send EXACT Delivery Charge:' : 'Exact Order Amount:'"></span>
                                <span class="font-display font-black text-orange-600 text-base mt-0.5 block" x-text="formatMoney(advanceRequired ? advanceAmount : grandTotal)"></span>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-500 font-semibold block">Account Name:</span>
                                <span class="font-bold text-slate-900 text-xs mt-1 block">{{ $nagadAccountName }} ({{ $nagadAccountType }})</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h5 class="text-xs font-bold text-orange-900">Instructions:</h5>
                            <pre class="bg-white p-4 rounded-2xl border border-orange-200 text-xs font-medium text-slate-700 font-mono whitespace-pre-wrap leading-relaxed">{{ $nagadInstructions }}</pre>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white p-4 rounded-2xl border border-orange-200">
                            <div>
                                <label class="block text-xs font-bold text-orange-900 mb-1">Transaction ID *</label>
                                <input type="text" name="transaction_id" placeholder="e.g. TRX98765432" value="{{ old('transaction_id') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-900 uppercase outline-none focus:border-orange-500">
                                <p class="text-[10px] text-slate-500 mt-1">Copy the TRX ID received in your Nagad SMS</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-orange-900 mb-1">
                                    Payment Screenshot Proof {{ $proofRequired ? '*' : '(Optional)' }}
                                </label>
                                <input type="file" name="payment_proof" accept="image/png,image/jpeg,image/jpg,image/webp" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Review Sidebar -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm h-fit space-y-6">
                <h3 class="font-display font-bold text-lg text-slate-900 border-b border-slate-100 pb-3">Order Items ({{ $cart->items->count() }})</h3>

                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    @foreach($cart->items as $item)
                        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900">{{ $item->quantity }}x</span>
                                <span class="text-slate-700 font-medium truncate max-w-[140px]">{{ $item->product->name }}</span>
                            </div>
                            <span class="font-bold text-slate-900">{{ format_price($item->price * $item->quantity) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2 text-xs border-t border-slate-100 pt-4">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-900" x-text="formatMoney(subtotal)"></span>
                    </div>
                    <div x-show="discount > 0" class="flex justify-between text-emerald-600 font-bold">
                        <span>Discount</span>
                        <span x-text="'-' + formatMoney(discount)"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Delivery Charge (<span class="font-semibold" x-text="zoneName"></span>)</span>
                        <span class="font-bold text-slate-900" x-text="formatMoney(deliveryCharge)"></span>
                    </div>
                    <div class="flex justify-between text-slate-600" x-show="taxEnabled">
                        <span><span x-text="taxName"></span> (<span x-text="taxRate + '%'"></span>)</span>
                        <span class="font-bold text-slate-900" x-text="formatMoney(tax)"></span>
                    </div>

                    <div x-show="advanceRequired" class="p-3 bg-amber-50 rounded-2xl border border-amber-200 space-y-1 my-2" style="display: none;">
                        <div class="flex justify-between font-bold text-amber-900 text-xs">
                            <span>Pay Now (Advance Delivery):</span>
                            <span class="text-amber-600 font-mono text-sm" x-text="formatMoney(advanceAmount)"></span>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-600">
                            <span>Remaining Due on Delivery (COD):</span>
                            <span class="font-bold text-slate-900" x-text="formatMoney(remainingAmount)"></span>
                        </div>
                    </div>

                    <div class="flex justify-between text-base font-bold text-slate-900 pt-3 border-t border-slate-100">
                        <span>Total Order Value</span>
                        <span class="text-brand-600 font-display text-xl" x-text="formatMoney(grandTotal)"></span>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-bold text-center text-sm rounded-full shadow-lg shadow-brand-500/20 transition-all hover:scale-[1.02] touch-target flex items-center justify-center gap-2">
                    <span x-text="advanceRequired ? 'Pay Advance & Place Order' : (['bkash', 'nagad'].includes(paymentMethod) ? 'Confirm & Submit Payment' : 'Confirm Order')">Confirm Order</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function checkoutEngine(config) {
    return {
        fullName: '{{ old('full_name', auth()->user()?->name) }}',
        email: '{{ old('email', auth()->user()?->email) }}',
        phone: '{{ old('phone', auth()->user()?->phone) }}',
        addressLine1: '{{ old('address_line_1') }}',
        city: config.initialCity || 'Dhaka',
        zoneName: config.initialZoneName || 'Inside Dhaka',
        deliveryCharge: config.initialDeliveryCharge || 60,
        advanceRequired: config.initialAdvanceRequired || false,
        advanceAmount: config.initialAdvanceRequired ? config.initialDeliveryCharge : 0,
        subtotal: config.subtotal || 0,
        discount: config.discount || 0,
        tax: 0,
        taxName: '{{ app(\App\Services\TaxService::class)->getDefaultTaxRate()?->name ?? "VAT" }}',
        taxRate: {{ app(\App\Services\TaxService::class)->getDefaultTaxRate()?->rate ?? 15.00 }},
        taxEnabled: {{ app(\App\Services\TaxService::class)->isTaxEnabled() ? 'true' : 'false' }},
        grandTotal: 0,
        remainingAmount: 0,
        paymentMethod: config.initialAdvanceRequired ? 'bkash' : 'cod',
        copiedToast: false,

        init() {
            this.calculateTotals();
            this.recalculateShipping();
        },

        calculateTotals() {
            let taxable = Math.max(0, this.subtotal - this.discount);
            this.tax = this.taxEnabled ? Math.round(taxable * (this.taxRate / 100) * 100) / 100 : 0;
            this.grandTotal = taxable + this.deliveryCharge + this.tax;
            this.advanceAmount = this.advanceRequired ? this.deliveryCharge : 0;
            this.remainingAmount = this.advanceRequired ? (this.grandTotal - this.advanceAmount) : this.grandTotal;

            if (this.advanceRequired && this.paymentMethod === 'cod') {
                this.paymentMethod = 'bkash';
            }
        },

        recalculateShipping() {
            if (!this.city || this.city.trim() === '') return;

            fetch('{{ route('checkout.calculate_shipping') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ city: this.city })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.zoneName = data.zone_name;
                    this.deliveryCharge = data.delivery_charge;
                    this.advanceRequired = data.advance_required;
                    this.advanceAmount = data.advance_amount;
                    this.remainingAmount = data.remaining_amount;
                    this.grandTotal = data.grand_total;
                    this.tax = data.tax_amount;
                    this.taxName = data.tax_name;
                    this.taxRate = data.tax_rate;
                    this.taxEnabled = data.tax_enabled;

                    if (this.advanceRequired && this.paymentMethod === 'cod') {
                        this.paymentMethod = 'bkash';
                    }
                }
            })
            .catch(err => console.error('Shipping calculation error:', err));
        },

        selectSavedAddress(addr) {
            this.fullName = addr.full_name;
            this.phone = addr.phone;
            this.addressLine1 = addr.address_line_1;
            this.city = addr.city;
            this.recalculateShipping();
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.copiedToast = true;
                setTimeout(() => this.copiedToast = false, 3000);
            });
        },

        formatMoney(amount) {
            return '৳' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
}
</script>
@endsection
