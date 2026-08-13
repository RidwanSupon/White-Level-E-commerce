<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class AdminShippingZoneController extends Controller
{
    public function index()
    {
        $zones = ShippingZone::with('shippingMethods')->latest()->get();
        return view('admin.shipping_zones.index', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'zone_type' => ['required', 'in:dhaka,outside_dhaka'],
            'delivery_charge' => ['required', 'numeric', 'min:0'],
            'advance_payment_required' => ['nullable', 'boolean'],
            'districts' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $districtsArray = array_filter(array_map('trim', explode(',', $request->input('districts', ''))));

        ShippingZone::create([
            'name' => $validated['name'],
            'zone_type' => $validated['zone_type'],
            'districts_json' => $districtsArray,
            'regions_json' => $districtsArray,
            'delivery_charge' => $validated['delivery_charge'],
            'advance_payment_required' => $request->has('advance_payment_required'),
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Shipping Zone created successfully!');
    }

    public function update(Request $request, $id)
    {
        $zone = ShippingZone::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'zone_type' => ['required', 'in:dhaka,outside_dhaka'],
            'delivery_charge' => ['required', 'numeric', 'min:0'],
            'districts' => ['nullable', 'string'],
        ]);

        $districtsArray = array_filter(array_map('trim', explode(',', $request->input('districts', ''))));

        $zone->update([
            'name' => $validated['name'],
            'zone_type' => $validated['zone_type'],
            'districts_json' => $districtsArray,
            'regions_json' => $districtsArray,
            'delivery_charge' => $validated['delivery_charge'],
            'advance_payment_required' => $request->has('advance_payment_required'),
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Shipping Zone updated successfully!');
    }

    public function toggleStatus($id)
    {
        $zone = ShippingZone::findOrFail($id);
        $zone->update(['is_active' => !$zone->is_active]);

        return back()->with('success', 'Shipping Zone status updated!');
    }

    public function destroy($id)
    {
        $zone = ShippingZone::findOrFail($id);
        $zone->delete();

        return back()->with('success', 'Shipping Zone deleted successfully.');
    }

    public function storeZone(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'regions_json' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = true;
        ShippingZone::create($validated);

        return back()->with('success', "Shipping zone '{$validated['name']}' created!");
    }

    public function storeMethod(Request $request)
    {
        $validated = $request->validate([
            'shipping_zone_id' => ['required', 'exists:shipping_zones,id'],
            'name' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'estimated_days' => ['nullable', 'string', 'max:50'],
        ]);

        $validated['charge'] = $validated['cost'];
        $validated['is_active'] = true;

        \App\Models\ShippingMethod::create($validated);

        return back()->with('success', "Shipping method '{$validated['name']}' created!");
    }
}
