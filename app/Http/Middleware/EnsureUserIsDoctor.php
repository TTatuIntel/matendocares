<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsDoctor
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->doctor) {
            // Return JSON for AJAX/API requests, otherwise redirect
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor privileges required'
                ], 403);
            }

            return redirect()->route('home')->with('error', 'Doctor privileges required');
        }

        return $next($request);
    }
}
