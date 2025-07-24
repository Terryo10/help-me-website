<?php

namespace App\Livewire\Gateways;

use App\Models\Donation;
use Livewire\Component;
use App\Models\Transaction;
use App\Services\EmailNotificationService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class StripeGateway extends Component
{
    public $donation_id;
    public $transaction_id;
    public $amount;
    public $site_url;
    public $email;
    public $cardNumber = '';
    public $expiryMonth = '';
    public $expiryYear = '';
    public $cvc = '';
    public $cardholderName = '';
    public $submitting = "false";
    public $submittingCheck = "false";
    public $paymentSent = "false";
    public $clientSecret;
    public $paymentIntentId;

    public function mount($donation_id)
    {
        $this->donation_id = $donation_id;
        $donation = Donation::findOrFail($donation_id);
        $transaction_latest_donation = Transaction::where('donation_id',  $donation_id)->latest()->first();
        $this->transaction_id = $transaction_latest_donation->id ?? null;
        $this->amount = $donation->amount;
        $this->site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $this->email = Auth::user()->email ?? $donation->email;
        $this->cardholderName = Auth::user()->name ?? $donation->name;
    }

    public function createPayment()
    {
        $this->validate([
            'email' => 'required|email',
            'cardNumber' => 'required|string|min:13|max:19',
            'expiryMonth' => 'required',
            'expiryYear' => 'required',
            'cvc' => 'required|digits_between:3,4',
            'cardholderName' => 'required|string|max:255',
        ]);

        $this->submitting = "true";
        $donation = Donation::findOrFail($this->donation_id);

        $new_trans = Transaction::updateOrCreate(
            ['donation_id' => $donation->id],
            [
                'transaction_id' => $this->generateRandomId(),
                'donation_id' => $this->donation_id ?? 1,
                'type' => 'donation',
                'amount' => $donation->amount,
                'currency' => 'USD',
                'status' => 'pending',
                'description' => "Donation payment for order #" . $donation->id
            ]
        );

        try {
            // Create PaymentIntent
            $paymentIntent = $this->createStripePaymentIntent($new_trans);

            if ($paymentIntent && isset($paymentIntent['id'])) {
                $new_trans->update([
                    'gateway_transaction_id' => $paymentIntent['id'],
                    'gateway_response' => $paymentIntent,
                    'status' => 'processing'
                ]);

                $this->paymentIntentId = $paymentIntent['id'];
                $this->clientSecret = $paymentIntent['client_secret'];

                // Process payment with card details
                $confirmResult = $this->confirmStripePayment($paymentIntent['id'], $paymentIntent['client_secret']);

                if ($confirmResult && isset($confirmResult['status'])) {
                    if ($confirmResult['status'] === 'succeeded') {
                        $new_trans->update([
                            'status' => 'completed',
                            'gateway_response' => $confirmResult,
                            'processed_at' => now()
                        ]);
                        $donation = \App\Models\Donation::findOrFail($new_trans->donation_id);
                        $donation->update(['status' => 'completed']);

                        $notificaionService = new EmailNotificationService();
                        $notificaionService->sendEmail("Payment Completed", "Someone donated to your campaign ID {$donation->campaign_id} ", $donation->campaign->user->email);



                        $this->submitting = "false";
                        return redirect()->to("/transaction/" . $new_trans->id)->with('message', 'Payment completed successfully!');
                    } else if ($confirmResult['status'] === 'requires_action') {
                        // Handle 3D Secure or other authentication
                        $this->paymentSent = "true";
                        session()->flash('message', 'Payment requires additional authentication. Please complete the verification.');
                    } else {
                        $this->submitting = "false";
                        session()->flash('error', 'Payment failed. Please check your card details and try again.');
                    }
                } else {
                    $this->submitting = "false";
                    session()->flash('error', 'Failed to process payment. Please try again.');
                }
            } else {
                $this->submitting = "false";
                session()->flash('error', 'Failed to create Stripe payment.');
            }
        } catch (Exception $error) {
            $this->submitting = "false";
            session()->flash('error', $error->getMessage());
        }
    }

    public function checkPayment()
    {
        $this->submittingCheck = "true";
        $this->paymentSent = "false";

        try {
            $transaction = Transaction::findOrFail($this->transaction_id);

            if ($transaction->gateway_transaction_id) {
                $paymentIntent = $this->getStripePaymentIntent($transaction->gateway_transaction_id);

                if ($paymentIntent && isset($paymentIntent['status'])) {
                    if ($paymentIntent['status'] === 'succeeded') {
                        $transaction->update([
                            'status' => 'completed',
                            'gateway_response' => $paymentIntent,
                            'processed_at' => now()
                        ]);
                        $donation = \App\Models\Donation::findOrFail($transaction->donation_id);
                        $donation->update(['status' => 'completed']);

                        $notificaionService = new EmailNotificationService();
                        $notificaionService->sendEmail("Payment Completed", "Someone donated to your campaign ID {$donation->campaign_id} ", $donation->campaign->user->email);


                        $this->submittingCheck = "false";
                        $this->submitting = "false";
                        $this->paymentSent = "false";

                        return redirect()->to("/transaction/" . $transaction->id)->with('message', 'Payment completed successfully!');
                    } else if ($paymentIntent['status'] === 'requires_payment_method') {
                        $this->submittingCheck = "false";
                        $this->submitting = "false";
                        $this->paymentSent = "false";
                        session()->flash('error', 'Payment failed. Please try with a different payment method.');
                    } else {
                        $this->submittingCheck = "false";
                        $this->submitting = "false";
                        $this->paymentSent = "false";
                        session()->flash('error', 'Payment is still processing. Please try again in a moment.');
                    }
                } else {
                    $this->submittingCheck = "false";
                    session()->flash('error', 'Could not retrieve Stripe payment status.');
                }
            } else {
                $this->submittingCheck = "false";
                session()->flash('error', 'No Stripe payment found for this transaction.');
            }
        } catch (Exception $error) {
            $this->submittingCheck = "false";
            session()->flash('error', $error->getMessage());
        }
    }

    private function createStripePaymentIntent($transaction)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('STRIPE_SECRET_KEY'),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post('https://api.stripe.com/v1/payment_intents', [
            'amount' => intval($transaction->amount * 100), // Convert to cents
            'currency' => strtolower($transaction->currency),
            'description' => $transaction->description,
            'metadata[transaction_id]' => $transaction->transaction_id,
            'metadata[order_id]' => $transaction->id,
            'metadata[user_id]' => Auth::id(),
            'receipt_email' => $this->email,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Stripe API Error: ' . $response->body());
    }

    private function confirmStripePayment($paymentIntentId, $clientSecret)
    {
        // Clean card number (remove spaces)
        $cardNumber = preg_replace('/\s+/', '', $this->cardNumber);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('STRIPE_SECRET_KEY'),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post("https://api.stripe.com/v1/payment_intents/{$paymentIntentId}/confirm", [
            'payment_method_data[type]' => 'card',
            'payment_method_data[card][number]' => $cardNumber,
            'payment_method_data[card][exp_month]' => $this->expiryMonth,
            'payment_method_data[card][exp_year]' => $this->expiryYear,
            'payment_method_data[card][cvc]' => $this->cvc,
            'payment_method_data[billing_details][name]' => $this->cardholderName,
            'payment_method_data[billing_details][email]' => $this->email,
            'return_url' => $this->site_url . '/stripe-return/' . $this->transaction_id,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Stripe Payment Confirmation Error: ' . $response->body());
    }

    private function getStripePaymentIntent($paymentIntentId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('STRIPE_SECRET_KEY'),
        ])->get("https://api.stripe.com/v1/payment_intents/{$paymentIntentId}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    private function generateRandomId()
    {
        return uniqid('stripe_txn_', true);
    }

    public function render()
    {
        return view('livewire.gateways.stripe-gateway')->extends('app');
    }
}
