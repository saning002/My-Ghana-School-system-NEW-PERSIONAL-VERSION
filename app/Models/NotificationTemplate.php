<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\TenantAwareConnection;

class NotificationTemplate extends Model
{
    use HasFactory, TenantAwareConnection;

    protected $table = 'notification_templates';

    protected $fillable = [
        'name',
        'category',
        'title',
        'body',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ── Placeholder resolution ─────────────────────────────────────────────

    /**
     * All supported placeholders and their human descriptions.
     * Used to build the "insert placeholder" buttons in the UI.
     */
    public static function placeholders(): array
    {
        return [
            // ── Today's attendance (most important for auto-alerts) ──────────
            '{today_status}'        => 'PRESENT or ABSENT — what was marked today',
            '{today_status_label}'  => 'Full sentence: "was marked PRESENT" or "was marked ABSENT"',
            '{today_date}'          => "Today's date in full (e.g. Monday, 08 October 2026)",

            // ── Student identity ────────────────────────────────────────────
            '{student_name}'        => 'Full name of the student',
            '{student_id}'          => 'Student ID number',
            '{parent_name}'         => 'Parent / guardian name',
            '{class}'               => 'Programme / class name',

            // ── School ──────────────────────────────────────────────────────
            '{school_name}'         => 'Name of the school',
            '{term}'                => 'Current term (e.g. Term 1, 2025/2026)',
            '{date}'                => "Today's date (short: 08 Oct 2026)",

            // ── Term attendance totals ──────────────────────────────────────
            '{present_count}'       => 'Number of days present this term',
            '{absent_count}'        => 'Number of days absent this term',
            '{total_days}'          => 'Total school days so far this term',
            '{attendance_rate}'     => 'Attendance rate as a percentage',

            // ── Fees ────────────────────────────────────────────────────────
            '{fee_balance}'         => 'Outstanding fee balance (GH₵)',
            '{fee_paid}'            => 'Amount paid so far (GH₵)',
            '{fee_total}'           => 'Total fee billed (GH₵)',

            // ── Extra ───────────────────────────────────────────────────────
            '{custom_note}'         => 'Extra note typed at send time',
        ];
    }

    /**
     * Resolve placeholders in the template body for a specific student.
     * Pass a Student model and an optional array of extra data to override or add values.
     */
    public function resolve(\App\Models\Student $student, array $extra = []): string
    {
        return self::resolvePlaceholders($this->body, $student, $extra);
    }

    /**
     * Static version — resolve a raw string against a student.
     */
    public static function resolvePlaceholders(string $text, \App\Models\Student $student, array $extra = []): string
    {
        // ── Load relationships we need ──────────────────────────────────────
        $student->loadMissing(['user', 'program', 'payments', 'attendances']);

        // ── Attendance counts for current active period ─────────────────────
        $activePeriod  = \App\Models\AcademicPeriod::where('is_active', true)->first();
        $activePeriodId = $activePeriod?->id;

        $attQuery = $student->attendances();
        if ($activePeriodId) {
            $attQuery->where('academic_period_id', $activePeriodId);
        }
        $allAtt       = $attQuery->get();
        $presentCount = $allAtt->where('status', 'present')->count();
        $absentCount  = $allAtt->where('status', 'absent')->count();
        $totalDays    = $presentCount + $absentCount;
        $attRate      = $totalDays > 0 ? round($presentCount / $totalDays * 100) : 0;

        // ── Fee amounts ─────────────────────────────────────────────────────
        $feePaid    = (float) $student->payments->sum('amount_paid');
        $feeTotal   = 0.0;
        $feeBalance = 0.0;
        try {
            $feeService = app(\App\Services\FeeCalculationService::class);
            $feeSummary = $feeService->calculate($student);
            $feeTotal   = (float) ($feeSummary['total'] ?? 0);
            $feeBalance = (float) ($feeSummary['balance'] ?? max(0, $feeTotal - $feePaid));
        } catch (\Throwable $e) {
            $feeBalance = max(0, $feeTotal - $feePaid);
        }

        // ── Parent name ─────────────────────────────────────────────────────
        $parentName = $student->guardian_name
            ?? $student->mother_name
            ?? $student->father_name
            ?? 'Parent/Guardian';

        // ── Term label ──────────────────────────────────────────────────────
        $termLabel = $activePeriod
            ? ($activePeriod->name ?? $activePeriod->month ?? 'Current Term')
              . ', ' . ($activePeriod->session?->year ?? '')
            : 'Current Term';

        // ── School name ─────────────────────────────────────────────────────
        $schoolName = \App\Models\Setting::get('school_name', config('app.name', 'School'));

        // ── Map ─────────────────────────────────────────────────────────────
        $map = array_merge([
            // Today's attendance status — resolved from $extra if provided by auto-alert
            '{today_status}'        => 'PRESENT',               // overridden by $extra
            '{today_status_label}'  => 'was marked PRESENT',    // overridden by $extra
            '{today_date}'          => now()->format('l, d F Y'),

            '{student_name}'    => $student->user?->full_name ?? 'Student',
            '{student_id}'      => $student->student_id ?? '—',
            '{parent_name}'     => $parentName,
            '{class}'           => $student->program?->name ?? '—',
            '{school_name}'     => $schoolName,
            '{term}'            => $termLabel,
            '{present_count}'   => $presentCount,
            '{absent_count}'    => $absentCount,
            '{total_days}'      => $totalDays,
            '{attendance_rate}' => $attRate . '%',
            '{fee_balance}'     => 'GH₵ ' . number_format($feeBalance, 2),
            '{fee_paid}'        => 'GH₵ ' . number_format($feePaid, 2),
            '{fee_total}'       => 'GH₵ ' . number_format($feeTotal, 2),
            '{date}'            => now()->format('d M Y'),
            '{custom_note}'     => '',
        ], $extra);

        return strtr($text, $map);
    }
}
