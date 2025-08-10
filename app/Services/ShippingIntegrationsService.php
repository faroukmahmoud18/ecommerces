<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ShippingIntegrationsService
{
    /**
     * Create Aramex shipment
     * 
     * @param Order $order
     * @param ShippingProvider $provider
     * @param array $shipmentData
     * @return array
     */
    public function createAramexShipment(Order $order, ShippingProvider $provider, array $shipmentData)
    {
        try {
            $aramexConfig = $provider->config;

            // Prepare Aramex API request
            $requestData = [
                'ClientInfo' => [
                    'AccountNumber' => $aramexConfig['account_number'],
                    'AccountPin' => $aramexConfig['account_pin'],
                    'AccountEntity' => $aramexConfig['account_entity'] ?? 'SAU',
                    'AccountCountryCode' => $aramexConfig['account_country_code'] ?? 'SA',
                    'UserName' => $aramexConfig['username'],
                    'Password' => $aramexConfig['password'],
                    'Version' => 'v1.0'
                ],
                'ShipmentDetails' => [
                    'Reference1' => $order->order_number,
                    'Shipper' => [
                        'Reference1' => $order->vendor->id,
                        'AccountNumber' => $aramexConfig['account_number'],
                        'PartyAddress' => [
                            'Line1' => $order->vendor->address,
                            'City' => $order->vendor->city,
                            'StateOrProvinceCode' => $order->vendor->state,
                            'PostCode' => $order->vendor->postal_code,
                            'CountryCode' => substr($order->vendor->country, 0, 2),
                        ],
                        'Contact' => [
                            'PersonName' => $order->vendor->name,
                            'CompanyName' => $order->vendor->company_name,
                            'PhoneNumber1' => $order->vendor->phone,
                            'EmailAddress' => $order->vendor->email
                        ]
                    ],
                    'Consignee' => [
                        'Reference1' => $order->user_id,
                        'PartyAddress' => [
                            'Line1' => $order->customer_address,
                            'City' => $order->customer_city,
                            'StateOrProvinceCode' => $order->customer_state,
                            'PostCode' => $order->customer_postal_code,
                            'CountryCode' => substr($order->customer_country, 0, 2),
                        ],
                        'Contact' => [
                            'PersonName' => $order->customer_name,
                            'PhoneNumber1' => $order->customer_phone,
                            'EmailAddress' => $order->customer_email
                        ]
                    ],
                    'ShippingDateTime' => now()->format('Y-m-d\TH:i:s'),
                    'DueDate' => now()->addDays(1)->format('Y-m-d\TH:i:s'),
                    'Comments' => $shipmentData['notes'] ?? '',
                    'Details' => [
                        'ActualWeight' => [
                            'Value' => $order->total_weight,
                            'Unit' => 'KG'
                        ],
                        'ProductGroup' => 'EXP',
                        'ProductType' => 'PDX',
                        'PaymentType' => 'P',
                        'NumberOfPieces' => $order->items->sum('quantity'),
                        'DescriptionOfGoods' => 'E-commerce order',
                        'GoodsOriginCountry' => 'SA',
                        'CashOnDeliveryAmount' => [
                            'Value' => $order->payment_method === 'cod' ? $order->total_amount : 0,
                            'CurrencyCode' => 'SAR'
                        ]
                    ]
                ]
            ];

            // Call Aramex API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($provider->api_url . '/shipping/v1/shipments/create', $requestData);

            if (!$response->successful()) {
                throw new Exception('Aramex API Error: ' . $response->body());
            }

            $responseData = $response->json();

            return [
                'success' => true,
                'tracking_number' => $responseData['Shipments'][0]['ID'] ?? null,
                'carrier' => 'Aramex',
                'shipping_method' => 'Express',
                'label_url' => $responseData['Shipments'][0]['ShipmentLabel']['LabelURL'] ?? null,
                'provider_response' => $responseData,
                'message' => 'تم إنشاء شحنة Aramex بنجاح',
            ];
        } catch (Exception $e) {
            Log::error('Aramex Shipment Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء شحنة Aramex',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create DHL shipment
     * 
     * @param Order $order
     * @param ShippingProvider $provider
     * @param array $shipmentData
     * @return array
     */
    public function createDHLShipment(Order $order, ShippingProvider $provider, array $shipmentData)
    {
        try {
            $dhlConfig = $provider->config;

            // Prepare DHL API request
            $requestData = [
                'plannedShippingDateAndTime' => now()->format('Y-m-d\TH:i:s\Z'),
                'pickup' => [
                    'isRequested' => false
                ],
                'customerDetails' => [
                    'shipperDetails' => [
                        'postalAddress' => [
                            'postalCode' => $order->vendor->postal_code,
                            'cityName' => $order->vendor->city,
                            'countryCode' => substr($order->vendor->country, 0, 2),
                            'addressLine1' => $order->vendor->address,
                        ],
                        'contactInformation' => [
                            'email' => $order->vendor->email,
                            'phone' => $order->vendor->phone,
                            'companyName' => $order->vendor->company_name,
                            'fullName' => $order->vendor->name
                        ]
                    ],
                    'receiverDetails' => [
                        'postalAddress' => [
                            'postalCode' => $order->customer_postal_code,
                            'cityName' => $order->customer_city,
                            'countryCode' => substr($order->customer_country, 0, 2),
                            'addressLine1' => $order->customer_address,
                        ],
                        'contactInformation' => [
                            'email' => $order->customer_email,
                            'phone' => $order->customer_phone,
                            'fullName' => $order->customer_name
                        ]
                    ]
                ],
                'content' => [
                    'packages' => [
                        [
                            'weight' => $order->total_weight,
                            'dimensions' => [
                                'length' => 30,
                                'width' => 30,
                                'height' => 30
                            ],
                            'customerReferences' => [
                                [
                                    'value' => $order->order_number,
                                    'typeCode' => 'CU'
                                ]
                            ]
                        ]
                    ],
                    'isCustomsDeclarable' => false,
                    'description' => 'E-commerce order',
                    'incoterm' => 'DAP',
                    'unitOfMeasurement' => 'metric'
                ],
                'serviceType' => 'N',
                'valueAddedServices' => $order->payment_method === 'cod' ? [
                    [
                        'serviceType' => 'COD',
                        'serviceValue' => $order->total_amount,
                        'currency' => 'SAR'
                    ]
                ] : []
            ];

            // Call DHL API
            $response = Http::withBasicAuth($dhlConfig['username'], $dhlConfig['password'])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Message-Reference' => Str::uuid()->toString()
                ])
                ->post($provider->api_url . '/shipments', $requestData);

            if (!$response->successful()) {
                throw new Exception('DHL API Error: ' . $response->body());
            }

            $responseData = $response->json();

            return [
                'success' => true,
                'tracking_number' => $responseData['shipmentTrackingNumber'] ?? null,
                'carrier' => 'DHL',
                'shipping_method' => 'Express',
                'label_url' => $responseData['documents'][0]['url'] ?? null,
                'provider_response' => $responseData,
                'message' => 'تم إنشاء شحنة DHL بنجاح',
            ];
        } catch (Exception $e) {
            Log::error('DHL Shipment Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء شحنة DHL',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create SMSA shipment
     * 
     * @param Order $order
     * @param ShippingProvider $provider
     * @param array $shipmentData
     * @return array
     */
    public function createSMSAShipment(Order $order, ShippingProvider $provider, array $shipmentData)
    {
        try {
            $smsaConfig = $provider->config;

            // Prepare SMSA API request
            $requestData = [
                'passKey' => $provider->api_key,
                'refNo' => $order->order_number,
                'sentDate' => now()->format('Y-m-d'),
                'idNo' => '',
                'cName' => $order->customer_name,
                'cntry' => $order->customer_country,
                'cCity' => $order->customer_city,
                'cZip' => $order->customer_postal_code,
                'cPOBox' => '',
                'cMobile' => $order->customer_phone,
                'cTel1' => '',
                'cTel2' => '',
                'cAddr1' => $order->customer_address,
                'cAddr2' => '',
                'shipType' => 'DLV',
                'PCs' => $order->items->sum('quantity'),
                'cEmail' => $order->customer_email,
                'weight' => $order->total_weight,
                'itemDesc' => 'E-commerce order',
                'codeType' => $order->payment_method === 'cod' ? 'cod' : '',
                'codAmt' => $order->payment_method === 'cod' ? $order->total_amount : 0,
                'custVal' => $order->total_amount,
                'custCurr' => 'SAR',
                'insrAmt' => 0,
                'insrCurr' => 'SAR',
            ];

            // Call SMSA API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($provider->api_url . '/addShip', $requestData);

            if (!$response->successful() || empty($response->body())) {
                throw new Exception('SMSA API Error: ' . $response->body());
            }

            $responseData = $response->json();

            if (isset($responseData['hasErrors']) && $responseData['hasErrors']) {
                throw new Exception('SMSA Error: ' . ($responseData['errors'][0]['message'] ?? 'Unknown error'));
            }

            return [
                'success' => true,
                'tracking_number' => $responseData['sawb'] ?? null,
                'carrier' => 'SMSA',
                'shipping_method' => 'Express',
                'label_url' => null,
                'provider_response' => $responseData,
                'message' => 'تم إنشاء شحنة SMSA بنجاح',
            ];
        } catch (Exception $e) {
            Log::error('SMSA Shipment Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء شحنة SMSA',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track Aramex shipment
     *
     * @param string $trackingNumber
     * @param ShippingProvider $provider
     * @return array
     */
    public function trackAramexShipment($trackingNumber, ShippingProvider $provider)
    {
        try {
            $aramexConfig = $provider->config;

            $requestData = [
                'ClientInfo' => [
                    'AccountNumber' => $aramexConfig['account_number'],
                    'AccountPin' => $aramexConfig['account_pin'],
                    'AccountEntity' => $aramexConfig['account_entity'] ?? 'SAU',
                    'AccountCountryCode' => $aramexConfig['account_country_code'] ?? 'SA',
                    'UserName' => $aramexConfig['username'],
                    'Password' => $aramexConfig['password'],
                    'Version' => 'v1.0'
                ],
                'TrackingNumbers' => [$trackingNumber]
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($provider->api_url . '/tracking/v1/shipments/track', $requestData);

            if (!$response->successful()) {
                throw new Exception('Aramex API Error: ' . $response->body());
            }

            $responseData = $response->json();

            $events = [];
            if (isset($responseData['TrackingResults'][0]['Value']['TrackingResult']['Shipments'][0]['Events'])) {
                foreach ($responseData['TrackingResults'][0]['Value']['TrackingResult']['Shipments'][0]['Events'] as $event) {
                    $events[] = [
                        'event_time' => $event['UpdateDateTime'],
                        'event_status' => $event['UpdateCode'] ?? $event['UpdateDescription'],
                        'event_location' => $event['UpdateLocation'] ?? '',
                        'event_description' => $event['Comments'] ?? $event['UpdateDescription'],
                    ];
                }
            }

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'events' => $events,
                'provider_response' => $responseData
            ];
        } catch (Exception $e) {
            Log::error('Aramex Tracking Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تتبع الشحنة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track DHL shipment
     *
     * @param string $trackingNumber
     * @param ShippingProvider $provider
     * @return array
     */
    public function trackDHLShipment($trackingNumber, ShippingProvider $provider)
    {
        try {
            $dhlConfig = $provider->config;

            $response = Http::withBasicAuth($dhlConfig['username'], $dhlConfig['password'])
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get($provider->api_url . "/shipments/{$trackingNumber}/tracking");

            if (!$response->successful()) {
                throw new Exception('DHL API Error: ' . $response->body());
            }

            $responseData = $response->json();

            $events = [];
            if (isset($responseData['shipments'][0]['events'])) {
                foreach ($responseData['shipments'][0]['events'] as $event) {
                    $events[] = [
                        'event_time' => $event['timestamp'],
                        'event_status' => $event['status'],
                        'event_location' => $event['location']['address']['addressLocality'] ?? '',
                        'event_description' => $event['description'],
                    ];
                }
            }

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'events' => $events,
                'provider_response' => $responseData
            ];
        } catch (Exception $e) {
            Log::error('DHL Tracking Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تتبع الشحنة',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track SMSA shipment
     *
     * @param string $trackingNumber
     * @param ShippingProvider $provider
     * @return array
     */
    public function trackSMSAShipment($trackingNumber, ShippingProvider $provider)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->get($provider->api_url . '/tracking', [
                'passkey' => $provider->api_key,
                'awb' => $trackingNumber
            ]);

            if (!$response->successful()) {
                throw new Exception('SMSA API Error: ' . $response->body());
            }

            $responseData = $response->json();

            $events = [];
            if (isset($responseData['activities'])) {
                foreach ($responseData['activities'] as $event) {
                    $events[] = [
                        'event_time' => $event['date'] . ' ' . $event['time'],
                        'event_status' => $event['activity'],
                        'event_location' => $event['location'] ?? '',
                        'event_description' => $event['details'] ?? $event['activity'],
                    ];
                }
            }

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'events' => $events,
                'provider_response' => $responseData
            ];
        } catch (Exception $e) {
            Log::error('SMSA Tracking Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء تتبع الشحنة',
                'error' => $e->getMessage(),
            ];
        }
    }
}