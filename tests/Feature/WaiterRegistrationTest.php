<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('waiter registration page loads', function () {
    $this->get(route('waiter.register'))
        ->assertOk()
        ->assertSee('Register as Waiter')
        ->assertSee('Or register with email');
});

test('waiter details page requires credentials step first', function () {
    $this->get(route('waiter.register.details'))
        ->assertRedirect(route('waiter.register'));
});

test('guest can register waiter account', function () {
    $email = 'newwaiter'.uniqid().'@example.com';

    $this->post(route('waiter.register.credentials'), [
        'email' => $email,
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
    ])->assertRedirect(route('waiter.register.details'));

    $response = $this->post(route('waiter.register.details.store'), [
        'first_name' => 'Asha',
        'last_name' => 'Mwangi',
        'phone' => '0712345678',
        'location' => 'Dar es Salaam',
    ]);

    $response->assertRedirect(route('waiter.dashboard'));

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('waiter'))->toBeTrue();
    expect($user->restaurant_id)->toBeNull();
    expect($user->global_waiter_number)->not->toBeEmpty();
});

test('waiter registration allows optional location', function () {
    $email = 'nowaiterloc'.uniqid().'@example.com';

    $this->post(route('waiter.register.credentials'), [
        'email' => $email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('waiter.register.details'));

    $this->post(route('waiter.register.details.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '0711111111',
    ])->assertRedirect(route('waiter.dashboard'));

    expect(User::where('email', $email)->first()?->location)->toBeNull();
});
