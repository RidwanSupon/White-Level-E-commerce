<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class AdminBannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'image' => ['required', 'url'],
            'link' => ['nullable', 'url'],
        ]);

        $validated['is_active'] = true;
        $validated['sort_order'] = 0;

        Banner::create($validated);

        return back()->with('success', 'Promo banner published successfully!');
    }

    public function destroy(int $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();
        return back()->with('success', 'Banner deleted successfully.');
    }
}
