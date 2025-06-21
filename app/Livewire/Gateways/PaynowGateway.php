<?php

namespace App\Livewire\Gateways;

use Livewire\Component;

use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\Auth;
use Paynow\Payments\Paynow;

class PaynowGateway extends Component
{

    public $orderId;
    public $amount;
    public $site_url;
    public $phone;
    public $submitting = "false";
    public $submittingCheck = "false";
    public $paymentSent = "false";

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $order = Orders::findOrFail($orderId);
        $this->amount = $order->total;
        $this->site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    }

    public function createPayment()
    {

        $this->validate([
            'phone' => 'required|regex:/^[0-9]{10,15}$/', // Add validation for phone number
        ]);

        $this->submitting = "true";
        $order = Orders::findOrFail($this->orderId);

        $new_trans = Transaction::updateOrCreate(
            ['order_id' => $this->orderId],
            [
                'user_id' => Auth::user()->id,
                'type' => 'paynow',
                'total' => $order->total
            ]
        );

        try {
            $uuid = $this->generateRandomId();
            $payment = $this->paynow($new_trans->id, "paynow")->createPayment("$uuid", Auth::user()->email);
            $payment->add("Invoice Payment With id of " . $order->id, $order->total);
            $response = $this->paynow($new_trans->id, "paynow")->sendMobile($payment, $this->phone, 'ecocash');
            if ($response->success) {
                $update_tran = Transaction::find($new_trans->id);
                $update_tran->update(['poll_url' => $response->pollUrl()]);

                $pollUrl = $response->pollUrl();


                // sleep(15);
                $status = $this->paynow($new_trans->id, "paynow")->pollTransaction($pollUrl);

                if ($status->status() === "sent") {
                    session()->flash('message', 'Payment has been sent to your phone please confirm with pin!!');
                    $this->paymentSent = "true";
                }

                if ($status->paid()) {
                    $this->submitting = "false";

                    return redirect()->to("/orders")->with('message', 'Your payment was successdull!!');
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
            $transaction = Transaction::where('order_id', $this->orderId)->first();
            $order = Orders::findOrFail($this->orderId);

            $pollUrl = $transaction->poll_url;


            // sleep(15);
            $status = $this->paynow($this->orderId, "paynow")->pollTransaction($pollUrl);

            if ($status->status() === "sent") {
                session()->flash('message', 'Payment was unsuccessfull please try repaying again!!');
                $this->paymentSent = "true";
            }

            if ($status->paid()) {
                $this->submittingCheck = "false";
                $this->submitting = "false";
                $transaction->update(['isPaid' => "true"]);
                $order->update(['status' => 'paid']);
                $this->paymentSent = "false";

                return redirect()->to("/orders")->with('message', 'Your payment was successdull!!');
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
        return view('livewire.gateways.paynow-gateway');
    }
}
