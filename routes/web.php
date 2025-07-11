<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Livewire\HomePage;
use App\Models\Transaction;

Route::get('/', \App\Livewire\HomePage::class)->name('home');
Route::get('/payment/ecocash/{donation_id}', \App\Livewire\Gateways\PaynowGateway::class)->name('transaction.ecocash');
Route::get('/payment/paypal/{donation_id}', \App\Livewire\Gateways\PaypalGateway::class)->name('transaction.paypal');
Route::get('/payment/stripe/{donation_id}', \App\Livewire\Gateways\StripeGateway::class)->name('transaction.stripe');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('dashboard');

    // Campaign routes
    Route::get('/campaigns', \App\Livewire\CampaignList::class)->name('campaigns.index');
    Route::get('/campaigns/create', \App\Livewire\CreateCampaign::class)->name('campaigns.create');
    Route::get('/campaigns/{campaign:slug}', \App\Livewire\CampaignShow::class)->name('campaigns.show');

    // Payment Gateway routes
    Route::get('/payment/paypal/{orderId}', \App\Livewire\Gateways\PaypalGateway::class)->name('payment.paypal');
    Route::get('/payment/paynow/{orderId}', \App\Livewire\Gateways\PaynowGateway::class)->name('payment.paynow');
    Route::get('/payment/stripe/{orderId}', \App\Livewire\Gateways\StripeGateway::class)->name('payment.stripe');

    // PayPal callback routes
    Route::get('/paypal-success/{transactionId}', function ($transactionId) {
        $transaction = \App\Models\Transaction::findOrFail($transactionId);
        $donation = \App\Models\Donation::findOrFail($transaction->donation_id);
        $transaction->update(['status' => 'completed']);
        $donation->update(['status' => 'completed']);
        return redirect()->route('transaction.show', $transactionId)->with('message', 'Payment completed successfully!');
    })->name('paypal.success');

    Route::get('/paypal-cancel/{transactionId}', function ($transactionId) {
        return redirect()->route('transaction.show', $transactionId)->with('error', 'Payment was cancelled.');
    })->name('paypal.cancel');

    // Stripe callback routes
    Route::get('/stripe-return/{transactionId}', function ($transactionId) {
        return redirect()->route('transaction.show', $transactionId)->with('message', 'Please check your payment status.');
    })->name('stripe.return');

    // Transaction routes
    Route::get('/transaction/{transactionId}', function ($transactionId) {
        $transaction = \App\Models\Transaction::findOrFail($transactionId);
        return view('transaction.show', compact('transaction'));
    })->name('transaction.show');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

// Webhook routes (public routes - no authentication required)
Route::post('/paypal/webhook', [App\Http\Controllers\PayPalWebhookController::class, 'handle'])->name('paypal.webhook');
Route::post('/stripe/webhook', [App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

require __DIR__ . '/auth.php';
