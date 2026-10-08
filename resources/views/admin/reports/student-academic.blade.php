@extends('layouts.app')
@section('title','Student Academic Report')
@section('subtitle','Individual report card with attendance, grades and ranking')

@section('content')
<div class="space-y-5">

{{-- Filters --}}
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="text-sm font-extrabold text-slate-800 mb-4"><i class="fas fa-filter text-indigo-400 mr-1.5"></i>Select Student</h3>
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program *</label>
            <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" required onchange="this.form.submit()">
                <option value="">-- Select Program --</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        @if($students->isNotEmpty())
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Student *</label>
            <select name="student_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">-- Select Student --</option>
                @foreach($students as $s)
                <option value="{{ $s->id }}" {{ request('student_id')==$s->id?'selected':'' }}>{{ $s->student_id }} — {{ $s->user->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Attempt</label>
            <select name="attempt" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @foreach(range(1,5) as $a)
                <option value="{{ $a }}" {{ request('attempt',1)==$a?'selected':'' }}>Attempt {{ $a }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-bold py-2.5 px-5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Generate
        </button>
    </form>
</div>

@if($data && $student)

@php $gc=['A1'=>'bg-emerald-100 text-emerald-800','A2'=>'bg-emerald-100 text-emerald-700','A3'=>'bg-teal-100 text-teal-800','B1'=>'bg-blue-100 text-blue-800','B2'=>'bg-blue-100 text-blue-700','B3'=>'bg-indigo-100 text-indigo-800','C'=>'bg-amber-100 text-amber-800','F'=>'bg-red-100 text-red-800']; @endphp

{{-- Student Header Card --}}
<div class="rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-violet-50 p-6 flex flex-col sm:flex-row sm:items-center gap-5">
    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-2xl font-extrabold shadow-lg">
        {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
    </div>
    <div class="flex-1">
        <h2 class="text-xl font-extrabold text-slate-900">{{ $student->user->full_name }}</h2>
        <div class="flex flex-wrap gap-3 mt-1.5 text-xs text-slate-500 font-semibold">
            <span><i class="fas fa-id-card text-indigo-400 mr-1"></i>{{ $student->student_id }}</span>
            <span><i class="fas fa-graduation-cap text-violet-400 mr-1"></i>{{ $data['program']->name }}</span>
            <span><i class="fas fa-redo text-slate-400 mr-1"></i>Attempt {{ request('attempt',1) }}</span>
            @if($data['period']??null)<span><i class="fas fa-calendar text-emerald-400 mr-1"></i>{{ $data['period']->full_label }}</span>@endif
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.reports.student-academic.pdf', request()->query()) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        {{-- Bulk: download all students in this program --}}
        @if(request('program_id'))
        <form method="POST" action="{{ route('admin.reports.bulk-academic-pdf') }}" class="inline">
            @csrf
            <input type="hidden" name="program_id" value="{{ request('program_id') }}">
            <input type="hidden" name="attempt"    value="{{ request('attempt',1) }}">
            <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
                <i class="fas fa-users"></i> All Students PDF
            </button>
        </form>
        @endif
    </div>
</div>

@if(!$data['has_scores'])
<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
    <p class="text-sm font-semibold text-slate-400">No scores found for this student in the selected program/attempt.</p>
</div>
@else

{{-- Summary Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach([
        ['label'=>'Overall Average','val'=>$data['overall_average'].'%','icon'=>'fa-chart-line','bg'=>'from-indigo-500 to-violet-600'],
        ['label'=>'Grade',          'val'=>$data['overall_grade'],      'icon'=>'fa-star',       'bg'=>'from-amber-400 to-orange-500'],
        ['label'=>'Position',       'val'=>$data['overall_position'].' / '.$data['total_students_in_exam'],'icon'=>'fa-trophy','bg'=>'from-yellow-400 to-amber-500'],
        ['label'=>'Result',         'val'=>$data['result'],             'icon'=>'fa-check-circle','bg'=>$data['result']==='PASS'?'from-emerald-500 to-teal-600':'from-red-500 to-rose-600'],
    ] as $k)
    <div class="rounded-2xl bg-gradient-to-br {{ $k['bg'] }} p-4 text-white shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/70">{{ $k['label'] }}</p>
            <i class="fas {{ $k['icon'] }} text-white/50 text-sm"></i>
        </div>
        <p class="text-xl font-extrabold leading-none">{{ $k['val'] }}</p>
    </div>
    @endforeach
</div>

{{-- Attendance Row --}}
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="text-sm font-extrabold text-slate-800 mb-3"><i class="fas fa-calendar-check text-emerald-500 mr-1.5"></i>Attendance</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['label'=>'School Days', 'val'=>$data['school_days']??0,  'color'=>'text-slate-800'],
            ['label'=>'Present',     'val'=>$data['present_days']??0, 'color'=>'text-emerald-700'],
            ['label'=>'Absent',      'val'=>$data['absent_days']??0,  'color'=>'text-red-600'],
            ['label'=>'Rate',        'val'=>($data['att_pct']??0).'%','color'=>($data['att_pct']??0)>=75?'text-emerald-700':'text-amber-700'],
        ] as $a)
        <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-center">
            <p class="text-2xl font-extrabold {{ $a['color'] }}">{{ $a['val'] }}</p>
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">{{ $a['label'] }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- Subject Results Table --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">Subject Results</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <th class="px-5 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-center">Class Score</th>
                    <th class="px-4 py-3 text-center">Exam Score</th>
                    <th class="px-4 py-3 text-center">Total</th>
                    <th class="px-4 py-3 text-center">Grade</th>
                    <th class="px-4 py-3 text-center">Position</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($data['courses'] as $row)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3 font-bold text-slate-800">{{ $row['course']->name }}</td>
                    <td class="px-4 py-3 text-center text-slate-600">{{ $row['class_score'] }}</td>
                    <td class="px-4 py-3 text-center text-slate-600">{{ $row['exam_score'] }}</td>
                    <td class="px-4 py-3 text-center font-extrabold text-slate-900">{{ $row['aggregate'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $gc[$row['grade']]??'bg-slate-100 text-slate-600' }}">{{ $row['grade'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs font-bold text-slate-500">{{ $row['position'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endif
@elseif(request('program_id') && !$data)
<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
    <p class="text-sm font-semibold text-slate-400">Select a student to generate their report.</p>
</div>
@endif

</div>
@endsection
