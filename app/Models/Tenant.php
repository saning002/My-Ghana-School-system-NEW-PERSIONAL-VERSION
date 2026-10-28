<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'subdomain', 'logo_path', 'address', 'phone',
        'admin_name', 'admin_email', 'admin_phone',
        'db_host', 'db_port', 'db_name', 'db_username', 'db_password',
        'plan_id', 'status', 'trial_ends_at', 'subscription_ends_at',
        'backup_frequency', 'last_backup_at', 'notes',
        'modules_json', 'theme_json',
        // Cloudinary per school
        'cloudinary_cloud_name', 'cloudinary_api_key', 'cloudinary_api_secret',
        // Backblaze B2 per school
        'b2_key_id', 'b2_application_key', 'b2_bucket_name', 'b2_bucket_id', 'b2_endpoint',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'subscription_ends_at' => 'datetime',
        'last_backup_at'       => 'datetime',
        'modules_json'         => 'array',
        'theme_json'           => 'array',
    ];

    // Encrypt password on set
    public function setDbPasswordAttribute(string $value): void
    {
        // Only encrypt if it's not already encrypted (doesn't start with eyJ)
        if (!empty($value)) {
            try {
                // Test if it's already a valid encrypted value
                \Illuminate\Support\Facades\Crypt::decryptString($value);
                // Already encrypted — store as-is
                $this->attributes['db_password'] = $value;
            } catch (\Exception $e) {
                // Plain text — encrypt it
                $this->attributes['db_password'] = \Illuminate\Support\Facades\Crypt::encryptString($value);
            }
        }
    }

    // Decrypt password on get — always safe, never throws
    public function getDbPasswordAttribute(?string $value): string
    {
        if (empty($value)) return '';
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Exception $e) {
            // Value is not encrypted (legacy plain text) — return as-is
            return $value;
        }
    }

    // Relationships
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'tenant_features')
                    ->withPivot('enabled')
                    ->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(OwnerPayment::class);
    }

    public function backups()
    {
        return $this->hasMany(Backup::class);
    }

    public function messagingCredit()
    {
        return $this->hasOne(\App\Models\MessagingCredit::class);
    }

    public function messagingCreditPayments()
    {
        return $this->hasMany(\App\Models\MessagingCreditPayment::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' ||
               ($this->subscription_ends_at && $this->subscription_ends_at->isPast());
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isFuture();
    }

    public function trialDaysRemaining(): int
    {
        if (!$this->trial_ends_at) return 0;
        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    public function subscriptionDaysRemaining(): int
    {
        if (!$this->subscription_ends_at) return 0;
        return max(0, now()->diffInDays($this->subscription_ends_at, false));
    }

    /**
     * Fast module check using cached modules_json column.
     * Falls back to DB query if cache is empty.
     */
    public function moduleEnabled(string $key): bool
    {
        if (!empty($this->modules_json)) {
            return in_array($key, (array) $this->modules_json, true);
        }
        return $this->hasFeature($key);
    }

    /**
     * Rebuild and cache the modules_json column from tenant_features + plan_features.
     * Call this after saving feature overrides.
     */
    public function rebuildModulesCache(): void
    {
        $allFeatures    = \App\Models\Feature::where('is_active', true)->get();
        $planFeatureIds = $this->plan_id
            ? \Illuminate\Support\Facades\DB::table('plan_features')
                ->where('plan_id', $this->plan_id)->pluck('feature_id')->toArray()
            : [];

        $overrides = \Illuminate\Support\Facades\DB::table('tenant_features')
            ->where('tenant_id', $this->id)
            ->get(['feature_id', 'enabled'])
            ->mapWithKeys(fn($r) => [(int)$r->feature_id => (bool)$r->enabled])
            ->toArray();

        $enabled = [];
        foreach ($allFeatures as $f) {
            $on = array_key_exists($f->id, $overrides)
                ? $overrides[$f->id]
                : in_array($f->id, $planFeatureIds);
            if ($on) {
                $enabled[] = $f->key;
            }
        }

        $this->updateQuietly(['modules_json' => $enabled]);
    }

    /**
     * Check if a feature is enabled for this tenant.
     * Priority: tenant_features override > plan_features
     */
    public function hasFeature(string $featureKey): bool
    {
        // Check tenant-level override first
        $override = $this->features()->where('key', $featureKey)->first();
        if ($override) {
            return (bool) $override->pivot->enabled;
        }

        // Fall back to plan features
        if ($this->plan) {
            return $this->plan->features()->where('key', $featureKey)->exists();
        }

        return false;
    }

    /**
     * Get DB connection config array for this tenant.
     * Supports both standard MySQL/Postgres and Neon (sslmode=require).
     */
    public function dbConfig(): array
    {
        $isNeon = str_contains($this->db_host, 'neon.tech');

        // For Neon: convert pooler URL to direct URL to avoid
        // "cached plan must not change result type" error from pgBouncer pooler.
        // Pooler URL:  ep-xxx-pooler.region.aws.neon.tech
        // Direct URL:  ep-xxx.region.aws.neon.tech (remove "-pooler")
        $host = $this->db_host;
        if ($isNeon) {
            $host = str_replace('-pooler', '', $host);
        }

        $config = [
            'driver'      => 'pgsql',
            'host'        => $host,
            'port'        => $this->db_port ?: '5432',
            'database'    => $this->db_name,
            'username'    => $this->db_username,
            'password'    => $this->db_password,
            'charset'     => 'utf8',
            'prefix'      => '',
            'schema'      => 'public',
        ];

        if ($isNeon || str_contains($this->db_host, 'render.com') || str_contains($this->db_host, 'supabase.co')) {
            $config['sslmode'] = 'require';
        }

        return $config;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active'    => 'success',
            'suspended' => 'danger',
            'trial'     => 'warning',
            'expired'   => 'secondary',
            default     => 'secondary',
        };
    }
}
