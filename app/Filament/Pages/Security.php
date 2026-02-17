<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Cache;

class Security extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?string $navigationParentItem = 'تنظیمات';
    
    protected static ?string $navigationLabel = 'امنیت';
    
    protected static ?string $modelLabel = 'امنیت';
    
    protected static ?string $pluralModelLabel = 'امنیت';
    
    protected static ?string $title = 'تنظیمات امنیتی';
    
    protected static string $view = 'filament.pages.security';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'session_timeout' => config('session.lifetime', 120),
            'max_login_attempts' => config('auth.max_attempts', 5),
            'lockout_duration' => config('auth.lockout_duration', 1),
            'require_password_change' => config('security.require_password_change', false),
            'password_min_length' => config('security.password_min_length', 8),
            'password_require_numbers' => config('security.password_require_numbers', true),
            'password_require_symbols' => config('security.password_require_symbols', true),
            'password_require_uppercase' => config('security.password_require_uppercase', true),
            'password_require_lowercase' => config('security.password_require_lowercase', true),
            'log_failed_attempts' => config('security.log_failed_attempts', true),
            'ip_whitelist' => config('security.ip_whitelist', ''),
            'ip_blacklist' => config('security.ip_blacklist', ''),
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تنظیمات جلسه کاربری')
                    ->schema([
                        Forms\Components\TextInput::make('session_timeout')
                            ->label('زمان انقضای جلسه (دقیقه)')
                            ->numeric()
                            ->required()
                            ->default(120)
                            ->helperText('پس از این مدت، کاربر باید مجدداً وارد شود'),
                        Forms\Components\TextInput::make('max_login_attempts')
                            ->label('حداکثر تلاش برای ورود')
                            ->numeric()
                            ->required()
                            ->default(5)
                            ->helperText('تعداد دفعات مجاز برای تلاش ناموفق ورود'),
                        Forms\Components\TextInput::make('lockout_duration')
                            ->label('مدت زمان قفل (دقیقه)')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->helperText('مدت زمان قفل شدن حساب پس از تلاش‌های ناموفق'),
                    ]),
                    
                Forms\Components\Section::make('تنظیمات رمز عبور')
                    ->schema([
                        Forms\Components\Toggle::make('require_password_change')
                            ->label('الزامی بودن تغییر رمز عبور پس از اولین ورود')
                            ->default(false),
                        Forms\Components\TextInput::make('password_min_length')
                            ->label('حداقل طول رمز عبور')
                            ->numeric()
                            ->required()
                            ->default(8),
                        Forms\Components\CheckboxList::make('password_requirements')
                            ->label('الزامات رمز عبور')
                            ->options([
                                'password_require_numbers' => 'شامل اعداد',
                                'password_require_symbols' => 'شامل نمادها (!@#$%^&*)',
                                'password_require_uppercase' => 'شامل حروف بزرگ',
                                'password_require_lowercase' => 'شامل حروف کوچک',
                            ])
                            ->columns(2),
                    ]),
                    
                Forms\Components\Section::make('تنظیمات پیشرفته')
                    ->schema([
                        Forms\Components\Toggle::make('log_failed_attempts')
                            ->label('ثبت تلاش‌های ناموفق ورود')
                            ->default(true),
                        Forms\Components\Textarea::make('ip_whitelist')
                            ->label('لیست IP مجاز (هر IP در یک خط)')
                            ->rows(3)
                            ->placeholder('192.168.1.1&#10;10.0.0.1')
                            ->helperText('فقط این IPها می‌توانند وارد شوند. خالی بگذارید برای همه'),
                        Forms\Components\Textarea::make('ip_blacklist')
                            ->label('لیست IP مسدود (هر IP در یک خط)')
                            ->rows(3)
                            ->placeholder('192.168.1.100&#10;10.0.0.100')
                            ->helperText('این IPها نمی‌توانند وارد شوند'),
                    ]),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        
        // Process password requirements
        $passwordRequirements = $data['password_requirements'] ?? [];
        $data['password_require_numbers'] = in_array('password_require_numbers', $passwordRequirements);
        $data['password_require_symbols'] = in_array('password_require_symbols', $passwordRequirements);
        $data['password_require_uppercase'] = in_array('password_require_uppercase', $passwordRequirements);
        $data['password_require_lowercase'] = in_array('password_require_lowercase', $passwordRequirements);
        
        // Remove the array field
        unset($data['password_requirements']);
        
        // Save to cache or database
        Cache::put('security_settings', $data);
        
        $this->getSavedNotification()?->send();
    }
    
    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->title('تنظیمات امنیتی ذخیره شد')
            ->success()
            ->body('تنظیمات امنیتی با موفقیت ذخیره شد.');
    }
    
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('ذخیره تنظیمات امنیتی')
                ->action('save')
                ->color('primary'),
        ];
    }
}
