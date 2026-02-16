<?php

use App\Models\User;

test('user can be created', function () {
    $user = User::factory()->create([
        'name' => 'احمد',
        'family' => 'رضایی',
        'national_code' => '1234567890',
        'mobile' => '09123456789',
    ]);

    expect($user->name)->toBe('احمد')
        ->and($user->family)->toBe('رضایی')
        ->and($user->national_code)->toBe('1234567890')
        ->and($user->mobile)->toBe('09123456789');
});

test('user can access panel if mobile is in admin list', function () {
    $adminUser = User::factory()->create([
        'mobile' => '09123456789',
    ]);

    expect($adminUser->canAccessPanel(null))->toBeTrue();
});

test('user cannot access panel if mobile is not in admin list', function () {
    $regularUser = User::factory()->create([
        'mobile' => '09987654321',
    ]);

    expect($regularUser->canAccessPanel(null))->toBeFalse();
});

test('user has exam attempts relationship', function () {
    $user = User::factory()->create();

    expect($user->examAttempts())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('user full name is concatenated correctly', function () {
    $user = User::factory()->create([
        'name' => 'محمد',
        'family' => 'احمدی',
    ]);

    expect($user->full_name)->toBe('محمد احمدی');
});
