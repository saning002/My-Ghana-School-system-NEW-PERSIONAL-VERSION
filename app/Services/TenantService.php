<?php

namespace App\Services;

use App\Models\Tenant;

class TenantService
{
    /**
     * Get the current tenant bound to this request.
     * Returns null when running outside tenant context (e.g. owner panel, CLI).
     */
    public static function current(): ?Tenant
    {
        try {
            return app('tenant');
        } catch (\Exception $e) {
            return null;
        }
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
