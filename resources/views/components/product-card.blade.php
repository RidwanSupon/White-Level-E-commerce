@props(['product'])

@php
    $hasVariants = $product->variants()->where('is_active', true)->count() > 0;
    $rating = $product->average_rating ?? 4.8;
    $isSaved = auth()->check() ? \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists() : false;
@endphp

<div class="group bg-white rounded-2xl sm:rounded-3xl p-3 sm:p-4 border border-slate-200/90 shadow-2xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative overflow-hidden"
     x-data="{ 
        isSaved: {{ $isSaved ? 'true' : 'false' }},
        isAdding: false,
        toggleWishlist() {
            @if(auth()->check())
                fetch('{{ route('customer.wishlist.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: {{ $product->id }} })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'added') {
                        this.isSaved = true;
                        $store.cart.triggerToast('Added to Wishlist ❤️');
                    } else {
                        this.isSaved = false;
                        $store.cart.triggerToast('Removed from Wishlist');
                    }
                });
            @else
                window.location.href = '{{ route('login') }}';
            @endif
        },
        addToCartDirect() {
            @if($hasVariants)
                window.location.href = '{{ route('product.show', $product->slug) }}';
            @else
                this.isAdding = true;
                fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
                })
                .then(res => res.json())
                .then(data => {
                    this.isAdding = false;
                    if (data.success) {
                        if (window.refreshCart) window.refreshCart();
                        $store.cart.triggerToast('Item added to cart! 🛒');
                    } else if (data.message) {
                        $store.cart.triggerToast(data.message);
                    }
                })
                .catch(err => {
                    this.isAdding = false;
                    console.error(err);
                });
            @endif
        }
     }">

    <div>
        <!-- Fixed Aspect Ratio Image Container -->
        <div class="relative w-full aspect-square rounded-xl sm:rounded-2xl overflow-hidden bg-slate-100 mb-2.5 sm:mb-3">
            <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                <img src="{{ $product->featured_image }}" 
                     alt="{{ $product->name }}" 
                     onerror="this.onerror=null;this.src='/images/placeholder.png';"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </a>

            <!-- Discount Badge (Top Left) -->
            @if($product->discount_percentage)
                <span class="absolute top-2 left-2 bg-rose-500 text-white text-[10px] sm:text-[11px] font-extrabold px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full shadow-xs">
                    -{{ $product->discount_percentage }}%
                </span>
            @endif

            <!-- Wishlist Heart Button (Top Right) -->
            <button type="button" 
                    @click.stop="toggleWishlist()"
                    class="absolute top-2 right-2 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/90 backdrop-blur-xs flex items-center justify-center shadow-sm transition-all touch-target"
                    :class="isSaved ? 'text-rose-500 bg-white' : 'text-slate-400 hover:text-rose-500'"
                    aria-label="Save to Wishlist">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 transition-transform active:scale-125" :fill="isSaved ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        </div>

        <!-- Product Category & Title (Fixed height area with line-clamp-2 to prevent layout shifts) -->
        <div class="space-y-1">
            <span class="text-[10px] sm:text-xs font-bold text-brand-600 uppercase tracking-wider block truncate">
                {{ $product->category?->name ?? 'Collection' }}
            </span>
            <h3 class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-2 hover:text-brand-600 transition-colors leading-snug min-h-[2rem] sm:min-h-[2.5rem]">
                <a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a>
            </h3>
        </div>

        <!-- Rating Star Badge -->
        <div class="flex items-center gap-1 mt-1 mb-2 text-[11px] font-bold text-amber-500">
            <span>★</span>
            <span class="text-slate-700 text-[10px] sm:text-xs font-bold">{{ number_format($rating, 1) }}</span>
        </div>
    </div>

    <!-- Card Footer: Price & Add to Cart Action -->
    <div class="pt-2 sm:pt-3 border-t border-slate-100 flex items-center justify-between gap-1.5 mt-1">
        <div class="min-w-0">
            <span class="text-xs sm:text-sm font-black text-slate-900 block truncate">{{ format_price($product->price) }}</span>
            @if($product->compare_price)
                <span class="text-[10px] sm:text-xs text-slate-400 line-through block truncate">{{ format_price($product->compare_price) }}</span>
            @endif
        </div>

        @if($hasVariants)
            <a href="{{ route('product.show', $product->slug) }}" 
               class="px-2.5 py-2 sm:px-3 sm:py-2 bg-slate-900 hover:bg-brand-600 text-white rounded-xl sm:rounded-2xl text-[10px] sm:text-xs font-extrabold shadow-xs transition-all touch-target shrink-0 flex items-center justify-center gap-1"
               title="Select Options">
                <span>Select</span>
            </a>
        @else
            <button type="button" 
                    @click="addToCartDirect()" 
                    :disabled="isAdding || {{ $product->stock_quantity <= 0 ? 'true' : 'false' }}"
                    class="p-2 sm:p-2.5 bg-brand-500 hover:bg-brand-600 active:scale-95 text-white rounded-xl sm:rounded-2xl shadow-sm shadow-brand-500/20 transition-all touch-target shrink-0 flex items-center justify-center disabled:opacity-50"
                    aria-label="Add to Cart">
                <template x-if="!isAdding">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                </template>
                <template x-if="isAdding">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </template>
            </button>
        @endif
    </div>
</div>
