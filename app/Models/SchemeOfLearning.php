<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemeOfLearning extends Model
{
    protected $table = 'scheme_of_learning';

    protected $fillable = [
        'program_id','course_id','lecturer_id','church_branch_id',
        'academic_year','term','week_number',
        'topic','subtopics','learning_objectives',
        'teaching_methods','resources','assessment_type','remarks',
        'pdf_path',
    ];

    public function program()      { return $this->belongsTo(Program::class); }
    public function course()       { return $this->belongsTo(Course::class); }
    public function lecturer()     { return $this->belongsTo(User::class, 'lecturer_id'); }
    public function churchBranch() { return $this->belongsTo(\App\Models\ChurchBranch::class); }
}
