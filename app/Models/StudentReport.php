<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentReport extends Model
{
    protected $fillable = [
        'student_id', 'program_id', 'created_by', 'attempt',
        'conduct', 'attitude', 'interest',
        'class_teacher_remark', 'head_teacher_remark', 'promoted_to',
    ];

    public function student()    { return $this->belongsTo(Student::class); }
    public function program()    { return $this->belongsTo(Program::class); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }

    public function attributes()
    {
        return $this->hasMany(StudentReportAttribute::class, 'student_report_id');
    }
}
