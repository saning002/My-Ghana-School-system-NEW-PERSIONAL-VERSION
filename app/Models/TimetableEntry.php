<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimetableEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecturer_id', 'course_id', 'program_id',
        'day_of_week', 'start_time', 'end_time', 'room', 'notes',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    public static array $dayNames = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
    ];

    public function getDayNameAttribute(): string
    {
        return self::$dayNames[$this->day_of_week] ?? 'Unknown';
    }

    public function lecturer() { return $this->belongsTo(User::class, 'lecturer_id'); }
    public function course()   { return $this->belongsTo(Course::class); }
    public function program()  { return $this->belongsTo(Program::class); }
}
