<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'EcoCash',
                'slug' => 'ecocash',
                'description' => 'Mobile money service for Zimbabwe',
                'provider_class' => 'App\\Services\\PaymentGateways\\EcoCashGateway',
                'configuration' => [
                    'merchant_code' => '',
                    'api_key' => '',
                    'api_secret' => '',
                    'environment' => 'sandbox',
                ],
                'fee_percentage' => 2.5,
                'fee_fixed' => 0,
                'currency' => 'USD',
                'supports_refunds' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'OneMoney',
                'slug' => 'onemoney',
                'description' => 'NetOne mobile money service',
                'provider_class' => 'App\\Services\\PaymentGateways\\OneMoneyGateway',
                'configuration' => [
                    'merchant_id' => '',
                    'api_key' => '',
                    'environment' => 'sandbox',
                ],
                'fee_percentage' => 2.0,
                'fee_fixed' => 0,
                'currency' => 'USD',
                'supports_refunds' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Telecash',
                'slug' => 'telecash',
                'description' => 'Telecel mobile money service',
                'provider_class' => 'App\\Services\\PaymentGateways\\TelecashGateway',
                'configuration' => [
                    'merchant_id' => '',
                    'api_secret' => '',
                    'environment' => 'sandbox',
                ],
                'fee_percentage' => 2.5,
                'fee_fixed' => 0,
                'currency' => 'USD',
                'supports_refunds' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'description' => 'International online payment system',
                'provider_class' => 'App\\Services\\PaymentGateways\\PayPalGateway',
                'configuration' => [
                    'client_id' => '',
                    'client_secret' => '',
                    'environment' => 'sandbox',
                ],
                'fee_percentage' => 3.4,
                'fee_fixed' => 0.30,
                'currency' => 'USD',
                'supports_refunds' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Stripe',
                'slug' => 'stripe',
                'description' => 'International card payment processor',
                'provider_class' => 'App\\Services\\PaymentGateways\\StripeGateway',
                'configuration' => [
                    'publishable_key' => '',
                    'secret_key' => '',
                    'environment' => 'test',
                ],
                'fee_percentage' => 2.9,
                'fee_fixed' => 0.30,
                'currency' => 'USD',
                'supports_refunds' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::firstOrCreate(
                ['slug' => $gateway['slug']],
                $gateway
            );
        }
    }
}
