@extends('staff-portal.layout')
@section('title','Timetable')
@section('subtitle', $date->isToday() ? "Today's Schedule" : $date->format('l, M d Y'))

@section('content')
<div class="space-y-5">

<div class="card p-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Program</label>
            <select name="program_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="this.form.submit()">
                <option value="">— Select —</option>
                @foreach($programs as $p)<option value="{{ $p->id }}" {{ $selectedProgram?->id==$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Date</label>
            <input type="date" name="date" value="{{ $date->toDateString() }}" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" onchange="this.form.submit()">
        </div>
        @if($selectedProgram)<input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">@endif
    </form>
</div>

@if($todayEntries->isNotEmpty())
<div class="space-y-3">
    <h3 class="text-sm font-extrabold text-slate-800"><i class="fas fa-calendar-day text-indigo-400 mr-1.5"></i>{{ $date->format('l') }}'s Classes</h3>
    @foreach($todayEntries as $i => $entry)
    @php $colors=[['bg-indigo-50 text-indigo-800'],['bg-emerald-50 text-emerald-800'],['bg-amber-50 text-amber-800'],['bg-pink-50 text-pink-800'],['bg-blue-50 text-blue-800']]; [$cls] = $colors[$i%count($colors)]; @endphp
    <div class="rounded-2xl border border-slate-200 bg-white p-4 flex items-center gap-4 hover:shadow-sm transition-all">
        <div class="shrink-0 rounded-xl {{ $cls }} px-4 py-3 text-center min-w-[80px]">
            <p class="text-base font-extrabold leading-none">{{ substr($entry->start_time,0,5) }}</p>
            <p class="text-[10px] opacity-60 mt-1">{{ substr($entry->end_time,0,5) }}</p>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-extrabold text-slate-900">{{ $entry->course->name }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ $entry->program->name }}</p>
            <p class="text-xs text-slate-500 mt-0.5"><i class="fas fa-chalkboard-teacher mr-1 text-slate-400"></i>{{ $entry->lecturer?->full_name ?? '—' }}@if($entry->room) · <i class="fas fa-door-open mr-1 text-slate-400"></i>{{ $entry->room }}@endif</p>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
    <i class="fas fa-calendar-check text-4xl text-slate-200 block mb-3"></i>
    <p class="text-sm font-semibold text-slate-400">No classes scheduled for {{ $date->format('l') }}.</p>
</div>
@endif

</div>
@endsection
