<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\Donation;
use Livewire\Component;

class CampaignShow extends Component
{
    public Campaign $campaign;
    public $donationAmount = 10;
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

    public function toggleDonationForm()
    {
        $this->showDonationForm = !$this->showDonationForm;
    }

    public function donate()
    {
        $this->validate([
            'donationAmount' => 'required|numeric|min:' . $this->campaign->minimum_donation,
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
            'status' => 'pending',
            'payment_gateway_id' => 1, // Default gateway
        ]);

        session()->flash('message', 'Thank you for your donation! You will be redirected to payment.');
        
        // Redirect to payment page
        return redirect()->route('payment.process', $donation->donation_id);
    }

    public function render()
    {
        $recentDonations = $this->campaign->donations()
            ->where('status', 'completed')
            ->where('show_comment_publicly', true)
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

        return view('livewire.campaign-show', [
            'recentDonations' => $recentDonations,
            'relatedCampaigns' => $relatedCampaigns
        ])->extends('app');
    }
}