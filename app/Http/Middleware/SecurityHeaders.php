<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 * Adds security headers to protect against common web vulnerabilities
 */
final class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Type Options - Prevents MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Frame Options - Prevents clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // XSS Protection - Enables browser's XSS filtering
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy - Controls referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy - Controls browser features
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=(), usb=()');

        // Content Security Policy - Prevents XSS and other injection attacks
        $csp = $this->buildContentSecurityPolicy();
        $response->headers->set('Content-Security-Policy', $csp);

        // Strict Transport Security - Enforces HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Cache Control for sensitive pages
        if ($this->isSensitivePage($request)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }

    /**
     * Build Content Security Policy
     */
    private function buildContentSecurityPolicy(): string
    {
        $policies = [
            // Default source - only allow same origin
            "default-src 'self'",
            
            // Script sources - allow same origin and trusted CDNs
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com",
            
            // Style sources - allow same origin and trusted CDNs
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            
            // Font sources - allow same origin and Google Fonts
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
            
            // Image sources - allow same origin and data URIs
            "img-src 'self' data: https: blob:",
            
            // Connect sources - allow same origin and API endpoints
            "connect-src 'self' https://api.example.com wss://localhost:*",
            
            // Media sources - allow same origin and blob URLs
            "media-src 'self' blob:",
            
            // Object sources - block all
            "object-src 'none'",
            
            // Base URI - restrict to same origin
            "base-uri 'self'",
            
            // Form action - restrict to same origin
            "form-action 'self'",
            
            // Frame ancestors - block all (prevents embedding)
            "frame-ancestors 'none'",
            
            // Upgrade insecure requests - upgrade HTTP to HTTPS
            "upgrade-insecure-requests",
        ];

        return implode('; ', $policies);
    }

    /**
     * Check if the current page is sensitive and should not be cached
     */
    private function isSensitivePage(Request $request): bool
    {
        $sensitivePaths = [
            '/login',
            '/register',
            '/password',
            '/profile',
            '/admin',
            '/dashboard',
        ];

        $currentPath = $request->path();

        foreach ($sensitivePaths as $path) {
            if (str_starts_with($currentPath, $path)) {
                return true;
            }
        }

        return false;
    }
} 