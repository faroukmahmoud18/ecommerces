
<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VendorReportsController extends Controller
{
    /**
     * Display the sales report.
     */
    public function sales(Request $request)
    {
        $vendor = Auth::user()->vendor;
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Get daily sales data
        $dailySales = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(shipping_cost) as shipping_cost'),
                DB::raw('SUM(tax_amount) as tax_amount'),
                DB::raw('SUM(discount_amount) as discount_amount')
            )
            ->where('vendor_id', $vendor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get monthly sales data for chart
        $monthlySales = Order::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('vendor_id', $vendor->id)
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfMonth(),
                Carbon::parse($endDate)->endOfMonth()
            ])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get top selling products
        $topProducts = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name as product_name',
                'products.sku',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_sales')
            )
            ->where('order_items.vendor_id', $vendor->id)
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_sales', 'desc')
            ->take(10)
            ->get();

        // Get sales by status
        $salesByStatus = Order::select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->where('vendor_id', $vendor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get();

        // Get sales by payment method
        $salesByPaymentMethod = Payment::select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->whereHas('order', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->get();

        // Calculate totals
        $totalOrders = Order::where('vendor_id', $vendor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalSales = Order::where('vendor_id', $vendor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        return view('vendor.reports.sales', compact(
            'dailySales',
            'monthlySales',
            'topProducts',
            'salesByStatus',
            'salesByPaymentMethod',
            'totalOrders',
            'totalSales',
            'averageOrderValue',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display the products report.
     */
    public function products(Request $request)
    {
        $vendor = Auth::user()->vendor;

        $products = Product::where('vendor_id', $vendor->id)
            ->withCount(['orders', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(20);

        return view('vendor.reports.products', compact('products'));
    }

    /**
     * Display the customers report.
     */
    public function customers(Request $request)
    {
        $vendor = Auth::user()->vendor;
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Get customers with their order counts and total spending
        $customers = Order::select(
                'customer_email',
                'customer_name',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('MAX(created_at) as last_order_date')
            )
            ->where('vendor_id', $vendor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('customer_email', 'customer_name')
            ->orderBy('total_spent', 'desc')
            ->paginate(20);

        return view('vendor.reports.customers', compact('customers', 'startDate', 'endDate'));
    }

    /**
     * Display the reviews report.
     */
    public function reviews(Request $request)
    {
        $vendor = Auth::user()->vendor;
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Get reviews with product information
        $reviews = Review::whereHas('product', function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->with(['product', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->paginate(20);

        // Get review statistics
        $totalReviews = Review::whereHas('product', function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->count();

        $averageRating = Review::whereHas('product', function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->avg('rating');

        $ratingDistribution = Review::whereHas('product', function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();

        return view('vendor.reviews.index', compact(
            'reviews',
            'totalReviews',
            'averageRating',
            'ratingDistribution',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export sales report to CSV.
     */
    public function exportSales(Request $request)
    {
        $vendor = Auth::user()->vendor;
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $orders = Order::where('vendor_id', $vendor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['items.product'])
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-report-' . $startDate . '-to-' . $endDate . '.csv"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'رقم الطلب',
                'تاريخ الطلب',
                'اسم العميل',
                'البريد الإلكتروني',
                'حالة الطلب',
                'حالة الدفع',
                'إجمالي المبلغ',
                'تكلفة الشحن',
                'الضريبة',
                'الخصم',
                'اسم المنتج',
                'SKU',
                'الكمية',
                'سعر الوحدة',
                'إجمالي المنتج',
            ]);

            // Add order data
            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    fputcsv($file, [
                        $order->order_number,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->customer_name,
                        $order->customer_email,
                        $order->status,
                        $order->payment_status,
                        $order->total_amount,
                        $order->shipping_cost,
                        $order->tax_amount,
                        $order->discount_amount,
                        $item->product_name,
                        $item->product_sku,
                        $item->quantity,
                        $item->price,
                        $item->subtotal,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
