<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'duration', 'requirements', 'sequence', 'expected_classworks', 'expected_homeworks', 'expected_tests'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function programFee()
    {
        return $this->hasOne(ProgramFee::class);
    }

    public function programFees()
    {
        return $this->hasMany(ProgramFee::class);
    }

    public function getBranchFeeAttribute()
    {
        try {
            $branchId = auth()->user()?->church_branch_id;
            $this->loadMissing('programFees');

            if ($branchId) {
                $branchFee = $this->programFees->firstWhere('church_branch_id', $branchId);
                if ($branchFee) {
                    return $branchFee;
                }
            }

            return $this->programFees->firstWhere('church_branch_id', null);
        } catch (\Throwable $e) {
            // programFees table may not exist yet - return null gracefully
            return null;
        }
    }

    public function examScores()
    {
        return $this->hasMany(ExamScore::class);
    }

    public function classTeacherAssignment()
    {
        return $this->hasOne(\App\Models\ClassTeacherAssignment::class);
    }
}
