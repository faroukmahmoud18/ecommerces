
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\ShippingRate;
use App\Models\ShippingProvider;
use App\Models\ShippingZone;
use App\Models\Address;
use App\Models\Vendor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class ShippingService
{
    /**
     * Create a shipment for an order.
     *
     * @param Order $order
     * @param array $shipmentData
     * @return array
     */
    public function createShipment(Order $order, array $shipmentData)
    {
        DB::beginTransaction();

        try {
            // Create shipment record
            $shipment = Shipment::create([
                'order_id' => $order->id,
                'vendor_id' => $order->vendor_id,
                'tracking_number' => $shipmentData['tracking_number'],
                'carrier' => $shipmentData['carrier'],
                'shipping_method' => $shipmentData['shipping_method'],
                'shipping_cost' => $shipmentData['shipping_cost'],
                'weight' => $shipmentData['weight'],
                'dimensions' => $shipmentData['dimensions'],
                'status' => 'pending',
                'notes' => $shipmentData['notes'] ?? null,
            ]);

            // Create tracking events
            if (!empty($shipmentData['tracking_events'])) {
                foreach ($shipmentData['tracking_events'] as $event) {
                    $shipment->trackingEvents()->create([
                        'event_time' => $event['event_time'],
                        'event_status' => $event['event_status'],
                        'event_location' => $event['event_location'],
                        'event_description' => $event['event_description'] ?? null,
                        'raw_data' => $event['raw_data'] ?? null,
                    ]);
                }
            }

            // Update order status
            $order->update([
                'status' => 'shipped',
                'shipment_id' => $shipment->id,
            ]);

            // Notify customer about shipment
            $order->user->notify(new \App\Notifications\OrderShipped($order, $shipment));

            DB::commit();

            return [
                'success' => true,
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'message' => 'تم إنشاء الشحنة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating shipment: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الشحنة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update shipment status.
     *
     * @param Shipment $shipment
     * @param string $status
     * @param array $trackingEvent
     * @return array
     */
    public function updateShipmentStatus(Shipment $shipment, string $status, array $trackingEvent = [])
    {
        DB::beginTransaction();

        try {
            // Update shipment status
            $shipment->update(['status' => $status]);

            // Create tracking event
            if (!empty($trackingEvent)) {
                $shipment->trackingEvents()->create([
                    'event_time' => $trackingEvent['event_time'] ?? now(),
                    'event_status' => $status,
                    'event_location' => $trackingEvent['event_location'] ?? null,
                    'event_description' => $trackingEvent['event_description'] ?? null,
                    'raw_data' => $trackingEvent['raw_data'] ?? null,
                ]);
            }

            // Update order status if delivered
            if ($status === 'delivered') {
                $shipment->order->update(['status' => 'delivered']);

                // Notify customer about delivery
                $shipment->order->user->notify(new \App\Notifications\OrderDelivered($shipment->order));
            }

            DB::commit();

            return [
                'success' => true,
                'shipment_id' => $shipment->id,
                'status' => $status,
                'message' => 'تم تحديث حالة الشحنة بنجاح',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating shipment status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الشحنة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get shipping rates for an order.
     *
     * @param Order $order
     * @param array $options
     * @return array
     */
    public function getShippingRates(Order $order, array $options = [])
    {
        try {
            // Calculate order weight and dimensions
            $totalWeight = 0;
            $totalDimensions = ['length' => 0, 'width' => 0, 'height' => 0];

            foreach ($order->items as $item) {
                $product = $item->product;

                // Calculate total weight
                $totalWeight += ($item->quantity * $product->weight);

                // Calculate total dimensions (assuming largest dimensions)
                $totalDimensions['length'] = max($totalDimensions['length'], $product->length);
                $totalDimensions['width'] = max($totalDimensions['width'], $product->width);
                $totalDimensions['height'] = max($totalDimensions['height'], $product->height);
            }

            // Get applicable shipping rates
            $shippingRates = ShippingRate::where('is_active', true)
                ->where(function($q) use ($options) {
                    // Filter by vendor if provided
                    if (!empty($options['vendor_id'])) {
                        $q->where('vendor_id', $options['vendor_id']);
                    }

                    // Filter by carrier if provided
                    if (!empty($options['carrier'])) {
                        $q->where('carrier', $options['carrier']);
                    }
                })
                ->where('min_weight', '<=', $totalWeight)
                ->where('max_weight', '>=', $totalWeight)
                ->where('min_length', '<=', $totalDimensions['length'])
                ->where('max_length', '>=', $totalDimensions['length'])
                ->where('min_width', '<=', $totalDimensions['width'])
                ->where('max_width', '>=', $totalDimensions['width'])
                ->where('min_height', '<=', $totalDimensions['height'])
                ->where('max_height', '>=', $totalDimensions['height'])
                ->orderBy('price')
                ->get();

            // If no rates found, get default rates
            if ($shippingRates->isEmpty()) {
                $shippingRates = ShippingRate::where('is_active', true)
                    ->where('is_default', true)
                    ->orderBy('price')
                    ->get();
            }

            // Format rates
            $formattedRates = [];
            foreach ($shippingRates as $rate) {
                $formattedRates[] = [
                    'id' => $rate->id,
                    'carrier' => $rate->carrier,
                    'service_name' => $rate->service_name,
                    'price' => $rate->price,
                    'estimated_delivery' => $rate->estimated_delivery,
                    'features' => $rate->features,
                ];
            }

            return [
                'success' => true,
                'rates' => $formattedRates,
                'total_weight' => $totalWeight,
                'total_dimensions' => $totalDimensions,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting shipping rates: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على أسعار الشحن',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track a shipment using tracking number.
     *
     * @param string $trackingNumber
     * @param string $carrier
     * @return array
     */
    public function trackShipment(string $trackingNumber, string $carrier)
    {
        try {
            // Try to find shipment in database first
            $shipment = Shipment::where('tracking_number', $trackingNumber)
                ->where('carrier', $carrier)
                ->first();

            if ($shipment && $shipment->trackingEvents->isNotEmpty()) {
                return [
                    'success' => true,
                    'shipment' => [
                        'tracking_number' => $shipment->tracking_number,
                        'carrier' => $shipment->carrier,
                        'status' => $shipment->status,
                        'events' => $shipment->trackingEvents->map(function($event) {
                            return [
                                'time' => $event->event_time->format('Y-m-d H:i:s'),
                                'status' => $event->event_status,
                                'location' => $event->event_location,
                                'description' => $event->event_description,
                            ];
                        })->toArray(),
                    ],
                ];
            }

            // If not found in database, try to track using carrier API
            $trackingData = $this->trackUsingCarrierAPI($trackingNumber, $carrier);

            if ($trackingData['success']) {
                return [
                    'success' => true,
                    'shipment' => $trackingData['shipment'],
                    'message' => 'تم تتبع الشحنة بنجاح',
                ];
            }

            return [
                'success' => false,
                'message' => 'لم يتم العثور على الشحة أو معلومات التتبع غير متوفرة',
                'error' => $trackingData['message'] ?? 'خطأ غير معروف',
            ];
        } catch (\Exception $e) {
            Log::error('Error tracking shipment: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تتبع الشحنة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track shipment using carrier API.
     *
     * @param string $trackingNumber
     * @param string $carrier
     * @return array
     */
    private function trackUsingCarrierAPI(string $trackingNumber, string $carrier)
    {
        try {
            // This is a simplified example. In a real implementation, you would use specific carrier APIs.
            $apiKey = config('services.shipping_api.key');

            switch ($carrier) {
                case 'aramex':
                    return $this->trackUsingAramexAPI($trackingNumber, $apiKey);

                case 'fedex':
                    return $this->trackUsingFedExAPI($trackingNumber, $apiKey);

                case 'ups':
                    return $this->trackUsingUPSAPI($trackingNumber, $apiKey);

                case 'dhl':
                    return $this->trackUsingDHLAPI($trackingNumber, $apiKey);

                default:
                    return [
                        'success' => false,
                        'message' => 'ناقل الشحن غير مدعوم',
                    ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تتبع الشحنة باستخدام API الناقل',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track shipment using Aramex API.
     *
     * @param string $trackingNumber
     * @param string $apiKey
     * @return array
     */
    private function trackUsingAramexAPI(string $trackingNumber, string $apiKey)
    {
        try {
            // This is a simplified example. In a real implementation, you would use Aramex PHP SDK.
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://aramex.shipping-api.com/track', [
                'tracking_number' => $trackingNumber,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    return [
                        'success' => true,
                        'shipment' => [
                            'tracking_number' => $data['tracking_number'],
                            'carrier' => 'aramex',
                            'status' => $data['status'],
                            'events' => collect($data['events'])->map(function($event) {
                                return [
                                    'time' => Carbon::parse($event['date'])->format('Y-m-d H:i:s'),
                                    'status' => $event['status'],
                                    'location' => $event['location'],
                                    'description' => $event['description'],
                                ];
                            })->toArray(),
                        ],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'فشل تتبع الشحنة باستخدام Aramex API',
                'error' => $response->json()['message'] ?? 'خطأ غير معروف',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تتبع الشحنة باستخدام Aramex API',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track shipment using FedEx API.
     *
     * @param string $trackingNumber
     * @param string $apiKey
     * @return array
     */
    private function trackUsingFedExAPI(string $trackingNumber, string $apiKey)
    {
        try {
            // This is a simplified example. In a real implementation, you would use FedEx PHP SDK.
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://fedex.shipping-api.com/track', [
                'tracking_number' => $trackingNumber,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] === true) {
                    return [
                        'success' => true,
                        'shipment' => [
                            'tracking_number' => $data['tracking_number'],
                            'carrier' => 'fedex',
                            'status' => $data['status'],
                            'events' => collect($data['events'])->map(function($event) {
                                return [
                                    'time' => Carbon::parse($event['timestamp'])->format('Y-m-d H:i:s'),
                                    'status' => $event['event_type'],
                                    'location' => $event['location'],
                                    'description' => $event['description'],
                                ];
                            })->toArray(),
                        ],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'فشل تتبع الشحنة باستخدام FedEx API',
                'error' => $response->json()['message'] ?? 'خطأ غير معروف',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تتبع الشحنة باستخدام FedEx API',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track shipment using UPS API.
     *
     * @param string $trackingNumber
     * @param string $apiKey
     * @return array
     */
    private function trackUsingUPSAPI(string $trackingNumber, string $apiKey)
    {
        try {
            // This is a simplified example. In a real implementation, you would use UPS PHP SDK.
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://onlinetools.ups.com/shipments/v1/track', [
                'tracking_number' => $trackingNumber,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['trackResponse']['shipment'][0]['package'][0]['trackingNumber'])) {
                    return [
                        'success' => true,
                        'shipment' => [
                            'tracking_number' => $data['trackResponse']['shipment'][0]['package'][0]['trackingNumber'],
                            'carrier' => 'ups',
                            'status' => $data['trackResponse']['shipment'][0]['package'][0]['activity'][0]['status']['description'],
                            'events' => collect($data['trackResponse']['shipment'][0]['package'][0]['activity'])->map(function($event) {
                                return [
                                    'time' => Carbon::parse($event['date'] . ' ' . $event['time'])->format('Y-m-d H:i:s'),
                                    'status' => $event['status']['description'],
                                    'location' => $event['location']['address']['city'] . ', ' . $event['location']['address']['countryCode'],
                                    'description' => $event['status']['description'],
                                ];
                            })->toArray(),
                        ],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'فشل تتبع الشحنة باستخدام UPS API',
                'error' => $response->json()['response']['errors'][0]['message'] ?? 'خطأ غير معروف',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تتبع الشحنة باستخدام UPS API',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track shipment using DHL API.
     *
     * @param string $trackingNumber
     * @param string $apiKey
     * @return array
     */
    private function trackUsingDHLAPI(string $trackingNumber, string $apiKey)
    {
        try {
            // This is a simplified example. In a real implementation, you would use DHL PHP SDK.
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.dhl.com/track/shipments', [
                'tracking_number' => $trackingNumber,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['shipments'][0]['status'])) {
                    return [
                        'success' => true,
                        'shipment' => [
                            'tracking_number' => $data['shipments'][0]['tracking_number'],
                            'carrier' => 'dhl',
                            'status' => $data['shipments'][0]['status'],
                            'events' => collect($data['shipments'][0]['events'])->map(function($event) {
                                return [
                                    'time' => Carbon::parse($event['timestamp'])->format('Y-m-d H:i:s'),
                                    'status' => $event['status'],
                                    'location' => $event['location']['address'] ?? null,
                                    'description' => $event['description'],
                                ];
                            })->toArray(),
                        ],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'فشل تتبع الشحنة باستخدام DHL API',
                'error' => $response->json()['message'] ?? 'خطأ غير معروف',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تتبع الشحنة باستخدام DHL API',
                'error' => $e->getMessage(),
            ];
        }
    }
}
