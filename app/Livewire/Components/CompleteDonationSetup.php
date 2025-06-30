<?php

namespace App\Livewire\Components;

use Livewire\Component;

class CompleteDonationSetup extends Component
{
    public $donation_id;

    public function mount($donation_id)
    {
        $this->donation_id = $donation_id;
    }

    public function render()
    {
        $donation = \App\Models\Donation::findOrFail($this->donation_id);
        return view('livewire.components.complete-donation-setup', [
            'donation' => $donation,
        ])->extends('app');
    }
}
