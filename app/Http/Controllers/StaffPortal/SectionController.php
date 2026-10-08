<?php
<?php
// Staff portal section controller — Work Logs, Scheme of Learning and Exam Questions
// render the exact same admin views scoped to the staff member's branch.

namespace App\Http\Controllers\StaffPortal;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\ExamScore;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Student;
use App\Services\FeeCalculationService;
use App\Services\ReportCardService;
use App\Services\ReportService;
use App\Services\DocumentVerificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    public function __construct(
        private FeeCalculationService $feeService,
        private ReportCardService $reportCardService,
        private ReportService $reportService,
        private DocumentVerificationService $docService,
    ) {}

    // ── Helpers ────────────────────────────────────────────────────────────────

    /** Get the branch ID this staff user is scoped to (null = all branches) */
    private function branchId(): ?int
    {
        return session('staff_portal_user_id')
            ? \App\Models\StaffPortalUser::find(session('staff_portal_user_id'))?->church_branch_id
            : null;
    }

    private function requirePermission(string $perm): void
    {
        $userId = session('staff_portal_user_id');
        $user   = $userId ? \App\Models\StaffPortalUser::with('permissions')->find($userId) : null;
        abort_unless($user && $user->can_access($perm), 403, "You do not have access to: {$perm}");
    }

    private function schoolMeta(): array
    {
        return [
            'schoolName'    => Setting::get('school_name', config('app.name')),
            'schoolSubtitle'=> Setting::get('school_subtitle', ''),
        ];
    }

    private function raisePdfMemory(): void
    {
        if ((int) ini_get('memory_limit') < 256) ini_set('memory_limit', '256M');
    }

    private function safeRecord(string $type, string $title, ?int $studentId = null, ?int $periodId = null, array $meta = []): ?\App\Models\GeneratedDocument
    {
        try {
            return $this->docService->record($type, $title, $studentId, $periodId, $meta);
        } catch (\Throwable $e) { return null; }
    }

    // ── Student Reports ────────────────────────────────────────────────────────

    public function studentReports(Request $request)
    {
        $this->requirePermission('student_reports');
        $branchId = $this->branchId();

        $programs        = Program::orderBy('sequence')->get();
        $students        = collect();
        $reports         = collect();
        $selectedProgram = null;
        $selectedAttempt = (int) $request->get('attempt', 1);

        if ($request->filled('program_id')) {
            $selectedProgram = Program::find($request->program_id);
            $students = \App\Models\Student::with('user')
                ->where('program_id', $request->program_id)
                ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId))
                ->forExams()
                ->orderBy('student_id')
                ->get();

            $reports = \App\Models\StudentReport::where('program_id', $request->program_id)
                ->where('attempt', $selectedAttempt)
                ->whereIn('student_id', $students->pluck('id'))
                ->with(['student.user'])
                ->get()
                ->keyBy('student_id');
        }

        return view('admin.student-reports.index', compact(
            'programs','students','reports','selectedProgram','selectedAttempt'
        ));
    }

    // ── Students ───────────────────────────────────────────────────────────────

    public function students(Request $request)
    {
        $this->requirePermission('students');
        $branchId = $this->branchId();
        $programs = Program::orderBy('sequence')->get();

        $query = Student::with(['user','program'])
            ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId))
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest();

        $students = $query->paginate(20)->withQueryString();
        $meta     = $this->schoolMeta();

        return view('staff-portal.sections.students', compact('students','programs','meta','branchId'));
    }

    // ── Attendance ─────────────────────────────────────────────────────────────

    public function attendance(Request $request)
    {
        $this->requirePermission('attendance');
        $branchId = $this->branchId();
        $courses  = Course::orderBy('name')->get();
        $programs = Program::orderBy('sequence')->get();
        $sessions = AcademicSession::with('periods')->orderByDesc('year')->get();

        $query = Attendance::with(['student.user','course','academicPeriod'])
            ->when($branchId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('church_branch_id', $branchId)))
            ->when($request->course_id, fn($q) => $q->where('course_id', $request->course_id))
            ->when($request->from, fn($q) => $q->whereDate('date', '>=', $request->from))
            ->when($request->to,   fn($q) => $q->whereDate('date', '<=', $request->to));

        $records  = $query->latest()->paginate(25)->withQueryString();
        $meta     = $this->schoolMeta();

        return view('staff-portal.sections.attendance', compact('records','courses','programs','sessions','meta','branchId'));
    }

    // ── Fees ───────────────────────────────────────────────────────────────────

    public function fees(Request $request)
    {
        $this->requirePermission('fees');
        $branchId = $this->branchId();
        $programs = Program::orderBy('sequence')->get();

        $global  = $this->feeService->getSummary($branchId);
        $students = Student::with(['user','program','payments'])
            ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId))
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->latest()->paginate(15)->withQueryString();

        $studentFees = $students->map(fn($s) => array_merge(['student'=>$s], $this->feeService->calculate($s)));
        $meta        = $this->schoolMeta();

        return view('staff-portal.sections.fees', compact('global','students','studentFees','programs','meta','branchId'));
    }

    public function feeStatementPdf(Request $request)
    {
        $this->requirePermission('fees');
        $this->raisePdfMemory();
        $request->validate(['student_id' => 'required|exists:students,id']);

        $student = Student::with(['user','program','payments'])->findOrFail($request->student_id);
        $raw     = $this->feeService->calculate($student);
        $fees    = ['billed'=>$raw['total']??0,'paid'=>$raw['paid']??0,'balance'=>$raw['balance']??0];

        $doc   = $this->safeRecord('fee_statement',"Fee Statement — {$student->user->full_name}",$student->id,null,$fees);
        $qrUrl = $doc ? $this->docService->qrUrl($doc) : '';
        $meta  = $this->schoolMeta();
        $schoolName = $meta['schoolName'];

        $pdf = Pdf::loadView('pdf.fee_statement', array_merge(compact('student','fees','doc','qrUrl','schoolName'),$meta))
            ->setPaper('a4');
        return $pdf->download("fee_statement_{$student->student_id}_".now()->format('Ymd').'.pdf');
    }

    // ── Reports ────────────────────────────────────────────────────────────────

    public function reports()
    {
        $this->requirePermission('reports');
        $branchId = $this->branchId();
        $programs = Program::orderBy('sequence')->get();

        $summary = [
            'total_students'  => Student::when($branchId, fn($q)=>$q->where('church_branch_id',$branchId))->count(),
            'active_students' => Student::when($branchId, fn($q)=>$q->where('church_branch_id',$branchId))->where('status','active')->count(),
            'fees'            => $this->feeService->getSummary($branchId),
            'attendance_rate' => $this->getAttendanceRate($branchId),
        ];
        $meta = $this->schoolMeta();

        return view('staff-portal.sections.reports', compact('summary','programs','meta','branchId'));
    }

    public function classPerformance(Request $request)
    {
        $this->requirePermission('reports');
        $branchId = $this->branchId();
        $programs = Program::orderBy('sequence')->get();
        $sessions = AcademicSession::with('periods')->orderByDesc('year')->get();
        $data     = null;

        if ($request->filled('program_id')) {
            $program = Program::findOrFail($request->program_id);
            $period  = $request->filled('period_id') ? \App\Models\AcademicPeriod::find($request->period_id) : AcademicSession::activePeriod();
            $attempt = (int) $request->get('attempt', 1);
            $data    = $this->reportService->classPerformance($program, $period, $attempt);
        }

        $meta = $this->schoolMeta();
        return view('staff-portal.sections.class-performance', compact('programs','sessions','data','meta','branchId'));
    }

    public function classPerformancePdf(Request $request)
    {
        $this->requirePermission('reports');
        $this->raisePdfMemory();
        $request->validate(['program_id'=>'required|exists:programs,id']);

        $program = Program::findOrFail($request->program_id);
        $period  = $request->filled('period_id') ? \App\Models\AcademicPeriod::find($request->period_id) : AcademicSession::activePeriod();
        $attempt = (int) $request->get('attempt', 1);
        $data    = $this->reportService->classPerformance($program, $period, $attempt);

        $doc   = $this->safeRecord('class_performance',"Class Performance — {$program->name}",null,$period?->id,['program_id'=>$program->id,'attempt'=>$attempt]);
        $qrUrl = $doc ? $this->docService->qrUrl($doc) : '';
        $meta  = $this->schoolMeta();

        $pdf = Pdf::loadView('pdf.class_performance', array_merge(compact('data','doc','qrUrl'),$meta))
            ->setPaper('a4','landscape');
        return $pdf->download("class_performance_{$program->name}_".now()->format('Ymd').'.pdf');
    }

    // ── Daily Fees ─────────────────────────────────────────────────────────────

    public function dailyFees(Request $request)
    {
        $this->requirePermission('daily_fees');
        $branchId = $this->branchId();
        $today    = $request->date ?? today()->toDateString();
        $programs = Program::orderBy('sequence')->get();
        $dailyRate= (float) Setting::get('daily_fee_rate', 5);

        $query    = Student::with(['user','program','feeExemption'])->forExams()
            ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId))
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id));

        $students = $query->orderBy('student_id')->get();
        $paid     = \App\Models\DailyFeeRecord::where('date', $today)
            ->whereIn('student_id', $students->pluck('id'))->get()->keyBy('student_id');

        $summary  = [
            'expected' => $students->count(),
            'paid'     => $paid->count(),
            'exempted' => $students->filter(fn($s)=>$s->feeExemption?->isActive())->count(),
            'unpaid'   => $students->filter(fn($s)=>!$paid->has($s->id)&&!$s->feeExemption?->isActive())->count(),
            'total_collected' => $paid->sum('amount'),
        ];

        $meta = $this->schoolMeta();
        return view('staff-portal.sections.daily-fees', compact('students','paid','today','dailyRate','programs','summary','meta','branchId'));
    }

    // ── Timetable ──────────────────────────────────────────────────────────────

    public function timetable(Request $request)
    {
        $this->requirePermission('timetable');
        $programs  = Program::orderBy('sequence')->get();
        $lecturers = \App\Models\User::where('role','lecturer')->orderBy('full_name')->get();
        $selectedProgram = $request->filled('program_id') ? Program::find($request->program_id) : $programs->first();

        $entries = collect();
        if ($selectedProgram) {
            $entries = \App\Models\TimetableEntry::with(['lecturer','course','program'])
                ->where('program_id', $selectedProgram->id)
                ->orderBy('day_of_week')->orderBy('start_time')->get();
        }

        $grid = [];
        foreach (\App\Models\TimetableEntry::$dayNames as $day => $name) {
            $grid[$day] = $entries->where('day_of_week',$day)->values();
        }

        // Today's entries
        $date     = $request->filled('date') ? \Carbon\Carbon::parse($request->date) : \Carbon\Carbon::today();
        $dayNum   = (int) $date->format('N');
        $todayEntries = $entries->where('day_of_week', $dayNum)->values();

        $meta = $this->schoolMeta();
        return view('staff-portal.sections.timetable', compact('programs','lecturers','selectedProgram','entries','grid','todayEntries','date','dayNum','meta'));
    }

    // ── Calendar ───────────────────────────────────────────────────────────────

    public function calendar()
    {
        $this->requirePermission('calendar');
        $upcomingEvents = \App\Models\SchoolEvent::where('start_date','>=',today())
            ->orderBy('start_date')->take(10)->get();
        $meta = $this->schoolMeta();
        return view('staff-portal.sections.calendar', compact('upcomingEvents','meta'));
    }

    // ── Exams ──────────────────────────────────────────────────────────────────

    public function exams(Request $request)
    {
        $this->requirePermission('exams');
        $branchId = $this->branchId();
        $programs = Program::orderBy('sequence')->get();
        $sessions = AcademicSession::with('periods')->orderByDesc('year')->get();

        $query = ExamScore::with(['student.user','course','academicPeriod'])
            ->when($branchId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('church_branch_id', $branchId)))
            ->when($request->program_id, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('program_id', $request->program_id)))
            ->when($request->period_id, fn($q) => $q->where('academic_period_id', $request->period_id));

        $scores = $query->latest()->paginate(25)->withQueryString();
        $meta   = $this->schoolMeta();
        return view('staff-portal.sections.exams', compact('scores','programs','sessions','meta','branchId'));
    }

    // ── Exam Questions ─────────────────────────────────────────────────────────

    public function examQuestions(Request $request)
    {
        $this->requirePermission('exam_questions');

        $programs  = Program::orderBy('sequence')->get();
        $courses   = $request->filled('program_id')
            ? Course::where('program_id', $request->program_id)->orderBy('name')->get()
            : collect();

        $lecturers = \App\Models\User::whereIn('role', ['lecturer', 'teacher'])
            ->when($this->branchId(), fn($q) => $q->where('church_branch_id', $this->branchId()))
            ->orderBy('full_name')->get();

        try {
            $q = \App\Models\ExamQuestion::with(['lecturer','course','program'])->orderByDesc('created_at');

            if ($this->branchId()) {
                $q->where(function($query) {
                    $query->where('church_branch_id', $this->branchId())->orWhereNull('church_branch_id');
                });
            }

            if ($request->filled('program_id'))    { $q->where('program_id',    $request->program_id); }
            if ($request->filled('course_id'))     { $q->where('course_id',     $request->course_id); }
            if ($request->filled('lecturer_id'))   { $q->where('lecturer_id',   $request->lecturer_id); }
            if ($request->filled('academic_year')) { $q->where('academic_year', $request->academic_year); }
            if ($request->filled('term'))          { $q->where('term',          $request->term); }
            if ($request->filled('document_type')) { $q->where('document_type', $request->document_type); }

            $documents = $q->paginate(25)->withQueryString();
        } catch (\Throwable $e) {
            $documents = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
        }

        $years = array_map(
            fn($y) => $y.'/'.(intval($y)+1),
            range(date('Y'), date('Y')-4)
        );

        $meta = $this->schoolMeta();
        return view('admin.exams.questions', compact('documents','programs','courses','lecturers','years','meta'));
    }

    // ── Scheme of Learning ─────────────────────────────────────────────────────

    public function schemeOfLearning(Request $request)
    {
        $this->requirePermission('scheme_of_learning');
        $branchId = $this->branchId();

        $programs        = Program::orderBy('sequence')->get();
        $courses         = collect();
        $entries         = collect();
        $selectedProgram = null;
        $selectedCourse  = null;
        $selectedYear    = $request->get('academic_year', date('Y').'/'. (date('Y')+1));
        $years           = array_map(fn($y) => $y.'/'.(intval($y)+1), range(date('Y'), date('Y')-4));
        $isAllCourses    = false;

        if ($request->filled('program_id')) {
            $selectedProgram = Program::find($request->program_id);
            $courses = Course::where('program_id', $request->program_id)->orderBy('name')->get();
        }

        if ($selectedProgram && $request->filled('course_id')) {
            $isAllCourses   = $request->course_id === 'all';
            $selectedCourse = $isAllCourses ? null : Course::find($request->course_id);

            $query = \App\Models\SchemeOfLearning::with(['lecturer','course'])
                ->where('program_id', $selectedProgram->id)
                ->where('academic_year', $selectedYear);

            if (!$isAllCourses) {
                $query->where('course_id', $selectedCourse->id);
            }
            if ($branchId) {
                $query->where(function($q) use ($branchId) {
                    $q->where('church_branch_id', $branchId)->orWhereNull('church_branch_id');
                });
            }
            if ($request->filled('term')) {
                $query->where('term', $request->term);
            }
            $entries = $query->orderBy('course_id')->orderBy('week_number')->get();
        }

        return view('admin.scheme-of-learning.index', compact(
            'programs','courses','entries',
            'selectedProgram','selectedCourse','selectedYear','years','isAllCourses'
        ));
    }

    // ── Work Logs ──────────────────────────────────────────────────────────────

    public function workLogs(Request $request)
    {
        $this->requirePermission('work_logs');
        $branchId = $this->branchId();

        $filterLecturer = $request->lecturer_id;
        $filterCourse   = $request->course_id;
        $filterType     = $request->type;
        $filterDate     = $request->date_from;

        $loggerIds = collect();
        try {
            $loggerIds = \Illuminate\Support\Facades\DB::table('teacher_work_logs')->distinct()->pluck('lecturer_id');
        } catch (\Throwable $e) {}

        $roleQuery = \App\Models\User::where(function($q) {
            $q->whereIn('role', ['lecturer','teacher','Teacher'])
              ->orWhereRaw('LOWER(role) LIKE ?', ['%lecturer%'])
              ->orWhereRaw('LOWER(role) LIKE ?', ['%teacher%']);
        });
        if ($branchId) {
            $roleQuery->where(function($q) use ($branchId) {
                $q->where('church_branch_id', $branchId)->orWhereNull('church_branch_id');
            });
        }
        $lecturers = $roleQuery->orderBy('full_name')->get();

        if ($loggerIds->isNotEmpty()) {
            $extra = \App\Models\User::whereIn('id', $loggerIds)
                ->whereNotIn('id', $lecturers->pluck('id'))
                ->orderBy('full_name')->get();
            $lecturers = $lecturers->merge($extra)->sortBy('full_name')->values();
        }

        $assignments = collect();
        $assignmentsByLecturer = collect();
        try {
            $aQuery = \App\Models\CourseAssignment::with(['program','course','lecturer']);
            if ($branchId) {
                $aQuery->whereHas('lecturer', fn($q) => $q->where('church_branch_id', $branchId)->orWhereNull('church_branch_id'));
            }
            $assignments = $aQuery->get();
            $assignmentsByLecturer = $assignments->groupBy('lecturer_id');
        } catch (\Throwable $e) {}

        $allLogs = collect();
        try {
            $q = \App\Models\TeacherWorkLog::with(['lecturer','program','course'])->orderByDesc('date')->orderByDesc('id');
            if ($branchId) {
                $ids = \App\Models\User::where(fn($sq) => $sq->where('church_branch_id', $branchId)->orWhereNull('church_branch_id'))->pluck('id');
                $q->whereIn('lecturer_id', $ids);
            }
            if ($filterLecturer) $q->where('lecturer_id', $filterLecturer);
            if ($filterCourse)   $q->where('course_id',   $filterCourse);
            if ($filterType)     $q->where('type',        $filterType);
            if ($filterDate)     $q->where('date',       '>=', $filterDate);
            $allLogs = $q->get();
        } catch (\Throwable $e) {}

        $logsByLecturer = $allLogs->groupBy('lecturer_id');

        $stats = [];
        foreach ($assignments as $a) {
            $key = "{$a->lecturer_id}_{$a->program_id}_{$a->course_id}";
            if (!isset($stats[$key])) { $stats[$key] = ['classwork'=>0,'homework'=>0,'monthly_test'=>0]; }
        }
        foreach ($allLogs as $log) {
            $key = "{$log->lecturer_id}_{$log->program_id}_{$log->course_id}";
            if (!isset($stats[$key])) { $stats[$key] = ['classwork'=>0,'homework'=>0,'monthly_test'=>0]; }
            if (isset($stats[$key][$log->type])) { $stats[$key][$log->type]++; }
        }

        $courses = Course::with('program')->orderBy('name')->get();
        $globalExpected = [
            'classwork'    => (int) \App\Models\Setting::get('expected_classworks', 4),
            'homework'     => (int) \App\Models\Setting::get('expected_homeworks', 4),
            'monthly_test' => (int) \App\Models\Setting::get('expected_tests', 1),
        ];

        return view('admin.work-monitoring.index', compact(
            'lecturers','assignmentsByLecturer','stats',
            'logsByLecturer','courses','globalExpected',
            'allLogs','filterLecturer','filterCourse','filterType','filterDate'
        ));
    }

    // ── Programs ───────────────────────────────────────────────────────────────

    public function programs()
    {
        $this->requirePermission('programs');
        $programs = Program::withCount('students','courses')->orderBy('sequence')->get();
        $meta     = $this->schoolMeta();
        return view('staff-portal.sections.programs', compact('programs','meta'));
    }

    // ── Courses ────────────────────────────────────────────────────────────────

    public function courses(Request $request)
    {
        $this->requirePermission('courses');
        $programs = Program::orderBy('sequence')->get();
        $courses  = Course::with('program')
            ->withCount('enrollments')
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->orderBy('name')->paginate(20)->withQueryString();
        $meta     = $this->schoolMeta();
        return view('staff-portal.sections.courses', compact('courses','programs','meta'));
    }

    // ── Lecturers ──────────────────────────────────────────────────────────────

    public function lecturers(Request $request)
    {
        $this->requirePermission('lecturers');
        $branchId  = $this->branchId();
        $lecturers = \App\Models\User::where('role','lecturer')
            ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId))
            ->orderBy('full_name')->paginate(20);
        $meta = $this->schoolMeta();
        return view('staff-portal.sections.lecturers', compact('lecturers','meta','branchId'));
    }

    // ── Class Teachers ─────────────────────────────────────────────────────────

    public function classTeachers()
    {
        $this->requirePermission('class_teachers');
        $assignments = \App\Models\ClassTeacherAssignment::with(['lecturer','program','course'])
            ->when($this->branchId(), fn($q) => $q->whereHas('lecturer', fn($sq) => $sq->where('church_branch_id', $this->branchId())))
            ->get();
        $meta = $this->schoolMeta();
        return view('staff-portal.sections.class-teachers', compact('assignments','meta'));
    }

    // ── Notifications ──────────────────────────────────────────────────────────

    public function notifications()
    {
        $this->requirePermission('notifications');
        $branchId      = $this->branchId();
        $notifications = \App\Models\StudentNotification::with('student.user')
            ->when($branchId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('church_branch_id', $branchId)))
            ->latest()->paginate(20);
        $meta = $this->schoolMeta();
        return view('staff-portal.sections.notifications', compact('notifications','meta','branchId'));
    }

    // ── Promotions ─────────────────────────────────────────────────────────────

    public function promotions()
    {
        $this->requirePermission('promotions');
        $branchId = $this->branchId();
        $history  = \App\Models\PromotionHistory::with(['student.user','fromProgram','toProgram'])
            ->when($branchId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('church_branch_id', $branchId)))
            ->latest()->paginate(20);
        $meta = $this->schoolMeta();
        return view('staff-portal.sections.promotions', compact('history','meta','branchId'));
    }

    // ── Academic Sessions ──────────────────────────────────────────────────────

    public function academicSessions()
    {
        $this->requirePermission('academic_sessions');
        $sessions = AcademicSession::with('periods')->orderByDesc('year')->get();
        $meta     = $this->schoolMeta();
        return view('staff-portal.sections.academic-sessions', compact('sessions','meta'));
    }

    // ── Branches ───────────────────────────────────────────────────────────────

    public function branches()
    {
        $this->requirePermission('branches');
        $branches = \App\Models\ChurchBranch::withCount('students','users')->orderBy('name')->get();
        $meta     = $this->schoolMeta();
        return view('staff-portal.sections.branches', compact('branches','meta'));
    }

    // ── Settings ───────────────────────────────────────────────────────────────

    public function settings()
    {
        $this->requirePermission('settings');
        $settings = \App\Models\Setting::all()->pluck('value','key');
        $meta     = $this->schoolMeta();
        return view('staff-portal.sections.settings', compact('settings','meta'));
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function getAttendanceRate(?int $branchId): int
    {
        try {
            $q = Attendance::query()
                ->when($branchId, fn($q)=>$q->whereHas('student',fn($sq)=>$sq->where('church_branch_id',$branchId)));
            $total   = $q->count();
            $present = (clone $q)->where('status','present')->count();
            return $total > 0 ? round($present/$total*100) : 0;
        } catch (\Throwable $e) { return 0; }
    }
}
