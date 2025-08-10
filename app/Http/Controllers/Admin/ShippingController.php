<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingProvider;
use App\Models\ShippingZone;
use App\Models\ShippingRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminShippingController extends Controller
{
    /**
     * عرض صفحة إدارة الشحن
     */
    public function index()
    {
        // إحصائيات الشحنات
        $totalShipments = Shipment::count();
        $pendingShipments = Shipment::where('status', 'pending')->count();
        $deliveredShipments = Shipment::where('status', 'delivered')->count();
        $failedShipments = Shipment::where('status', 'failed')->count();

        // جلب بيانات الشحنات مع العلاقات
        $shipments = Shipment::with(['order', 'order.vendor', 'order.customer'])
            ->latest()
            ->paginate(15);

        // جلب مزودي الشحن
        $providers = ShippingProvider::orderBy('priority', 'desc')->get();

        // جلب مناطق الشحن
        $zones = ShippingZone::with('provider')
            ->orderBy('providers.priority', 'desc')
            ->orderBy('zones.priority', 'desc')
            ->get();

        // جلب أسعار الشحن
        $rates = ShippingRate::with(['zone', 'zone.provider'])
            ->orderBy('zones.priority', 'desc')
            ->orderBy('rates.priority', 'desc')
            ->get();

        return view('admin.shipping.index', compact(
            'totalShipments',
            'pendingShipments',
            'deliveredShipments',
            'failedShipments',
            'shipments',
            'providers',
            'zones',
            'rates'
        ));
    }

    /**
     * جلب بيانات الشحنات عبر API
     */
    public function getShipments(Request $request)
    {
        $query = Shipment::with(['order', 'order.vendor', 'order.customer']);

        // التصفية حسب الحالة
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // البحث
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
            })->orWhereHas('order.customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $shipments = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $shipments
        ]);
    }

    /**
     * جلب شحنة معينة عبر API
     */
    public function getShipment(Shipment $shipment)
    {
        $shipment->load(['order', 'order.vendor', 'order.customer', 'trackingEvents']);

        return response()->json([
            'success' => true,
            'shipment' => $shipment
        ]);
    }

    /**
     * جلب مزودي الشحن عبر API
     */
    public function getProviders()
    {
        $providers = ShippingProvider::orderBy('priority', 'desc')->get();

        return response()->json([
            'success' => true,
            'providers' => $providers
        ]);
    }

    /**
     * جلب مزود شحن معين عبر API
     */
    public function getProvider(ShippingProvider $provider)
    {
        return response()->json([
            'success' => true,
            'provider' => $provider
        ]);
    }

    /**
     * جلب مناطق الشحن عبر API
     */
    public function getZones(Request $request)
    {
        $query = ShippingZone::with('provider');

        // التصفية حسب مزود الشحن
        if ($request->has('provider_id') && $request->provider_id !== '') {
            $query->where('provider_id', $request->provider_id);
        }

        $zones = $query->orderBy('providers.priority', 'desc')
            ->orderBy('zones.priority', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'zones' => $zones
        ]);
    }

    /**
     * جلب منطقة شحن معينة عبر API
     */
    public function getZone(ShippingZone $zone)
    {
        $zone->load('provider');

        return response()->json([
            'success' => true,
            'zone' => $zone
        ]);
    }

    /**
     * جلب أسعار الشحن عبر API
     */
    public function getRates(Request $request)
    {
        $query = ShippingRate::with(['zone', 'zone.provider']);

        // التصفية حسب المنطقة
        if ($request->has('zone_id') && $request->zone_id !== '') {
            $query->where('zone_id', $request->zone_id);
        }

        $rates = $query->orderBy('zones.priority', 'desc')
            ->orderBy('rates.priority', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'rates' => $rates
        ]);
    }

    /**
     * جلب سعر شحن معين عبر API
     */
    public function getRate(ShippingRate $rate)
    {
        $rate->load(['zone', 'zone.provider']);

        return response()->json([
            'success' => true,
            'rate' => $rate
        ]);
    }

    /**
     * إنشاء مزود شحن جديد
     */
    public function createProvider(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shipping_providers,code',
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'priority' => 'required|integer|min:0',
            'config' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $provider = ShippingProvider::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة مزود الشحن بنجاح',
                'provider' => $provider
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating shipping provider: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة مزود الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث مزود شحن
     */
    public function updateProvider(Request $request, ShippingProvider $provider)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shipping_providers,code,' . $provider->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'priority' => 'required|integer|min:0',
            'config' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $provider->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث مزود الشحن بنجاح',
                'provider' => $provider
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating shipping provider: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث مزود الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف مزود شحن
     */
    public function deleteProvider(ShippingProvider $provider)
    {
        try {
            DB::beginTransaction();

            $provider->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف مزود الشحن بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting shipping provider: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف مزود الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء منطقة شحن جديدة
     */
    public function createZone(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:shipping_providers,id',
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:2',
            'state' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'zip_from' => 'nullable|string',
            'zip_to' => 'nullable|string',
            'estimated_delivery_days' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'priority' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $zone = ShippingZone::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة منطقة الشحن بنجاح',
                'zone' => $zone
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating shipping zone: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة منطقة الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث منطقة شحن
     */
    public function updateZone(Request $request, ShippingZone $zone)
    {
        $request->validate([
            'provider_id' => 'required|exists:shipping_providers,id',
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:2',
            'state' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'zip_from' => 'nullable|string',
            'zip_to' => 'nullable|string',
            'estimated_delivery_days' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'priority' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $zone->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث منطقة الشحن بنجاح',
                'zone' => $zone
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating shipping zone: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث منطقة الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف منطقة شحن
     */
    public function deleteZone(ShippingZone $zone)
    {
        try {
            DB::beginTransaction();

            $zone->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف منطقة الشحن بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting shipping zone: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف منطقة الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء سعر شحن جديد
     */
    public function createRate(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:shipping_zones,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'priority' => 'required|integer|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'handling_fee' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $rate = ShippingRate::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة سعر الشحن بنجاح',
                'rate' => $rate
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating shipping rate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة سعر الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث سعر شحن
     */
    public function updateRate(Request $request, ShippingRate $rate)
    {
        $request->validate([
            'zone_id' => 'required|exists:shipping_zones,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'priority' => 'required|integer|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'handling_fee' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $rate->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث سعر الشحن بنجاح',
                'rate' => $rate
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating shipping rate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث سعر الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف سعر شحن
     */
    public function deleteRate(ShippingRate $rate)
    {
        try {
            DB::beginTransaction();

            $rate->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف سعر الشحن بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting shipping rate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف سعر الشحن',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}