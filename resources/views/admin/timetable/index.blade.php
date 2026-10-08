@extends('layouts.app')
@section('title','Timetable')
@section('subtitle', $date->isToday() ? "Today's Timetable — " . $date->format('l, F j') : $date->format('l, F j Y') . ' Timetable')

@push('head')
<style>
    .slot-card { border-radius:10px; padding:10px 12px; margin-bottom:7px; font-size:12px; position:relative; }
    .slot-card .course-name { font-weight:800; font-size:13px; line-height:1.3; }
    .slot-card .meta { font-size:11px; opacity:.65; margin-top:2px; }
    .day-header { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; }
</style>
@endpush

@section('content')
<div class="space-y-5">

{{-- Flash --}}
@if(session('success'))<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}</div>@endif
@if($errors->any())<div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3 text-sm text-red-800"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

{{-- Hero header --}}
<div class="rounded-3xl relative overflow-hidden p-6 lg:p-8 text-white"
     style="background:linear-gradient(135deg,#0d0a2e 0%,#2d2480 40%,#5647d6 75%,#a395f5 100%)">
    <div class="pointer-events-none absolute -top-16 -right-16 h-56 w-56 rounded-full bg-white/4 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-yellow-400/50 to-transparent"></div>
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">
                @if($date->isToday()) Today — @endif
                <span class="text-yellow-300">{{ $date->format('l, F j Y') }}</span>
            </h1>
            <p class="text-indigo-200 text-sm mt-1">
                {{ $selectedProgram?->name ?? 'All Programs' }} ·
                {{ $todayEntries->count() }} lesson{{ $todayEntries->count() !== 1 ? 's' : '' }} today
            </p>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            {{-- Date picker --}}
            <form method="GET" class="flex items-center gap-2">
                @if($selectedProgram)<input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">@endif
                <input type="date" name="date" value="{{ $date->toDateString() }}"
                    class="rounded-xl bg-white/15 border border-white/20 text-white text-sm font-semibold px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/30"
                    onchange="this.form.submit()">
                @if(!$date->isToday())
                <a href="{{ route('admin.timetable.index', array_filter(['program_id'=>$selectedProgram?->id])) }}"
                   class="inline-flex items-center gap-1 rounded-xl bg-white/15 border border-white/20 text-white text-xs font-bold px-3 py-2 hover:bg-white/25 transition-colors">
                    <i class="fas fa-calendar-day text-[10px]"></i> Today
                </a>
                @endif
            </form>
            <button onclick="document.getElementById('addModal').classList.remove('hidden');document.getElementById('addModal').classList.add('flex')"
                class="inline-flex items-center gap-1.5 rounded-xl bg-yellow-400 hover:bg-yellow-300 text-yellow-900 text-xs font-bold px-4 py-2 shadow transition-all active:scale-95">
                <i class="fas fa-plus text-[10px]"></i> Add Lesson
            </button>
        </div>
    </div>

    {{-- Week strip --}}
    <div class="relative z-10 flex items-center gap-2 mt-5 flex-wrap">
        @foreach(range(0,5) as $offset)
        @php $d = \Carbon\Carbon::today()->startOfWeek()->addDays($offset); $sel = $d->toDateString()===$date->toDateString(); @endphp
        <a href="{{ route('admin.timetable.index', array_filter(['program_id'=>$selectedProgram?->id,'date'=>$d->toDateString()])) }}"
           class="inline-flex flex-col items-center rounded-xl px-3 py-2 text-[10px] font-bold transition-colors
                  {{ $sel ? 'bg-white text-indigo-900' : ($d->isToday() ? 'bg-white/20 text-white' : 'text-white/60 hover:text-white hover:bg-white/10') }}">
            <span>{{ $d->format('D') }}</span>
            <span class="text-[9px] mt-0.5 {{ $sel ? 'text-indigo-600' : 'opacity-70' }}">{{ $d->format('j') }}</span>
            @php $cnt = $entries->where('day_of_week',(int)$d->format('N'))->count(); @endphp
            @if($cnt > 0)<span class="mt-0.5 inline-block rounded-full w-4 h-4 text-[8px] {{ $sel ? 'bg-indigo-600 text-white' : 'bg-white/25 text-white' }} flex items-center justify-center">{{ $cnt }}</span>@endif
        </a>
        @endforeach
    </div>
</div>

