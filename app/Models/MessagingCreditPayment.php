<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagingCreditPayment extends Model
{
    protected $fillable = [
        'tenant_id', 'package_id', 'receipt_number', 'amount', 'currency',
        'sms_credits_added', 'whatsapp_credits_added', 'payment_method',
        'paystack_reference', 'paystack_status', 'status', 'notes', 'receipt_emailed',
    ];

    protected $casts = [
        'amount'                => 'decimal:2',
        'sms_credits_added'     => 'integer',
        'whatsapp_credits_added'=> 'integer',
        'receipt_emailed'       => 'boolean',
    ];

    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function package() { return $this->belongsTo(MessagingCreditPackage::class, 'package_id'); }

    public static function generateReceiptNumber(): string
    {
        $year   = date('Y');
        $latest = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'MSG-' . $year . '-' . str_pad($latest + 1, 5, '0', STR_PAD_LEFT);
    }
}
