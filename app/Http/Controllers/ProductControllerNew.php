<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Review;
use App\Models\Offer;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get search parameters
        $search = $request->input('search', '');
        $category_id = $request->input('category_id', '');
        $vendor_id = $request->input('vendor_id', '');
        $min_price = $request->input('min_price', 0);
        $max_price = $request->input('max_price', 10000);
        $has_offer = $request->input('has_offer', '');

        // Build query
        $query = Product::query();

        // Apply search
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
        }

        // Apply category filter
        if (!empty($category_id)) {
            $query->whereHas('categories', function($q) use ($category_id) {
                $q->where('id', $category_id);
            });
        }

        // Apply vendor filter
        if (!empty($vendor_id)) {
            $query->where('vendor_id', $vendor_id);
        }

        // Apply price filter
        $query->whereBetween('price', [$min_price, $max_price]);

        // Apply offer filter
        if ($has_offer === 'true') {
            $query->whereHas('offers', function($q) {
                $q->where('status', 'active')
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
            });
        }

        // Get products with pagination
        $products = $query->with(['vendor', 'categories', 'activeOffers'])
                         ->latest()
                         ->paginate(12);

        // Get categories and vendors for filters
        $categories = Category::active()->get();
        $vendors = Vendor::active()->get();

        // Get active offers for filter
        $offers = Offer::active()->get();

        // Return the products index view with data
        return view('products.index', compact('products', 'categories', 'vendors', 'offers', 'search', 'category_id', 'vendor_id', 'min_price', 'max_price', 'has_offer'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find the product with related data
        $product = Product::with(['vendor', 'categories', 'images', 'activeOffers', 'reviews'])
                         ->findOrFail($id);

        // Get related products
        $relatedProducts = Product::whereHas('categories', function($q) use ($product) {
                $q->whereIn('id', $product->categories->pluck('id'));
            })
                                 ->where('id', '!=', $product->id)
                                 ->with(['vendor', 'activeOffers'])
                                 ->inRandomOrder()
                                 ->take(4)
                                 ->get();

        // Get reviews
        $reviews = $product->reviews()->latest()->paginate(5);

        // Calculate average rating
        $averageRating = $product->average_rating;

        // Get offers for this product
        $productOffers = $product->active_offers;

        // Return the product show view with data
        return view('products.show', compact('product', 'relatedProducts', 'reviews', 'averageRating', 'productOffers'));
    }
}