<?php

use App\Models\User;

test('admin user can access admin panel', function () {
    $admin = User::factory()->create([
        'mobile' => '09123456789',
    ]);

    $this->actingAs($admin);

    $response = $this->get('/admin');

    $response->assertOk();
});

test('non-admin user cannot access admin panel', function () {
    $user = User::factory()->create([
        'mobile' => '09987654321',
    ]);

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertForbidden();
});

test('guest cannot access admin panel', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});

test('admin can login to admin panel', function () {
    $admin = User::factory()->create([
        'mobile' => '09123456789',
        'national_code' => '1234567890',
        'password' => bcrypt('password123'),
    ]);

    $this->actingAs($admin);
    
    expect($admin->canAccessPanel())->toBeTrue();
});

test('non-admin cannot login to admin panel', function () {
    $user = User::factory()->create([
        'mobile' => '09987654321',
        'national_code' => '1234567890',
        'password' => bcrypt('password123'),
    ]);

    expect($user->canAccessPanel())->toBeFalse();
});
