<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AcademicPeriod;
use App\Models\AcademicSession;
use App\Models\ClassTeacherAssignment;
use App\Models\CourseAssignment;
use App\Models\ExamScore;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Course;
use App\Models\Setting;
use App\Models\Student;
use App\Services\ReportCardService;
use App\Services\ReportService;
use App\Services\DocumentVerificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(
        private ReportCardService $reportCardService,
        private ReportService $reportService,
        private DocumentVerificationService $docService,
    ) {}

    public function index()
    {
        abort_unless(Setting::lecturerCan('exams'), 403, 'Exam score entry has been disabled by the administrator.');
        $lecturer = auth()->user();
        $assignments = CourseAssignment::with(['course', 'program'])
            ->where('lecturer_id', $lecturer->id)
            ->get();

        return view('lecturer.exams.index', compact('assignments'));
    }

    public function create(Request $request)
    {
        abort_unless(Setting::lecturerCan('exams'), 403, 'Exam score entry has been disabled by the administrator.');
        $lecturer = auth()->user();
        $assignments = CourseAssignment::with(['course', 'program'])
            ->where('lecturer_id', $lecturer->id)
            ->get();

        $selectedAssignment = null;
        $selectedProgram = null;
        $students = collect();
        $courses = collect();
        $existing = collect();
        $selectedStudent = null;
        $scores = null;
        $attempts = collect();
        $currentAttempt = 1;
        $quizPercentage = 30;
        $examPercentage = 70;

        $sbaSubWeights = ReportCardService::getSbaSubWeights();
        $sbaSubLabels  = ReportCardService::getSbaSubLabels();
        $totalSubW     = ReportCardService::getTotalSubWeight();

        if ($request->filled('assignment')) {
            [$courseId, $programId] = explode('|', $request->assignment);
            $selectedAssignment = $assignments->first(fn($assignment) => $assignment->course_id == $courseId && $assignment->program_id == $programId);

            if (! $selectedAssignment) {
                abort(403, 'Unauthorized assignment selection.');
            }

            $selectedProgram = $selectedAssignment->program;
            $courses = Course::where('id', $courseId)->get();

            $students = $lecturer->lecturerStudentQuery((int)$programId)
                ->whereHas('enrollments', fn($q) => $q->where('course_id', $courseId))
                ->with('user')->orderBy('student_id')->get();
        } elseif ($request->filled('program_id')) {
            $selectedProgram = Program::find($request->program_id);
            if ($selectedProgram) {
                $assignedCourseIds = $assignments->where('program_id', $selectedProgram->id)->pluck('course_id');
                $courses = Course::whereIn('id', $assignedCourseIds)->orderBy('name')->get();
                $students = $lecturer->lecturerStudentQuery($selectedProgram->id)
                    ->forExams()->with('user')->orderBy('student_id')->get();
            }
        }

        if ($request->filled('student_id') && ($selectedAssignment || $selectedProgram)) {
            $selectedStudent = Student::with('user')->find($request->student_id);
            if ($selectedStudent) {
                $prog = $selectedProgram ?: $selectedStudent->program;
                if ($prog) {
                    $selectedProgram = $prog;
                    $currentAttempt = $request->filled('attempt') ? (int) $request->attempt : 1;

                    $attempts = ExamScore::where('student_id', $selectedStudent->id)
                        ->where('program_id', $selectedProgram->id)
                        ->distinct()
                        ->orderBy('attempt')
                        ->pluck('attempt');

                    if ($attempts->isNotEmpty() && ! $request->filled('attempt')) {
                        $currentAttempt = $attempts->last();
                    }

                    $existing = ExamScore::where('student_id', $selectedStudent->id)
                        ->where('program_id', $selectedProgram->id)
                        ->where('attempt', $currentAttempt)
                        ->get()
                        ->keyBy('course_id');

                    if ($selectedAssignment) {
                        $scores = $existing->get($selectedAssignment->course_id);
                    }

                    [$quizPercentage, $examPercentage] = ReportCardService::getPercentages($selectedProgram, $currentAttempt);
                }
            }
        }

        return view('lecturer.exams.create', compact(
            'assignments', 'selectedAssignment', 'selectedProgram', 'students', 'courses', 'selectedStudent',
            'scores', 'existing', 'attempts', 'currentAttempt', 'quizPercentage', 'examPercentage',
            'sbaSubWeights', 'sbaSubLabels', 'totalSubW'
        ));
    }

    // ── Batch score entry ─────────────────────────────────────────────────────

    public function batchScores(int $courseId, int $programId)
    {
        abort_unless(\App\Models\Setting::lecturerCan('exams'), 403, 'Exam score entry has been disabled.');

        $lecturer = auth()->user();

        $assignment = \App\Models\CourseAssignment::where('lecturer_id', $lecturer->id)
            ->where('course_id', $courseId)
            ->where('program_id', $programId)
            ->firstOrFail();

        $course  = $assignment->course;
        $program = $assignment->program;

        $lecturer = auth()->user();
        $students = \App\Models\Student::with('user')
            ->where('program_id', $programId)
            ->whereHas('enrollments', fn($q) => $q->where('course_id', $courseId))
            ->when($lecturer->church_branch_id, fn($q) => $q->where('church_branch_id', $lecturer->church_branch_id))
            ->forExams()
            ->orderBy('student_id')
            ->get();

        // Default to attempt 1; allow ?attempt= override
        $attempt = max(1, (int) request('attempt', 1));

        $existing = ExamScore::where('course_id', $courseId)
            ->where('program_id', $programId)
            ->where('attempt', $attempt)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        [$quizPct, $examPct] = \App\Services\ReportCardService::getPercentages($program, $attempt);

        $sbaSubWeights = \App\Services\ReportCardService::getSbaSubWeights();
        $sbaSubLabels  = \App\Services\ReportCardService::getSbaSubLabels();
        $totalSubW     = \App\Services\ReportCardService::getTotalSubWeight();

        // All attempts that exist for this course+program
        $attempts = ExamScore::where('course_id', $courseId)
            ->where('program_id', $programId)
            ->distinct()->orderBy('attempt')->pluck('attempt');

        return view('lecturer.exams.batch-scores', compact(
            'course', 'program', 'students', 'existing',
            'attempt', 'attempts', 'quizPct', 'examPct',
            'sbaSubWeights', 'sbaSubLabels', 'totalSubW',
            'courseId', 'programId'
        ));
    }

    public function batchScoresSave(Request $request, int $courseId, int $programId)
    {
        abort_unless(\App\Models\Setting::lecturerCan('exams'), 403, 'Exam score entry has been disabled.');

        $lecturer = auth()->user();

        $assignmentExists = \App\Models\CourseAssignment::where('lecturer_id', $lecturer->id)
            ->where('course_id', $courseId)
            ->where('program_id', $programId)
            ->exists();

        abort_unless($assignmentExists, 403, 'Not authorized for this course.');

        $request->validate([
            'attempt'      => 'required|integer|min:1',
            'scores'       => 'required|array',
            'scores.*.student_id' => 'required|exists:students,id',
        ]);

        $attempt    = (int) $request->attempt;
        $activePeriod = \App\Models\AcademicSession::activePeriod();
        $periodId   = $activePeriod?->id;
        $saved      = 0;

        foreach ($request->scores as $row) {
            $sid = $row['student_id'];

            $t1 = isset($row['test1'])     && $row['test1']     !== '' ? (float)$row['test1']     : null;
            $gw = isset($row['groupwork']) && $row['groupwork'] !== '' ? (float)$row['groupwork'] : null;
            $t2 = isset($row['test2'])     && $row['test2']     !== '' ? (float)$row['test2']     : null;
            $pw = isset($row['project'])   && $row['project']   !== '' ? (float)$row['project']   : null;

            $hasSub  = ($t1 !== null || $gw !== null || $t2 !== null || $pw !== null);
            $sba     = isset($row['sba'])  && $row['sba']  !== '' ? (float)$row['sba']  : null;
            $exam    = isset($row['exam']) && $row['exam'] !== '' ? (float)$row['exam'] : null;

            if ($hasSub) {
                $sba = ExamScore::computeSbaFromSubScores($t1, $gw, $t2, $pw);
            }

            if ($sba === null && $exam === null && !$hasSub) continue;

            ExamScore::updateOrCreate(
                ['student_id'=>$sid,'course_id'=>$courseId,'program_id'=>$programId,'attempt'=>$attempt],
                [
                    'academic_period_id' => $periodId,
                    'quiz_score'         => $sba,
                    'sba_score'          => $sba,
                    'exam_score'         => $exam,
                    'test1_score'        => $t1,
                    'groupwork_score'    => $gw,
                    'test2_score'        => $t2,
                    'project_score'      => $pw,
                ]
            );
            $saved++;
        }

        return back()->with('success', "{$saved} student score(s) saved successfully.");
    }

    // ── Excel template download for batch entry ───────────────────────────────

    public function batchTemplate(int $courseId, int $programId)
    {
        abort_unless(\App\Models\Setting::lecturerCan('exams'), 403);

        $lecturer = auth()->user();
        abort_unless(
            \App\Models\CourseAssignment::where('lecturer_id', $lecturer->id)
                ->where('course_id', $courseId)->where('program_id', $programId)->exists(),
            403
        );

        $course   = \App\Models\Course::findOrFail($courseId);
        $program  = \App\Models\Program::findOrFail($programId);
        $students = \App\Models\Student::with('user')
            ->where('program_id', $programId)
            ->whereHas('enrollments', fn($q) => $q->where('course_id', $courseId))
            ->forExams()->orderBy('student_id')->get();

        $labels = \App\Services\ReportCardService::getSbaSubLabels();
        $weights= \App\Services\ReportCardService::getSbaSubWeights();

        // Build CSV
        $headers = [
            'student_id','student_name',
            'test1 (/'.$weights['test1'].')',
            'groupwork (/'.$weights['groupwork'].')',
            'test2 (/'.$weights['test2'].')',
            'project (/'.$weights['project'].')',
            'exam_score (/100)',
        ];

        $rows = [];
        foreach ($students as $s) {
            $rows[] = [
                $s->student_id,
                $s->user->full_name ?? '',
                '', '', '', '', '',
            ];
        }

        $filename = 'batch_scores_'.$course->code.'_'.$program->name.'.csv';
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);

        $callback = function() use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) fputcsv($out, $row);
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Setting::lecturerCan('exams'), 403, 'Exam score entry has been disabled by the administrator.');

        $lecturer = auth()->user();
        $student = Student::findOrFail($request->student_id);

        if ($request->has('scores') && is_array($request->scores)) {
            $request->validate([
                'student_id'   => 'required|exists:students,id',
                'program_id'   => 'required|exists:programs,id',
                'attempt'      => 'required|integer|min:1',
                'scores'       => 'required|array',
                'scores.*.course_id' => 'required|exists:courses,id',
            ]);

            $assignedCourseIds = CourseAssignment::where('lecturer_id', $lecturer->id)
                ->where('program_id', $request->program_id)
                ->pluck('course_id')
                ->toArray();

            foreach ($request->scores as $scoreData) {
                $cId = $scoreData['course_id'];
                if (! in_array($cId, $assignedCourseIds)) {
                    continue;
                }

                $t1  = isset($scoreData['test1_score'])     && $scoreData['test1_score']     !== '' ? (float)$scoreData['test1_score']     : null;
                $gw  = isset($scoreData['groupwork_score']) && $scoreData['groupwork_score'] !== '' ? (float)$scoreData['groupwork_score'] : null;
                $t2  = isset($scoreData['test2_score'])     && $scoreData['test2_score']     !== '' ? (float)$scoreData['test2_score']     : null;
                $pw  = isset($scoreData['project_score'])   && $scoreData['project_score']   !== '' ? (float)$scoreData['project_score']   : null;

                $hasSub = ($t1 !== null || $gw !== null || $t2 !== null || $pw !== null);
                $sba    = isset($scoreData['sba_score'])  && $scoreData['sba_score']  !== '' ? (float)$scoreData['sba_score']  : null;
                $exam   = isset($scoreData['exam_score']) && $scoreData['exam_score'] !== '' ? (float)$scoreData['exam_score'] : null;

                if ($hasSub) {
                    $sba = ExamScore::computeSbaFromSubScores($t1, $gw, $t2, $pw);
                }

                ExamScore::updateOrCreate(
                    [
                        'student_id' => $request->student_id,
                        'course_id'  => $cId,
                        'program_id' => $request->program_id,
                        'attempt'    => $request->attempt,
                    ],
                    [
                        'quiz_score'      => $sba,
                        'sba_score'       => $sba,
                        'exam_score'      => $exam,
                        'test1_score'     => $t1,
                        'groupwork_score' => $gw,
                        'test2_score'     => $t2,
                        'project_score'   => $pw,
                    ]
                );
            }

            return back()->with('success', 'Exam scores saved successfully.');
        }

        $request->validate([
            'course_id'   => 'required|exists:courses,id',
            'program_id'  => 'required|exists:programs,id',
            'student_id'  => 'required|exists:students,id',
            'attempt'     => 'required|integer|min:1',
            'sba_score'   => 'nullable|numeric|min:0|max:100',
            'exam_score'  => 'nullable|numeric|min:0|max:100',
        ]);

        $assignmentExists = CourseAssignment::where('lecturer_id', $lecturer->id)
            ->where('course_id', $request->course_id)
            ->where('program_id', $request->program_id)
            ->exists();

        if (! $assignmentExists) {
            abort(403, 'You are not authorized to enter exam scores for this course.');
        }

        $t1  = $request->filled('test1_score')     ? (float)$request->test1_score     : null;
        $gw  = $request->filled('groupwork_score') ? (float)$request->groupwork_score : null;
        $t2  = $request->filled('test2_score')     ? (float)$request->test2_score     : null;
        $pw  = $request->filled('project_score')   ? (float)$request->project_score   : null;

        $hasSub = ($t1 !== null || $gw !== null || $t2 !== null || $pw !== null);
        $sbaScore = $request->sba_score ?? $request->quiz_score ?? null;

        if ($hasSub) {
            $sbaScore = ExamScore::computeSbaFromSubScores($t1, $gw, $t2, $pw);
        }

        $activePeriod = \App\Models\AcademicSession::activePeriod();

        ExamScore::updateOrCreate([
            'student_id' => $request->student_id,
            'course_id'  => $request->course_id,
            'program_id' => $request->program_id,
            'attempt'    => $request->attempt,
        ], [
            'academic_period_id' => $activePeriod?->id,
            'quiz_score'         => $sbaScore,
            'sba_score'          => $sbaScore,
            'exam_score'         => $request->exam_score,
            'test1_score'        => $t1,
            'groupwork_score'    => $gw,
            'test2_score'        => $t2,
            'project_score'      => $pw,
        ]);

        return back()->with('success', 'Score saved successfully.');
    }

    // ── Report Card Downloads for Teachers ───────────────────────────────────

    /**
     * Download a single student's report card as PDF.
     * Teacher must be assigned to at least one course in the student's program.
     */
    public function downloadReportCard(Request $request)
    {
        abort_unless(Setting::lecturerCan('reports'), 403, 'Report access has been disabled.');
        $this->raisePdfMemory();

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'program_id' => 'required|exists:programs,id',
            'attempt'    => 'nullable|integer|min:1',
        ]);

        $lecturer = auth()->user();
        $student  = Student::with(['user','program'])->findOrFail($request->student_id);
        $program  = Program::findOrFail($request->program_id);
        $attempt  = (int) $request->get('attempt', 1);

        // Ensure lecturer teaches in this program
        $hasAccess = ClassTeacherAssignment::where('lecturer_id', $lecturer->id)
                         ->where('program_id', $program->id)->exists()
                  || CourseAssignment::where('lecturer_id', $lecturer->id)
                         ->where('program_id', $program->id)->exists();
        abort_unless($hasAccess, 403, 'You are not assigned to this class.');

        $data   = $this->reportCardService->generate($student, $program, $attempt);
        $period = AcademicSession::activePeriod();

        $attQ        = Attendance::where('student_id', $student->id)->where('program_id', $program->id);
        if ($period) $attQ->where('academic_period_id', $period->id);
        $attRecords  = $attQ->get();
        $schoolDays  = $attRecords->pluck('date')->unique()->count();
        $presentDays = $attRecords->where('status','present')->count();

        $data = array_merge($data, [
            'school_days'  => $schoolDays,
            'present_days' => $presentDays,
            'absent_days'  => $schoolDays - $presentDays,
            'att_pct'      => $schoolDays > 0 ? round($presentDays/$schoolDays*100,1) : 0,
            'period'       => $period,
        ]);

        $doc   = $this->safeRecord('report_card', "Report Card — {$student->user->full_name}", $student->id, $period?->id, ['program_id'=>$program->id,'attempt'=>$attempt]);
        $qrUrl = $doc ? $this->docService->qrUrl($doc) : '';
        $meta  = [
            'schoolName'     => Setting::get('school_name', config('app.name')),
            'schoolSubtitle' => Setting::get('school_subtitle', ''),
            'schoolAddress'  => Setting::get('school_address', ''),
            'siteLogoData'   => \App\Support\ReportCardPdfAssets::logoBase64(),
        ];

        // Conduct / remarks from student report
        try {
            $rpt = \App\Models\StudentReport::where('student_id', $student->id)
                ->where('program_id', $program->id)
                ->where('attempt', $attempt)
                ->first();
            $meta['conduct_value']        = $rpt->conduct              ?? '';
            $meta['attitude_value']       = $rpt->attitude             ?? '';
            $meta['interest_value']       = $rpt->interest             ?? '';
            $meta['class_teacher_remark'] = $rpt->class_teacher_remark ?? '';
            $meta['head_teacher_remark']  = $rpt->head_teacher_remark  ?? '';
            $meta['promoted_to']          = $rpt->promoted_to          ?? '';
        } catch (\Throwable $e) {
            $meta['conduct_value'] = $meta['attitude_value'] = $meta['interest_value'] = '';
            $meta['class_teacher_remark'] = $meta['head_teacher_remark'] = $meta['promoted_to'] = '';
        }

        $pdf = Pdf::loadView('pdf.report_card', array_merge(compact('data','student','program','doc','qrUrl','attempt'), $meta));
        $orientation = 'portrait';
        $pdf->setPaper('a4', $orientation);

        return $pdf->download("report_card_{$student->student_id}_".now()->format('Ymd').'.pdf');
    }

    /**
     * Bulk download: all selected students' report cards in one PDF.
     */
    public function bulkDownloadReportCards(Request $request)
    {
        abort_unless(Setting::lecturerCan('reports'), 403, 'Report access has been disabled.');
        $this->raisePdfMemory();

        $request->validate([
            'student_ids' => 'required|array',
            'program_id'  => 'required|exists:programs,id',
            'attempt'     => 'nullable|integer|min:1',
        ]);

        $lecturer = auth()->user();
        $program  = Program::findOrFail($request->program_id);
        $attempt  = (int) $request->get('attempt', 1);

        $hasAccess = ClassTeacherAssignment::where('lecturer_id', $lecturer->id)
                         ->where('program_id', $program->id)->exists()
                  || CourseAssignment::where('lecturer_id', $lecturer->id)
                         ->where('program_id', $program->id)->exists();
        abort_unless($hasAccess, 403, 'You are not assigned to this class.');

        $students = Student::with(['user','program'])
            ->whereIn('id', $request->student_ids)
            ->when($lecturer->church_branch_id, fn($q) => $q->where('church_branch_id', $lecturer->church_branch_id))
            ->forExams()->orderBy('student_id')->get();

        $period    = AcademicSession::activePeriod();
        $schoolName = Setting::get('school_name', config('app.name'));
        $siteLogoData = \App\Support\ReportCardPdfAssets::logoBase64();
        $cards = [];

        foreach ($students as $student) {
            $data = $this->reportCardService->generate($student, $program, $attempt);
            if (!$data['has_scores']) continue;

            $attQ        = Attendance::where('student_id', $student->id)->where('program_id', $program->id);
            if ($period) $attQ->where('academic_period_id', $period->id);
            $attRecords  = $attQ->get();
            $schoolDays  = $attRecords->pluck('date')->unique()->count();
            $presentDays = $attRecords->where('status','present')->count();

            $data = array_merge($data, [
                'school_days'  => $schoolDays,
                'present_days' => $presentDays,
                'absent_days'  => $schoolDays - $presentDays,
                'att_pct'      => $schoolDays > 0 ? round($presentDays/$schoolDays*100,1) : 0,
                'period'       => $period,
            ]);

            $doc   = $this->safeRecord('report_card', "Report Card — {$student->user->full_name}", $student->id, $period?->id, ['program_id'=>$program->id,'attempt'=>$attempt,'bulk'=>true]);
            $qrUrl = $doc ? $this->docService->qrUrl($doc) : '';
            $cards[] = ['data'=>$data,'student'=>$student,'program'=>$program,'doc'=>$doc,'qrUrl'=>$qrUrl];
        }

        if (empty($cards)) {
            return back()->with('error', 'No scores found for the selected students.');
        }

        $pdf = Pdf::loadView('pdf.bulk_academic_reports', compact('cards','attempt','schoolName','siteLogoData'))
            ->setPaper('a4');

        return $pdf->download("bulk_reports_{$program->name}_".now()->format('Ymd').'.pdf');
    }

    /**
     * Class performance report (for class teachers).
     */
    public function classPerformance(Request $request)
    {
        try {
            $lecturer   = auth()->user();
            $assignment = ClassTeacherAssignment::where('lecturer_id', $lecturer->id)->with('program')->first();
            abort_unless($assignment && $assignment->program, 403, 'You are not assigned as a class teacher to any program. Please contact the administrator.');

            $program  = $assignment->program;
            $sessions = collect();
            try {
                $sessions = \App\Models\AcademicSession::with('periods')->orderByDesc('year')->get();
            } catch (\Throwable $e) {
                \Log::warning('classPerformance: sessions load failed: '.$e->getMessage());
            }

            $period = null;
            try {
                $period = $request->filled('period_id') ? \App\Models\AcademicPeriod::find($request->period_id) : AcademicSession::activePeriod();
            } catch (\Throwable $e) {
                \Log::warning('classPerformance: period resolve failed: '.$e->getMessage());
            }

            $attempt  = max(1, (int) $request->get('attempt', 1));
            $data     = $this->reportService->classPerformance($program, $period, $attempt);

            return view('lecturer.reports.class-performance', compact('program','sessions','data','period','attempt'));
        } catch (\Throwable $e) {
            \Log::error('classPerformance 500: '.$e->getMessage()."\n".$e->getTraceAsString());
            return redirect()->route('lecturer.dashboard')->with('error', 'The Class Report could not be loaded. Please ensure you are assigned as a class teacher and try again. Details: '.$e->getMessage());
        }
    }

    public function classPerformancePdf(Request $request)
    {
        try {
            $this->raisePdfMemory();
            $lecturer   = auth()->user();
            $assignment = ClassTeacherAssignment::where('lecturer_id', $lecturer->id)->with('program')->first();
            abort_unless($assignment && $assignment->program, 403, 'Not a class teacher.');

            $program  = $assignment->program;
            $period   = $request->filled('period_id') ? \App\Models\AcademicPeriod::find($request->period_id) : AcademicSession::activePeriod();
            $attempt  = max(1, (int) $request->get('attempt', 1));
            $data     = $this->reportService->classPerformance($program, $period, $attempt);

            $doc   = $this->safeRecord('class_performance', "Class Performance — {$program->name}", null, $period?->id, ['program_id'=>$program->id,'attempt'=>$attempt]);
            $qrUrl = $doc ? $this->docService->qrUrl($doc) : '';
            $meta  = [
                'schoolName'     => Setting::get('school_name', config('app.name')),
                'schoolSubtitle' => Setting::get('school_subtitle', ''),
                'siteLogoData'   => \App\Support\ReportCardPdfAssets::logoBase64(),
            ];

            $pdf = Pdf::loadView('pdf.class_performance', array_merge(compact('data','doc','qrUrl','program','period','attempt'), $meta))
                ->setPaper('a4','landscape');

            return $pdf->download("class_performance_{$program->name}_".now()->format('Ymd').'.pdf');
        } catch (\Throwable $e) {
            \Log::error('classPerformancePdf 500: '.$e->getMessage()."\n".$e->getTraceAsString());
            return back()->with('error', 'Failed to generate class performance PDF: '.$e->getMessage());
        }
    }

    private function raisePdfMemory(): void
    {
        if ((int) ini_get('memory_limit') < 256) {
            ini_set('memory_limit', '256M');
        }
    }

    private function safeRecord(string $type, string $title, ?int $studentId = null, ?int $periodId = null, array $meta = []): ?\App\Models\GeneratedDocument
    {
        try {
            return $this->docService->record($type, $title, $studentId, $periodId, $meta);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
