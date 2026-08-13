@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-xl space-y-6">
        <div class="text-center">
            <h1 class="font-display text-2xl font-bold text-slate-900">Create Account</h1>
            <p class="text-xs text-slate-500 mt-1">Join {{ $site_name }} for exclusive offers and faster checkout</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name</label>
                <input type="text" name="name" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-brand-500">
            </div>

            <button type="submit" class="w-full py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-full shadow-lg shadow-brand-500/20 transition-all touch-target">
                Register Account
            </button>
        </form>
    </div>
</div>
@endsection
