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
                <script src="https://cdn.jsdelivr.net/npm/jalaali-js@1.2.3/dist/jalaali.js"></script>',
            )
            ->renderHook(
                'panels::body.end',
                fn (): string => '<script>
(function() {
    var J_MONTHS = ["فروردین","اردیبهشت","خرداد","تیر","مرداد","شهریور","مهر","آبان","آذر","دی","بهمن","اسفند"];
    var J_WEEKDAYS = ["ش","ی","د","س","چ","پ","ج"];

    function waitForJalaali(cb) {
        if (typeof jalaali !== "undefined" && jalaali.toJalaali) { cb(); }
        else { setTimeout(function(){ waitForJalaali(cb); }, 150); }
    }

    function pad2(n) { return n < 10 ? "0" + n : "" + n; }

    function gregDateToJalali(date) {
        var y = date.getFullYear(), m = date.getMonth() + 1, d = date.getDate();
        return jalaali.toJalaali(y, m, d);
    }

    function jalaliFirstWeekday(jy, jm) {
        var g = jalaali.toGregorian(jy, jm, 1);
        return new Date(g.gy, g.gm - 1, g.gd).getDay();
    }

    function getAlpineComp(el) {
        if (!el) return null;
        var stack = el._x_dataStack;
        if (stack && stack.length) return stack[0];
        return null;
    }

    function patchPicker(el) {
        if (el._jPatch) return;
        var comp = getAlpineComp(el);
        if (!comp) return;
        el._jPatch = true;

        var hasTime = !!(comp.hasTime);

        /* ---- display text: show Jalali ---- */
        comp.setDisplayText = function() {
            var d = this.getSelectedDate ? this.getSelectedDate() : null;
            if (!d) { this.displayText = ""; return; }
            var dt = d.toDate ? d.toDate() : new Date(d);
            var j = gregDateToJalali(dt);
            var base = j.jy + "/" + pad2(j.jm) + "/" + pad2(j.jd);
            if (hasTime) {
                base += " " + pad2(d.hour ? d.hour() : 0) + ":" + pad2(d.minute ? d.minute() : 0);
            }
            this.displayText = base;
        };

        /* ---- month names: Jalali ---- */
        comp.setMonths = function() { this.months = J_MONTHS; };

        /* ---- weekday labels: Jalali ---- */
        comp.setDayLabels = function() { this.dayLabels = J_WEEKDAYS; };

        /* ---- days grid: based on Jalali month of focusedDate ---- */
        comp.setupDaysGrid = function() {
            if (!this.focusedDate) return;
            var dt = this.focusedDate.toDate ? this.focusedDate.toDate() : new Date(this.focusedDate);
            var j = gregDateToJalali(dt);
            var jy = j.jy, jm = j.jm;

            var offset = (jalaliFirstWeekday(jy, jm) - 6 + 7) % 7;
            var days   = jalaali.jalaaliMonthLength(jy, jm);

            this.emptyDaysInFocusedMonth = Array.from({length: offset}, function(_,i){ return i+1; });
            this.daysInFocusedMonth      = Array.from({length: days},   function(_,i){ return i+1; });

            /* keep focusedMonth/focusedYear as Jalali so the select/input show correct values */
            this.focusedMonth = jm - 1;
            this.focusedYear  = jy;
        };

        /* ---- day selection/hover: map Jalali day → Gregorian focusedDate ---- */
        function jalaliDayToGreg(comp, day) {
            var dt = comp.focusedDate.toDate ? comp.focusedDate.toDate() : new Date(comp.focusedDate);
            var j  = gregDateToJalali(dt);
            var g  = jalaali.toGregorian(j.jy, j.jm, day);
            return comp.focusedDate.year(g.gy).month(g.gm - 1).date(g.gd);
        }

        comp.setFocusedDay = function(day) {
            if (!this.focusedDate) return;
            this.focusedDate = jalaliDayToGreg(this, day);
        };

        comp.dayIsSelected = function(day) {
            var sel = this.getSelectedDate ? this.getSelectedDate() : null;
            if (!sel) return false;
            var jSel = gregDateToJalali(sel.toDate ? sel.toDate() : new Date(sel));
            var jFoc = gregDateToJalali(this.focusedDate.toDate ? this.focusedDate.toDate() : new Date(this.focusedDate));
            return jSel.jd === day && jSel.jm === jFoc.jm && jSel.jy === jFoc.jy;
        };

        comp.dayIsToday = function(day) {
            var now = new Date();
            var jNow = jalaali.toJalaali(now.getFullYear(), now.getMonth()+1, now.getDate());
            var jFoc = gregDateToJalali(this.focusedDate.toDate ? this.focusedDate.toDate() : new Date(this.focusedDate));
            return jNow.jd === day && jNow.jm === jFoc.jm && jNow.jy === jFoc.jy;
        };

        comp.selectDate = function(day) {
            if (day && this.focusedDate) {
                this.focusedDate = jalaliDayToGreg(this, day);
            }
            if (this.focusedDate) {
                var fd = this.focusedDate;
                this.state = fd.year() + "-" + pad2(fd.month()+1) + "-" + pad2(fd.date())
                           + " " + pad2(this.hour||0) + ":" + pad2(this.minute||0) + ":" + pad2(this.second||0);
                this.setDisplayText();
            }
            if (this.shouldCloseOnDateSelection !== false) {
                if (this.$refs && this.$refs.panel) this.$refs.panel.style.display = "none";
            }
        };

        /* ---- month/year dropdown changes: treat values as Jalali ---- */
        /* We intercept AFTER Alpine sets focusedDate.month()/year() by watching the panel DOM */
        el._jPanelObs = new MutationObserver(function() {
            /* After Alpine reacts to focusedMonth/focusedYear change, re-run setupDaysGrid */
            if (comp.focusedDate) {
                /* The watcher set focusedDate to a wrong Gregorian date; fix it back */
                var jm = (comp.focusedMonth || 0) + 1;
                var jy = comp.focusedYear || 1400;
                if (jm >= 1 && jm <= 12 && jy >= 1300) {
                    var g = jalaali.toGregorian(jy, jm, 1);
                    var corrected = comp.focusedDate.year(g.gy).month(g.gm-1).date(1);
                    if (corrected.month() !== comp.focusedDate.month() ||
                        corrected.year() !== comp.focusedDate.year()) {
                        comp.focusedDate = corrected;
                    }
                }
                comp.setupDaysGrid();
            }
        });
        var panel = el.querySelector(".fi-fo-date-time-picker-panel");
        if (panel) el._jPanelObs.observe(panel, {childList: true, subtree: true, characterData: true});

        /* ---- wrap togglePanelVisibility to re-apply Jalali grid on open ---- */
        var origToggle = comp.togglePanelVisibility ? comp.togglePanelVisibility.bind(comp) : null;
        if (origToggle) {
            comp.togglePanelVisibility = function() {
                origToggle();
                var self = this;
                setTimeout(function() { if (self.focusedDate) self.setupDaysGrid(); }, 30);
            };
        }

        /* ---- initial render ---- */
        comp.setMonths();
        comp.setDayLabels();
        if (comp.focusedDate) comp.setupDaysGrid();
        comp.setDisplayText();
    }

    function patchAll() {
        document.querySelectorAll(".fi-fo-date-time-picker").forEach(function(el) {
            if (!el._jPatch) {
                var comp = getAlpineComp(el);
                if (comp) patchPicker(el);
            }
        });
    }

    waitForJalaali(function() {
        var bodyObs = new MutationObserver(function() {
            document.querySelectorAll(".fi-fo-date-time-picker:not([data-jp])").forEach(function(el) {
                var comp = getAlpineComp(el);
                if (comp) { el.setAttribute("data-jp","1"); patchPicker(el); }
            });
        });
        bodyObs.observe(document.body, {childList: true, subtree: true});

        document.addEventListener("alpine:initialized", function(){ setTimeout(patchAll, 80); });
        document.addEventListener("livewire:navigated",  function(){ setTimeout(patchAll, 200); });
        setTimeout(patchAll, 600);
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
