<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\ExamScore;
use App\Models\GeneratedDocument;
use App\Models\Payment;
use App\Models\Program;
use App\Models\ProgramFee;
use App\Models\Student;
use App\Models\User;
use App\Services\DocumentVerificationService;
use App\Services\FeeCalculationService;
use App\Services\GradingScale;
use App\Services\ReportCardService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(
        private FeeCalculationService $feeService,
        private ReportCardService $reportCardService,
        private ReportService $reportService,
        private DocumentVerificationService $docService,
    ) {}

    // ══════════════════════════════════════════════════════════════════════════
    // EXISTING REPORTS (unchanged logic, kept intact)
    // ══════════════════════════════════════════════════════════════════════════

    /** Shared school branding for all PDF views */
    private function schoolMeta(): array
    {
        return [
            'schoolName'     => \App\Models\Setting::get('school_name',     config('app.name', 'School')),
            'schoolSubtitle' => \App\Models\Setting::get('school_subtitle', ''),
            'schoolAddress'  => \App\Models\Setting::get('school_address',  ''),
            'schoolPhone'    => \App\Models\Setting::get('school_phone',    ''),
            'programLabel'   => \Illuminate\Support\Str::singular(
                                    \App\Models\Setting::get('sidebar_programs_label', 'Program')),
            'siteLogoData'   => \App\Support\ReportCardPdfAssets::logoBase64(),
        ];
    }

    /**
     * Safely record a generated document — never crashes the PDF if the
     * generated_documents table doesn't exist yet (migration not run).
     */
    private function safeRecord(string $type, string $title, ?int $studentId = null, ?int $periodId = null, array $meta = []): ?\App\Models\GeneratedDocument
    {
        try {
            return $this->docService->record($type, $title, $studentId, $periodId, $meta);
        } catch (\Throwable $e) {
            \Log::warning("GeneratedDocument record() failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get QR URL — returns empty string if doc is null (safe for blade @if checks).
     */
    private function safeQr(?\App\Models\GeneratedDocument $doc, int $size = 120): string
    {
        if (! $doc) return '';
        try {
            return $this->docService->qrUrl($doc, $size);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /** Raise PHP memory limit for heavy PDF generation */
    private function raisePdfMemory(): void
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(600);
    }

    public function index()
    {
        // Redirect to the new comprehensive reports hub
        return redirect()->route('admin.reports.hub');
    }

    public function students(Request $request)
    {
        $branchId = auth()->check() && auth()->user()->isBranchAdmin() ? auth()->user()->church_branch_id : null;
        $query = Student::with(['user', 'program', 'churchBranch', 'enrollments'])
            ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId));
        if ($request->filled('program_id')) $query->where('program_id', $request->program_id);
        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('branch_id'))  $query->where('church_branch_id', $request->branch_id);

        $students = $query->latest()->paginate(15)->withQueryString();
        $programs = Program::orderBy('sequence')->get();
        return view('admin.reports.students', compact('students', 'programs'));
    }

    public function attendance(Request $request)
    {
        $branchId = auth()->check() && auth()->user()->isBranchAdmin() ? auth()->user()->church_branch_id : null;
        $query = Attendance::with(['student.user', 'course', 'academicPeriod'])
            ->when($branchId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('church_branch_id', $branchId)));
        if ($request->filled('course_id')) $query->where('course_id', $request->course_id);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('from'))      $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))        $query->whereDate('date', '<=', $request->to);

        $attendances    = $query->latest()->paginate(25)->withQueryString();
        $courses        = Course::with('program')->get();
        $studentSummary = Student::with('user')->get()->map(function ($s) {
            $total   = $s->attendances()->count();
            $present = $s->attendances()->where('status', 'present')->count();
            return ['student' => $s, 'total' => $total, 'present' => $present,
                    'rate' => $total > 0 ? round(($present / $total) * 100) : 0];
        })->sortByDesc('rate')->values();

        return view('admin.reports.attendance', compact('attendances', 'courses', 'studentSummary'));
    }

    public function financial(Request $request)
    {
        $branchId = auth()->check() && auth()->user()->isBranchAdmin() ? auth()->user()->church_branch_id : null;
        $query = Student::with(['user', 'program', 'payments'])
            ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId));
        if ($request->filled('program_id')) $query->where('program_id', $request->program_id);

        $students = $query->latest()->paginate(15)->withQueryString();
        $programs = Program::orderBy('sequence')->get();
        $global   = $this->feeService->getSummary();

        $studentFees = $students->map(fn($s) => array_merge(['student' => $s], $this->feeService->calculate($s)));

        return view('admin.reports.financial', compact('students', 'studentFees', 'programs', 'global'));
    }

    public function programs(Request $request)
    {
        $programs = Program::with('courses')->withCount('students')->orderBy('sequence')->get();

        $branchId = null;
        if ($request->filled('branch_id')) {
            $branchId = $request->branch_id;
        } elseif (auth()->check() && auth()->user()->isBranchAdmin()) {
            $branchId = auth()->user()->church_branch_id;
        }

        if ($branchId) {
            foreach ($programs as $p) {
                $students = $p->students()->with('user')->where('church_branch_id', $branchId)->get();
                $p->setRelation('students', $students);
                $p->students_count = $students->count();
            }
        } else {
            $programs->load('students.user');
        }

        return view('admin.reports.programs', compact('programs'));
    }

    public function courses(Request $request)
    {
        $query = Course::with(['program', 'enrollments'])->withCount('enrollments');
        if ($request->filled('program_id')) $query->where('program_id', $request->program_id);

        $courses  = $query->get();
        $programs = Program::orderBy('sequence')->get();
        return view('admin.reports.courses', compact('courses', 'programs'));
    }

    public function financialPdf(Request $request)
    {
        $this->raisePdfMemory();
        $students    = Student::with(['user', 'program', 'payments'])->get();
        $studentFees = $students->map(fn($s) => array_merge(['student' => $s], $this->feeService->calculate($s)));
        $global      = $this->feeService->getSummary();
        $pdf = Pdf::loadView('pdf.financial_report', array_merge(
            compact('studentFees', 'global'),
            $this->schoolMeta(),
            ['programLabelSingular' => \Illuminate\Support\Str::singular(\App\Models\Setting::get('sidebar_programs_label', 'Program'))]
        ))->setPaper('a4');
        return $pdf->download('financial_report_' . now()->format('Y-m-d') . '.pdf');
    }

    public function studentsPdf(Request $request)
    {
        $this->raisePdfMemory();
        $students = Student::with(['user', 'program', 'churchBranch'])->get();
        $pdf = Pdf::loadView('pdf.students_report', array_merge(
            compact('students'),
            $this->schoolMeta(),
            ['programLabelSingular' => \Illuminate\Support\Str::singular(\App\Models\Setting::get('sidebar_programs_label', 'Program'))]
        ))->setPaper('a4');
        return $pdf->download('students_report_' . now()->format('Y-m-d') . '.pdf');
    }

    public function attendancePdf(Request $request)
    {
        $this->raisePdfMemory();
        $attendances = Attendance::with(['student.user', 'course'])->latest()->get();
        $pdf = Pdf::loadView('pdf.attendance_report', array_merge(
            compact('attendances'),
            $this->schoolMeta()
        ))->setPaper('a4', 'landscape');
        return $pdf->download('attendance_report_' . now()->format('Y-m-d') . '.pdf');
    }

    // ─── Excel Exports ────────────────────────────────────────────────────────

    public function studentsExcel(Request $request)
    {
        $students = Student::with(['user', 'program', 'churchBranch'])
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->branch_id,  fn($q) => $q->where('church_branch_id', $request->branch_id))
            ->latest()->get();
        $filename = 'students_report_' . now()->format('Y-m-d') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StudentsReportExport($students), $filename
        );
    }

    public function attendanceExcel(Request $request)
    {
        $attendances = Attendance::with(['student.user', 'course'])
            ->when($request->course_id, fn($q) => $q->where('course_id', $request->course_id))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->when($request->from,      fn($q) => $q->whereDate('date', '>=', $request->from))
            ->when($request->to,        fn($q) => $q->whereDate('date', '<=', $request->to))
            ->latest()->get();
        $filename = 'attendance_report_' . now()->format('Y-m-d') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceReportExport($attendances), $filename
        );
    }

    public function financialExcel(Request $request)
    {
        $students    = Student::with(['user', 'program', 'payments'])
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->latest()->get();
        $studentFees = $students->map(fn($s) => array_merge(['student' => $s], $this->feeService->calculate($s)));
        $filename    = 'financial_report_' . now()->format('Y-m-d') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\FinancialReportExport($studentFees), $filename
        );
    }

    public function programsPdf(Request $request)
    {
        $this->raisePdfMemory();
        $programs = Program::with('courses')->withCount('students')->orderBy('sequence')->get();
        $branchId = $request->branch_id ?? (auth()->check() && auth()->user()->isBranchAdmin() ? auth()->user()->church_branch_id : null);
        if ($branchId) {
            foreach ($programs as $p) {
                $students = $p->students()->with('user')->where('church_branch_id', $branchId)->get();
                $p->setRelation('students', $students);
                $p->students_count = $students->count();
            }
        } else {
            $programs->load('students.user');
        }
        $pdf = Pdf::loadView('pdf.programs_report', array_merge(
            compact('programs'),
            $this->schoolMeta()
        ))->setPaper('a4');
        return $pdf->download('programs_report_' . now()->format('Y-m-d') . '.pdf');
    }

    public function programsExcel(Request $request)
    {
        $programs = Program::with('courses')->withCount('students')->orderBy('sequence')->get();
        $branchId = $request->branch_id ?? (auth()->check() && auth()->user()->isBranchAdmin() ? auth()->user()->church_branch_id : null);
        if ($branchId) {
            foreach ($programs as $p) {
                $students = $p->students()->where('church_branch_id', $branchId)->get();
                $p->setRelation('students', $students);
                $p->students_count = $students->count();
            }
        } else {
            $programs->load('students');
        }
        $filename = 'programs_report_' . now()->format('Y-m-d') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProgramsReportExport($programs), $filename
        );
    }

    public function coursesPdf(Request $request)
    {
        $this->raisePdfMemory();
        $courses = Course::with(['program', 'enrollments'])
            ->withCount('enrollments')
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->get();
        $pdf = Pdf::loadView('pdf.courses_report', array_merge(
            compact('courses'),
            $this->schoolMeta()
        ))->setPaper('a4');
        return $pdf->download('courses_report_' . now()->format('Y-m-d') . '.pdf');
    }

    public function coursesExcel(Request $request)
    {
        $courses = Course::with(['program', 'enrollments'])
            ->withCount('enrollments')
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->get();
        $filename = 'courses_report_' . now()->format('Y-m-d') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CoursesReportExport($courses), $filename
        );
    }

    // ─── Preview Methods ──────────────────────────────────────────────────────

    public function studentsPreview(Request $request)
    {
        $query = Student::with(['user', 'program', 'churchBranch', 'enrollments']);
        if ($request->filled('program_id')) $query->where('program_id', $request->program_id);
        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('branch_id'))  $query->where('church_branch_id', $request->branch_id);
        $students = $query->latest()->get();
        $programs = Program::orderBy('sequence')->get();
        return view('admin.reports.preview.students', compact('students', 'programs'));
    }

    public function attendancePreview(Request $request)
    {
        $query = Attendance::with(['student.user', 'course', 'academicPeriod']);
        if ($request->filled('course_id')) $query->where('course_id', $request->course_id);
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('from'))      $query->whereDate('date', '>=', $request->from);
        if ($request->filled('to'))        $query->whereDate('date', '<=', $request->to);
        $attendances = $query->latest()->get();
        $courses     = Course::with('program')->get();
        return view('admin.reports.preview.attendance', compact('attendances', 'courses'));
    }

    public function financialPreview(Request $request)
    {
        $students = Student::with(['user', 'program', 'payments'])
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->latest()->get();
        $studentFees = $students->map(fn($s) => array_merge(['student' => $s], $this->feeService->calculate($s)));
        $programs    = Program::orderBy('sequence')->get();
        $global      = $this->feeService->getSummary();
        return view('admin.reports.preview.financial', compact('students', 'studentFees', 'programs', 'global'));
    }

    public function programsPreview(Request $request)
    {
        $programs = Program::with('courses')->withCount('students')->orderBy('sequence')->get();
        $branchId = $request->branch_id ?? (auth()->check() && auth()->user()->isBranchAdmin() ? auth()->user()->church_branch_id : null);
        if ($branchId) {
            foreach ($programs as $p) {
                $students = $p->students()->with('user')->where('church_branch_id', $branchId)->get();
                $p->setRelation('students', $students);
                $p->students_count = $students->count();
            }
        } else {
            $programs->load('students.user');
        }
        return view('admin.reports.preview.programs', compact('programs'));
    }

    public function coursesPreview(Request $request)
    {
        $courses = Course::with(['program', 'enrollments'])
            ->withCount('enrollments')
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->get();
        $programs = Program::orderBy('sequence')->get();
        return view('admin.reports.preview.courses', compact('courses', 'programs'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // NEW REPORT ENDPOINTS
    // ══════════════════════════════════════════════════════════════════════════

    // ── Reports Hub Dashboard ─────────────────────────────────────────────────

    public function hub()
    {
        try {
            $isBranch = auth()->check() && auth()->user()->isBranchAdmin();
            $branchId = $isBranch ? auth()->user()->church_branch_id : null;

            $summary = [
                'total_students'  => Student::when($branchId, fn($q)=>$q->where('church_branch_id',$branchId))->count(),
                'active_students' => Student::when($branchId, fn($q)=>$q->where('church_branch_id',$branchId))->where('status','active')->count(),
                'graduated'       => Student::when($branchId, fn($q)=>$q->where('church_branch_id',$branchId))->where('status','graduated')->count(),
                'total_programs'  => Program::count(),
                'total_courses'   => Course::count(),
            ];
            $total   = Attendance::when($branchId, fn($q)=>$q->whereHas('student',fn($sq)=>$sq->where('church_branch_id',$branchId)))->count();
            $present = Attendance::when($branchId, fn($q)=>$q->whereHas('student',fn($sq)=>$sq->where('church_branch_id',$branchId)))->where('status','present')->count();
            $summary['attendance_rate'] = $total > 0 ? round($present/$total*100) : 0;
            $summary['fees']            = $this->feeService->getSummary($branchId);

            $activePeriod = AcademicSession::activePeriod();
            $programs     = Program::orderBy('sequence')->get();
            $sessions     = AcademicSession::with('periods')->orderByDesc('year')->get();

            try {
                $submissionStatus = $this->reportService->markSubmissionStatus($activePeriod);
                $completeCount    = $submissionStatus['total_complete'];
                $pendingCount     = $submissionStatus['total_pending'];
            } catch (\Throwable $e) {
                \Log::warning('hub: markSubmissionStatus failed: ' . $e->getMessage());
                $completeCount = 0;
                $pendingCount  = 0;
            }

            return view('admin.reports.hub', compact('summary','activePeriod','programs','sessions','completeCount','pendingCount'));
        } catch (\Throwable $e) {
            \Log::error('Reports hub failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $summary = ['total_students'=>0,'active_students'=>0,'graduated'=>0,'total_programs'=>0,'total_courses'=>0,'attendance_rate'=>0,'fees'=>['billed'=>0,'collected'=>0,'outstanding'=>0]];
            $activePeriod  = null;
            $programs      = collect();
            $sessions      = collect();
            $completeCount = 0;
            $pendingCount  = 0;
            return view('admin.reports.hub', compact('summary','activePeriod','programs','sessions','completeCount','pendingCount'))
                ->with('error', 'Some report data could not be loaded. Please check system logs.');
        }
    }

    // ── Class Performance Report ───────────────────────────────────────────────

    public function classPerformance(Request $request)
    {
        $programs = Program::orderBy('sequence')->get();
        $sessions = AcademicSession::with('periods')->orderByDesc('year')->get();
        $data     = null;

        if ($request->filled('program_id')) {
            try {
                $program = Program::findOrFail($request->program_id);
                $period  = $request->filled('period_id') ? AcademicPeriod::find($request->period_id) : null;
                $attempt = (int) $request->get('attempt', 1);
                $data    = $this->reportService->classPerformance($program, $period, $attempt);
            } catch (\Throwable $e) {
                \Log::error('Admin classPerformance error: ' . $e->getMessage());
                return back()->with('error', 'Could not generate report: ' . $e->getMessage());
            }
        }

        return view('admin.reports.class-performance', compact('programs', 'sessions', 'data'));
    }

    public function classPerformancePdf(Request $request)
    {
        $this->raisePdfMemory();
        $request->validate(['program_id' => 'required|exists:programs,id']);
        $program = Program::findOrFail($request->program_id);
        $period  = $request->filled('period_id') ? AcademicPeriod::find($request->period_id) : null;
        $attempt = (int) $request->get('attempt', 1);
        $data    = $this->reportService->classPerformance($program, $period, $attempt);

        $doc = $this->safeRecord(
            'class_performance',
            "Class Performance — {$program->name}",
            null, $period?->id,
            ['program_id' => $program->id, 'attempt' => $attempt]
        );
        $qrUrl = $this->safeQr($doc);

        $pdf = Pdf::loadView('pdf.class_performance', array_merge(compact('data', 'doc', 'qrUrl'), $this->schoolMeta()))
            ->setPaper('a4', 'landscape');
        return $pdf->download("class_performance_{$program->name}_" . now()->format('Ymd') . '.pdf');
    }

    // ── Student Academic Report (enhanced view) ────────────────────────────────

    public function studentAcademic(Request $request)
    {
        $programs = Program::orderBy('sequence')->get();
        $students = collect();
        $data     = null;
        $student  = null;

        if ($request->filled('program_id')) {
            $students = Student::with('user')
                ->where('program_id', $request->program_id)
                ->forExams()->orderBy('student_id')->get();
        }

        if ($request->filled('student_id') && $request->filled('program_id')) {
            $student = Student::with(['user', 'program'])->findOrFail($request->student_id);
            $program = Program::findOrFail($request->program_id);
            $attempt = (int) $request->get('attempt', 1);
            $data    = $this->reportCardService->generate($student, $program, $attempt);

            // Attendance for this program
            $period  = AcademicSession::activePeriod();
            $attQuery = Attendance::where('student_id', $student->id)
                ->where('program_id', $program->id);
            if ($period) $attQuery->where('academic_period_id', $period->id);
            $attRecords   = $attQuery->get();
            $schoolDays   = $attRecords->pluck('date')->unique()->count();
            $presentDays  = $attRecords->where('status', 'present')->count();
            $absentDays   = $attRecords->where('status', 'absent')->count();
            $attPct       = $schoolDays > 0 ? round($presentDays / $schoolDays * 100, 1) : 0;

            $data = array_merge($data, [
                'school_days'  => $schoolDays,
                'present_days' => $presentDays,
                'absent_days'  => $absentDays,
                'att_pct'      => $attPct,
                'period'       => $period,
            ]);
        }

        return view('admin.reports.student-academic', compact('programs', 'students', 'data', 'student'));
    }

    public function studentAcademicPdf(Request $request)
    {
        $this->raisePdfMemory();
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'program_id' => 'required|exists:programs,id',
        ]);

        $student = Student::with(['user', 'program'])->findOrFail($request->student_id);
        $program = Program::findOrFail($request->program_id);
        $attempt = (int) $request->get('attempt', 1);
        $data    = $this->reportCardService->generate($student, $program, $attempt);

        $period = AcademicSession::activePeriod();
        $attQ   = Attendance::where('student_id', $student->id)->where('program_id', $program->id);
        if ($period) $attQ->where('academic_period_id', $period->id);
        $attRecords  = $attQ->get();
        $schoolDays  = $attRecords->pluck('date')->unique()->count();
        $presentDays = $attRecords->where('status', 'present')->count();

        $data = array_merge($data, [
            'school_days'  => $schoolDays,
            'present_days' => $presentDays,
            'absent_days'  => $schoolDays - $presentDays,
            'att_pct'      => $schoolDays > 0 ? round($presentDays / $schoolDays * 100, 1) : 0,
            'period'       => $period,
        ]);

        $doc = $this->safeRecord(
            'report_card',
            "Academic Report — {$student->user->full_name}",
            $student->id, $period?->id,
            ['program_id' => $program->id, 'attempt' => $attempt]
        );
        $qrUrl = $this->safeQr($doc);

        $pdf = Pdf::loadView('pdf.student_academic_report', array_merge(compact('data', 'student', 'program', 'doc', 'qrUrl', 'attempt'), $this->schoolMeta()))
            ->setPaper('a4');
        return $pdf->download("report_card_{$student->student_id}_" . now()->format('Ymd') . '.pdf');
    }

    // ── Student Performance Report ────────────────────────────────────────────

    public function studentPerformance(Request $request)
    {
        $programs = Program::orderBy('sequence')->get();
        $students = collect();
        $data     = null;
        $student  = null;

        if ($request->filled('program_id')) {
            $students = Student::with('user')
                ->where('program_id', $request->program_id)
                ->forExams()->orderBy('student_id')->get();
        }

        if ($request->filled('student_id')) {
            $student  = Student::with(['user','program'])->findOrFail($request->student_id);
            $allScores = ExamScore::with(['course','program'])
                ->where('student_id', $student->id)
                ->get();

            // Build trend: one entry per program × attempt
            $trend = [];
            foreach ($programs as $prog) {
                $attempts = $allScores->where('program_id', $prog->id)
                    ->pluck('attempt')->unique()->sort()->values();
                foreach ($attempts as $att) {
                    $card = $this->reportCardService->generate($student, $prog, $att);
                    if (! $card['has_scores']) continue;
                    $trend[] = [
                        'program'   => $prog,
                        'attempt'   => $att,
                        'average'   => $card['overall_average'],
                        'grade'     => $card['overall_grade'],
                        'result'    => $card['result'],
                        'position'  => $card['overall_position'],
                        'of'        => $card['total_students_in_exam'],
                        'courses'   => $card['courses'],
                    ];
                }
            }

            // Subject-level trend (for chart): group by course name
            $subjectTrend = [];
            foreach ($trend as $t) {
                foreach ($t['courses'] as $row) {
                    $name = $row['course']->name;
                    $subjectTrend[$name][] = [
                        'label'     => $t['program']->name . ' A' . $t['attempt'],
                        'aggregate' => $row['aggregate'],
                    ];
                }
            }

            $data = [
                'student'       => $student,
                'trend'         => $trend,
                'subject_trend' => $subjectTrend,
                'best'          => count($trend) ? collect($trend)->max('average') : null,
                'worst'         => count($trend) ? collect($trend)->min('average') : null,
                'overall_avg'   => count($trend) ? round(collect($trend)->avg('average'), 2) : null,
            ];
        }

        return view('admin.reports.student-performance', compact('programs','students','data','student'));
    }

    public function studentPerformancePdf(Request $request)
    {
        $this->raisePdfMemory();
        $request->validate(['student_id'=>'required|exists:students,id']);
        $student  = Student::with(['user','program'])->findOrFail($request->student_id);
        $programs = Program::orderBy('sequence')->get();
        $allScores = ExamScore::with(['course','program'])->where('student_id',$student->id)->get();

        $trend = [];
        foreach ($programs as $prog) {
            $attempts = $allScores->where('program_id',$prog->id)->pluck('attempt')->unique()->sort()->values();
            foreach ($attempts as $att) {
                $card = $this->reportCardService->generate($student,$prog,$att);
                if (!$card['has_scores']) continue;
                $trend[] = ['program'=>$prog,'attempt'=>$att,'average'=>$card['overall_average'],
                    'grade'=>$card['overall_grade'],'result'=>$card['result'],
                    'position'=>$card['overall_position'],'of'=>$card['total_students_in_exam'],
                    'courses'=>$card['courses']];
            }
        }

        $data = ['student'=>$student,'trend'=>$trend,
            'best'=>count($trend)?collect($trend)->max('average'):null,
            'worst'=>count($trend)?collect($trend)->min('average'):null,
            'overall_avg'=>count($trend)?round(collect($trend)->avg('average'),2):null];

        $doc = $this->safeRecord('student_performance',
            "Student Performance — {$student->user->full_name}", $student->id, null,
            ['programs'=>count($trend)]);
        $qrUrl = $this->safeQr($doc);

        $pdf = Pdf::loadView('pdf.student_performance', array_merge(compact('data','student','doc','qrUrl'), $this->schoolMeta()))
            ->setPaper('a4');
        return $pdf->download("performance_{$student->student_id}_".now()->format('Ymd').'.pdf');
    }

    // ── Transcript ────────────────────────────────────────────────────────────

    public function transcript(Request $request)
    {
        $students = Student::with('user')->forExams()->orderBy('student_id')->get();
        $data     = null;
        $student  = null;

        if ($request->filled('student_id')) {
            $student = Student::with(['user', 'program'])->findOrFail($request->student_id);
            $data    = $this->reportService->transcript($student);
        }

        return view('admin.reports.transcript', compact('students', 'data', 'student'));
    }

    public function transcriptPdf(Request $request)
    {
        $this->raisePdfMemory();
        $request->validate(['student_id' => 'required|exists:students,id']);
        $student = Student::with(['user', 'program'])->findOrFail($request->student_id);
        $data    = $this->reportService->transcript($student);

        $doc = $this->safeRecord(
            'transcript',
            "Academic Transcript — {$student->user->full_name}",
            $student->id, null,
            ['generated_at' => now()->toDateTimeString()]
        );
        $qrUrl = $this->safeQr($doc);

        $pdf = Pdf::loadView('pdf.transcript', array_merge(compact('data', 'student', 'doc', 'qrUrl'), $this->schoolMeta()))
            ->setPaper('a4');
        return $pdf->download("transcript_{$student->student_id}_" . now()->format('Ymd') . '.pdf');
    }

    // ── Attendance Summary ─────────────────────────────────────────────────────

    public function attendanceSummary(Request $request)
    {
        $programs = Program::orderBy('sequence')->get();
        $sessions = AcademicSession::with('periods')->orderByDesc('year')->get();
        $data     = null;

        if ($request->filled('program_id')) {
            $program = Program::findOrFail($request->program_id);
            $period  = $request->filled('period_id') ? AcademicPeriod::find($request->period_id) : null;
            $data    = $this->reportService->attendanceSummary($program, $period);
        }

        return view('admin.reports.attendance-summary', compact('programs', 'sessions', 'data'));
    }

    public function attendanceSummaryPdf(Request $request)
    {
        $this->raisePdfMemory();
        $request->validate(['program_id' => 'required|exists:programs,id']);
        $program = Program::findOrFail($request->program_id);
        $period  = $request->filled('period_id') ? AcademicPeriod::find($request->period_id) : null;
        $data    = $this->reportService->attendanceSummary($program, $period);

        $doc = $this->safeRecord(
            'attendance_summary',
            "Attendance Summary — {$program->name}",
            null, $period?->id,
            ['program_id' => $program->id]
        );
        $qrUrl = $this->safeQr($doc);

        $pdf = Pdf::loadView('pdf.attendance_summary', array_merge(compact('data', 'doc', 'qrUrl'), $this->schoolMeta()))
            ->setPaper('a4');
        return $pdf->download("attendance_summary_{$program->name}_" . now()->format('Ymd') . '.pdf');
    }

    // ── Teacher Mark Submission Status ─────────────────────────────────────────

    public function markSubmission(Request $request)
    {
        $programs     = Program::orderBy('sequence')->get();
        $sessions     = AcademicSession::with('periods')->orderByDesc('year')->get();
        $activePeriod = AcademicSession::activePeriod();
        $period       = $request->filled('period_id')
            ? AcademicPeriod::find($request->period_id)
            : $activePeriod;
        $programId = $request->filled('program_id') ? (int)$request->program_id : null;
        $data      = $this->reportService->markSubmissionStatus($period, $programId);

        return view('admin.reports.mark-submission', compact('programs', 'sessions', 'data', 'period', 'activePeriod'));
    }

    // ── Fee Statement (per student) ────────────────────────────────────────────

    public function feeStatement(Request $request)
    {
        $branchId = auth()->check() && auth()->user()->isBranchAdmin() ? auth()->user()->church_branch_id : null;
        $programs = Program::orderBy('sequence')->get();
        $students = collect();
        $student  = null;
        $fees     = null;

        if ($request->filled('program_id')) {
            $students = Student::with('user')
                ->where('program_id', $request->program_id)
                ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId))
                ->orderBy('student_id')->get();
        }

        if ($request->filled('student_id')) {
            $student = Student::with(['user', 'program', 'payments'])->findOrFail($request->student_id);
            $fees    = $this->feeService->calculate($student);
        }

        return view('admin.reports.fee-statement', compact('programs', 'students', 'student', 'fees'));
    }

    public function feeStatementPdf(Request $request)
    {
        $this->raisePdfMemory();
        $request->validate(['student_id' => 'required|exists:students,id']);
        $student = Student::with(['user', 'program', 'payments'])->findOrFail($request->student_id);
        $raw     = $this->feeService->calculate($student);

        // Always normalise to billed/paid/balance — PDF view uses these keys
        $fees = [
            'billed'  => (float)($raw['total'] ?? 0) + (float)($raw['exam_total'] ?? 0),
            'paid'    => (float)($raw['paid']   ?? 0),
            'balance' => (float)($raw['balance'] ?? 0),
        ];

        $studentName = $student->user?->full_name ?? 'Student';
        $doc   = $this->safeRecord('fee_statement', "Fee Statement — {$studentName}", $student->id, null, $fees);
        $qrUrl = $this->safeQr($doc);
        $meta  = $this->schoolMeta();
        $schoolName = $meta['schoolName'];

        $pdf = Pdf::loadView('pdf.fee_statement', array_merge(compact('student','fees','doc','qrUrl','schoolName'), $meta))
            ->setPaper('a4');
        return $pdf->download("fee_statement_{$student->student_id}_" . now()->format('Ymd') . '.pdf');
    }

    // ── Bulk PDF: all students in a class (academic reports) ─────────────────

    public function bulkAcademicPdf(Request $request)
    {
        $this->raisePdfMemory();
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'attempt'    => 'nullable|integer|min:1',
        ]);

        $program  = Program::findOrFail($request->program_id);
        $attempt  = (int) $request->get('attempt', 1);
        $students = Student::with(['user','program'])
            ->where('program_id', $program->id)
            ->forExams()->orderBy('student_id')->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students found for this program.');
        }

        $period = AcademicSession::activePeriod();
        $meta   = $this->schoolMeta();
        $cards  = [];

        foreach ($students as $student) {
            $card = $this->reportCardService->generate($student, $program, $attempt);
            if (! $card['has_scores']) continue;

            $attQ        = Attendance::where('student_id', $student->id)->where('program_id', $program->id);
            if ($period) $attQ->where('academic_period_id', $period->id);
            $attRecords  = $attQ->get();
            $schoolDays  = $attRecords->pluck('date')->unique()->count();
            $presentDays = $attRecords->where('status','present')->count();
            $card = array_merge($card, [
                'school_days'  => $schoolDays,
                'present_days' => $presentDays,
                'absent_days'  => $schoolDays - $presentDays,
                'att_pct'      => $schoolDays > 0 ? round($presentDays/$schoolDays*100,1) : 0,
                'period'       => $period,
            ]);

            $doc   = $this->safeRecord('report_card',
                "Academic Report — {$student->user->full_name}",
                $student->id, $period?->id,
                ['program_id'=>$program->id,'attempt'=>$attempt,'bulk'=>true]);
            $qrUrl = $this->safeQr($doc);

            $cards[] = ['data'=>$card,'student'=>$student,'program'=>$program,'doc'=>$doc,'qrUrl'=>$qrUrl];
        }

        if (empty($cards)) {
            return back()->with('error', 'No scores found for any student in this program/attempt.');
        }

        $pdf = Pdf::loadView('pdf.bulk_academic_reports',
            array_merge(['cards'=>$cards,'attempt'=>$attempt], $meta))
            ->setPaper('a4');

        return $pdf->download("bulk_reports_{$program->name}_attempt{$attempt}_".now()->format('Ymd').'.pdf');
    }

    // ── Bulk PDF: attendance summary for all programs ─────────────────────────

    public function bulkAttendancePdf(Request $request)
    {
        $this->raisePdfMemory();
        $request->validate([
            'program_id' => 'required|exists:programs,id',
        ]);

        $program = Program::findOrFail($request->program_id);
        $period  = $request->filled('period_id') ? AcademicPeriod::find($request->period_id) : null;
        $data    = $this->reportService->attendanceSummary($program, $period);

        $doc   = $this->safeRecord('attendance_summary',
            "Bulk Attendance — {$program->name}", null, $period?->id,
            ['program_id'=>$program->id,'bulk'=>true]);
        $qrUrl = $this->safeQr($doc);

        $pdf = Pdf::loadView('pdf.attendance_summary',
            array_merge(compact('data','doc','qrUrl'), $this->schoolMeta()))
            ->setPaper('a4');

        return $pdf->download("attendance_{$program->name}_".now()->format('Ymd').'.pdf');
    }

    // ── Document History ──────────────────────────────────────────────────────

    public function documentHistory(Request $request)
    {
        $docs = GeneratedDocument::with(['student.user'])
            ->when($request->type, fn($q) => $q->where('document_type', $request->type))
            ->when($request->student_id, fn($q) => $q->where('student_id', $request->student_id))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $students = Student::with('user')->forExams()->orderBy('student_id')->get();
        return view('admin.reports.document-history', compact('docs', 'students'));
    }
}


