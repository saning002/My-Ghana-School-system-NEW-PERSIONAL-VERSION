@extends('staff-portal.layout')
@section('title','Class Performance')
@section('subtitle','Academic rankings and subject analysis')

@section('content')
<div class="space-y-5">

<div class="card p-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Program *</label>
            <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" required>
                <option value="">— Select —</option>
                @foreach($programs as $p)<option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Period</label>
            <select name="period_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                <option value="">Active Period</option>
                @foreach($sessions as $sess)@foreach($sess->periods as $p)
                <option value="{{ $p->id }}" {{ request('period_id')==$p->id?'selected':'' }}>{{ $p->full_label }}{{ $p->is_active?' ★':'' }}</option>
                @endforeach@endforeach
            </select>
        </div>
        <div class="w-[120px]">
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Attempt</label>
            <select name="attempt" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                @foreach(range(1,5) as $a)<option value="{{ $a }}" {{ request('attempt',1)==$a?'selected':'' }}>Attempt {{ $a }}</option>@endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Generate
        </button>
        @if(request('program_id'))
        <a href="{{ route('staff-portal.reports.class.pdf', request()->query()) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-file-pdf text-xs"></i> PDF
        </a>
        @endif
    </form>
</div>

@if($data && $data['has_data'])

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    @foreach([['Students',$data['total_students'],'from-blue-500 to-cyan-600','fa-users'],['Average',$data['class_average'].'%','from-emerald-500 to-teal-600','fa-chart-line'],['Highest',$data['highest_score'].'%','from-amber-500 to-orange-600','fa-arrow-up'],['Lowest',$data['lowest_score'].'%','from-rose-500 to-red-600','fa-arrow-down'],['Pass Rate',$data['pass_rate'].'%','from-teal-500 to-cyan-600','fa-check-circle']] as [$l,$v,$g])
    <div class="rounded-2xl bg-gradient-to-br {{ $g }} p-4 text-white shadow-sm text-center">
        <p class="text-[10px] font-bold uppercase tracking-wider text-white/70 mb-1">{{ $l }}</p>
        <p class="text-xl font-extrabold">{{ $v }}</p>
    </div>
    @endforeach
</div>

{{-- Subject Performance: Desktop Table --}}
<div class="card overflow-hidden hidden lg:block">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50"><h3 class="text-sm font-extrabold text-slate-800">Subject Performance</h3></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 text-left">Subject</th><th class="px-4 py-3 text-center">Average</th><th class="px-4 py-3 text-center">Highest</th><th class="px-4 py-3 text-center">Lowest</th><th class="px-4 py-3 text-center">Pass Rate</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($data['subject_stats'] as $s)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-bold text-slate-800">{{ $s['course']->name }}</td>
                    <td class="px-4 py-3 text-center font-bold">{{ $s['average'] }}%</td>
                    <td class="px-4 py-3 text-center text-emerald-700 font-semibold">{{ $s['highest'] }}%</td>
                    <td class="px-4 py-3 text-center text-red-600 font-semibold">{{ $s['lowest'] }}%</td>
                    <td class="px-4 py-3 text-center"><span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $s['pass_rate']>=50?'bg-emerald-100 text-emerald-800':'bg-red-100 text-red-800' }}">{{ $s['pass_rate'] }}%</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Subject Performance: Mobile Cards --}}
<div class="lg:hidden space-y-3">
    <div class="px-1 mb-1">
        <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Subject Performance</h3>
    </div>
    @foreach($data['subject_stats'] as $s)
    <div class="card p-4">
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
<div class="card overflow-hidden hidden lg:block">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50"><h3 class="text-sm font-extrabold text-slate-800">Student Rankings</h3></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 text-left">Rank</th><th class="px-5 py-3 text-left">Student</th><th class="px-4 py-3 text-center">Average</th><th class="px-4 py-3 text-center">Grade</th><th class="px-4 py-3 text-center">Status</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($data['ranked_students'] as $r)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3"><span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-extrabold {{ $r['rank']==1?'bg-yellow-400 text-yellow-900':($r['rank']==2?'bg-slate-300 text-slate-700':($r['rank']==3?'bg-amber-600 text-white':'bg-slate-100 text-slate-600')) }}">{{ $r['rank'] }}</span></td>
                    <td class="px-5 py-3 font-bold text-slate-800">{{ $r['student']->user->full_name }}<br><span class="text-[10px] text-slate-400 font-mono">{{ $r['student']->student_id }}</span></td>
                    <td class="px-4 py-3 text-center font-extrabold">{{ $r['average'] }}%</td>
                    <td class="px-4 py-3 text-center"><span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold bg-amber-100 text-amber-800">{{ $r['grade'] }}</span></td>
                    <td class="px-4 py-3 text-center"><span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $r['passed']?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700' }}">{{ $r['passed']?'Pass':'Fail' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Student Rankings: Mobile Cards --}}
<div class="lg:hidden space-y-3 mt-5">
    <div class="px-1 mb-1">
        <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Student Rankings</h3>
    </div>
    @foreach($data['ranked_students'] as $r)
    <div class="card p-4">
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
                        <p class="text-sm"><span class="rounded-full px-2.5 py-0.5 text-[10px] font-extrabold bg-amber-100 text-amber-800">{{ $r['grade'] }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@elseif(request('program_id'))
<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
    <i class="fas fa-chart-bar text-4xl text-slate-200 block mb-3"></i>
    <p class="text-sm font-semibold text-slate-400">No score data found for the selected filters.</p>
</div>
@endif

</div>
@endsection
