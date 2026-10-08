<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use App\Models\Student;
use App\Models\ExamScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index()
    {
        $programs = Program::with(['courses' => function ($q) {
            $q->withCount('enrollments')->orderBy('name');
        }])->orderBy('sequence')->get();

        $totalCourses = Course::count();

        return view('admin.courses.index', compact('programs', 'totalCourses'));
    }

    public function create()
    {
        $programs = Program::orderBy('sequence')->get();
        return view('admin.courses.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|unique:courses,code',
            'description' => 'nullable|string',
            'program_id'  => 'required|exists:programs,id',
        ]);

        $course = Course::create($request->only('name', 'code', 'description', 'program_id'));

        // Auto-enroll all students already in this program
        $year = date('Y') . '/' . (date('Y') + 1);
        $studentIds = Student::where('program_id', $course->program_id)->pluck('id');
        foreach ($studentIds as $sid) {
            DB::table('enrollments')->updateOrInsert(
                ['student_id' => $sid, 'course_id' => $course->id, 'program_id' => $course->program_id],
                ['year' => $year, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created and ' . count($studentIds) . ' student(s) auto-enrolled successfully.');
    }

    public function show(Course $course)
    {
        $course->load(['program', 'enrollments.student.user']);
        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $programs = Program::orderBy('sequence')->get();
        return view('admin.courses.edit', compact('course', 'programs'));
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|unique:courses,code,' . $course->id,
            'description' => 'nullable|string',
            'program_id'  => 'required|exists:programs,id',
        ]);

        $course->update($request->only('name', 'code', 'description', 'program_id'));
        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        if (ExamScore::where('course_id', $course->id)->exists()) {
            return back()->with('error', 'Cannot delete a course with recorded exam scores.');
        }

        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }
}
