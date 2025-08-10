<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display admin dashboard.
     */
    public function index()
    {
        // Get statistics
        $totalUsers = User::count();
        $totalVendors = Vendor::count();
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', 'delivered')->sum('total_amount');
        $pendingOrders = Order::where('status', 'pending')->count();
        $pendingVendors = Vendor::where('status', 'pending')->count();
        $totalReviews = Review::count();
        $averageRating = Review::avg('rating');
        $totalPayouts = \App\Models\VendorPayout::where('payout_status', 'completed')->sum('amount');
        $pendingPayouts = \App\Models\VendorPayout::where('payout_status', 'pending')->count();

        // Get recent vendors
        $recentVendors = Vendor::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Get recent orders
        $recentOrders = Order::latest()
            ->take(5)
            ->get();

        // Get revenue chart data
        $revenueData = $this->getRevenueChartData();

        // Get sales chart data
        $salesData = $this->getSalesChartData();

        // Get top vendors by sales
        $topVendors = Vendor::withCount('orders')
            ->withSum(['orders' => function($query) {
                $query->select(DB::raw('SUM(total_amount)'));
            }])
            ->orderBy('orders_sum_total_amount', 'desc')
            ->take(5)
            ->get();

        // Get top selling products
        $topProducts = Product::withCount('orders')
            ->withSum(['orders' => function($query) {
                $query->select(DB::raw('SUM(quantity)'));
            }])
            ->orderBy('orders_sum_quantity', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalUsers',
            'totalVendors',
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'pendingOrders',
            'pendingVendors',
            'totalReviews',
            'averageRating',
            'recentVendors',
            'recentOrders',
            'revenueData',
            'salesData',
            'topVendors',
            'topProducts'
        ));
    }

    /**
     * Get revenue chart data for the last 30 days.
     */
    private function getRevenueChartData()
    {
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $total = Order::whereDate('created_at', $date)->sum('total_amount');

            $data[] = [
                'date' => $date,
                'total' => $total,
            ];
        }

        return $data;
    }

    /**
     * Get sales chart data for the last 30 days.
     */
    private function getSalesChartData()
    {
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = Order::whereDate('created_at', $date)->count();

            $data[] = [
                'date' => $date,
                'count' => $count,
            ];
        }

        return $data;
    }

    /**
     * Display admin profile.
     */
    public function profile()
    {
        return view('admin.profile.index');
    }

    /**
     * Update admin profile.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => bcrypt($request->password),
            ]);
        }

        return redirect()->route('admin.profile')
            ->with('success', 'تم تحديث ملفك الشخصي بنجاح');
    }

    /**
     * Display system settings.
     */
    public function settings()
    {
        return view('admin.settings.index');
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_description' => ['nullable', 'string'],
            'app_url' => ['required', 'url'],
            'default_commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'currency_code' => ['required', 'string', 'size:3'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_position' => ['required', 'string', 'in:left,right'],
            'date_format' => ['required', 'string'],
            'time_format' => ['required', 'string'],
            'timezone' => ['required', 'string'],
        ]);

        // Save settings to database
        foreach ($request->except('_token') as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('admin.settings')
            ->with('success', 'تم تحديث الإعدادات بنجاح');
    }

    /**
     * Get analytics data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analytics(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Get sales data
        $salesData = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_sales, COUNT(*) as total_orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get top vendors
        $topVendors = Vendor::whereHas('orders', function($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'delivered')
                      ->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->selectRaw('vendors.id, vendors.name, SUM(orders.total_amount) as total_sales')
            ->join('orders', 'vendors.id', '=', 'orders.vendor_id')
            ->groupBy('vendors.id', 'vendors.name')
            ->orderBy('total_sales', 'desc')
            ->take(10)
            ->get();

        // Get order status distribution
        $orderStatusDistribution = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Get payment status distribution
        $paymentStatusDistribution = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('payment_status, COUNT(*) as count')
            ->groupBy('payment_status')
            ->get();

        return response()->json([
            'sales_data' => $salesData,
            'top_vendors' => $topVendors,
            'order_status_distribution' => $orderStatusDistribution,
            'payment_status_distribution' => $paymentStatusDistribution,
        ]);
    }

    /**
     * Display reports page.
     *
     * @return \Illuminate\Http\Response
     */
    public function reports()
    {
        return view('admin.reports.index');
    }

    /**
     * Generate sales report.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateSalesReport(Request $request)
    {
        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'format' => ['required', 'string', 'in:pdf,xls,csv'],
        ]);

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $format = $request->input('format');

        // Get sales data
        $salesData = Order::with(['vendor', 'user'])
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->latest()
            ->get();

        // Get summary data
        $summary = [
            'total_orders' => $salesData->count(),
            'total_revenue' => $salesData->sum('total_amount'),
            'total_commission' => $salesData->sum(function($order) {
                return $order->total_amount * ($order->vendor->commission_rate / 100);
            }),
            'total_vendors' => $salesData->pluck('vendor_id')->unique()->count(),
        ];

        // Generate report based on format
        switch ($format) {
            case 'pdf':
                return $this->generateSalesReportPdf($salesData, $summary, $dateFrom, $dateTo);
            case 'xls':
                return $this->generateSalesReportExcel($salesData, $summary, $dateFrom, $dateTo);
            case 'csv':
                return $this->generateSalesReportCsv($salesData, $summary, $dateFrom, $dateTo);
            default:
                return back()->with('error', 'تنسيق غير مدعوم');
        }
    }

    /**
     * Generate sales report PDF.
     *
     * @param \Illuminate\Database\Eloquent\Collection $salesData
     * @param array $summary
     * @param string $dateFrom
     * @param string $dateTo
     * @return \Illuminate\Http\Response
     */
    private function generateSalesReportPdf($salesData, $summary, $dateFrom, $dateTo)
    {
        // In a real implementation, you would use a PDF library like DomPDF or TCPDF
        // For now, we'll return a view that can be printed as PDF
        return view('admin.reports.sales-pdf', compact('salesData', 'summary', 'dateFrom', 'dateTo'));
    }

    /**
     * Generate sales report Excel.
     *
     * @param \Illuminate\Database\Eloquent\Collection $salesData
     * @param array $summary
     * @param string $dateFrom
     * @param string $dateTo
     * @return \Illuminate\Http\Response
     */
    private function generateSalesReportExcel($salesData, $summary, $dateFrom, $dateTo)
    {
        // In a real implementation, you would use a library like Maatwebsite/Laravel-Excel
        // For now, we'll return a CSV file which can be opened in Excel
        return $this->generateSalesReportCsv($salesData, $summary, $dateFrom, $dateTo);
    }

    /**
     * Generate sales report CSV.
     *
     * @param \Illuminate\Database\Eloquent\Collection $salesData
     * @param array $summary
     * @param string $dateFrom
     * @param string $dateTo
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function generateSalesReportCsv($salesData, $summary, $dateFrom, $dateTo)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-report-' . $dateFrom . '-to-' . $dateTo . '.csv"',
        ];

        $callback = function() use ($salesData, $summary, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');

            // Add summary
            fputcsv($file, ['Sales Report Summary']);
            fputcsv($file, ['Report Period', $dateFrom . ' to ' . $dateTo]);
            fputcsv($file, ['Total Orders', $summary['total_orders']]);
            fputcsv($file, ['Total Revenue', $summary['total_revenue']]);
            fputcsv($file, ['Total Commission', $summary['total_commission']]);
            fputcsv($file, ['Total Vendors', $summary['total_vendors']]);
            fputcsv($file, []); // Empty row

            // Add headers
            fputcsv($file, [
                'Order ID',
                'Order Date',
                'Customer Name',
                'Customer Email',
                'Vendor Name',
                'Total Amount',
                'Commission Rate',
                'Commission Amount',
                'Net Amount',
                'Status',
                'Payment Status',
            ]);

            // Add data
            foreach ($salesData as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->customer_name,
                    $order->customer_email,
                    $order->vendor->name,
                    $order->total_amount,
                    $order->vendor->commission_rate . '%',
                    $order->total_amount * ($order->vendor->commission_rate / 100),
                    $order->total_amount - ($order->total_amount * ($order->vendor->commission_rate / 100)),
                    $order->status,
                    $order->payment_status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
