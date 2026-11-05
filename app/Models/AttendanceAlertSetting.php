<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantAwareConnection;

class AttendanceAlertSetting extends Model
{
    use TenantAwareConnection;

    protected $table = 'attendance_alert_settings';

    protected $fillable = [
        'enabled',
        'present_message',
        'absent_message',
        'recipient_field',
        'send_delay_minutes',
    ];

    protected $casts = [
        'enabled'             => 'boolean',
        'send_delay_minutes'  => 'integer',
    ];

    /**
     * Get the single settings row, creating it if it doesn't exist.
     */
    public static function current(): static
    {
        return static::firstOrCreate([], [
            'enabled'             => false,
            'present_message'     => 'Dear {parent_name}, {student_name} of {class} has been marked PRESENT at {school_name} today ({date}). Term attendance: {present_count} present, {absent_count} absent out of {total_days} days.',
            'absent_message'      => 'Dear {parent_name}, {student_name} of {class} was marked ABSENT at {school_name} today ({date}). Total absences this term: {absent_count} out of {total_days} days. Please contact the school if needed.',
            'recipient_field'     => 'guardian_phone',
            'send_delay_minutes'  => 0,
        ]);
    }

    /**
     * Resolve the correct phone number from a student record
     * based on the configured recipient_field.
     */
    public function resolvePhone(\App\Models\Student $student): ?string
    {
        return match ($this->recipient_field) {
            'mother_phone'   => $student->mother_phone   ?? $student->guardian_phone ?? null,
            'father_phone'   => $student->father_phone   ?? $student->guardian_phone ?? null,
            'student_phone'  => $student->user?->phone   ?? $student->phone          ?? null,
            default          => $student->guardian_phone  // guardian_phone (default)
                             ?? $student->mother_phone
                             ?? $student->father_phone
                             ?? $student->user?->phone
                             ?? null,
        };
    }
}
