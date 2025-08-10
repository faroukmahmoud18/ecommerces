<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Vendor;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get featured categories
        $categories = Category::inRandomOrder()->take(6)->get();
        
        // Get featured offers
        $offers = Offer::inRandomOrder()->take(4)->get();
        
        // Get featured products
        $featuredProducts = Product::inRandomOrder()->take(8)->get();
        
        // Get top vendors
        $topVendors = Vendor::inRandomOrder()->take(4)->get();
        
        // Return the home view with data
        return view('home', compact('categories', 'offers', 'featuredProducts', 'topVendors'));
    }
}
