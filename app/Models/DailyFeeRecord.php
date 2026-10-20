<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\TenantAwareConnection;

class DailyFeeRecord extends Model
{
    use HasFactory;
    use TenantAwareConnection;

    protected $fillable = [
        'student_id', 'date', 'amount', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function student()   { return $this->belongsTo(Student::class); }
    public function recorder()  { return $this->belongsTo(User::class, 'recorded_by'); }
}
