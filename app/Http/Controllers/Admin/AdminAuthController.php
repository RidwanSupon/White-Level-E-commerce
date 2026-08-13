<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            if (!$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Access denied. You do not have administrator permissions.']);
            }

            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Logged into Admin Portal.');
        }

        return back()->withErrors(['email' => 'Invalid administrator credentials.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Logged out from Admin Portal.');
    }
}
