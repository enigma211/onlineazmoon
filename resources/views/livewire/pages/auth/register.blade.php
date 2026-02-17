<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state([
    'name' => '',
    'family' => '',
    'mobile' => '',
    'national_code' => '',
    'education_field' => '',
    'birth_date' => '',
    'password' => '',
    'password_confirmation' => ''
]);

rules([
    'name' => ['required', 'string', 'max:255'],
    'family' => ['required', 'string', 'max:255'],
    'mobile' => ['required', 'string', 'max:11', 'unique:'.User::class],
    'national_code' => ['required', 'string', 'size:10', 'unique:'.User::class],
    'education_field' => ['required', 'string', 'max:255'],
    'birth_date' => ['nullable', 'date'],
    'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
]);

$register = function () {
    $validated = $this->validate();

    $validated['password'] = Hash::make($validated['password']);

    event(new Registered($user = User::create($validated)));

    Auth::login($user);

    $this->redirect(route('dashboard', absolute: false), navigate: true);
};

?>

<div>
    <form wire:submit="register" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" value="نام" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" placeholder="نام" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="family" value="نام خانوادگی" />
                <x-text-input wire:model="family" id="family" class="block mt-1 w-full" type="text" name="family" required autocomplete="family-name" placeholder="نام خانوادگی" />
                <x-input-error :messages="$errors->get('family')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="national_code" value="کد ملی" />
            <x-text-input wire:model="national_code" id="national_code" class="block mt-1 w-full" type="text" name="national_code" required placeholder="کد ملی 10 رقمی" />
            <x-input-error :messages="$errors->get('national_code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="mobile" value="شماره موبایل" />
            <x-text-input wire:model="mobile" id="mobile" class="block mt-1 w-full" type="text" name="mobile" required autocomplete="tel" placeholder="0912xxxxxxx" />
            <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="education_field" value="رشته تحصیلی" />
            <x-text-input wire:model="education_field" id="education_field" class="block mt-1 w-full" type="text" name="education_field" required placeholder="رشته تحصیلی" />
            <x-input-error :messages="$errors->get('education_field')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="birth_date" value="تاریخ تولد" />
            <x-text-input wire:model="birth_date" id="birth_date" class="block mt-1 w-full" type="date" name="birth_date" />
            <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="رمز عبور" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="رمز عبور" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="تکرار رمز عبور" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="تکرار رمز عبور" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800">
                ثبت نام در سامانه
            </x-primary-button>
        </div>

        <div class="text-center pt-2">
            <span class="text-sm text-gray-500">قبلاً ثبت نام کرده‌اید؟ </span>
            <a class="text-sm text-red-600 hover:text-red-700 font-bold" href="{{ route('login') }}" wire:navigate>
                وارد شوید
            </a>
        </div>
    </form>
</div>
