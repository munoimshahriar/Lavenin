<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // The Filament admin (/manage) relies on inline scripts/styles from Livewire, so it gets the headers
        // above but not this CSP. The storefront gets a nonce-based CSP.
        if (! $request->is('manage', 'manage/*', 'livewire/*') && ! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $this->storefrontCsp(Vite::cspNonce()));
        }

        return $response;
    }

    private function storefrontCsp(string $nonce): string
    {
        $dev = app()->isLocal() ? ' http://localhost:5173 ws://localhost:5173' : '';

        return implode('; ', [
            "default-src 'self'",
            // 'unsafe-eval' is required by Alpine.js (standard build) to evaluate x-data/x-on expressions.
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' https://www.googletagmanager.com https://connect.facebook.net{$dev}",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com{$dev}",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: blob: https://www.facebook.com https://*.google-analytics.com https://*.googletagmanager.com",
            "connect-src 'self' https://*.google-analytics.com https://*.analytics.google.com https://www.facebook.com{$dev}",
            'frame-src https://www.youtube-nocookie.com https://www.youtube.com https://www.facebook.com',
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
