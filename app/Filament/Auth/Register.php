<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Form;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Actions\Action;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;

class Register extends BaseRegister
{
    public function getHeading(): string | Htmlable
    {
        return 'ثبت نام در سامانه';
    }

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label('ثبت نام')
            ->submit('register');
    }

    protected function getRedirectUrl(): string
    {
        return route('dashboard');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('نام')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label('نام خانوادگی')
                    ->required()
                    ->maxLength(255),
                TextInput::make('mobile')
                    ->label('شماره موبایل')
                    ->required()
                    ->tel()
                    ->regex('/^09[0-9]{9}$/')
                    ->unique('users', 'mobile')
                    ->validationMessages([
                        'regex' => 'شماره موبایل باید با 09 شروع شده و 11 رقم باشد.',
                        'unique' => 'این شماره موبایل قبلاً ثبت شده است.',
                    ]),
                TextInput::make('national_code')
                    ->label('کد ملی')
                    ->required()
                    ->numeric()
                    ->length(10)
                    ->unique('users', 'national_code')
                    ->validationMessages([
                        'length' => 'کد ملی باید دقیقاً 10 رقم باشد.',
                        'unique' => 'این کد ملی قبلاً ثبت شده است.',
                    ]),
                TextInput::make('password')
                    ->label('رمز عبور')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->same('passwordConfirmation'),
                TextInput::make('passwordConfirmation')
                    ->label('تکرار رمز عبور')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->dehydrated(false),
            ]);
    }
}
