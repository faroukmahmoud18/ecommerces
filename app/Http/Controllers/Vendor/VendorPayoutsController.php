
<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorPayout;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PayoutProcessed;

class VendorPayoutsController extends Controller
{
    /**
     * Display a listing of the payouts.
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendor;

        $query = VendorPayout::where('vendor_id', $vendor->id);

        // Apply filters
        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->where('status', $status);
        }

        if ($request->filled('date_from')) {
            $dateFrom = $request->input('date_from');
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = $request->input('date_to');
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $payouts = $query->latest()->paginate(20);

        return view('vendor.payouts.index', compact('payouts'));
    }

    /**
     * Show the form for creating a new payout.
     */
    public function create()
    {
        $vendor = Auth::user()->vendor;

        // Get eligible orders for payout
        $eligibleOrders = Order::where('vendor_id', $vendor->id)
            ->where('status', 'delivered')
            ->whereDoesntHave('payouts')
            ->latest()
            ->get();

        // Calculate total eligible amount
        $totalAmount = $eligibleOrders->sum(function($order) {
            // Calculate vendor's share (after commission)
            $vendorShare = $order->total_amount - ($order->total_amount * ($order->vendor->commission_rate / 100));
            return $vendorShare;
        });

        return view('vendor.payouts.create', compact('eligibleOrders', 'totalAmount'));
    }

    /**
     * Store a newly created payout in storage.
     */
    public function store(Request $request)
    {
        $vendor = Auth::user()->vendor;

        $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['exists:orders,id'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,paypal,cheque'],
            'notes' => ['nullable', 'string'],
        ]);

        // Get selected orders
        $orders = Order::whereIn('id', $request->order_ids)
            ->where('vendor_id', $vendor->id)
            ->where('status', 'delivered')
            ->get();

        // Calculate total amount
        $totalAmount = $orders->sum(function($order) {
            // Calculate vendor's share (after commission)
            $vendorShare = $order->total_amount - ($order->total_amount * ($order->vendor->commission_rate / 100));
            return $vendorShare;
        });

        // Create payout
        DB::transaction(function() use ($request, $vendor, $orders, $totalAmount) {
            $payout = VendorPayout::create([
                'vendor_id' => $vendor->id,
                'amount' => $totalAmount,
                'fee' => 0, // Could be calculated based on payment method
                'net_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Link orders to payout
            $payout->orders()->attach($orders->pluck('id'));

            // Update vendor wallet balance
            $vendor->decrement('wallet_balance', $totalAmount);
        });

        return redirect()->route('vendor.payouts.index')
            ->with('success', 'تم إنشاء طلب الدفع بنجاح. سيتم مراجعته ومعالجته قريباً.');
    }

    /**
     * Display the specified payout.
     */
    public function show(VendorPayout $payout)
    {
        // Ensure the payout belongs to the vendor
        if ($payout->vendor_id !== Auth::user()->vendor->id) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الدفعة');
        }

        $payout->load(['orders', 'order']);

        return view('vendor.payouts.show', compact('payout'));
    }

    /**
     * Download payout receipt.
     */
    public function downloadReceipt(VendorPayout $payout)
    {
        // Ensure the payout belongs to the vendor
        if ($payout->vendor_id !== Auth::user()->vendor->id) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الدفعة');
        }

        // Here you would generate and return the receipt PDF
        // For now, we'll just return a placeholder response
        return response()->json([
            'success' => true,
            'message' => 'سيتم تنزيل إيصال الدفعة قريباً',
        ]);
    }
}
