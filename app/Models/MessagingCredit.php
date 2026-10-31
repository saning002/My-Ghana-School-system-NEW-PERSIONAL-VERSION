<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagingCredit extends Model
{
    // Always use central DB — this table lives in the owner DB, not tenant DB
    protected $connection = 'pgsql';
    protected $fillable = [
        'tenant_id', 'branch_id', 'sms_credits', 'whatsapp_credits',
        'sms_used', 'whatsapp_used', 'last_topped_up_at',
    ];

    protected $casts = [
        'sms_credits'      => 'integer',
        'whatsapp_credits' => 'integer',
        'sms_used'         => 'integer',
        'whatsapp_used'    => 'integer',
        'last_topped_up_at'=> 'datetime',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }

    public function hasSmsCredits(int $needed = 1): bool
    {
        return $this->sms_credits >= $needed;
    }

    public function hasWhatsappCredits(int $needed = 1): bool
    {
        return $this->whatsapp_credits >= $needed;
    }

    public function deductSms(int $count = 1): void
    {
        $this->decrement('sms_credits', $count);
        $this->increment('sms_used', $count);
    }

    public function deductWhatsapp(int $count = 1): void
    {
        $this->decrement('whatsapp_credits', $count);
        $this->increment('whatsapp_used', $count);
    }

    public function addCredits(int $sms, int $whatsapp): void
    {
        $this->increment('sms_credits', $sms);
        $this->increment('whatsapp_credits', $whatsapp);
        $this->update(['last_topped_up_at' => now()]);
    }

    /**
     * Get the school-wide shared credits (branch_id = NULL).
     */
    public static function forTenant(int $tenantId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId, 'branch_id' => null],
            ['sms_credits' => 0, 'whatsapp_credits' => 0, 'sms_used' => 0, 'whatsapp_used' => 0]
        );
    }

    /**
     * Get credits for a specific branch.
     * Returns branch-specific pool if it exists, otherwise falls back to shared pool.
     */
    public static function forBranch(int $tenantId, int $branchId): self
    {
        $branch = static::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->first();

        if ($branch) return $branch;

        // Fall back to shared pool
        return static::forTenant($tenantId);
    }

    /**
     * Get or create a dedicated credit pool for a branch.
     */
    public static function forBranchDedicated(int $tenantId, int $branchId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId, 'branch_id' => $branchId],
            ['sms_credits' => 0, 'whatsapp_credits' => 0, 'sms_used' => 0, 'whatsapp_used' => 0]
        );
    }

    /**
     * Get all credit pools for a tenant (shared + all branch-specific).
     */
    public static function allForTenant(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('tenant_id', $tenantId)
            ->orderByRaw('branch_id IS NULL DESC') // shared first
            ->orderBy('branch_id')
            ->get();
    }

    public function isShared(): bool
    {
        return $this->branch_id === null;
    }
}

    public function hasSmsCredits(int $needed = 1): bool
    {
        return $this->sms_credits >= $needed;
    }

    public function hasWhatsappCredits(int $needed = 1): bool
    {
        return $this->whatsapp_credits >= $needed;
    }

    public function deductSms(int $count = 1): void
    {
        $this->decrement('sms_credits', $count);
        $this->increment('sms_used', $count);
    }

    public function deductWhatsapp(int $count = 1): void
    {
        $this->decrement('whatsapp_credits', $count);
        $this->increment('whatsapp_used', $count);
    }

    public function addCredits(int $sms, int $whatsapp): void
    {
        $this->increment('sms_credits', $sms);
        $this->increment('whatsapp_credits', $whatsapp);
        $this->update(['last_topped_up_at' => now()]);
    }

    /** Get or create credits record for a tenant */
    public static function forTenant(int $tenantId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['sms_credits' => 0, 'whatsapp_credits' => 0, 'sms_used' => 0, 'whatsapp_used' => 0]
        );
    }
}
