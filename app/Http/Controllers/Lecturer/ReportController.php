<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CourseAssignment;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Student;
use App\Services\ReportCardService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportCardService $reportCardService) {}

    public function index(Request $request)
    {
        abort_unless(Setting::lecturerCan('reports'), 403, 'Student reports have been disabled by the administrator.');
        $lecturer = auth()->user();
        $programIds = CourseAssignment::where('lecturer_id', $lecturer->id)
            ->pluck('program_id')
            ->unique()
            ->toArray();

        $programs = Program::whereIn('id', $programIds)->orderBy('sequence')->get();
        $selectedProgram = null;
        $students = collect();

        if ($request->filled('program_id')) {
            $selectedProgram = Program::whereIn('id', $programIds)->find($request->program_id);
            if ($selectedProgram) {
                $students = $lecturer->lecturerStudentQuery($selectedProgram->id)
                    ->with('user')->orderBy('student_id')->get();
            }
        }

        return view('lecturer.reports.index', compact('programs', 'selectedProgram', 'students'));
    }

    public function student(Student $student, Request $request)
    {
        abort_unless(Setting::lecturerCan('reports'), 403, 'Student reports have been disabled by the administrator.');
        $lecturer = auth()->user();
        $programIds = CourseAssignment::where('lecturer_id', $lecturer->id)
            ->pluck('program_id')
            ->unique()
            ->toArray();

        if (! in_array($student->program_id, $programIds, true)) {
            abort(403, 'You are not authorized to view reports for this student.');
        }

        // Strict branch check — lecturer must share the same branch as the student
        if (! empty($lecturer->church_branch_id)
            && $student->church_branch_id !== $lecturer->church_branch_id) {
            abort(403, 'You are not authorized to view reports for students outside your branch.');
        }

        $program = $student->program;
        $attempt = $request->filled('attempt') ? (int) $request->attempt : 
            $student->examScores()->where('program_id', $program->id)->max('attempt') ?? 1;

        $data = $this->reportCardService->generate($student, $program, $attempt);
        $data['attempt'] = $attempt;
        $data['program'] = $program;

        return view('lecturer.reports.student', $data);
    }
}
