@extends('layouts.app')
@section('title','Student Reports')
@section('subtitle','Manage conduct, attitude and personality ratings per student')

@section('content')
<div class="space-y-5">

@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif

{{-- Filters --}}
<div class="card p-5">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program *</label>
            <select name="program_id" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <option value="">— Select Program —</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Attempt</label>
            <select name="attempt" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                @foreach(range(1,5) as $a)
                <option value="{{ $a }}" {{ $selectedAttempt==$a?'selected':'' }}>Attempt {{ $a }}</option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('admin.student-reports.attributes') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-50 text-indigo-700 rounded-xl text-sm font-bold hover:bg-indigo-100 transition-colors">
            <i class="fas fa-list-check text-xs"></i> Manage Attributes
        </a>
    </form>
</div>

@if($selectedProgram && $students->isEmpty())
<div class="card p-10 text-center text-slate-400">
    <i class="fas fa-user-graduate text-3xl mb-2 block"></i>
    <p class="text-sm font-semibold">No students found for this program.</p>
</div>
@elseif($selectedProgram)
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-extrabold text-slate-800">{{ $selectedProgram->name }} — Attempt {{ $selectedAttempt }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ $students->count() }} students · Click a row to fill in their report</p>
        </div>
        <span class="text-xs text-slate-400">
            {{ $reports->count() }} of {{ $students->count() }} reports filled
        </span>
    </div>
    <div class="divide-y divide-slate-50">
        @foreach($students as $student)
        @php $rpt = $reports->get($student->id); @endphp
        <a href="{{ route('admin.student-reports.edit', [$student, 'program_id'=>$selectedProgram->id,'attempt'=>$selectedAttempt]) }}"
           class="flex items-center gap-4 px-5 py-3.5 hover:bg-yellow-50 transition-colors group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-yellow-600 flex items-center justify-center text-white text-xs font-extrabold shrink-0 shadow">
                {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-slate-800 group-hover:text-yellow-700 transition-colors">{{ $student->user->full_name }}</p>
                <p class="text-xs text-slate-400 font-mono">{{ $student->student_id }}</p>
            </div>
            @if($rpt)
            <div class="flex flex-wrap gap-1.5 shrink-0 hidden sm:flex">
                @if($rpt->conduct)
                <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Conduct: {{ Str::limit($rpt->conduct,20) }}</span>
                @endif
                @if($rpt->class_teacher_remark)
                <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Remark saved</span>
                @endif
            </div>
            <span class="shrink-0 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5">
                <i class="fas fa-check text-[8px]"></i> Filled
            </span>
            @else
            <span class="shrink-0 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5">
                <i class="fas fa-edit text-[8px]"></i> Fill Now
            </span>
            @endif
            <i class="fas fa-chevron-right text-slate-300 text-xs shrink-0"></i>
        </a>
        @endforeach
    </div>
</div>
@else
<div class="card p-10 text-center text-slate-400">
    <i class="fas fa-file-signature text-3xl mb-2 block"></i>
    <p class="text-sm font-semibold">Select a program above to manage student reports.</p>
</div>
@endif

</div>
@endsection
