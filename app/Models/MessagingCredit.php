<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagingCredit extends Model
{
    protected $fillable = [
        'tenant_id', 'sms_credits', 'whatsapp_credits',
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

    /** Get or create credits record for a tenant */
    public static function forTenant(int $tenantId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['sms_credits' => 0, 'whatsapp_credits' => 0, 'sms_used' => 0, 'whatsapp_used' => 0]
        );
    }
}
