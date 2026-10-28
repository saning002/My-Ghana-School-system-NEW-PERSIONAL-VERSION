extends('layouts.app')
@section('title','Class Teacher Assignments')
@section('subtitle','Assign one teacher to manage each class – they take daily register for all subjects')

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
    <i class="fas fa-circle-exclamation text-red-500"></i>{{ session('error'?'border-emerald-200 bg-emerald-50/50' : 'border-slate-200 bg-white' }} overflow-hidden shadow-sm">

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
                    <p class="text-sm font-bold text-emerald-900 truncate">{{ $assignment->lecturer?->full_name ?? '–' }}</p>
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
                        <option value="">– Select teacher –</option>
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
