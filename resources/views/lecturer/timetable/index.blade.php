@extends('layouts.app')
@section('title','My Timetable')
@section('subtitle', $date->isToday() ? "Today's Classes" : $date->format('l, M d Y'))

@section('content')
<div class="space-y-5">

{{-- ── Date Nav Hero ───────────────────────────────────────────────── --}}
<div class="rounded-3xl relative overflow-hidden p-6 lg:p-8 text-white"
     style="background:linear-gradient(135deg,#0d0a2e 0%,#2d2480 40%,#5647d6 75%,#a395f5 100%)">
    <div class="pointer-events-none absolute -top-16 -right-16 h-56 w-56 rounded-full bg-white/4 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-yellow-400/50 to-transparent"></div>

    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-indigo-300 mb-1">Teaching Schedule</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">
                @if($date->isToday())
                    Today — <span class="text-yellow-300">{{ $date->format('l, F j') }}</span>
                @else
                    <span class="text-yellow-300">{{ $date->format('l, F j Y') }}</span>
                @endif
            </h1>
            <p class="text-indigo-200 text-sm mt-1">
                {{ $todaysEntries->count() }} class{{ $todaysEntries->count() !== 1 ? 'es' : '' }} scheduled
                @if($dayNum == 7) &nbsp;·&nbsp; <span class="text-amber-300 font-semibold">Sunday — No classes</span> @endif
            </p>
        </div>

        {{-- Date picker --}}
        <form method="GET" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date->toDateString() }}"
                class="rounded-xl bg-white/15 border border-white/20 text-white text-sm font-semibold px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-white/30"
                onchange="this.form.submit()">
            @if(!$date->isToday())
            <a href="{{ route('lecturer.timetable.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 border border-white/20 text-white text-xs font-bold px-3 py-2.5 hover:bg-white/25 transition-colors">
                <i class="fas fa-calendar-day text-[10px]"></i> Today
            </a>
            @endif
        </form>
    </div>

    {{-- Prev / Next day --}}
    <div class="relative z-10 flex items-center gap-2 mt-5">
        <a href="{{ route('lecturer.timetable.index', ['date'=>$date->copy()->subDay()->toDateString()]) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-white text-xs font-bold px-3 py-2 transition-colors">
            <i class="fas fa-chevron-left text-[10px]"></i> {{ $date->copy()->subDay()->format('D') }}
        </a>
        @foreach(range(0,6) as $offset)
        @php $d = \Carbon\Carbon::today()->startOfWeek()->addDays($offset); $isSelected = $d->toDateString() === $date->toDateString(); $isToday = $d->isToday(); @endphp
        <a href="{{ route('lecturer.timetable.index', ['date'=>$d->toDateString()]) }}"
           class="hidden sm:inline-flex flex-col items-center rounded-xl px-3 py-1.5 text-[10px] font-bold transition-colors {{ $isSelected ? 'bg-white text-indigo-900' : ($isToday ? 'bg-white/20 text-white' : 'text-white/60 hover:text-white hover:bg-white/10') }}">
            <span>{{ $d->format('D') }}</span>
            <span class="text-[9px] mt-0.5 {{ $isSelected ? 'text-indigo-600' : 'opacity-70' }}">{{ $d->format('j') }}</span>
        </a>
        @endforeach
        <a href="{{ route('lecturer.timetable.index', ['date'=>$date->copy()->addDay()->toDateString()]) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-white text-xs font-bold px-3 py-2 transition-colors">
            {{ $date->copy()->addDay()->format('D') }} <i class="fas fa-chevron-right text-[10px]"></i>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- ── Today's Classes ──────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-3">

        @if($dayNum == 7)
        {{-- Sunday --}}
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-14 text-center">
            <i class="fas fa-couch text-4xl text-slate-200 block mb-3"></i>
            <p class="text-sm font-semibold text-slate-500">No school on Sunday.</p>
        </div>

        @elseif($todaysEntries->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-14 text-center">
            <i class="fas fa-calendar-check text-4xl text-slate-200 block mb-3"></i>
            <p class="text-sm font-semibold text-slate-500">No classes scheduled for this day.</p>
            <p class="text-xs text-slate-400 mt-1">Check another day using the date picker above.</p>
        </div>

        @else
        @php
        $palettes = [
            ['from-indigo-500 to-violet-600',  'bg-indigo-50',  'text-indigo-800',  '#5647d6'],
            ['from-emerald-500 to-teal-600',   'bg-emerald-50', 'text-emerald-800', '#059669'],
            ['from-amber-500 to-orange-500',   'bg-amber-50',   'text-amber-800',   '#d97706'],
            ['from-pink-500 to-rose-600',      'bg-pink-50',    'text-pink-800',    '#db2777'],
            ['from-blue-500 to-cyan-600',      'bg-blue-50',    'text-blue-800',    '#2563eb'],
            ['from-violet-500 to-purple-600',  'bg-violet-50',  'text-violet-800',  '#7c3aed'],
        ];
        @endphp

        @php $now = \Carbon\Carbon::now(); @endphp

        @foreach($todaysEntries as $i => $entry)
        @php
            [$grad, $lightBg, $textColor, $hexColor] = $palettes[$i % count($palettes)];
            $startCarbon = \Carbon\Carbon::parse($date->toDateString() . ' ' . $entry->start_time);
            $endCarbon   = \Carbon\Carbon::parse($date->toDateString() . ' ' . $entry->end_time);
            $isNow       = $date->isToday() && $now->between($startCarbon, $endCarbon);
            $isPast      = $date->isToday() && $now->gt($endCarbon);
            $duration    = $startCarbon->diffInMinutes($endCarbon);
        @endphp

        <div class="rounded-2xl border-2 {{ $isNow ? 'border-emerald-400 shadow-lg shadow-emerald-100' : ($isPast ? 'border-slate-200 opacity-60' : 'border-slate-200') }} bg-white overflow-hidden transition-all hover:shadow-md">
            {{-- Colour strip --}}
            <div class="h-1 bg-gradient-to-r {{ $grad }}"></div>

            <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                {{-- Time block --}}
                <div class="shrink-0 text-center sm:w-24">
                    <div class="inline-flex flex-col items-center justify-center rounded-2xl p-3 {{ $lightBg }} {{ $textColor }} w-20">
                        <span class="text-lg font-extrabold leading-none">{{ substr($entry->start_time,0,5) }}</span>
                        <span class="text-[10px] font-bold mt-1 opacity-60">{{ substr($entry->end_time,0,5) }}</span>
                        <span class="text-[9px] mt-1 font-semibold opacity-50">{{ $duration }}min</span>
                    </div>
                    @if($isNow)
                    <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-emerald-500 text-white text-[9px] font-extrabold px-2 py-0.5 uppercase tracking-wider">
                        <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Now
                    </div>
                    @elseif($isPast)
                    <p class="mt-1.5 text-[9px] text-slate-400 font-semibold uppercase tracking-wider">Done</p>
                    @endif
                </div>

                {{-- Class info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 leading-tight">{{ $entry->course->name }}</h3>
                            <p class="text-sm text-slate-500 mt-0.5 font-semibold">{{ $entry->program->name }}</p>
                        </div>
                        <span class="rounded-full {{ $lightBg }} {{ $textColor }} text-[11px] font-extrabold px-3 py-1 shrink-0">
                            {{ \App\Models\TimetableEntry::$dayNames[$entry->day_of_week] }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-3 mt-3">
                        @if($entry->room)
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                            <i class="fas fa-door-open text-slate-400"></i> {{ $entry->room }}
                        </span>
                        @endif
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                            <i class="fas fa-clock text-slate-400"></i>
                            {{ substr($entry->start_time,0,5) }} – {{ substr($entry->end_time,0,5) }}
                        </span>
                        @if($entry->notes)
                        <span class="flex items-center gap-1.5 text-xs text-slate-500">
                            <i class="fas fa-note-sticky text-slate-400"></i> {{ $entry->notes }}
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Quick action --}}
                @if(\App\Models\Setting::lecturerCan('attendance'))
                <a href="{{ route('lecturer.attendance.create') }}?assignment={{ $entry->course_id }}|{{ $entry->program_id }}"
                   class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-br {{ $grad }} text-white text-xs font-bold px-3 py-2.5 shadow hover:opacity-90 transition-opacity active:scale-95">
                    <i class="fas fa-clipboard-check text-[10px]"></i> Take Attendance
                </a>
                @endif
            </div>
        </div>
        @endforeach
        @endif
    </div>

    {{-- ── Sidebar: This Week + Events ─────────────────────────────── --}}
    <div class="space-y-4">

        {{-- This week mini-strip --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
            <h3 class="text-sm font-extrabold text-slate-800 mb-3">
                <i class="fas fa-calendar-week text-indigo-400 mr-1.5"></i>This Week
            </h3>
            @foreach(\App\Models\TimetableEntry::$dayNames as $dayNum2 => $dayName)
            @php
                $dayClasses = $weekEntries->get($dayNum2, collect());
                $isSelectedDay = $dayNum2 == $dayNum;
                $d = \Carbon\Carbon::today()->startOfWeek()->addDays($dayNum2 - 1);
            @endphp
            <a href="{{ route('lecturer.timetable.index', ['date'=>$d->toDateString()]) }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 mb-1 transition-colors {{ $isSelectedDay ? 'bg-indigo-50 border border-indigo-200' : 'hover:bg-slate-50' }}">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[10px] font-extrabold uppercase
                    {{ $isSelectedDay ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                    {{ substr($dayName,0,2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold {{ $isSelectedDay ? 'text-indigo-800' : 'text-slate-700' }}">{{ $dayName }}</p>
                    @if($dayClasses->isEmpty())
                    <p class="text-[10px] text-slate-400">No classes</p>
                    @else
                    <p class="text-[10px] text-slate-500 truncate">
                        {{ $dayClasses->map(fn($e)=>$e->course->name)->implode(', ') }}
                    </p>
                    @endif
                </div>
                <span class="shrink-0 text-xs font-bold {{ $isSelectedDay ? 'text-indigo-700' : 'text-slate-400' }}">
                    {{ $dayClasses->count() }}
                </span>
            </a>
            @endforeach
        </div>

        {{-- Upcoming school events --}}
        @if($upcomingEvents->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
            <h3 class="text-sm font-extrabold text-slate-800 mb-3">
                <i class="fas fa-calendar-days text-emerald-500 mr-1.5"></i>Upcoming Events
            </h3>
            @foreach($upcomingEvents as $ev)
            <div class="flex items-start gap-2.5 py-2.5 border-b border-slate-50 last:border-0">
                <span class="w-2.5 h-2.5 rounded-full mt-1 shrink-0" style="background:{{ $ev->color }}"></span>
                <div>
                    <p class="text-xs font-bold text-slate-800 leading-tight">{{ $ev->title }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $ev->start_date->format('M d, Y') }}</p>
                    <span class="inline-block mt-0.5 text-[9px] font-bold uppercase rounded-full px-1.5 py-0.5"
                          style="background:{{ $ev->color }}15; color:{{ $ev->color }}">{{ ucfirst($ev->event_type) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
</div>
@endsection
