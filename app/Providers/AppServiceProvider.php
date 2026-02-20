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
        View::composer('*', static function ($view): void {
            static $shared = null;

            if ($shared === null) {
                $settings = SiteSettings::all();
                $siteDescription = config('app.description', '');

                if (array_key_exists('site_description', $settings)) {
                    $description = $settings['site_description'];
                    $siteDescription = is_string($description) ? trim($description) : '';
                }

                $shared = [
                    'siteSettings'    => $settings,
                    'siteName'        => $settings['site_name'] ?? config('app.name', 'سامانه آزمون‌ها'),
                    'siteDescription' => $siteDescription,
                    'siteLogoUrl'     => SiteSettings::logoUrl(),
                    'siteFaviconUrl'  => SiteSettings::faviconUrl(),
                ];
            }

            $view->with($shared);
        });
    }
}
