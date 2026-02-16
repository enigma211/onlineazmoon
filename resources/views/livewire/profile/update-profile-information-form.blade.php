<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

use function Livewire\Volt\state;

state([
    'name' => fn () => auth()->user()->name,
    'last_name' => fn () => auth()->user()->last_name,
    'mobile' => fn () => auth()->user()->mobile,
    'national_code' => fn () => auth()->user()->national_code,
    'education_field' => fn () => auth()->user()->education_field,
    'birth_date' => fn () => auth()->user()->birth_date?->format('Y-m-d'),
]);

$updateProfileInformation = function () {
    $user = Auth::user();

    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'mobile' => ['required', 'string', 'max:11', Rule::unique(User::class)->ignore($user->id)],
        'national_code' => ['required', 'string', 'size:10', Rule::unique(User::class)->ignore($user->id)],
        'education_field' => ['nullable', 'string', 'max:255'],
        'birth_date' => ['nullable', 'date'],
    ]);

    $user->fill($validated);

    $user->save();

    $this->dispatch('profile-updated', name: $user->name);
};

?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="last_name" :value="__('Last Name')" />
            <x-text-input wire:model="last_name" id="last_name" name="last_name" type="text" class="mt-1 block w-full" required autocomplete="family-name" />
            <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
        </div>

        <div>
            <x-input-label for="mobile" :value="__('Mobile')" />
            <x-text-input wire:model="mobile" id="mobile" name="mobile" type="text" class="mt-1 block w-full" required autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('mobile')" />
        </div>

        <div>
            <x-input-label for="national_code" :value="__('National Code')" />
            <x-text-input wire:model="national_code" id="national_code" name="national_code" type="text" class="mt-1 block w-full" required />
            <x-input-error class="mt-2" :messages="$errors->get('national_code')" />
        </div>

        <div>
            <x-input-label for="education_field" :value="__('Education Field')" />
            <x-text-input wire:model="education_field" id="education_field" name="education_field" type="text" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('education_field')" />
        </div>

        <div>
            <x-input-label for="birth_date" :value="__('Birth Date')" />
            <x-text-input wire:model="birth_date" id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" />
            <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
