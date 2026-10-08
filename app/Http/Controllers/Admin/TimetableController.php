<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimetableEntry;
use App\Models\Course;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $programs  = Program::orderBy('sequence')->get();
        $lecturers = User::where('role', 'lecturer')->orderBy('full_name')->get();

        $selectedProgram = $request->filled('program_id')
            ? Program::find($request->program_id)
            : $programs->first();

        // Selected day (default today, Mon–Sat only)
        $date    = $request->filled('date')
            ? \Carbon\Carbon::parse($request->date)
            : \Carbon\Carbon::today();
        $dayNum  = (int) $date->format('N'); // 1=Mon…6=Sat, 7=Sun

        $entries = collect();
        if ($selectedProgram) {
            $entries = TimetableEntry::with(['lecturer','course','program'])
                ->where('program_id', $selectedProgram->id)
                ->when($request->filled('lecturer_id'), fn($q) => $q->where('lecturer_id', $request->lecturer_id))
                ->orderBy('day_of_week')->orderBy('start_time')
                ->get();
        }

        // Today's entries for the selected program
        $todayEntries = $entries->where('day_of_week', $dayNum)->values();

        // Weekly grid
        $grid = [];
        foreach (TimetableEntry::$dayNames as $day => $name) {
            $grid[$day] = $entries->where('day_of_week', $day)->values();
        }

        $courses = $selectedProgram
            ? Course::where('program_id', $selectedProgram->id)->orderBy('name')->get()
            : collect();

        return view('admin.timetable.index', compact(
            'programs', 'lecturers', 'selectedProgram',
            'entries', 'todayEntries', 'grid', 'courses', 'date', 'dayNum'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lecturer_id'  => 'required|exists:users,id',
            'course_id'    => 'required|exists:courses,id',
            'program_id'   => 'required|exists:programs,id',
            'day_of_week'  => 'required|integer|between:1,6',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'room'         => 'nullable|string|max:80',
            'notes'        => 'nullable|string|max:255',
        ]);

        // Conflict check: same lecturer, same day, overlapping time
        $conflict = TimetableEntry::where('lecturer_id', $request->lecturer_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                  ->orWhereBetween('end_time',  [$request->start_time, $request->end_time])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('start_time', '<=', $request->start_time)
                         ->where('end_time',   '>=', $request->end_time);
                  });
            })->exists();

        if ($conflict) {
            return back()->withErrors(['time' => 'This teacher already has a class at that time on that day.'])->withInput();
        }

        TimetableEntry::create($request->only([
            'lecturer_id','course_id','program_id',
            'day_of_week','start_time','end_time','room','notes',
        ]));

        return back()->with('success', 'Timetable entry added.');
    }

    public function destroy(TimetableEntry $timetable)
    {
        $timetable->delete();
        return back()->with('success', 'Entry removed.');
    }

    /** JSON endpoint for FullCalendar / AJAX grid refresh */
    public function json(Request $request)
    {
        $q = TimetableEntry::with(['lecturer','course','program']);
        if ($request->filled('program_id'))  $q->where('program_id',  $request->program_id);
        if ($request->filled('lecturer_id')) $q->where('lecturer_id', $request->lecturer_id);
        return response()->json($q->get());
    }

    /** AJAX: return courses for a given program */
    public function coursesByProgram(Request $request)
    {
        $courses = Course::where('program_id', $request->program_id)
            ->orderBy('name')
            ->get(['id','name','code']);
        return response()->json($courses);
    }
}
