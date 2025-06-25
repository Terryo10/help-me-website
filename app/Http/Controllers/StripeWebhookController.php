<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            // Verify webhook signature if secret is configured
            if ($endpointSecret && $sigHeader) {
                if (!$this->verifySignature($payload, $sigHeader, $endpointSecret)) {
                    Log::error('Stripe webhook signature verification failed');
                    return response('Invalid signature', 400);
                }
            }

            $event = json_decode($payload, true);

            if (!$event || !isset($event['type'])) {
                return response('Invalid webhook payload', 400);
            }

            Log::info('Stripe Webhook received', ['event_type' => $event['type'], 'event_id' => $event['id'] ?? null]);

            // Handle the event
            switch ($event['type']) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event['data']['object']);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event['data']['object']);
                    break;

                case 'payment_intent.requires_action':
                    $this->handlePaymentIntentRequiresAction($event['data']['object']);
                    break;

                case 'payment_intent.canceled':
                    $this->handlePaymentIntentCanceled($event['data']['object']);
                    break;

                case 'charge.succeeded':
                    $this->handleChargeSucceeded($event['data']['object']);
                    break;

                case 'charge.failed':
                    $this->handleChargeFailed($event['data']['object']);
                    break;

                default:
                    Log::info('Unhandled Stripe webhook event type: ' . $event['type']);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage(), [
                'payload' => $payload,
                'trace' => $e->getTraceAsString()
            ]);

            return response('Webhook processing failed', 500);
        }
    }

    private function verifySignature($payload, $sigHeader, $secret)
    {
        $elements = explode(',', $sigHeader);
        $signature = null;
        $timestamp = null;

        foreach ($elements as $element) {
            $item = explode('=', $element, 2);
            if ($item[0] === 'v1') {
                $signature = $item[1];
            } elseif ($item[0] === 't') {
                $timestamp = $item[1];
            }
        }

        if (!$signature || !$timestamp) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        $paymentIntentId = $paymentIntent['id'];

        $transaction = Transaction::where('gateway_transaction_id', $paymentIntentId)->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'completed',
                'processed_at' => now(),
                'gateway_response' => $paymentIntent
            ]);

            Log::info('Stripe payment intent succeeded', [
                'transaction_id' => $transaction->id,
                'payment_intent_id' => $paymentIntentId,
                'amount' => ($paymentIntent['amount_received'] ?? $paymentIntent['amount']) / 100
            ]);
        } else {
            Log::warning('Stripe payment intent succeeded but no matching transaction found', [
                'payment_intent_id' => $paymentIntentId
            ]);
        }
    }

    private function handlePaymentIntentFailed($paymentIntent)
    {
        $paymentIntentId = $paymentIntent['id'];

        $transaction = Transaction::where('gateway_transaction_id', $paymentIntentId)->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'failed',
                'gateway_response' => $paymentIntent
            ]);

            Log::info('Stripe payment intent failed', [
                'transaction_id' => $transaction->id,
                'payment_intent_id' => $paymentIntentId,
                'failure_reason' => $paymentIntent['last_payment_error']['message'] ?? 'Unknown'
            ]);
        }
    }

    private function handlePaymentIntentRequiresAction($paymentIntent)
    {
        $paymentIntentId = $paymentIntent['id'];

        $transaction = Transaction::where('gateway_transaction_id', $paymentIntentId)->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'processing',
                'gateway_response' => array_merge($transaction->gateway_response ?? [], $paymentIntent)
            ]);

            Log::info('Stripe payment intent requires action', [
                'transaction_id' => $transaction->id,
                'payment_intent_id' => $paymentIntentId
            ]);
        }
    }

    private function handlePaymentIntentCanceled($paymentIntent)
    {
        $paymentIntentId = $paymentIntent['id'];

        $transaction = Transaction::where('gateway_transaction_id', $paymentIntentId)->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'cancelled',
                'gateway_response' => $paymentIntent
            ]);

            Log::info('Stripe payment intent canceled', [
                'transaction_id' => $transaction->id,
                'payment_intent_id' => $paymentIntentId
            ]);
        }
    }

    private function handleChargeSucceeded($charge)
    {
        $paymentIntentId = $charge['payment_intent'];

        if ($paymentIntentId) {
            $transaction = Transaction::where('gateway_transaction_id', $paymentIntentId)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'gateway_response' => array_merge($transaction->gateway_response ?? [], ['charge' => $charge])
                ]);

                Log::info('Stripe charge succeeded', [
                    'transaction_id' => $transaction->id,
                    'charge_id' => $charge['id'],
                    'amount' => $charge['amount'] / 100
                ]);
            }
        }
    }

    private function handleChargeFailed($charge)
    {
        $paymentIntentId = $charge['payment_intent'];

        if ($paymentIntentId) {
            $transaction = Transaction::where('gateway_transaction_id', $paymentIntentId)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'failed',
                    'gateway_response' => array_merge($transaction->gateway_response ?? [], ['charge' => $charge])
                ]);

                Log::info('Stripe charge failed', [
                    'transaction_id' => $transaction->id,
                    'charge_id' => $charge['id'],
                    'failure_reason' => $charge['failure_message'] ?? 'Unknown'
                ]);
            }
        }
    }
}
