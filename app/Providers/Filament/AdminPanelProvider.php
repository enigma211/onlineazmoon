<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Auth\Login::class)
            ->font('Vazirmatn', asset('fonts/vazirmatn.css'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => '<style>
                    body {
                        font-family: "Vazirmatn", sans-serif;
                    }
                    .fi-sidebar {
                        text-align: right;
                    }
                    .fi-sidebar .fi-sidebar-item,
                    .fi-sidebar .fi-sidebar-group-button {
                        text-align: right;
                    }

                    @media (max-width: 1023px) {
                        .fi-topbar-open-sidebar-btn {
                            display: inline-flex !important;
                        }

                        .fi-sidebar {
                            right: 0;
                            left: auto;
                            max-width: 20rem;
                            width: 85vw;
                        }

                        .fi-main {
                            width: 100%;
                        }
                    }
                </style>',
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
