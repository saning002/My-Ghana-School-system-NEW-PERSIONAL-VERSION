<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Student;
use App\Models\Course;
use App\Models\ExamScore;
use App\Models\Setting;
use App\Exports\ExamSheetExport;
use App\Imports\ExamSheetImport;
use App\Services\GradingScale;
use App\Services\ReportCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExamSheetController extends Controller
{
    /**
     * Load all students eligible for a given program+attempt.
     *
     * For a specific attempt we merge:
     *   (a) current-program eligible students (forExams scope) — so new names
     *       can still receive scores
     *   (b) ANY student who already has ExamScore records for this
     *       program+attempt — covers students who were promoted/demoted/repeated
     *       since they sat the exam
     *
     * Suspended and withdrawn students are still excluded via forExams scope
     * for group (a), but if they already have scores in group (b) they appear
     * so existing records stay visible (admin can still read them).
     */
    private function loadStudentsForAttempt(Program $program, int $attempt, ?int $branchId = null): \Illuminate\Support\Collection
    {
        // (a) currently-enrolled eligible students
        $query = Student::where('program_id', $program->id)->forExams();
        if ($branchId) {
            $query->where('church_branch_id', $branchId);
        }
        $currentStudents = $query->with('user')->get();

        // (b) students who already have scores for this program+attempt
        $scoredStudentIds = ExamScore::where('program_id', $program->id)
            ->where('attempt', $attempt)
            ->distinct()
            ->pluck('student_id');

        // exclude suspended/withdrawn from the historical set too
        $historicalStudents = Student::whereIn('id', $scoredStudentIds)
            ->whereNotIn('status', Student::EXAM_EXCLUDED_STATUSES)
            ->whereNotIn('id', $currentStudents->pluck('id')) // avoid duplicates
            ->with('user')
            ->get();

        if ($branchId) {
            $historicalStudents = $historicalStudents->where('church_branch_id', $branchId)->values();
        }

        return $currentStudents->merge($historicalStudents)->sortBy('id')->values();
    }

    public function index(Request $request)
    {
        $programs = Program::orderBy('sequence')->get();
        $selectedProgram = null;
        $students = collect();
        $courses = collect();
        $existingScores = [];
        $attempts = collect();
        $currentAttempt = 1;

        if ($request->filled('program_id')) {
            $selectedProgram = Program::find($request->program_id);
            if ($selectedProgram) {
                $isBranchAdmin = auth()->check() && auth()->user()->isBranchAdmin();
                $branchId = $isBranchAdmin ? auth()->user()->church_branch_id : null;

                $courses = $selectedProgram->courses()->orderBy('name')->get();

                // Discover all attempts that have any scores for this program
                $attempts = ExamScore::where('program_id', $selectedProgram->id)
                    ->distinct()
                    ->orderBy('attempt')
                    ->pluck('attempt');

                $currentAttempt = $request->filled('attempt')
                    ? (int) $request->attempt
                    : ($attempts->max() ?? 1);

                // Load merged student list for this attempt
                $students = $this->loadStudentsForAttempt($selectedProgram, $currentAttempt, $branchId);

                // Load existing scores for the matrix
                $studentIds = $students->pluck('id');
                $courseIds  = $courses->pluck('id');

                $scores = ExamScore::whereIn('student_id', $studentIds)
                    ->whereIn('course_id', $courseIds)
                    ->where('program_id', $selectedProgram->id)
                    ->where('attempt', $currentAttempt)
                    ->get();

                foreach ($scores as $score) {
                    $existingScores[$score->student_id][$score->course_id] = [
                        'sba_score'       => $score->sba_score !== null ? $score->sba_score : $score->quiz_score,
                        'exam_score'      => $score->exam_score,
                        'test1_score'     => $score->test1_score,
                        'groupwork_score' => $score->groupwork_score,
                        'test2_score'     => $score->test2_score,
                        'project_score'   => $score->project_score,
                    ];
                }
            }
        }

        [$quizPercentage, $examPercentage] = ReportCardService::getPercentages($selectedProgram, $currentAttempt);
        $sbaSubWeights = \App\Services\ReportCardService::getSbaSubWeights();
        $sbaSubLabels  = \App\Services\ReportCardService::getSbaSubLabels();

        return view('admin.exams.sheet', compact(
            'programs', 'selectedProgram', 'students', 'courses', 'existingScores', 'attempts', 'currentAttempt',
            'quizPercentage', 'examPercentage', 'sbaSubWeights', 'sbaSubLabels'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'attempt'    => 'required|integer|min:1',
            'scores'     => 'array', // Format: scores[student_id][course_id][sba_score|exam_score]
        ]);

        $programId = $request->program_id;
        $attempt   = $request->attempt;

        if ($request->has('scores')) {
            foreach ($request->scores as $studentId => $courseScores) {
                foreach ($courseScores as $courseId => $scoreValues) {
                    // Check for sub-score components
                    $test1     = isset($scoreValues['test1_score'])     && $scoreValues['test1_score']     !== '' ? (float)$scoreValues['test1_score']     : null;
                    $groupwork = isset($scoreValues['groupwork_score']) && $scoreValues['groupwork_score'] !== '' ? (float)$scoreValues['groupwork_score'] : null;
                    $test2     = isset($scoreValues['test2_score'])     && $scoreValues['test2_score']     !== '' ? (float)$scoreValues['test2_score']     : null;
                    $project   = isset($scoreValues['project_score'])   && $scoreValues['project_score']   !== '' ? (float)$scoreValues['project_score']   : null;

                    // Legacy single SBA field (used if sub-scores not provided)
                    $sbaScore  = isset($scoreValues['sba_score'])   && $scoreValues['sba_score']   !== '' ? (float)$scoreValues['sba_score']   : null;
                    $examScore = isset($scoreValues['exam_score'])   && $scoreValues['exam_score']  !== '' ? (float)$scoreValues['exam_score']  : null;

                    // Compute aggregate SBA from sub-scores if any sub-score entered
                    $hasSubScores = ($test1 !== null || $groupwork !== null || $test2 !== null || $project !== null);
                    if ($hasSubScores) {
                        $sbaScore = \App\Models\ExamScore::computeSbaFromSubScores($test1, $groupwork, $test2, $project);
                    }

                    // Skip row if everything is empty
                    if ($sbaScore === null && $examScore === null && !$hasSubScores) {
                        continue;
                    }

                    \App\Models\ExamScore::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'course_id'  => $courseId,
                            'program_id' => $programId,
                            'attempt'    => $attempt,
                        ],
                        [
                            'quiz_score'      => $sbaScore,
                            'sba_score'       => $sbaScore,
                            'exam_score'      => $examScore,
                            'test1_score'     => $test1,
                            'groupwork_score' => $groupwork,
                            'test2_score'     => $test2,
                            'project_score'   => $project,
                        ]
                    );
                }
            }
        }

        return redirect()->route('admin.exams.sheet', [
            'program_id' => $programId,
            'attempt'    => $attempt,
        ])->with('success', 'Exam sheet scores saved successfully.');
    }

    /**
     * Save quiz and exam percentage settings.
     */
    public function savePercentages(Request $request)
    {
        $request->validate([
            'quiz_percentage'      => 'required|numeric|min:0|max:100',
            'exam_percentage'      => 'required|numeric|min:0|max:100',
            'sba_test1_weight'     => 'nullable|numeric|min:0',
            'sba_groupwork_weight' => 'nullable|numeric|min:0',
            'sba_test2_weight'     => 'nullable|numeric|min:0',
            'sba_project_weight'   => 'nullable|numeric|min:0',
            'sba_test1_label'      => 'nullable|string|max:40',
            'sba_groupwork_label'  => 'nullable|string|max:40',
            'sba_test2_label'      => 'nullable|string|max:40',
            'sba_project_label'    => 'nullable|string|max:40',
        ]);

        $quiz = (float) $request->quiz_percentage;
        $exam = (float) $request->exam_percentage;

        if (abs(($quiz + $exam) - 100) > 0.01) {
            return back()->with('error', 'SBA and Exam percentages must add up to 100%.');
        }

        Setting::set('quiz_percentage', $quiz);
        Setting::set('exam_percentage', $exam);

        // SBA sub-weights — admin can set any values, no sum requirement
        $t1 = (float) ($request->sba_test1_weight     ?? 25);
        $gw = (float) ($request->sba_groupwork_weight ?? 25);
        $t2 = (float) ($request->sba_test2_weight     ?? 25);
        $pw = (float) ($request->sba_project_weight   ?? 25);

        Setting::set('sba_test1_weight',     $t1);
        Setting::set('sba_groupwork_weight', $gw);
        Setting::set('sba_test2_weight',     $t2);
        Setting::set('sba_project_weight',   $pw);

        // Labels
        Setting::set('sba_test1_label',     $request->sba_test1_label     ?: 'Test 1');
        Setting::set('sba_groupwork_label', $request->sba_groupwork_label ?: 'Group Work');
        Setting::set('sba_test2_label',     $request->sba_test2_label     ?: 'Test 2');
        Setting::set('sba_project_label',   $request->sba_project_label   ?: 'Project Work');

        $totalSubW = $t1 + $gw + $t2 + $pw;
        return back()->with('success', "Saved. SBA {$quiz}% / Exam {$exam}%. Sub-weights: {$t1}+{$gw}+{$t2}+{$pw} = {$totalSubW} (total out of {$totalSubW}).");
    }

    public function export(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'attempt'    => 'required|integer|min:1',
        ]);

        $program = Program::findOrFail($request->program_id);
        $isBranchAdmin = auth()->check() && auth()->user()->isBranchAdmin();
        $branchId = $isBranchAdmin ? auth()->user()->church_branch_id : null;

        $students = $this->loadStudentsForAttempt($program, (int) $request->attempt, $branchId);
        $courses  = $program->courses()->orderBy('name')->get();

        $filename = 'ExamSheet_' . \Str::slug($program->name) . '_Attempt' . $request->attempt . '.xlsx';

        return Excel::download(new ExamSheetExport($program, $students, $courses, $request->attempt), $filename);
    }

    public function exportMerit(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'attempt'    => 'required|integer|min:1',
        ]);

        $program = Program::findOrFail($request->program_id);
        $isBranchAdmin = auth()->check() && auth()->user()->isBranchAdmin();
        $branchId = $isBranchAdmin ? auth()->user()->church_branch_id : null;

        $students   = $this->loadStudentsForAttempt($program, (int) $request->attempt, $branchId);
        $courses    = $program->courses()->orderBy('name')->get();
        $studentIds = $students->pluck('id');
        $courseIds  = $courses->pluck('id');

        $scores = ExamScore::where('program_id', $program->id)
            ->where('attempt', $request->attempt)
            ->whereIn('student_id', $studentIds)
            ->whereIn('course_id', $courseIds)
            ->get()
            ->groupBy('student_id');

        $rows = collect($students)->map(function ($student) use ($courses, $scores, $program, $request) {
            $studentScores = $scores->get($student->id, collect());
            $total = 0;
            $courseScores = [];

            foreach ($courses as $course) {
                $score     = $studentScores->firstWhere('course_id', $course->id);
                $quizRaw   = $score ? (float) $score->quiz_score : null;
                $examRaw   = $score ? (float) $score->exam_score : null;
                $aggregate = ($score !== null) ? ReportCardService::calcAggregate($quizRaw ?? 0, $examRaw ?? 0, $program, (int) $request->attempt) : null;
                $formattedScore = $aggregate === null ? '' : number_format($aggregate, 2);
                $grade = $aggregate === null ? '' : GradingScale::assign($aggregate);

                $courseScores[] = [
                    'score' => $formattedScore,
                    'grade' => $grade,
                ];
                $total += $aggregate ?? 0;
            }

            $countCourses = $courses->count();
            $average = $countCourses > 0 ? round($total / $countCourses, 2) : 0.0;

            return [
                'name' => $student->user->full_name,
                'course_scores' => $courseScores,
                'total' => round($total, 2),
                'average' => $average,
            ];
        })->sortByDesc('total')->values();

        $position = 0;
        $lastTotal = null;
        $rowIndex = 0;

        $rows = $rows->map(function ($row) use (&$position, &$lastTotal, &$rowIndex) {
            $rowIndex++;
            if ($lastTotal === null || $row['total'] !== $lastTotal) {
                $position = $rowIndex;
                $lastTotal = $row['total'];
            }
            $row['position'] = $position;
            return $row;
        });

        $branchName = 'All Branches';
        if ($isBranchAdmin) {
            $branchName = auth()->user()->branch?->name ?? 'Branch';
        }

        $data = [
            'program' => $program,
            'students' => $students,
            'courses' => $courses,
            'rows' => $rows,
            'attempt' => $request->attempt,
            'attemptLabel' => strtoupper('Attempt ' . $request->attempt . ', ' . now()->format('F Y')),
            'branchName' => strtoupper($branchName),
            'generatedAt' => now()->format('F j, Y H:i'),
        ];

        $filename = 'OrderOfMerit_' . \Str::slug($program->name) . '_Attempt' . $request->attempt . '.pdf';

        $pdf = Pdf::loadView('pdf.order_of_merit', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    public function downloadSbaPdf(Request $request)
    {
        $request->validate([
            'program_id'    => 'required|exists:programs,id',
            'attempt'       => 'required|integer|min:1',
            'include_exam'  => 'nullable|boolean',
            'split_sba'     => 'nullable|boolean',
            'student_ids'   => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $program  = Program::findOrFail($request->program_id);
        $isBranch = auth()->check() && auth()->user()->isBranchAdmin();
        $branchId = $isBranch ? auth()->user()->church_branch_id : null;
        $students = $this->loadStudentsForAttempt($program, (int)$request->attempt, $branchId);

        if ($request->filled('student_ids')) {
            $selected = collect($request->student_ids)->map('intval');
            $students = $students->whereIn('id', $selected)->values();
        }

        $courses     = $program->courses()->orderBy('name')->get();
        $includeExam = $request->boolean('include_exam', false);
        $splitSba    = $request->boolean('split_sba', true);
        $suffix      = $includeExam ? '_with_Exam' : '_SBA_only';
        $filename    = 'SBA_' . \Str::slug($program->name) . '_Attempt' . $request->attempt . $suffix . '.pdf';

        return (new \App\Exports\SbaScorePdfExport($program, $courses, $students, (int)$request->attempt, $includeExam, $splitSba))
            ->download($filename);
    }

    /**
     * Supports filtering by individual students or a group.
     */
    public function downloadSba(Request $request)
    {
        $request->validate([
            'program_id'    => 'required|exists:programs,id',
            'attempt'       => 'required|integer|min:1',
            'include_exam'  => 'nullable|boolean',
            'split_sba'     => 'nullable|boolean',
            'student_ids'   => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $program  = Program::findOrFail($request->program_id);
        $isBranch = auth()->check() && auth()->user()->isBranchAdmin();
        $branchId = $isBranch ? auth()->user()->church_branch_id : null;

        $students = $this->loadStudentsForAttempt($program, (int)$request->attempt, $branchId);

        // Filter to specific students if requested
        if ($request->filled('student_ids')) {
            $selected = collect($request->student_ids)->map('intval');
            $students = $students->whereIn('id', $selected)->values();
        }

        $courses     = $program->courses()->orderBy('name')->get();
        $includeExam = $request->boolean('include_exam', false);
        $splitSba    = $request->boolean('split_sba', true);

        $suffix   = $includeExam ? '_with_Exam' : '_SBA_only';
        $filter   = $request->filled('student_ids') ? '_selected' : '_all';
        $filename = 'SBA_' . \Str::slug($program->name) . '_Attempt' . $request->attempt . $suffix . $filter . '.xlsx';

        return Excel::download(
            new \App\Exports\SbaScoreExport($program, $courses, $students, (int)$request->attempt, $includeExam, $splitSba),
            $filename
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'attempt'    => 'required|integer|min:1',
            'sheet_file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new ExamSheetImport($request->program_id, $request->attempt), $request->file('sheet_file'));
            return redirect()->route('admin.exams.sheet', [
                'program_id' => $request->program_id,
                'attempt' => $request->attempt
            ])->with('success', 'Exam sheet uploaded and scores imported successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to import scores. Please ensure the file matches the downloaded template format. Error: ' . $e->getMessage()]);
        }
    }
}
