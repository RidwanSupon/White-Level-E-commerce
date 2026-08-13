@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Product Attribute Options</h1>
            <p class="text-xs text-slate-400 mt-0.5">Manage global reusable attributes (Size, Color, Fabric, Fit, Sleeve, Length) for product variants</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 h-fit space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Create New Attribute</h3>
            <form action="{{ route('admin.attributes.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Attribute Name</label>
                    <input type="text" name="name" required placeholder="e.g. Neckline, Material" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Display Type</label>
                    <select name="type" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
                        <option value="select">Dropdown Select</option>
                        <option value="color">Color Swatch</option>
                        <option value="button">Pill Button</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">Add Reusable Attribute</button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-6">
            @foreach($attributes as $attr)
                <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div>
                            <h4 class="font-bold text-white text-base inline-flex items-center gap-2">
                                {{ $attr->name }} 
                                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-mono text-[10px] uppercase font-bold">{{ $attr->type }}</span>
                            </h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                @if(in_array($attr->name, ['Size', 'Color', 'Fabric', 'Fit']))
                                    Reusable across T-Shirt, Shirt, Pant, Ladies Dress
                                @elseif($attr->name === 'Sleeve')
                                    Reusable across Shirt, Ladies Dress, T-Shirt
                                @elseif($attr->name === 'Length')
                                    Reusable across Pant, Ladies Dress
                                @else
                                    Global reusable attribute
                                @endif
                            </p>
                        </div>
                        
                        <form action="{{ route('admin.attributes.destroy', $attr->id) }}" method="POST" onsubmit="return confirm('Delete attribute {{ $attr->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-bold px-2 py-1 bg-rose-500/10 hover:bg-rose-500/20 rounded-lg">Delete</button>
                        </form>
                    </div>

                    <!-- Values List -->
                    <div class="flex flex-wrap gap-2">
                        @foreach($attr->values as $val)
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-900 border border-slate-800 text-slate-200 text-xs rounded-xl font-medium group">
                                @if($val->color_code)
                                    <span class="w-3.5 h-3.5 rounded-full border border-slate-700 shadow-sm shrink-0" style="background-color: {{ $val->color_code }}"></span>
                                @endif
                                <span>{{ $val->value }}</span>
                                <form action="{{ route('admin.attributes.values.destroy', $val->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-500 hover:text-rose-400 font-extrabold ml-1 text-xs" title="Remove value">✕</button>
                                </form>
                            </span>
                        @endforeach
                    </div>

                    <!-- Add Value Form -->
                    <form action="{{ route('admin.attributes.values.store', $attr->id) }}" method="POST" class="flex gap-3 pt-3 border-t border-slate-800/80">
                        @csrf
                        <input type="text" name="value" required placeholder="Add option (e.g. {{ $attr->name === 'Size' ? 'XL' : ($attr->name === 'Color' ? 'Navy Blue' : 'Cotton') }})" class="flex-1 bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:border-brand-500">
                        @if($attr->type === 'color')
                            <input type="text" name="color_code" placeholder="#000000" class="w-24 bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:border-brand-500 font-mono">
                        @endif
                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition-all">+ Add Option</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
