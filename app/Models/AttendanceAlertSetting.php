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
            'enabled'            => false,
            'present_message'    => 'Dear {parent_name}, {student_name} of {class} {today_status_label} at {school_name} on {today_date}. Term attendance so far: {present_count} days present, {absent_count} days absent ({attendance_rate} rate). — {school_name}',
            'absent_message'     => 'Dear {parent_name}, {student_name} of {class} {today_status_label} at {school_name} on {today_date}. Term absences so far: {absent_count} out of {total_days} days. Please contact the school if needed. — {school_name}',
            'recipient_field'    => 'guardian_phone',
            'send_delay_minutes' => 0,
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
