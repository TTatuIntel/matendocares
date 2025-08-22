<?php

namespace App\Http\Middleware;

use Closure;

class CheckTempAccess
{
    public function handle($request, Closure $next)
    {
        // Allow if user is authenticated
        if (auth()->check()) {
            return $next($request);
        }

        // Check for temporary access
        if (session()->has('temp_access_user_id')) {
            if (now()->gt(session('temp_access_expires_at'))) {
                return redirect('/')->with('error', 'Temporary access has expired');
            }
            return $next($request);
        }

        return redirect('/')->with('error', 'Unauthorized access');
    }
}
