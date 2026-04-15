<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * SecurityHeaders Middleware
 * 
 * Adds security headers to all HTTP responses to protect against common web vulnerabilities:
 * - X-Frame-Options: Prevents clickjacking attacks
 * - X-Content-Type-Options: Prevents MIME-type sniffing
 * - X-XSS-Protection: Enables browser's XSS filter
 * - Referrer-Policy: Controls referrer information
 * - Permissions-Policy: Controls browser features
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Prevent clickjacking by disallowing iframe embedding except same origin
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS filter in older browsers (deprecated but still useful for legacy support)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control referrer information sent with requests
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable dangerous browser features
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Strict-Transport-Security (HSTS) - Only enable if using HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
