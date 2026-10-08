<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Course;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $branchId = null;
        if (request()->filled('branch_id')) {
            $branchId = request('branch_id');
        } elseif (auth()->check() && auth()->user()->isBranchAdmin()) {
            $branchId = auth()->user()->church_branch_id;
        }

        $query = Program::with(['courses', 'programFee'])->orderBy('sequence');

        if (!is_null($branchId)) {
            $query->withCount(['students as students_count' => function ($q) use ($branchId) {
                $q->where('church_branch_id', $branchId);
            }]);
        } else {
            $query->withCount('students');
        }

        $programs = $query->paginate(15);
        return view('admin.programs.index', compact('programs'));
    }

    public function create()
    {
        return view('admin.programs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'duration'            => 'required|integer|min:1',
            'requirements'        => 'nullable|string',
            'sequence'            => 'required|integer|min:0',
            'expected_classworks' => 'nullable|integer|min:0',
            'expected_homeworks'  => 'nullable|integer|min:0',
            'expected_tests'      => 'nullable|integer|min:0',
        ]);

        Program::create($request->only('name', 'duration', 'requirements', 'sequence', 'expected_classworks', 'expected_homeworks', 'expected_tests'));
        return redirect()->route('admin.programs.index')->with('success', 'Program created successfully.');
    }

    public function show(Program $program)
    {
        $query = $program->students()->with('user');

        if (request()->filled('branch_id')) {
            $query->where('church_branch_id', request('branch_id'));
        } elseif (auth()->check() && auth()->user()->isBranchAdmin()) {
            $query->where('church_branch_id', auth()->user()->church_branch_id);
        }

        $students = $query->get();
        $program->setRelation('students', $students);
        $program->loadMissing(['courses', 'enrollments']);

        $enrollmentCount = $students->count();
        return view('admin.programs.show', compact('program', 'enrollmentCount'));
    }

    public function edit(Program $program)
    {
        return view('admin.programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'duration'            => 'required|integer|min:1',
            'requirements'        => 'nullable|string',
            'sequence'            => 'required|integer|min:0',
            'expected_classworks' => 'nullable|integer|min:0',
            'expected_homeworks'  => 'nullable|integer|min:0',
            'expected_tests'      => 'nullable|integer|min:0',
        ]);

        $program->update($request->only('name', 'duration', 'requirements', 'sequence', 'expected_classworks', 'expected_homeworks', 'expected_tests'));
        return redirect()->route('admin.programs.index')->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        if ($program->students()->exists() || $program->enrollments()->exists()) {
            return back()->with('error', 'Cannot delete a program with enrolled students.');
        }

        $program->delete();
        return redirect()->route('admin.programs.index')->with('success', 'Program deleted successfully.');
    }

    public function courses(Program $program)
    {
        return response()->json($program->courses()->select('id', 'name', 'code')->get());
    }

    public function students(Program $program)
    {
        // Allow optional branch filtering via `branch_id` query param.
        // Also automatically scope to the authenticated branch admin's branch.
        $query = $program->students()->with('user');

        if (request()->filled('branch_id')) {
            $query->where('church_branch_id', request('branch_id'));
        } else if (auth()->check() && auth()->user()->isBranchAdmin()) {
            $query->where('church_branch_id', auth()->user()->church_branch_id);
        }

        $students = $query->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'student_id' => $s->student_id,
                'full_name'  => $s->user?->full_name ?? '—',
                'name'       => $s->user?->full_name ?? '—',
                'photo'      => $s->photo,
                'photo_url'  => $s->photo_url,
                'church_branch_id' => $s->church_branch_id,
            ]);

        return response()->json($students);
    }
}
