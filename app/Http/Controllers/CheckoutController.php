<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Show checkout page
     */
    public function index()
    {
        $cartItems = session()->get('cart', []);

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'السلة فارغة');
        }

        // Calculate totals
        $subtotal = 0;
        $shipping = 0;
        $tax = 0;

        // Group items by vendor for shipping calculation
        $vendorItems = collect($cartItems)->groupBy('vendor_id');

        foreach ($vendorItems as $vendorId => $items) {
            $vendorSubtotal = $items->sum(function($item) {
                return $item['price'] * $item['quantity'];
            });

            $subtotal += $vendorSubtotal;
            $shipping += 20; // شحن ثابت لكل مورد
        }

        // Calculate tax (15% VAT)
        $tax = $subtotal * 0.15;
        $total = $subtotal + $shipping + $tax;

        // Get available payment methods
        $paymentGateways = $this->paymentService->getAvailableGateways();

        return view('checkout.index', compact(
            'cartItems', 
            'vendorItems', 
            'subtotal', 
            'shipping', 
            'tax', 
            'total',
            'paymentGateways'
        ));
    }

    /**
     * Process checkout
     */
    public function process(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string',
            'customer_city' => 'required|string|max:100',
            'customer_state' => 'nullable|string|max:100',
            'customer_country' => 'required|string|max:100',
            'customer_postal_code' => 'nullable|string|max:20',
            'payment_method' => 'required|string|in:stripe,paypal,fawry,paymob',
            'payment_method_id' => 'nullable|string', // For Stripe
            'agree_terms' => 'required|accepted',
        ]);

        $cartItems = session()->get('cart', []);

        if (empty($cartItems)) {
            return response()->json([
                'success' => false,
                'message' => 'السلة فارغة'
            ]);
        }

        DB::beginTransaction();

        try {
            // Group items by vendor
            $vendorItems = collect($cartItems)->groupBy('vendor_id');
            $allOrders = [];

            foreach ($vendorItems as $vendorId => $items) {
                // Create order for each vendor
                $order = $this->createOrder($request, $vendorId, $items->toArray());
                $allOrders[] = $order;

                // Create order items
                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'product_name' => $item['name'],
                        'product_sku' => Product::find($item['id'])->sku ?? '',
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['price'] * $item['quantity'],
                        'vendor_id' => $vendorId,
                    ]);

                    // Update product quantity
                    $product = Product::find($item['id']);
                    if ($product && $product->manage_inventory) {
                        $product->decrement('quantity', $item['quantity']);
                    }
                }

                // Process payment for this order
                $paymentResult = $this->paymentService->processPayment($order, [
                    'payment_method' => $request->payment_method,
                    'payment_method_id' => $request->payment_method_id,
                    'gateway_response' => null,
                ]);

                if (!$paymentResult['success']) {
                    DB::rollBack();
                    return response()->json($paymentResult);
                }
            }

            // Clear cart
            session()->forget('cart');

            DB::commit();

            // If payment requires redirect (like Fawry)
            if (isset($paymentResult['payment_url'])) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $paymentResult['payment_url'],
                    'message' => 'سيتم توجيهك لإكمال الدفع'
                ]);
            }

            return response()->json([
                'success' => true,
                'redirect_url' => route('checkout.success', ['orders' => collect($allOrders)->pluck('id')->implode(',')]),
                'message' => 'تم إنشاء الطلبات بنجاح'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Success page
     */
    public function success(Request $request)
    {
        $orderIds = explode(',', $request->get('orders', ''));
        $orders = Order::whereIn('id', $orderIds)->with(['items', 'vendor'])->get();

        if ($orders->isEmpty()) {
            return redirect()->route('home')->with('error', 'الطلبات غير موجودة');
        }

        return view('checkout.success', compact('orders'));
    }

    /**
     * Cancel page
     */
    public function cancel()
    {
        return view('checkout.cancel');
    }

    /**
     * Create order
     */
    private function createOrder(Request $request, $vendorId, array $items)
    {
        $subtotal = collect($items)->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });

        $shipping = 20; // ثابت لكل مورد
        $tax = $subtotal * 0.15; // 15% VAT
        $total = $subtotal + $shipping + $tax;

        return Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'user_id' => Auth::id(),
            'vendor_id' => $vendorId,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'customer_city' => $request->customer_city,
            'customer_state' => $request->customer_state,
            'customer_country' => $request->customer_country,
            'customer_postal_code' => $request->customer_postal_code,
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'status' => 'pending',
            'currency' => 'SAR',
        ]);
    }

    /**
     * Get shipping cost
     */
    public function getShippingCost(Request $request)
    {
        $request->validate([
            'city' => 'required|string',
            'country' => 'required|string'
        ]);

        $cartItems = session()->get('cart', []);
        $vendorCount = collect($cartItems)->pluck('vendor_id')->unique()->count();

        // شحن ثابت لكل مورد
        $shippingCost = $vendorCount * 20;

        // يمكن تخصيص الشحن حسب المدينة/البلد
        if ($request->country !== 'Saudi Arabia') {
            $shippingCost *= 2; // مضاعفة التكلفة للشحن الدولي
        }

        return response()->json([
            'shipping_cost' => $shippingCost,
            'vendor_count' => $vendorCount
        ]);
    }

    /**
     * Apply coupon
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string'
        ]);

        // البحث عن الكوبون
        $coupon = \App\Models\Coupon::where('code', $request->coupon_code)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'كود الخصم غير صالح أو منتهي الصلاحية'
            ]);
        }

        $cartItems = session()->get('cart', []);
        $subtotal = collect($cartItems)->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });

        // حساب الخصم
        if ($coupon->type === 'percentage') {
            $discount = $subtotal * ($coupon->value / 100);
        } else {
            $discount = $coupon->value;
        }

        // تطبيق الحد الأقصى للخصم
        if ($coupon->max_discount && $discount > $coupon->max_discount) {
            $discount = $coupon->max_discount;
        }

        // التحقق من الحد الأدنى لقيمة الطلب
        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'الحد الأدنى لقيمة الطلب هو ' . $coupon->min_order_amount . ' ريال'
            ]);
        }

        // حفظ الكوبون في الجلسة
        session()->put('applied_coupon', [
            'code' => $coupon->code,
            'discount' => $discount,
            'type' => $coupon->type,
            'value' => $coupon->value
        ]);

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'message' => 'تم تطبيق كود الخصم بنجاح'
        ]);
    }

    /**
     * Remove coupon
     */
    public function removeCoupon()
    {
        session()->forget('applied_coupon');

        return response()->json([
            'success' => true,
            'message' => 'تم إزالة كود الخصم'
        ]);
    }
}