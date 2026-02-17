<?php

use App\Models\EducationField;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use function Livewire\Volt\state;

state([
    'name' => fn () => auth()->user()->name,
    'family' => fn () => auth()->user()->family,
    'mobile' => fn () => auth()->user()->mobile,
    'national_code' => fn () => auth()->user()->national_code,
    'education_field' => fn () => auth()->user()->education_field,
]);

$updateProfileInformation = function () {
    $user = Auth::user();

    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'family' => ['required', 'string', 'max:255'],
        'mobile' => ['required', 'string', 'max:11', Rule::unique(User::class)->ignore($user->id)],
        'national_code' => ['required', 'string', 'size:10', Rule::unique(User::class)->ignore($user->id)],
        'education_field' => ['required', 'string', 'max:255'],
    ]);

    $user->fill($validated);

    $user->save();

    $this->dispatch('profile-updated', name: $user->name);
};

?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            اطلاعات پروفایل
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            مشخصات حساب کاربری خود را ویرایش کنید.
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="name" value="نام" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="family" value="نام خانوادگی" />
            <x-text-input wire:model="family" id="family" name="family" type="text" class="mt-1 block w-full" required autocomplete="family-name" />
            <x-input-error class="mt-2" :messages="$errors->get('family')" />
        </div>

        <div>
            <x-input-label for="mobile" value="شماره موبایل" />
            <x-text-input wire:model="mobile" id="mobile" name="mobile" type="text" class="mt-1 block w-full" required autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('mobile')" />
        </div>

        <div>
            <x-input-label for="national_code" value="کد ملی" />
            <x-text-input wire:model="national_code" id="national_code" name="national_code" type="text" class="mt-1 block w-full" required />
            <x-input-error class="mt-2" :messages="$errors->get('national_code')" />
        </div>

        <div>
            <x-input-label for="education_field" value="رشته تحصیلی" />
            <select wire:model="education_field" id="education_field" name="education_field" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">انتخاب کنید</option>
                @foreach (EducationField::getActive() as $field)
                    <option value="{{ $field->name }}">{{ $field->name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('education_field')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg border border-blue-800 transition-colors shadow-sm">
                ذخیره تغییرات
            </button>

            <x-action-message class="me-3" on="profile-updated">
                با موفقیت ذخیره شد.
            </x-action-message>
        </div>
    </form>
</section>
