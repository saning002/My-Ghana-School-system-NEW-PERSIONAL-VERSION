<?php

namespace App\Http\Controllers\Admin\Concerns;

/**
 * UsesTenantDb
 * ─────────────────────────────────────────────────────────────────────────
 * Marker trait for admin controllers that operate on tenant (school) DBs.
 *
 * DB switching is handled entirely by TenantMiddleware which runs BEFORE
 * any controller method — including before route model binding.
 *
 * Controllers using this trait must NOT call any extra DB switching.
 * Just use Eloquent/DB normally — they will automatically use the tenant DB.
 */
trait UsesTenantDb
{
    /**
     * Get the current tenant from the request (set by TenantMiddleware).
     */
    protected function currentTenant(): ?\App\Models\Tenant
    {
        try {
            return app('tenant');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
