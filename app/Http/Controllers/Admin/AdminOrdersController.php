
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vendor;
use App\Models\Shipment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusUpdated;

class AdminOrdersController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(Request $request)
    {
        $query = Order::latest();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->where('status', $status);
        }

        if ($request->filled('payment_status')) {
            $paymentStatus = $request->input('payment_status');
            $query->where('payment_status', $paymentStatus);
        }

        if ($request->filled('vendor')) {
            $vendorId = $request->input('vendor');
            $query->where('vendor_id', $vendorId);
        }

        if ($request->filled('date_from')) {
            $dateFrom = $request->input('date_from');
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = $request->input('date_to');
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $query->paginate(20);

        $vendors = Vendor::active()->get();

        return view('admin.orders.index', compact('orders', 'vendors'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['items.product', 'items.variant', 'vendor', 'payments', 'shipments']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,processing,shipped,delivered,cancelled,refunded'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Update order status
        $order->update(['status' => $newStatus]);

        // Handle specific status changes
        if ($newStatus === 'confirmed') {
            // Update payment status to paid if it was pending
            if ($order->payment_status === 'pending') {
                $order->update(['payment_status' => 'paid']);
            }
        } elseif ($newStatus === 'processing') {
            $order->update(['processed_at' => now()]);
        } elseif ($newStatus === 'shipped') {
            $order->update(['shipped_at' => now()]);
        } elseif ($newStatus === 'delivered') {
            $order->update(['delivered_at' => now()]);
        } elseif ($newStatus === 'cancelled') {
            $order->update(['cancelled_at' => now()]);
        }

        // Send notification to customer
        Mail::to($order->customer_email)->send(new OrderStatusUpdated($order, $oldStatus, $newStatus));

        return redirect()->back()
            ->with('success', 'تم تحديث حالة الطلب بنجاح');
    }

    /**
     * Update the payment status.
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => ['required', 'string', 'in:pending,paid,partially_paid,refunded,partially_refunded,failed,cancelled'],
        ]);

        $oldStatus = $order->payment_status;
        $newStatus = $request->payment_status;

        // Update payment status
        $order->update(['payment_status' => $newStatus]);

        // Handle specific status changes
        if ($newStatus === 'paid') {
            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'vendor_id' => $order->vendor_id,
                'amount' => $order->total_amount,
                'payment_method' => $order->payment_method,
                'payment_status' => 'paid',
                'transaction_id' => $request->input('transaction_id'),
                'notes' => $request->input('notes'),
            ]);

            // Update vendor wallet balance
            $vendor = $order->vendor;
            $vendor->increment('wallet_balance', $order->total_amount);
        }

        return redirect()->back()
            ->with('success', 'تم تحديث حالة الدفع بنجاح');
    }

    /**
     * Create a new shipment for the order.
     */
    public function createShipment(Request $request, Order $order)
    {
        return view('admin.orders.create-shipment', compact('order'));
    }

    /**
     * Store a new shipment.
     */
    public function storeShipment(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => ['required', 'string', 'max:255'],
            'carrier' => ['required', 'string', 'max:255'],
            'shipping_method' => ['required', 'string', 'max:255'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        // Prepare dimensions array
        $dimensions = null;
        if ($request->filled('length') || $request->filled('width') || $request->filled('height')) {
            $dimensions = [
                'length' => $request->input('length'),
                'width' => $request->input('width'),
                'height' => $request->input('height'),
            ];
        }

        // Prepare from address (vendor address)
        $fromAddress = [
            'name' => $order->vendor->name,
            'address' => $order->vendor->address,
            'city' => $order->vendor->city ?? '',
            'state' => $order->vendor->state ?? '',
            'country' => $order->vendor->country ?? '',
            'postal_code' => $order->vendor->postal_code ?? '',
        ];

        // Prepare to address (customer address)
        $toAddress = [
            'name' => $order->customer_name,
            'address' => $order->customer_address,
            'city' => $order->customer_city,
            'state' => $order->customer_state,
            'country' => $order->customer_country,
            'postal_code' => $order->customer_postal_code,
        ];

        // Create shipment
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'vendor_id' => $order->vendor_id,
            'tracking_number' => $request->tracking_number,
            'carrier' => $request->carrier,
            'status' => 'picked',
            'shipping_method' => $request->shipping_method,
            'shipping_cost' => $request->shipping_cost,
            'weight' => $request->weight,
            'dimensions' => $dimensions,
            'from_address' => $fromAddress,
            'to_address' => $toAddress,
            'notes' => $request->notes,
        ]);

        // Add tracking event
        $shipment->trackingEvents()->create([
            'event_code' => 'picked',
            'event_description' => 'تم استلام الشحنة من البائع',
            'event_location' => $order->vendor->city ?? '',
            'event_date' => now(),
            'event_time' => now(),
        ]);

        // Update order status to "shipped"
        $order->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'تم إنشاء الشحنة بنجاح');
    }

    /**
     * Update shipment status.
     */
    public function updateShipmentStatus(Request $request, Shipment $shipment)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,picked,in_transit,out_for_delivery,delivered,exception,returned'],
            'event_description' => ['nullable', 'string'],
            'event_location' => ['nullable', 'string'],
        ]);

        $oldStatus = $shipment->status;
        $newStatus = $request->status;

        // Update shipment status
        $shipment->update(['status' => $newStatus]);

        // Handle specific status changes
        if ($newStatus === 'delivered') {
            $shipment->update(['delivered_at' => now()]);

            // Update order status to "delivered" if all shipments are delivered
            $order = $shipment->order;
            if ($order->shipments()->where('status', '!=', 'delivered')->doesntExist()) {
                $order->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);
            }
        }

        // Add tracking event
        $shipment->trackingEvents()->create([
            'event_code' => $newStatus,
            'event_description' => $request->event_description ?? 'تم تحديث حالة الشحنة',
            'event_location' => $request->event_location ?? '',
            'event_date' => now(),
            'event_time' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'تم تحديث حالة الشحنة بنجاح');
    }

    /**
     * Download order invoice.
     */
    public function downloadInvoice(Order $order)
    {
        // Here you would generate and return the invoice PDF
        // For now, we'll just return a placeholder response
        return response()->json([
            'success' => true,
            'message' => 'سيتم تنزيل فاتورة الطلب قريباً',
        ]);
    }
}
