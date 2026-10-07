<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach HTTP security headers.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Clickjacking protection
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME-type sniffing protection
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS Filter protection for legacy browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Information restriction
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict sensitive hardware features
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
