<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantAwareConnection;

class TeacherWorkLog extends Model
{
    use HasFactory;
    use TenantAwareConnection;

    protected $fillable = [
        'lecturer_id',
        'program_id',
        'course_id',
        'type',
        'title',
        'notes',
        'topic_covered',
        'week_number',
        'admin_comment',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
