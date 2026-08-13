<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')->where('is_active', true)->with('children')->orderBy('sort_order')->take(8)->get();
        $featuredProducts = Product::where('is_active', true)->where('is_featured', true)->with(['category', 'brand'])->take(8)->get();
        $newArrivals = Product::where('is_active', true)->with(['category', 'brand'])->latest()->take(8)->get();
        $banners = Banner::where('is_active', true)->orderBy('sort_order')->get();

        return view('customer.home', compact('categories', 'featuredProducts', 'newArrivals', 'banners'));
    }
}
