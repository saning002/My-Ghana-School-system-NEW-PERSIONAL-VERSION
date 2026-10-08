@extends('layouts.app')
@section('title','Attendance Summary')
@section('subtitle','Present, absent and attendance percentage per student per period')

@section('content')
<div class="space-y-5">

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="text-sm font-extrabold text-slate-800 mb-4"><i class="fas fa-filter text-emerald-400 mr-1.5"></i>Filters</h3>
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program *</label>
            <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" required>
                <option value="">-- Select Program --</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Period</label>
            <select name="period_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
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
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-bold py-2.5 px-5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Generate
        </button>
    </form>
</div>

@if($data)
{{-- Summary KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @php $summaries = $data['student_summaries']; @endphp
    @foreach([
        ['label'=>'Total Students','val'=>$summaries->count(),                              'bg'=>'from-indigo-500 to-violet-600'],
        ['label'=>'School Days',   'val'=>$data['school_days'],                             'bg'=>'from-blue-500 to-cyan-600'],
        ['label'=>'Avg Attendance','val'=>round($data['overall_rate'],1).'%',               'bg'=>'from-emerald-500 to-teal-600'],
        ['label'=>'Poor Attendance','val'=>$summaries->where('status','poor')->count().' students','bg'=>'from-rose-500 to-red-600'],
    ] as $k)
    <div class="rounded-2xl bg-gradient-to-br {{ $k['bg'] }} p-4 text-white shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-wider text-white/70 mb-1">{{ $k['label'] }}</p>
        <p class="text-xl font-extrabold">{{ $k['val'] }}</p>
    </div>
    @endforeach
</div>

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">
            {{ $data['program']->name }} — {{ $data['period']?->full_label ?? 'All Periods' }}
        </h3>
        <a href="{{ route('admin.reports.attendance-summary.pdf', request()->query()) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2 shadow transition-all active:scale-95">
            <i class="fas fa-file-pdf text-[10px]"></i> PDF
        </a>
        <form method="POST" action="{{ route('admin.reports.bulk-attendance-pdf') }}" class="inline">
            @csrf
            <input type="hidden" name="program_id" value="{{ request('program_id') }}">
            @if(request('period_id'))<input type="hidden" name="period_id" value="{{ request('period_id') }}">@endif
            <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold px-4 py-2 shadow transition-all active:scale-95">
                <i class="fas fa-users text-[10px]"></i> Bulk PDF
            </button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <th class="px-5 py-3 text-left">Student</th>
                    <th class="px-4 py-3 text-center">School Days</th>
                    <th class="px-4 py-3 text-center">Present</th>
                    <th class="px-4 py-3 text-center">Absent</th>
                    <th class="px-4 py-3 text-center">% Attendance</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($summaries->sortByDesc('percentage') as $row)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-7 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-[10px] font-bold shrink-0">
                                {{ strtoupper(substr($row['student']->user->full_name??'?',0,1)) }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm leading-tight">{{ $row['student']->user->full_name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $row['student']->student_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-slate-600">{{ $row['school_days'] }}</td>
                    <td class="px-4 py-3 text-center font-bold text-emerald-700">{{ $row['present'] }}</td>
                    <td class="px-4 py-3 text-center font-bold text-red-600">{{ $row['absent'] }}</td>
                    <td class="px-4 py-3 text-center font-extrabold">
                        <div class="inline-flex items-center gap-1.5">
                            {{ $row['percentage'] }}%
                            <div class="w-16 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $row['percentage']>=75?'bg-emerald-500':($row['percentage']>=50?'bg-amber-500':'bg-red-500') }}"
                                     style="width:{{ $row['percentage'] }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold
                            {{ $row['status']==='good'?'bg-emerald-100 text-emerald-700':($row['status']==='average'?'bg-amber-100 text-amber-700':'bg-red-100 text-red-700') }}">
                            {{ ucfirst($row['status']) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</div>
@endsection
