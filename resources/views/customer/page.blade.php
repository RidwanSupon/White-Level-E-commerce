@extends('layouts.app')

@section('meta_title', $page->meta_title ?: $page->title . ' - ' . config('app.name', 'LuxeCart'))
@section('meta_description', $page->meta_description ?: Str::limit(strip_tags($page->content), 160))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="border-b border-slate-200 pb-6 space-y-2">
        <div class="flex items-center gap-2 text-xs font-semibold text-brand-600 uppercase tracking-wider">
            <a href="{{ route('home') }}" class="hover:underline">Home</a>
            <span>/</span>
            <span>Pages</span>
        </div>
        <h1 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 leading-tight">{{ $page->title }}</h1>
        <p class="text-xs font-medium text-slate-500">Published on {{ $page->updated_at->format('F d, Y') }}</p>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm text-slate-800 leading-relaxed text-sm sm:text-base space-y-4">
        {!! nl2br(e($page->content)) !!}
    </div>
</div>
@endsection
