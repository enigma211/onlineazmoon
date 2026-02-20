<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
