<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Student;
use App\Services\PromotionService;
use App\Services\DemotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    // ── Branch helper ────────────────────────────────────────────────────────────

    /**
     * Returns the branch ID if the logged-in user is a branch admin, or null for super-admins.
     */
    private function branchId(): ?int
    {
        $user = auth()->user();
        return ($user && $user->isBranchAdmin()) ? (int) $user->church_branch_id : null;
    }

    /**
     * Abort with 403 if a branch admin tries to act on a student from another branch.
     */
    private function authorizeStudent(Student $student): void
    {
        $branchId = $this->branchId();
        if ($branchId && (int) $student->church_branch_id !== $branchId) {
            abort(403, 'You are not authorised to manage students from another branch.');
        }
    }

    // ── Single student ──────────────────────────────────────────────────────────

    public function promote(Student $student)
    {
        $this->authorizeStudent($student);

        try {
            $service = app(PromotionService::class);
            $service->promote($student);
            $programName = $student->fresh()->program?->name ?? 'next program';
            return back()->with('success', "Student promoted successfully to {$programName}.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Promote failed', ['student' => $student->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Promotion failed: ' . $e->getMessage());
        }
    }

    public function demote(Student $student)
    {
        $this->authorizeStudent($student);

        try {
            $service = app(DemotionService::class);
            $previousProgram = $service->getPreviousProgram($student);
            if (!$previousProgram) {
                return back()->with('error', "Cannot demote student: no previous program in promotion history.");
            }
            $service->demote($student);
            $programName = $student->fresh()->program?->name ?? 'previous program';
            return back()->with('success', "Student demoted successfully to {$programName}.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Demote failed', ['student' => $student->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Demotion failed: ' . $e->getMessage());
        }
    }

    // ── Bulk operations ─────────────────────────────────────────────────────────

    /**
     * Show the bulk promote selection page.
     * Lists all active students grouped by their current program.
     */
    public function bulkPromotePage()
    {
        try {
            $branchId = $this->branchId();

            $programs = Program::with(['students' => function ($q) use ($branchId) {
                $q->with('user')
                  ->whereIn('status', ['active', 'manifestation'])
                  ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId))
                  ->orderBy('student_id');
            }])->orderBy('sequence')->orderBy('id')->get();

            // Determine the final program so we can warn about non-promotable students
            $finalProgramId = $programs->last()?->id;

            return view('admin.students.bulk-promote', compact('programs', 'finalProgramId'));
        } catch (\Throwable $e) {
            \Log::error('bulkPromotePage failed: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return redirect()->route('admin.students.index')->with('error', 'Bulk promotion page failed to load: ' . $e->getMessage());
        }
    }

    /**
     * Show the bulk promotion & repeat page (filtered by program).
     */
    public function bulkIndex(Request $request)
    {
        try {
            $programs = Program::orderBy('sequence')->orderBy('id')->get();
            $selectedProgram = null;
            $students = collect();

            $branchId = $this->branchId();

            if ($request->filled('program_id')) {
                $selectedProgram = Program::find($request->program_id);
                if ($selectedProgram) {
                    $query = Student::where('program_id', $selectedProgram->id)
                        ->whereIn('status', ['active', 'manifestation'])
                        ->with('user');
                    if ($branchId) {
                        $query->where('church_branch_id', $branchId);
                    }
                    $students = $query->orderBy('id')->get();
                }
            }

            $nextProgram = null;
            if ($selectedProgram && $selectedProgram->sequence !== null) {
                $nextProgram = Program::where('sequence', '>', $selectedProgram->sequence)
                    ->orderBy('sequence')->orderBy('id')->first();
            }

            return view('admin.students.bulk-promotion', compact(
                'programs', 'selectedProgram', 'students', 'nextProgram'
            ));
        } catch (\Throwable $e) {
            \Log::error('bulkIndex failed: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return redirect()->route('admin.students.index')->with('error', 'Bulk promotion page failed to load: ' . $e->getMessage());
        }
    }

    /**
     * Process the bulk promotion of selected students.
     */
    public function bulkPromote(Request $request)
    {
        try {
            $request->validate([
                'student_ids'   => 'required|array|min:1',
                'student_ids.*' => 'exists:students,id',
            ]);

            $branchId = $this->branchId();
            $service  = app(PromotionService::class);
            $promoted = [];
            $skipped  = [];
            $errors   = [];

            foreach ($request->student_ids as $id) {
                $student = Student::with(['user', 'program', 'churchBranch'])->find($id);
                if (! $student) continue;

                // Branch isolation: silently skip students from other branches
                if ($branchId && (int) $student->church_branch_id !== $branchId) {
                    $skipped[] = ($student->user?->full_name ?? "#{$id}") . ': not in your branch';
                    continue;
                }

                try {
                    $service->promote($student);
                    $toProgram  = $student->fresh()->program?->name ?? 'next';
                    $promoted[] = ($student->user?->full_name ?? "#{$student->id}") . " → {$toProgram}";
                } catch (\InvalidArgumentException $e) {
                    $skipped[] = ($student->user?->full_name ?? "#{$student->id}") . ': ' . $e->getMessage();
                } catch (\Throwable $e) {
                    $errors[] = ($student->user?->full_name ?? "#{$id}") . ': ' . $e->getMessage();
                }
            }

            $msg    = count($promoted) . ' student(s) promoted successfully.';
            if ($skipped) $msg .= ' Skipped: ' . implode('; ', $skipped);
            if ($errors)  $msg .= ' Errors: '  . implode('; ', $errors);
            $status = count($promoted) > 0 ? 'success' : 'error';

            return redirect()->route('admin.students.index')->with($status, $msg);
        } catch (\Throwable $e) {
            \Log::error('bulkPromote failed: ' . $e->getMessage());
            return redirect()->route('admin.students.index')->with('error', 'Bulk promotion failed: ' . $e->getMessage());
        }
    }

    /**
     * Process marking selected students to repeat.
     */
    public function bulkRepeat(Request $request)
    {
        try {
            $request->validate([
                'student_ids'   => 'required|array|min:1',
                'student_ids.*' => 'exists:students,id',
            ]);

            $branchId = $this->branchId();
            $repeated = [];
            $errors   = [];

            foreach ($request->student_ids as $id) {
                $student = Student::with('user')->find($id);
                if (! $student) continue;

                // Branch isolation: silently skip students from other branches
                if ($branchId && (int) $student->church_branch_id !== $branchId) {
                    continue;
                }

                try {
                    $student->update(['status' => 'active']);
                    $repeated[] = $student->user?->full_name ?? "#{$id}";
                } catch (\Throwable $e) {
                    $errors[] = ($student->user?->full_name ?? "#{$id}") . ': ' . $e->getMessage();
                }
            }

            $msg    = count($repeated) . ' student(s) marked to repeat.';
            if ($errors) $msg .= ' Errors: ' . implode('; ', $errors);
            $status = count($repeated) > 0 ? 'success' : 'error';

            return redirect()->route('admin.students.index')->with($status, $msg);
        } catch (\Throwable $e) {
            \Log::error('bulkRepeat failed: ' . $e->getMessage());
            return redirect()->route('admin.students.index')->with('error', 'Bulk repeat failed: ' . $e->getMessage());
        }
    }
}
