<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use App\Services\SMS;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.guest')]
class ForgotPassword extends Component
{
    public $step = 1;
    public $mobile;
    public $otp;
    public $password;
    public $password_confirmation;
    public $errorMessage = '';

    public function sendOtp()
    {
        $this->validate([
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/i'],
        ], [
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.regex' => 'فرمت شماره موبایل معتبر نیست.',
        ]);

        $user = User::where('mobile', $this->mobile)->first();

        if (!$user) {
            $this->addError('mobile', 'کاربری با این شماره موبایل یافت نشد.');
            return;
        }

        // Generate a 4-digit OTP
        $code = rand(1000, 9999);
        
        // Cache the OTP for 3 minutes
        Cache::put("otp_{$this->mobile}", $code, now()->addMinutes(3));

        // Send OTP via MeliPayamak
        if (config('melipayamak.username') && config('melipayamak.password')) {
            SMS::sendOTP($this->mobile, (string) $code);
        } else {
            Log::info("OTP for {$this->mobile} is: {$code} (SMS skipped due to missing config)");
        }

        $this->step = 2;
        $this->errorMessage = '';
    }

    public function verifyOtp()
    {
        $this->validate([
            'otp' => 'required|numeric|digits:4',
        ], [
            'otp.required' => 'کد تایید الزامی است.',
            'otp.digits' => 'کد تایید باید 4 رقم باشد.',
        ]);

        $cachedOtp = Cache::get("otp_{$this->mobile}");

        if (!$cachedOtp || $cachedOtp != $this->otp) {
            $this->addError('otp', 'کد تایید نامعتبر یا منقضی شده است.');
            return;
        }

        $this->step = 3;
        $this->errorMessage = '';
    }

    public function resetPassword()
    {
        $this->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
            'password.confirmed' => 'رمز عبور و تایید آن مطابقت ندارند.',
        ]);

        $user = User::where('mobile', $this->mobile)->first();
        
        if ($user) {
            $user->password = Hash::make($this->password);
            $user->save();

            // Clear the OTP cache
            Cache::forget("otp_{$this->mobile}");

            session()->flash('status', 'رمز عبور شما با موفقیت تغییر کرد.');
            return redirect()->route('login');
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
