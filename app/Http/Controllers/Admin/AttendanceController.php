<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use App\Models\AcademicPeriod;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AttendanceAlertSetting;
use App\Models\NotificationTemplate;
use App\Services\SmsService;
use App\Exports\AttendanceTemplateExport;
use App\Imports\AttendanceImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function __construct(private SmsService $sms) {}
    public function index(Request $request)
    {
        $user          = auth()->user();
        $isBranchAdmin = $user->isBranchAdmin();
        $branchId      = $isBranchAdmin ? $user->church_branch_id : null;

        $programs = Program::orderBy('sequence')->get();
        $date = $request->filled('date') ? $request->date : date('Y-m-d');

        // Total students — scoped to branch for branch admins
        $studentBase = Student::whereNotIn('status', Student::EXAM_EXCLUDED_STATUSES);
        if ($isBranchAdmin) {
            $studentBase->where('church_branch_id', $branchId);
        }
        $totalStudents = $studentBase->count();

        // Total present today — scoped to branch
        $presentBase = Attendance::where('date', $date)->where('status', 'present');
        if ($isBranchAdmin) {
            $presentBase->whereHas('student', fn($q) => $q->where('church_branch_id', $branchId));
        }
        $totalPresentToday = $presentBase->count();

        $classStats = [];
        foreach ($programs as $program) {
            $totalQuery = Student::where('program_id', $program->id)
                ->whereNotIn('status', Student::EXAM_EXCLUDED_STATUSES);
            if ($isBranchAdmin) {
                $totalQuery->where('church_branch_id', $branchId);
            }
            $totalInClass = $totalQuery->count();

            $presentQuery = Attendance::where('date', $date)
                ->where('program_id', $program->id)
                ->where('status', 'present');
            if ($isBranchAdmin) {
                $presentQuery->whereHas('student', fn($q) => $q->where('church_branch_id', $branchId));
            }
            $presentInClass = $presentQuery->count();

            $absentQuery = Attendance::where('date', $date)
                ->where('program_id', $program->id)
                ->where('status', 'absent');
            if ($isBranchAdmin) {
                $absentQuery->whereHas('student', fn($q) => $q->where('church_branch_id', $branchId));
            }
            $absentInClass = $absentQuery->count();

            $classStats[] = [
                'program' => $program,
                'total'   => $totalInClass,
                'present' => $presentInClass,
                'absent'  => $absentInClass,
            ];
        }

        $query = Attendance::with(['student.user', 'student.program', 'course', 'academicPeriod'])
            ->where('date', $date)
            ->latest();

        if ($isBranchAdmin) {
            $query->whereHas('student', fn($q) => $q->where('church_branch_id', $branchId));
        }

        if ($request->filled('program_id')) {
            $query->whereHas('student', fn($q) => $q->where('program_id', $request->program_id));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('student', function ($q) use ($s) {
                $q->where('student_id', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('full_name', 'like', "%{$s}%"));
            });
        }

        $attendances = $query->paginate(20)->withQueryString();
        return view('admin.attendance.index', compact('attendances', 'programs', 'date', 'totalStudents', 'totalPresentToday', 'classStats'));
    }

    public function create(Request $request)
    {
        $courses  = Course::orderBy('name')->get();
        $programs = Program::orderBy('sequence')->get();

        // All periods with session, ordered active-first then by session year + sequence
        $periods  = AcademicPeriod::with('session')
            ->orderByDesc('is_active')
            ->get()
            ->sortBy(fn($p) => [$p->session?->year ?? '', $p->sequence ?? 0])
            ->values();

        $activePeriod    = \App\Models\AcademicSession::activePeriod();
        $selectedPeriodId = $request->filled('period_id') ? $request->period_id : $activePeriod?->id;
        $selectedDate    = $request->filled('date') ? $request->date : date('Y-m-d');

        $students = collect();
        $existingByStudent = collect();
        $isEditMode = false;
        $selectedProgram = null;

        if ($request->filled('program_id')) {
            $selectedProgram = Program::find($request->program_id);

            $query = \App\Models\Student::with('user')
                ->where('program_id', $request->program_id)
                ->whereNotIn('status', Student::EXAM_EXCLUDED_STATUSES);

            // Branch admins only mark attendance for their own branch students
            if (auth()->user()->isBranchAdmin()) {
                $query->where('church_branch_id', auth()->user()->church_branch_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('student_id', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($u) => $u->where('full_name', 'like', "%{$search}%"));
                });
            }

            $students = $query->get();

            // Pre-fill existing records for this date (edit mode)
            if ($selectedPeriodId) {
                $existing = Attendance::whereNull('course_id')
                    ->where('program_id', $request->program_id)
                    ->where('academic_period_id', $selectedPeriodId)
                    ->where('date', $selectedDate)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->get();

                if ($existing->isNotEmpty()) {
                    $isEditMode        = true;
                    $existingByStudent = $existing->keyBy('student_id');
                }
            }
        }

        return view('admin.attendance.create', compact(
            'courses', 'programs', 'periods', 'students', 'selectedProgram',
            'activePeriod', 'selectedPeriodId', 'selectedDate',
            'existingByStudent', 'isEditMode'
        ));
    }

    /**
     * JSON endpoint: search students by program, with optional name/ID filter.
     * GET /admin/attendance/students?program_id=&q=
     */
    public function searchStudents(Request $request)
    {
        $request->validate(['program_id' => 'required|exists:programs,id']);
        $q = trim($request->get('q', ''));

        $query = Student::with(['user', 'program'])
            ->where('program_id', $request->program_id)
            ->where('status', 'active');

        // Scope to branch for branch admins
        if (auth()->user()->isBranchAdmin()) {
            $query->where('church_branch_id', auth()->user()->church_branch_id);
        }

        if (strlen($q) >= 1) {
            $query->where(function ($sq) use ($q) {
                $sq->where('student_id', 'like', "%{$q}%")
                   ->orWhereHas('user', fn($u) => $u->where('full_name', 'like', "%{$q}%"));
            });
        }

        return response()->json(
            $query->get()
                  ->sortBy(fn($s) => $s->user->full_name ?? '')
                  ->values()
                  ->map(fn($s) => [
                      'id'         => $s->id,
                      'full_name'  => $s->user->full_name ?? '—',
                      'student_id' => $s->student_id,
                      'program'    => $s->program->name ?? '—',
                  ])
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id'         => 'required|exists:programs,id',
            'academic_period_id' => 'required|exists:academic_periods,id',
            'date'               => 'required|date',
            'attendances'        => 'required|array',
            'attendances.*'      => 'in:present,absent',
        ]);

        foreach ($request->attendances as $student_id => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id'          => $student_id,
                    'course_id'           => null,
                    'program_id'          => $request->program_id,
                    'academic_period_id'  => $request->academic_period_id,
                    'date'                => $request->date,
                ],
                ['status' => $status]
            );
        }

        // ── Fire automatic attendance SMS alerts if enabled ─────────────────
        $this->fireAttendanceAlerts(
            $request->attendances,   // [student_id => 'present'|'absent']
            $request->academic_period_id,
            $request->date
        );

        return redirect()->route('admin.attendance.index')
            ->with('success', 'Attendance recorded successfully.');
    }

    // ── Auto-alert helper ──────────────────────────────────────────────────

    /**
     * After attendance is saved, check if auto-SMS alerts are enabled
     * and send personalised messages to each student's parent/guardian.
     */
    private function fireAttendanceAlerts(array $attendances, int $periodId, string $date): void
    {
        try {
            $settings = AttendanceAlertSetting::current();

            if (! $settings->enabled) {
                return;
            }

            // Need at least one template message configured
            if (empty($settings->present_message) && empty($settings->absent_message)) {
                return;
            }

            $tenantId = session('tenant_id');

            // Load all students in one query
            $studentIds = array_keys($attendances);
            $students   = Student::with(['user', 'program', 'attendances', 'payments'])
                ->whereIn('id', $studentIds)
                ->get()
                ->keyBy('id');

            foreach ($attendances as $studentId => $status) {
                $student = $students->get($studentId);
                if (! $student) {
                    continue;
                }

                // Pick the right message template
                $rawMessage = $status === 'present'
                    ? $settings->present_message
                    : $settings->absent_message;

                if (empty($rawMessage)) {
                    continue;
                }

                // Resolve placeholders into real values
                // Pass today's status so {today_status} and {today_status_label} resolve correctly
                $todayLabel = $status === 'present'
                    ? 'was marked PRESENT'
                    : 'was marked ABSENT';

                $message = NotificationTemplate::resolvePlaceholders($rawMessage, $student, [
                    '{today_status}'        => strtoupper($status),          // PRESENT or ABSENT
                    '{today_status_label}'  => $todayLabel,                  // "was marked PRESENT"
                    '{today_date}'          => now()->format('l, d F Y'),    // Monday, 08 October 2026
                ]);

                // Resolve the parent phone number
                $phone = $settings->resolvePhone($student);
                if (empty($phone)) {
                    \Log::info("Attendance alert skipped for student {$studentId} — no parent phone on record.");
                    continue;
                }

                // Send — respects send_delay_minutes via a queued job if > 0
                if ($settings->send_delay_minutes > 0) {
                    // Dispatch delayed — uses the default queue
                    \App\Jobs\SendAttendanceAlertSms::dispatch(
                        $phone,
                        $message,
                        $tenantId ? (int) $tenantId : null
                    )->delay(now()->addMinutes($settings->send_delay_minutes));
                } else {
                    // Send immediately (synchronous — safe for small classes)
                    $this->sms->sendBulk([$phone], $message, $tenantId ? (int) $tenantId : null);
                }
            }
        } catch (\Throwable $e) {
            // Never let alert failures break the attendance save
            \Log::error('Attendance auto-alert failed: ' . $e->getMessage());
        }
    }

    public function template(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'academic_period_id' => 'required|exists:academic_periods,id',
        ]);

        $program = Program::findOrFail($request->program_id);
        $period = AcademicPeriod::findOrFail($request->academic_period_id);
        $course = null;

        $studentQuery = Student::where('program_id', $program->id)->whereNotIn('status', Student::EXAM_EXCLUDED_STATUSES);
        $students = $studentQuery->with('user')->orderBy('student_id')->get();

        $filename = sprintf(
            'AttendanceBook_%s_%s_%s.xlsx',
            str_replace(' ', '_', $program->name),
            str_replace(' ', '_', $period->month),
            date('Ymd')
        );

        return Excel::download(
            new AttendanceTemplateExport($students, $course, $program, $period, $request->date ?? date('Y-m-d')),
            $filename
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'academic_period_id' => 'required|exists:academic_periods,id',
            'attendance_file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        $program = Program::findOrFail($request->program_id);

        try {
            Excel::import(
                new AttendanceImport(
                    $request->program_id,
                    null, // course_id
                    $request->academic_period_id,
                    '', // course code
                    $program->name,
                    $request->date ?? date('Y-m-d')
                ),
                $request->file('attendance_file')
            );

            return redirect()->route('admin.attendance.index')
                ->with('success', 'Attendance file imported successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['attendance_file' => 'Failed to import attendance file: ' . $e->getMessage()]);
        }
    }
}
