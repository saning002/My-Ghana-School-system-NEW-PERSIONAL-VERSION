<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\TimetableEntry;
use App\Models\SchoolEvent;
use Carbon\Carbon;

class TimetableController extends Controller
{
    /**
     * Daily timetable — shows TODAY's classes for this teacher,
     * with a date picker to jump to any day.
     */
    public function index()
    {
        $lecturer = auth()->user();
        $date     = request('date') ? Carbon::parse(request('date')) : Carbon::today();
        $dayNum   = (int) $date->format('N'); // 1=Mon … 6=Sat, 7=Sun

        // All entries for today's day-of-week
        $todaysEntries = TimetableEntry::with(['course','program'])
            ->where('lecturer_id', $lecturer->id)
            ->where('day_of_week', $dayNum)
            ->orderBy('start_time')
            ->get();

        // Next 5 upcoming events
        $upcomingEvents = SchoolEvent::where('start_date', '>=', today())
            ->orderBy('start_date')->take(5)->get();

        // This week's full schedule (for the week overview strip)
        $weekEntries = TimetableEntry::with(['course','program'])
            ->where('lecturer_id', $lecturer->id)
            ->whereBetween('day_of_week', [1, 6])
            ->orderBy('day_of_week')->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return view('lecturer.timetable.index', compact(
            'todaysEntries', 'date', 'dayNum', 'upcomingEvents', 'weekEntries', 'lecturer'
        ));
    }

    public function calendar()
    {
        $upcomingEvents = SchoolEvent::where('start_date', '>=', today())
            ->orderBy('start_date')->take(10)->get();

        return view('lecturer.calendar.index', compact('upcomingEvents'));
    }
}
