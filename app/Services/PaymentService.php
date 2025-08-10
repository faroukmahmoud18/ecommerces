<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\VendorPayout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Process payment for an order.
     *
     * @param Order $order
     * @param array $paymentData
     * @return array
     */
    public function processPayment(Order $order, array $paymentData)
    {
        DB::beginTransaction();

        try {
            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'vendor_id' => $order->vendor_id,
                'amount' => $order->total_amount,
                'payment_method' => $paymentData['payment_method'],
                'payment_status' => 'pending',
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'gateway_response' => $paymentData['gateway_response'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
            ]);

            // Process payment based on payment method
            switch ($paymentData['payment_method']) {
                case 'stripe':
                    $result = $this->processStripePayment($order, $paymentData);
                    break;

                case 'paypal':
                    $result = $this->processPayPalPayment($order, $paymentData);
                    break;

                case 'fawry':
                    $result = $this->processFawryPayment($order, $paymentData);
                    break;

                default:
                    $result = [
                        'success' => false,
                        'message' => 'طريقة دفع غير مدعومة'
                    ];
            }

            // Update payment status based on result
            if ($result['success']) {
                $payment->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $result['transaction_id'] ?? $payment->transaction_id,
                    'gateway_response' => $result['gateway_response'] ?? $payment->gateway_response,
                ]);

                // Update order payment status
                $order->update(['payment_status' => 'paid']);

                // Update vendor wallet balance
                $order->vendor->increment('wallet_balance', $order->total_amount);

                // Create vendor payout record if needed
                $this->createVendorPayout($order);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'تمت عملية الدفع بنجاح',
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                ];
            } else {
                $payment->update([
                    'payment_status' => 'failed',
                    'gateway_response' => $result['gateway_response'] ?? null,
                ]);

                DB::commit();

                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'فشلت عملية الدفع',
                    'payment_id' => $payment->id,
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment processing failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الدفع',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process Stripe payment.
     *
     * @param Order $order
     * @param array $paymentData
     * @return array
     */
    private function processStripePayment(Order $order, array $paymentData)
    {
        try {
            // Stripe API integration
            // This is a simplified example. In a real implementation, you would use Stripe PHP SDK.

            $stripeSecret = config('services.stripe.secret');
            $stripeApiKey = config('services.stripe.key');

            // Create payment intent with Stripe
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $stripeSecret,
                'Content-Type' => 'application/json',
            ])->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => $order->total_amount * 100, // Convert to cents
                'currency' => 'sar', // Saudi Riyal
                'payment_method_types' => ['card'],
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);

            if ($response->successful()) {
                $paymentIntent = $response->json();

                // Confirm the payment intent
                $confirmResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $stripeSecret,
                    'Content-Type' => 'application/json',
                ])->post("https://api.stripe.com/v1/payment_intents/{$paymentIntent['id']}/confirm", [
                    'payment_method' => $paymentData['payment_method_id'],
                ]);

                if ($confirmResponse->successful()) {
                    $confirmedPayment = $confirmResponse->json();

                    if ($confirmedPayment['status'] === 'succeeded') {
                        return [
                            'success' => true,
                            'transaction_id' => $confirmedPayment['id'],
                            'gateway_response' => $confirmedPayment,
                        ];
                    }
                }
            }

            return [
                'success' => false,
                'message' => 'فشلت عملية الدفع مع Stripe',
                'gateway_response' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الدفع مع Stripe',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process PayPal payment.
     *
     * @param Order $order
     * @param array $paymentData
     * @return array
     */
    private function processPayPalPayment(Order $order, array $paymentData)
    {
        try {
            // PayPal API integration
            // This is a simplified example. In a real implementation, you would use PayPal PHP SDK.

            $clientId = config('services.paypal.client_id');
            $clientSecret = config('services.paypal.client_secret');

            // Get PayPal access token
            $tokenResponse = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$tokenResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'فشل الحصول على رمز الوصول من PayPal',
                    'gateway_response' => $tokenResponse->json(),
                ];
            }

            $accessToken = $tokenResponse->json()['access_token'];

            // Create PayPal order
            $orderResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => uniqid(),
            ])->post('https://api-m.sandbox.paypal.com/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $order->id,
                        'amount' => [
                            'currency_code' => 'SAR',
                            'value' => $order->total_amount,
                        ],
                    ],
                ],
            ]);

            if (!$orderResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'فشل إنشاء طلب PayPal',
                    'gateway_response' => $orderResponse->json(),
                ];
            }

            $paypalOrder = $orderResponse->json();

            // Capture PayPal payment
            $captureResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://api-m.sandbox.paypal.com/v2/checkout/orders/{$paypalOrder['id']}/capture");

            if ($captureResponse->successful()) {
                $captureResult = $captureResponse->json();

                if ($captureResult['status'] === 'COMPLETED') {
                    return [
                        'success' => true,
                        'transaction_id' => $captureResult['purchase_units'][0]['payments']['captures'][0]['id'],
                        'gateway_response' => $captureResult,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'فشلت عملية الدفع مع PayPal',
                'gateway_response' => $captureResponse->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الدفع مع PayPal',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process Fawry payment.
     *
     * @param Order $order
     * @param array $paymentData
     * @return array
     */
    private function processFawryPayment(Order $order, array $paymentData)
    {
        try {
            // Fawry API integration
            // This is a simplified example. In a real implementation, you would use Fawry PHP SDK.

            $merchantCode = config('services.fawry.merchant_code');
            $securityKey = config('services.fawry.security_key');
            $integrationId = config('services.fawry.integration_id');

            // Create Fawy payment request
            $paymentRequest = [
                'merchantCode' => $merchantCode,
                'merchantRefNum' => $order->order_number,
                'customerName' => $order->customer_name,
                'customerEmail' => $order->customer_email,
                'customerMobile' => $order->customer_phone,
                'amount' => $order->total_amount,
                'currency' => 'SAR',
                'description' => 'Payment for order ' . $order->order_number,
                'chargeItems' => [
                    [
                        'itemId' => 'order_' . $order->id,
                        'description' => 'Order items',
                        'price' => $order->total_amount,
                        'quantity' => 1,
                    ],
                ],
                'signature' => hash('sha256', $merchantCode . $order->order_number . $order->total_amount . $securityKey),
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://atfawry.fawrystaging.com/api/charges', $paymentRequest);

            if ($response->successful()) {
                $fawryResponse = $response->json();

                if ($fawryResponse['statusCode'] === '2000') {
                    return [
                        'success' => true,
                        'transaction_id' => $fawryResponse['billNumber'],
                        'gateway_response' => $fawryResponse,
                        'payment_url' => $fawryResponse['paymentURL'],
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $fawryResponse['statusMessage'] ?? 'فشلت عملية الدفع مع فوري',
                        'gateway_response' => $fawryResponse,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'فشلت عملية الدفع مع فوري',
                'gateway_response' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الدفع مع فوري',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create vendor payout record.
     *
     * @param Order $order
     * @return void
     */
    private function createVendorPayout(Order $order)
    {
        // Calculate vendor's share (after commission)
        $vendorShare = $order->total_amount - ($order->total_amount * ($order->vendor->commission_rate / 100));

        VendorPayout::create([
            'vendor_id' => $order->vendor_id,
            'order_id' => $order->id,
            'amount' => $vendorShare,
            'fee' => 0, // Could be calculated based on payment method
            'net_amount' => $vendorShare,
            'payment_method' => $order->payment_method,
            'status' => 'pending',
            'notes' => 'Auto-created payout for order ' . $order->order_number,
        ]);
    }

    /**
     * Refund payment.
     *
     * @param Payment $payment
     * @param float $amount
     * @param string $reason
     * @return array
     */
    public function refundPayment(Payment $payment, float $amount, string $reason = '')
    {
        DB::beginTransaction();

        try {
            // Process refund based on payment method
            switch ($payment->payment_method) {
                case 'stripe':
                    $result = $this->processStripeRefund($payment, $amount, $reason);
                    break;

                case 'paypal':
                    $result = $this->processPayPalRefund($payment, $amount, $reason);
                    break;

                case 'fawry':
                    $result = $this->processFawryRefund($payment, $amount, $reason);
                    break;

                default:
                    $result = [
                        'success' => false,
                        'message' => 'طريقة دفع غير مدعومة لعملية الإرجاع'
                    ];
            }

            // Update payment status based on result
            if ($result['success']) {
                if ($amount >= $payment->amount) {
                    $payment->update([
                        'payment_status' => 'refunded',
                        'gateway_response' => $result['gateway_response'] ?? $payment->gateway_response,
                    ]);
                } else {
                    $payment->update([
                        'payment_status' => 'partially_refunded',
                        'gateway_response' => $result['gateway_response'] ?? $payment->gateway_response,
                    ]);
                }

                // Update vendor wallet balance
                $payment->vendor->decrement('wallet_balance', $amount);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'تمت عملية الإرجاع بنجاح',
                    'refund_id' => $result['refund_id'] ?? null,
                    'transaction_id' => $result['transaction_id'] ?? null,
                ];
            } else {
                DB::commit();

                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'فشلت عملية الإرجاع',
                    'error' => $result['error'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment refund failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الإرجاع',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process Stripe refund.
     *
     * @param Payment $payment
     * @param float $amount
     * @param string $reason
     * @return array
     */
    private function processStripeRefund(Payment $payment, float $amount, string $reason)
    {
        try {
            $stripeSecret = config('services.stripe.secret');

            // Create refund with Stripe
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $stripeSecret,
                'Content-Type' => 'application/json',
            ])->post("https://api.stripe.com/v1/refunds", [
                'payment_intent' => $payment->transaction_id,
                'amount' => $amount * 100, // Convert to cents
                'reason' => $reason ? 'requested_by_customer' : 'fraudulent',
            ]);

            if ($response->successful()) {
                $refund = $response->json();

                return [
                    'success' => true,
                    'refund_id' => $refund['id'],
                    'transaction_id' => $refund['id'],
                    'gateway_response' => $refund,
                ];
            }

            return [
                'success' => false,
                'message' => 'فشلت عملية الإرجاع مع Stripe',
                'gateway_response' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الإرجاع مع Stripe',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process PayPal refund.
     *
     * @param Payment $payment
     * @param float $amount
     * @param string $reason
     * @return array
     */
    private function processPayPalRefund(Payment $payment, float $amount, string $reason)
    {
        try {
            $clientId = config('services.paypal.client_id');
            $clientSecret = config('services.paypal.client_secret');

            // Get PayPal access token
            $tokenResponse = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$tokenResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'فشل الحصول على رمز الوصول من PayPal',
                    'gateway_response' => $tokenResponse->json(),
                ];
            }

            $accessToken = $tokenResponse->json()['access_token'];

            // Create PayPal refund
            $refundResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://api-m.sandbox.paypal.com/v2/payments/captures/{$payment->transaction_id}/refund", [
                'amount' => [
                    'value' => $amount,
                    'currency_code' => 'SAR',
                ],
                'note_to_payer' => $reason,
                'invoice_id' => 'refund_' . $payment->id,
            ]);

            if ($refundResponse->successful()) {
                $refund = $refundResponse->json();

                return [
                    'success' => true,
                    'refund_id' => $refund['id'],
                    'transaction_id' => $refund['id'],
                    'gateway_response' => $refund,
                ];
            }

            return [
                'success' => false,
                'message' => 'فشلت عملية الإرجاع مع PayPal',
                'gateway_response' => $refundResponse->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الإرجاع مع PayPal',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process Fawry refund.
     *
     * @param Payment $payment
     * @param float $amount
     * @param string $reason
     * @return array
     */
    private function processFawryRefund(Payment $payment, float $amount, string $reason)
    {
        try {
            $merchantCode = config('services.fawry.merchant_code');
            $securityKey = config('services.fawry.security_key');

            // Create Fawry refund request
            $refundRequest = [
                'merchantCode' => $merchantCode,
                'referenceNumber' => $payment->transaction_id,
                'refundAmount' => $amount,
                'reason' => $reason,
                'signature' => hash('sha256', $merchantCode . $payment->transaction_id . $amount . $securityKey),
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://atfawry.fawrystaging.com/api/voidRefund', $refundRequest);

            if ($response->successful()) {
                $refundResponse = $response->json();

                if ($refundResponse['statusCode'] === '2000') {
                    return [
                        'success' => true,
                        'refund_id' => $refundResponse['refundNumber'],
                        'transaction_id' => $refundResponse['refundNumber'],
                        'gateway_response' => $refundResponse,
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $refundResponse['statusMessage'] ?? 'فشلت عملية الإرجاع مع فوري',
                        'gateway_response' => $refundResponse,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'فشلت عملية الإرجاع مع فوري',
                'gateway_response' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الإرجاع مع فوري',
                'error' => $e->getMessage(),
            ];
        }
    }
}
