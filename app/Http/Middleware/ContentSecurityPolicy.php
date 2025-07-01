<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Define CSP directives
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'nonce-".$this->generateNonce()."'",
            "style-src 'self' https://fonts.googleapis.com",
            "img-src 'self' data: https:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self'",
            "frame-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            'block-all-mixed-content',
        ];

        // Only add upgrade-insecure-requests in production
        if (app()->environment('production')) {
            $csp[] = 'upgrade-insecure-requests';
        }

        // Add CSP header
        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', $csp)
        );

        return $response;
    }

    /**
     * Generate a random nonce for CSP.
     */
    private function generateNonce(): string
    {
        return base64_encode(random_bytes(16));
    }
}
