<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to prevent browser caching of HTML responses.
 * This ensures browsers always fetch fresh HTML with correct asset URLs.
 */
final class NoCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HTML Responses should NOT be cached, only assets, due to the nature of this Application
        $contentType = $response->headers->get('Content-Type', '');
        if (str_contains($contentType, 'text/html') || $request->is('/')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            $response->headers->set('Surrogate-Control', 'no-store');
        }

        return $response;
    }
}
