<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\CourseAssignment;
use App\Models\Enrollment;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function create(Request $request)
    {
        abort_unless(Setting::lecturerCan('attendance'), 403, 'Attendance recording has been disabled by the administrator.');

        $lecturer = auth()->user();

        // Get programs where the lecturer is the class teacher
        $assignments = \App\Models\ClassTeacherAssignment::with(['program'])
            ->where('lecturer_id', $lecturer->id)
            ->get();

        // All periods ordered by session year desc, then sequence
        $periods = AcademicPeriod::with('session')
            ->orderByDesc('is_active')
            ->get()
            ->sortBy(fn($p) => [$p->session?->year ?? '', $p->sequence ?? 0])
            ->values();

        // The currently active period (system-wide)
        $activePeriod = AcademicSession::activePeriod();

        // Default period_id to active period if none selected
        $selectedPeriodId = $request->filled('period_id')
            ? $request->period_id
            : ($activePeriod?->id);

        $students       = collect();
        $existingByStudent = collect(); // keyed by student_id for pre-filling
        $selectedDate   = $request->filled('date') ? $request->date : date('Y-m-d');
        $isEditMode     = false;

        if ($request->filled('assignment') && $selectedPeriodId) {
            $programId = $request->assignment;

            if (! $assignments->contains(fn($a) => $a->program_id == $programId)) {
                abort(403, 'You are not authorized to record attendance for the selected class.');
            }

            $query = $lecturer->lecturerStudentQuery((int)$programId)
                ->with('user')
                ->whereNotIn('status', Student::EXAM_EXCLUDED_STATUSES);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('student_id', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($u) => $u->where('full_name', 'like', "%{$search}%"));
                });
            }

            $students = $query->orderBy('student_id')->get();

            // Check if records already exist for this program+period+date
            // If yes → edit mode (pre-fill statuses)
            $existing = Attendance::whereNull('course_id')
                ->where('program_id', $programId)
                ->where('academic_period_id', $selectedPeriodId)
                ->where('date', $selectedDate)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();

            if ($existing->isNotEmpty()) {
                $isEditMode        = true;
                $existingByStudent = $existing->keyBy('student_id');
            }
        }

        return view('lecturer.attendance.create', compact(
            'assignments', 'periods', 'students',
            'activePeriod', 'selectedPeriodId', 'selectedDate',
            'existingByStudent', 'isEditMode'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(Setting::lecturerCan('attendance'), 403, 'Attendance recording has been disabled by the administrator.');

        $request->validate([
            'program_id'         => 'required|exists:programs,id',
            'academic_period_id' => 'required|exists:academic_periods,id',
            'date'               => 'required|date',
            'attendances'        => 'required|array',
            'attendances.*'      => 'in:present,absent',
        ]);

        $lecturer = auth()->user();

        abort_unless(
            \App\Models\ClassTeacherAssignment::where('lecturer_id', $lecturer->id)
                ->where('program_id', $request->program_id)
                ->exists(),
            403,
            'You are not authorized to record attendance for this class.'
        );

        // Prevent modifying attendance for a past date if it was already marked?
        // Wait, the requirement says "the attendance should be marked by class teachers once and that all, no other marking of attendance by other teachers again".
        // updateOrCreate is fine, since it overwrites their own marking. The "no other marking" is satisfied by `abort_unless` checking `ClassTeacherAssignment`.
        
        foreach ($request->attendances as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id'         => $studentId,
                    'course_id'          => null, // Class attendance is not tied to a specific course
                    'program_id'         => $request->program_id,
                    'academic_period_id' => $request->academic_period_id,
                    'date'               => $request->date,
                ],
                ['status' => $status]
            );
        }

        return redirect()->route('lecturer.attendance.create', [
            'assignment' => $request->program_id,
            'period_id'  => $request->academic_period_id,
            'date'       => $request->date,
        ])->with('success', 'Attendance saved. You can edit it at any time by selecting the same date.');
    }
}
