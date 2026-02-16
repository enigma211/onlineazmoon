<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response
            ->assertSeeVolt('pages.auth.forgot-password')
            ->assertStatus(200);
    }

    public function test_otp_can_be_requested_with_valid_mobile(): void
    {
        $user = User::factory()->create([
            'mobile' => '09123456789'
        ]);

        $component = Volt::test('pages.auth.forgot-password')
            ->set('mobile', '09123456789');

        $component->call('sendOtp');

        $component->assertRedirect(route('password.verify-otp'));

        $this->assertTrue(Session::has('reset_mobile'));
        $this->assertEquals('09123456789', Session::get('reset_mobile'));
        $this->assertTrue(Cache::has('otp_09123456789'));
    }

    public function test_verify_otp_screen_can_be_rendered(): void
    {
        $response = $this->get('/verify-otp');

        $response
            ->assertSeeVolt('pages.auth.verify-otp')
            ->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_otp(): void
    {
        $user = User::factory()->create([
            'mobile' => '09123456789',
            'password' => Hash::make('old-password'),
        ]);

        Session::put('reset_mobile', '09123456789');
        Cache::put('otp_09123456789', '12345');

        $component = Volt::test('pages.auth.verify-otp')
            ->set('otp', '12345')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password');

        $component->call('resetPassword');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertFalse(Cache::has('otp_09123456789'));
        $this->assertFalse(Session::has('reset_mobile'));
    }

    public function test_password_reset_fails_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'mobile' => '09123456789'
        ]);

        Session::put('reset_mobile', '09123456789');
        Cache::put('otp_09123456789', '12345');

        $component = Volt::test('pages.auth.verify-otp')
            ->set('otp', 'wrong')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password');

        $component->call('resetPassword');

        $component->assertHasErrors(['otp']);
        $this->assertTrue(Hash::check('password', $user->fresh()->password)); // Factory default is 'password'
    }
}
