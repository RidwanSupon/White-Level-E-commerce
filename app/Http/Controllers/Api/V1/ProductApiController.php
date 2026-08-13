<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('is_active', true)
            ->with(['category', 'brand'])
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'brand', 'images', 'variants'])
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $product,
        ]);
    }
}
