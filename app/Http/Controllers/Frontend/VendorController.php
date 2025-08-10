<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Order;

class VendorController extends Controller
{
    /**
     * Display a listing of the vendors.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vendors = Vendor::with('user')
            ->latest()
            ->paginate(20);

        return view('vendors.index', compact('vendors'));
    }

    /**
     * Display the specified vendor.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find the vendor
        $vendor = Vendor::findOrFail($id);

        // Load related data
        $vendor->load(['user', 'products', 'orders']);

        // Calculate statistics
        $totalProducts = $vendor->products()->count();
        $totalOrders = $vendor->orders()->count();
        $totalRevenue = $vendor->orders()->sum('total_amount');
        $pendingOrders = $vendor->orders()->where('status', 'pending')->count();
        $processingOrders = $vendor->orders()->where('status', 'processing')->count();
        $completedOrders = $vendor->orders()->where('status', 'delivered')->count();

        // Get products with pagination
        $products = $vendor->products()->latest()->paginate(12);

        return view('vendors.show', compact(
            'vendor',
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'pendingOrders',
            'processingOrders',
            'completedOrders',
            'products'
        ));
    }
}
