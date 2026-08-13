@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-xl space-y-6">
        <div class="text-center">
            <h1 class="font-display text-2xl font-bold text-slate-900">Sign In to {{ $site_name }}</h1>
            <p class="text-xs text-slate-500 mt-1">Access your saved addresses, cart items, and order history</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" required value="customer@example.com" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required value="password" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                    <input type="checkbox" name="remember" class="rounded text-brand-500">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-full shadow-lg shadow-brand-500/20 transition-all touch-target">
                Sign In
            </button>
        </form>

        <div class="text-center pt-4 border-t border-slate-100 text-xs text-slate-500">
            Don't have an account? <a href="{{ route('register') }}" class="text-brand-500 font-bold hover:underline">Create Account</a>
        </div>
    </div>
</div>
@endsection
