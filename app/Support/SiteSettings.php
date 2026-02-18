<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SiteSettings
{
    public static function all(): array
    {
        return Cache::rememberForever('site_settings', function (): array {
            if (!DB::getSchemaBuilder()->hasTable('site_settings')) {
                return [];
            }

            return DB::table('site_settings')
                ->pluck('value', 'key')
                ->map(function ($value) {
                    $decoded = json_decode($value, true);

                    return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
                })
                ->toArray();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function logoUrl(): string
    {
        $logoPath = self::get('site_logo');

        if (is_string($logoPath) && $logoPath !== '') {
            return Storage::disk('public')->url($logoPath);
        }

        return asset('images/logo.png');
    }

    public static function faviconUrl(): string
    {
        $faviconPath = self::get('site_favicon');

        if (is_string($faviconPath) && $faviconPath !== '') {
            return Storage::disk('public')->url($faviconPath);
        }

        return asset('favicon.ico');
    }
}
