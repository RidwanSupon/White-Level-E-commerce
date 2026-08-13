<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">

    <title>{{ $site_name }} - {{ $site_tagline }}</title>

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: 'hsl(var(--primary) / 0.05)',
                            100: 'hsl(var(--primary) / 0.1)',
                            500: 'hsl(var(--primary) / 1)',
                            600: 'hsl(var(--primary) / 0.9)',
                            700: 'hsl(var(--primary) / 0.8)',
                        },
                        secondary: 'hsl(var(--secondary) / 1)',
                        accent: 'hsl(var(--accent) / 1)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js CDN for reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- White-Label Dynamic HSL Styling -->
    <style>
        {!! $white_label_css !!}
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        .touch-target { min-height: 44px; min-width: 44px; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="flex flex-col min-h-full text-slate-800 antialiased pb-20 md:pb-0" 
      x-data="globalCartStore()" 
      x-init="initCart()"
      @cart-updated.window="fetchCart()"
      @show-toast.window="triggerToast($event.detail)">

    <!-- Header Navigation -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Logo & Brand Name -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                        @if(!empty(setting('site_logo')) && file_exists(public_path(setting('site_logo'))))
                            <img src="{{ setting('site_logo') }}" alt="{{ $site_name }}" class="h-10 max-w-[160px] object-contain">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform">
                                {{ substr($site_name, 0, 1) }}
                            </div>
                        @endif
                        <div class="flex flex-col">
                            <span class="font-display font-bold text-xl sm:text-2xl text-slate-900 leading-none tracking-tight">{{ $site_name }}</span>
                            <span class="text-[10px] text-slate-500 font-medium tracking-wide uppercase mt-0.5">{{ $site_tagline }}</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Search Bar with Live Autocomplete Dropdown -->
                <div class="hidden md:flex flex-1 max-w-md mx-8 relative" x-data="{ query: '', results: [], open: false, fetchResults() { if(this.query.length < 2) { this.results = []; this.open = false; return; } fetch('/api/v1/search/autocomplete?q=' + encodeURIComponent(this.query)).then(res => res.json()).then(data => { this.results = data.results; this.open = true; }); } }">
                    <form action="{{ route('shop') }}" method="GET" class="w-full relative">
                        <input type="text" name="q" placeholder="Search smartphones, headphones, sneakers..." value="{{ request('q') }}"
                               x-model="query" @input.debounce.300ms="fetchResults()" @click.away="open = false"
                               class="w-full bg-slate-100/80 hover:bg-slate-100 focus:bg-white text-sm text-slate-900 rounded-full pl-11 pr-4 py-2.5 border border-transparent focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none transition-all">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </form>

                    <!-- Autocomplete Results Popup -->
                    <div x-show="open && results.length > 0" x-cloak class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden z-50 p-2 space-y-1">
                        <template x-for="item in results" :key="item.id">
                            <a :href="item.url" class="flex items-center gap-3 p-2.5 hover:bg-slate-50 rounded-xl transition-colors">
                                <img :src="item.featured_image" class="w-10 h-10 rounded-lg object-cover border border-slate-100 bg-slate-50">
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-xs font-bold text-slate-900 truncate" x-text="item.name"></h5>
                                    <span class="text-[10px] text-slate-400 uppercase" x-text="item.category"></span>
                                </div>
                                <span class="text-xs font-bold text-brand-500" x-text="item.price"></span>
                            </a>
                        </template>
                    </div>
                </div>

                <!-- Right Action Controls -->
                <div class="flex items-center gap-2 sm:gap-4">
                    @php
                        $navCmsPages = \App\Models\Page::where('is_published', true)->where('show_in_nav', true)->get();
                    @endphp
                    @foreach($navCmsPages as $navPage)
                        <a href="{{ route('page.show', $navPage->slug) }}" class="hidden lg:inline-block text-xs font-semibold text-slate-700 hover:text-brand-600 transition-colors">
                            {{ $navPage->title }}
                        </a>
                    @endforeach
                    
                    <!-- Search button (Mobile) -->
                    <button @click="mobileSearch = !mobileSearch" class="md:hidden p-2 text-slate-600 hover:text-slate-900 rounded-full hover:bg-slate-100 touch-target flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>

                    <!-- Interactive Real-Time Cart Icon with Live Count Badge -->
                    <button type="button" @click="isDrawerOpen = true" class="relative p-2.5 text-slate-700 hover:text-brand-500 rounded-full hover:bg-slate-100 transition-colors touch-target flex items-center justify-center group" title="View Cart">
                        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span x-show="count >= 0" 
                              x-text="count" 
                              class="absolute top-1 right-1 min-w-[20px] h-5 px-1 bg-brand-500 text-white text-[11px] font-extrabold rounded-full flex items-center justify-center shadow-md transition-transform duration-200"
                              :class="{ 'scale-125': isBouncing }">
                        </span>
                    </button>

                    <!-- User Account -->
                    @auth
                        <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2 px-3.5 py-2 rounded-full border border-slate-200 hover:border-slate-300 bg-white text-sm font-medium text-slate-700 hover:text-slate-900 shadow-sm transition-all touch-target">
                            <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-semibold text-xs">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-white bg-brand-500 hover:bg-brand-600 rounded-full shadow-md shadow-brand-500/20 transition-all touch-target flex items-center">
                            Sign In
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Mobile Expandable Search -->
            <div x-show="mobileSearch" x-collapse class="md:hidden pb-4">
                <form action="{{ route('shop') }}" method="GET" class="relative">
                    <input type="text" name="q" placeholder="Search products..." value="{{ request('q') }}"
                           class="w-full bg-slate-100 text-sm rounded-full pl-11 pr-4 py-2.5 outline-none border border-slate-200 focus:border-brand-500">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </form>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-2xl p-4 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Mini-Cart Slide-Over Side Drawer -->
    <div x-show="isDrawerOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden" 
         role="dialog" 
         aria-modal="true">
        
        <div x-show="isDrawerOpen" 
             x-transition:enter="ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="isDrawerOpen = false"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex md:pl-10 items-end md:items-stretch">
            <div x-show="isDrawerOpen"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-y-full md:translate-y-0 md:translate-x-full"
                 x-transition:enter-end="translate-y-0 md:translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-y-0 md:translate-x-0"
                 x-transition:leave-end="translate-y-full md:translate-y-0 md:translate-x-full"
                 class="w-full md:w-screen md:max-w-md max-h-[85vh] md:max-h-full rounded-t-3xl md:rounded-none bg-white shadow-2xl flex flex-col justify-between overflow-hidden">
                
                <!-- Drawer Header -->
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <h3 class="font-display font-bold text-base sm:text-lg text-slate-900">Your Shopping Cart</h3>
                        <span class="px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-600 font-extrabold text-xs" x-text="count + ' items'"></span>
                    </div>
                    <button type="button" @click="isDrawerOpen = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-full hover:bg-slate-100 touch-target">
                        ✕
                    </button>
                </div>

                <!-- Drawer Items List -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-3">
                    <template x-if="items.length === 0">
                        <div class="text-center py-12 space-y-4">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <h4 class="font-bold text-slate-900 text-base">Your cart is empty</h4>
                            <p class="text-xs text-slate-500 max-w-xs mx-auto">Explore our high quality products and add your favorite items to your shopping cart.</p>
                            <a href="{{ route('shop') }}" @click="isDrawerOpen = false" class="inline-block px-6 py-2.5 bg-brand-500 text-white rounded-full font-bold text-xs shadow-md shadow-brand-500/20">
                                Start Shopping
                            </a>
                        </div>
                    </template>

                    <template x-for="item in items" :key="item.id">
                        <div class="flex items-center gap-3 p-3 bg-slate-50/80 rounded-2xl border border-slate-100 relative group">
                            <div class="w-16 h-16 rounded-xl overflow-hidden bg-white border border-slate-200 shrink-0 flex items-center justify-center">
                                <img :src="item.image_url" :alt="item.name" onerror="this.onerror=null;this.src='/images/placeholder.png';" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <a :href="item.url" class="font-bold text-xs text-slate-900 truncate block hover:text-brand-500" x-text="item.name"></a>
                                <span x-show="item.variant_name" class="text-[10px] text-slate-500 font-semibold block" x-text="item.variant_name"></span>
                                <div class="flex items-center justify-between mt-1.5">
                                    <div class="flex items-center border border-slate-200 rounded-lg bg-white overflow-hidden">
                                        <button type="button" @click="updateQty(item.id, item.quantity - 1)" class="w-7 h-7 flex items-center justify-center text-xs font-bold text-slate-600 hover:bg-slate-100 touch-target">-</button>
                                        <span class="px-2 text-xs font-bold text-slate-900" x-text="item.quantity"></span>
                                        <button type="button" @click="updateQty(item.id, item.quantity + 1)" class="w-7 h-7 flex items-center justify-center text-xs font-bold text-slate-600 hover:bg-slate-100 touch-target">+</button>
                                    </div>
                                    <span class="font-extrabold text-xs text-slate-900" x-text="item.formatted_line_total"></span>
                                </div>
                            </div>
                            <button type="button" @click="removeItem(item.id)" class="text-slate-400 hover:text-rose-500 p-2 text-xs font-bold touch-target" title="Remove item">
                                ✕
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Drawer Footer Summary -->
                <div x-show="items.length > 0" class="p-4 sm:p-6 border-t border-slate-100 bg-slate-50/50 space-y-3">
                    <div class="flex items-center justify-between text-sm font-bold text-slate-900">
                        <span>Subtotal</span>
                        <span class="text-base text-brand-600 font-extrabold" x-text="formattedSubtotal"></span>
                    </div>
                    <p class="text-[10px] text-slate-400">Shipping, taxes, and discounts calculated at checkout.</p>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('cart.index') }}" @click="isDrawerOpen = false" class="py-3 px-4 text-center rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition-colors">
                            View Cart
                        </a>
                        <a href="{{ route('checkout.index') }}" @click="isDrawerOpen = false" class="py-3 px-4 text-center rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs shadow-lg shadow-brand-500/20 transition-all">
                            Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Global Toast Feedback Popup -->
    <div x-show="toastVisible" 
         x-cloak
         x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-24 right-5 z-50 bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-4 border border-slate-800 max-w-sm">
        <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
            ✓
        </div>
        <div class="flex-1 text-xs font-semibold" x-text="toastMessage"></div>
        <button type="button" @click="toastVisible = false" class="text-slate-400 hover:text-white font-bold text-xs">✕</button>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <h3 class="font-display text-lg font-bold text-white mb-3">{{ $site_name }}</h3>
                <p class="text-sm text-slate-400 mb-4">{{ $site_tagline }}</p>
                <p class="text-xs text-slate-500">{{ setting('footer_copyright') }}</p>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Shop</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('shop') }}" class="hover:text-white transition-colors">All Products</a></li>
                    <li><a href="{{ route('shop', ['sort' => 'latest']) }}" class="hover:text-white transition-colors">New Arrivals</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Customer Service</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('customer.dashboard') }}" class="hover:text-white transition-colors">Track Order</a></li>
                    @php
                        $footerCmsPages = \App\Models\Page::where('is_published', true)->where('show_in_footer', true)->get();
                    @endphp
                    @foreach($footerCmsPages as $cmsPage)
                        <li><a href="{{ route('page.show', $cmsPage->slug) }}" class="hover:text-white transition-colors">{{ $cmsPage->title }}</a></li>
                    @endforeach
                    <li><span class="text-slate-400">Email: {{ setting('contact_email') }}</span></li>
                    <li><span class="text-slate-400">Phone: {{ setting('contact_phone') }}</span></li>
                </ul>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Admin Portal</h4>
                <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700 transition-colors">
                    <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Go to Admin Portal
                </a>
            </div>
        </div>
    </footer>

    <!-- Native Mobile Bottom Navigation Bar (App-like UX) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-slate-200/80 px-2 py-1.5 flex items-center justify-around shadow-lg"
         style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <a href="{{ route('home') }}" class="flex flex-col items-center py-1 px-3 rounded-xl touch-target {{ request()->routeIs('home') ? 'text-brand-500 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-[10px] tracking-tight mt-0.5">Home</span>
        </a>
        <a href="{{ route('shop') }}" class="flex flex-col items-center py-1 px-3 rounded-xl touch-target {{ request()->routeIs('shop') ? 'text-brand-500 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
            <span class="text-[10px] tracking-tight mt-0.5">Shop</span>
        </a>
        <a href="{{ route('customer.wishlist.index') }}" class="flex flex-col items-center py-1 px-3 rounded-xl touch-target {{ request()->routeIs('customer.wishlist.*') ? 'text-brand-500 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <span class="text-[10px] tracking-tight mt-0.5">Saved</span>
        </a>
        <button type="button" @click="isDrawerOpen = true" class="flex flex-col items-center py-1 px-3 rounded-xl touch-target relative text-slate-500 hover:text-slate-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span x-show="count > 0" x-text="count" class="absolute top-1 right-2 min-w-[16px] h-4 px-1 bg-brand-500 text-white text-[9px] font-extrabold rounded-full flex items-center justify-center shadow-sm"></span>
            <span class="text-[10px] tracking-tight mt-0.5">Cart</span>
        </button>
        <a href="{{ auth()->check() ? route('customer.dashboard') : route('login') }}" class="flex flex-col items-center py-1 px-3 rounded-xl touch-target {{ request()->routeIs('customer.dashboard') || request()->routeIs('login') ? 'text-brand-500 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="text-[10px] tracking-tight mt-0.5">{{ auth()->check() ? 'Account' : 'Sign In' }}</span>
        </a>
    </nav>

    <script>
    function globalCartStore() {
        return {
            mobileSearch: false,
            isDrawerOpen: false,
            count: 0,
            subtotal: 0,
            formattedSubtotal: '৳0.00',
            items: [],
            isBouncing: false,
            toastMessage: '',
            toastVisible: false,

            initCart() {
                this.fetchCart();
                window.refreshCart = () => this.fetchCart();
            },

            fetchCart() {
                fetch('{{ route("cart.data") }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.summary) {
                            this.count = data.summary.count;
                            this.subtotal = data.summary.subtotal;
                            this.formattedSubtotal = data.summary.formatted_subtotal;
                            this.items = data.summary.items;
                        }
                    })
                    .catch(err => console.error("Error fetching cart data:", err));
            },

            triggerToast(msg) {
                this.toastMessage = msg;
                this.toastVisible = true;
                this.isBouncing = true;
                setTimeout(() => this.isBouncing = false, 300);
                setTimeout(() => this.toastVisible = false, 3500);
            },

            updateQty(itemId, newQty) {
                if (newQty < 1) return;
                fetch('/cart/item/' + itemId, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: newQty })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.fetchCart();
                    } else if (data.message) {
                        this.triggerToast(data.message);
                    }
                });
            },

            removeItem(itemId) {
                fetch('/cart/item/' + itemId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.triggerToast('Product removed from cart.');
                        this.fetchCart();
                    }
                });
            }
        }
    }
    </script>
</body>
</html>
