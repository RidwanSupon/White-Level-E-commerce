@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Product Catalog</h1>
            <p class="text-xs text-slate-400 mt-0.5">Manage products, inventory counts, pricing, and active status</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
            + Create New Product
        </a>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex flex-col sm:flex-row gap-4 justify-between items-center">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-1 gap-3 w-full sm:w-auto">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or SKU..." class="flex-1 bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white outline-none focus:border-brand-500">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold">Filter</button>
        </form>
    </div>

    <!-- Product Data Table -->
    <div class="bg-slate-950 rounded-3xl border border-slate-800/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                        <th class="py-3.5 px-4">Product</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">SKU</th>
                        <th class="py-3.5 px-4">Price</th>
                        <th class="py-3.5 px-4">Stock</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 text-xs">
                    @foreach($products as $product)
                        <tr class="hover:bg-slate-900/40">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $product->featured_image }}" class="w-10 h-10 rounded-lg object-cover border border-slate-800 bg-slate-900">
                                    <span class="font-bold text-white max-w-xs truncate">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $product->category?->name ?? 'Uncategorized' }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-300">{{ $product->sku }}</td>
                            <td class="py-3.5 px-4 font-bold text-white">{{ format_price($product->price) }}</td>
                            <td class="py-3.5 px-4">
                                @if($product->stock_quantity == 0)
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-rose-500/10 border border-rose-500/20 text-rose-400">
                                        🔴 Out of Stock
                                    </span>
                                @elseif($product->stock_quantity <= ($product->low_stock_threshold ?? 5))
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-amber-500/10 border border-amber-500/20 text-amber-400">
                                        🟡 Low Stock ({{ $product->stock_quantity }} left)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                                        🟢 In Stock ({{ $product->stock_quantity }})
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.products.toggle_status', $product->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Click to toggle status" class="px-3 py-1 rounded-full font-bold text-[10px] transition-all flex items-center gap-1.5 {{ $product->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30 hover:bg-amber-500/20' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                                        {{ $product->is_active ? 'Published' : 'Unpublished' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <form action="{{ route('admin.products.toggle_status', $product->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $product->is_active ? 'bg-amber-500/10 text-amber-400 hover:bg-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' }}">
                                        {{ $product->is_active ? 'Unpublish' : 'Publish' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-semibold">Edit</a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 rounded-lg text-xs font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-800">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
