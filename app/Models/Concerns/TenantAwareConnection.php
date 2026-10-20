<?php

namespace App\Models\Concerns;

/**
 * TenantAwareConnection
 * ─────────────────────
 * Apply this trait to any Eloquent model that lives in the tenant DB.
 * It makes getConnectionName() smart — even before TenantMiddleware runs
 * (e.g. during route model binding), it will configure the tenant
 * connection on-the-fly using the tenant_slug stored in the session.
 *
 * This fixes 404 errors when clicking on a student/lecturer/etc. page
 * because Laravel's route model binding fires before TenantMiddleware.
 */
trait TenantAwareConnection
{
    public function getConnectionName(): string
    {
        // 1. Already switched by TenantMiddleware — fast path
        $default = \Illuminate\Support\Facades\DB::getDefaultConnection();
        if ($default === 'tenant') {
            return 'tenant';
        }

        // 2. Tenant connection already configured (database key is set)
        $tenantDb = config('database.connections.tenant.database', '');
        if (!empty($tenantDb) && session()->has('tenant_slug')) {
            return 'tenant';
        }

        // 3. Session has slug but connection not configured yet —
        //    happens during route model binding before TenantMiddleware.
        //    Configure once per request, cache in app container.
        if (session()->has('tenant_slug')) {
            try {
                if (!app()->bound('_tenant_conn_configured')) {
                    $slug   = session('tenant_slug');
                    $tenant = \App\Models\Tenant::on('pgsql')->where('slug', $slug)->first();
                    if ($tenant) {
                        \Illuminate\Support\Facades\DB::purge('tenant');
                        \Illuminate\Support\Facades\Config::set(
                            'database.connections.tenant',
                            $tenant->dbConfig()
                        );
                        app()->instance('_tenant_conn_configured', true);
                        app()->instance('tenant', $tenant);
                    }
                }
                $tenantDb = config('database.connections.tenant.database', '');
                if (!empty($tenantDb)) {
                    return 'tenant';
                }
            } catch (\Throwable $e) {
                // Fall through to default
            }
        }

        return $this->connection ?? config('database.default');
    }
}
