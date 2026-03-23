<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('student')->check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

