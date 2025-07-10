<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip cache headers for authenticated users to ensure fresh content
        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        }

        // Set cache headers based on route type
        $path = $request->path();
        
        // API responses - short cache for public data
        if (str_starts_with($path, 'api/')) {
            $response->headers->set('Cache-Control', 'public, max-age=600, s-maxage=300'); // 10 minutes, CDN 5 minutes
            $response->headers->set('ETag', '"' . md5($response->getContent()) . '"');
            return $response;
        }

        // Static pages - longer cache
        if (in_array($path, ['', 'welcome', 'about', 'contact'])) {
            $response->headers->set('Cache-Control', 'public, max-age=7200, s-maxage=3600'); // 2 hours, CDN 1 hour
            $response->headers->set('ETag', '"' . md5($response->getContent()) . '"');
            return $response;
        }

        // Assets and media - aggressive caching
        if (str_starts_with($path, 'js/') || str_starts_with($path, 'css/') || str_starts_with($path, 'images/') || str_starts_with($path, 'media/')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable'); // 1 year
            return $response;
        }

        // Dynamic content - no cache
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
} 