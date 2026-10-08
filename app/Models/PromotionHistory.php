<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionHistory extends Model
{
    use HasFactory;

    protected $table = 'promotion_histories';

    protected $fillable = [
        'student_id',
        'from_program_id',
        'to_program_id',
        'promoted_at',
    ];

    protected $casts = [
        'promoted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromProgram()
    {
        return $this->belongsTo(Program::class, 'from_program_id');
    }

    public function toProgram()
    {
        return $this->belongsTo(Program::class, 'to_program_id');
    }
}
