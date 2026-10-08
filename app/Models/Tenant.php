<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'subdomain', 'logo_path', 'address', 'phone',
        'admin_name', 'admin_email', 'admin_phone',
        'db_host', 'db_port', 'db_name', 'db_username', 'db_password',
        'plan_id', 'status', 'trial_ends_at', 'subscription_ends_at',
        'backup_frequency', 'last_backup_at', 'notes',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'subscription_ends_at' => 'datetime',
        'last_backup_at'       => 'datetime',
    ];

    // Encrypt password on set
    public function setDbPasswordAttribute(string $value): void
    {
        $this->attributes['db_password'] = Crypt::encryptString($value);
    }

    // Decrypt password on get
    public function getDbPasswordAttribute(string $value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // fallback if already plain (migration edge case)
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

        $config = [
            'driver'   => 'pgsql',
            'host'     => $this->db_host,
            'port'     => $this->db_port ?: '5432',
            'database' => $this->db_name,
            'username' => $this->db_username,
            'password' => $this->db_password,
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ];

        // Neon and most cloud Postgres providers require SSL
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
