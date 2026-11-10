<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\AttendanceAlertSetting;
use App\Models\ClassTeacherAssignment;
use App\Models\NotificationTemplate;
use App\Models\Setting;
use App\Models\Student;
use App\Services\SmsService;
use Illuminate\Http\Request;

class ClassRegisterController extends Controller
{
    public function __construct(private SmsService $sms) {}
    /**
     * Show the class register for the lecturer's assigned class.
     * Class teachers mark attendance for ALL students in their program —
     * not course-specific, just class-level present/absent.
     */
    public function index()
    {
        $lecturer   = auth()->user();
        $assignment = ClassTeacherAssignment::where('lecturer_id', $lecturer->id)
            ->with('program')
            ->first();

        abort_unless($assignment, 403, 'You are not assigned as a class teacher for any class.');

        $program  = $assignment->program;
        $periods  = \App\Models\AcademicPeriod::with('session')
            ->orderByDesc('is_active')
            ->get()
            ->sortBy(fn($p) => [$p->session?->year ?? '', $p->sequence ?? 0])
            ->values();

        $activePeriod    = AcademicSession::activePeriod();
        $selectedPeriodId = request('period_id') ?? $activePeriod?->id;
        $selectedDate    = request('date', today()->toDateString());

        $students = $lecturer->lecturerStudentQuery($program->id)
            ->with('user')
            ->forExams()
            ->orderBy('student_id')
            ->get();

        // Check for existing attendance records for today
        $existing = collect();
        $isEditMode = false;

        if ($selectedPeriodId) {
            $records = Attendance::where('program_id', $program->id)
                ->where('academic_period_id', $selectedPeriodId)
                ->where('date', $selectedDate)
                ->whereNull('course_id')           // class-level records have no course_id
                ->whereIn('student_id', $students->pluck('id'))
                ->get();

            if ($records->isNotEmpty()) {
                $isEditMode = true;
                $existing   = $records->keyBy('student_id');
            }
        }

        return view('lecturer.class-register.index', compact(
            'program', 'students', 'periods', 'activePeriod',
            'selectedPeriodId', 'selectedDate', 'existing', 'isEditMode'
        ));
    }

    public function store(Request $request)
    {
        $lecturer   = auth()->user();
        $assignment = ClassTeacherAssignment::where('lecturer_id', $lecturer->id)->first();
        abort_unless($assignment, 403);

        $request->validate([
            'academic_period_id' => 'required|exists:academic_periods,id',
            'date'               => 'required|date',
            'attendances'        => 'required|array',
            'attendances.*'      => 'in:present,absent',
        ]);

        foreach ($request->attendances as $studentId => $status) {
            // course_id is NULL — this is a class-level (whole-day) attendance record
            Attendance::updateOrCreate(
                [
                    'student_id'         => $studentId,
                    'program_id'         => $assignment->program_id,
                    'course_id'          => null,
                    'academic_period_id' => $request->academic_period_id,
                    'date'               => $request->date,
                ],
                ['status' => $status]
            );
        }

        // Fire automatic SMS attendance alerts to parents
        $this->fireAttendanceAlerts(
            $request->attendances,
            $request->academic_period_id,
            $request->date
        );

        return redirect()->route('lecturer.class-register.index', [
            'period_id' => $request->academic_period_id,
            'date'      => $request->date,
        ])->with('success', 'Class register saved. You can edit it any time by selecting the same date.');
    }

    private function fireAttendanceAlerts(array $attendances, int $periodId, string $date): void
    {
        try {
            $settings = AttendanceAlertSetting::current();

            if (!$settings->enabled) {
                \Log::info('Attendance alert (class register) skipped: alerts not enabled.');
                return;
            }

            if (empty($settings->present_message) && empty($settings->absent_message)) {
                \Log::info('Attendance alert (class register) skipped: no message templates set.');
                return;
            }

            $tenantId   = session('tenant_id');
            $studentIds = array_keys($attendances);
            $students   = Student::with(['user', 'program', 'attendances', 'payments'])
                ->whereIn('id', $studentIds)
                ->get()->keyBy('id');

            foreach ($attendances as $studentId => $status) {
                $student = $students->get($studentId);
                if (!$student) continue;

                $rawMessage = $status === 'present'
                    ? $settings->present_message
                    : $settings->absent_message;

                if (empty($rawMessage)) continue;

                $todayLabel = $status === 'present' ? 'was marked PRESENT' : 'was marked ABSENT';

                $message = NotificationTemplate::resolvePlaceholders($rawMessage, $student, [
                    '{today_status}'        => strtoupper($status),
                    '{today_status_label}'  => $todayLabel,
                    '{today_date}'          => now()->format('l, d F Y'),
                ]);

                $phone = $settings->resolvePhone($student);
                if (empty($phone)) {
                    \Log::info("Attendance alert (class register): student {$studentId} has no parent phone — skipped.");
                    continue;
                }

                \Log::info("Attendance alert (class register): sending to {$phone} for student {$studentId} — {$status}");

                if ($settings->send_delay_minutes > 0) {
                    \App\Jobs\SendAttendanceAlertSms::dispatch(
                        $phone, $message, $tenantId ? (int)$tenantId : null
                    )->delay(now()->addMinutes($settings->send_delay_minutes));
                } else {
                    $result = $this->sms->sendBulk([$phone], $message, $tenantId ? (int)$tenantId : null);
                    \Log::info("Attendance alert (class register): SMS result — " . json_encode($result));
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Attendance auto-alert (class register) failed: ' . $e->getMessage());
        }
    }
}
