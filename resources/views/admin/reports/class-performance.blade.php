@extends('layouts.app')
@section('title','Class Performance Report')
@section('subtitle','Average, rankings, subject breakdown and pass/fail rates')

@section('content')
<div class="space-y-5">

{{-- Filters --}}
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="text-sm font-extrabold text-slate-800 mb-4"><i class="fas fa-filter text-indigo-400 mr-1.5"></i>Filters</h3>
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program *</label>
            <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                <option value="">-- Select Program --</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Period</label>
            <select name="period_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Periods</option>
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
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Attempt</label>
            <select name="attempt" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @foreach(range(1,5) as $a)
                <option value="{{ $a }}" {{ request('attempt',1)==$a?'selected':'' }}>Attempt {{ $a }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-bold py-2.5 px-5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Generate
        </button>
    </form>
</div>

@if($data)

@if(!$data['has_data'])
<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-14 text-center">
    <i class="fas fa-chart-bar text-4xl text-slate-200 mb-3 block"></i>
    <p class="text-sm font-semibold text-slate-500">No score data found for the selected filters.</p>
</div>
@else

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    @foreach([
        ['label'=>'Students',     'val'=>$data['total_students'], 'icon'=>'fa-users',           'bg'=>'from-indigo-500 to-violet-600'],
        ['label'=>'Class Average','val'=>$data['class_average'].'%','icon'=>'fa-chart-line',    'bg'=>'from-blue-500 to-cyan-600'],
        ['label'=>'Highest',      'val'=>$data['highest_score'].'%','icon'=>'fa-arrow-up',      'bg'=>'from-emerald-500 to-teal-600'],
        ['label'=>'Lowest',       'val'=>$data['lowest_score'].'%', 'icon'=>'fa-arrow-down',    'bg'=>'from-rose-500 to-red-600'],
        ['label'=>'Pass Rate',    'val'=>$data['pass_rate'].'%',    'icon'=>'fa-check-circle',  'bg'=>'from-amber-500 to-orange-600'],
    ] as $k)
    <div class="rounded-2xl bg-gradient-to-br {{ $k['bg'] }} p-4 text-white shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/70">{{ $k['label'] }}</p>
            <i class="fas {{ $k['icon'] }} text-white/50 text-sm"></i>
        </div>
        <p class="text-2xl font-extrabold leading-none">{{ $k['val'] }}</p>
    </div>
    @endforeach
</div>

{{-- Grade Distribution --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-extrabold text-slate-800 mb-4">Grade Distribution</h3>
        @php $totalStudents = $data['total_students']; @endphp
        @foreach($data['grade_distribution'] as $grade => $count)
        @php
            $pct = $totalStudents > 0 ? round($count/$totalStudents*100) : 0;
            $colors = ['A1'=>'bg-emerald-500','A2'=>'bg-emerald-400','A3'=>'bg-teal-400','B1'=>'bg-blue-500','B2'=>'bg-blue-400','B3'=>'bg-indigo-400','C'=>'bg-amber-500','F'=>'bg-red-500'];
            $bar = $colors[$grade] ?? 'bg-slate-400';
        @endphp
        <div class="flex items-center gap-2 mb-2">
            <span class="w-8 text-xs font-extrabold text-slate-700">{{ $grade }}</span>
            <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full {{ $bar }}" style="width:{{ $pct }}%"></div>
            </div>
            <span class="w-10 text-right text-xs font-bold text-slate-500">{{ $count }}</span>
        </div>
        @endforeach
    </div>

    {{-- Subject Stats --}}
    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
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
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3 font-bold text-slate-800">{{ $s['course']->name }}</td>
                        <td class="px-4 py-3 text-center font-bold text-slate-700">{{ $s['average'] }}%</td>
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
</div>

{{-- Student Ranking Table --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">Student Rankings</h3>
        <a href="{{ route('admin.reports.class-performance.pdf', request()->query()) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2 shadow transition-all active:scale-95">
            <i class="fas fa-file-pdf text-[10px]"></i> Download PDF
        </a>
        {{-- Bulk all-students PDF --}}
        @if(request('program_id'))
        <form method="POST" action="{{ route('admin.reports.bulk-academic-pdf') }}" class="inline">
            @csrf
            <input type="hidden" name="program_id" value="{{ request('program_id') }}">
            <input type="hidden" name="attempt"    value="{{ request('attempt',1) }}">
            <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold px-4 py-2 shadow transition-all active:scale-95">
                <i class="fas fa-users text-[10px]"></i> Bulk All Cards PDF
            </button>
        </form>
        @endif
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
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($data['ranked_students'] as $r)
                <tr class="hover:bg-slate-50 transition-colors">
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
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endif
@endif

</div>
@endsection
