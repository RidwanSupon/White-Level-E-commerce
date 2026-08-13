@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div>
        <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900">Saved Wishlist</h1>
        <p class="text-xs text-slate-500 mt-1">Keep track of products you love and move them to cart anytime</p>
    </div>

    @if($wishlists->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-sm max-w-md mx-auto space-y-4">
            <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto text-2xl">❤️</div>
            <h3 class="font-bold text-slate-900 text-lg">Your Wishlist is Empty</h3>
            <p class="text-xs text-slate-500">Explore our catalog and click the heart icon on any item to save it for later.</p>
            <a href="{{ route('shop') }}" class="inline-block px-6 py-3 bg-brand-500 text-white font-bold text-xs rounded-full shadow-md">Browse Store</a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
            @foreach($wishlists as $item)
                @if($item->product)
                    <x-product-card :product="$item->product" />
                @endif
            @endforeach
        </div>
    @endif
</div>
@endsection
