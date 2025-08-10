<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get search parameters
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $payment_status = $request->input('payment_status', '');
        $vendor_id = $request->input('vendor_id', '');
        $date_from = $request->input('date_from', '');
        $date_to = $request->input('date_to', '');
        $sort_by = $request->input('sort_by', 'latest'); // latest, oldest, amount_asc, amount_desc

        // Build query
        $query = Order::query();

        // Apply search
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('order_number', 'like', '%' . $search . '%')
                      ->orWhere('customer_name', 'like', '%' . $search . '%')
                      ->orWhere('customer_email', 'like', '%' . $search . '%');
            });
        }

        // Apply status filter
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Apply payment status filter
        if (!empty($payment_status)) {
            $query->where('payment_status', $payment_status);
        }

        // Apply vendor filter
        if (!empty($vendor_id)) {
            $query->where('vendor_id', $vendor_id);
        }

        // Apply date range filter
        if (!empty($date_from) && !empty($date_to)) {
            $query->whereBetween('created_at', [$date_from, $date_to]);
        }

        // Apply sorting
        switch ($sort_by) {
            case 'oldest':
                $query->oldest();
                break;
            case 'amount_asc':
                $query->orderBy('total_amount', 'asc');
                break;
            case 'amount_desc':
                $query->orderBy('total_amount', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // Get orders with pagination
        $orders = $query->with(['vendor', 'user', 'payments'])
                         ->latest()
                         ->paginate(20);

        // Get vendors for filter
        $vendors = Vendor::active()->get();

        // Get order status options
        $statusOptions = [
            'pending' => 'قيد الانتظار',
            'confirmed' => 'مؤكد',
            'processing' => 'قيد المعالجة',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغي',
            'refunded' => 'تم الإرجاع',
        ];

        // Get payment status options
        $paymentStatusOptions = [
            'pending' => 'قيد الانتظار',
            'paid' => 'مدفوع',
            'partially_paid' => 'مدفوع جزئياً',
            'refunded' => 'مسترد',
            'partially_refunded' => 'مسترد جزئياً',
            'failed' => 'فشل',
            'cancelled' => 'ملغي',
        ];

        return view('admin.orders.index', compact(
            'orders', 
            'vendors', 
            'statusOptions', 
            'paymentStatusOptions',
            'search',
            'status',
            'payment_status',
            'vendor_id',
            'date_from',
            'date_to',
            'sort_by'
        ));
    }

    /**
     * Display the specified order.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $order->load(['vendor', 'user', 'items.product', 'items.variant', 'payments', 'shipments']);

        // Calculate statistics
        $subtotal = $order->items->sum('subtotal');
        $shippingCost = $order->shipping_cost;
        $taxAmount = $order->tax_amount;
        $discountAmount = $order->discount_amount;
        $totalAmount = $order->total_amount;
        $commissionRate = $order->vendor->commission_rate ?? 0;
        $commissionAmount = $totalAmount * ($commissionRate / 100);
        $vendorNetAmount = $totalAmount - $commissionAmount;

        return view('admin.orders.show', compact(
            'order',
            'subtotal',
            'shippingCost',
            'taxAmount',
            'discountAmount',
            'totalAmount',
            'commissionRate',
            'commissionAmount',
            'vendorNetAmount'
        ));
    }

    /**
     * Update the specified order in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,processing,shipped,delivered,cancelled,refunded'],
            'payment_status' => ['required', 'string', 'in:pending,paid,partially_paid,refunded,partially_refunded,failed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            // Update order
            $order->update([
                'status' => $request->status,
                'payment_status' => $request->payment_status,
                'notes' => $request->notes,
            ]);

            // If order is cancelled, update stock for managed inventory products
            if ($request->status === 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product->manage_inventory) {
                        $item->product->increment('quantity', $item->quantity);
                    }
                }
            }

            // If order is delivered, create vendor payout record
            if ($request->status === 'delivered') {
                $commissionRate = $order->vendor->commission_rate ?? 0;
                $commissionAmount = $order->total_amount * ($commissionRate / 100);
                $vendorNetAmount = $order->total_amount - $commissionAmount;

                // Create payout record
                $order->vendor->payouts()->create([
                    'order_id' => $order->id,
                    'amount' => $vendorNetAmount,
                    'fee' => $commissionAmount,
                    'net_amount' => $vendorNetAmount,
                    'payout_method' => 'platform',
                    'payout_status' => 'pending',
                    'notes' => 'Payout for order #' . $order->order_number,
                ]);

                // Add amount to vendor wallet
                $order->vendor->increment('wallet_balance', $vendorNetAmount);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'تم تحديث الطلب بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث الطلب: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified order from storage.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        try {
            DB::beginTransaction();

            // Delete order items
            $order->items()->delete();

            // Delete payments
            $order->payments()->delete();

            // Delete shipments
            $order->shipments()->delete();

            // Delete order
            $order->delete();

            DB::commit();

            return redirect()->route('admin.orders.index')
                ->with('success', 'تم حذف الطلب بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء حذف الطلب: ' . $e->getMessage());
        }
    }

    /**
     * Display order analytics.
     *
     * @return \Illuminate\Http\Response
     */
    public function analytics(Request $request)
    {
        // Get date range
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Get sales data
        $salesData = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_sales, COUNT(*) as total_orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get top selling products
        $topProducts = OrderItem::whereHas('order', function($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'delivered')
                      ->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->with('product')
            ->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(subtotal) as total_sales')
            ->groupBy('product_id')
            ->orderBy('total_sales', 'desc')
            ->take(10)
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

        return view('admin.orders.analytics', compact(
            'dateFrom',
            'dateTo',
            'salesData',
            'topProducts',
            'topVendors',
            'orderStatusDistribution',
            'paymentStatusDistribution'
        ));
    }
}
