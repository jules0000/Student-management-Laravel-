<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrInstructorAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('admin')->check() && ! auth('instructor')->check() && ! auth('program_chair')->check()) {
            // Default to admin login.
            return redirect()->route('login');
        }

        return $next($request);
    }
}

