<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantAwareConnection;

class Payment extends Model
{
    use HasFactory;
    use TenantAwareConnection;

    protected $fillable = ['student_id', 'amount_paid', 'payment_date', 'notes'];

    protected $casts = [
        'payment_date' => 'date',
        'amount_paid'  => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
