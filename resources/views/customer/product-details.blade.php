@extends('layouts.app')

@section('content')
@php
    $galleryList = $product->images->pluck('image_path')->toArray();
    if (empty($galleryList)) {
        $galleryList = [$product->featured_image_url];
    }
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="productGalleryComponent({{ json_encode($galleryList) }}, '{{ $product->featured_image_url }}', {{ $product->variants->isNotEmpty() ? 'true' : 'false' }}, {{ $product->stock_quantity }})">
    
    <!-- Breadcrumb -->
    <nav class="flex text-xs font-medium text-slate-500 mb-6 gap-2">
        <a href="{{ route('home') }}" class="hover:text-slate-900">Home</a>
        <span>/</span>
        <a href="{{ route('shop') }}" class="hover:text-slate-900">Shop</a>
        <span>/</span>
        <span class="text-slate-900 font-semibold truncate">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm">
        
        <!-- Gallery Container -->
        <div class="space-y-4">
            <!-- Main Product Image Container with Desktop Hover Zoom & Mobile Touch Swipe -->
            <div class="product-image-container relative w-full h-80 sm:h-96 rounded-2xl overflow-hidden bg-slate-50 border border-slate-200 flex items-center justify-center cursor-zoom-in group select-none"
                 @mouseenter="isZoomed = true"
                 @mouseleave="isZoomed = false; zoomX = 50; zoomY = 50"
                 @mousemove="handleMouseMove($event)"
                 @touchstart="touchStartX = $event.changedTouches[0].screenX"
                 @touchend="touchEndX = $event.changedTouches[0].screenX; handleSwipe()"
                 @click="isLightboxOpen = true">
                 
                <!-- Main Zoomable Image -->
                <img :src="activeImg" 
                     alt="{{ $product->name }}" 
                     onerror="this.onerror=null;this.src='/images/placeholder.png';"
                     class="w-full h-full object-cover transition-transform duration-200 ease-out pointer-events-none"
                     :style="isZoomed ? 'transform: scale(1.85); transform-origin: ' + zoomX + '% ' + zoomY + '%;' : 'transform: scale(1); transform-origin: center;'">

                <!-- Desktop Hover Zoom Instruction Badge -->
                <div class="hidden md:flex absolute top-3 right-3 px-2.5 py-1 bg-slate-950/70 text-white text-[10px] font-bold rounded-full items-center gap-1.5 backdrop-blur-sm opacity-80 group-hover:opacity-100 transition-opacity">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                    Hover to Zoom
                </div>

                <!-- Mobile Swipe Next / Prev Buttons -->
                <button type="button" @click.stop="prevImage()" class="md:hidden absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-950/60 text-white flex items-center justify-center font-bold text-sm backdrop-blur-sm">
                    ‹
                </button>
                <button type="button" @click.stop="nextImage()" class="md:hidden absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-950/60 text-white flex items-center justify-center font-bold text-sm backdrop-blur-sm">
                    ›
                </button>

                <!-- Mobile Image Counter Badge (e.g. 1 / 5) -->
                <div class="absolute bottom-3 right-3 px-3 py-1 bg-slate-950/80 text-white text-xs font-mono font-bold rounded-full backdrop-blur-sm shadow-md">
                    <span x-text="activeIdx + 1"></span> / <span x-text="images.length"></span>
                </div>
            </div>

            <!-- Thumbnail Selector Gallery -->
            @if($product->images->isNotEmpty())
                <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-thin">
                    <template x-for="(imgUrl, idx) in images" :key="idx">
                        <button type="button" 
                                @click="selectImage(idx)" 
                                :class="activeIdx === idx ? 'border-brand-500 ring-2 ring-brand-500/30 scale-[1.03]' : 'border-slate-200 hover:border-brand-400 opacity-75 hover:opacity-100'"
                                class="product-image-container w-20 h-20 rounded-xl overflow-hidden border-2 shrink-0 relative transition-all duration-150 bg-slate-100 flex items-center justify-center">
                            <img :src="imgUrl" 
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='/images/placeholder.png';" 
                                 class="w-full h-full object-cover">
                            <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-slate-950/80 text-white font-mono text-[9px] font-bold"
                                  x-text="'#' + (idx + 1)"></span>
                        </button>
                    </template>
                </div>
            @endif
        </div>

        <!-- Product Specs & Action Form -->
        <div class="flex flex-col justify-between">
            <div>
                <span class="inline-block px-3 py-1 bg-brand-50 text-brand-600 text-xs font-bold uppercase rounded-full tracking-wider mb-3">
                    {{ $product->brand?->name ?? 'Official Warranty' }}
                </span>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 mb-3">{{ $product->name }}</h1>
                
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex text-amber-400">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= round($product->rating_cache) ? 'fill-current' : 'text-slate-200' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <span class="text-xs text-slate-500 font-semibold">({{ $product->reviews_count }} Verified Reviews)</span>
                </div>

                <div class="flex items-baseline gap-4 mb-6">
                    <span class="font-display text-3xl font-bold text-slate-900" x-text="currentPrice"></span>
                    @if($product->compare_price)
                        <span class="text-sm text-slate-400 line-through">{{ format_price($product->compare_price) }}</span>
                    @endif
                </div>

                <p class="text-slate-600 text-sm leading-relaxed mb-6">
                    {{ $product->description }}
                </p>

                <!-- Product Variant Selection (Required if variants exist) -->
                @if($product->variants->isNotEmpty())
                    <div class="mb-6 space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Select Variant / Option <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            @foreach($product->variants as $variant)
                                <button type="button" 
                                        @click="selectVariant({{ json_encode($variant) }})" 
                                        :class="selectedVariantId === {{ $variant->id }} ? 'border-brand-500 bg-brand-50/50 ring-2 ring-brand-500/20 text-brand-600 font-bold' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300'"
                                        class="p-3 rounded-xl border text-xs font-semibold flex flex-col items-start transition-all text-left">
                                    <span class="font-mono text-[11px] truncate w-full">{{ $variant->sku }}</span>
                                    <span class="text-[10px] text-slate-500 mt-0.5">Stock: {{ $variant->stock_quantity }}</span>
                                    <span class="font-extrabold text-slate-900 mt-1">{{ format_price($variant->price) }}</span>
                                </button>
                            @endforeach
                        </div>
                        <p x-show="variantError" x-cloak class="text-xs font-bold text-rose-500 mt-1">⚠️ Please select a product option.</p>
                    </div>
                @endif

                <!-- Stock Badge -->
                <div class="mb-6">
                    <template x-if="maxStock > 0">
                        <span class="inline-flex items-center gap-2 text-xs font-bold text-emerald-600 bg-emerald-50 px-3.5 py-1.5 rounded-full">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> In Stock (<span x-text="maxStock"></span> units remaining)
                        </span>
                    </template>
                    <template x-if="maxStock <= 0">
                        <span class="inline-flex items-center gap-2 text-xs font-bold text-rose-600 bg-rose-50 px-3.5 py-1.5 rounded-full">
                            Out of Stock
                        </span>
                    </template>
                </div>
            </div>

            <!-- Action Controls Form (Add to Cart & Buy Now) -->
            <div class="space-y-4 pt-6 border-t border-slate-100">
                
                <!-- Professional Quantity Selector [-] 1 [+] -->
                <div class="flex items-center gap-4">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Quantity:</label>
                    <div class="flex items-center border border-slate-200 rounded-full px-3 py-1.5 bg-slate-50 shadow-inner">
                        <button type="button" @click="decrementQty()" class="w-8 h-8 rounded-full text-slate-600 hover:bg-slate-200 font-extrabold text-base flex items-center justify-center transition-colors">
                            -
                        </button>
                        <input type="number" x-model.number="qty" readonly class="w-12 text-center text-sm font-extrabold bg-transparent outline-none text-slate-900">
                        <button type="button" @click="incrementQty()" class="w-8 h-8 rounded-full text-slate-600 hover:bg-slate-200 font-extrabold text-base flex items-center justify-center transition-colors">
                            +
                        </button>
                    </div>
                    <span class="text-xs text-slate-400 font-semibold" x-text="'Max: ' + maxStock"></span>
                </div>

                <!-- Action Buttons: [ Add to Cart ] & [ Buy Now ] -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <button type="button" 
                            @click="addToCart()" 
                            :disabled="maxStock <= 0"
                            class="w-full py-4 px-6 bg-brand-50 hover:bg-brand-100 text-brand-600 font-bold text-sm rounded-2xl border border-brand-200 transition-all hover:scale-[1.01] touch-target flex items-center justify-center gap-2 disabled:opacity-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Add to Cart
                    </button>

                    <button type="button" 
                            @click="buyNow()" 
                            :disabled="maxStock <= 0"
                            class="w-full py-4 px-6 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-brand-500/25 transition-all hover:scale-[1.01] touch-target flex items-center justify-center gap-2 disabled:opacity-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        ⚡ Buy Now
                    </button>
                </div>
        </div>
    </div>

    <!-- Related Products Section with 2-Column Mobile Grid -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
        <div class="mt-16 pt-10 border-t border-slate-200/80 space-y-6">
            <div>
                <h3 class="font-display text-2xl font-bold text-slate-900">You May Also Like</h3>
                <p class="text-slate-500 text-xs mt-1">Explore similar items in this category</p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
                @foreach($relatedProducts as $relProduct)
                    <x-product-card :product="$relProduct" />
                @endforeach
            </div>
        </div>
    @endif

    <!-- Mobile & Fullscreen Lightbox Modal -->
    <div x-show="isLightboxOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-4">
        
        <button type="button" @click="isLightboxOpen = false" class="absolute top-5 right-5 text-white bg-slate-900/80 hover:bg-slate-800 p-2.5 rounded-full font-bold text-lg">
            ✕
        </button>

        <button type="button" @click="prevImage()" class="absolute left-4 top-1/2 -translate-y-1/2 text-white bg-slate-900/80 hover:bg-slate-800 w-12 h-12 rounded-full font-bold text-xl flex items-center justify-center shadow-lg">
            ‹
        </button>

        <div class="max-w-4xl max-h-[85vh] p-2 flex items-center justify-center">
            <img :src="activeImg" class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl">
        </div>

        <button type="button" @click="nextImage()" class="absolute right-4 top-1/2 -translate-y-1/2 text-white bg-slate-900/80 hover:bg-slate-800 w-12 h-12 rounded-full font-bold text-xl flex items-center justify-center shadow-lg">
            ›
        </button>

        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white font-mono text-sm font-bold bg-slate-900/80 px-4 py-1.5 rounded-full shadow-md">
            <span x-text="activeIdx + 1"></span> / <span x-text="images.length"></span>
        </div>
    </div>
