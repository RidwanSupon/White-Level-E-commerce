@extends('layouts.admin')

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">Create CMS Page</h1>
            <p class="text-xs text-slate-400 mt-0.5">Publish custom policy pages, terms of service, or landing content</p>
        </div>
        <a href="{{ route('admin.pages.index') }}" class="text-xs font-bold text-slate-400 hover:text-white">← Back to Pages</a>
    </div>

    <form action="{{ route('admin.pages.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Page Details</h3>
            
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Page Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. About Our Store" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-semibold">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Body Content *</label>
                <textarea name="content" rows="10" required placeholder="Type page content here..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500 font-mono">{{ old('content') }}</textarea>
            </div>
        </div>

        <div class="bg-slate-950 p-6 rounded-3xl border border-slate-800 space-y-4">
            <h3 class="font-bold text-white text-base border-b border-slate-800 pb-3">Publishing & Navigation Visibility</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" checked class="w-4 h-4 accent-emerald-500 rounded">
                    <div>
                        <span class="text-xs font-bold text-emerald-400 block">Publish Page</span>
                        <span class="text-[10px] text-slate-500">Make accessible to customers</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="show_in_nav" value="1" class="w-4 h-4 accent-brand-500 rounded">
                    <div>
                        <span class="text-xs font-bold text-brand-400 block">Show in Navbar</span>
                        <span class="text-[10px] text-slate-500">Add link to top navigation bar</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="show_in_footer" value="1" checked class="w-4 h-4 accent-slate-400 rounded">
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
                <input type="text" name="meta_title" value="{{ old('meta_title') }}" placeholder="About Our Store - LuxeCart" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Meta Description</label>
                <textarea name="meta_description" rows="2" placeholder="Brief summary for Google search results and OpenGraph cards..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-brand-500">{{ old('meta_description') }}</textarea>
            </div>
        </div>

        <button type="submit" class="px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/20">
            Save & Publish CMS Page
        </button>
    </form>
</div>
@endsection
