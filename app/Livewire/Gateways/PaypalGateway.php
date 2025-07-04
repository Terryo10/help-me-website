<?php

namespace App\Livewire\Gateways;

use App\Models\Donation;
use Livewire\Component;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PaypalGateway extends Component
{
    public $donation_id;
    public $transaction_id;
    public $amount;
    public $site_url;
    public $email;
    public $submitting = "false";
    public $submittingCheck = "false";
    public $paymentSent = "false";
    public $paymentUrl;

    public function mount($donation_id)
    {
        $this->donation_id = $donation_id;
        $donation = Donation::findOrFail($donation_id);
        $transaction_latest_donation = Transaction::where('donation_id',  $donation_id)->latest()->first();
        $this->transaction_id = $transaction_latest_donation->id;
        $this->amount = $donation->amount;
        $this->site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $this->email = Auth::user()->email;
    }

    public function createPayment()
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        $this->submitting = "true";
        $donation = Donation::findOrFail($this->donation_id);

        $new_trans = Transaction::updateOrCreate(
            ['donation_id' => $donation->id],
            [
                'donation_random_id' => $this->generateRandomId(),
                'donation_id' => $donation->id ?? 1,
                'type' => 'donation',
                'amount' => $donation->amount,
                'currency' => 'USD',
                'status' => 'pending',
                'description' => "Donation payment for transaction #" . $donation->id
            ]
        );

        try {
            $paypalTransaction = $this->createPayPalOrder($new_trans);

            if ($paypalTransaction && isset($paypalTransaction['id'])) {
                $new_trans->update([
                    'gateway_donation_id' => $paypalTransaction['id'],
                    'gateway_response' => $paypalTransaction,
                    'status' => 'processing'
                ]);

                // Get approval URL from PayPal response
                $approvalUrl = null;
                if (isset($paypalTransaction['links'])) {
                    foreach ($paypalTransaction['links'] as $link) {
                        if ($link['rel'] === 'approve') {
                            $approvalUrl = $link['href'];
                            break;
                        }
                    }
                }

                if ($approvalUrl) {
                    $this->paymentUrl = $approvalUrl;
                    $this->paymentSent = "true";
                    session()->flash('message', 'Payment created successfully! Please complete payment on PayPal.');
                } else {
                    $this->submitting = "false";
                    session()->flash('error', 'Could not get PayPal approval URL.');
                }
            } else {
                $this->submitting = "false";
                session()->flash('error', 'Failed to create PayPal payment.');
            }
        } catch (Exception $error) {
            $this->submitting = "false";
            session()->flash('error', $error->getMessage());
        }
    }

    public function redirectToPayPal()
    {
        if ($this->paymentUrl) {
            return redirect()->away($this->paymentUrl);
        }
    }

    public function checkPayment()
    {
        $this->submittingCheck = "true";
        $this->paymentSent = "false";

        try {
            $transaction = Transaction::findOrFail($this->transaction_id);

            if ($transaction->gateway_donation_id) {
                $paypalTransaction = $this->getPayPalOrder($transaction->gateway_donation_id);

                if ($paypalTransaction && isset($paypalTransaction['status'])) {
                    if ($paypalTransaction['status'] === 'COMPLETED') {
                        $transaction->update([
                            'status' => 'completed',
                            'gateway_response' => $paypalTransaction,
                            'processed_at' => now()
                        ]);

                        $donation = \App\Models\Donation::findOrFail($transaction->donation_id);
                        $donation->update(['status' => 'completed']);

                        $this->submittingCheck = "false";
                        $this->submitting = "false";
                        $this->paymentSent = "false";

                        return redirect()->to("/transaction/" . $transaction->id)->with('message', 'Payment completed successfully!');
                    } else if ($paypalTransaction['status'] === 'APPROVED') {
                        // Capture the payment
                        $captureResult = $this->capturePayPalOrder($transaction->gateway_donation_id);

                        if ($captureResult && isset($captureResult['status']) && $captureResult['status'] === 'COMPLETED') {
                            $transaction->update([
                                'status' => 'completed',
                                'gateway_response' => $captureResult,
                                'processed_at' => now()
                            ]);

                            $this->submittingCheck = "false";
                            $this->submitting = "false";
                            $this->paymentSent = "false";

                            return redirect()->to("/transaction/" . $transaction->id)->with('message', 'Payment completed successfully!');
                        }
                    } else {
                        $this->submittingCheck = "false";
                        $this->submitting = "false";
                        $this->paymentSent = "false";
                        session()->flash('error', 'Payment is still pending. Please complete the payment on PayPal.');
                    }
                } else {
                    $this->submittingCheck = "false";
                    session()->flash('error', 'Could not retrieve PayPal payment status.');
                }
            } else {
                $this->submittingCheck = "false";
                session()->flash('error', 'No PayPal payment found for this transaction.');
            }
        } catch (Exception $error) {
            $this->submittingCheck = "false";
            session()->flash('error', $error->getMessage());
        }
    }

    private function createPayPalOrder($transaction)
    {
        $accessToken = $this->getPayPalAccessToken();

        if (!$accessToken) {
            throw new Exception('Failed to get PayPal access token');
        }

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $transaction->donation_id,
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($transaction->amount, 2, '.', '')
                    ],
                    'description' => $transaction->description
                ]
            ],
            'application_context' => [
                'return_url' => $this->site_url . '/paypal-success/' . $transaction->id,
                'cancel_url' => $this->site_url . '/paypal-cancel/' . $transaction->id,
                'brand_name' => config('app.name', 'Donation Platform'),
                'user_action' => 'PAY_NOW'
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->post($this->getPayPalApiUrl() . '/v2/checkout/orders', $orderData);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('PayPal API Error: ' . $response->body());
    }

    private function getPayPalOrder($donation_id)
    {
        $accessToken = $this->getPayPalAccessToken();

        if (!$accessToken) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($this->getPayPalApiUrl() . '/v2/checkout/orders/' . $donation_id);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    private function capturePayPalOrder($donation_id)
    {
        $accessToken = $this->getPayPalAccessToken();

        if (!$accessToken) {
            return null;
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->post($this->getPayPalApiUrl() . '/v2/checkout/orders/' . $donation_id . '/capture');

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    private function getPayPalAccessToken()
    {
        $clientId = env('PAYPAL_CLIENT_ID');
        $clientSecret = env('PAYPAL_CLIENT_SECRET');


        if (!$clientId || !$clientSecret) {
            return null;
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post($this->getPayPalApiUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['access_token'] ?? null;
        }

        return null;
    }

    private function getPayPalApiUrl()
    {
        $mode = env('PAYPAL_MODE', 'sandbox');
        return $mode === 'live'
            ? 'https://api.paypal.com'
            : 'https://api.sandbox.paypal.com';
    }

    private function generateRandomId()
    {
        return uniqid('paypal_txn_', true);
    }

    public function render()
    {
        return view('livewire.gateways.paypal-gateway')->extends('app');
    }
}
