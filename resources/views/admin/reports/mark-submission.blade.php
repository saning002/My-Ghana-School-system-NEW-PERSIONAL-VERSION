@extends('layouts.app')
@section('title','Mark Submission Status')
@section('subtitle','Track which teachers have submitted scores for the current period')

@section('content')
<div class="space-y-5">

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Period</label>
            <select name="period_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-pink-400">
                <option value="">Active Period (default)</option>
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
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program</label>
            <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-pink-400">
                <option value="">All Programs</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-pink-600 to-rose-600 text-white text-sm font-bold py-2.5 px-5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Filter
        </button>
    </form>
</div>

{{-- Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    @foreach([
        ['label'=>'Complete',  'val'=>$data['total_complete'], 'bg'=>'from-emerald-500 to-teal-600',  'icon'=>'fa-check-circle'],
        ['label'=>'Pending',   'val'=>$data['total_pending'],  'bg'=>'from-amber-500 to-orange-600', 'icon'=>'fa-clock'],
        ['label'=>'Period',    'val'=>$period?->full_label??'—','bg'=>'from-indigo-500 to-violet-600','icon'=>'fa-calendar'],
    ] as $k)
    <div class="rounded-2xl bg-gradient-to-br {{ $k['bg'] }} p-4 text-white shadow-sm flex items-center gap-3">
        <i class="fas {{ $k['icon'] }} text-2xl text-white/60"></i>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/70">{{ $k['label'] }}</p>
            <p class="text-xl font-extrabold truncate max-w-[200px]">{{ $k['val'] }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">Mark Submission by Teacher</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <th class="px-5 py-3 text-left">Teacher</th>
                    <th class="px-4 py-3 text-left">Course</th>
                    <th class="px-4 py-3 text-left">Program</th>
                    <th class="px-4 py-3 text-center">Expected</th>
                    <th class="px-4 py-3 text-center">Submitted</th>
                    <th class="px-4 py-3 text-center">Missing</th>
                    <th class="px-4 py-3 text-center">Progress</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($data['rows'] as $row)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3">
                        <p class="font-bold text-slate-800 text-sm">{{ $row['lecturer']?->full_name ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600 font-medium">{{ $row['course']?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600 font-medium">{{ $row['program']?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $row['expected'] }}</td>
                    <td class="px-4 py-3 text-center font-bold text-emerald-700">{{ $row['submitted'] }}</td>
                    <td class="px-4 py-3 text-center font-bold {{ $row['missing']>0?'text-red-600':'text-slate-400' }}">{{ $row['missing'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center gap-1.5 justify-center">
                            <div class="w-20 h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $row['complete']?'bg-emerald-500':'bg-amber-400' }}"
                                     style="width:{{ min(100,$row['percentage']) }}%"></div>
                            </div>
                            <span class="text-[11px] font-bold text-slate-500">{{ $row['percentage'] }}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $row['complete']?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700' }}">
                            {{ $row['complete']?'Complete':'Pending' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-10 text-sm text-slate-400">No assignments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>
@endsection
