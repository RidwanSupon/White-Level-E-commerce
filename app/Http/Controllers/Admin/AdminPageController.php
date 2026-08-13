<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPageController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'show_in_nav' => ['nullable', 'boolean'],
            'show_in_footer' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_published'] = $request->has('is_published');
        $validated['show_in_nav'] = $request->has('show_in_nav');
        $validated['show_in_footer'] = $request->has('show_in_footer');

        $page = Page::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'cms_page.created',
            'module' => 'cms',
            'record_id' => $page->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $statusStr = $page->is_published ? 'published' : 'saved as draft';
        return redirect()->route('admin.pages.index')->with('success', "Page '{$page->title}' {$statusStr}!");
    }

    public function edit(int $id)
    {
        $page = Page::findOrFail($id);
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, int $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_published'] = $request->has('is_published');
        $validated['show_in_nav'] = $request->has('show_in_nav');
        $validated['show_in_footer'] = $request->has('show_in_footer');

        $page->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'cms_page.updated',
            'module' => 'cms',
            'record_id' => $page->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.pages.index')->with('success', "Page '{$page->title}' updated successfully!");
    }

    public function destroy(int $id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return back()->with('success', 'Page deleted successfully.');
    }
}
