<div class="w-full">
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">فراموشی رمز عبور</h2>
        <p class="text-sm text-gray-600 mt-2">
            @if($step === 1)
                لطفا شماره موبایل خود را وارد کنید.
            @elseif($step === 2)
                کد 4 رقمی پیامک شده را وارد کنید.
            @else
                رمز عبور جدید خود را وارد کنید.
            @endif
        </p>
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit.prevent="{{ $step === 1 ? 'sendOtp' : ($step === 2 ? 'verifyOtp' : 'resetPassword') }}">
        
        <!-- Step 1: Mobile Number -->
        @if($step === 1)
            <div>
                <x-input-label for="mobile" value="شماره موبایل" />
                <x-text-input wire:model="mobile" id="mobile" class="block mt-1 w-full text-left" type="text" dir="ltr" placeholder="09xxxxxxxxx" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    بازگشت به ورود
                </a>

                <x-primary-button class="ms-3">
                    ارسال کد تایید
                </x-primary-button>
            </div>
        @endif

        <!-- Step 2: Verify OTP -->
        @if($step === 2)
            <div class="text-center mb-4 text-sm text-gray-600">
                کد تایید به شماره <strong class="text-gray-900" dir="ltr">{{ $mobile }}</strong> ارسال شد.
            </div>

            <div>
                <x-input-label for="otp" value="کد تایید 4 رقمی" />
                <x-text-input wire:model="otp" id="otp" class="block mt-1 w-full text-center text-xl tracking-widest font-bold" type="text" dir="ltr" maxlength="4" placeholder="----" required autofocus autocomplete="one-time-code" />
                <x-input-error :messages="$errors->get('otp')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <button type="button" wire:click="$set('step', 1)" class="underline text-sm text-gray-600 hover:text-gray-900">
                    تغییر شماره
                </button>

                <x-primary-button class="ms-3">
                    تایید کد
                </x-primary-button>
            </div>
        @endif

        <!-- Step 3: Reset Password -->
        @if($step === 3)
            <div>
                <x-input-label for="password" value="رمز عبور جدید" />
                <x-text-input wire:model="password" id="password" class="block mt-1 w-full text-left" type="password" dir="ltr" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password_confirmation" value="تکرار رمز عبور جدید" />
                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full text-left" type="password" dir="ltr" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-6">
                <x-primary-button>
                    تغییر رمز عبور
                </x-primary-button>
            </div>
        @endif

    </form>
</div>
