<?php

use App\Models\User;
use App\Models\EducationField;
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
    'password' => '',
    'password_confirmation' => ''
]);

rules([
    'name' => ['required', 'string', 'max:255'],
    'family' => ['required', 'string', 'max:255'],
    'mobile' => ['required', 'string', 'max:11', 'unique:'.User::class],
    'national_code' => ['required', 'string', 'size:10', 'unique:'.User::class],
    'education_field' => ['required', 'string', 'max:255'],
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

<form wire:submit.prevent="register">
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" value="نام" />
                <x-text-input wire:model="name" wire:keydown.enter="register" id="name" class="block mt-1 w-full" type="text" required autofocus autocomplete="name" placeholder="نام" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="family" value="نام خانوادگی" />
                <x-text-input wire:model="family" wire:keydown.enter="register" id="family" class="block mt-1 w-full" type="text" required autocomplete="family-name" placeholder="نام خانوادگی" />
                <x-input-error :messages="$errors->get('family')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="national_code" value="کد ملی" />
            <x-text-input wire:model="national_code" wire:keydown.enter="register" id="national_code" class="block mt-1 w-full" type="text" required placeholder="کد ملی 10 رقمی" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" />
            <x-input-error :messages="$errors->get('national_code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="mobile" value="شماره موبایل" />
            <x-text-input wire:model="mobile" wire:keydown.enter="register" id="mobile" class="block mt-1 w-full" type="text" required autocomplete="tel" placeholder="0912xxxxxxx" maxlength="11" pattern="[0-9]{11}" inputmode="numeric" />
            <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="education_field" value="رشته تحصیلی" />
            <select wire:model="education_field" wire:keydown.enter="register" id="education_field" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">انتخاب کنید</option>
                @foreach(App\Models\EducationField::getActive() as $field)
                    <option value="{{ $field->name }}">{{ $field->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('education_field')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="رمز عبور" />
            <x-text-input wire:model="password" wire:keydown.enter="register" id="password" class="block mt-1 w-full" type="password" required autocomplete="new-password" placeholder="رمز عبور" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="تکرار رمز عبور" />
            <x-text-input wire:model="password_confirmation" wire:keydown.enter="register" id="password_confirmation" class="block mt-1 w-full" type="password" required autocomplete="new-password" placeholder="تکرار رمز عبور" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <button 
                type="submit"
                class="w-full inline-flex justify-center items-center px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-red-500/30">
                <span wire:loading.remove wire:target="register">ثبت نام در سامانه</span>
                <span wire:loading wire:target="register">در حال پردازش...</span>
            </button>
        </div>
        
        @if ($errors->any())
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="text-sm text-red-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="text-center pt-2">
            <span class="text-sm text-gray-500">قبلاً ثبت نام کرده‌اید؟ </span>
            <a class="text-sm text-red-600 hover:text-red-700 font-bold" href="{{ route('login') }}" wire:navigate>
                وارد شوید
            </a>
        </div>
    </div>
</form>
