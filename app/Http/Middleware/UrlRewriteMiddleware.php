<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\UrlRewrite;

class UrlRewriteMiddleware
{
    /**
     * Handle an incoming request and check for URL rewrites
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        // Skip middleware for admin routes, API routes, and static assets
        if ($this->shouldSkipMiddleware($request)) {
            return $next($request);
        }

        $currentPath = $request->getPathInfo();
        
        // Skip if it's already a valid route (no 404)
        $response = $next($request);
        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        // Check for URL rewrite
        $rewrite = $this->findUrlRewrite($currentPath);
        
        if ($rewrite) {
            // Log the redirect for analytics
            $this->logRedirect($currentPath, $rewrite->new_path, $request);
            
            // Increment hit count
            $rewrite->increment('hit_count');
            $rewrite->touch('last_accessed_at');
            
            // Perform 301 redirect
            return redirect($rewrite->new_path, 301)
                ->header('X-Redirect-Reason', 'url-rewrite')
                ->header('X-Original-URL', $currentPath);
        }

        // If no rewrite found, continue with 404 response
        return $response;
    }

    /**
     * Determine if middleware should be skipped for this request
     */
    private function shouldSkipMiddleware(Request $request): bool
    {
        $path = $request->getPathInfo();
        
        // Skip for admin routes
        if (str_starts_with($path, '/admin')) {
            return true;
        }
        
        // Skip for API routes
        if (str_starts_with($path, '/api')) {
            return true;
        }
        
        // Skip for static assets
        $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), $staticExtensions)) {
            return true;
        }
        
        // Skip for Laravel system routes
        $systemPaths = ['/telescope', '/horizon', '/debugbar', '/clockwork'];
        foreach ($systemPaths as $systemPath) {
            if (str_starts_with($path, $systemPath)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Find URL rewrite using cache for performance
     */
    private function findUrlRewrite(string $path): ?UrlRewrite
    {
        // Cache key for this specific path
        $cacheKey = 'url_rewrite:' . md5($path);
        
        return Cache::remember($cacheKey, 3600, function () use ($path) {
            return UrlRewrite::where('old_path', $path)
                ->where('is_active', true)
                ->first();
        });
    }

    /**
     * Log redirect for analytics and debugging
     */
    private function logRedirect(string $oldUrl, string $newUrl, Request $request): void
    {
        Log::info('URL Redirect', [
            'old_url' => $oldUrl,
            'new_url' => $newUrl,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'referer' => $request->header('referer'),
            'timestamp' => now()->toISOString()
        ]);
    }
}