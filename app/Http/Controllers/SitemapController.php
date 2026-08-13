<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;

class SitemapController extends Controller
{
    public function sitemap()
    {
        $products = Product::where('is_active', true)->select('slug', 'updated_at')->get();
        $categories = Category::select('slug', 'updated_at')->get();
        $pages = Page::where('is_active', true)->select('slug', 'updated_at')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Home
        $xml .= '<url><loc>' . route('home') . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>';

        // Products
        foreach ($products as $prod) {
            $xml .= '<url><loc>' . route('product.show', $prod->slug) . '</loc><lastmod>' . $prod->updated_at->toAtomString() . '</lastmod><priority>0.8</priority></url>';
        }

        // Categories
        foreach ($categories as $cat) {
            $xml .= '<url><loc>' . route('shop', ['category' => $cat->slug]) . '</loc><priority>0.7</priority></url>';
        }

        // Pages
        foreach ($pages as $pg) {
            $xml .= '<url><loc>' . route('page.show', $pg->slug) . '</loc><priority>0.5</priority></url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'text/xml']);
    }

    public function robots()
    {
        $content = "User-agent: *\nDisallow: /admin/\nDisallow: /checkout\nDisallow: /cart\n\nSitemap: " . route('sitemap');
        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
