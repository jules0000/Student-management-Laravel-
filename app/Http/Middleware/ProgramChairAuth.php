<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgramChairAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('program_chair')->check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

