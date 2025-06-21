<?php

namespace App\Livewire\Gateways;

use Livewire\Component;

class PaypalGateway extends Component
{
    public function render()
    {
        return view('livewire.gateways.paypal-gateway');
    }
}
