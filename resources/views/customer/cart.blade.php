@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 pb-48 md:pb-12" 
     x-data="{
        loadingItems: {},
        isUpdating: false,
        init() {
            if ($store.cart && (!items || items.length === 0)) {
                $store.cart.fetchCart();
            }
        }
     }">
    
    <!-- ------------------------------------------------------------- -->
    <!-- DEDICATED NATIVE MOBILE CART UX (< 768px)                      -->
    <!-- ------------------------------------------------------------- -->
    <div class="md:hidden space-y-5">
        <!-- Clean Mobile Cart Header -->
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-3.5">
            <div class="flex items-center gap-3">
                <a href="{{ route('shop') }}" class="p-2 text-slate-600 hover:text-slate-900 rounded-full bg-slate-100 touch-target flex items-center justify-center" aria-label="Continue Shopping">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="font-display text-xl font-extrabold text-slate-900 leading-none">Your Cart</h1>
                    <span class="text-xs text-slate-500 font-semibold mt-1 block" x-text="count > 0 ? count + (count === 1 ? ' Item' : ' Items') : '0 Items'"></span>
                </div>
            </div>
            <a href="{{ route('shop') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Shop More →</a>
        </div>

        <!-- Mobile Empty Cart UI -->
        <template x-if="!items || items.length === 0">
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-200 shadow-sm space-y-4 my-6">
                <div class="w-20 h-20 bg-brand-50 rounded-full flex items-center justify-center mx-auto text-brand-500">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900">Your cart is empty</h3>
                <p class="text-slate-500 text-xs leading-relaxed max-w-xs mx-auto">Explore our high quality collection and discover your favorite products.</p>
                <a href="{{ route('shop') }}" class="px-8 py-3 bg-brand-500 text-white rounded-full font-bold text-xs shadow-md shadow-brand-500/20 inline-flex items-center gap-2 touch-target">
                    Start Shopping Now
                </a>
            </div>
        </template>

        <!-- Mobile Cart Product Cards & Summary -->
        <template x-if="items && items.length > 0">
            <div class="space-y-5">
                <!-- Free Shipping Progress Bar Mobile -->
                <div class="bg-brand-50/70 rounded-2xl p-3.5 border border-brand-100 shadow-2xs space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold text-brand-900">
                        <span>Free Shipping Goal</span>
                        <span x-text="formattedSubtotal"></span>
                    </div>
                    <div class="w-full h-2 bg-slate-200/80 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-500 transition-all duration-500" :style="'width: ' + Math.min(100, Math.round((subtotal / 5000) * 100)) + '%'"></div>
                    </div>
                </div>

                <!-- Product Cards List -->
                <div class="space-y-3.5">
                    <template x-for="item in items" :key="item.id">
                        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3 relative overflow-hidden">
                            <!-- Loading overlay for item update -->
                            <div x-show="loadingItems[item.id]" class="absolute inset-0 bg-white/70 backdrop-blur-xs z-10 flex items-center justify-center">
                                <svg class="animate-spin h-6 w-6 text-brand-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>

                            <div class="flex gap-3.5">
                                <!-- Fixed Aspect Ratio Product Image -->
                                <a :href="item.url" class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden bg-slate-100 border border-slate-200/80 shrink-0 flex items-center justify-center block">
                                    <img :src="item.image_url" :alt="item.name" onerror="this.onerror=null;this.src='/images/placeholder.png';" class="w-full h-full object-cover">
                                </a>

                                <div class="flex-1 min-w-0 space-y-1">
                                    <a :href="item.url" class="font-bold text-slate-900 text-sm line-clamp-2 hover:text-brand-600 leading-snug block" x-text="item.name"></a>
                                    <div x-show="item.variant_name" class="text-xs text-brand-600 font-semibold truncate" x-text="item.variant_name"></div>
                                    <div class="text-sm font-extrabold text-slate-900" x-text="item.formatted_unit_price"></div>
                                </div>
                            </div>

                            <!-- Quantity Control [-] qty [+] and Remove Action -->
                            <div class="flex items-center justify-between pt-2.5 border-t border-slate-100">
                                <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 overflow-hidden shadow-2xs">
                                    <button type="button" 
                                            @click="loadingItems[item.id] = true; updateQty(item.id, item.quantity - 1); setTimeout(() => loadingItems[item.id] = false, 400)" 
                                            :disabled="item.quantity <= 1"
                                            :class="item.quantity <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-200 active:bg-slate-300'"
                                            class="w-10 h-10 flex items-center justify-center text-slate-700 font-black text-base touch-target transition-colors" 
                                            aria-label="Decrease quantity">-</button>
                                    
                                    <span class="px-3.5 text-xs font-extrabold text-slate-900 min-w-[28px] text-center" x-text="item.quantity"></span>
                                    
                                    <button type="button" 
                                            @click="loadingItems[item.id] = true; updateQty(item.id, item.quantity + 1); setTimeout(() => loadingItems[item.id] = false, 400)" 
                                            :disabled="item.available_stock && item.quantity >= item.available_stock"
                                            :class="(item.available_stock && item.quantity >= item.available_stock) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-200 active:bg-slate-300'"
                                            class="w-10 h-10 flex items-center justify-center text-slate-700 font-black text-base touch-target transition-colors" 
                                            aria-label="Increase quantity">+</button>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="font-black text-slate-900 text-sm" x-text="item.formatted_line_total"></span>
                                    <button type="button" @click="loadingItems[item.id] = true; removeItem(item.id); setTimeout(() => loadingItems[item.id] = false, 400)" class="text-xs font-bold text-rose-500 hover:text-rose-700 p-2 touch-target flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Remove</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Mobile Order Summary Card -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-xs space-y-3">
                    <h3 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-2">Order Summary</h3>
                    
                    <div class="space-y-2 text-xs font-semibold text-slate-600">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span class="text-slate-900 font-bold" x-text="formattedSubtotal"></span>
                        </div>
                        
                        @if(($summary['discount'] ?? 0) > 0)
                            <div class="flex justify-between text-emerald-600 font-bold">
                                <span>Discount</span>
                                <span>-{{ format_price($summary['discount']) }}</span>
                            </div>
                        @endif

                        @if(app(\App\Services\TaxService::class)->isTaxEnabled())
                            <div class="flex justify-between">
                                <span>Tax (VAT)</span>
                                <span class="text-slate-900 font-bold">{{ format_price($summary['tax'] ?? 0) }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <span>Delivery Fee</span>
                            <span class="text-slate-900 font-bold">{{ ($summary['shipping_fee'] ?? 0) == 0 ? 'FREE' : format_price($summary['shipping_fee']) }}</span>
                        </div>
                    </div>

                    <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200/80 text-[11px] text-amber-900 flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong>Outside Dhaka Delivery:</strong> Delivery charge is payable in advance at checkout via bKash or Nagad.</span>
                    </div>

                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between text-sm font-black text-slate-900">
                        <span>Grand Total</span>
                        <span class="text-base text-brand-600 font-display font-black" x-text="formattedSubtotal"></span>
                    </div>
                </div>
            </div>
        </template>

        <!-- ALWAYS REACHABLE STICKY MOBILE CHECKOUT BAR (< 768px) -->
        <!-- Positioned above native mobile bottom nav (bottom-14) with z-50 and safe area padding -->
        <template x-if="items && items.length > 0">
            <div class="fixed left-0 right-0 z-50 bg-white/95 backdrop-blur-xl border-t border-slate-200/90 p-3.5 sm:p-4 shadow-2xl transition-all"
                 style="bottom: calc(3.5rem + env(safe-area-inset-bottom, 0px));">
                <div class="max-w-md mx-auto flex flex-row items-center justify-between gap-3">
                    <div class="shrink-0">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider leading-tight">Total Amount</span>
                        <span class="text-base sm:text-lg font-black text-brand-600 font-display" x-text="formattedSubtotal"></span>
                    </div>
                    <a href="{{ route('checkout.index') }}" 
                       class="flex-1 py-3.5 px-5 bg-brand-500 hover:bg-brand-600 active:scale-98 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-brand-500/25 transition-all text-center flex items-center justify-center gap-2 touch-target outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        <span>Proceed to Checkout</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        </template>
    </div>


    <!-- ------------------------------------------------------------- -->
    <!-- DESKTOP & TABLET SHOPPING CART LAYOUT (>= 768px)              -->
    <!-- ------------------------------------------------------------- -->
    <div class="hidden md:block">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-display text-3xl font-extrabold text-slate-900">Your Shopping Cart</h1>
                <p class="text-xs text-slate-500 mt-1">Review your selected items and proceed to checkout.</p>
            </div>
            <a href="{{ route('shop') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">← Continue Shopping</a>
        </div>

        @if(empty($summary['items']))
            <!-- Desktop Empty Cart View -->
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm max-w-xl mx-auto space-y-4">
                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-300">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-900">Your shopping cart is empty</h3>
                <p class="text-slate-500 text-sm max-w-md mx-auto">Looks like you haven't added anything to your cart yet. Explore our high quality catalog and discover great deals.</p>
                <a href="{{ route('shop') }}" class="px-8 py-3.5 bg-brand-500 text-white rounded-full font-bold text-sm shadow-md shadow-brand-500/20 inline-flex items-center gap-2 hover:bg-brand-600 transition-colors">
                    Start Shopping Now
                </a>
            </div>
        @else
            <!-- Desktop Free Shipping Progress Bar -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 mb-8 shadow-sm">
                <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                    <span>Free Shipping Threshold (Target: {{ format_price($summary['free_shipping_threshold']) }})</span>
                    <span>{{ $summary['shipping_progress'] }}%</span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-500 transition-all duration-500" style="width: {{ $summary['shipping_progress'] }}%"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items List -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($summary['items'] as $item)
                        <div class="bg-white rounded-2xl p-4 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-4 w-full sm:w-auto">
                                <div class="product-image-container w-20 h-20 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0 flex items-center justify-center relative">
                                    <img src="{{ $item['image_url'] }}" 
                                         alt="{{ $item['name'] }}" 
                                         onerror="this.onerror=null;this.src='/images/placeholder.png';" 
                                         class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <a href="{{ $item['url'] }}" class="font-bold text-slate-900 text-base line-clamp-1 hover:text-brand-500">{{ $item['name'] }}</a>
                                    @if(!empty($item['variant_name']))
                                        <span class="text-xs text-brand-600 font-semibold block mt-0.5">Variant: {{ $item['variant_name'] }}</span>
                                    @endif
                                    <span class="text-xs text-slate-500 font-semibold block mt-0.5">{{ $item['formatted_unit_price'] }} each</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100">
                                <!-- Quantity Controls [-] qty [+] -->
                                <form action="{{ route('cart.update', $item['id']) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 overflow-hidden">
                                        <button type="submit" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}" class="px-2.5 py-1 text-slate-600 hover:bg-slate-200 font-bold text-xs">-</button>
                                        <span class="px-3 py-1 text-xs font-bold text-slate-900 bg-white">{{ $item['quantity'] }}</span>
                                        <button type="submit" name="quantity" value="{{ min($item['available_stock'], $item['quantity'] + 1) }}" class="px-2.5 py-1 text-slate-600 hover:bg-slate-200 font-bold text-xs">+</button>
                                    </div>
                                </form>

                                <span class="font-extrabold text-slate-900 text-base min-w-[90px] text-right">{{ $item['formatted_line_total'] }}</span>

                                <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-500 transition-colors" title="Remove item">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Order Summary Sidebar -->
                <div class="space-y-6">
                    <!-- Promo Coupon Form -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-3">
                        <h4 class="font-bold text-slate-900 text-sm">Have a Coupon Code?</h4>
                        <form action="{{ route('cart.coupon') }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="coupon_code" placeholder="PROMO2026" value="{{ $summary['coupon_code'] }}" required class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 uppercase outline-none focus:border-brand-500">
                            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-colors">Apply</button>
                        </form>
                    </div>

                    <!-- Order Calculations -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                        <h3 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">Order Summary</h3>

                        <div class="space-y-2 text-xs font-semibold text-slate-600">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span class="text-slate-900 font-bold">{{ $summary['formatted_subtotal'] }}</span>
                            </div>
                            @if(($summary['discount'] ?? 0) > 0)
                                <div class="flex justify-between text-emerald-600">
                                    <span>Discount</span>
                                    <span class="font-bold">-{{ $summary['formatted_discount'] }}</span>
                                </div>
                            @endif
                            @if(app(\App\Services\TaxService::class)->isTaxEnabled())
                                <div class="flex justify-between">
                                    <span>Tax (VAT)</span>
                                    <span class="text-slate-900 font-bold">{{ format_price($summary['tax'] ?? 0) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span>Shipping Fee</span>
                                <span class="text-slate-900 font-bold">{{ ($summary['shipping_fee'] ?? 0) == 0 ? 'FREE' : $summary['formatted_shipping_fee'] }}</span>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-3 flex items-center justify-between text-base font-extrabold text-slate-900">
                            <span>Grand Total</span>
                            <span class="text-xl text-brand-600 font-display">{{ $summary['formatted_grand_total'] }}</span>
                        </div>

                        <a href="{{ route('checkout.index') }}" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-brand-500/25 transition-all flex items-center justify-center gap-2">
                            Proceed to Checkout →
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
