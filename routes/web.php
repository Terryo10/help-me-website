<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Livewire\HomePage;

Route::get('/', HomePage::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('dashboard');
    
    // Campaign routes
    Route::get('/campaigns', \App\Livewire\CampaignList::class)->name('campaigns.index');
    Route::get('/campaigns/create', \App\Livewire\CreateCampaign::class)->name('campaigns.create');
    Route::get('/campaigns/{campaign:slug}', \App\Livewire\CampaignShow::class)->name('campaigns.show');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
