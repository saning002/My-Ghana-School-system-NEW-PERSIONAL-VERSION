<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class StudentFeeExemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'exemption_type', 'daily_override',
        'reason', 'valid_from', 'valid_until', 'granted_by',
    ];

    protected $casts = [
        'valid_from'     => 'date',
        'valid_until'    => 'date',
        'daily_override' => 'decimal:2',
    ];

    public static array $types = [
        'scholarship' => 'Scholarship',
        'waiver'      => 'Fee Waiver',
        'partial'     => 'Partial Exemption',
        'other'       => 'Other',
    ];

    /**
     * Is this exemption active today?
     */
    public function isActive(): bool
    {
        $today = Carbon::today();
        if ($this->valid_from && $today->lt($this->valid_from)) return false;
        if ($this->valid_until && $today->gt($this->valid_until)) return false;
        return true;
    }

    /**
     * Is this a full exemption (pays nothing)?
     */
    public function isFull(): bool
    {
        return $this->daily_override === null;
    }

    public function student()  { return $this->belongsTo(Student::class); }
    public function granter()  { return $this->belongsTo(User::class, 'granted_by'); }
}
