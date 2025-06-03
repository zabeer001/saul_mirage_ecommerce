<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated via API guard and has admin role
        if (Auth::guard('api')->check() && Auth::guard('api')->user()->role === 'admin') {
            return $next($request);
        }

        // Return unauthorized response if user is not admin
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: Admin access required.'
        ], 403);
    }
}