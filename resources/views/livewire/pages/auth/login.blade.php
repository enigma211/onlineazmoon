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

    <div class="space-y-4">
        <div>
            <x-input-label for="mobile" value="شماره موبایل" />
            <x-text-input wire:model="form.mobile" wire:keydown.enter="login" id="mobile" class="block mt-1 w-full" type="text" required autofocus autocomplete="tel" placeholder="0912xxxxxxx" maxlength="11" pattern="[0-9]{11}" inputmode="numeric" />
            <x-input-error :messages="$errors->get('form.mobile')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="رمز عبور" />
            <x-text-input wire:model="form.password" wire:keydown.enter="login" id="password" class="block mt-1 w-full" type="password" required autocomplete="current-password" placeholder="رمز عبور خود را وارد کنید" />
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
            <button type="button" wire:click="login" class="w-full inline-flex justify-center items-center px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-red-500/30">
                <span wire:loading.remove>ورود به سامانه</span>
                <span wire:loading>در حال پردازش...</span>
            </button>
        </div>

        <div class="text-center pt-2">
            <span class="text-sm text-gray-500">حساب کاربری ندارید؟ </span>
            <a class="text-sm text-red-600 hover:text-red-700 font-bold" href="{{ route('register') }}" wire:navigate>
                ثبت نام کنید
            </a>
        </div>
    </div>
</div>
