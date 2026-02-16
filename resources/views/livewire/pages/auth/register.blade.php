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
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Family Name -->
        <div class="mt-4">
            <x-input-label for="family" :value="__('Family Name')" />
            <x-text-input wire:model="family" id="family" class="block mt-1 w-full" type="text" name="family" required autocomplete="family-name" />
            <x-input-error :messages="$errors->get('family')" class="mt-2" />
        </div>

        <!-- Mobile -->
        <div class="mt-4">
            <x-input-label for="mobile" :value="__('Mobile')" />
            <x-text-input wire:model="mobile" id="mobile" class="block mt-1 w-full" type="text" name="mobile" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
        </div>

        <!-- National Code -->
        <div class="mt-4">
            <x-input-label for="national_code" :value="__('National Code')" />
            <x-text-input wire:model="national_code" id="national_code" class="block mt-1 w-full" type="text" name="national_code" required />
            <x-input-error :messages="$errors->get('national_code')" class="mt-2" />
        </div>

        <!-- Education Field -->
        <div class="mt-4">
            <x-input-label for="education_field" :value="__('Education Field')" />
            <x-text-input wire:model="education_field" id="education_field" class="block mt-1 w-full" type="text" name="education_field" required />
            <x-input-error :messages="$errors->get('education_field')" class="mt-2" />
        </div>

        <!-- Birth Date -->
        <div class="mt-4">
            <x-input-label for="birth_date" :value="__('Birth Date')" />
            <x-text-input wire:model="birth_date" id="birth_date" class="block mt-1 w-full" type="date" name="birth_date" />
            <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
