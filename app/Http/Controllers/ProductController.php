<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Review;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get search parameters
        $search      = $request->input('search', '');
        $category_id = $request->input('category_id', '');
        $vendor_id   = $request->input('vendor_id', '');
        $min_price   = $request->input('min_price', 0);
        $max_price   = $request->input('max_price', 10000);
        
        // Build query
        $query = Product::query();

        // Apply search
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Apply category filter (many-to-many)
        if (!empty($category_id)) {
            $query->whereHas('categories', function($q) use ($category_id) {
                $q->where('categories.id', $category_id);
            });
        }
        
        // Apply vendor filter
        if (!empty($vendor_id)) {
            $query->where('vendor_id', $vendor_id);
        }
        
        // Apply price filter
        $query->whereBetween('price', [$min_price, $max_price]);
        
        // Get products with pagination
        $products = $query->latest()->paginate(12);
        
        // Get categories and vendors for filters
        $categories = Category::all();
        $vendors    = Vendor::all();
        
        // Return the products index view with data
        return view('products.index', compact(
            'products', 'categories', 'vendors', 
            'search', 'category_id', 'vendor_id', 
            'min_price', 'max_price'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Find the product
        $product = Product::findOrFail($id);
        
        // Get the first category of the product (or null if none)
        $category = $product->categories()->first();

        // Get related products (same category, excluding current product)
        $relatedProducts = collect(); // default empty
        if ($category) {
            $relatedProducts = Product::whereHas('categories', function($q) use ($category) {
                    $q->where('categories.id', $category->id);
                })
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->take(4)
                ->get();
        }
        
        // Get reviews
        $reviews = Review::where('product_id', $id)
                         ->latest()
                         ->paginate(5);
        
        // Calculate average rating
        $averageRating = Review::where('product_id', $id)->avg('rating') ?? 0;
        
        // Return the product show view with data
        return view('products.show', compact(
            'product', 'relatedProducts', 'reviews', 'averageRating'
        ));
    }
}
