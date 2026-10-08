<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseAssignment;
use App\Models\Course;
use App\Models\TeacherWorkLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherWorkLogController extends Controller
{
    public function index(Request $request)
    {
        $isBranch = auth()->check() && auth()->user()->isBranchAdmin();
        $branchId = $isBranch ? auth()->user()->church_branch_id : null;

        // Filters
        $filterLecturer = $request->lecturer_id;
        $filterCourse   = $request->course_id;
        $filterType     = $request->type;
        $filterDate     = $request->date_from;

        // ── STEP 1: Get all lecturer_ids who have ANY work log entry ──────────
        // Use raw DB so this works even if extra columns are missing
        $loggerIds = collect();
        try {
            $loggerIds = DB::table('teacher_work_logs')->distinct()->pluck('lecturer_id');
        } catch (\Throwable $e) {
            \Log::warning('WorkLog: could not fetch logger IDs: ' . $e->getMessage());
        }

        // ── STEP 2: Build lecturers list ──────────────────────────────────────
        // First: anyone with a teacher/lecturer role
        $roleQuery = User::where(function ($q) {
            $q->whereIn('role', ['lecturer', 'teacher', 'Teacher'])
              ->orWhereRaw('LOWER(role) LIKE ?', ['%lecturer%'])
              ->orWhereRaw('LOWER(role) LIKE ?', ['%teacher%']);
        });
        if ($branchId) {
            $roleQuery->where(function($q) use ($branchId) {
                $q->where('church_branch_id', $branchId)
                  ->orWhereNull('church_branch_id');
            });
        }
        $lecturers = $roleQuery->orderBy('full_name')->get();

        // Second: anyone who has logged work but wasn't caught by the role filter
        if ($loggerIds->isNotEmpty()) {
            $extraQuery = User::whereIn('id', $loggerIds)
                ->whereNotIn('id', $lecturers->pluck('id'));
            if ($branchId) {
                $extraQuery->where(function($q) use ($branchId) {
                    $q->where('church_branch_id', $branchId)
                      ->orWhereNull('church_branch_id');
                });
            }
            $extra     = $extraQuery->orderBy('full_name')->get();
            $lecturers = $lecturers->merge($extra)->sortBy('full_name')->values();
        }

        // ── STEP 3: Course assignments ────────────────────────────────────────
        $assignments           = collect();
        $assignmentsByLecturer = collect();
        try {
            $aQuery = CourseAssignment::with(['program', 'course', 'lecturer']);
            if ($branchId) {
                $aQuery->whereHas('lecturer', function($q) use ($branchId) {
                    $q->where('church_branch_id', $branchId)
                      ->orWhereNull('church_branch_id');
                });
            }
            $assignments           = $aQuery->get();
            $assignmentsByLecturer = $assignments->groupBy('lecturer_id');
        } catch (\Throwable $e) {
            \Log::warning('Work log: assignments fetch failed: ' . $e->getMessage());
        }

        // ── STEP 4: Work logs — multi-level fallback ──────────────────────────
        $allLogs = collect();

        // Attempt A: full query with all columns
        try {
            $q = TeacherWorkLog::with(['lecturer', 'program', 'course'])
                ->orderByDesc('date')->orderByDesc('id');
            if ($branchId) {
                $lecturerIdsInBranch = User::where(function($sub) use ($branchId) {
                    $sub->where('church_branch_id', $branchId)
                        ->orWhereNull('church_branch_id');
                })->pluck('id');
                $q->whereIn('lecturer_id', $lecturerIdsInBranch);
            }
            if ($filterLecturer) $q->where('lecturer_id', $filterLecturer);
            if ($filterCourse)   $q->where('course_id',   $filterCourse);
            if ($filterType)     $q->where('type',         $filterType);
            if ($filterDate)     $q->where('date',        '>=', $filterDate);
            $allLogs = $q->get();
        } catch (\Throwable $e) {
            \Log::warning('WorkLog full query failed: ' . $e->getMessage());

            // Attempt B: only core columns that definitely exist
            try {
                $q2 = TeacherWorkLog::select('id', 'lecturer_id', 'program_id', 'course_id', 'type', 'title', 'date', 'created_at', 'updated_at')
                    ->with(['lecturer', 'program', 'course'])
                    ->orderByDesc('date')->orderByDesc('id');
                if ($branchId) {
                    $lecturerIdsInBranch = User::where(function($sub) use ($branchId) {
                        $sub->where('church_branch_id', $branchId)
                            ->orWhereNull('church_branch_id');
                    })->pluck('id');
                    $q2->whereIn('lecturer_id', $lecturerIdsInBranch);
                }
                if ($filterLecturer) $q2->where('lecturer_id', $filterLecturer);
                if ($filterCourse)   $q2->where('course_id',   $filterCourse);
                if ($filterType)     $q2->where('type',         $filterType);
                if ($filterDate)     $q2->where('date',        '>=', $filterDate);
                $allLogs = $q2->get();
            } catch (\Throwable $e2) {
                \Log::warning('WorkLog fallback query also failed: ' . $e2->getMessage());

                // Attempt C: pure raw DB — no Eloquent at all
                try {
                    $rawRows = DB::table('teacher_work_logs')
                        ->orderByDesc('date')->orderByDesc('id')
                        ->get(['id', 'lecturer_id', 'program_id', 'course_id', 'type', 'title', 'date', 'created_at', 'updated_at']);
                    
                    $usersById    = User::whereIn('id', $rawRows->pluck('lecturer_id')->unique())->get()->keyBy('id');
                    $coursesById  = Course::whereIn('id', $rawRows->pluck('course_id')->unique())->get()->keyBy('id');
                    $programsById = \App\Models\Program::whereIn('id', $rawRows->pluck('program_id')->unique())->get()->keyBy('id');

                    $allLogs = $rawRows->map(function ($row) use ($usersById, $coursesById, $programsById) {
                        $model = new TeacherWorkLog();
                        foreach ((array) $row as $k => $v) { $model->$k = $v; }
                        $model->setRelation('lecturer', $usersById->get($row->lecturer_id));
                        $model->setRelation('course', $coursesById->get($row->course_id));
                        $model->setRelation('program', $programsById->get($row->program_id));
                        return $model;
                    });
                } catch (\Throwable $e3) {
                    \Log::error('WorkLog raw query failed: ' . $e3->getMessage());
                }
            }
        }

        $logsByLecturer = $allLogs->groupBy('lecturer_id');

        // ── STEP 5: Stats ─────────────────────────────────────────────────────
        $stats = [];
        foreach ($assignments as $a) {
            $key = "{$a->lecturer_id}_{$a->program_id}_{$a->course_id}";
            if (! isset($stats[$key])) {
                $stats[$key] = ['classwork' => 0, 'homework' => 0, 'monthly_test' => 0];
            }
        }
        foreach ($allLogs as $log) {
            $key = "{$log->lecturer_id}_{$log->program_id}_{$log->course_id}";
            if (! isset($stats[$key])) {
                $stats[$key] = ['classwork' => 0, 'homework' => 0, 'monthly_test' => 0];
            }
            if (isset($stats[$key][$log->type])) {
                $stats[$key][$log->type]++;
            }
        }

        // ── STEP 6: Courses for expectations form ─────────────────────────────
        $courses = Course::with('program')->orderBy('name')->get();

        $globalExpected = [
            'classwork'    => (int) Setting::get('expected_classworks', 4),
            'homework'     => (int) Setting::get('expected_homeworks', 4),
            'monthly_test' => (int) Setting::get('expected_tests', 1),
        ];

        return view('admin.work-monitoring.index', compact(
            'lecturers', 'assignmentsByLecturer', 'stats',
            'logsByLecturer', 'courses', 'globalExpected',
            'allLogs', 'filterLecturer', 'filterCourse', 'filterType', 'filterDate'
        ));
    }

    /** Update per-course expectations */
    public function updateExpectations(Request $request, Course $course)
    {
        $request->validate([
            'expected_classworks' => 'nullable|integer|min:0',
            'expected_homeworks'  => 'nullable|integer|min:0',
            'expected_tests'      => 'nullable|integer|min:0',
        ]);
        $course->update([
            'expected_classworks' => $request->expected_classworks,
            'expected_homeworks'  => $request->expected_homeworks,
            'expected_tests'      => $request->expected_tests,
        ]);
        return back()->with('success', "Expectations updated for {$course->name}.");
    }

    /** Return full detail of a single log entry as JSON (for modal) */
    public function show(TeacherWorkLog $log)
    {
        $log->load(['lecturer', 'program', 'course']);

        return response()->json([
            'id'            => $log->id,
            'date'          => $log->date instanceof \Carbon\Carbon
                                ? $log->date->format('M d, Y')
                                : \Carbon\Carbon::parse($log->date)->format('M d, Y'),
            'teacher'       => $log->lecturer?->full_name ?? '—',
            'type'          => $log->type ?? '—',
            'type_label'    => match($log->type) {
                                'classwork'    => 'Classwork',
                                'homework'     => 'Homework',
                                'monthly_test' => 'Monthly Test',
                                default        => ucfirst($log->type ?? 'Unknown'),
                               },
            'subject'       => $log->course?->name ?? '—',
            'program'       => $log->program?->name ?? '—',
            'title'         => $log->title ?? '—',
            'topic_covered' => $log->topic_covered ?? null,
            'week_number'   => $log->week_number ?? null,
            'notes'         => $log->notes ?? null,
            'admin_comment' => $log->admin_comment ?? null,
        ]);
    }

    /** Admin adds a comment / feedback to a specific log entry */
    public function comment(Request $request, TeacherWorkLog $log)
    {
        $request->validate(['admin_comment' => 'required|string|max:1000']);
        try {
            $log->update(['admin_comment' => $request->admin_comment]);
        } catch (\Throwable $e) {
            DB::table('teacher_work_logs')->where('id', $log->id)->update(['admin_comment' => $request->admin_comment]);
        }
        return back()->with('success', 'Comment saved to log entry.');
    }

    /** Admin deletes a log entry */
    public function destroyLog(TeacherWorkLog $log)
    {
        $log->delete();
        return back()->with('success', 'Log entry deleted.');
    }
}
