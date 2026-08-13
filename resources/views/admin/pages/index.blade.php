@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white font-display">CMS Pages & Custom Content</h1>
            <p class="text-xs text-slate-400 mt-0.5">Manage policy pages, terms of service, SEO meta tags, and storefront placement</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-500/20">
            + Create New Page
        </a>
    </div>

    <div class="bg-slate-950 rounded-3xl border border-slate-800 overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-900/50">
                    <th class="py-3.5 px-4">Page Title</th>
                    <th class="py-3.5 px-4">URL Slug</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4">Visibility</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($pages as $page)
                    <tr class="hover:bg-slate-900/40">
                        <td class="py-3.5 px-4 font-bold text-white">{{ $page->title }}</td>
                        <td class="py-3.5 px-4 text-brand-400 font-mono">/pages/{{ $page->slug }}</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full font-bold text-[10px] {{ $page->is_published ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400' }}">
                                {{ $page->is_published ? '✓ Published' : 'Draft' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 space-x-1">
                            @if($page->show_in_nav)
                                <span class="px-2 py-0.5 bg-brand-500/10 border border-brand-500/20 text-brand-400 text-[10px] font-bold rounded-lg">Navbar</span>
                            @endif
                            @if($page->show_in_footer)
                                <span class="px-2 py-0.5 bg-slate-800 border border-slate-700 text-slate-300 text-[10px] font-bold rounded-lg">Footer</span>
                            @endif
                            @if(!$page->show_in_nav && !$page->show_in_footer)
                                <span class="text-slate-500 italic text-[11px]">Direct URL only</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2">
                            @if($page->is_published)
                                <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="px-3 py-1.5 bg-slate-800 text-slate-300 rounded-lg text-xs font-semibold hover:text-white">View ↗</a>
                            @else
                                <span class="px-3 py-1.5 bg-slate-900 text-slate-600 rounded-lg text-xs font-semibold cursor-not-allowed" title="Draft page not visible publicly">Draft</span>
                            @endif
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="px-3 py-1.5 bg-slate-800 text-slate-200 hover:text-white rounded-lg text-xs font-semibold">Edit</a>
                            <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete page?')">
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
</div>
@endsection
