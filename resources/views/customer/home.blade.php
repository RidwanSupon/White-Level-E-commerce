@extends('layouts.app')

@section('content')
<!-- Hero Section with Ambient Glow -->
<div class="relative overflow-hidden bg-slate-900 text-white py-16 sm:py-24">
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-500/20 text-brand-400 text-xs font-bold uppercase tracking-wider mb-6 border border-brand-500/30">
                    ✨ Premium White-Label E-Commerce Platform
                </span>
                <h1 class="font-display text-4xl sm:text-6xl font-extrabold tracking-tight leading-tight text-white mb-6">
                    Luxury Shopping, <span class="bg-gradient-to-r from-brand-400 to-indigo-300 bg-clip-text text-transparent">Redefined.</span>
                </h1>
                <p class="text-slate-300 text-base sm:text-lg mb-8 leading-relaxed">
                    Explore curated flagship smartphones, noise-canceling audio systems, luxury footwear, and modern home accessories with instant delivery.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('shop') }}" class="px-7 py-3.5 rounded-full bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm shadow-lg shadow-brand-500/30 transition-all hover:scale-105 touch-target flex items-center">
                        Explore Collection
                    </a>
                    <a href="{{ route('shop', ['sort' => 'latest']) }}" class="px-7 py-3.5 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm border border-slate-700 transition-all touch-target flex items-center">
                        New Arrivals
                    </a>
                </div>
            </div>
            <div class="relative">
                <div class="relative mx-auto max-w-md lg:max-w-none rounded-3xl overflow-hidden shadow-2xl border border-slate-800 bg-slate-800/50 backdrop-blur-xl p-4">
                    <img src="https://images.unsplash.com/photo-1695048133142-1a20484d2569?auto=format&fit=crop&w=800&q=80" alt="Hero Product" class="w-full h-80 sm:h-96 object-cover rounded-2xl">
                    <div class="absolute bottom-8 left-8 right-8 bg-slate-900/90 backdrop-blur-md p-4 rounded-2xl border border-slate-700/80 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-brand-400 font-semibold uppercase">Featured Release</span>
                            <h4 class="text-white font-bold text-base">iPhone 16 Pro Max</h4>
                        </div>
                        <span class="text-white font-bold text-lg">৳ 165,000</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Categories Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="font-display text-2xl sm:text-3xl font-bold text-slate-900">Featured Categories</h2>
            <p class="text-slate-500 text-sm mt-1">Browse through our top curated collections</p>
        </div>
        <a href="{{ route('shop') }}" class="text-sm font-bold text-brand-500 hover:text-brand-600 flex items-center gap-1">
            View All <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
        @foreach($categories as $category)
            <a href="{{ route('shop', ['category' => $category->slug]) }}" class="group relative rounded-3xl overflow-hidden bg-slate-100 h-48 sm:h-64 shadow-sm hover:shadow-xl transition-all duration-300">
                <img src="{{ $category->image ?? 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=600&q=80' }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent p-6 flex flex-col justify-end">
                    <h3 class="text-white font-display font-bold text-lg sm:text-xl">{{ $category->name }}</h3>
                    <p class="text-slate-300 text-xs mt-1 line-clamp-1">{{ $category->description }}</p>
                </div>
            </a>
        @endforeach
    </div>
</div>

<!-- Featured Products Showcase -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="font-display text-2xl sm:text-3xl font-bold text-slate-900">Trending Flagships</h2>
            <p class="text-slate-500 text-sm mt-1">Handpicked top rated devices and luxury items</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        @foreach($featuredProducts as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</div>
@endsection
