<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')->get();
        return view('admin.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'url'],
            'website' => ['nullable', 'url'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        Brand::create($validated);

        return back()->with('success', 'Brand created successfully!');
    }

    public function destroy(int $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();
        return back()->with('success', 'Brand deleted successfully.');
    }
}
