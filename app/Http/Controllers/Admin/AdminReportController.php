<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Payment;
use App\Models\VendorPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class AdminReportController extends Controller
{
    /**
     * Display reports dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Generate sales report.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function sales(Request $request)
    {
        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'format' => ['required', 'string', 'in:pdf,xls,csv'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'status' => ['nullable', 'string', 'in:pending,confirmed,processing,shipped,delivered,cancelled,refunded'],
        ]);

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $format = $request->input('format');
        $vendorId = $request->input('vendor_id');
        $status = $request->input('status');

        // Build query
        $query = Order::with(['vendor', 'user'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        // Apply filters
        if (!empty($vendorId)) {
            $query->where('vendor_id', $vendorId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Get sales data
        $salesData = $query->latest()->get();

        // Get summary data
        $summary = [
            'total_orders' => $salesData->count(),
            'total_revenue' => $salesData->sum('total_amount'),
            'total_commission' => $salesData->sum(function($order) {
                return $order->total_amount * ($order->vendor->commission_rate / 100);
            }),
            'total_vendors' => $salesData->pluck('vendor_id')->unique()->count(),
        ];

        // Get vendors for filter
        $vendors = Vendor::active()->get();

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
     * Generate vendor report.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function vendors(Request $request)
    {
        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'format' => ['required', 'string', 'in:pdf,xls,csv'],
        ]);

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $format = $request->input('format');

        // Get vendor data
        $vendorData = Vendor::with(['user', 'products' => function($query) use ($dateFrom, $dateTo) {
                $query->whereHas('orders', function($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                });
            }])
            ->withCount(['orders' => function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->withSum(['orders' => function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            }], 'total_amount')
            ->get();

        // Get summary data
        $summary = [
            'total_vendors' => $vendorData->count(),
            'total_products' => $vendorData->sum(function($vendor) {
                return $vendor->products()->count();
            }),
            'total_orders' => $vendorData->sum('orders_count'),
            'total_revenue' => $vendorData->sum('orders_sum_total_amount'),
        ];

        // Generate report based on format
        switch ($format) {
            case 'pdf':
                return $this->generateVendorReportPdf($vendorData, $summary, $dateFrom, $dateTo);
            case 'xls':
                return $this->generateVendorReportExcel($vendorData, $summary, $dateFrom, $dateTo);
            case 'csv':
                return $this->generateVendorReportCsv($vendorData, $summary, $dateFrom, $dateTo);
            default:
                return back()->with('error', 'تنسيق غير مدعوم');
        }
    }

    /**
     * Generate product report.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function products(Request $request)
    {
        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'format' => ['required', 'string', 'in:pdf,xls,csv'],
        ]);

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $format = $request->input('format');

        // Get product data
        $productData = \App\Models\OrderItem::with('product', 'product.vendor')
            ->whereHas('order', function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(subtotal) as total_sales')
            ->groupBy('product_id')
            ->orderBy('total_sales', 'desc')
            ->get();

        // Get summary data
        $summary = [
            'total_products' => $productData->count(),
            'total_quantity' => $productData->sum('total_quantity'),
            'total_sales' => $productData->sum('total_sales'),
            'average_price' => $productData->avg('total_sales') / $productData->avg('total_quantity'),
        ];

        // Generate report based on format
        switch ($format) {
            case 'pdf':
                return $this->generateProductReportPdf($productData, $summary, $dateFrom, $dateTo);
            case 'xls':
                return $this->generateProductReportExcel($productData, $summary, $dateFrom, $dateTo);
            case 'csv':
                return $this->generateProductReportCsv($productData, $summary, $dateFrom, $dateTo);
            default:
                return back()->with('error', 'تنسيق غير مدعوم');
        }
    }

    /**
     * Generate commission report.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
 => ['required', 'after_or_equal:date 'format' => ['required', 'in:pdf,x'],
        ]);

        $date('date_from');
        $ $request->_to');
        $format =input('format // Get commission data
       Data = Vendor::with(['orders' => function($querydateFrom, $dateTo $query->created_at', [$dateFromTo])
                      ->where('delivered');
            }])
                       ->map(function($vendor $totalSales = $vendorsum('total_amount');
                = $vendor->commission_ratecommissionAmount = $totalSalesRate / 100);

 [
                    'vendor_id'->id,
                    'vendor $vendor->name,
                   ' => $vendor->user                    'commission_rate' =>,
                    ' => $totalSales,
                   ' => $commissionAmount,
_amount' => $totalSalesAmount,
                ];
            })
Desc('commission_amount');

        data
        $summary =total_vendors' => $count(),
            'total_salescommissionData->sum('total 'total_commission' =>->sum('commission_amount'),
_net' => $commissionDatanet_amount'),
            'average' => $avg('commission ];

        // Generate report based        switch ($format) 'pdf':
                return $CommissionReportPdf($commissionData, $summaryFrom, $dateTo);
xls':
                return $thisReportExcel($commissionData, $dateFrom, $date case 'csv $this->Csv($commissionData, $dateFrom, $dateTo:
                return back()->with 'تنسيق غيروم');
        }
    }

 * Generate sales report PDF.
 * @param \Illuminate\DatabasesalesData
     * @ $summary
     * @dateFrom
     * @dateTo
     * @\Http\Response
     */
   SalesReportPdf($salesData, $dateFrom, $    {
        return view('.sales-pdf', compact(' 'summary', 'dateFromTo'));
    }

    /**
 sales report Excel.
     *
param \Illuminate\Database\Eloquent\Collection
     * @param array     * @param string $     * @param string $     * @return \Illuminate     */
    private function generate($salesData, $summaryFrom, $dateTo)
 return $thisReportCsv($salesData, $summary, $dateFrom, $dateTo);
    }

    /**
     * Generate sales report CSV.
     *
     * @\Database\Eloquent\Collection $salesData
     * @param array     * @param string $     * @param string $     * @return \Symfony\StreamedResponse
 private function generateSalesReportCsv($salesData, $dateFrom, $    {
        $headers =Content-Type' => 'text            'Content-Disposition' => filename="sales-report-' . . '-todateTo . '.csv"',
        $callback = function()Data, $summary, $ $dateTo) {
            fopen('php://output',            // Add summary
csv($file, ['Sales']);
            fputcsv($Report Period', $dateFrom ' . $dateTo]);
csv($file, ['Totalsummary['total fputcsv($file,', $summary['total_re fputcsv($file,', $summary['total_com fputcsv($file,endors', $summary['total            fput, []); // Empty row Add headers
            fput, [
                'Order IDOrder Date',
                'Customer 'Customer EmailVendor Name',
                'Total 'Commission Rate',
                '',
                'Net Amount',
',
                '',
            ]);

            // Add foreach ($salesData as $                fputcsv($                    $order->id,
->created_atY-m-d H:i:sorder->customer_name,
                   customer_email,
                    $ordername,
                    $order->                    $ordercommission_rate . '%',
                   total_amount * ($order->_rate / 100),
                   total_amount - ($order-> ($order->vendor->commission100)),
                    $order-> $order->payment_status,
 }

            fclose($file);
 return response()->stream($callback, $headers);
    }

 * Generate vendor report PDF     * @param \Illuminate $vendorData @param array $summary
param string $dateFrom
param string $dateTo
return \Illuminate\Http\Response
 private function generateVendorReportData, $summary, $ $dateTo)
    {
('admin.reports.vendor compact('vendorData', ' 'dateFrom', 'date }

    /**
     * Generate Excel.
     *
     * @param \\Collection $vendorData
     * @param
     * @param string
     * @param string
     * @return \
     */
    private functionExcel($vendorData, $dateFrom,)
    {
        return $VendorReportCsv($vendorData, $dateFrom, $    }

    /**
     * CSV.
     *
     *\Collection $vendorData
     array $summary
     * $dateFrom
     * $dateTo
     *Symfony\Component\HttpFoundation\Stream     */
   VendorReportCsv($vendorData, $dateFrom, $    {
        $headers =Content-Type' => 'text            'Content-Disposition' => filename="vendor-report-'From . '-to-'To . '.csv"',
        $callback = function()Data, $summary, $ $dateTo) {
            fopen('php://output',            // Add summary
csv($file, ['']);
            fputcsv($Report Period', $dateFrom ' . $dateTo]);
csv($file, ['Total $summary['total_vendorsputcsv($file, [' $summary['total_products']]);
csv($file, ['Totalsummary['total_orders']]);
csv($file Revenue', $_revenue']]);
            fput, []); // Empty // Add headers
            ffile, [
                'Vendor 'Vendor NameVendor Email',
                'Total 'Total Orders',
                '            ]);

            //            foreach ($vendorData as {
                ffile, [
                    $vendor                    $vendor->name,
->user->email,
                   products()->count(),
                    $_count,
                    $vendor->_amount,
                ]);
            fclose($file);
        };

()->stream($callback, $headers);
    }

 * Generate product report PDF     * @param \Illuminate $productData
     array $summary
     * $dateFrom
     * $dateTo @return \Illuminate\Http\Response    private function generateProductReportData, $summary, $ $dateTo)
    {
('admin.reports.product-pdf', compact(' 'summary', 'dateFromdateTo));
    }

 * Generate product report Excel.
 * @param \Illuminate\DatabaseproductData
     * @ * @param string $date * @param string $date * @return \Illuminate\Http */
    private function generateProductproductData, $summary,, $dateTo)
 return $this->generateProductproductData, $summary,, $dateTo);
 /**
     * Generate product report *
     * @param \\Collection $product * @param array $summary @param string $dateFrom @param string $dateTo @return \Symfony\Component\HttpFoundationedResponse
     */
   ProductReportCsv, $summary, $datedateTo)
    {
        [
            'Content-Type'/csv',
            'Content-Dispositionattachment; filename="product-reportdateFrom . . $dateTo .        ];

        $callback use ($productData, $dateFrom, $dateTo $file = fopen('php 'w');

 Add summary
            fput, ['Product Report fputcsv($file,', $dateFrom . ' $dateTo]);
            ffile, ['Total Products',total_products']]);
            fput, ['Total Quantity',total_quantity']]);
            fput, ['Total Sales', $_sales']]);
            fputcsv ['Average Price', $summary']]);
            ffile, []); // Empty // Add headers
            ffile, [
                'Product 'Product Name',
                '                'Total                'Total Sales',
            // Add data
            foreach as $item) {
               ($file, [
                    $->id,
->product->name,
                   product->vendor->name,
->total_quantity,
                    $_sales,
                ]);
            fclose($file);
        };

()->stream($callback, $headers);
    }

 * Generate commission report PDF     * @commissionData
     * @ $summary
     * @dateFrom
     * @dateTo
     * @\Http\Response
     */
    private function generatePdf($commissionsummary, $dateFrom,)
    {
        return viewports.commission-pdf', compactData', 'summary', ' $dateTo));
    /**
     * Generate commission.
     *
     * @\Support\Collection $commissionData
param array $summary
     string $dateFrom
     string $dateTo
     \Illuminate\Http\Response
     function generateCommissionReportExcel($ $summary, $dateFromTo)
    {
       ->generateCommissionReportCsv, $summaryFrom, $dateTo);
    /**
     * Generate commission.
     *
     * @\Support\Collection $commissionData
param array $summary
     string $dateFrom
     string $date * @return \Symfony\ComponentedResponse
     */
   CommissionReportCsv($commissionData, $dateFrom, $    {
        [
            ' => 'text/csv',
           ' => 'attachment; filename-' . $dateFrom . . $dateTo .        ];

        $callback use ($commissionsummary, $dateFrom,) {
            $file =://output', 'w');

 Add summary
            fput, ['Commission Report Summaryputcsv($file, [' $dateFrom . ' todateTo]);
            fput, ['Total Vendors',total_vendors']]);
            ffile, ['Total Sales',total_sales']]);
            fput, ['Total Commission', $_commission']]);
            fput, ['Total Net',total_net']]);
            fput, ['Average Commission Rate',_rate'] . '%']);
           ($file, []); //            // Add headers
           ($file, [
                '                'Vendor Name',
                'Vendor EmailCommission Rate',
                'Total 'Commission Amount',
                '            ]);

            //            foreach ($commissionData as {
                fputcsv($                    $data['vendor_iddata['vendor_name'],
                   vendor_email'],
                    $data'] . '%',
                    $_sales'],
                    $data['                    $data['net_amount            }

            fclose($        };

        return response()->, 200, $headers}
