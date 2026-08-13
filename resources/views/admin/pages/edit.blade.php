@extends('layouts.admin')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Edit CMS Page</h1>
            <p class="text-xs text-slate-400 mt-0.5">Update page content, publishing status, and storefront visibility</p>
        </div>
        <a href="{{ route('admin.pages.index') }}" class="text-xs font-bold text-slate-400 hover:text-white">← Back to Pages</a>
    </div>

    <form action="{{ route('admin.pages.update', $page->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3 flex items-center justify-between">
                <span>Page Details</span>
                <span class="text-xs font-mono font-bold text-brand-400">/pages/{{ $page->slug }}</span>
            </h3>
            
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Page Title *</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-semibold">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Body Content *</label>
                <textarea name="content" rows="10" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-mono">{{ old('content', $page->content) }}</textarea>
            </div>
        </div>

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Publishing & Navigation Visibility</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" {{ $page->is_published ? 'checked' : '' }} class="w-4 h-4 accent-emerald-500 rounded">
                    <div>
                        <span class="text-xs font-bold text-emerald-400 block">Publish Page</span>
                        <span class="text-[10px] text-slate-500">Make accessible to customers</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="show_in_nav" value="1" {{ $page->show_in_nav ? 'checked' : '' }} class="w-4 h-4 accent-brand-500 rounded">
                    <div>
                        <span class="text-xs font-bold text-brand-400 block">Show in Navbar</span>
                        <span class="text-[10px] text-slate-500">Add link to top navigation bar</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="show_in_footer" value="1" {{ $page->show_in_footer ? 'checked' : '' }} class="w-4 h-4 accent-slate-400 rounded">
                    <div>
                        <span class="text-xs font-bold text-slate-200 block">Show in Footer</span>
                        <span class="text-[10px] text-slate-500">Add link to website footer</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">SEO Search Metadata</h3>
            
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Meta Description</label>
                <textarea name="meta_description" rows="2" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">{{ old('meta_description', $page->meta_description) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="submit" class="px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/20">
                Update CMS Page
            </button>
            @if($page->is_published)
                <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="px-4 py-2.5 bg-slate-800 text-slate-200 hover:text-white rounded-xl text-xs font-bold">
                    View Live Page ↗
                </a>
            @endif
        </div>
    </form>
</div>
@endsection
