
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Review;
use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get dashboard statistics.
     *
     * @param string $period ('day', 'week', 'month', 'year')
     * @return array
     */
    public function getDashboardStats($period = 'month')
    {
        try {
            $startDate = match ($period) {
                'day' => Carbon::now()->subDay(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonth(),
            };

            // Get basic statistics
            $stats = [
                'total_revenue' => Order::where('created_at', '>=', $startDate)
                    ->where('status', 'delivered')
                    ->sum('total_amount'),

                'total_orders' => Order::where('created_at', '>=', $startDate)
                    ->count(),

                'total_customers' => User::where('created_at', '>=', $startDate)
                    ->where('role', 'customer')
                    ->count(),

                'total_vendors' => Vendor::where('created_at', '>=', $startDate)
                    ->where('status', 'approved')
                    ->count(),

                'total_products' => Product::where('created_at', '>=', $startDate)
                    ->where('is_active', true)
                    ->where('is_approved', true)
                    ->count(),

                'average_order_value' => Order::where('created_at', '>=', $startDate)
                    ->where('status', 'delivered')
                    ->avg('total_amount'),
            ];

            // Get conversion rate
            $visitors = $this->getVisitorsCount($startDate);
            $stats['conversion_rate'] = $visitors > 0 ? ($stats['total_orders'] / $visitors) * 100 : 0;

            // Get new vs returning customers
            $newCustomers = User::where('created_at', '>=', $startDate)
                ->where('role', 'customer')
                ->count();

            $returningCustomers = User::where('created_at', '<', $startDate)
                ->where('role', 'customer')
                ->whereHas('orders', function($q) use ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                })
                ->count();

            $stats['new_customers'] = $newCustomers;
            $stats['returning_customers'] = $returningCustomers;
            $stats['customer_retention_rate'] = $newCustomers > 0 ? ($returningCustomers / $newCustomers) * 100 : 0;

            // Get top selling products
            $topProducts = OrderItem::whereHas('order', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->groupBy('product_id')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_sales'))
            ->orderBy('total_sales', 'desc')
            ->take(5)
            ->get();

            $stats['top_products'] = $topProducts->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'total_quantity' => $item->total_quantity,
                    'total_sales' => $item->total_sales,
                ];
            });

            // Get top vendors by sales
            $topVendors = Order::where('created_at', '>=', $startDate)
                ->where('status', 'delivered')
                ->groupBy('vendor_id')
                ->select('vendor_id', DB::raw('SUM(total_amount) as total_sales'))
                ->orderBy('total_sales', 'desc')
                ->take(5)
                ->get();

            $stats['top_vendors'] = $topVendors->map(function($item) {
                return [
                    'vendor_id' => $item->vendor_id,
                    'vendor_name' => $item->vendor->name,
                    'total_sales' => $item->total_sales,
                ];
            });

            // Get sales by category
            $salesByCategory = OrderItem::whereHas('order', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('product_category', 'products.id', '=', 'product_category.product_id')
            ->join('categories', 'product_category.category_id', '=', 'categories.id')
            ->groupBy('categories.id')
            ->select('categories.id', 'categories.name', DB::raw('SUM(order_items.quantity) as total_quantity'), DB::raw('SUM(order_items.subtotal) as total_sales'))
            ->orderBy('total_sales', 'desc')
            ->take(5)
            ->get();

            $stats['sales_by_category'] = $salesByCategory->map(function($item) {
                return [
                    'category_id' => $item->id,
                    'category_name' => $item->name,
                    'total_quantity' => $item->total_quantity,
                    'total_sales' => $item->total_sales,
                ];
            });

            // Get sales trends
            $salesTrends = $this->getSalesTrends($period);
            $stats['sales_trends'] = $salesTrends;

            // Get customer acquisition cost
            $stats['customer_acquisition_cost'] = $this->getCustomerAcquisitionCost($period);

            // Get customer lifetime value
            $stats['customer_lifetime_value'] = $this->getCustomerLifetimeValue($period);

            // Get product performance metrics
            $productMetrics = $this->getProductMetrics($period);
            $stats['product_metrics'] = $productMetrics;

            return [
                'success' => true,
                'stats' => $stats,
                'period' => $period,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting dashboard stats: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على الإحصائيات',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get sales trends.
     *
     * @param string $period ('day', 'week', 'month', 'year')
     * @return array
     */
    private function getSalesTrends($period)
    {
        $startDate = match ($period) {
            'day' => Carbon::now()->subDays(6),
            'week' => Carbon::now()->subWeeks(3),
            'month' => Carbon::now()->subMonths(11),
            'year' => Carbon::now()->subYears(4),
            default => Carbon::now()->subMonths(11),
        };

        $dateFormat = match ($period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m',
        };

        $sales = Order::where('created_at', '>=', $startDate)
            ->where('status', 'delivered')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "' . $dateFormat . '") as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as average_order_value')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $sales->toArray();
    }

    /**
     * Get customer acquisition cost.
     *
     * @param string $period ('day', 'week', 'month', 'year')
     * @return float
     */
    private function getCustomerAcquisitionCost($period)
    {
        // This is a simplified calculation. In a real implementation, you would consider marketing expenses.
        $startDate = match ($period) {
            'day' => Carbon::now()->subDay(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth(),
        };

        $marketingExpenses = 1000; // Placeholder value
        $newCustomers = User::where('created_at', '>=', $startDate)
            ->where('role', 'customer')
            ->count();

        return $newCustomers > 0 ? $marketingExpenses / $newCustomers : 0;
    }

    /**
     * Get customer lifetime value.
     *
     * @param string $period ('day', 'week', 'month', 'year')
     * @return float
     */
    private function getCustomerLifetimeValue($period)
    {
        $startDate = match ($period) {
            'day' => Carbon::now()->subDay(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth(),
        };

        $totalSales = Order::where('created_at', '>=', $startDate)
            ->where('status', 'delivered')
            ->sum('total_amount');

        $totalCustomers = User::where('role', 'customer')
            ->count();

        return $totalCustomers > 0 ? $totalSales / $totalCustomers : 0;
    }

    /**
     * Get product metrics.
     *
     * @param string $period ('day', 'week', 'month', 'year')
     * @return array
     */
    private function getProductMetrics($period)
    {
        $startDate = match ($period) {
            'day' => Carbon::now()->subDay(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth(),
        };

        $metrics = [
            'total_products' => Product::where('is_active', true)
                ->where('is_approved', true)
                ->count(),

            'total_views' => Product::sum('views'),

            'total_sold' => OrderItem::whereHas('order', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })->sum('quantity'),

            'total_revenue' => OrderItem::whereHas('order', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })->sum('subtotal'),
        ];

        // Get low stock products
        $lowStockProducts = Product::where('manage_inventory', true)
            ->where('quantity', '<=', config('app.low_stock_threshold', 10))
            ->count();

        $metrics['low_stock_products'] = $lowStockProducts;

        // Get out of stock products
        $outOfStockProducts = Product::where('manage_inventory', true)
            ->where('quantity', 0)
            ->count();

        $metrics['out_of_stock_products'] = $outOfStockProducts;

        // Get top viewed products
        $topViewedProducts = Product::orderBy('views', 'desc')
            ->take(5)
            ->get(['id', 'name', 'views']);

        $metrics['top_viewed_products'] = $topViewedProducts->map(function($item) {
            return [
                'product_id' => $item->id,
                'product_name' => $item->name,
                'views' => $item->views,
            ];
        });

        // Get top rated products
        $topRatedProducts = Product::whereHas('reviews', function($q) {
                $q->where('status', 'approved');
            })
            ->withAvg('reviews as average_rating', 'rating')
            ->orderBy('average_rating', 'desc')
            ->take(5)
            ->get(['id', 'name', 'average_rating']);

        $metrics['top_rated_products'] = $topRatedProducts->map(function($item) {
            return [
                'product_id' => $item->id,
                'product_name' => $item->name,
                'average_rating' => round($item->average_rating, 1),
            ];
        });

        return $metrics;
    }

    /**
     * Get visitors count.
     *
     * @param Carbon $startDate
     * @return int
     */
    private function getVisitorsCount($startDate)
    {
        // This is a simplified implementation. In a real implementation, you would use analytics data.
        // For demonstration purposes, we'll use a random number.
        return rand(1000, 5000);
    }

    /**
     * Get customer demographics.
     *
     * @param string $period ('day', 'week', 'month', 'year')
     * @return array
     */
    public function getCustomerDemographics($period = 'month')
    {
        try {
            $startDate = match ($period) {
                'day' => Carbon::now()->subDay(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonth(),
            };

            // Get customers by city
            $customersByCity = User::where('role', 'customer')
                ->whereNotNull('city')
                ->groupBy('city')
                ->select('city', DB::raw('COUNT(*) as count'))
                ->orderBy('count', 'desc')
                ->take(10)
                ->get();

            // Get customers by age group
            $now = Carbon::now();
            $customersByAgeGroup = User::where('role', 'customer')
                ->whereNotNull('date_of_birth')
                ->select([
                    DB::raw(
                        'CASE ' .
                        'WHEN TIMESTAMPDIFF(YEAR, date_of_birth, "' . $now->format('Y-m-d') . '") BETWEEN 18 AND 24 THEN "18-24" ' .
                        'WHEN TIMESTAMPDIFF(YEAR, date_of_birth, "' . $now->format('Y-m-d') . '") BETWEEN 25 AND 34 THEN "25-34" ' .
                        'WHEN TIMESTAMPDIFF(YEAR, date_of_birth, "' . $now->format('Y-m-d') . '") BETWEEN 35 AND 44 THEN "35-44" ' .
                        'WHEN TIMESTAMPDIFF(YEAR, date_of_birth, "' . $now->format('Y-m-d') . '") BETWEEN 45 AND 54 THEN "45-54" ' .
                        'WHEN TIMESTAMPDIFF(YEAR, date_of_birth, "' . $now->format('Y-m-d') . '") >= 55 THEN "55+" ' .
                        'ELSE "غير محدد" ' .
                        'END as age_group'
                    ),
                    DB::raw('COUNT(*) as count')
                ])
                ->groupBy('age_group')
                ->orderBy('count', 'desc')
                ->get();

            // Get customers by registration date
            $customersByRegistrationDate = User::where('role', 'customer')
                ->where('created_at', '>=', $startDate)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return [
                'success' => true,
                'customers_by_city' => $customersByCity,
                'customers_by_age_group' => $customersByAgeGroup,
                'customers_by_registration_date' => $customersByRegistrationDate,
                'period' => $period,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting customer demographics: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على بيانات الديموغرافية',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get sales by payment method.
     *
     * @param string $period ('day', 'week', 'month', 'year')
     * @return array
     */
    public function getSalesByPaymentMethod($period = 'month')
    {
        try {
            $startDate = match ($period) {
                'day' => Carbon::now()->subDay(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonth(),
            };

            $salesByPaymentMethod = Order::where('created_at', '>=', $startDate)
                ->where('status', 'delivered')
                ->groupBy('payment_method')
                ->select('payment_method', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total_amount) as total_sales'))
                ->orderBy('total_sales', 'desc')
                ->get();

            return [
                'success' => true,
                'sales_by_payment_method' => $salesByPaymentMethod,
                'period' => $period,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting sales by payment method: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على بيانات المبيعات حسب طريقة الدفع',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get sales by shipping method.
     *
     * @param string $period ('day', 'week', 'month', 'year')
     * @return array
     */
    public function getSalesByShippingMethod($period = 'month')
    {
        try {
            $startDate = match ($period) {
                'day' => Carbon::now()->subDay(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonth(),
            };

            $salesByShippingMethod = Order::where('created_at', '>=', $startDate)
                ->where('status', 'delivered')
                ->groupBy('shipping_method')
                ->select('shipping_method', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total_amount) as total_sales'), DB::raw('SUM(shipping_cost) as total_shipping_cost'))
                ->orderBy('total_sales', 'desc')
                ->get();

            return [
                'success' => true,
                'sales_by_shipping_method' => $salesByShippingMethod,
                'period' => $period,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting sales by shipping method: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على بيانات المبيعات حسب طريقة الشحن',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get product performance metrics.
     *
     * @param int $productId
     * @param string $period ('day', 'week', 'month', 'year')
     * @return array
     */
    public function getProductPerformance($productId, $period = 'month')
    {
        try {
            $startDate = match ($period) {
                'day' => Carbon::now()->subDay(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonth(),
            };

            $product = Product::find($productId);

            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'المنتج غير موجود',
                ];
            }

            // Get product sales
            $productSales = OrderItem::where('product_id', $productId)
                ->whereHas('order', function($q) use ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                })
                ->select(
                    DB::raw('DATE(order_items.created_at) as date'),
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(subtotal) as total_sales')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get product views
            $productViews = $product->views;

            // Get product reviews
            $productReviews = Review::where('product_id', $productId)
                ->where('status', 'approved')
                ->avg('rating');

            // Get product inventory
            $productInventory = $product->quantity;

            // Get product low stock warning
            $lowStockWarning = $product->manage_inventory && $product->quantity <= config('app.low_stock_threshold', 10);

            return [
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'sku' => $product->sku,
                ],
                'sales' => $productSales,
                'views' => $productViews,
                'average_rating' => round($productReviews, 1),
                'inventory' => $productInventory,
                'low_stock_warning' => $lowStockWarning,
                'period' => $period,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting product performance: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على بيانات أداء المنتج',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get vendor performance metrics.
     *
     * @param int $vendorId
     * @param string $period ('day', 'week', 'month', 'year')
     * @return array
     */
    public function getVendorPerformance($vendorId, $period = 'month')
    {
        try {
            $startDate = match ($period) {
                'day' => Carbon::now()->subDay(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonth(),
            };

            $vendor = Vendor::find($vendorId);

            if (!$vendor) {
                return [
                    'success' => false,
                    'message' => 'البائع غير موجود',
                ];
            }

            // Get vendor sales
            $vendorSales = Order::where('vendor_id', $vendorId)
                ->where('created_at', '>=', $startDate)
                ->where('status', 'delivered')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as orders_count'),
                    DB::raw('SUM(total_amount) as total_sales')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get vendor products count
            $vendorProductsCount = Product::where('vendor_id', $vendorId)
                ->where('is_active', true)
                ->where('is_approved', true)
                ->count();

            // Get vendor average rating
            $vendorAverageRating = Review::where('vendor_id', $vendorId)
                ->where('status', 'approved')
                ->avg('rating');

            // Get vendor reviews count
            $vendorReviewsCount = Review::where('vendor_id', $vendorId)
                ->where('status', 'approved')
                ->count();

            // Get vendor wallet balance
            $vendorWalletBalance = $vendor->wallet_balance;

            return [
                'success' => true,
                'vendor' => [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'email' => $vendor->email,
                    'phone' => $vendor->phone,
                ],
                'sales' => $vendorSales,
                'products_count' => $vendorProductsCount,
                'average_rating' => round($vendorAverageRating, 1),
                'reviews_count' => $vendorReviewsCount,
                'wallet_balance' => $vendorWalletBalance,
                'period' => $period,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting vendor performance: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على بيانات أداء البائع',
                'error' => $e->getMessage(),
            ];
        }
    }
}
