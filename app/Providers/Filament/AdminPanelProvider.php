<?php

namespace App\Providers\Filament;

use App\Support\SiteSettings;
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
use Illuminate\Support\HtmlString;
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
            ->brandName(fn (): string => SiteSettings::get('site_name', config('app.name', 'سامانه آزمون‌ها')))
            ->brandLogo(fn (): HtmlString => new HtmlString('<img src="' . e(SiteSettings::logoUrl()) . '" alt="logo" style="height: 2rem; width: auto; object-fit: contain;">'))
            ->favicon(fn (): string => SiteSettings::faviconUrl())
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

                    .fi-topbar .fi-avatar,
                    .fi-topbar .fi-user-menu-trigger .fi-avatar,
                    .fi-user-menu .fi-avatar,
                    .fi-avatar,
                    .fi-avatar.fi-color-custom {
                        background-color: #8b5cf6 !important;
                        background-image: none !important;
                        color: #ffffff !important;
                        border: 1px solid #7c3aed !important;
                    }

                    .dark .fi-topbar .fi-avatar,
                    .dark .fi-topbar .fi-user-menu-trigger .fi-avatar,
                    .dark .fi-user-menu .fi-avatar,
                    .dark .fi-avatar,
                    .dark .fi-avatar.fi-color-custom {
                        background-color: #a78bfa !important;
                        background-image: none !important;
                        color: #ffffff !important;
                        border-color: #8b5cf6 !important;
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

                </style>
                <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/moment-jalaali@0.10.0/build/moment-jalaali.js"></script>',
            )
            ->renderHook(
                'panels::body.end',
                fn (): string => '<script>
(function() {
    var jMonths = ["فروردین","اردیبهشت","خرداد","تیر","مرداد","شهریور","مهر","آبان","آذر","دی","بهمن","اسفند"];
    var jWeekdaysShort = ["ش","ی","د","س","چ","پ","ج"];

    function waitForLibs(cb) {
        if (typeof moment !== "undefined" && typeof moment.fn.jYear !== "undefined") {
            cb();
        } else {
            setTimeout(function() { waitForLibs(cb); }, 200);
        }
    }

    function toJalali(gregStr) {
        if (!gregStr) return "";
        var m = moment(gregStr, ["YYYY-MM-DD HH:mm:ss", "YYYY-MM-DDTHH:mm:ss", "YYYY-MM-DD"]);
        if (!m.isValid()) return gregStr;
        return m.format("jYYYY/jMM/jDD");
    }

    function toJalaliWithTime(gregStr) {
        if (!gregStr) return "";
        var m = moment(gregStr, ["YYYY-MM-DD HH:mm:ss", "YYYY-MM-DDTHH:mm:ss", "YYYY-MM-DD"]);
        if (!m.isValid()) return gregStr;
        return m.format("jYYYY/jMM/jDD HH:mm");
    }

    function patchAlpineComponent(el) {
        if (el._jalaliPatched) return;
        el._jalaliPatched = true;

        var comp = el._x_dataStack && el._x_dataStack[0];
        if (!comp) return;

        var hasTime = comp.hasTime !== undefined ? comp.hasTime : false;

        comp.setMonths = function() {
            this.months = jMonths;
        };

        comp.setDayLabels = function() {
            this.dayLabels = jWeekdaysShort;
        };

        comp.setDisplayText = function() {
            var date = this.getSelectedDate ? this.getSelectedDate() : null;
            if (!date) { this.displayText = ""; return; }
            var gregStr = date.format ? date.format("YYYY-MM-DD HH:mm:ss") : String(date);
            this.displayText = hasTime ? toJalaliWithTime(gregStr) : toJalali(gregStr);
        };

        comp.setupDaysGrid = function() {
            if (!this.focusedDate) return;
            var jDate = moment(this.focusedDate.toDate ? this.focusedDate.toDate() : this.focusedDate);
            var jYear = parseInt(jDate.format("jYYYY"));
            var jMonth = parseInt(jDate.format("jM")) - 1;

            var firstOfMonth = moment(jYear + "/" + (jMonth + 1) + "/1", "jYYYY/jM/jD");
            var firstDayOfMonth = firstOfMonth.day();
            var offset = (firstDayOfMonth - 6 + 7) % 7;

            var daysCount = jMonth < 6 ? 31 : (jMonth < 11 ? 30 : (moment.jIsLeapYear(jYear) ? 30 : 29));

            this.emptyDaysInFocusedMonth = Array.from({length: offset}, function(_, i) { return i + 1; });
            this.daysInFocusedMonth = Array.from({length: daysCount}, function(_, i) { return i + 1; });

            this.focusedMonth = jMonth;
            this.focusedYear = jYear;
        };

        comp.dayIsSelected = function(day) {
            var selected = this.getSelectedDate ? this.getSelectedDate() : null;
            if (!selected) return false;
            var jSel = moment(selected.toDate ? selected.toDate() : selected);
            var jFoc = moment(this.focusedDate ? (this.focusedDate.toDate ? this.focusedDate.toDate() : this.focusedDate) : new Date());
            return parseInt(jSel.format("jD")) === day &&
                   parseInt(jSel.format("jM")) - 1 === parseInt(jFoc.format("jM")) - 1 &&
                   parseInt(jSel.format("jYYYY")) === parseInt(jFoc.format("jYYYY"));
        };

        comp.dayIsToday = function(day) {
            var today = moment();
            var jFoc = moment(this.focusedDate ? (this.focusedDate.toDate ? this.focusedDate.toDate() : this.focusedDate) : new Date());
            return parseInt(today.format("jD")) === day &&
                   parseInt(today.format("jM")) - 1 === parseInt(jFoc.format("jM")) - 1 &&
                   parseInt(today.format("jYYYY")) === parseInt(jFoc.format("jYYYY"));
        };

        comp.selectDate = function(day) {
            if (day) {
                var jFoc = moment(this.focusedDate ? (this.focusedDate.toDate ? this.focusedDate.toDate() : this.focusedDate) : new Date());
                var jYear = parseInt(jFoc.format("jYYYY"));
                var jMonth = parseInt(jFoc.format("jM"));
                var newDate = moment(jYear + "/" + jMonth + "/" + day, "jYYYY/jM/jD");
                if (newDate.isValid()) {
                    this.focusedDate = newDate;
                }
            }
            if (this.focusedDate) {
                var fd = this.focusedDate;
                var gregStr = (fd.format ? fd.format("YYYY-MM-DD") : moment(fd).format("YYYY-MM-DD")) + " " + (this.hour || 0) + ":" + (this.minute || 0) + ":" + (this.second || 0);
                this.state = moment(gregStr, "YYYY-MM-DD H:m:s").format("YYYY-MM-DD HH:mm:ss");
                this.setDisplayText();
            }
            if (this.shouldCloseOnDateSelection !== false) {
                this.$refs && this.$refs.panel && this.$refs.panel.style && (this.$refs.panel.style.display = "none");
            }
        };

        comp.setMonths();
        comp.setDayLabels();
        if (comp.focusedDate) comp.setupDaysGrid();
        comp.setDisplayText();
    }

    function patchAllPickers() {
        document.querySelectorAll(".fi-fo-date-time-picker").forEach(function(el) {
            if (!el._jalaliPatched && el._x_dataStack) {
                patchAlpineComponent(el);
            }
        });
    }

    waitForLibs(function() {
        var observer = new MutationObserver(function() {
            document.querySelectorAll(".fi-fo-date-time-picker:not([data-jalali-patched])").forEach(function(el) {
                if (el._x_dataStack) {
                    el.setAttribute("data-jalali-patched", "1");
                    patchAlpineComponent(el);
                }
            });
        });
        observer.observe(document.body, { childList: true, subtree: true, attributes: false });

        document.addEventListener("alpine:initialized", function() {
            setTimeout(patchAllPickers, 100);
        });

        setTimeout(patchAllPickers, 500);

        document.addEventListener("livewire:navigated", function() { setTimeout(patchAllPickers, 300); });
    });
})();
</script>',
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
