
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReportService
{
    /**
     * Generate sales report.
     *
     * @param array $filters
     * @return array
     */
    public function generateSalesReport($filters = [])
    {
        try {
            $query = Order::query();

            // Apply date filters
            if (!empty($filters['start_date'])) {
                $query->whereDate('created_at', '>=', $filters['start_date']);
            }

            if (!empty($filters['end_date'])) {
                $query->whereDate('created_at', '<=', $filters['end_date']);
            }

            // Apply status filter
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // Apply payment status filter
            if (!empty($filters['payment_status'])) {
                $query->where('payment_status', $filters['payment_status']);
            }

            // Apply vendor filter
            if (!empty($filters['vendor_id'])) {
                $query->where('vendor_id', $filters['vendor_id']);
            }

            // Get report data
            $reportData = [
                'total_orders' => $query->count(),
                'total_revenue' => $query->sum('total_amount'),
                'total_tax' => $query->sum('tax_amount'),
                'total_shipping' => $query->sum('shipping_cost'),
                'total_discount' => $query->sum('discount_amount'),
                'average_order_value' => $query->count() > 0 ? $query->sum('total_amount') / $query->count() : 0,
            ];

            // Get daily sales data
            $dailySalesData = $this->getDailySalesData($filters);

            // Get monthly sales data
            $monthlySalesData = $this->getMonthlySalesData($filters);

            // Get sales by status
            $salesByStatus = $query->groupBy('status')
                ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total_amount'))
                ->get();

            // Get sales by payment method
            $salesByPaymentMethod = $query->groupBy('payment_method')
                ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total_amount'))
                ->get();

            // Get top selling products
            $topProducts = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [
                    $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                    $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                ])
                ->groupBy('products.id', 'products.name')
                ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_quantity'), DB::raw('SUM(order_items.subtotal) as total_sales'))
                ->orderBy('total_sales', 'desc')
                ->take(10)
                ->get();

            // Get top vendors by sales
            $topVendors = $query->groupBy('vendor_id')
                ->join('vendors', 'orders.vendor_id', '=', 'vendors.id')
                ->select('vendors.id', 'vendors.name', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total_amount) as total_sales'))
                ->orderBy('total_sales', 'desc')
                ->take(10)
                ->get();

            return [
                'success' => true,
                'data' => $reportData,
                'daily_sales' => $dailySalesData,
                'monthly_sales' => $monthlySalesData,
                'sales_by_status' => $salesByStatus,
                'sales_by_payment_method' => $salesByPaymentMethod,
                'top_products' => $topProducts,
                'top_vendors' => $topVendors,
            ];
        } catch (\Exception $e) {
            Log::error('Error generating sales report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء تقرير المبيعات',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get daily sales data.
     *
     * @param array $filters
     * @return array
     */
    private function getDailySalesData($filters = [])
    {
        $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date']) : Carbon::now()->subDays(30);
        $endDate = !empty($filters['end_date']) ? Carbon::parse($filters['end_date']) : Carbon::now();

        $data = [];

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dayData = Order::whereDate('created_at', $date->format('Y-m-d'))
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as orders_count'),
                    DB::raw('SUM(total_amount) as total_sales'),
                    DB::raw('SUM(subtotal) as subtotal'),
                    DB::raw('SUM(shipping_cost) as shipping_cost'),
                    DB::raw('SUM(tax_amount) as tax_amount'),
                    DB::raw('SUM(discount_amount) as discount_amount')
                )
                ->first();

            $data[] = [
                'date' => $dayData->date ?? $date->format('Y-m-d'),
                'orders_count' => $dayData->orders_count ?? 0,
                'total_sales' => $dayData->total_sales ?? 0,
                'subtotal' => $dayData->subtotal ?? 0,
                'shipping_cost' => $dayData->shipping_cost ?? 0,
                'tax_amount' => $dayData->tax_amount ?? 0,
                'discount_amount' => $dayData->discount_amount ?? 0,
            ];
        }

        return $data;
    }

    /**
     * Get monthly sales data.
     *
     * @param array $filters
     * @return array
     */
    private function getMonthlySalesData($filters = [])
    {
        $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date']) : Carbon::now()->subMonths(12);
        $endDate = !empty($filters['end_date']) ? Carbon::parse($filters['end_date']) : Carbon::now();

        $data = [];

        for ($date = $startDate; $date->lte($endDate); $date->addMonth()) {
            $monthData = Order::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as orders_count'),
                    DB::raw('SUM(total_amount) as total_sales'),
                    DB::raw('SUM(subtotal) as subtotal'),
                    DB::raw('SUM(shipping_cost) as shipping_cost'),
                    DB::raw('SUM(tax_amount) as tax_amount'),
                    DB::raw('SUM(discount_amount) as discount_amount')
                )
                ->first();

            $data[] = [
                'month' => $monthData->month ?? $date->format('Y-m'),
                'orders_count' => $monthData->orders_count ?? 0,
                'total_sales' => $monthData->total_sales ?? 0,
                'subtotal' => $monthData->subtotal ?? 0,
                'shipping_cost' => $monthData->shipping_cost ?? 0,
                'tax_amount' => $monthData->tax_amount ?? 0,
                'discount_amount' => $monthData->discount_amount ?? 0,
            ];
        }

        return $data;
    }

    /**
     * Generate product performance report.
     *
     * @param array $filters
     * @return array
     */
    public function generateProductReport($filters = [])
    {
        try {
            $query = Product::query();

            // Apply date filters
            if (!empty($filters['start_date'])) {
                $query->whereHas('orders', function($q) use ($filters) {
                    $q->whereDate('created_at', '>=', $filters['start_date']);
                });
            }

            if (!empty($filters['end_date'])) {
                $query->whereHas('orders', function($q) use ($filters) {
                    $q->whereDate('created_at', '<=', $filters['end_date']);
                });
            }

            // Apply vendor filter
            if (!empty($filters['vendor_id'])) {
                $query->where('vendor_id', $filters['vendor_id']);
            }

            // Apply category filter
            if (!empty($filters['category_id'])) {
                $query->whereHas('categories', function($q) use ($filters) {
                    $q->where('categories.id', $filters['category_id']);
                });
            }

            // Get report data
            $reportData = [
                'total_products' => $query->count(),
                'total_views' => $query->sum('views'),
                'total_sold' => DB::table('order_items')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereBetween('orders.created_at', [
                        $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                        $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                    ])
                    ->sum('order_items.quantity'),
                'total_revenue' => DB::table('order_items')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereBetween('orders.created_at', [
                        $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                        $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                    ])
                    ->sum('order_items.subtotal'),
            ];

            // Get top selling products
            $topSellingProducts = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [
                    $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                    $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                ])
                ->groupBy('products.id', 'products.name', 'products.price')
                ->select('products.id', 'products.name', 'products.price', DB::raw('SUM(order_items.quantity) as total_quantity'), DB::raw('SUM(order_items.subtotal) as total_sales'))
                ->orderBy('total_sales', 'desc')
                ->take(10)
                ->get();

            // Get most viewed products
            $mostViewedProducts = $query->orderBy('views', 'desc')
                ->take(10)
                ->get(['id', 'name', 'price', 'views']);

            // Get low stock products
            $lowStockProducts = $query->where('manage_inventory', true)
                ->where('quantity', '<=', config('app.low_stock_threshold', 10))
                ->orderBy('quantity', 'asc')
                ->take(10)
                ->get(['id', 'name', 'quantity', 'manage_inventory']);

            // Get products with reviews
            $productReviews = DB::table('reviews')
                ->join('products', 'reviews.product_id', '=', 'products.id')
                ->whereBetween('reviews.created_at', [
                    $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                    $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                ])
                ->groupBy('products.id', 'products.name')
                ->select('products.id', 'products.name', DB::raw('COUNT(*) as review_count'), DB::raw('AVG(reviews.rating) as average_rating'))
                ->orderBy('average_rating', 'desc')
                ->take(10)
                ->get();

            return [
                'success' => true,
                'data' => $reportData,
                'top_selling_products' => $topSellingProducts,
                'most_viewed_products' => $mostViewedProducts,
                'low_stock_products' => $lowStockProducts,
                'product_reviews' => $productReviews,
            ];
        } catch (\Exception $e) {
            Log::error('Error generating product report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء تقرير المنتجات',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate customer report.
     *
     * @param array $filters
     * @return array
     */
    public function generateCustomerReport($filters = [])
    {
        try {
            $query = User::query()->whereHas('orders');

            // Apply date filters
            if (!empty($filters['start_date'])) {
                $query->whereHas('orders', function($q) use ($filters) {
                    $q->whereDate('created_at', '>=', $filters['start_date']);
                });
            }

            if (!empty($filters['end_date'])) {
                $query->whereHas('orders', function($q) use ($filters) {
                    $q->whereDate('created_at', '<=', $filters['end_date']);
                });
            }

            // Get report data
            $reportData = [
                'total_customers' => $query->count(),
                'total_revenue' => $query->has('orders')->join('orders', 'users.id', '=', 'orders.customer_id')
                    ->whereBetween('orders.created_at', [
                        $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                        $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                    ])
                    ->sum('orders.total_amount'),
                'average_order_value' => $query->has('orders')->join('orders', 'users.id', '=', 'orders.customer_id')
                    ->whereBetween('orders.created_at', [
                        $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                        $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                    ])
                    ->count() > 0 ? 
                    $query->has('orders')->join('orders', 'users.id', '=', 'orders.customer_id')
                        ->whereBetween('orders.created_at', [
                            $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                            $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                        ])
                        ->sum('orders.total_amount') / 
                    $query->has('orders')->join('orders', 'users.id', '=', 'orders.customer_id')
                        ->whereBetween('orders.created_at', [
                            $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                            $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                        ])
                        ->count() : 0,
            ];

            // Get top customers by spending
            $topCustomers = $query->has('orders')->join('orders', 'users.id', '=', 'orders.customer_id')
                ->whereBetween('orders.created_at', [
                    $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                    $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                ])
                ->groupBy('users.id', 'users.name', 'users.email')
                ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(orders.id) as order_count'), DB::raw('SUM(orders.total_amount) as total_spent'))
                ->orderBy('total_spent', 'desc')
                ->take(10)
                ->get();

            // Get customer acquisition data
            $customerAcquisition = [];
            $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date']) : Carbon::now()->subMonths(6);
            $endDate = !empty($filters['end_date']) ? Carbon::parse($filters['end_date']) : Carbon::now();

            for ($date = $startDate; $date->lte($endDate); $date->addMonth()) {
                $monthCustomers = User::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();

                $customerAcquisition[] = [
                    'month' => $date->format('Y-m'),
                    'customers' => $monthCustomers,
                ];
            }

            // Get customer retention data
            $customerRetention = [];
            $periodStart = !empty($filters['start_date']) ? Carbon::parse($filters['start_date']) : Carbon::now()->subMonths(3);
            $periodEnd = !empty($filters['end_date']) ? Carbon::parse($filters['end_date']) : Carbon::now();

            // Get customers who made purchases in the current period
            $currentPeriodCustomers = $query->has('orders')->join('orders', 'users.id', '=', 'orders.customer_id')
                ->whereBetween('orders.created_at', [$periodStart, $periodEnd])
                ->pluck('users.id')
                ->unique();

            // Get customers who made purchases in the previous period
            $previousPeriodStart = $periodStart->subMonths($periodEnd->diffInMonths($periodStart));
            $previousPeriodCustomers = User::whereHas('orders', function($q) use ($previousPeriodStart, $periodStart) {
                $q->whereBetween('created_at', [$previousPeriodStart, $periodStart]);
            })->pluck('id')->unique();

            // Calculate retention rate
            $retainedCustomers = $currentPeriodCustomers->intersect($previousPeriodCustomers)->count();
            $previousPeriodTotal = $previousPeriodCustomers->count();
            $retentionRate = $previousPeriodTotal > 0 ? ($retainedCustomers / $previousPeriodTotal) * 100 : 0;

            $customerRetention = [
                'previous_period_customers' => $previousPeriodTotal,
                'current_period_customers' => $currentPeriodCustomers->count(),
                'retained_customers' => $retainedCustomers,
                'retention_rate' => round($retentionRate, 2),
            ];

            return [
                'success' => true,
                'data' => $reportData,
                'top_customers' => $topCustomers,
                'customer_acquisition' => $customerAcquisition,
                'customer_retention' => $customerRetention,
            ];
        } catch (\Exception $e) {
            Log::error('Error generating customer report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء تقرير العملاء',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate vendor performance report.
     *
     * @param array $filters
     * @return array
     */
    public function generateVendorReport($filters = [])
    {
        try {
            $query = Vendor::query();

            // Apply date filters
            if (!empty($filters['start_date'])) {
                $query->whereHas('orders', function($q) use ($filters) {
                    $q->whereDate('created_at', '>=', $filters['start_date']);
                });
            }

            if (!empty($filters['end_date'])) {
                $query->whereHas('orders', function($q) use ($filters) {
                    $q->whereDate('created_at', '<=', $filters['end_date']);
                });
            }

            // Apply status filter
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // Get report data
            $reportData = [
                'total_vendors' => $query->count(),
                'total_revenue' => $query->has('orders')->join('orders', 'vendors.id', '=', 'orders.vendor_id')
                    ->whereBetween('orders.created_at', [
                        $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                        $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                    ])
                    ->sum('orders.total_amount'),
                'total_commission' => $query->has('orders')->join('orders', 'vendors.id', '=', 'orders.vendor_id')
                    ->whereBetween('orders.created_at', [
                        $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                        $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                    ])
                    ->sum(DB::raw('orders.total_amount * vendors.commission_rate / 100')),
            ];

            // Get top vendors by sales
            $topVendors = $query->has('orders')->join('orders', 'vendors.id', '=', 'orders.vendor_id')
                ->whereBetween('orders.created_at', [
                    $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                    $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                ])
                ->groupBy('vendors.id', 'vendors.name')
                ->select('vendors.id', 'vendors.name', DB::raw('COUNT(orders.id) as order_count'), DB::raw('SUM(orders.total_amount) as total_sales'))
                ->orderBy('total_sales', 'desc')
                ->take(10)
                ->get();

            // Get vendor performance by status
            $vendorPerformance = $query->has('orders')->join('orders', 'vendors.id', '=', 'orders.vendor_id')
                ->whereBetween('orders.created_at', [
                    $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                    $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                ])
                ->groupBy('vendors.status')
                ->select('vendors.status', DB::raw('COUNT(DISTINCT vendors.id) as vendor_count'), DB::raw('COUNT(orders.id) as order_count'), DB::raw('SUM(orders.total_amount) as total_sales'))
                ->get();

            // Get product count per vendor
            $vendorProductCounts = $query->withCount('products')
                ->orderBy('products_count', 'desc')
                ->take(10)
                ->get(['id', 'name', 'products_count']);

            // Get payout data
            $payoutData = DB::table('vendor_payouts')
                ->join('vendors', 'vendor_payouts.vendor_id', '=', 'vendors.id')
                ->whereBetween('vendor_payouts.created_at', [
                    $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d'),
                    $filters['end_date'] ?? Carbon::now()->format('Y-m-d')
                ])
                ->groupBy('vendors.id', 'vendors.name')
                ->select('vendors.id', 'vendors.name', DB::raw('SUM(vendor_payouts.amount) as total_payouts'), DB::raw('COUNT(vendor_payouts.id) as payout_count'))
                ->orderBy('total_payouts', 'desc')
                ->take(10)
                ->get();

            return [
                'success' => true,
                'data' => $reportData,
                'top_vendors' => $topVendors,
                'vendor_performance' => $vendorPerformance,
                'vendor_product_counts' => $vendorProductCounts,
                'payout_data' => $payoutData,
            ];
        } catch (\Exception $e) {
            Log::error('Error generating vendor report: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء تقرير البائعين',
                'error' => $e->getMessage(),
            ];
        }
    }
}
