<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolEvent;
use Illuminate\Http\Request;

class SchoolEventController extends Controller
{
    public function index()
    {
        $events = SchoolEvent::orderByDesc('start_date')->paginate(20);
        return view('admin.calendar.index', compact('events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:200',
            'description'     => 'nullable|string',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'start_time'      => 'nullable|date_format:H:i',
            'end_time'        => 'nullable|date_format:H:i',
            'all_day'         => 'nullable|boolean',
            'event_type'      => 'required|in:holiday,exam,meeting,activity,term,other',
            'show_on_website' => 'nullable|boolean',
        ]);

        SchoolEvent::create([
            'title'           => $request->title,
            'description'     => $request->description,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'start_time'      => $request->start_time,
            'end_time'        => $request->end_time,
            'all_day'         => $request->boolean('all_day', true),
            'event_type'      => $request->event_type,
            'show_on_website' => $request->boolean('show_on_website'),
            'created_by'      => auth()->id(),
        ]);

        return back()->with('success', 'Event added to calendar.');
    }

    public function update(Request $request, SchoolEvent $event)
    {
        $request->validate([
            'title'      => 'required|string|max:200',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'event_type' => 'required|in:holiday,exam,meeting,activity,term,other',
        ]);

        $event->update($request->only([
            'title','description','start_date','end_date',
            'start_time','end_time','event_type','show_on_website','all_day',
        ]));

        return back()->with('success', 'Event updated.');
    }

    public function destroy(SchoolEvent $event)
    {
        $event->delete();
        return back()->with('success', 'Event removed.');
    }

    /** JSON feed for FullCalendar */
    public function feed(Request $request)
    {
        $events = SchoolEvent::query()
            ->when($request->filled('start'), fn($q) => $q->whereDate('start_date', '>=', $request->start))
            ->when($request->filled('end'),   fn($q) => $q->whereDate('start_date', '<=', $request->end))
            ->get();

        return response()->json($events->map->toCalendarArray()->values());
    }
}
