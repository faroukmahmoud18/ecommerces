
<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorDashboardController extends Controller
{
    /**
     * Display vendor dashboard.
     */
    public function index()
    {
        $vendor = Auth::user()->vendor;

        // Get statistics
        $totalProducts = $vendor->products()->count();
        $totalOrders = $vendor->orders()->count();
        $totalRevenue = $vendor->orders()->sum('total_amount');
        $pendingOrders = $vendor->orders()->where('status', 'pending')->count();
        $processingOrders = $vendor->orders()->where('status', 'processing')->count();
        $shippedOrders = $vendor->orders()->where('status', 'shipped')->count();
        $deliveredOrders = $vendor->orders()->where('status', 'delivered')->count();
        $totalReviews = $vendor->products()->withCount('reviews')->get()->sum('reviews_count');
        $averageRating = $vendor->products()->withAvg('reviews', 'rating')->get()->avg('reviews_avg_rating');

        // Get recent orders
        $recentOrders = $vendor->orders()
            ->latest()
            ->take(5)
            ->get();

        // Get low stock products
        $lowStockProducts = Product::where('vendor_id', $vendor->id)
            ->where('manage_inventory', true)
            ->where('quantity', '<=', config('app.low_stock_threshold', 10))
            ->latest()
            ->take(5)
            ->get();

        // Get recent reviews
        $recentReviews = Review::whereHas('product', function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->latest()
            ->take(5)
            ->get();

        // Get revenue chart data
        $revenueData = $this->getRevenueChartData($vendor);

        return view('vendor.dashboard.index', compact(
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'pendingOrders',
            'processingOrders',
            'shippedOrders',
            'deliveredOrders',
            'totalReviews',
            'averageRating',
            'recentOrders',
            'lowStockProducts',
            'recentReviews',
            'revenueData'
        ));
    }

    /**
     * Get revenue chart data for the last 30 days.
     */
    private function getRevenueChartData($vendor)
    {
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $total = $vendor->orders()
                ->whereDate('created_at', $date)
                ->sum('total_amount');

            $data[] = [
                'date' => $date,
                'total' => $total,
            ];
        }

        return $data;
    }

    /**
     * Display vendor profile.
     */
    public function profile()
    {
        $vendor = Auth::user()->vendor;
        return view('vendor.profile.index', compact('vendor'));
    }

    /**
     * Update vendor profile.
     */
    public function updateProfile(Request $request)
    {
        $vendor = Auth::user()->vendor;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url'],
            'social_links' => ['nullable', 'array'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($vendor->logo) {
                Storage::delete('public/' . $vendor->logo);
            }

            // Store new logo
            $path = $request->file('logo')->store('vendor/logos', 'public');
            $request->merge(['logo' => $path]);
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old cover image if exists
            if ($vendor->cover_image) {
                Storage::delete('public/' . $vendor->cover_image);
            }

            // Store new cover image
            $path = $request->file('cover_image')->store('vendor/cover-images', 'public');
            $request->merge(['cover_image' => $path]);
        }

        $vendor->update($request->all());

        return redirect()->route('vendor.profile')
            ->with('success', 'تم تحديث ملفك الشخصي بنجاح');
    }
}
