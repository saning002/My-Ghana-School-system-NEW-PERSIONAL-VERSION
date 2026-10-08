@extends('staff-portal.layout')
@section('title','School Calendar')
@section('subtitle','Upcoming school events and holidays')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">
@endpush

@section('content')
<div class="space-y-5">

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2 card p-5">
        <div class="flex flex-wrap gap-3 mb-4">
            @foreach(\App\Models\SchoolEvent::$typeColors as $type => $color)
            <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                <span class="w-2.5 h-2.5 rounded-full inline-block" style="background:{{ $color }}"></span>
                {{ ucfirst($type) }}
            </span>
            @endforeach
        </div>
        <div id="staff-calendar" style="height:520px"></div>
    </div>
    <div class="card p-4 self-start">
        <h3 class="text-sm font-extrabold text-slate-800 mb-4"><i class="fas fa-calendar-days text-emerald-500 mr-1.5"></i>Upcoming Events</h3>
        @forelse($upcomingEvents as $ev)
        <div class="flex items-start gap-2.5 py-3 border-b border-slate-50 last:border-0">
            <span class="w-2.5 h-2.5 rounded-full mt-1 shrink-0" style="background:{{ $ev->color }}"></span>
            <div>
                <p class="text-sm font-bold text-slate-800 leading-tight">{{ $ev->title }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $ev->start_date->format('M d, Y') }}</p>
                <span class="inline-block mt-0.5 text-[9px] font-bold uppercase rounded-full px-1.5 py-0.5" style="background:{{ $ev->color }}18;color:{{ $ev->color }}">{{ ucfirst($ev->event_type) }}</span>
            </div>
        </div>
        @empty
        <p class="text-xs text-slate-400">No upcoming events.</p>
        @endforelse
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('staff-calendar');
    if (!el) return;
    new FullCalendar.Calendar(el, {
        initialView:'dayGridMonth',
        headerToolbar:{left:'prev,next today',center:'title',right:'dayGridMonth,listWeek'},
        height:520,
        events:'{{ route('admin.calendar.feed') }}',
        eventClick:function(info){ alert(info.event.title+(info.event.extendedProps.description?'\n\n'+info.event.extendedProps.description:'')); },
        dayMaxEvents:3,
    }).render();
});
</script>
@endpush
