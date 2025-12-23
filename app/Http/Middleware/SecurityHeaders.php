<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // 1) anty-clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 2) anty MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 3) mniej wycieku referera
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $csp = implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "img-src 'self' data: https:",
            "font-src 'self' data: https:",
            "style-src 'self' 'unsafe-inline' https:",  // Bootstrap często wymaga inline style
            "script-src 'self' https:",                  // jeśli masz CDN, dopisz domeny
            "connect-src 'self' https:",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        // 4) ogranicz “feature policy”
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // 5) HSTS (tylko jeśli masz HTTPS!)
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            // jak masz pewność, że wszystko działa na HTTPS:
            // . '; preload'
            );
        }

        return $response;
    }
}

