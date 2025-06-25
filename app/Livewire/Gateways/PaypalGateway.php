<?php

namespace App\Livewire\Gateways;

use Livewire\Component;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PaypalGateway extends Component
{
    public $orderId;
    public $amount;
    public $site_url;
    public $email;
    public $submitting = "false";
    public $submittingCheck = "false";
    public $paymentSent = "false";
    public $paymentUrl;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $order = Transaction::findOrFail($orderId);
        $this->amount = $order->amount;
        $this->site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $this->email = Auth::user()->email;
    }

    public function createPayment()
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        $this->submitting = "true";
        $order = Transaction::findOrFail($this->orderId);

        $new_trans = Transaction::updateOrCreate(
            ['id' => $this->orderId],
            [
                'transaction_id' => $this->generateRandomId(),
                'donation_id' => $order->donation_id ?? 1,
                'payment_gateway_id' => 1, // Assuming PayPal gateway ID
                'type' => 'donation',
                'amount' => $order->amount,
                'currency' => 'USD',
                'status' => 'pending',
                'description' => "Donation payment for order #" . $order->id
            ]
        );

        try {
            $paypalOrder = $this->createPayPalOrder($new_trans);

            if ($paypalOrder && isset($paypalOrder['id'])) {
                $new_trans->update([
                    'gateway_transaction_id' => $paypalOrder['id'],
                    'gateway_response' => $paypalOrder,
                    'status' => 'processing'
                ]);

                // Get approval URL from PayPal response
                $approvalUrl = null;
                if (isset($paypalOrder['links'])) {
                    foreach ($paypalOrder['links'] as $link) {
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
            $transaction = Transaction::findOrFail($this->orderId);

            if ($transaction->gateway_transaction_id) {
                $paypalOrder = $this->getPayPalOrder($transaction->gateway_transaction_id);

                if ($paypalOrder && isset($paypalOrder['status'])) {
                    if ($paypalOrder['status'] === 'COMPLETED') {
                        $transaction->update([
                            'status' => 'completed',
                            'gateway_response' => $paypalOrder,
                            'processed_at' => now()
                        ]);

                        $this->submittingCheck = "false";
                        $this->submitting = "false";
                        $this->paymentSent = "false";

                        return redirect()->to("/transaction/" . $transaction->id)->with('message', 'Payment completed successfully!');
                    } else if ($paypalOrder['status'] === 'APPROVED') {
                        // Capture the payment
                        $captureResult = $this->capturePayPalOrder($transaction->gateway_transaction_id);

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
                    'reference_id' => $transaction->transaction_id,
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

    private function getPayPalOrder($orderId)
    {
        $accessToken = $this->getPayPalAccessToken();

        if (!$accessToken) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($this->getPayPalApiUrl() . '/v2/checkout/orders/' . $orderId);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    private function capturePayPalOrder($orderId)
    {
        $accessToken = $this->getPayPalAccessToken();

        if (!$accessToken) {
            return null;
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->post($this->getPayPalApiUrl() . '/v2/checkout/orders/' . $orderId . '/capture');

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
        return view('livewire.gateways.paypal-gateway');
    }
}
