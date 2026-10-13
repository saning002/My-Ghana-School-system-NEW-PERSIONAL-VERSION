<?php

namespace App\Services;

use App\Models\Tenant;

class TenantService
{
    /**
     * Get the current tenant bound to this request.
     * Checks app container first (TenantMiddleware), then session (Option A slug login).
     * Returns null when running outside tenant context (e.g. owner panel, CLI).
     */
    public static function current(): ?Tenant
    {
        // Never resolve a tenant on owner panel routes
        if (request()->is('owner/*') || request()->is('owner')) {
            return null;
        }

        // 1. Check if TenantMiddleware already resolved it
        try {
            $tenant = app('tenant');
            if ($tenant instanceof Tenant) {
                return $tenant;
            }
        } catch (\Exception $e) {
            // Not bound yet — fall through
        }

        // 2. Check session (Option A — school admin logged in via /school/{slug}/login)
        $slug = session('tenant_slug');
        if ($slug) {
            return Tenant::where('slug', $slug)->first();
        }

        return null;
    }

    /**
     * Check if the current tenant has a feature enabled.
     */
    public static function hasFeature(string $featureKey): bool
    {
        $tenant = static::current();
        if (!$tenant) return true; // No tenant context = owner/dev, allow all

        return $tenant->hasFeature($featureKey);
    }

    /**
     * Assert a feature is available — throws if not.
     */
    public static function requireFeature(string $featureKey): void
    {
        if (!static::hasFeature($featureKey)) {
            abort(403, "The feature '{$featureKey}' is not available on your current plan.");
        }
    }
}
