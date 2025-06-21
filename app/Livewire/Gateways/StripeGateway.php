<?php

namespace App\Livewire\Gateways;

use Livewire\Component;

class StripeGateway extends Component
{
    public function render()
    {
        return view('livewire.gateways.stripe-gateway');
    }
}
