<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\User;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state(['mobile' => '']);

rules(['mobile' => ['required', 'string', 'exists:users,mobile']]);

$sendOtp = function () {
    $this->validate();

    $otp = rand(10000, 99999);
    
    // Store OTP in Cache for 5 minutes
    Cache::put('otp_' . $this->mobile, $otp, now()->addMinutes(5));

    // Send SMS (Mock for now, would use Melipayamak here)
    \Illuminate\Support\Facades\Log::info("OTP for {$this->mobile}: {$otp}");

    // Redirect to verification page
    Session::put('reset_mobile', $this->mobile);
    $this->redirect(route('password.verify-otp'), navigate: true);
};

?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? Enter your mobile number to receive a verification code.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendOtp">
        <!-- Mobile -->
        <div>
            <x-input-label for="mobile" :value="__('Mobile')" />
            <x-text-input wire:model="mobile" id="mobile" class="block mt-1 w-full" type="text" name="mobile" required autofocus />
            <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Send Verification Code') }}
            </x-primary-button>
        </div>
    </form>
</div>
