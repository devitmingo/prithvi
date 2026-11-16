<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and attach enterprise security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking by restricting framing to same-origin
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME-type sniffing attacks
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable cross-site scripting filter in legacy browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy: Send full URL for same-origin, domain-only for cross-origin HTTPS
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy: Restrict sensor/camera/mic access
        $response->headers->set('Permissions-Policy', 'geolocation=(self), microphone=()');

        return $response;
    }
}
