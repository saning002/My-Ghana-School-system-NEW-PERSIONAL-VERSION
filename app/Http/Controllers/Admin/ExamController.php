<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamScore;
use App\Models\Program;
use App\Models\Student;
use App\Services\ReportCardService;
use App\Exports\ReportCardExport;
use App\Support\ReportCardPdfAssets;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExamController extends Controller
{
    public function __construct(private ReportCardService $reportCardService) {}

    public function index()
    {
        $programs = Program::withCount('courses')->orderBy('sequence')->get();
        return view('admin.exams.index', compact('programs'));
    }

    /**
     * Step 1 — pick a program (GET /admin/exams/create)
     * Step 2 — pick a student  (GET /admin/exams/create?program_id=X)
     * Step 3 — enter scores    (GET /admin/exams/create?program_id=X&student_id=Y[&attempt=N])
     */
    public function create(Request $request)
    {
        $programs  = Program::orderBy('sequence')->get();
        $students  = collect();
        $courses   = collect();
        $existing  = collect();
        $attempts  = collect();

        $selectedProgram = null;
        $selectedStudent = null;
        $currentAttempt  = 1;

        $isBranchAdmin = auth()->check() && auth()->user()->isBranchAdmin();
        $branchId = $isBranchAdmin ? auth()->user()->church_branch_id : null;

        if ($request->filled('program_id')) {
            $selectedProgram = Program::find($request->program_id);
            if ($selectedProgram) {
                // For the student picker, show currently-enrolled eligible students
                // PLUS any student who already has scores for any attempt in this program
                // (covers promoted/demoted students who need score corrections)
                $query = Student::where('program_id', $selectedProgram->id)->forExams();
                if ($isBranchAdmin) {
                    $query->where('church_branch_id', $branchId);
                }
                $currentStudents = $query->with('user')->get();

                // Historically scored students for this program
                $scoredIds = ExamScore::where('program_id', $selectedProgram->id)
                    ->distinct()->pluck('student_id');
                $historicalStudents = Student::whereIn('id', $scoredIds)
                    ->whereNotIn('status', Student::EXAM_EXCLUDED_STATUSES)
                    ->whereNotIn('id', $currentStudents->pluck('id'))
                    ->with('user')->get();
                if ($isBranchAdmin) {
                    $historicalStudents = $historicalStudents->where('church_branch_id', $branchId)->values();
                }
                $students = $currentStudents->merge($historicalStudents)->sortBy('id')->values();
            }
        }

        if ($selectedProgram && $request->filled('student_id')) {
            $selectedStudent = Student::with('user')->find($request->student_id);
            if ($selectedStudent) {
                $courses = $selectedProgram->courses()->orderBy('name')->get();

                // All distinct attempts this student has for this program
                $attempts = ExamScore::where('student_id', $selectedStudent->id)
                    ->where('program_id', $selectedProgram->id)
                    ->distinct()
                    ->orderBy('attempt')
                    ->pluck('attempt');

                // Determine which attempt to show/edit
                $latestAttempt  = $attempts->max() ?? 0;
                $currentAttempt = $request->filled('attempt')
                    ? (int) $request->attempt
                    : ($latestAttempt ?: 1);

                // Load scores for the selected attempt
                $existing = ExamScore::where('student_id', $selectedStudent->id)
                    ->where('program_id', $selectedProgram->id)
                    ->where('attempt', $currentAttempt)
                    ->get()
                    ->keyBy('course_id');
            }
        }

        [$quizPercentage, $examPercentage] = ReportCardService::getPercentages($selectedProgram, $currentAttempt);
        $sbaSubWeights = ReportCardService::getSbaSubWeights();
        $sbaSubLabels  = ReportCardService::getSbaSubLabels();
        $totalSubW     = ReportCardService::getTotalSubWeight();

        return view('admin.exams.create', compact(
            'programs', 'students', 'courses', 'existing',
            'selectedProgram', 'selectedStudent',
            'attempts', 'currentAttempt',
            'quizPercentage', 'examPercentage',
            'sbaSubWeights', 'sbaSubLabels', 'totalSubW'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'                   => 'required|exists:students,id',
            'program_id'                   => 'required|exists:programs,id',
            'attempt'                      => 'required|integer|min:1',
            'scores'                       => 'required|array',
            'scores.*.course_id'           => 'required|exists:courses,id',
            'scores.*.sba_score'           => 'nullable|numeric|min:0',
            'scores.*.exam_score'          => 'nullable|numeric|min:0|max:100',
            'scores.*.test1_score'         => 'nullable|numeric|min:0',
            'scores.*.groupwork_score'     => 'nullable|numeric|min:0',
            'scores.*.test2_score'         => 'nullable|numeric|min:0',
            'scores.*.project_score'       => 'nullable|numeric|min:0',
        ]);

        foreach ($request->scores as $scoreData) {
            $t1  = isset($scoreData['test1_score'])     && $scoreData['test1_score']     !== '' ? (float)$scoreData['test1_score']     : null;
            $gw  = isset($scoreData['groupwork_score']) && $scoreData['groupwork_score'] !== '' ? (float)$scoreData['groupwork_score'] : null;
            $t2  = isset($scoreData['test2_score'])     && $scoreData['test2_score']     !== '' ? (float)$scoreData['test2_score']     : null;
            $pw  = isset($scoreData['project_score'])   && $scoreData['project_score']   !== '' ? (float)$scoreData['project_score']   : null;

            $hasSub = ($t1 !== null || $gw !== null || $t2 !== null || $pw !== null);
            $sba    = isset($scoreData['sba_score'])  && $scoreData['sba_score']  !== '' ? (float)$scoreData['sba_score']  : null;
            $exam   = isset($scoreData['exam_score']) && $scoreData['exam_score'] !== '' ? (float)$scoreData['exam_score'] : null;

            // If sub-scores provided, recompute sba from them
            if ($hasSub) {
                $sba = \App\Models\ExamScore::computeSbaFromSubScores($t1, $gw, $t2, $pw);
            }

            ExamScore::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'course_id'  => $scoreData['course_id'],
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

        return redirect()
            ->route('admin.exams.show', [
                $request->student_id,
                $request->program_id,
                'attempt' => $request->attempt,
            ])
            ->with('success', 'Exam scores saved successfully.');
    }

    public function show(Student $student, Program $program, Request $request)
    {
        // Suspended and withdrawn students have no report card
        if (in_array($student->status, Student::EXAM_EXCLUDED_STATUSES)) {
            return redirect()->route('admin.exams.index')
                ->with('error', ucfirst($student->status) . ' students do not have report cards.');
        }

        $student->load(['user', 'churchBranch', 'program']);

        // All attempts this student has for this program
        $attempts = ExamScore::where('student_id', $student->id)
            ->where('program_id', $program->id)
            ->distinct()
            ->orderBy('attempt')
            ->pluck('attempt');

        $currentAttempt = $request->filled('attempt')
            ? (int) $request->attempt
            : ($attempts->max() ?? 1);

        $data = $this->reportCardService->generate($student, $program, $currentAttempt);
        $data['attempts']       = $attempts;
        $data['currentAttempt'] = $currentAttempt;

        return view('admin.exams.show', $data);
    }

    public function pdf(Student $student, Program $program, Request $request)
    {
        // Suspended and withdrawn students must not have report cards generated
        if (in_array($student->status, Student::EXAM_EXCLUDED_STATUSES)) {
            abort(403, 'Report cards cannot be generated for suspended or withdrawn students.');
        }

        $student->load(['user', 'program', 'churchBranch']);
        $attempt = $request->filled('attempt') ? (int) $request->attempt : 1;

        try {
            $data = $this->reportCardService->generate($student, $program, $attempt);
        } catch (\Throwable $e) {
            \Log::error('Admin exam report generation failed', ['exception' => $e->getMessage()]);
            return response('Unable to generate report card. Please try again later.', 500);
        }

        $data['logoBase64']  = ReportCardPdfAssets::logoBase64();
        $data['photoBase64'] = ReportCardPdfAssets::studentPhotoBase64($student);

        // School meta for header
        $data['schoolName']     = \App\Models\Setting::get('school_name',     config('app.name', 'School'));
        $data['schoolSubtitle'] = \App\Models\Setting::get('school_subtitle', '');
        $data['schoolAddress']  = \App\Models\Setting::get('school_address',  '');
        $data['siteLogoData']   = \App\Support\ReportCardPdfAssets::logoBase64();

        // Attendance for this student/program
        try {
            $period = \App\Models\AcademicSession::activePeriod();
            $attQ   = \App\Models\Attendance::where('student_id', $student->id)
                        ->where('program_id', $program->id);
            if ($period) $attQ->where('academic_period_id', $period->id);
            $attRecs = $attQ->get();
            $data['school_days']   = $attRecs->pluck('date')->unique()->count();
            $data['present_days']  = $attRecs->where('status','present')->count();
            $data['absent_days']   = $attRecs->where('status','absent')->count();
        } catch (\Throwable $e) {
            $data['school_days']  = null;
            $data['present_days'] = null;
        }

        // Conduct & remarks from student report
        $rpt = null;
        try {
            $rpt = \App\Models\StudentReport::where('student_id', $student->id)
                ->where('program_id', $program->id)
                ->where('attempt', $attempt)
                ->first();
            $data['conduct_value']         = $rpt->conduct        ?? '';
            $data['attitude_value']        = $rpt->attitude       ?? '';
            $data['interest_value']        = $rpt->interest       ?? '';
            $data['class_teacher_remark']  = $rpt->class_teacher_remark ?? '';
            $data['head_teacher_remark']   = $rpt->head_teacher_remark  ?? '';
            $data['promoted_to']           = $rpt->promoted_to    ?? '';
        } catch (\Throwable $e) {
            $data['conduct_value']        = '';
            $data['attitude_value']       = '';
            $data['interest_value']       = '';
            $data['class_teacher_remark'] = '';
            $data['head_teacher_remark']  = '';
            $data['promoted_to']          = '';
        }

        // Personality Development attributes and ratings
        $attrDefs = collect();
        try {
            if (class_exists(\App\Models\AttributeDefinition::class)) {
                $attrDefs = \App\Models\AttributeDefinition::where('is_active', true)->orderBy('sort_order')->get();
            }
        } catch (\Throwable $e) {}

        $studentRatings = collect();
        try {
            if ($rpt) {
                $studentRatings = \App\Models\StudentReportAttribute::where('student_report_id', $rpt->id)->get()->keyBy('attribute_definition_id');
            }
        } catch (\Throwable $e) {}

        $data['attrDefs']       = $attrDefs;
        $data['studentRatings'] = $studentRatings;
        $data['schoolMotto']    = \App\Models\Setting::get('school_motto', 'Learners Today, Leaders Tomorrow');

        try {
            @ini_set('memory_limit', '1024M');
            @set_time_limit(300);

            $filename = 'report_card_' . $student->student_id . '_' . \Str::slug($program->name) . '.pdf';
            $pdf = Pdf::loadView('pdf.report_card', $data)->setPaper('a4', 'portrait');

            if ($request->input('mode') === 'print' || $request->has('print')) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Log::error('Admin exam PDF generation failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response('PDF generation failed: ' . $e->getMessage(), 500);
        }
    }

    public function excel(Student $student, Program $program, Request $request)
    {
        // Suspended and withdrawn students must not have report cards generated
        if (in_array($student->status, Student::EXAM_EXCLUDED_STATUSES)) {
            abort(403, 'Report cards cannot be generated for suspended or withdrawn students.');
        }

        $student->load(['user', 'program', 'churchBranch']);
        $attempt = $request->filled('attempt') ? (int) $request->attempt : 1;

        try {
            $data = $this->reportCardService->generate($student, $program, $attempt);
        } catch (\Throwable $e) {
            \Log::error('Admin exam report generation failed', ['exception' => $e]);
            return response('Unable to generate report card. Please try again later.', 500);
        }

        try {
            $filename = 'report_card_' . $student->student_id . '_' . \Str::slug($program->name) . '.xlsx';
            return Excel::download(new ReportCardExport($data), $filename);
        } catch (\Throwable $e) {
            \Log::error('Admin exam Excel generation failed', ['exception' => $e]);
            return response('Excel generation failed. Please contact support.', 500);
        }
    }

    /**
     * Show the bulk report-card download/print selector page.
     */
    public function bulkDownload(Request $request)
    {
        $programs = Program::orderBy('sequence')->get();

        $selectedProgram = null;
        $students        = collect();
        $attempts        = collect();
        $currentAttempt  = 1;

        $isBranchAdmin = auth()->check() && auth()->user()->isBranchAdmin();
        $branchId      = $isBranchAdmin ? auth()->user()->church_branch_id : null;

        if ($request->filled('program_id')) {
            $selectedProgram = Program::find($request->program_id);

            if ($selectedProgram) {
                // All attempts that have any scores for this program
                $attempts = ExamScore::where('program_id', $selectedProgram->id)
                    ->distinct()
                    ->orderBy('attempt')
                    ->pluck('attempt');

                $currentAttempt = $request->filled('attempt')
                    ? (int) $request->attempt
                    : ($attempts->max() ?? 1);

                // All students who sat this attempt (current + historical)
                $scoredIds = ExamScore::where('program_id', $selectedProgram->id)
                    ->where('attempt', $currentAttempt)
                    ->distinct()
                    ->pluck('student_id');

                $query = Student::whereIn('id', $scoredIds)
                    ->whereNotIn('status', Student::EXAM_EXCLUDED_STATUSES);
                if ($isBranchAdmin) {
                    $query->where('church_branch_id', $branchId);
                }
                $students = $query->with('user')->orderBy('id')->get();
            }
        }

        return view('admin.exams.bulk', compact(
            'programs', 'selectedProgram', 'students', 'attempts', 'currentAttempt'
        ));
    }

    /**
     * Generate a multi-page PDF with one report card per selected student.
     */
    public function bulkPdf(Request $request)
    {
        $request->validate([
            'program_id'   => 'required|exists:programs,id',
            'attempt'      => 'required|integer|min:1',
            'student_ids'  => 'required|array|min:1',
            'student_ids.*'=> 'exists:students,id',
        ]);

        $program = Program::findOrFail($request->program_id);
        $attempt = (int) $request->attempt;

        $logoBase64     = ReportCardPdfAssets::logoBase64();
        $schoolName     = \App\Models\Setting::get('school_name',     config('app.name', 'School'));
        $schoolSubtitle = \App\Models\Setting::get('school_subtitle', '');
        $schoolAddress  = \App\Models\Setting::get('school_address',  '');
        $schoolMotto    = \App\Models\Setting::get('school_motto',    'Learners Today, Leaders Tomorrow');
        $siteLogoData   = $logoBase64;

        // Personality Development definitions (fetched ONCE)
        $attrDefs = collect();
        try {
            if (class_exists(\App\Models\AttributeDefinition::class)) {
                $attrDefs = \App\Models\AttributeDefinition::where('is_active', true)->orderBy('sort_order')->get();
            }
        } catch (\Throwable $e) {}

        $period = null;
        try {
            $period = \App\Models\AcademicSession::activePeriod();
        } catch (\Throwable $e) {}

        $cards = [];
        foreach ($request->student_ids as $studentId) {
            $student = Student::with(['user', 'churchBranch', 'program'])->find($studentId);
            if (! $student) continue;

            try {
                $data = $this->reportCardService->generate($student, $program, $attempt);
                $data['logoBase64']     = $logoBase64;
                $data['photoBase64']    = ReportCardPdfAssets::studentPhotoBase64($student);
                $data['schoolName']     = $schoolName;
                $data['schoolSubtitle'] = $schoolSubtitle;
                $data['schoolAddress']  = $schoolAddress;
                $data['schoolMotto']    = $schoolMotto;
                $data['siteLogoData']   = $siteLogoData;
                $data['attrDefs']       = $attrDefs;

                // Attendance for this student/program
                try {
                    $attQ = \App\Models\Attendance::where('student_id', $student->id)
                                ->where('program_id', $program->id);
                    if ($period) $attQ->where('academic_period_id', $period->id);
                    $attRecs = $attQ->get();
                    $data['school_days']   = $attRecs->pluck('date')->unique()->count();
                    $data['present_days']  = $attRecs->where('status','present')->count();
                    $data['absent_days']   = $attRecs->where('status','absent')->count();
                } catch (\Throwable $e) {
                    $data['school_days']  = null;
                    $data['present_days'] = null;
                }

                // Conduct & remarks from student report
                $rpt = null;
                try {
                    $rpt = \App\Models\StudentReport::where('student_id', $student->id)
                        ->where('program_id', $program->id)
                        ->where('attempt', $attempt)
                        ->first();
                    $data['conduct_value']         = $rpt->conduct        ?? '';
                    $data['attitude_value']        = $rpt->attitude       ?? '';
                    $data['interest_value']        = $rpt->interest       ?? '';
                    $data['class_teacher_remark']  = $rpt->class_teacher_remark ?? '';
                    $data['head_teacher_remark']   = $rpt->head_teacher_remark  ?? '';
                    $data['promoted_to']           = $rpt->promoted_to    ?? '';
                } catch (\Throwable $e) {
                    $data['conduct_value']        = '';
                    $data['attitude_value']       = '';
                    $data['interest_value']       = '';
                    $data['class_teacher_remark'] = '';
                    $data['head_teacher_remark']  = '';
                    $data['promoted_to']          = '';
                }

                // Student ratings
                $studentRatings = collect();
                try {
                    if ($rpt) {
                        $studentRatings = \App\Models\StudentReportAttribute::where('student_report_id', $rpt->id)->get()->keyBy('attribute_definition_id');
                    }
                } catch (\Throwable $e) {}
                $data['studentRatings'] = $studentRatings;

                $cards[] = $data;
            } catch (\Throwable $e) {
                \Log::warning("Bulk PDF: skipping student {$studentId} — " . $e->getMessage());
            }
        }

        if (empty($cards)) {
            return back()->with('error', 'No report cards could be generated for the selected students.');
        }

        try {
            @ini_set('memory_limit', '1024M');
            @set_time_limit(600);

            $filename = 'ReportCards_' . \Str::slug($program->name) . '_Attempt' . $attempt . '_' . count($cards) . 'students.pdf';
            
            $pdf = Pdf::loadView('pdf.report_cards_bulk', [
                'cards'          => $cards,
                'attempt'        => $attempt,
                'schoolName'     => $schoolName,
                'schoolSubtitle' => $schoolSubtitle,
                'schoolAddress'  => $schoolAddress,
                'siteLogoData'   => $siteLogoData,
            ])->setPaper('a4', 'portrait');

            if ($request->input('mode') === 'print' || $request->has('print')) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Log::error('Bulk PDF generation failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'PDF generation failed: ' . $e->getMessage() . '. Please try with fewer students or contact support.');
        }
    }
}
