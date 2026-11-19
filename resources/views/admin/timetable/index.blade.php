@extends('layouts.app')
@section('title','Timetable')
@section('subtitle','Weekly lesson schedule at a glance')

@section('content')
@php
    $dayLabels = \App\Models\TimetableEntry::$dayNames;
    $today     = (int)\Carbon\Carbon::today()->format('N');
@endphp

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<div class="page-hero-banner">
    <div class="hero-left">
        <h1><i class="fas fa-calendar-alt mr-2" style="color:var(--primary)"></i>Timetable</h1>
        <p>Weekly lesson schedule — all programmes, all teachers</p>
    </div>
    <div class="hero-actions">
        <button onclick="openAdd()"
            class="btn-primary-action">
            <i class="fas fa-plus"></i> Add Lesson
        </button>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold mb-5">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif
@if($errors->has('time'))
<div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold mb-5">
    <i class="fas fa-exclamation-circle text-red-500"></i> {{ $errors->first('time') }}
</div>
@endif

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<form method="GET" class="flex flex-wrap gap-3 items-end mb-5">
    <div>
        <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Programme</label>
        <select name="program_id" onchange="this.form.submit()"
            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
            <option value="">All Programmes</option>
            @foreach($programs as $p)
            <option value="{{ $p->id }}" {{ $selectedProgram?->id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Teacher</label>
        <select name="lecturer_id" onchange="this.form.submit()"
            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
            <option value="">All Teachers</option>
            @foreach($lecturers as $l)
            <option value="{{ $l->id }}" {{ request('lecturer_id') == $l->id ? 'selected' : '' }}>{{ $l->full_name }}</option>
            @endforeach
        </select>
    </div>
    @if($selectedProgram)<input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">@endif
</form>

{{-- ── Weekly grid ─────────────────────────────────────────────────────── --}}
@if($entries->isEmpty())
<div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-16 text-center">
    <i class="fas fa-calendar-alt text-5xl text-slate-200 block mb-4"></i>
    <p class="text-sm font-bold text-slate-500 mb-2">No lessons scheduled yet</p>
    <p class="text-xs text-slate-400 mb-5">{{ $selectedProgram ? 'No entries for ' . $selectedProgram->name : 'Select a programme or add the first lesson.' }}</p>
    <button onclick="openAdd()"
        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold px-5 py-2.5 shadow transition-all">
        <i class="fas fa-plus text-xs"></i> Add First Lesson
    </button>
</div>
@else
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm border-collapse" style="min-width:700px">
        <thead>
            <tr class="border-b border-slate-200">
                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500 bg-slate-50 w-28">Time</th>
                @foreach($dayLabels as $dayN => $dayName)
                <th class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wide border-l border-slate-100
                    {{ $dayN == $today ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-50 text-slate-500' }}">
                    {{ substr($dayName, 0, 3) }}
                    @if($dayN == $today)
                    <span class="block text-[9px] font-semibold text-indigo-400 uppercase">Today</span>
                    @endif
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @php
            {{-- Collect all unique time slots across the week --}}
            $timeSlots = $entries->map(fn($e) => substr($e->start_time, 0, 5))->unique()->sort()->values();
            $colors = [
                0 => ['border-l-indigo-500','bg-indigo-50','text-indigo-800'],
                1 => ['border-l-emerald-500','bg-emerald-50','text-emerald-800'],
                2 => ['border-l-amber-500','bg-amber-50','text-amber-800'],
                3 => ['border-l-rose-500','bg-rose-50','text-rose-800'],
                4 => ['border-l-blue-500','bg-blue-50','text-blue-800'],
                5 => ['border-l-violet-500','bg-violet-50','text-violet-800'],
            ];
            $courseColorMap = [];
            $colorIdx = 0;
            foreach ($entries as $e) {
                if (!isset($courseColorMap[$e->course_id])) {
                    $courseColorMap[$e->course_id] = $colors[$colorIdx % count($colors)];
                    $colorIdx++;
                }
            }
        @endphp
        @forelse($timeSlots as $slot)
        @php
            $slotEntries = $entries->filter(fn($e) => substr($e->start_time, 0, 5) === $slot);
        @endphp
        <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
            <td class="px-4 py-3 font-mono font-bold text-slate-700 text-xs whitespace-nowrap align-top">
                {{ $slot }}
            </td>
            @foreach($dayLabels as $dayN => $dayName)
            @php $daySlotEntries = $slotEntries->where('day_of_week', $dayN)->values(); @endphp
            <td class="px-2 py-2 border-l border-slate-100 align-top {{ $dayN == $today ? 'bg-indigo-50/30' : '' }}" style="min-width:120px">
                @forelse($daySlotEntries as $entry)
                @php [$borderColor, $bgColor, $textColor] = $courseColorMap[$entry->course_id] ?? $colors[0]; @endphp
                <div class="rounded-xl border-l-4 {{ $borderColor }} {{ $bgColor }} px-2.5 py-2 mb-1.5 relative group">
                    <p class="font-extrabold text-xs {{ $textColor }} leading-tight truncate">{{ $entry->course->name }}</p>
                    <p class="text-[10px] text-slate-500 font-semibold mt-0.5 truncate">
                        <i class="fas fa-chalkboard-user text-slate-400 mr-0.5"></i>{{ explode(' ', $entry->lecturer?->full_name ?? '—')[0] }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5">
                        {{ substr($entry->start_time,0,5) }}–{{ substr($entry->end_time,0,5) }}
                        @if($entry->room) · <i class="fas fa-door-open"></i> {{ $entry->room }}@endif
                    </p>
                    {{-- Delete button on hover --}}
                    <form method="POST" action="{{ route('admin.timetable.destroy', $entry) }}"
                          onsubmit="return confirm('Remove this lesson?')"
                          class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-5 h-5 rounded-md bg-red-100 hover:bg-red-500 text-red-500 hover:text-white flex items-center justify-center transition-colors">
                            <i class="fas fa-times text-[9px]"></i>
                        </button>
                    </form>
                </div>
                @empty
                <span class="text-slate-200 text-xs">—</span>
                @endforelse
            </td>
            @endforeach
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-8 text-slate-400 text-sm">No entries match your filters.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- ── Summary strip ────────────────────────────────────────────────────── --}}
<div class="mt-4 flex flex-wrap gap-3 text-xs text-slate-500">
    <span><strong class="text-slate-700">{{ $entries->count() }}</strong> total lessons</span>
    <span>·</span>
    <span><strong class="text-slate-700">{{ $entries->pluck('lecturer_id')->unique()->count() }}</strong> teacher{{ $entries->pluck('lecturer_id')->unique()->count() !== 1 ? 's' : '' }}</span>
    <span>·</span>
    <span><strong class="text-slate-700">{{ $entries->pluck('course_id')->unique()->count() }}</strong> course{{ $entries->pluck('course_id')->unique()->count() !== 1 ? 's' : '' }}</span>
</div>
@endif

{{-- ── Add Lesson Modal ─────────────────────────────────────────────────── --}}
<div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-7 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-extrabold text-slate-900">
                <i class="fas fa-plus text-indigo-500 mr-2"></i>Add Timetable Entry
            </h3>
            <button onclick="closeAdd()" class="text-slate-400 hover:text-slate-700">
                <i class="fas fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.timetable.store') }}" class="space-y-3">
            @csrf

            {{-- Programme --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Programme <span class="text-red-400">*</span></label>
                <select name="program_id" id="modalProgram" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    onchange="loadCourses(this.value)">
                    <option value="">— Select programme —</option>
                    @foreach($programs as $p)
                    <option value="{{ $p->id }}" {{ $selectedProgram?->id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Course --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Subject / Course <span class="text-red-400">*</span></label>
                <select name="course_id" id="modalCourse" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— Select programme first —</option>
                    @foreach($courses as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}{{ $c->code ? ' ('.$c->code.')' : '' }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Teacher --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Teacher <span class="text-red-400">*</span></label>
                <select name="lecturer_id" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— Select teacher —</option>
                    @foreach($lecturers as $l)
                    <option value="{{ $l->id }}">{{ $l->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                {{-- Day --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Day <span class="text-red-400">*</span></label>
                    <select name="day_of_week" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach($dayLabels as $d => $n)
                        <option value="{{ $d }}" {{ $today == $d ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Room --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Room</label>
                    <input type="text" name="room" maxlength="80" placeholder="e.g. Room A1"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                {{-- Start --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Start Time <span class="text-red-400">*</span></label>
                    <input type="time" name="start_time" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                {{-- End --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">End Time <span class="text-red-400">*</span></label>
                    <input type="time" name="end_time" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Notes</label>
                <input type="text" name="notes" maxlength="255" placeholder="Optional"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="flex-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-sm font-bold py-3 shadow transition-all active:scale-95">
                    <i class="fas fa-plus mr-1"></i> Add Entry
                </button>
                <button type="button" onclick="closeAdd()"
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
function openAdd()  { var m=document.getElementById('addModal'); m.classList.remove('hidden'); m.classList.add('flex'); }
function closeAdd() { var m=document.getElementById('addModal'); m.classList.add('hidden'); m.classList.remove('flex'); }

document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) closeAdd();
});

function loadCourses(programId) {
    var sel = document.getElementById('modalCourse');
    sel.innerHTML = '<option value="">Loading…</option>';
    if (!programId) { sel.innerHTML = '<option value="">— Select programme first —</option>'; return; }
    fetch('{{ route('admin.timetable.courses') }}?program_id=' + programId)
        .then(r => r.json())
        .then(courses => {
            if (courses.length === 0) {
                sel.innerHTML = '<option value="">No courses for this programme</option>';
                return;
            }
            sel.innerHTML = '<option value="">— Select course —</option>' +
                courses.map(c => `<option value="${c.id}">${c.name}${c.code ? ' ('+c.code+')' : ''}</option>`).join('');
        })
        .catch(() => { sel.innerHTML = '<option value="">Error loading courses</option>'; });
}

// Auto-load courses if programme already selected
document.addEventListener('DOMContentLoaded', function() {
    var pId = document.getElementById('modalProgram')?.value;
    if (pId) loadCourses(pId);

    // Open modal automatically if there were validation errors
    @if($errors->any())
    openAdd();
    @endif
});
</script>
@endpush
