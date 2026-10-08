<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'program_id', 'expected_classworks', 'expected_homeworks', 'expected_tests'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function assignments()
    {
        return $this->hasMany(CourseAssignment::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
