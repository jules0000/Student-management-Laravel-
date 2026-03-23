<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrProgramChairAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('admin')->check() && ! auth('program_chair')->check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

