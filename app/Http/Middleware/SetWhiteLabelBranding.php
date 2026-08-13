<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetWhiteLabelBranding
{
    public function handle(Request $request, Closure $next): Response
    {
        // Bind white label settings to view global namespace
        view()->share('site_name', setting('site_name', 'LuxeCart'));
        view()->share('site_tagline', setting('site_tagline', 'Premium E-Commerce Platform'));
        view()->share('site_logo', setting('site_logo', '/images/logo.png'));
        view()->share('site_favicon', setting('site_favicon', '/favicon.ico'));
        view()->share('currency_symbol', setting('currency_symbol', '৳'));
        view()->share('white_label_css', white_label_css());

        return $next($request);
    }
}
