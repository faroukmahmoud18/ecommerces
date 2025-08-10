
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\ShippingMethod;
use App\Models\Address;
use App\Models\OrderStatus;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderStatusNotification;
use Carbon\Carbon;

class OrderService
{
    /**
     * Create a new order.
     *
     * @param array $data
     * @return array
     */
    public function createOrder(array $data)
    {
        DB::beginTransaction();

        try {
            // Create order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $data['customer_id'],
                'vendor_id' => $data['vendor_id'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'payment_status' => $data['payment_status'] ?? 'pending',
                'payment_method' => $data['payment_method'] ?? null,
                'subtotal' => $data['subtotal'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'total_amount' => $data['total_amount'] ?? 0,
                'currency' => $data['currency'] ?? config('app.currency', 'SAR'),
                'notes' => $data['notes'] ?? null,
                'billing_address_id' => $data['billing_address_id'] ?? null,
                'shipping_address_id' => $data['shipping_address_id'] ?? null,
            ]);

            // Add order items if provided
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'product_sku' => $item['product_sku'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['total'],
                        'vendor_id' => $item['vendor_id'] ?? null,
                    ]);
                }
            }

            // Create payment if provided
            if (!empty($data['payment'])) {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $data['payment']['amount'] ?? $order->total_amount,
                    'payment_method' => $data['payment']['payment_method'] ?? $order->payment_method,
                    'transaction_id' => $data['payment']['transaction_id'] ?? null,
                    'status' => $data['payment']['status'] ?? 'pending',
                    'paid_at' => $data['payment']['paid_at'] ?? null,
                ]);
            }

            // Create shipping if provided
            if (!empty($data['shipping'])) {
                Shipping::create([
                    'order_id' => $order->id,
                    'shipping_method_id' => $data['shipping']['shipping_method_id'] ?? null,
                    'tracking_number' => $data['shipping']['tracking_number'] ?? null,
                    'status' => $data['shipping']['status'] ?? 'pending',
                    'shipped_at' => $data['shipping']['shipped_at'] ?? null,
                    'delivered_at' => $data['shipping']['delivered_at'] ?? null,
                ]);
            }

            // Create order status history
            OrderStatus::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'notes' => $data['status_notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            // Send order notification to customer
            $order->customer->notify(new OrderStatusNotification($order));

            return [
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'message' => 'تم إنشاء الطلب بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating order: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الطلب',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update order status.
     *
     * @param Order $order
     * @param array $data
     * @return array
     */
    public function updateOrderStatus(Order $order, array $data)
    {
        DB::beginTransaction();

        try {
            // Update order
            $order->update([
                'status' => $data['status'],
                'payment_status' => $data['payment_status'] ?? $order->payment_status,
                'notes' => $data['notes'] ?? $order->notes,
            ]);

            // Create order status history
            OrderStatus::create([
                'order_id' => $order->id,
                'status' => $data['status'],
                'notes' => $data['status_notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // Update shipping status if provided
            if (!empty($data['shipping_status'])) {
                $shipping = $order->shipping;
                if ($shipping) {
                    $shipping->update([
                        'status' => $data['shipping_status'],
                        'shipped_at' => $data['shipped_at'] ?? null,
                        'delivered_at' => $data['delivered_at'] ?? null,
                    ]);
                }
            }

            // Update payment status if provided
            if (!empty($data['payment_status'])) {
                $payment = $order->payment;
                if ($payment) {
                    $payment->update([
                        'status' => $data['payment_status'],
                        'paid_at' => $data['paid_at'] ?? null,
                    ]);
                }
            }

            DB::commit();

            // Send order notification to customer
            $order->customer->notify(new OrderStatusNotification($order));

            return [
                'success' => true,
                'order_id' => $order->id,
                'message' => 'تم تحديث حالة الطلب بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating order status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الطلب',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a refund.
     *
     * @param Order $order
     * @param array $data
     * @return array
     */
    public function createRefund(Order $order, array $data)
    {
        DB::beginTransaction();

        try {
            // Create refund
            $refund = Refund::create([
                'order_id' => $order->id,
                'order_item_id' => $data['order_item_id'] ?? null,
                'customer_id' => $order->customer_id,
                'vendor_id' => $order->vendor_id,
                'amount' => $data['amount'],
                'reason' => $data['reason'],
                'status' => $data['status'] ?? 'pending',
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // Create refund status history
            \App\Models\RefundStatus::create([
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'notes' => $data['status_notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'message' => 'تم إنشاء الاسترداد بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating refund: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الاسترداد',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get order statistics.
     *
     * @param array $data
     * @return array
     */
    public function getOrderStatistics(array $data = [])
    {
        try {
            $startDate = $data['start_date'] ?? Carbon::now()->subMonth()->toDateString();
            $endDate = $data['end_date'] ?? Carbon::now()->toDateString();
            $vendorId = $data['vendor_id'] ?? null;

            // Get order statistics
            $orderStats = Order::where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->when($vendorId, function($query) use ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                })
                ->select(
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(total_amount) as total_revenue'),
                    DB::raw('AVG(total_amount) as average_order_value'),
                    DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_orders'),
                    DB::raw('COUNT(CASE WHEN status = "processing" THEN 1 END) as processing_orders'),
                    DB::raw('COUNT(CASE WHEN status = "shipped" THEN 1 END) as shipped_orders'),
                    DB::raw('COUNT(CASE WHEN status = "delivered" THEN 1 END) as delivered_orders'),
                    DB::raw('COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled_orders'),
                    DB::raw('COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as paid_orders'),
                    DB::raw('COUNT(CASE WHEN payment_status = "pending" THEN 1 END) as pending_payments'),
                    DB::raw('COUNT(CASE WHEN payment_status = "failed" THEN 1 END) as failed_payments'),
                )
                ->first();

            // Get monthly sales
            $monthlySales = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $monthStart = $month->startOfMonth();
                $monthEnd = $month->endOfMonth();

                $monthlySales[] = [
                    'month' => $month->format('M Y'),
                    'orders' => Order::where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd)
                        ->when($vendorId, function($query) use ($vendorId) {
                            $query->where('vendor_id', $vendorId);
                        })
                        ->count(),
                    'revenue' => Order::where('created_at', '>=', $monthStart)
                        ->where('created_at', '<=', $monthEnd)
                        ->when($vendorId, function($query) use ($vendorId) {
                            $query->where('vendor_id', $vendorId);
                        })
                        ->sum('total_amount'),
                ];
            }

            // Get top selling products
            $topProducts = OrderItem::whereHas('order', function($query) use ($startDate, $endDate, $vendorId) {
                $query->where('created_at', '>=', $startDate)
                      ->where('created_at', '<=', $endDate);

                if ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                }
            })
            ->groupBy('product_id')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total) as total_amount'))
            ->orderBy('total_quantity', 'desc')
            ->take(10)
            ->get();

            // Get payment methods
            $paymentMethods = Payment::whereHas('order', function($query) use ($startDate, $endDate, $vendorId) {
                $query->where('created_at', '>=', $startDate)
                      ->where('created_at', '<=', $endDate);

                if ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                }
            })
            ->groupBy('payment_method')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->orderBy('count', 'desc')
            ->get();

            return [
                'success' => true,
                'data' => [
                    'order_stats' => $orderStats,
                    'monthly_sales' => $monthlySales,
                    'top_products' => $topProducts,
                    'payment_methods' => $paymentMethods,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting order statistics: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على إحصائيات الطلبات',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate order number.
     *
     * @return string
     */
    private function generateOrderNumber()
    {
        $prefix = config('order.prefix', 'ORD');
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $random = mt_rand(1000, 9999);

        return $prefix . $year . $month . $day . $random;
    }
}
