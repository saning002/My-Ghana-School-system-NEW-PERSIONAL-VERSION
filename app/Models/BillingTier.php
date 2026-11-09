<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingTier extends Model
{
    // billing_tiers lives on the MAIN database, not the tenant DB
    protected $connection = 'pgsql';

    protected $fillable = [
        'tenant_id',
        'min_students',
        'max_students',
        'rate_per_student',
        'sort_order',
    ];

    protected $casts = [
        'min_students'     => 'integer',
        'max_students'     => 'integer',
        'rate_per_student' => 'float',
        'sort_order'       => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Find the matching tier for a given student count.
     * Returns the BillingTier or null if none configured.
     */
    public static function resolveTier(int $tenantId, int $studentCount): ?self
    {
        return static::where('tenant_id', $tenantId)
            ->where('min_students', '<=', $studentCount)
            ->where(function ($q) use ($studentCount) {
                $q->whereNull('max_students')
                  ->orWhere('max_students', '>=', $studentCount);
            })
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Get all tiers for a school, ordered.
     */
    public static function forTenant(int $tenantId)
    {
        return static::where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('min_students')
            ->get();
    }

    /**
     * Sync tiers for a school from an array of tier data.
     * Deletes old tiers and inserts new ones.
     */
    public static function syncForTenant(int $tenantId, array $tiers): void
    {
        static::where('tenant_id', $tenantId)->delete();

        $sort = 0;
        foreach ($tiers as $tier) {
            if (empty($tier['rate_per_student']) || $tier['rate_per_student'] <= 0) {
                continue; // Skip empty tiers
            }
            static::create([
                'tenant_id'        => $tenantId,
                'min_students'     => (int) ($tier['min_students'] ?? 0),
                'max_students'     => isset($tier['max_students']) && $tier['max_students'] !== '' ? (int) $tier['max_students'] : null,
                'rate_per_student' => (float) $tier['rate_per_student'],
                'sort_order'       => $sort++,
            ]);
        }
    }
}
