<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Cache;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'تنظیمات';
    
    protected static ?string $modelLabel = 'تنظیمات';
    
    protected static ?string $pluralModelLabel = 'تنظیمات';
    
    protected static ?string $title = 'تنظیمات سیستم';
    
    protected static string $view = 'filament.pages.settings';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $settings = Cache::get('site_settings', []);
        $this->form->fill([
            'site_name' => $settings['site_name'] ?? config('app.name', 'سامانه آزمون‌ها'),
            'site_description' => $settings['site_description'] ?? config('app.description', 'سامانه آزمون‌های دفتر مقررات ملی ساختمان'),
            'contact_email' => $settings['contact_email'] ?? config('mail.from.address', 'info@example.com'),
            'enable_registration' => $settings['enable_registration'] ?? true,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تنظیمات اصلی سایت')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label('نام سایت')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('site_description')
                            ->label('توضیحات سایت')
                            ->rows(3)
                            ->maxLength(500),
                        Forms\Components\TextInput::make('contact_email')
                            ->label('ایمیل تماس')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),
                    
                Forms\Components\Section::make('تنظیمات کاربران')
                    ->schema([
                        Forms\Components\Toggle::make('enable_registration')
                            ->label('فعال بودن ثبت‌نام کاربران')
                            ->helperText('در صورت غیرفعال بودن، کاربران جدید نمی‌توانند در سایت ثبت‌نام کنند')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        
        // Save to cache or database
        Cache::put('site_settings', $data);
        
        // You can also save to .env file or database table
        // For now, we'll use cache
        
        $this->getSavedNotification()?->send();
    }
    
    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->title('تنظیمات ذخیره شد')
            ->success()
            ->body('تنظیمات سیستم با موفقیت ذخیره شد.');
    }
    
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('ذخیره تنظیمات')
                ->action('save')
                ->color('primary'),
        ];
    }
}
