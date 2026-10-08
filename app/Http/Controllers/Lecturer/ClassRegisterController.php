<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\ClassTeacherAssignment;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Http\Request;

class ClassRegisterController extends Controller
{
    /**
     * Show the class register for the lecturer's assigned class.
     * Class teachers mark attendance for ALL students in their program —
     * not course-specific, just class-level present/absent.
     */
    public function index()
    {
        $lecturer   = auth()->user();
        $assignment = ClassTeacherAssignment::where('lecturer_id', $lecturer->id)
            ->with('program')
            ->first();

        abort_unless($assignment, 403, 'You are not assigned as a class teacher for any class.');

        $program  = $assignment->program;
        $periods  = \App\Models\AcademicPeriod::with('session')
            ->orderByDesc('is_active')
            ->get()
            ->sortBy(fn($p) => [$p->session?->year ?? '', $p->sequence ?? 0])
            ->values();

        $activePeriod    = AcademicSession::activePeriod();
        $selectedPeriodId = request('period_id') ?? $activePeriod?->id;
        $selectedDate    = request('date', today()->toDateString());

        $students = $lecturer->lecturerStudentQuery($program->id)
            ->with('user')
            ->forExams()
            ->orderBy('student_id')
            ->get();

        // Check for existing attendance records for today
        $existing = collect();
        $isEditMode = false;

        if ($selectedPeriodId) {
            $records = Attendance::where('program_id', $program->id)
                ->where('academic_period_id', $selectedPeriodId)
                ->where('date', $selectedDate)
                ->whereNull('course_id')           // class-level records have no course_id
                ->whereIn('student_id', $students->pluck('id'))
                ->get();

            if ($records->isNotEmpty()) {
                $isEditMode = true;
                $existing   = $records->keyBy('student_id');
            }
        }

        return view('lecturer.class-register.index', compact(
            'program', 'students', 'periods', 'activePeriod',
            'selectedPeriodId', 'selectedDate', 'existing', 'isEditMode'
        ));
    }

    public function store(Request $request)
    {
        $lecturer   = auth()->user();
        $assignment = ClassTeacherAssignment::where('lecturer_id', $lecturer->id)->first();
        abort_unless($assignment, 403);

        $request->validate([
            'academic_period_id' => 'required|exists:academic_periods,id',
            'date'               => 'required|date',
            'attendances'        => 'required|array',
            'attendances.*'      => 'in:present,absent',
        ]);

        foreach ($request->attendances as $studentId => $status) {
            // course_id is NULL — this is a class-level (whole-day) attendance record
            Attendance::updateOrCreate(
                [
                    'student_id'         => $studentId,
                    'program_id'         => $assignment->program_id,
                    'course_id'          => null,
                    'academic_period_id' => $request->academic_period_id,
                    'date'               => $request->date,
                ],
                ['status' => $status]
            );
        }

        return redirect()->route('lecturer.class-register.index', [
            'period_id' => $request->academic_period_id,
            'date'      => $request->date,
        ])->with('success', 'Class register saved. You can edit it any time by selecting the same date.');
    }
}
