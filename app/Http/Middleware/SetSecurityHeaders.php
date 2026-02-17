<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Set Content Security Policy that allows Livewire to work
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self' http: https: data: blob: 'unsafe-inline'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https: http:; " .
            "style-src 'self' 'unsafe-inline' https: http:; " .
            "img-src 'self' data: https: http:; " .
            "font-src 'self' data: https: http:; " .
            "connect-src 'self' https: http: ws: wss:;"
        );

        return $response;
    }
}
