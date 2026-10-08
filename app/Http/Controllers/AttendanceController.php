<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\AcademicPeriod;
use App\Models\Enrollment;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function create(Request $request)
    {
        // Lecturer selects course, program, month
        $lecturer = auth()->user();
        
        $assignments = $lecturer->courseAssignments()->with(['course', 'program'])->get();
        $periods = AcademicPeriod::with('session')->get();

        $students = [];
        if ($request->has('course_id') && $request->has('program_id') && $request->has('period_id')) {
            $students = Enrollment::with('student.user')
                ->where('course_id', $request->course_id)
                ->where('program_id', $request->program_id)
                ->get()
                ->pluck('student');
        }

        return view('lecturer.attendance.create', compact('assignments', 'periods', 'students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'program_id' => 'required|exists:programs,id',
            'academic_period_id' => 'required|exists:academic_periods,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*' => 'in:present,absent',
        ]);

        foreach ($request->attendances as $student_id => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $student_id,
                    'course_id' => $request->course_id,
                    'program_id' => $request->program_id,
                    'academic_period_id' => $request->academic_period_id,
                    'date' => $request->date,
                ],
                ['status' => $status]
            );
        }

        return redirect()->back()->with('success', 'Attendance recorded successfully.');
    }
}
