@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-display text-3xl font-bold text-slate-900">Store Catalog</h1>
            <p class="text-slate-500 text-sm mt-1">Showing {{ $products->total() }} premium products</p>
        </div>

        <!-- Sorting Dropdown -->
        <form method="GET" action="{{ route('shop') }}" class="flex items-center gap-2">
            @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
            @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
            <label for="sort" class="text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:inline">Sort by:</label>
            <select name="sort" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-xl px-3.5 py-2 outline-none focus:border-brand-500 shadow-sm">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Arrivals</option>
                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Filter Sidebar -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm h-fit">
            <h3 class="font-bold text-slate-900 text-base mb-4 pb-3 border-b border-slate-100">Categories</h3>
            <ul class="space-y-2 mb-6 text-sm">
                <li>
                    <a href="{{ route('shop') }}" class="flex items-center justify-between py-1.5 px-3 rounded-xl transition-colors {{ !request('category') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span>All Categories</span>
                    </a>
                </li>
                @foreach($categories as $cat)
                    <li>
                        <a href="{{ route('shop', array_merge(request()->query(), ['category' => $cat->slug])) }}" class="flex items-center justify-between py-1.5 px-3 rounded-xl transition-colors {{ request('category') == $cat->slug ? 'bg-brand-50 text-brand-600 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span>{{ $cat->name }}</span>
                            <span class="text-xs bg-slate-100 px-2 py-0.5 rounded-full text-slate-500">{{ $cat->products_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Product Grid -->
        <div class="lg:col-span-3">
            @if($products->isEmpty())
                <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
                    <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <h3 class="text-lg font-bold text-slate-900 mb-1">No products found</h3>
                    <p class="text-slate-500 text-sm mb-6">Try clearing your filters or searching for something else.</p>
                    <a href="{{ route('shop') }}" class="px-6 py-2.5 bg-brand-500 text-white rounded-full text-sm font-semibold shadow-md">Clear Filters</a>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-6">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
