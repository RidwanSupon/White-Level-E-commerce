<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminAttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->get();
        return view('admin.attributes.index', compact('attributes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:select,color,button'],
        ]);

        $validated['code'] = Str::slug($validated['name']);
        Attribute::create($validated);

        return back()->with('success', "Attribute '{$validated['name']}' created!");
    }

    public function storeValue(Request $request, int $attributeId)
    {
        $validated = $request->validate([
            'value' => ['required', 'string', 'max:100'],
            'color_code' => ['nullable', 'string', 'max:20'],
        ]);

        $attribute = Attribute::findOrFail($attributeId);
        AttributeValue::create([
            'attribute_id' => $attribute->id,
            'value' => $validated['value'],
            'color_code' => $validated['color_code'] ?? null,
        ]);

        return back()->with('success', "Attribute value '{$validated['value']}' added!");
    }

    public function destroyValue(int $id)
    {
        $val = AttributeValue::findOrFail($id);
        $val->delete();

        return back()->with('success', 'Attribute option value removed.');
    }

    public function destroy(int $id)
    {
        $attr = Attribute::findOrFail($id);
        $attr->delete();

        return back()->with('success', 'Attribute deleted.');
    }
}
