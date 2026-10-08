<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AttributeDefinition;
use App\Models\ClassTeacherAssignment;
use App\Models\CourseAssignment;
use App\Models\Program;
use App\Models\Student;
use App\Models\StudentReport;
use App\Models\StudentReportAttribute;
use Illuminate\Http\Request;

class StudentReportController extends Controller
{
    // ─── Index: list all students in the lecturer's class ────────────────────

    public function index(Request $request)
    {
        $lecturer   = auth()->user();
        $assignment = ClassTeacherAssignment::where('lecturer_id', $lecturer->id)->first();

        $program = null;
        if ($assignment) {
            $program = $assignment->program;
        } else {
            $programId = CourseAssignment::where('lecturer_id', $lecturer->id)->value('program_id');
            $program   = $programId ? Program::find($programId) : null;
        }

        abort_unless($program, 403, 'You are not assigned to any class. Please contact the administrator.');

        $attempt  = (int) $request->get('attempt', 1);
        $students = $lecturer->lecturerStudentQuery($program->id)
            ->with('user')
            ->forExams()
            ->orderBy('student_id')
            ->get();

        $reports = StudentReport::where('program_id', $program->id)
            ->where('attempt', $attempt)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('lecturer.reports.student-reports', compact(
            'program', 'students', 'reports', 'attempt'
        ));
    }

    // ─── Edit: individual student report form ────────────────────────────────

    public function edit(Request $request, Student $student)
    {
        $lecturer   = auth()->user();
        $assignment = ClassTeacherAssignment::where('lecturer_id', $lecturer->id)->first();
        $programId  = $assignment?->program_id
            ?? CourseAssignment::where('lecturer_id', $lecturer->id)->value('program_id');

        abort_unless($programId, 403, 'Not assigned to any class.');

        $program = Program::findOrFail($programId);
        $attempt = (int) $request->get('attempt', 1);

        abort_unless($student->program_id === $program->id, 403, 'Student not in your class.');

        // Also verify the student is in the lecturer's branch
        if (! empty($lecturer->church_branch_id)
            && $student->church_branch_id !== $lecturer->church_branch_id) {
            abort(403, 'You are not authorized to access students outside your branch.');
        }

        $report = StudentReport::firstOrNew([
            'student_id' => $student->id,
            'program_id' => $program->id,
            'attempt'    => $attempt,
        ]);

        $attributes = AttributeDefinition::where('is_active', true)
            ->orderBy('sort_order')->orderBy('name')->get();

        $existingRatings = collect();
        if ($report->exists) {
            $existingRatings = StudentReportAttribute::where('student_report_id', $report->id)
                ->get()->keyBy('attribute_definition_id');
        }

        return view('lecturer.reports.student-report-edit', compact(
            'student', 'program', 'attempt', 'report', 'attributes', 'existingRatings'
        ));
    }

    // ─── Save: individual student report ─────────────────────────────────────

    public function save(Request $request, Student $student)
    {
        $request->validate([
            'program_id'           => 'required|exists:programs,id',
            'attempt'              => 'required|integer|min:1',
            'conduct'              => 'nullable|string|max:255',
            'attitude'             => 'nullable|string|max:255',
            'interest'             => 'nullable|string|max:255',
            'class_teacher_remark' => 'nullable|string|max:1000',
            'promoted_to'          => 'nullable|string|max:255',
            'ratings'              => 'nullable|array',
            'ratings.*'            => 'nullable|in:Very Good,Good,Average,Weak / Poor',
        ]);

        $report = StudentReport::updateOrCreate(
            [
                'student_id' => $student->id,
                'program_id' => $request->program_id,
                'attempt'    => $request->attempt,
            ],
            [
                'created_by'           => auth()->id(),
                'conduct'              => $request->conduct,
                'attitude'             => $request->attitude,
                'interest'             => $request->interest,
                'class_teacher_remark' => $request->class_teacher_remark,
                'promoted_to'          => $request->promoted_to,
            ]
        );

        if ($request->has('ratings')) {
            foreach ($request->ratings as $attrId => $rating) {
                if (! $rating) {
                    StudentReportAttribute::where('student_report_id', $report->id)
                        ->where('attribute_definition_id', $attrId)->delete();
                    continue;
                }
                StudentReportAttribute::updateOrCreate(
                    [
                        'student_report_id'       => $report->id,
                        'student_id'              => $student->id,
                        'program_id'              => $request->program_id,
                        'attribute_definition_id' => $attrId,
                        'attempt'                 => $request->attempt,
                    ],
                    ['rating' => $rating]
                );
            }
        }

        return redirect()->route('lecturer.student-reports.index', ['attempt' => $request->attempt])
            ->with('success', 'Report saved for ' . ($student->user->full_name ?? 'student') . '.');
    }

    // ─── Bulk Save: apply same values to multiple students at once ───────────

    public function bulkSave(Request $request)
    {
        $request->validate([
            'program_id'           => 'required|exists:programs,id',
            'attempt'              => 'required|integer|min:1',
            'student_ids'          => 'required|array|min:1',
            'student_ids.*'        => 'exists:students,id',
            'conduct'              => 'nullable|string|max:255',
            'attitude'             => 'nullable|string|max:255',
            'interest'             => 'nullable|string|max:255',
            'class_teacher_remark' => 'nullable|string|max:1000',
            'promoted_to'          => 'nullable|string|max:255',
            'ratings'              => 'nullable|array',
            'ratings.*'            => 'nullable|in:Very Good,Good,Average,Weak / Poor',
        ]);

        $lecturer = auth()->user();
        $count    = 0;

        foreach ($request->student_ids as $studentId) {
            $student = Student::find($studentId);
            if (! $student) continue;

            // Build update array — only overwrite fields that were actually submitted
            $updateData = ['created_by' => $lecturer->id];
            if ($request->filled('conduct'))              $updateData['conduct']              = $request->conduct;
            if ($request->filled('attitude'))             $updateData['attitude']             = $request->attitude;
            if ($request->filled('interest'))             $updateData['interest']             = $request->interest;
            if ($request->filled('class_teacher_remark')) $updateData['class_teacher_remark'] = $request->class_teacher_remark;
            if ($request->filled('promoted_to'))          $updateData['promoted_to']          = $request->promoted_to;

            $report = StudentReport::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'program_id' => $request->program_id,
                    'attempt'    => $request->attempt,
                ],
                $updateData
            );

            // Save personality ratings (empty = "Skip" — leave existing unchanged)
            if ($request->has('ratings')) {
                foreach ($request->ratings as $attrId => $rating) {
                    if (empty($rating)) continue;
                    StudentReportAttribute::updateOrCreate(
                        [
                            'student_report_id'       => $report->id,
                            'student_id'              => $student->id,
                            'program_id'              => $request->program_id,
                            'attribute_definition_id' => $attrId,
                            'attempt'                 => $request->attempt,
                        ],
                        ['rating' => $rating]
                    );
                }
            }
            $count++;
        }

        return redirect()->route('lecturer.student-reports.index', ['attempt' => $request->attempt])
            ->with('success', "Bulk report saved for {$count} student(s) successfully.");
    }
}
