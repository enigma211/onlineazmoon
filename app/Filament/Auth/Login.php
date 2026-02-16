<?php

namespace App\Filament\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Http\Responses\Auth\LoginResponse as DefaultLoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

use Illuminate\Contracts\Support\Htmlable;
use Filament\Actions\Action;

class Login extends BaseLogin
{
    public function getHeading(): string | Htmlable
    {
        return 'ورود به سامانه';
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('ورود')
            ->submit('authenticate');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('national_code')
                    ->label('کد ملی')
                    ->required()
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1])
                    ->numeric()
                    ->length(10)
                    ->placeholder('کد ملی 10 رقمی'),
                $this->getPasswordFormComponent(),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $data = $this->form->getState();

            $user = \App\Models\User::where('national_code', $data['national_code'])->first();

            if (!$user || !\Illuminate\Support\Facades\Hash::check($data['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'data.national_code' => 'کد ملی یا رمز عبور اشتباه است.',
                ]);
            }

            // Check if user can access admin panel BEFORE logging in
            if (!$user->canAccessPanel(\Filament\Facades\Filament::getCurrentPanel())) {
                throw ValidationException::withMessages([
                    'data.national_code' => 'شما دسترسی به پنل مدیریت ندارید. لطفاً از صفحه اصلی وارد شوید.',
                ]);
            }

            Filament::auth()->login($user);

            session()->regenerate();

            return app(LoginResponse::class);

        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.national_code' => __('filament::login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }
    }
}
