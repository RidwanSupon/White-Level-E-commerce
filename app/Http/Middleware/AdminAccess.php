<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized access to Admin portal.'], 403);
            }
            return redirect()->route('admin.login')->with('error', 'Please log in with admin privileges.');
        }

        return $next($request);
    }
}
