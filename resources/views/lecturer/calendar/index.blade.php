@extends('layouts.app')
@section('title','School Calendar')
@section('subtitle','Upcoming school events, holidays and term dates')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">
<style>
    .fc-toolbar-title { font-size:.95rem !important; font-weight:800 !important; color:#1e1b4b; }
    .fc-button { font-size:11px !important; font-weight:700 !important; border-radius:8px !important; }
    .fc-button-primary { background:#5647d6 !important; border-color:#5647d6 !important; }
    .fc-button-primary:hover { background:#4338ca !important; }
    .fc-day-today { background:rgba(86,71,214,0.05) !important; }
    .fc-event { cursor:pointer; border-radius:5px !important; font-size:11px !important; padding:2px 5px !important; }
</style>
@endpush

@section('content')
<div class="space-y-5">

{{-- Hero --}}
<div class="rounded-3xl overflow-hidden relative p-6 lg:p-8 text-white"
     style="background:linear-gradient(135deg,#064e3b 0%,#065f46 40%,#059669 75%,#34d399 100%)">
    <div class="relative z-10">
        <h1 class="text-2xl font-extrabold">School Calendar</h1>
        <p class="text-emerald-100 text-sm mt-1">View school events, holidays, exams and term dates</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-4 gap-5">

    {{-- Calendar --}}
    <div class="xl:col-span-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        {{-- Legend --}}
        <div class="flex flex-wrap gap-3 mb-4">
            @foreach(\App\Models\SchoolEvent::$typeColors as $type => $color)
            <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $color }}"></span>
                {{ ucfirst($type) }}
            </span>
            @endforeach
        </div>
        <div id="lecturer-calendar"></div>
    </div>

    {{-- Upcoming events --}}
    <div class="space-y-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-extrabold text-slate-800 mb-4">
                <i class="fas fa-calendar-days text-emerald-500 mr-1.5"></i>Upcoming
            </h3>
            @forelse($upcomingEvents as $ev)
            <div class="flex items-start gap-2.5 py-3 border-b border-slate-50 last:border-0">
                <span class="w-2.5 h-2.5 rounded-full mt-1 shrink-0" style="background:{{ $ev->color }}"></span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 leading-tight">{{ $ev->title }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        {{ $ev->start_date->format('M d') }}
                        @if($ev->end_date && $ev->end_date != $ev->start_date)
                            – {{ $ev->end_date->format('M d') }}
                        @endif
                    </p>
                    @if($ev->description)
                    <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $ev->description }}</p>
                    @endif
                    <span class="inline-block mt-1 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase"
                          style="background:{{ $ev->color }}22; color:{{ $ev->color }}">
                        {{ ucfirst($ev->event_type) }}
                    </span>
                </div>
            </div>
            @empty
            <div class="text-center py-6">
                <i class="fas fa-calendar-xmark text-3xl text-slate-200 block mb-2"></i>
                <p class="text-xs text-slate-400 font-semibold">No upcoming events.</p>
            </div>
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
    var el = document.getElementById('lecturer-calendar');
    if (!el) return;
    new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
        height: 520,
        events: '{{ route('admin.calendar.feed') }}',
        eventClick: function(info) {
            var desc = info.event.extendedProps.description ?? '';
            alert(info.event.title + (desc ? '\n\n' + desc : ''));
        },
        dayMaxEvents: 3,
    }).render();
});
</script>
@endpush
