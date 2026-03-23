<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminAuth::class,
            'student' => \App\Http\Middleware\StudentAuth::class,
            'instructor' => \App\Http\Middleware\InstructorAuth::class,
            'admin_or_instructor' => \App\Http\Middleware\AdminOrInstructorAuth::class,
            'admin_or_program_chair' => \App\Http\Middleware\AdminOrProgramChairAuth::class,
            'program_chair' => \App\Http\Middleware\ProgramChairAuth::class,
            'dashboard_any_role' => \App\Http\Middleware\DashboardAnyRoleAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
