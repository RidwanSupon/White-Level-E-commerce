@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 h-fit space-y-4">
        <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Create Brand</h3>
        <form action="{{ route('admin.brands.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Brand Name</label>
                <input type="text" name="name" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-white outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Logo URL</label>
                <input type="url" name="logo" placeholder="https://..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-white outline-none focus:border-brand-500">
            </div>
            <button type="submit" class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md">Add Brand</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800 font-bold text-white text-base">Brand Registry</div>
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                    <th class="py-3 px-4">Brand</th>
                    <th class="py-3 px-4">Products Count</th>
                    <th class="py-3 px-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($brands as $brand)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3 px-4 font-bold text-white flex items-center gap-3">
                            @if($brand->logo)
                                <img src="{{ $brand->logo }}" class="w-8 h-8 rounded-lg object-cover">
                            @endif
                            <span>{{ $brand->name }}</span>
                        </td>
                        <td class="py-3 px-4 font-bold text-brand-400">{{ $brand->products_count }} items</td>
                        <td class="py-3 px-4 text-right">
                            <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Delete this brand?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:underline text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
