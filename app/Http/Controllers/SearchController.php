<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get search query
        $query = $request->input('q', '');
        
        // Initialize empty collections
        $products = collect();
        $categories = collect();
        $vendors = collect();
        
        // If search query is not empty, perform search
        if (!empty($query)) {
            // Search products
            $products = Product::where('name', 'like', '%' . $query . '%')
                              ->orWhere('description', 'like', '%' . $query . '%')
                              ->latest()
                              ->paginate(12);
            
            // Search categories
            $categories = Category::where('name', 'like', '%' . $query . '%')
                                  ->latest()
                                  ->get();
            
            // Search vendors
            $vendors = Vendor::where('name', 'like', '%' . $query . '%')
                             ->latest()
                             ->get();
        }
        
        // Return the search index view with data
        return view('search.index', compact('query', 'products', 'categories', 'vendors'));
    }
}
