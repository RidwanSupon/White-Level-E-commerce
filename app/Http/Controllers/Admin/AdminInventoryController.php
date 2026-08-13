<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminInventoryController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(15);
        $movements = InventoryMovement::with(['product', 'user'])->latest()->take(10)->get();

        return view('admin.inventory.index', compact('products', 'movements'));
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:IN,OUT,ADJUSTMENT,RETURN,DAMAGE'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $quantity = (int) $validated['quantity'];

        if (in_array($validated['type'], ['IN', 'RETURN'])) {
            $product->increment('stock_quantity', $quantity);
        } else {
            $product->decrement('stock_quantity', $quantity);
        }

        app(\App\Services\StockAlertService::class)->checkStockAndNotify($product->fresh());

        InventoryMovement::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'quantity' => $quantity,
            'reference' => $validated['reference'] ?? 'MANUAL-' . strtoupper(uniqid()),
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'inventory.adjusted',
            'module' => 'inventory',
            'record_id' => $product->id,
            'new_values' => ['type' => $validated['type'], 'quantity' => $quantity],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Stock adjustment for {$product->name} recorded successfully!");
    }
}
