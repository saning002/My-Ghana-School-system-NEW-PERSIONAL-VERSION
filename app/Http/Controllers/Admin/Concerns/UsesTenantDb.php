<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * UsesTenantDb
 * ─────────────────────────────────────────────────────────────────────────
 * Trait for admin controllers to ensure they always operate on the
 * correct tenant (school) database, not the central owner database.
 *
 * CRITICAL: Route model binding resolves Eloquent models BEFORE the
 * controller method runs, using the DEFAULT connection at that time.
 * If TenantMiddleware already switched the connection, binding works.
 * But if the connection reset between requests, binding fails with 404.
 *
 * Solution: Use `int $id` in method signatures, call ensureTenantDb()
 * first, then resolve models manually.
 */
trait UsesTenantDb
{
    /**
     * Force the database connection to the current tenant's DB.
     * Safe to call multiple times — idempotent.
     */
    protected function ensureTenantDb(): void
    {
        $slug = session('tenant_slug');
        if (!$slug) return;

        // Always re-switch — never trust the current connection state
        $tenant = \App\Models\Tenant::where('slug', $slug)->first();
        if ($tenant) {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');
        }
    }

    /**
     * Get the current tenant from session.
     */
    protected function currentTenant(): ?\App\Models\Tenant
    {
        $slug = session('tenant_slug');
        if (!$slug) return null;
        return \App\Models\Tenant::where('slug', $slug)->first();
    }
}
