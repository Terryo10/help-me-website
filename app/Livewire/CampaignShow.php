<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\Donation;
use Livewire\Component;

class CampaignShow extends Component
{
    public Campaign $campaign;
    public $donationAmount = 10;
    public $selected_gateway = 'ecocash';
    public $donorName = '';
    public $donorEmail = '';
    public $comment = '';
    public $isAnonymous = false;
    public $showDonationForm = false;

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $campaign->increment('view_count');

        if (auth()->check()) {
            $this->donorName = auth()->user()->name;
            $this->donorEmail = auth()->user()->email;
        }
    }

    public function updated($propertyName)
    {
        $this->showDonationForm = true; // Automatically show the donation form when any property is updated
        // This method is called whenever any public property is updated
        // You can add custom logic here, for example logging or validation
        // Example: \Log::info("Property {$propertyName} updated to: " . $this->$propertyName);
    }

    public function toggleDonationForm()
    {
        $this->showDonationForm = true; // Automatically show the donation form when any property is updated
        $this->showDonationForm = !$this->showDonationForm;
    }

    public function donate()
    {
        $this->validate([
            'donationAmount' => 'required|numeric|min:' . $this->campaign->minimum_donation,
            'selected_gateway' => 'required',
            'donorName' => 'required_unless:isAnonymous,true|string|max:255',
            'donorEmail' => 'required_unless:isAnonymous,true|email',
            'comment' => 'nullable|string|max:500',
        ]);

        // Create donation record
        $donation = Donation::create([
            'donation_id' => 'DON-' . time() . '-' . rand(1000, 9999),
            'campaign_id' => $this->campaign->id,
            'user_id' => auth()->id(),
            'donor_name' => $this->isAnonymous ? null : $this->donorName,
            'donor_email' => $this->isAnonymous ? null : $this->donorEmail,
            'is_anonymous' => $this->isAnonymous,
            'amount' => $this->donationAmount,
            'currency' => $this->campaign->currency,
            'comment' => $this->comment,
            'status' => 'pending', // Default gateway
        ]);

        session()->flash('message', 'Thank you for your donation! You will be redirected to payment.');

        if ($this->selected_gateway == 'ecocash') {
            return redirect('/payment/ecocash/' . $donation->id);
        } else if ($this->selected_gateway == 'paypal') {
            return redirect('/payment/paypal/' . $donation->id);
        } else if ($this->selected_gateway == 'stripe') {
            return redirect('/payment/stripe/' . $donation->id);
        }

        // Redirect to payment page
        return redirect()->back();
    }

    public function render()
    {
        $recentDonations = $this->campaign->donations()
            ->where('status', 'completed')
            // ->where('show_comment_publicly', true)
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        $relatedCampaigns = Campaign::where('id', '!=', $this->campaign->id)
            ->whereHas('categories', function ($query) {
                $query->whereIn('categories.id', $this->campaign->categories->pluck('id'));
            })
            ->active()
            ->take(3)
            ->get();

        $donationCount = Donation::where('campaign_id', $this->campaign->id)->count();

        return view('livewire.campaign-show', [
            'recentDonations' => $recentDonations,
            'relatedCampaigns' => $relatedCampaigns,
            'donationCount' => $donationCount
        ])->extends('app');
    }
}
