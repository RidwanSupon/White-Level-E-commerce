<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        DB::table('newsletter_subscribers')->updateOrInsert(
            ['email' => $validated['email']],
            ['created_at' => now(), 'updated_at' => now()]
        );

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Thank you for subscribing to our newsletter!']);
        }

        return back()->with('success', 'Thank you for subscribing to our newsletter!');
    }
}
