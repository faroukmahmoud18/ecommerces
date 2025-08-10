<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingProvider;
use App\Models\ShippingZone;
use App\Models\ShippingRate;
use App\Models\Address;
use App\Services\ShippingService;
use App\Services\ShippingIntegrationsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingController extends Controller
{
    protected $shippingService;
    protected $shippingIntegrationsService;

    public function __construct(
        ShippingService $shippingService,
        ShippingIntegrationsService $shippingIntegrationsService
    ) {
        $this->shippingService = $shippingService;
        $this->shippingIntegrationsService = $shippingIntegrationsService;
    }

    /**
     * الحصول على قائمة مزودي الشحن المتاحين
     */
    public function getProviders()
    {
        $result = $this->shippingService->getAvailableProviders();

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * الحصول على خيارات الشحن للعنوان والوزن
     */
    public function getShippingOptions(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'weight' => 'required|numeric|min:0',
            'order_total' => 'required|numeric|min:0',
        ]);

        $address = Address::findOrFail($request->address_id);

        // التحقق من أن العنوان ينتمي للمستخدم الحالي
        if ($address->user_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا العنوان لا ينتمي للمستخدم الحالي'
            ], 403);
        }

        $result = $this->shippingService->getShippingOptionsForAddress(
            $address,
            $request->weight,
            $request->order_total
        );

        return response()->json($result);
    }

    /**
     * الحصول على خيارات الشحن للطلب
     */
    public function getShippingOptionsForOrder(Request $request, Order $order)
    {
        // التحقق من أن الطلب ينتمي للمستخدم الحالي أو البائع
        if ($order->user_id != Auth::id() && $order->vendor_id != Auth::user()->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية الوصول لهذا الطلب'
            ], 403);
        }

        $address = new Address([
            'country' => $order->customer_country,
            'state' => $order->customer_state,
            'city' => $order->customer_city,
            'postal_code' => $order->customer_postal_code,
        ]);

        $result = $this->shippingService->getShippingOptionsForAddress(
            $address,
            $order->total_weight,
            $order->total_amount
        );

        return response()->json($result);
    }

    /**
     * إنشاء شحنة للطلب
     */
    public function createShipment(Request $request, Order $order)
    {
        // التحقق من أن المستخدم هو البائع للطلب
        if (Auth::user()->vendor_id != $order->vendor_id && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية إنشاء شحنة لهذا الطلب'
            ], 403);
        }

        // التحقق من حالة الطلب
        if ($order->status != 'confirmed' && $order->status != 'processing') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إنشاء شحنة للطلب في الحالة الحالية'
            ], 400);
        }

        $request->validate([
            'provider_id' => 'nullable|exists:shipping_providers,id',
            'tracking_number' => 'required_without:provider_id|string',
            'carrier' => 'required_without:provider_id|string',
            'shipping_method' => 'required_without:provider_id|string',
            'shipping_cost' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0',
            'dimensions' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $result = $this->shippingService->createShipment($order, $request->all());

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * الحصول على شحنة طلب
     */
    public function getShipment(Order $order)
    {
        // التحقق من أن الطلب ينتمي للمستخدم الحالي أو البائع
        if ($order->user_id != Auth::id() && $order->vendor_id != Auth::user()->vendor_id && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية الوصول لهذا الطلب'
            ], 403);
        }

        $shipment = $order->shipment;

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد شحنة لهذا الطلب'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'shipment' => $shipment->load('trackingEvents')
        ]);
    }

    /**
     * تتبع الشحنة
     */
    public function trackShipment(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'carrier' => 'required|string',
        ]);

        $result = $this->shippingService->trackShipment($request->tracking_number, $request->carrier);

        return response()->json($result);
    }

    /**
     * طباعة ملصق الشحنة
     */
    public function printLabel(Shipment $shipment)
    {
        // التحقق من أن الشحنة تنتمي للبائع
        if (Auth::user()->vendor_id != $shipment->vendor_id && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية الوصول لهذه الشحنة'
            ], 403);
        }

        if (!$shipment->label_url) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد ملصق شحن لهذه الشحنة'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'label_url' => $shipment->label_url
        ]);
    }

    /**
     * تحديث حالة الشحنة
     */
    public function updateStatus(Request $request, Shipment $shipment)
    {
        // التحقق من أن الشحنة تنتمي للبائع
        if (Auth::user()->vendor_id != $shipment->vendor_id && !Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية تعديل هذه الشحنة'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,created,shipped,in_transit,out_for_delivery,delivered,failed,returned',
            'event_location' => 'nullable|string',
            'event_description' => 'nullable|string',
        ]);

        $trackingEvent = [
            'event_time' => now(),
            'event_status' => $request->status,
            'event_location' => $request->event_location ?? $shipment->order->vendor->city,
            'event_description' => $request->event_description,
        ];

        $result = $this->shippingService->updateShipmentStatus($shipment, $request->status, $trackingEvent);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }
}