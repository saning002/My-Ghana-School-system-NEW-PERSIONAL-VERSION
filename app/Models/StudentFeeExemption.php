<?php

namespace App\Models;

use App\Models\Concerns\TenantAwareConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class StudentFeeExemption extends Model
{
    use HasFactory, TenantAwareConnection;

    protected $fillable = [
        'student_id', 'exemption_type', 'daily_override',
        'reason', 'valid_from', 'valid_until', 'granted_by',
    ];

    protected $casts = [
        'valid_from'  => 'date',
        'valid_until' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function isActive(): bool
    {
        $now = Carbon::now();
        if ($this->valid_from && $now->lt($this->valid_from)) return false;
        if ($this->valid_until && $now->gt($this->valid_until)) return false;
        return true;
    }
}
