<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.guest');

form(LoginForm::class);

$login = function () {
    $this->validate();

    $this->form->authenticate();

    Session::regenerate();

    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
};

?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit.prevent="login" class="space-y-4" method="POST">
        @csrf
        <div>
            <x-input-label for="mobile" value="شماره موبایل" />
            <x-text-input wire:model="form.mobile" id="mobile" class="block mt-1 w-full" type="text" required autofocus autocomplete="tel" placeholder="0912xxxxxxx" maxlength="11" pattern="[0-9]{11}" inputmode="numeric" />
            <x-input-error :messages="$errors->get('form.mobile')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="رمز عبور" />
            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full" type="password" required autocomplete="current-password" placeholder="رمز عبور خود را وارد کنید" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ms-2 text-sm text-gray-600">مرا به خاطر بسپار</span>
            </label>
            
            @if (Route::has('password.request'))
                <a class="text-sm text-gray-500 hover:text-red-600 transition-colors" href="{{ route('password.request') }}" wire:navigate>
                    فراموشی رمز عبور؟
                </a>
            @endif
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800">
                ورود به سامانه
            </x-primary-button>
        </div>

        <div class="text-center pt-2">
            <span class="text-sm text-gray-500">حساب کاربری ندارید؟ </span>
            <a class="text-sm text-red-600 hover:text-red-700 font-bold" href="{{ route('register') }}" wire:navigate>
                ثبت نام کنید
            </a>
        </div>
    </form>
</div>
