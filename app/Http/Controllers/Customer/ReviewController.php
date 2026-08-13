<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, int $productId)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['required', 'string', 'min:5'],
        ]);

        $product = Product::findOrFail($productId);

        // Check if verified buyer
        $isVerified = Order::where('user_id', auth()->id())
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->where('status', 'delivered')
            ->exists();

        Review::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'],
            'is_verified' => $isVerified,
            'is_approved' => true,
        ]);

        // Recalculate product average rating cache
        $avg = Review::where('product_id', $product->id)->avg('rating') ?? 5;
        $count = Review::where('product_id', $product->id)->count();

        $product->update([
            'rating_cache' => round($avg, 1),
            'reviews_count' => $count,
        ]);

        return back()->with('success', 'Thank you! Your product review has been published.');
    }
}
