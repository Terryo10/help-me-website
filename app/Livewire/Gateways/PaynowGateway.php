<?php

namespace App\Livewire\Gateways;

use App\Models\Donation;
use Livewire\Component;

use App\Models\Transaction;
use App\Services\EmailNotificationService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Paynow\Payments\Paynow;

class PaynowGateway extends Component
{

    public $donation_id;
    public $amount;
    public $site_url;
    public $phone;
    public $submitting = "false";
    public $submittingCheck = "false";
    public $paymentSent = "false";

    public function mount($donation_id)
    {
        $this->donation_id = $donation_id;
        $donation = Donation::findOrFail($donation_id);
        $this->amount = $donation->amount;
        $this->site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    }

    public function createPayment()
    {

        $this->validate([
            'phone' => 'required|regex:/^[0-9]{10,15}$/', // Add validation for phone number
        ]);

        $this->submitting = "true";
        $donation = Donation::findOrFail($this->donation_id);

        $new_trans = Transaction::updateOrCreate(
            ['donation_id' => $this->donation_id],
            [
                'donation_random_id' => $this->generateRandomId(),
                'donation_id' => $this->donation_id,
                'type' => 'donation',
                'amount' => $donation->amount,
                'currency' => 'USD',
                'status' => 'pending',
                'description' => "Donation payment for transaction #" . $donation->id
            ]
        );

        try {
            $uuid = $this->generateRandomId();
            $payment = $this->paynow($new_trans->id, "paynow")->createPayment("$uuid", Auth::user()->email);
            $payment->add("Invoice Payment With id of " . $donation->id, $donation->amount);
            $response = $this->paynow($new_trans->id, "paynow")->sendMobile($payment, $this->phone, 'ecocash');
            if ($response->success) {
                $update_tran = Transaction::find($new_trans->id);
                $update_tran->update(['reference' => $response->pollUrl()]);

                $pollUrl = $response->pollUrl();


                // sleep(15);
                $status = $this->paynow($new_trans->id, "paynow")->pollTransaction($pollUrl);

                if ($status->status() === "sent") {
                    session()->flash('success', 'Payment has been sent to your phone please confirm with pin!!');
                    $this->paymentSent = "true";
                }

                if ($status->paid()) {
                    $this->submitting = "false";

                    return redirect()->to("/Transaction")->with('success', 'Your payment was successdull!!');
                } else {
                    // $this->submitting = "false";
                    // session()->flash('error', 'Why not pay!!');
                }
            } else {
                $this->submitting = "false";
                session()->flash('error', 'Oops something went wrong while trying to process your transaction. Please try again.');
            }
        } catch (\Exception $error) {
            $this->submitting = "false";
            session()->flash('error', $error->getMessage());
        }
    }

    public function checkPayment()
    {
        $this->submittingCheck = "true";
        $this->paymentSent = "false";

        try {
            $transaction = Transaction::where('donation_id', $this->donation_id)->first();
            $donation = Donation::findOrFail($this->donation_id);

            $pollUrl = $transaction->reference;


            // sleep(15);
            $status = $this->paynow($this->donation_id, "paynow")->pollTransaction($pollUrl);

            if ($status->status() === "sent") {
                session()->flash('success', 'Payment was unsuccessfull please try repaying again!!');
                $this->paymentSent = "true";
            }

            if ($status->paid()) {
                $this->submittingCheck = "false";
                $this->submitting = "false";
                $transaction->update(['status' => "paid"]);
                $donation->update(['status' => 'paid']);
                $this->paymentSent = "false";
                $donation = \App\Models\Donation::findOrFail($transaction->donation_id);
                $donation->update(['status' => 'completed']);
                $notificaionService = new EmailNotificationService();
                $notificaionService->sendEmail("Payment Completed", "Someone donated to your campaign ID {$donation->campaign_id} ", $donation->campaign->user->email);


                return redirect()->to("/transaction/" . $transaction->id)->with('success', 'Your payment was successdull!!');
            } else {
                $this->submittingCheck = "false";
                $this->submitting = "false";
                $this->paymentSent = "false";
                session()->flash('error', 'Payment was unsuccessfull please try and pay again!!');
            }
        } catch (Exception $error) {
            $this->submittingCheck = "false";
            session()->flash('error', $error->getMessage());
        }
    }

    private function generateRandomId()
    {
        return uniqid('txn_', true);
    }

    public function paynow($id = "", $type = "")
    {
        $site_url = $this->site_url;
        $request_url = $_SERVER['REQUEST_URI'];
        return new Paynow(
            env('PAYNOW_INTERGRATION_ID'),
            env('PAYNOW_INTERGRATION_KEY'),
            // The return url can be set at later stages. You might want to do this if you want to pass data to the return url (like the reference of the transaction)
            "$site_url/check-payment/$id", //return url
            "$site_url/check-payment/$id", //result url
        );
    }
    public function render()
    {
        return view('livewire.gateways.paynow-gateway')->extends('app');
    }
}
