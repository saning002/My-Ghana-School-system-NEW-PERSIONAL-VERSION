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

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
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

    public function isShared(): bool
    {
        return $this->branch_id === null;
    }

    /** Get school-wide shared pool (branch_id = NULL) */
    public static function forTenant(int $tenantId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId, 'branch_id' => null],
            ['sms_credits' => 0, 'whatsapp_credits' => 0, 'sms_used' => 0, 'whatsapp_used' => 0]
        );
    }

    /** Get branch pool, falling back to shared if no dedicated pool exists */
    public static function forBranch(int $tenantId, int $branchId): self
    {
        $branch = static::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->first();

        return $branch ?? static::forTenant($tenantId);
    }

    /** Get or create a dedicated pool for a specific branch */
    public static function forBranchDedicated(int $tenantId, int $branchId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId, 'branch_id' => $branchId],
            ['sms_credits' => 0, 'whatsapp_credits' => 0, 'sms_used' => 0, 'whatsapp_used' => 0]
        );
    }

    /** Get all pools for a tenant (shared first, then branches) */
    public static function allForTenant(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('tenant_id', $tenantId)
            ->orderByRaw('branch_id IS NULL DESC')
            ->orderBy('branch_id')
            ->get();
    }
}
