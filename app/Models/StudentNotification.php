<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNotification extends Model
{
    protected $table = 'student_notifications';

    protected $fillable = [
        'sent_by', 'student_id', 'lecturer_id', 'recipient_type',
        'audience', 'type', 'title', 'message', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    /** Scope: only student notifications */
    public function scopeForStudents($query)
    {
        return $query->where('recipient_type', 'student')
                     ->orWhereNull('recipient_type');
    }

    /** Scope: only lecturer notifications */
    public function scopeForLecturers($query)
    {
        return $query->where('recipient_type', 'lecturer');
    }

    /** Scope: notifications visible to a specific student */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('recipient_type', 'student')
            ->where(function ($q) use ($studentId) {
                $q->where('audience', 'all')
                  ->orWhere(function ($q2) use ($studentId) {
                      $q2->where('audience', 'individual')
                         ->where('student_id', $studentId);
                  });
            });
    }

    /** Scope: notifications visible to a specific lecturer */
    public function scopeVisibleToLecturer($query, $lecturerId)
    {
        return $query->where('recipient_type', 'lecturer')
            ->where(function ($q) use ($lecturerId) {
                $q->where('audience', 'all')
                  ->orWhere(function ($q2) use ($lecturerId) {
                      $q2->where('audience', 'individual')
                         ->where('lecturer_id', $lecturerId);
                  });
            });
    }
}
