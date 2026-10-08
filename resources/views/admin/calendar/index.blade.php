@extends('layouts.app')
@section('title','School Calendar')
@section('subtitle','Manage school events, holidays and term dates')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">
<style>
    #school-calendar { max-width: 100%; }
    .fc-event { cursor: pointer; border-radius: 6px !important; padding: 2px 5px !important; font-size: 11px !important; }
    .fc-toolbar-title { font-size: 1rem !important; font-weight: 800 !important; color: #1e1b4b; }
    .fc-button { font-size: 12px !important; font-weight: 700 !important; border-radius: 8px !important; }
    .fc-button-primary { background: #5647d6 !important; border-color: #5647d6 !important; }
    .fc-button-primary:hover { background: #4338ca !important; }
    .fc-day-today { background: rgba(86,71,214,0.05) !important; }
    .legend-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
</style>
@endpush

@section('content')
<div class="space-y-5">

{{-- Flash --}}
@if(session('success'))<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}</div>@endif

<div class="grid grid-cols-1 xl:grid-cols-4 gap-5">

    {{-- Calendar --}}
    <div class="xl:col-span-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        {{-- Legend --}}
        <div class="flex flex-wrap gap-3 mb-4">
            @foreach(\App\Models\SchoolEvent::$typeColors as $type => $color)
            <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                <span class="legend-dot" style="background:{{ $color }}"></span>
                {{ ucfirst($type) }}
            </span>
            @endforeach
        </div>
        <div id="school-calendar"></div>
    </div>

    {{-- Sidebar: Add event + upcoming list --}}
    <div class="space-y-4">
        {{-- Add form --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-extrabold text-slate-800 mb-4"><i class="fas fa-plus text-indigo-500 mr-1.5"></i>Add Event</h3>
            <form method="POST" action="{{ route('admin.calendar.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Title *</label>
                    <input type="text" name="title" required maxlength="200" placeholder="Event title"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Type *</label>
                    <select name="event_type" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(['holiday'=>'Holiday','exam'=>'Exam','meeting'=>'Meeting','activity'=>'Activity','term'=>'Term Date','other'=>'Other'] as $v => $l)
                        <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Start *</label>
                        <input type="date" name="start_date" required value="{{ today()->toDateString() }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">End</label>
                        <input type="date" name="end_date"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                    <input type="checkbox" name="all_day" value="1" checked class="rounded">
                    All day event
                </label>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Optional details..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
                </div>
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                    <input type="checkbox" name="show_on_website" value="1" class="rounded">
                    Show on public website
                </label>
                <button type="submit" class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold py-2.5 transition-colors active:scale-95">
                    Save Event
                </button>
            </form>
        </div>

        {{-- Upcoming events list --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="text-sm font-extrabold text-slate-800 mb-3">Upcoming Events</h3>
            @php $upcoming = \App\Models\SchoolEvent::where('start_date','>=',today())->orderBy('start_date')->take(8)->get(); @endphp
            @forelse($upcoming as $ev)
            <div class="flex items-start gap-2.5 py-2.5 border-b border-slate-50 last:border-0 group">
                <span class="w-2.5 h-2.5 rounded-full mt-1 shrink-0" style="background:{{ $ev->color }}"></span>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ $ev->title }}</p>
                    <p class="text-[10px] text-slate-400">{{ $ev->start_date->format('M d, Y') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.calendar.destroy', $ev) }}" class="opacity-0 group-hover:opacity-100 transition-opacity">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-400 hover:text-red-600 text-[10px]"><i class="fas fa-trash"></i></button>
                </form>
            </div>
            @empty
            <p class="text-xs text-slate-400">No upcoming events.</p>
            @endforelse
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calEl = document.getElementById('school-calendar');
    if (!calEl) return;

    var cal = new FullCalendar.Calendar(calEl, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
        height: 560,
        events: '{{ route('admin.calendar.feed') }}',
        eventClick: function(info) {
            var ev = info.event;
            var desc = ev.extendedProps.description ?? '';
            alert(ev.title + (desc ? '\n\n' + desc : ''));
        },
        eventDisplay: 'block',
        dayMaxEvents: 3,
    });

    cal.render();
});
</script>
@endpush
