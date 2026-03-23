<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardAnyRoleAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            auth('admin')->check()
            || auth('student')->check()
            || auth('instructor')->check()
            || auth('program_chair')->check()
        ) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}

