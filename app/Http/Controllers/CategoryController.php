<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all categories
        $categories = Category::all();
        
        // Get featured categories
        $featuredCategories = Category::inRandomOrder()->take(6)->get();
        
        // Return the categories index view with data
        return view('categories.index', compact('categories', 'featuredCategories'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find the category
        $category = Category::findOrFail($id);
        
        // Get products in this category via relationship
        $products = $category->products()
                            ->latest()
                            ->paginate(12);
        
        // Get subcategories
        $subcategories = Category::where('parent_id', $id)->get();
        
        // Return the category show view with data
        return view('categories.show', compact('category', 'products', 'subcategories'));
    }

}
