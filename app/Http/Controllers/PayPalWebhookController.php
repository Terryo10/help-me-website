<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('PayPal Webhook received', $payload);

            $eventType = $payload['event_type'] ?? null;
            $resource = $payload['resource'] ?? null;

            if (!$eventType || !$resource) {
                return response('Invalid webhook payload', 400);
            }

            switch ($eventType) {
                case 'CHECKOUT.ORDER.APPROVED':
                    $this->handleOrderApproved($resource);
                    break;

                case 'CHECKOUT.ORDER.COMPLETED':
                    $this->handleOrderCompleted($resource);
                    break;

                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handleCaptureCompleted($resource);
                    break;

                case 'PAYMENT.CAPTURE.DENIED':
                case 'PAYMENT.CAPTURE.FAILED':
                    $this->handleCaptureFailed($resource);
                    break;

                default:
                    Log::info('Unhandled PayPal webhook event type: ' . $eventType);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('PayPal webhook error: ' . $e->getMessage(), [
                'payload' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('Webhook processing failed', 500);
        }
    }

    private function handleOrderApproved($resource)
    {
        $orderId = $resource['id'] ?? null;

        if ($orderId) {
            $transaction = Transaction::where('gateway_transaction_id', $orderId)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'processing',
                    'gateway_response' => array_merge($transaction->gateway_response ?? [], $resource)
                ]);

                Log::info('PayPal order approved', ['transaction_id' => $transaction->id, 'paypal_order_id' => $orderId]);
            }
        }
    }

    private function handleOrderCompleted($resource)
    {
        $orderId = $resource['id'] ?? null;

        if ($orderId) {
            $transaction = Transaction::where('gateway_transaction_id', $orderId)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'gateway_response' => array_merge($transaction->gateway_response ?? [], $resource)
                ]);

                Log::info('PayPal order completed', ['transaction_id' => $transaction->id, 'paypal_order_id' => $orderId]);
            }
        }
    }

    private function handleCaptureCompleted($resource)
    {
        // Get order ID from the resource
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        if ($orderId) {
            $transaction = Transaction::where('gateway_transaction_id', $orderId)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'gateway_response' => array_merge($transaction->gateway_response ?? [], $resource)
                ]);

                Log::info('PayPal capture completed', ['transaction_id' => $transaction->id, 'paypal_order_id' => $orderId]);
            }
        }
    }

    private function handleCaptureFailed($resource)
    {
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        if ($orderId) {
            $transaction = Transaction::where('gateway_transaction_id', $orderId)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'failed',
                    'gateway_response' => array_merge($transaction->gateway_response ?? [], $resource)
                ]);

                Log::info('PayPal capture failed', ['transaction_id' => $transaction->id, 'paypal_order_id' => $orderId]);
            }
        }
    }
}
