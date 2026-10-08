<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'amount', 'exam_fee', 'notes'];

    protected $casts = [
        'amount'    => 'decimal:2',
        'exam_fee'  => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
