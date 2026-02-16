<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state([
    'otp' => '',
    'password' => '',
    'password_confirmation' => '',
]);

rules([
    'otp' => ['required', 'string', 'size:5'],
    'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
]);

$resetPassword = function () {
    $this->validate();

    $mobile = Session::get('reset_mobile');

    if (! $mobile) {
        $this->redirect(route('password.request'), navigate: true);
        return;
    }

    $cachedOtp = Cache::get('otp_' . $mobile);

    if (! $cachedOtp || $cachedOtp != $this->otp) {
        $this->addError('otp', __('The provided code is invalid or expired.'));
        return;
    }

    $user = User::where('mobile', $mobile)->first();

    if (! $user) {
        $this->addError('mobile', __('User not found.'));
        return;
    }

    $user->forceFill([
        'password' => Hash::make($this->password),
        'remember_token' => \Illuminate\Support\Str::random(60),
    ])->save();

    Cache::forget('otp_' . $mobile);
    Session::forget('reset_mobile');

    auth()->login($user);

    $this->redirect(route('dashboard', absolute: false), navigate: true);
};

?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Please enter the verification code sent to your mobile number along with your new password.') }}
    </div>

    <form wire:submit="resetPassword">
        <!-- OTP -->
        <div>
            <x-input-label for="otp" :value="__('Verification Code')" />
            <x-text-input wire:model="otp" id="otp" class="block mt-1 w-full" type="text" name="otp" required autofocus />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</div>
