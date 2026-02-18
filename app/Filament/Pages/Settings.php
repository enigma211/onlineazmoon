<?php

namespace App\Filament\Pages;

use App\Support\SiteSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'تنظیمات';
    
    protected static ?string $navigationLabel = 'تنظیمات';
    
    protected static ?string $modelLabel = 'تنظیمات';
    
    protected static ?string $pluralModelLabel = 'تنظیمات';
    
    protected static ?string $title = 'تنظیمات سیستم';
    
    protected static string $view = 'filament.pages.settings';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $settings = $this->getSiteSettings();

        $siteDescription = config('app.description', '');
        if (array_key_exists('site_description', $settings)) {
            $description = $settings['site_description'];
            $siteDescription = is_string($description) ? $description : '';
        }

        $this->form->fill([
            'site_name' => $settings['site_name'] ?? config('app.name', 'سامانه آزمون‌ها'),
            'site_description' => $siteDescription,
            'enable_registration' => $settings['enable_registration'] ?? true,
            'site_logo' => $settings['site_logo'] ?? null,
            'site_favicon' => $settings['site_favicon'] ?? null,
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
                    ]),

                Forms\Components\Section::make('هویت بصری سایت')
                    ->schema([
                        Forms\Components\FileUpload::make('site_logo')
                            ->label('لوگو سایت')
                            ->disk('public')
                            ->directory('settings')
                            ->image()
                            ->imageEditor()
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                            ->helperText('این لوگو در صفحات اصلی، ورود/ثبت‌نام و داشبورد کاربر نمایش داده می‌شود.'),
                        Forms\Components\FileUpload::make('site_favicon')
                            ->label('Favicon سایت')
                            ->disk('public')
                            ->directory('settings')
                            ->image()
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'])
                            ->helperText('این آیکن در تب مرورگر نمایش داده می‌شود.'),
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

        foreach ($data as $key => $value) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => json_encode($value, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        Cache::forever('site_settings', $data);
        
        $this->getSavedNotification()?->send();
    }

    protected function getSiteSettings(): array
    {
        return SiteSettings::all();
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
