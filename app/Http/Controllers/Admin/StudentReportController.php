<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeDefinition;
use App\Models\Program;
use App\Models\Student;
use App\Models\StudentReport;
use App\Models\StudentReportAttribute;
use Illuminate\Http\Request;

/**
 * Manages:
 *  1. Attribute Definitions — admin creates/edits/deletes personality attributes
 *  2. Student Reports — admin/teacher fills in conduct, attitude, interest,
 *     remarks, personality ratings for a specific student + program + attempt
 */
class StudentReportController extends Controller
{
    // ── 1. Attribute Definitions ─────────────────────────────────────────────

    public function attributeIndex()
    {
        $attributes = AttributeDefinition::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.student-reports.attributes', compact('attributes'));
    }

    public function attributeStore(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:150',
            'type'       => 'required|in:personality,conduct,attitude,interest',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        AttributeDefinition::create([
            'name'       => $request->name,
            'type'       => $request->type,
            'sort_order' => $request->sort_order ?? 99,
            'is_active'  => true,
        ]);
        return back()->with('success', 'Attribute added successfully.');
    }

    public function attributeUpdate(Request $request, AttributeDefinition $attributeDefinition)
    {
        $request->validate([
            'name'       => 'required|string|max:150',
            'type'       => 'required|in:personality,conduct,attitude,interest',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);
        $attributeDefinition->update([
            'name'       => $request->name,
            'type'       => $request->type,
            'sort_order' => $request->sort_order ?? $attributeDefinition->sort_order,
            'is_active'  => $request->boolean('is_active', true),
        ]);
        return back()->with('success', 'Attribute updated.');
    }

    public function attributeDestroy(AttributeDefinition $attributeDefinition)
    {
        $attributeDefinition->delete();
        return back()->with('success', 'Attribute deleted.');
    }

    public function attributeReorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);
        foreach ($request->order as $sortOrder => $id) {
            AttributeDefinition::where('id', $id)->update(['sort_order' => $sortOrder]);
        }
        return response()->json(['ok' => true]);
    }

    // ── 2. Student Reports ───────────────────────────────────────────────────

    public function reportIndex(Request $request)
    {
        $user      = auth()->user();
        $isBranch  = $user->isBranchAdmin();
        $branchId  = $isBranch ? $user->church_branch_id : null;

        $programs  = Program::orderBy('sequence')->get();
        $students  = collect();
        $reports   = collect();

        $selectedProgram = null;
        $selectedAttempt = (int) $request->get('attempt', 1);

        if ($request->filled('program_id')) {
            $selectedProgram = Program::find($request->program_id);
            $students = Student::with('user')
                ->where('program_id', $request->program_id)
                ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId))
                ->forExams()
                ->orderBy('student_id')
                ->get();

            $reports = StudentReport::where('program_id', $request->program_id)
                ->where('attempt', $selectedAttempt)
                ->whereIn('student_id', $students->pluck('id'))
                ->with(['student.user'])
                ->get()
                ->keyBy('student_id');
        }

        return view('admin.student-reports.index', compact(
            'programs', 'students', 'reports', 'selectedProgram', 'selectedAttempt'
        ));
    }

    public function reportEdit(Request $request, Student $student)
    {
        $program = Program::findOrFail($request->program_id ?? $student->program_id);
        $attempt = (int) $request->get('attempt', 1);

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

        return view('admin.student-reports.edit', compact(
            'student', 'program', 'attempt', 'report', 'attributes', 'existingRatings'
        ));
    }

    public function reportSave(Request $request, Student $student)
    {
        $request->validate([
            'program_id'           => 'required|exists:programs,id',
            'attempt'              => 'required|integer|min:1',
            'conduct'              => 'nullable|string|max:255',
            'attitude'             => 'nullable|string|max:255',
            'interest'             => 'nullable|string|max:255',
            'class_teacher_remark' => 'nullable|string|max:1000',
            'head_teacher_remark'  => 'nullable|string|max:1000',
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
                'head_teacher_remark'  => $request->head_teacher_remark,
                'promoted_to'          => $request->promoted_to,
            ]
        );

        // Save personality attribute ratings
        if ($request->has('ratings')) {
            foreach ($request->ratings as $attrId => $rating) {
                if (! $rating) continue;
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
            // Remove ratings that were cleared (null/empty)
            $clearedIds = collect($request->ratings)->filter(fn($r) => ! $r)->keys();
            if ($clearedIds->isNotEmpty()) {
                StudentReportAttribute::where('student_report_id', $report->id)
                    ->whereIn('attribute_definition_id', $clearedIds)
                    ->delete();
            }
        }

        return redirect()->route('admin.student-reports.index', [
            'program_id' => $request->program_id,
            'attempt'    => $request->attempt,
        ])->with('success', 'Report saved for ' . $student->user->full_name . '.');
    }
}