</div>

<script>
function productGalleryComponent(images, initialFeaturedUrl, hasVariants, defaultStock) {
    return {
        images: images && images.length ? images : [initialFeaturedUrl],
        activeIdx: 0,
        activeImg: initialFeaturedUrl,
        isZoomed: false,
        zoomX: 50,
        zoomY: 50,
        touchStartX: 0,
        touchEndX: 0,
        isLightboxOpen: false,

        // Variant & Stock State
        hasVariants: hasVariants,
        selectedVariantId: null,
        variantError: false,
        maxStock: defaultStock,
        qty: 1,
        currentPrice: '{{ format_price($product->price) }}',

        init() {
            if (this.images.length > 0) {
                const matchIdx = this.images.indexOf(initialFeaturedUrl);
                if (matchIdx !== -1) {
                    this.activeIdx = matchIdx;
                } else {
                    this.activeImg = this.images[0];
                }
            }
        },

        selectVariant(variant) {
            this.selectedVariantId = variant.id;
            this.maxStock = variant.stock_quantity;
            this.currentPrice = '৳' + parseFloat(variant.price).toLocaleString();
            this.variantError = false;
            if (this.qty > this.maxStock) {
                this.qty = Math.max(1, this.maxStock);
            }
        },

        incrementQty() {
            if (this.qty < this.maxStock) {
                this.qty++;
            } else {
                window.dispatchEvent(new CustomEvent('show-toast', { detail: 'Only ' + this.maxStock + ' items are available.' }));
            }
        },

        decrementQty() {
            if (this.qty > 1) {
                this.qty--;
            }
        },

        validateSelection() {
            if (this.hasVariants && !this.selectedVariantId) {
                this.variantError = true;
                return false;
            }
            if (this.qty > this.maxStock) {
                window.dispatchEvent(new CustomEvent('show-toast', { detail: 'Only ' + this.maxStock + ' items are available.' }));
                return false;
            }
            return true;
        },

        addToCart() {
            if (!this.validateSelection()) return;

            fetch('{{ route("cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    product_id: {{ $product->id }},
                    product_variant_id: this.selectedVariantId,
                    quantity: this.qty
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: data.message || 'Added to cart successfully.' }));
                    // Open mini-cart drawer
                    if (window.Alpine) {
                        const root = document.querySelector('body');
                        if (root && root._x_dataStack && root._x_dataStack[0]) {
                            root._x_dataStack[0].isDrawerOpen = true;
                        }
                    }
                } else if (data.message) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: data.message }));
                }
            })
            .catch(err => console.error("Add to cart error:", err));
        },

        buyNow() {
            if (!this.validateSelection()) return;

            fetch('{{ route("buy_now") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    product_id: {{ $product->id }},
                    product_variant_id: this.selectedVariantId,
                    quantity: this.qty
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else if (data.message) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: data.message }));
                }
            })
            .catch(err => console.error("Buy now error:", err));
        },

        selectImage(idx) {
            if (idx >= 0 && idx < this.images.length) {
                this.activeIdx = idx;
                this.activeImg = this.images[idx];
            }
        },

        nextImage() {
            this.activeIdx = (this.activeIdx + 1) % this.images.length;
            this.activeImg = this.images[this.activeIdx];
        },

        prevImage() {
            this.activeIdx = (this.activeIdx - 1 + this.images.length) % this.images.length;
            this.activeImg = this.images[this.activeIdx];
        },

        handleMouseMove(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            this.zoomX = Math.max(0, Math.min(100, x));
            this.zoomY = Math.max(0, Math.min(100, y));
        },

        handleSwipe() {
            const diff = this.touchStartX - this.touchEndX;
            if (Math.abs(diff) > 40) {
                if (diff > 0) {
                    this.nextImage();
                } else {
                    this.prevImage();
                }
            }
        }
    }
}
</script>
@endsection
