<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Trait for handling HTTP headers
 */
trait HandlesHttpHeaders
{
    /**
     * Add security headers to the response.
     *
     * @param SymfonyResponse $response The response to modify
     * @return SymfonyResponse The modified response
     */
    protected function addSecurityHeaders(SymfonyResponse $response): SymfonyResponse
    {
        // Content Security Policy
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'"
        );
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // Enable XSS protection in browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Strict Transport Security (only in production)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        
        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Feature Policy / Permissions Policy
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(self), accelerometer=(), gyroscope=()'
        );
        
        return $response;
    }
    
    /**
     * Add cache control headers to the response.
     *
     * @param SymfonyResponse $response The response to modify
     * @param int $maxAge Max age in seconds
     * @param bool $public Whether the response is public or private
     * @return SymfonyResponse The modified response
     */
    protected function addCacheHeaders(SymfonyResponse $response, int $maxAge = 0, bool $public = false): SymfonyResponse
    {
        $cacheControl = $public ? 'public' : 'private';
        
        if ($maxAge <= 0) {
            // No caching
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        } else {
            // Allow caching with specified max age
            $response->headers->set(
                'Cache-Control',
                "$cacheControl, max-age=$maxAge, s-maxage=$maxAge"
            );
            
            // Set expires header
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
        }
        
        return $response;
    }
    
    /**
     * Determine if the request is cacheable.
     *
     * @param Request $request The request to check
     * @return bool Whether the request is cacheable
     */
    protected function isCacheableRequest(Request $request): bool
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return false;
        }
        
        // Don't cache authenticated requests
        if ($request->user()) {
            return false;
        }
        
        // Don't cache requests with query parameters
        if (count($request->query()) > 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get appropriate cache settings based on the route.
     *
     * @param Request $request The request to check
     * @return array{maxAge: int, public: bool} Cache settings
     */
    protected function getCacheSettings(Request $request): array
    {
        $path = $request->path();
        
        // Static assets can be cached longer
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff2?|ttf|eot)$/i', $path)) {
            return [
                'maxAge' => 604800, // 1 week
                'public' => true,
            ];
        }
        
        // Public pages can be cached briefly
        if (in_array($path, ['/', 'about', 'contact', 'privacy', 'terms'])) {
            return [
                'maxAge' => 300, // 5 minutes
                'public' => true,
            ];
        }
        
        // Default: no caching
        return [
            'maxAge' => 0,
            'public' => false,
        ];
    }
}