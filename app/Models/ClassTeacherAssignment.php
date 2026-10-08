<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassTeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['program_id', 'lecturer_id'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    /**
     * Check if a given lecturer is a class teacher for any program.
     */
    public static function isClassTeacher(int $lecturerId): bool
    {
        return static::where('lecturer_id', $lecturerId)->exists();
    }

    /**
     * Get all programs a lecturer manages as class teacher.
     */
    public static function programsFor(int $lecturerId)
    {
        return static::where('lecturer_id', $lecturerId)
            ->with('program')
            ->get()
            ->pluck('program');
    }
}
