<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Services\TaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTaxController extends Controller
{
    protected TaxService $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    public function index()
    {
        $taxRates = TaxRate::latest()->get();
        $isTaxEnabled = $this->taxService->isTaxEnabled();
        $taxAppliesToDelivery = $this->taxService->taxAppliesToDelivery();
        $defaultTaxRate = $this->taxService->getDefaultTaxRate();

        return view('admin.taxes.index', compact('taxRates', 'isTaxEnabled', 'taxAppliesToDelivery', 'defaultTaxRate'));
    }

    public function storeSettings(Request $request)
    {
        $taxEnabled = $request->has('tax_system_enabled') ? '1' : '0';
        $taxAppliesToDelivery = $request->has('tax_applies_to_delivery') ? '1' : '0';

        Setting::updateOrCreate(
            ['key' => 'tax_system_enabled'],
            ['value' => $taxEnabled, 'group' => 'tax', 'type' => 'boolean']
        );

        Setting::updateOrCreate(
            ['key' => 'tax_applies_to_delivery'],
            ['value' => $taxAppliesToDelivery, 'group' => 'tax', 'type' => 'boolean']
        );

        if ($request->has('default_tax_id') && !empty($request->input('default_tax_id'))) {
            $taxRate = TaxRate::find($request->input('default_tax_id'));
            if ($taxRate) {
                TaxRate::query()->update(['is_default' => false]);
                $taxRate->update(['is_default' => true, 'is_active' => true]);
            }
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'tax.settings_updated',
            'module' => 'finance',
            'new_values' => [
                'tax_system_enabled' => $taxEnabled,
                'tax_applies_to_delivery' => $taxAppliesToDelivery,
                'default_tax_id' => $request->input('default_tax_id'),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $statusStr = $taxEnabled === '1' ? 'Enabled' : 'Disabled';
        return back()->with('success', "Tax System configuration updated. Tax System is now {$statusStr}!");
    }

    public function create()
    {
        return view('admin.taxes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:tax_rates,code'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::slug($validated['name'])) . '-' . rand(10, 99);
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_default'] = $request->has('is_default');

        if ($validated['is_default']) {
            TaxRate::query()->update(['is_default' => false]);
            $validated['is_active'] = true;
        }

        $taxRate = TaxRate::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'tax.rate_created',
            'module' => 'finance',
            'record_id' => $taxRate->id,
            'new_values' => $taxRate->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.taxes.index')->with('success', "Tax Rate '{$taxRate->name}' ({$taxRate->rate}%) created successfully!");
    }

    public function edit(int $id)
    {
        $taxRate = TaxRate::findOrFail($id);
        return view('admin.taxes.edit', compact('taxRate'));
    }

    public function update(Request $request, int $id)
    {
        $taxRate = TaxRate::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:tax_rates,code,' . $taxRate->id],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_default'] = $request->has('is_default');

        if ($validated['is_default']) {
            TaxRate::where('id', '!=', $taxRate->id)->update(['is_default' => false]);
            $validated['is_active'] = true;
        }

        $taxRate->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'tax.rate_updated',
            'module' => 'finance',
            'record_id' => $taxRate->id,
            'new_values' => $taxRate->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.taxes.index')->with('success', "Tax Rate '{$taxRate->name}' updated successfully!");
    }

    public function toggleStatus(int $id)
    {
        $taxRate = TaxRate::findOrFail($id);
        $newStatus = !$taxRate->is_active;

        if (!$newStatus && $taxRate->is_default) {
            $taxRate->is_default = false;
        }

        $taxRate->is_active = $newStatus;
        $taxRate->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'tax.status_toggled',
            'module' => 'finance',
            'record_id' => $taxRate->id,
            'new_values' => ['is_active' => $newStatus],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $msg = $newStatus ? 'activated' : 'deactivated';
        return back()->with('success', "Tax Rate '{$taxRate->name}' {$msg} successfully!");
    }

    public function destroy(int $id)
    {
        $taxRate = TaxRate::findOrFail($id);
        $name = $taxRate->name;
        $taxRate->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'tax.rate_deleted',
            'module' => 'finance',
            'record_id' => $id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', "Tax Rate '{$name}' deleted successfully!");
    }
}
