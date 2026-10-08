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
        // Trust all proxies — required for Render's load balancer to pass HTTPS correctly
        $middleware->trustProxies(at: '*');

        // Authenticated admins hitting /login must not go to "/" (root redirects back to login → redirect loop)
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));

        $middleware->alias([
            'role'         => \App\Http\Middleware\EnsureUserHasRole::class,
            'portal.auth'  => \App\Http\Middleware\EnsurePortalAuth::class,
            'staff.auth'   => \App\Http\Middleware\EnsureStaffPortalAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
