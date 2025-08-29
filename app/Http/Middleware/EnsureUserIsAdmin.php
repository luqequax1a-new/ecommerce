<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Check if user has admin role
        $user = $request->user();
        
        // For now, we'll check if user has 'admin' role or email contains 'admin'
        // In a real application, you would have a proper role system
        if (!$this->isAdmin($user)) {
            return response()->json([
                'message' => 'Bu işlem için admin yetkisi gereklidir.',
                'error' => 'Insufficient permissions'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user is admin
     */
    private function isAdmin($user): bool
    {
        // Check multiple conditions for admin access
        return $user->role === 'admin' 
            || $user->role === 'super_admin'
            || $user->is_admin === true
            || str_contains($user->email, 'admin')
            || $user->email === 'admin@example.com';
    }
}