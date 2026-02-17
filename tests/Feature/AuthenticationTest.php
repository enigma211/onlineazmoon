<?php

use App\Models\User;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Cache;

test('user can register with valid data', function () {
    $component = Volt::test('pages.auth.register')
        ->set('name', 'احمد')
        ->set('family', 'رضایی')
        ->set('national_code', '1234567890')
        ->set('mobile', '09123456789')
        ->set('education_field', 'Engineering')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'national_code' => '1234567890',
        'mobile' => '09123456789',
    ]);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'mobile' => '09123456789',
        'password' => bcrypt('password123'),
    ]);

    $component = Volt::test('pages.auth.login')
        ->set('form.mobile', '09123456789')
        ->set('form.password', 'password123');

    $component->call('login');

    $component->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
});

test('user cannot login with invalid credentials', function () {
    User::factory()->create([
        'mobile' => '09123456789',
        'password' => bcrypt('password123'),
    ]);

    $component = Volt::test('pages.auth.login')
        ->set('form.mobile', '09123456789')
        ->set('form.password', 'wrongpassword');

    $component->call('login');

    $component->assertHasErrors(['form.mobile']);
    $this->assertGuest();
});

test('user can logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('registration is disabled when setting is off', function () {
    // Mock the settings cache
    Cache::put('site_settings', ['enable_registration' => false]);

    $response = $this->get(route('register'));

    $response->assertForbidden();
});

test('registration is enabled when setting is on', function () {
    Cache::put('site_settings', ['enable_registration' => true]);

    $response = $this->get(route('register'));

    $response->assertOk();
});

test('guest homepage contains login and register links to auth routes when registration is enabled', function () {
    Cache::put('site_settings', ['enable_registration' => true]);

    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee('href="'.route('login').'"', false)
        ->assertSee('href="'.route('register').'"', false);
});

test('guest homepage hides register link when registration is disabled', function () {
    Cache::put('site_settings', ['enable_registration' => false]);

    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee('href="'.route('login').'"', false)
        ->assertDontSee('href="'.route('register').'"', false);
});
