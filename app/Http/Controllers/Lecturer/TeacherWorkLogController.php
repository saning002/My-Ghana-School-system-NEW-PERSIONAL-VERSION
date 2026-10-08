<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\TeacherWorkLog;
use App\Models\CourseAssignment;
use App\Models\Setting;
use Illuminate\Http\Request;

class TeacherWorkLogController extends Controller
{
    public function index(Request $request)
    {
        $lecturerId  = auth()->id();

        $assignments = CourseAssignment::with(['program', 'course'])
            ->where('lecturer_id', $lecturerId)
            ->get();

        $logsQuery = TeacherWorkLog::with(['program', 'course'])
            ->where('lecturer_id', $lecturerId)
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->filled('course_id')) { $logsQuery->where('course_id', $request->course_id); }
        if ($request->filled('type'))      { $logsQuery->where('type', $request->type); }

        try {
            $logs = $logsQuery->get();
        } catch (\Throwable $e) {
            // Fallback: query only core columns if new columns don't exist yet
            \Log::warning('TeacherWorkLog lecturer query error: ' . $e->getMessage());
            $logs = TeacherWorkLog::select('id','lecturer_id','program_id','course_id','type','title','date','created_at','updated_at')
                ->with(['program','course'])
                ->where('lecturer_id', $lecturerId)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();
        }

        // Global expected targets for display
        $globalCw = (int) Setting::get('expected_classworks', 4);
        $globalHw = (int) Setting::get('expected_homeworks', 4);
        $globalTs = (int) Setting::get('expected_tests', 1);

        return view('lecturer.work-logs.index', compact(
            'assignments', 'logs', 'globalCw', 'globalHw', 'globalTs'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:course_assignments,id',
            'type'          => 'required|in:classwork,homework,monthly_test',
            'title'         => 'required|string|max:255',
            'notes'         => 'nullable|string|max:1000',
            'topic_covered' => 'nullable|string|max:255',
            'week_number'   => 'nullable|integer|min:1|max:52',
            'date'          => 'required|date',
        ]);

        $assignment = CourseAssignment::where('lecturer_id', auth()->id())
            ->findOrFail($request->assignment_id);

        TeacherWorkLog::create([
            'lecturer_id'   => auth()->id(),
            'program_id'    => $assignment->program_id,
            'course_id'     => $assignment->course_id,
            'type'          => $request->type,
            'title'         => $request->title,
            'notes'         => $request->notes,
            'topic_covered' => $request->topic_covered,
            'week_number'   => $request->week_number,
            'date'          => $request->date,
        ]);

        return back()->with('success', 'Work log entry saved successfully.');
    }

    public function destroy(TeacherWorkLog $teacherWorkLog)
    {
        abort_unless($teacherWorkLog->lecturer_id === auth()->id(), 403);
        $teacherWorkLog->delete();
        return back()->with('success', 'Log entry deleted.');
    }
}
