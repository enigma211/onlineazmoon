<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get registration status from cache or default to true
        $settings = Cache::get('site_settings', []);
        $enableRegistration = $settings['enable_registration'] ?? true;

        // If registration is disabled and trying to access registration page
        if (!$enableRegistration && ($request->is('admin/register') || $request->is('register'))) {
            abort(403, 'ثبت‌نام در حال حاضر غیرفعال است.');
        }

        // Check if user is trying to access admin panel
        if ($request->is('admin/*') && auth()->check()) {
            $user = auth()->user();
            $panel = \Filament\Facades\Filament::getCurrentPanel();
            
            // If user is logged in but cannot access admin panel, redirect to dashboard
            if (!$user->canAccessPanel($panel)) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
