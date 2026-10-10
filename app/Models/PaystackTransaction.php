<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaystackTransaction extends Model
{
    protected $fillable = [
        'type', 'tenant_id', 'student_id', 'student_name',
        'payer_email', 'payer_name', 'reference',
        'amount', 'currency', 'months_paid', 'description',
        'status', 'paystack_id', 'channel', 'paystack_response',
        'owner_payment_id', 'school_payment_id', 'paid_at',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'paid_at'  => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function ownerPayment()
    {
        return $this->belongsTo(OwnerPayment::class);
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
