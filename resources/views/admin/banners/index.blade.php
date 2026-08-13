@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 h-fit space-y-4">
        <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Create Hero Promo Banner</h3>
        <form action="{{ route('admin.banners.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Headline Title</label>
                <input type="text" name="title" required placeholder="Flash Sale 50% Off" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-white outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Subtitle</label>
                <input type="text" name="subtitle" placeholder="Limited time deal on flagship smartphones" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Image URL</label>
                <input type="url" name="image" required placeholder="https://images.unsplash.com/..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Target Link URL</label>
                <input type="url" name="link" placeholder="https://..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white outline-none focus:border-brand-500">
            </div>
            <button type="submit" class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md">Publish Banner</button>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <div class="font-bold text-white text-base">Active Store Banners</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($banners as $banner)
                <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden relative group">
                    <img src="{{ $banner->image }}" class="w-full h-40 object-cover opacity-60">
                    <div class="p-4 absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent flex flex-col justify-end">
                        <h4 class="font-bold text-white text-base leading-snug">{{ $banner->title }}</h4>
                        <p class="text-xs text-slate-300 line-clamp-1 mb-2">{{ $banner->subtitle }}</p>
                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Delete banner?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1 bg-rose-500/20 text-rose-400 rounded-lg text-xs font-bold hover:bg-rose-500/30">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
