<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\PaymentGateway;
use App\Models\Donation;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DonationWizard extends Component
{
    public Campaign $campaign;
    public $step = 1;
    public $donationAmount = 10;
    public $donorName = '';
    public $donorEmail = '';
    public $comment = '';
    public $isAnonymous = false;
    public $selectedGateway = null;
    public $gateways = [];
    public $show = false;

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $this->donationAmount = $campaign->minimum_donation;
        $this->gateways = PaymentGateway::where('is_active', true)->get();
        if (Auth::check()) {
            $this->donorName = Auth::user()->name;
            $this->donorEmail = Auth::user()->email;
        }
    }

    public function showWizard()
    {
        $this->show = true;
    }

    public function hideWizard()
    {
        $this->show = false;
        $this->reset(['step', 'donationAmount', 'donorName', 'donorEmail', 'comment', 'isAnonymous', 'selectedGateway']);
    }

    public function nextStep()
    {
        $this->step++;
    }

    public function prevStep()
    {
        $this->step--;
    }

    public function selectGateway($gatewayId)
    {
        $this->selectedGateway = $gatewayId;
        $this->nextStep();
    }

    public function submitDonation()
    {
        $this->validate([
            'donationAmount' => 'required|numeric|min:' . $this->campaign->minimum_donation,
            'donorName' => 'required_unless:isAnonymous,true|string|max:255',
            'donorEmail' => 'required_unless:isAnonymous,true|email',
            'comment' => 'nullable|string|max:500',
            'selectedGateway' => 'required|exists:payment_gateways,id',
        ]);

        $donation = Donation::create([
            'donation_id' => 'DON-' . time() . '-' . rand(1000, 9999),
            'campaign_id' => $this->campaign->id,
            'user_id' => Auth::id(),
            'donor_name' => $this->isAnonymous ? null : $this->donorName,
            'donor_email' => $this->isAnonymous ? null : $this->donorEmail,
            'is_anonymous' => $this->isAnonymous,
            'amount' => $this->donationAmount,
            'currency' => $this->campaign->currency,
            'comment' => $this->comment,
            'status' => 'pending',
            'payment_gateway_id' => $this->selectedGateway,
        ]);

        // Redirect to payment gateway component or route
        session()->flash('message', 'Thank you for your donation! Please complete your payment.');
        // You can emit an event or redirect as needed here
        // For now, just reset
        $this->hideWizard();
    }

    public function render()
    {
        return view('livewire.donation-wizard');
    }
}
