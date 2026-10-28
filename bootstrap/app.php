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

        // Redirect authenticated web users to correct dashboard based on role
        // Never redirect owner panel users — they use a separate guard
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('owner/*') || $request->is('owner')) {
                return route('owner.dashboard');
            }
            // After login, send each role to their own dashboard
            $user = $request->user();
            if ($user && $user->role === 'lecturer') {
                return route('lecturer.dashboard');
            }
            return route('admin.dashboard');
        });

        // When an unauthenticated user tries to access a protected page,
        // redirect to the school's login page (not the generic /login → /website/portal)
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            $slug = session('tenant_slug') ?? $request->query('tenant');
            if ($slug) {
                return route('school.admin.login', $slug);
            }
            return route('website.portal');
        });

        $middleware->alias([
            'role'         => \App\Http\Middleware\EnsureUserHasRole::class,
            'portal.auth'  => \App\Http\Middleware\EnsurePortalAuth::class,
            'staff.auth'   => \App\Http\Middleware\EnsureStaffPortalAuth::class,
            'owner.auth'   => \App\Http\Middleware\EnsureOwnerAuth::class,
            'tenant'       => \App\Http\Middleware\TenantMiddleware::class,
            'feature'      => \App\Http\Middleware\FeatureMiddleware::class,
            'buffer'       => \App\Http\Middleware\BufferOutput::class,
        ]);

        // Add output buffering to the global web middleware stack
        // This runs on ALL web requests — captures full response before sending
        // so Neon DB cold-start latency never causes a partial HTML flash
        $middleware->web(append: [
            \App\Http\Middleware\BufferOutput::class,
        ]);

        // Exclude Paystack webhook from CSRF — Paystack sends raw POST with no token
        $middleware->validateCsrfTokens(except: [
            'paystack/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
