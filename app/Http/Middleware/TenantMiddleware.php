<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if (!$tenant) {
            return response()->view('tenant.not-found', [], 404);
        }

        // Store tenant on the request so controllers/views can access it
        $request->merge(['_tenant' => $tenant]);
        app()->instance('tenant', $tenant);

        // Check status before switching DB
        if ($tenant->isSuspended()) {
            return response()->view('tenant.suspended', compact('tenant'), 403);
        }

        if ($tenant->isExpired()) {
            // Auto-update status in DB
            $tenant->update(['status' => 'expired']);
            return response()->view('tenant.expired', compact('tenant'), 403);
        }

        // Switch database connection to this tenant's DB
        $this->switchDatabase($tenant);

        // Share tenant with all Blade views
        view()->share('currentTenant', $tenant);

        $response = $next($request);

        // Reset to central DB after request (important for queued jobs, etc.)
        $this->resetDatabase();

        return $response;
    }

    /**
     * Resolve tenant from subdomain, session (Option A), header, or query param.
     */
    private function resolveTenant(Request $request): ?Tenant
    {
        // 1. Session-based (Option A — slug login at /school/{slug}/login)
        $sessionSlug = session('tenant_slug');
        if ($sessionSlug) {
            return Tenant::where('slug', $sessionSlug)->first();
        }

        // 2. Try subdomain — e.g. "accra-academy" from "accra-academy.yourdomain.com"
        $host      = $request->getHost();
        $appDomain = config('app.domain', parse_url(config('app.url'), PHP_URL_HOST));

        if ($appDomain && str_ends_with($host, '.' . $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
            if ($subdomain && $subdomain !== $appDomain) {
                return Tenant::where('subdomain', $subdomain)->first();
            }
        }

        // 3. Try X-Tenant-Slug header (useful for local dev / Postman testing)
        $slug = $request->header('X-Tenant-Slug');
        if ($slug) {
            return Tenant::where('slug', $slug)->first();
        }

        // 4. Try ?tenant= query param (last resort for local dev)
        $tenantParam = $request->query('tenant');
        if ($tenantParam) {
            return Tenant::where('slug', $tenantParam)->orWhere('subdomain', $tenantParam)->first();
        }

        return null;
    }

    /**
     * Dynamically configure and switch to the tenant's database.
     */
    private function switchDatabase(Tenant $tenant): void
    {
        // Purge any existing tenant connection so it reconnects fresh
        DB::purge('tenant');

        Config::set('database.connections.tenant', $tenant->dbConfig());

        // Set tenant as the default connection for this request
        DB::setDefaultConnection('tenant');
    }

    /**
     * Reset back to the central (default) connection.
     */
    private function resetDatabase(): void
    {
        $default = config('database.default_central', 'mysql');
        DB::setDefaultConnection($default);
        DB::purge('tenant');
    }
}
