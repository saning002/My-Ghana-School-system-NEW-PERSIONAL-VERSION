@extends('layouts.app')
@section('title','Class Teacher Assignments')
@section('subtitle','Assign one teacher to manage each class — they take daily register for all subjects')

@section('content')
<div class="space-y-5">

{{-- Flash --}}
@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3 text-sm font-semibold text-red-800 flex items-center gap-2">
    <i class="fas fa-circle-exclamation text-red-500"></i>{{ session('error') }}
</div>
@endif

{{-- Hero --}}
<div class="rounded-3xl relative overflow-hidden p-6 lg:p-8 text-white"
     style="background:linear-gradient(135deg,#0d0a2e 0%,#2d2480 40%,#5647d6 75%,#a395f5 100%)">
    <div class="pointer-events-none absolute -top-16 -right-16 h-56 w-56 rounded-full bg-white/4 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-yellow-400/50 to-transparent"></div>
    <div class="relative z-10">
        <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">Class Teacher Assignments</h1>
        <p class="text-indigo-200 text-sm mt-2 max-w-2xl leading-relaxed">
            A <strong class="text-white">Class Teacher</strong> is assigned to manage one class (program). They take daily register covering all subjects for that class.
            They can still be assigned as a <strong class="text-white">Subject Teacher</strong> in other classes too.
        </p>
        <div class="flex flex-wrap gap-3 mt-4">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-3.5 py-1 text-xs font-bold text-white/80">
                <i class="fas fa-users text-indigo-300 text-[10px]"></i>
                {{ $programs->count() }} classes total
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/20 border border-emerald-400/30 px-3.5 py-1 text-xs font-bold text-emerald-200">
                <i class="fas fa-check text-emerald-300 text-[10px]"></i>
                {{ $programs->filter(fn($p) => $p->classTeacherAssignment)->count() }} assigned
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-amber-500/20 border border-amber-400/30 px-3.5 py-1 text-xs font-bold text-amber-200">
                <i class="fas fa-exclamation text-amber-300 text-[10px]"></i>
                {{ $programs->filter(fn($p) => !$p->classTeacherAssignment)->count() }} unassigned
            </div>
        </div>
    </div>
</div>

{{-- Info box --}}
<div class="rounded-2xl bg-indigo-50 border border-indigo-100 px-5 py-4 flex items-start gap-3">
    <i class="fas fa-lightbulb text-indigo-400 mt-0.5 shrink-0"></i>
    <div class="text-xs text-indigo-700 leading-relaxed space-y-1">
        <p><strong class="text-indigo-900">How it works:</strong></p>
        <p>• The assigned class teacher logs in and sees a <strong>Class Register</strong> button on their dashboard.</p>
        <p>• They mark attendance once per day for their class — this covers all subjects for that day.</p>
        <p>• Subject teachers can still mark attendance for their own courses as usual.</p>
        <p>• A teacher can be a class teacher for one class AND a subject teacher in multiple other classes.</p>
    </div>
</div>

{{-- Programs grid --}}
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($programs as $program)
    @php $assignment = $program->classTeacherAssignment; @endphp
    <div class="rounded-2xl border {{ $assignment ? 'border-emerald-200 bg-emerald-50/50' : 'border-slate-200 bg-white' }} overflow-hidden shadow-sm">

        {{-- Card header --}}
        <div class="px-5 py-4 border-b {{ $assignment ? 'border-emerald-100 bg-emerald-50' : 'border-slate-100 bg-slate-50' }} flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl
                    {{ $assignment ? 'bg-emerald-600' : 'bg-slate-300' }} text-white text-xs font-extrabold shadow-sm">
                    {{ substr($program->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-extrabold {{ $assignment ? 'text-emerald-900' : 'text-slate-800' }}">{{ $program->name }}</p>
                    <p class="text-[11px] {{ $assignment ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $assignment ? 'Class teacher assigned' : 'No class teacher yet' }}
                    </p>
                </div>
            </div>
            @if($assignment)
            <span class="rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-2.5 py-1 flex items-center gap-1">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
            </span>
            @else
            <span class="rounded-full bg-slate-100 text-slate-500 text-[10px] font-semibold px-2.5 py-1">Unassigned</span>
            @endif
        </div>

        <div class="px-5 py-4 space-y-3">

            {{-- Current teacher --}}
            @if($assignment)
            <div class="flex items-center gap-3 rounded-xl bg-emerald-100/60 border border-emerald-200 px-4 py-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white text-xs font-extrabold uppercase shadow">
                    {{ strtoupper(substr($assignment->lecturer?->full_name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-emerald-900 truncate">{{ $assignment->lecturer?->full_name ?? '—' }}</p>
                    <p class="text-[11px] text-emerald-600">Class Teacher</p>
                </div>
                <form method="POST" action="{{ route('admin.class-teachers.remove') }}"
                      onsubmit="return confirm('Remove {{ addslashes($assignment->lecturer?->full_name ?? '') }} as class teacher for {{ addslashes($program->name) }}?')">
                    @csrf
                    <input type="hidden" name="program_id" value="{{ $program->id }}">
                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-100 hover:bg-red-200 text-red-500 hover:text-red-700 transition-colors">
                        <i class="fas fa-user-minus text-xs"></i>
                    </button>
                </form>
            </div>
            @endif

            {{-- Assign form --}}
            <form method="POST" action="{{ route('admin.class-teachers.assign') }}">
                @csrf
                <input type="hidden" name="program_id" value="{{ $program->id }}">
                <div class="flex gap-2">
                    <select name="lecturer_id" required
                        class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 min-w-0">
                        <option value="">— Select teacher —</option>
                        @foreach($lecturers as $l)
                        <option value="{{ $l->id }}"
                            {{ $assignment?->lecturer_id == $l->id ? 'selected' : '' }}>
                            {{ $l->full_name }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-3 py-2.5 shadow transition-all active:scale-95">
                        <i class="fas fa-user-check text-[10px]"></i>
                        {{ $assignment ? 'Change' : 'Assign' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>

</div>
@endsection
