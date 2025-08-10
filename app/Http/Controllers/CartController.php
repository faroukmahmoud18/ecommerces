<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Vendor;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get cart items from session
        $cartItems = session()->get('cart', []);
        
        // Calculate totals
        $subtotal = 0;
        $shipping = 20; // Fixed shipping cost
        $discount = 0; // No discount by default
        $total = 0;
        
        // Calculate subtotal
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        // Calculate total
        $total = $subtotal + $shipping - $discount;
        
        // Get vendors for shipping calculation
        $vendors = [];
        foreach ($cartItems as $item) {
            $vendors[$item['vendor_id']] = Vendor::find($item['vendor_id']);
        }
        
        // Return the cart index view with data
        return view('cart.index', compact('cartItems', 'subtotal', 'shipping', 'discount', 'total', 'vendors'));
    }
    
    /**
     * Add a product to the cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        // Get product
        $product = Product::findOrFail($request->input('product_id'));
        
        // Get cart items
        $cartItems = session()->get('cart', []);
        
        // Check if product is already in cart
        if (isset($cartItems[$product->id])) {
            // Increment quantity
            $cartItems[$product->id]['quantity'] += $request->input('quantity', 1);
        } else {
            // Add new item to cart
            $cartItems[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $request->input('quantity', 1),
                'image' => $product->featured_image,
                'vendor_id' => $product->vendor_id,
            ];
        }
        
        // Save cart
        session()->put('cart', $cartItems);
        
        // Redirect back with success message
        return redirect()->back()->with('success', 'تمت إضافة المنتج إلى السلة بنجاح');
    }
    
    /**
     * Update cart item quantity.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Get cart items
        $cartItems = session()->get('cart', []);
        
        // Update quantity if item exists
        if (isset($cartItems[$request->input('product_id')])) {
            $cartItems[$request->input('product_id')]['quantity'] = $request->input('quantity');
            
            // Save cart
            session()->put('cart', $cartItems);
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الكمية بنجاح'
            ]);
        }
        
        // Return error response
        return response()->json([
            'success' => false,
            'message' => 'المنتج غير موجود في السلة'
        ], 404);
    }
    
    /**
     * Remove an item from the cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request)
    {
        // Get cart items
        $cartItems = session()->get('cart', []);
        
        // Remove item if exists
        if (isset($cartItems[$request->input('product_id')])) {
            unset($cartItems[$request->input('product_id')]);
            
            // Save cart
            session()->put('cart', $cartItems);
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'تمت إزالة المنتج من السلة بنجاح'
            ]);
        }
        
        // Return error response
        return response()->json([
            'success' => false,
            'message' => 'المنتج غير موجود في السلة'
        ], 404);
    }
}
