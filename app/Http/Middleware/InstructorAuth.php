<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstructorAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('instructor')->check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

