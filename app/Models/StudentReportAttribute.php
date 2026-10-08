<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentReportAttribute extends Model
{
    protected $fillable = [
        'student_report_id', 'student_id', 'program_id',
        'attribute_definition_id', 'attempt', 'rating',
    ];

    public function studentReport()       { return $this->belongsTo(StudentReport::class); }
    public function attributeDefinition() { return $this->belongsTo(AttributeDefinition::class); }
    public function student()             { return $this->belongsTo(Student::class); }
}