{{-- Filters --}}
<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Program</label>
            <select name="program_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="this.form.submit()">
                <option value="">All Programs</option>
                @foreach($programs as $p)<option value="{{ $p->id }}" {{ $selectedProgram?->id==$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Teacher</label>
            <select name="lecturer_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="this.form.submit()">
                <option value="">All Teachers</option>
                @foreach($lecturers as $l)<option value="{{ $l->id }}" {{ request('lecturer_id')==$l->id?'selected':'' }}>{{ $l->full_name }}</option>@endforeach
            </select>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- ── TODAY'S LESSONS (primary) ──────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-3">
        <h2 class="text-sm font-extrabold text-slate-800">
            <i class="fas fa-calendar-day text-indigo-400 mr-1.5"></i>
            {{ $date->isToday() ? "Today's Lessons" : $date->format('l') . "'s Lessons" }}
            @if($dayNum == 7)<span class="ml-2 text-xs font-semibold text-slate-400">(Sunday — Rest Day)</span>@endif
        </h2>

        @if($dayNum == 7 || $todayEntries->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-14 text-center">
            <i class="fas fa-calendar-check text-4xl text-slate-200 block mb-3"></i>
            <p class="text-sm font-semibold text-slate-500">
                {{ $dayNum == 7 ? 'No school on Sunday.' : 'No lessons scheduled for ' . $date->format('l') . '.' }}
            </p>
            <button onclick="document.getElementById('addModal').classList.remove('hidden');document.getElementById('addModal').classList.add('flex')"
                class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2 shadow transition-all">
                <i class="fas fa-plus text-[10px]"></i> Add a lesson for this day
            </button>
        </div>
        @else
        @php
        $palettes=[['from-indigo-500 to-violet-600','bg-indigo-50 text-indigo-900','#5647d6'],['from-emerald-500 to-teal-600','bg-emerald-50 text-emerald-900','#059669'],['from-amber-500 to-orange-500','bg-amber-50 text-amber-900','#d97706'],['from-pink-500 to-rose-600','bg-pink-50 text-pink-900','#db2777'],['from-blue-500 to-cyan-600','bg-blue-50 text-blue-900','#2563eb'],['from-violet-500 to-purple-600','bg-violet-50 text-violet-900','#7c3aed']];
        $now = \Carbon\Carbon::now();
        @endphp
        @foreach($todayEntries as $i => $entry)
        @php
            [$grad,$lightBg,$hex] = $palettes[$i % count($palettes)];
            $start = \Carbon\Carbon::parse($date->toDateString().' '.$entry->start_time);
            $end   = \Carbon\Carbon::parse($date->toDateString().' '.$entry->end_time);
            $isNow = $date->isToday() && $now->between($start,$end);
            $isPast= $date->isToday() && $now->gt($end);
            $mins  = $start->diffInMinutes($end);
        @endphp
        <div class="rounded-2xl border-2 bg-white overflow-hidden transition-all hover:shadow-md
                    {{ $isNow ? 'border-emerald-400 shadow-lg shadow-emerald-100' : ($isPast ? 'border-slate-200 opacity-65' : 'border-slate-200') }}">
            <div class="h-1 bg-gradient-to-r {{ $grad }}"></div>
            <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                {{-- Time block --}}
                <div class="shrink-0 text-center w-24">
                    <div class="inline-flex flex-col items-center justify-center rounded-2xl p-3 {{ $lightBg }} w-20">
                        <span class="text-base font-extrabold leading-none">{{ substr($entry->start_time,0,5) }}</span>
                        <span class="text-[10px] font-bold mt-1 opacity-60">{{ substr($entry->end_time,0,5) }}</span>
                        <span class="text-[9px] mt-1 font-semibold opacity-50">{{ $mins }}min</span>
                    </div>
                    @if($isNow)
                    <div class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-emerald-500 text-white text-[9px] font-extrabold px-2 py-0.5 uppercase">
                        <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Now
                    </div>
                    @elseif($isPast)
                    <p class="mt-1 text-[9px] text-slate-400 font-semibold uppercase tracking-wide">Done</p>
                    @endif
                </div>

                {{-- Class info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 leading-tight">{{ $entry->course->name }}</h3>
                            <p class="text-sm text-slate-500 font-semibold mt-0.5">{{ $entry->program->name }}</p>
                        </div>
                        <span class="rounded-full {{ $lightBg }} text-[11px] font-extrabold px-3 py-1 shrink-0">
                            {{ $entry->course->code ?? '' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-3 mt-2.5 text-xs text-slate-500 font-semibold">
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-chalkboard-teacher text-slate-400"></i>{{ $entry->lecturer?->full_name ?? '—' }}
                        </span>
                        @if($entry->room)
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-door-open text-slate-400"></i>{{ $entry->room }}
                        </span>
                        @endif
                        @if($entry->notes)
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-note-sticky text-slate-400"></i>{{ $entry->notes }}
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Delete --}}
                <form method="POST" action="{{ route('admin.timetable.destroy', $entry) }}" class="shrink-0"
                      onsubmit="return confirm('Remove this lesson from the timetable?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-50 hover:bg-red-100 text-red-400 hover:text-red-600 transition-colors">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
        @endif
    </div>

    {{-- ── WEEKLY OVERVIEW (sidebar) ──────────────────────────────────── --}}
    <div class="space-y-4">
        <h2 class="text-sm font-extrabold text-slate-800">
            <i class="fas fa-calendar-week text-indigo-400 mr-1.5"></i>This Week's Overview
        </h2>

        @foreach(\App\Models\TimetableEntry::$dayNames as $dayN => $dayName)
        @php
            $dayEntries = $grid[$dayN] ?? collect();
            $d = \Carbon\Carbon::today()->startOfWeek()->addDays($dayN - 1);
            $isSelected = $dayN == $dayNum;
        @endphp
        <div class="rounded-2xl border {{ $isSelected ? 'border-indigo-300 bg-indigo-50' : 'border-slate-200 bg-white' }} overflow-hidden">
            <a href="{{ route('admin.timetable.index', array_filter(['program_id'=>$selectedProgram?->id,'date'=>$d->toDateString()])) }}"
               class="flex items-center justify-between px-4 py-3 {{ $isSelected ? 'bg-indigo-600' : 'bg-slate-50 hover:bg-slate-100' }} transition-colors">
                <span class="text-sm font-extrabold {{ $isSelected ? 'text-white' : 'text-slate-700' }}">{{ $dayName }}</span>
                <span class="rounded-full text-[10px] font-extrabold px-2 py-0.5
                             {{ $isSelected ? 'bg-white text-indigo-700' : 'bg-indigo-100 text-indigo-700' }}">
                    {{ $dayEntries->count() }} lesson{{ $dayEntries->count() !== 1 ? 's' : '' }}
                </span>
            </a>
            @if($dayEntries->isNotEmpty())
            <div class="px-4 py-2 space-y-1.5">
                @foreach($dayEntries as $e)
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-400 font-mono shrink-0">{{ substr($e->start_time,0,5) }}</span>
                    <span class="flex-1 font-bold text-slate-700 truncate">{{ $e->course->name }}</span>
                    <span class="text-slate-400 shrink-0 truncate max-w-[80px]">{{ $e->lecturer?->full_name ? explode(' ',$e->lecturer->full_name)[0] : '—' }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

</div>

{{-- ══ ADD LESSON MODAL ═══════════════════════════════════════════════════ --}}
<div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-7 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-extrabold text-slate-900">
                <i class="fas fa-plus text-indigo-500 mr-2"></i>Add Timetable Entry
            </h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-700"><i class="fas fa-xmark text-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.timetable.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">

                {{-- Program --}}
                <div class="col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Program *</label>
                    <select name="program_id" id="modalProgram" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        onchange="loadCourses(this.value)">
                        <option value="">— Select program —</option>
                        @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ $selectedProgram?->id==$p->id?'selected':'' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Course (loaded via AJAX) --}}
                <div class="col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Course / Subject *</label>
                    <select name="course_id" id="modalCourse" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Select program first —</option>
                        @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}{{ $c->code ? ' ('.$c->code.')' : '' }}</option>
                        @endforeach
                    </select>
                    <p id="courseLoadingMsg" class="text-[11px] text-indigo-500 mt-1 hidden"><i class="fas fa-spinner fa-spin mr-1"></i>Loading courses…</p>
                </div>

                {{-- Teacher --}}
                <div class="col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Teacher *</label>
                    <select name="lecturer_id" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Select teacher —</option>
                        @foreach($lecturers as $l)<option value="{{ $l->id }}">{{ $l->full_name }}</option>@endforeach
                    </select>
                </div>

                {{-- Day --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Day *</label>
                    <select name="day_of_week" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\TimetableEntry::$dayNames as $d => $n)
                        <option value="{{ $d }}" {{ $dayNum==$d ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Room --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Room</label>
                    <input type="text" name="room" maxlength="80" placeholder="e.g. Room A1"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                {{-- Start time --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Start Time *</label>
                    <input type="time" name="start_time" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                {{-- End time --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">End Time *</label>
                    <input type="time" name="end_time" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                {{-- Notes --}}
                <div class="col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Notes</label>
                    <input type="text" name="notes" maxlength="255" placeholder="Optional"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="flex-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-sm font-bold py-3 shadow transition-all active:scale-95">
                    <i class="fas fa-plus mr-1"></i> Add Entry
                </button>
                <button type="button" onclick="closeModal()"
                    class="px-5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function closeModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.getElementById('addModal').classList.remove('flex');
}
document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

function loadCourses(programId) {
    const sel = document.getElementById('modalCourse');
    const msg = document.getElementById('courseLoadingMsg');
    sel.innerHTML = '<option value="">Loading…</option>';
    if (!programId) { sel.innerHTML = '<option value="">— Select program first —</option>'; return; }
    msg.classList.remove('hidden');
    fetch('{{ route('admin.timetable.courses') }}?program_id=' + programId)
        .then(r => r.json())
        .then(courses => {
            msg.classList.add('hidden');
            if (courses.length === 0) {
                sel.innerHTML = '<option value="">No courses for this program</option>';
                return;
            }
            sel.innerHTML = '<option value="">— Select course —</option>' +
                courses.map(c => `<option value="${c.id}">${c.name}${c.code ? ' ('+c.code+')' : ''}</option>`).join('');
        })
        .catch(() => {
            msg.classList.add('hidden');
            sel.innerHTML = '<option value="">Error loading courses</option>';
        });
}

// Auto-load courses if a program is already selected
document.addEventListener('DOMContentLoaded', function() {
    const pId = document.getElementById('modalProgram')?.value;
    if (pId) loadCourses(pId);
});
</script>
@endpush
