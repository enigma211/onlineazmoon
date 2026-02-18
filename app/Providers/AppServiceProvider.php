<?php

namespace App\Providers;

use App\Support\SiteSettings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            $settings = SiteSettings::all();

            $view->with('siteSettings', $settings);
            $view->with('siteName', $settings['site_name'] ?? config('app.name', 'سامانه آزمون‌ها'));
            $view->with('siteDescription', $settings['site_description'] ?? config('app.description', 'سامانه آزمون‌های دفتر مقررات ملی ساختمان'));
            $view->with('siteLogoUrl', SiteSettings::logoUrl());
            $view->with('siteFaviconUrl', SiteSettings::faviconUrl());
        });
    }
}
