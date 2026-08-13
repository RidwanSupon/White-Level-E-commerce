@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-display text-3xl font-bold text-slate-900">Customer Portal</h1>
            <p class="text-slate-500 text-sm mt-1">Welcome back, {{ $user->name }} ({{ $user->email }})</p>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-full transition-colors">
                Sign Out
            </button>
        </form>
    </div>

    <!-- Order History -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <h3 class="font-display font-bold text-xl text-slate-900 mb-6">Your Recent Orders</h3>

        @if($orders->isEmpty())
            <div class="text-center py-8 text-slate-500">
                <p class="text-sm">You haven't placed any orders yet.</p>
                <a href="{{ route('shop') }}" class="mt-4 inline-block px-6 py-2.5 bg-brand-500 text-white rounded-full text-xs font-bold shadow-md">Shop Now</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Order #</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Payment</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($orders as $order)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-4 px-4 font-bold text-slate-900">{{ $order->order_number }}</td>
                                <td class="py-4 px-4 text-slate-500 text-xs">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 text-amber-700">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $order->payment_status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-900">{{ format_price($order->grand_total) }}</td>
                                <td class="py-4 px-4 text-right">
                                    <a href="{{ route('customer.orders.show', $order->id) }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
