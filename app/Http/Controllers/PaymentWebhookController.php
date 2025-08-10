<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle Stripe webhook
     */
    public function stripe(Request $request)
    {
        try {
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');

            // Verify webhook signature
            if (!$this->paymentService->verifyWebhook('stripe', ['Stripe-Signature' => $sigHeader], $payload)) {
                Log::warning('Invalid Stripe webhook signature');
                return response('Invalid signature', 400);
            }

            $event = json_decode($payload, true);

            Log::info('Stripe webhook received', ['type' => $event['type']]);

            // Handle different event types
            switch ($event['type']) {
                case 'payment_intent.succeeded':
                    $this->handleStripePaymentSuccess($event['data']['object']);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handleStripePaymentFailed($event['data']['object']);
                    break;

                case 'charge.dispute.created':
                    $this->handleStripeDispute($event['data']['object']);
                    break;

                default:
                    Log::info('Unhandled Stripe webhook event', ['type' => $event['type']]);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    /**
     * Handle PayPal webhook
     */
    public function paypal(Request $request)
    {
        try {
            $payload = $request->getContent();
            $headers = $request->headers->all();

            // Verify webhook signature
            if (!$this->paymentService->verifyWebhook('paypal', $headers, $payload)) {
                Log::warning('Invalid PayPal webhook signature');
                return response('Invalid signature', 400);
            }

            $event = json_decode($payload, true);

            Log::info('PayPal webhook received', ['event_type' => $event['event_type']]);

            // Handle different event types
            switch ($event['event_type']) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePayPalPaymentSuccess($event['resource']);
                    break;

                case 'PAYMENT.CAPTURE.DENIED':
                    $this->handlePayPalPaymentFailed($event['resource']);
                    break;

                default:
                    Log::info('Unhandled PayPal webhook event', ['type' => $event['event_type']]);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('PayPal webhook error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    /**
     * Handle Fawry webhook
     */
    public function fawry(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('Fawry webhook received', $payload);

            // Verify signature
            $expectedSignature = hash('sha256', 
                $payload['merchantRefNumber'] . 
                $payload['orderAmount'] . 
                config('services.fawry.security_key')
            );

            if ($payload['signature'] !== $expectedSignature) {
                Log::warning('Invalid Fawry webhook signature');
                return response('Invalid signature', 400);
            }

            // Handle payment status
            switch ($payload['orderStatus']) {
                case 'PAID':
                    $this->handleFawryPaymentSuccess($payload);
                    break;

                case 'FAILED':
                case 'CANCELLED':
                    $this->handleFawryPaymentFailed($payload);
                    break;

                default:
                    Log::info('Unhandled Fawry status', ['status' => $payload['orderStatus']]);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Fawry webhook error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    /**
     * Handle Stripe payment success
     */
    private function handleStripePaymentSuccess($paymentIntent)
    {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;

        if ($orderId) {
            $this->updatePaymentStatus($orderId, 'stripe', $paymentIntent['id'], 'paid', $paymentIntent);
        }
    }

    /**
     * Handle Stripe payment failure
     */
    private function handleStripePaymentFailed($paymentIntent)
    {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;

        if ($orderId) {
            $this->updatePaymentStatus($orderId, 'stripe', $paymentIntent['id'], 'failed', $paymentIntent);
        }
    }

    /**
     * Handle Stripe dispute
     */
    private function handleStripeDispute($dispute)
    {
        // Handle chargeback/dispute logic
        Log::warning('Stripe dispute created', ['dispute_id' => $dispute['id']]);

        // You might want to automatically create a case or notify admin
        // event(new \App\Events\DisputeCreated($dispute));
    }

    /**
     * Handle PayPal payment success
     */
    private function handlePayPalPaymentSuccess($capture)
    {
        $orderId = $capture['custom_id'] ?? null;

        if ($orderId) {
            $this->updatePaymentStatus($orderId, 'paypal', $capture['id'], 'paid', $capture);
        }
    }

    /**
     * Handle PayPal payment failure
     */
    private function handlePayPalPaymentFailed($capture)
    {
        $orderId = $capture['custom_id'] ?? null;

        if ($orderId) {
            $this->updatePaymentStatus($orderId, 'paypal', $capture['id'], 'failed', $capture);
        }
    }

    /**
     * Handle Fawry payment success
     */
    private function handleFawryPaymentSuccess($payload)
    {
        $orderNumber = $payload['merchantRefNumber'];
        $order = \App\Models\Order::where('order_number', $orderNumber)->first();

        if ($order) {
            $this->updatePaymentStatus($order->id, 'fawry', $payload['fawryRefNumber'], 'paid', $payload);
        }
    }

    /**
     * Handle Fawry payment failure
     */
    private function handleFawryPaymentFailed($payload)
    {
        $orderNumber = $payload['merchantRefNumber'];
        $order = \App\Models\Order::where('order_number', $orderNumber)->first();

        if ($order) {
            $this->updatePaymentStatus($order->id, 'fawry', $payload['fawryRefNumber'] ?? null, 'failed', $payload);
        }
    }

    /**
     * Update payment status
     */
    private function updatePaymentStatus($orderId, $gateway, $transactionId, $status, $gatewayResponse)
    {
        try {
            $order = \App\Models\Order::find($orderId);

            if (!$order) {
                Log::error('Order not found for webhook', ['order_id' => $orderId]);
                return;
            }

            $payment = \App\Models\Payment::where('order_id', $orderId)
                ->where('gateway', $gateway)
                ->first();

            if (!$payment) {
                Log::error('Payment not found for webhook', [
                    'order_id' => $orderId,
                    'gateway' => $gateway
                ]);
                return;
            }

            // Update payment
            $payment->update([
                'payment_status' => $status,
                'transaction_id' => $transactionId,
                'gateway_response' => $gatewayResponse,
                'paid_at' => $status === 'paid' ? now() : null,
            ]);

            // Update order
            $order->update([
                'payment_status' => $status,
                'status' => $status === 'paid' ? 'confirmed' : ($status === 'failed' ? 'cancelled' : $order->status),
            ]);

            // Trigger events
            if ($status === 'paid') {
                event(new \App\Events\PaymentCompleted($payment));
                event(new \App\Events\OrderPaid($order));
            } elseif ($status === 'failed') {
                event(new \App\Events\PaymentFailed($payment));
            }

            Log::info('Payment status updated via webhook', [
                'order_id' => $orderId,
                'gateway' => $gateway,
                'status' => $status,
                'transaction_id' => $transactionId
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating payment status from webhook: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'gateway' => $gateway,
                'transaction_id' => $transactionId
            ]);
        }
    }
}