@extends('layouts.app')
@section('title','Class Performance Report')
@section('subtitle', $program->name . ' — Your class academic performance')

@section('content')
<div class="space-y-5">

{{-- Hero --}}
<div class="rounded-3xl relative overflow-hidden p-6 lg:p-8 text-white"
     style="background:linear-gradient(135deg,#0d0a2e 0%,#2d2480 40%,#5647d6 75%,#a395f5 100%)">
    <div class="pointer-events-none absolute -top-16 -right-16 h-56 w-56 rounded-full bg-white/4 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-yellow-400/50 to-transparent"></div>
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1 text-[11px] font-bold uppercase tracking-widest text-indigo-200 mb-2">
                <i class="fas fa-user-tie text-yellow-300 text-[10px]"></i> Class Teacher Report
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold">{{ $program->name }}</h1>
            <p class="text-indigo-200 text-sm mt-1">Your class performance — attempt {{ $attempt }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('lecturer.class-performance.pdf', request()->query()) }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-rose-500 hover:bg-rose-400 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1.5">Period</label>
            <select name="period_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">Active Period</option>
                @foreach($sessions as $sess)
                    @foreach($sess->periods as $p)
                    <option value="{{ $p->id }}" {{ request('period_id')==$p->id?'selected':'' }}>
                        {{ $p->full_label }}{{ $p->is_active?' ★':'' }}
                    </option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1.5">Attempt</label>
            <select name="attempt" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @foreach(range(1,5) as $a)
                <option value="{{ $a }}" {{ $attempt==$a?'selected':'' }}>Attempt {{ $a }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Generate
        </button>
    </form>
</div>

@if(!$data['has_data'])
<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-14 text-center">
    <i class="fas fa-chart-bar text-4xl text-slate-200 block mb-3"></i>
    <p class="text-sm font-semibold text-slate-400">No scores found for this attempt/period.</p>
</div>
@else

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    @foreach([
        ['label'=>'Students',     'val'=>$data['total_students'], 'icon'=>'fa-users',         'bg'=>'from-indigo-500 to-violet-600'],
        ['label'=>'Class Average','val'=>$data['class_average'].'%','icon'=>'fa-chart-line',  'bg'=>'from-blue-500 to-cyan-600'],
        ['label'=>'Highest',      'val'=>$data['highest_score'].'%','icon'=>'fa-arrow-up',    'bg'=>'from-emerald-500 to-teal-600'],
        ['label'=>'Lowest',       'val'=>$data['lowest_score'].'%', 'icon'=>'fa-arrow-down',  'bg'=>'from-rose-500 to-red-600'],
        ['label'=>'Pass Rate',    'val'=>$data['pass_rate'].'%',    'icon'=>'fa-check-circle', 'bg'=>'from-amber-500 to-orange-600'],
    ] as $k)
    <div class="rounded-2xl bg-gradient-to-br {{ $k['bg'] }} p-4 text-white shadow-sm">
        <div class="flex items-center justify-between mb-1.5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/70">{{ $k['label'] }}</p>
            <i class="fas {{ $k['icon'] }} text-white/50 text-sm"></i>
        </div>
        <p class="text-2xl font-extrabold">{{ $k['val'] }}</p>
    </div>
    @endforeach
</div>

{{-- Subject stats: Desktop Table --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden hidden lg:block">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">Subject Performance</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <th class="px-5 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-center">Average</th>
                    <th class="px-4 py-3 text-center">Highest</th>
                    <th class="px-4 py-3 text-center">Lowest</th>
                    <th class="px-4 py-3 text-center">Pass Rate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($data['subject_stats'] as $s)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-bold text-slate-800">{{ $s['course']->name }}</td>
                    <td class="px-4 py-3 text-center font-bold">{{ $s['average'] }}%</td>
                    <td class="px-4 py-3 text-center text-emerald-700 font-semibold">{{ $s['highest'] }}%</td>
                    <td class="px-4 py-3 text-center text-red-600 font-semibold">{{ $s['lowest'] }}%</td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $s['pass_rate']>=50?'bg-emerald-100 text-emerald-800':'bg-red-100 text-red-800' }}">
                            {{ $s['pass_rate'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Subject stats: Mobile Cards --}}
<div class="lg:hidden space-y-3">
    <div class="px-1 mb-1">
        <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Subject Performance</h3>
    </div>
    @foreach($data['subject_stats'] as $s)
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
        <div class="flex items-start justify-between gap-2 mb-3">
            <p class="font-bold text-slate-900 text-sm leading-tight">{{ $s['course']->name }}</p>
            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold {{ $s['pass_rate']>=50?'bg-emerald-100 text-emerald-800':'bg-red-100 text-red-800' }}">{{ $s['pass_rate'] }}% Pass</span>
        </div>
        <div class="grid grid-cols-3 gap-2 pt-3 border-t border-slate-100">
            <div class="text-center">
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Average</p>
                <p class="text-sm font-bold text-slate-800 mt-0.5">{{ $s['average'] }}%</p>
            </div>
            <div class="text-center">
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Highest</p>
                <p class="text-sm font-bold text-emerald-700 mt-0.5">{{ $s['highest'] }}%</p>
            </div>
            <div class="text-center">
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Lowest</p>
                <p class="text-sm font-bold text-red-600 mt-0.5">{{ $s['lowest'] }}%</p>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Student Rankings: Desktop Table --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden hidden lg:block">
    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">Student Rankings</h3>
        {{-- Bulk report card download for class --}}
        <form method="POST" action="{{ route('lecturer.exams.bulk-report-cards') }}">
            @csrf
            <input type="hidden" name="program_id" value="{{ $program->id }}">
            <input type="hidden" name="attempt" value="{{ $attempt }}">
            @foreach($data['ranked_students'] as $r)
            <input type="hidden" name="student_ids[]" value="{{ $r['student']->id }}">
            @endforeach
            <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold px-4 py-2 shadow transition-all active:scale-95">
                <i class="fas fa-users text-[10px]"></i> All Report Cards PDF
            </button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <th class="px-5 py-3 text-left">Rank</th>
                    <th class="px-5 py-3 text-left">Student</th>
                    <th class="px-4 py-3 text-center">Average</th>
                    <th class="px-4 py-3 text-center">Grade</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Report</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($data['ranked_students'] as $r)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-extrabold
                            {{ $r['rank']==1?'bg-yellow-400 text-yellow-900':($r['rank']==2?'bg-slate-300 text-slate-700':($r['rank']==3?'bg-amber-600 text-white':'bg-slate-100 text-slate-600')) }}">
                            {{ $r['rank'] }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-7 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center text-white text-[10px] font-bold shrink-0">
                                {{ strtoupper(substr($r['student']->user->full_name??'?',0,1)) }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm leading-tight">{{ $r['student']->user->full_name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $r['student']->student_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center font-extrabold text-slate-800">{{ $r['average'] }}%</td>
                    <td class="px-4 py-3 text-center">
                        @php $gc=['A1'=>'bg-emerald-100 text-emerald-800','A2'=>'bg-emerald-100 text-emerald-700','A3'=>'bg-teal-100 text-teal-800','B1'=>'bg-blue-100 text-blue-800','B2'=>'bg-blue-100 text-blue-700','B3'=>'bg-indigo-100 text-indigo-800','C'=>'bg-amber-100 text-amber-800','F'=>'bg-red-100 text-red-800']; @endphp
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $gc[$r['grade']]??'bg-slate-100 text-slate-600' }}">{{ $r['grade'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $r['passed']?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700' }}">
                            {{ $r['passed']?'Pass':'Fail' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('lecturer.exams.report-card', ['student_id'=>$r['student']->id,'program_id'=>$program->id,'attempt'=>$attempt]) }}"
                           class="inline-flex items-center gap-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-bold px-2.5 py-1.5 transition-colors">
                            <i class="fas fa-file-pdf text-[10px]"></i> PDF
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Student Rankings: Mobile Cards --}}
<div class="lg:hidden space-y-3">
    <div class="flex items-center justify-between px-1 mb-2">
        <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Student Rankings</h3>
        <form method="POST" action="{{ route('lecturer.exams.bulk-report-cards') }}">
            @csrf
            <input type="hidden" name="program_id" value="{{ $program->id }}">
            <input type="hidden" name="attempt" value="{{ $attempt }}">
            @foreach($data['ranked_students'] as $r)
            <input type="hidden" name="student_ids[]" value="{{ $r['student']->id }}">
            @endforeach
            <button type="submit"
                class="inline-flex items-center gap-1 rounded-xl bg-violet-600 text-white text-[10px] font-bold px-3 py-1.5 shadow">
                <i class="fas fa-file-pdf text-[9px]"></i> Bulk PDF
            </button>
        </form>
    </div>
    @foreach($data['ranked_students'] as $r)
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
        <div class="flex items-start gap-3">
            <span class="shrink-0 inline-flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-extrabold shadow-sm
                {{ $r['rank']==1?'bg-gradient-to-br from-yellow-300 to-yellow-500 text-yellow-900':($r['rank']==2?'bg-gradient-to-br from-slate-200 to-slate-400 text-slate-800':($r['rank']==3?'bg-gradient-to-br from-amber-500 to-amber-700 text-white':'bg-slate-100 text-slate-600')) }}">
                #{{ $r['rank'] }}
            </span>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-900 text-sm leading-tight truncate">{{ $r['student']->user->full_name }}</p>
                        <p class="text-[11px] font-mono text-slate-500 mt-0.5">{{ $r['student']->student_id }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold {{ $r['passed']?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700' }}">
                        {{ $r['passed']?'Pass':'Fail' }}
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 pt-3 border-t border-slate-100">
                    <div>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Average</p>
                        <p class="text-sm font-extrabold text-slate-800 mt-0.5">{{ $r['average'] }}%</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Grade</p>
                        @php $gc=['A1'=>'bg-emerald-100 text-emerald-800','A2'=>'bg-emerald-100 text-emerald-700','A3'=>'bg-teal-100 text-teal-800','B1'=>'bg-blue-100 text-blue-800','B2'=>'bg-blue-100 text-blue-700','B3'=>'bg-indigo-100 text-indigo-800','C'=>'bg-amber-100 text-amber-800','F'=>'bg-red-100 text-red-800']; @endphp
                        <p class="text-sm mt-0.5"><span class="rounded-full px-2.5 py-0.5 text-[10px] font-extrabold {{ $gc[$r['grade']]??'bg-slate-100 text-slate-600' }}">{{ $r['grade'] }}</span></p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('lecturer.exams.report-card', ['student_id'=>$r['student']->id,'program_id'=>$program->id,'attempt'=>$attempt]) }}"
                       class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-bold py-2 transition-colors">
                        <i class="fas fa-file-pdf text-[10px]"></i> Download Report Card PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endif
</div>
@endsection
