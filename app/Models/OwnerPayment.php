<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerPayment extends Model
{
    protected $fillable = [
        'tenant_id', 'receipt_number', 'amount', 'currency',
        'payment_date', 'payment_method', 'reference', 'description',
        'months_paid', 'notes', 'receipt_path',
        'receipt_emailed', 'receipt_emailed_at',
    ];

    protected $casts = [
        'payment_date'       => 'date',
        'amount'             => 'decimal:2',
        'months_paid'        => 'integer',
        'receipt_emailed'    => 'boolean',
        'receipt_emailed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Generate a unique receipt number.
     */
    public static function generateReceiptNumber(): string
    {
        $year   = date('Y');
        $latest = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'RCP-' . $year . '-' . str_pad($latest + 1, 5, '0', STR_PAD_LEFT);
    }
}
