<?php

use App\Livewire\Auth\Register;
use Livewire\Livewire;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('user_type', 'individual')
        ->call('register');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('new organizations can register', function () {
    $response = Livewire::test(Register::class)
        ->set('name', 'Test Organization')
        ->set('email', 'org@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('user_type', 'non_profit')
        ->call('register');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    
    $user = \App\Models\User::where('email', 'org@example.com')->first();
    $this->assertEquals('non_profit', $user->user_type);
});