@extends('layouts.app')
@section('title','Student Performance')
@section('subtitle','Academic trend across all programs and attempts')

@section('content')
<div class="space-y-5">

{{-- Filters --}}
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="text-sm font-extrabold text-slate-800 mb-4">
        <i class="fas fa-filter text-indigo-400 mr-1.5"></i>Select Student
    </h3>
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program</label>
            <select name="program_id"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                onchange="this.form.submit()">
                <option value="">-- Filter by program --</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Student *</label>
            <select name="student_id"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">-- Select student --</option>
                @foreach($students as $s)
                <option value="{{ $s->id }}" {{ request('student_id')==$s->id?'selected':'' }}>
                    {{ $s->student_id }} — {{ $s->user->full_name }}
                </option>
                @endforeach
                @if($students->isEmpty())
                    @foreach(\App\Models\Student::with('user')->forExams()->orderBy('student_id')->get() as $s)
                    <option value="{{ $s->id }}" {{ request('student_id')==$s->id?'selected':'' }}>
                        {{ $s->student_id }} — {{ $s->user->full_name }}
                    </option>
                    @endforeach
                @endif
            </select>
        </div>
        <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-bold py-2.5 px-5 shadow transition-all active:scale-95">
            <i class="fas fa-chart-line text-xs"></i> Generate
        </button>
    </form>
</div>

@if($student && $data)

@if(empty($data['trend']))
<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
    <i class="fas fa-chart-line text-4xl text-slate-200 block mb-3"></i>
    <p class="text-sm font-semibold text-slate-400">No score records found for this student.</p>
</div>
@else

{{-- Student header --}}
<div class="rounded-2xl bg-gradient-to-r from-indigo-50 to-violet-50 border border-indigo-100 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-xl font-extrabold shadow">
        {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
    </div>
    <div class="flex-1">
        <h2 class="text-lg font-extrabold text-slate-900">{{ $student->user->full_name }}</h2>
        <p class="text-xs text-slate-500 mt-0.5">{{ $student->student_id }} &bull; {{ $student->program?->name }}</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('admin.reports.student-performance.pdf', ['student_id'=>$student->id]) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
    </div>
</div>

{{-- KPI summary --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach([
        ['Attempts Recorded', count($data['trend']),        'fa-layer-group',  'from-indigo-500 to-violet-600'],
        ['Overall Average',   ($data['overall_avg']??0).'%','fa-chart-line',   'from-blue-500 to-cyan-600'],
        ['Best Performance',  ($data['best']??0).'%',        'fa-trophy',      'from-amber-400 to-orange-500'],
        ['Lowest Score',      ($data['worst']??0).'%',       'fa-arrow-down',  'from-rose-500 to-red-600'],
    ] as [$label,$val,$icon,$grad])
    <div class="rounded-2xl bg-gradient-to-br {{ $grad }} p-4 text-white shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/70">{{ $label }}</p>
            <i class="fas {{ $icon }} text-white/50 text-sm"></i>
        </div>
        <p class="text-2xl font-extrabold">{{ $val }}</p>
    </div>
    @endforeach
</div>

{{-- Performance trend chart --}}
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-extrabold text-slate-800 mb-5">
        <i class="fas fa-chart-line text-indigo-400 mr-1.5"></i>Performance Trend
    </h3>
    <div class="h-64">
        <canvas id="perfTrendChart"></canvas>
    </div>
</div>

{{-- Detailed records per program/attempt --}}
@foreach($data['trend'] as $entry)
@php
$gc = ['A1'=>'bg-emerald-100 text-emerald-800','A2'=>'bg-emerald-100 text-emerald-700',
       'A3'=>'bg-teal-100 text-teal-800','B1'=>'bg-blue-100 text-blue-800',
       'B2'=>'bg-blue-100 text-blue-700','B3'=>'bg-indigo-100 text-indigo-800',
       'C'=>'bg-amber-100 text-amber-800','F'=>'bg-red-100 text-red-800'];
@endphp
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3.5 bg-gradient-to-r from-slate-800 to-slate-700">
        <div class="flex items-center gap-3">
            <span class="text-sm font-extrabold text-white">{{ $entry['program']->name }}</span>
            <span class="rounded-full bg-white/15 text-white/80 text-[10px] font-bold px-2.5 py-0.5">
                Attempt {{ $entry['attempt'] }}
            </span>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="text-xs text-white/60 font-semibold">
                Avg: <strong class="text-white">{{ $entry['average'] }}%</strong>
            </span>
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $gc[$entry['grade']]??'bg-slate-100 text-slate-600' }}">
                {{ $entry['grade'] }}
            </span>
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold
                {{ $entry['result']==='PASS'?'bg-emerald-100 text-emerald-800':'bg-red-100 text-red-800' }}">
                {{ $entry['result'] }}
            </span>
            <span class="text-[10px] text-white/50 font-semibold">
                Rank: {{ $entry['position'] }} / {{ $entry['of'] }}
            </span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 bg-slate-50">
                    <th class="px-5 py-2.5 text-left">Subject</th>
                    <th class="px-4 py-2.5 text-center">Class Score</th>
                    <th class="px-4 py-2.5 text-center">Exam</th>
                    <th class="px-4 py-2.5 text-center">Total</th>
                    <th class="px-4 py-2.5 text-center">Grade</th>
                    <th class="px-4 py-2.5 text-center">Position</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($entry['courses'] as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-2.5 font-medium text-slate-800">{{ $row['course']->name }}</td>
                    <td class="px-4 py-2.5 text-center text-slate-500 text-xs">{{ $row['class_score'] }}</td>
                    <td class="px-4 py-2.5 text-center text-slate-500 text-xs">{{ $row['exam_score'] }}</td>
                    <td class="px-4 py-2.5 text-center font-bold text-slate-800">{{ $row['aggregate'] }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $gc[$row['grade']]??'bg-slate-100 text-slate-600' }}">
                            {{ $row['grade'] }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center text-xs text-slate-400 font-semibold">{{ $row['position'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach

@endif
@endif

</div>
@endsection

@push('scripts')
@if($data && !empty($data['trend']))
<script>
(function(){
    const trend  = @json($data['trend']);
    const labels = trend.map(t => t.program.name + ' A' + t.attempt);
    const avgs   = trend.map(t => t.average);

    const ctx = document.getElementById('perfTrendChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Average %',
                data: avgs,
                borderColor: '#5647d6',
                backgroundColor: 'rgba(86,71,214,0.10)',
                pointBackgroundColor: '#5647d6',
                pointRadius: 6,
                pointHoverRadius: 8,
                borderWidth: 2.5,
                tension: 0.35,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(13,10,46,.92)',
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => ' Average: ' + ctx.parsed.y + '%',
                        afterLabel: (ctx) => {
                            const t = trend[ctx.dataIndex];
                            return ` Grade: ${t.grade}  Result: ${t.result}`;
                        },
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
                y: {
                    beginAtZero: true, max: 100,
                    grid: { color: 'rgba(226,232,240,0.5)' },
                    ticks: { font: { size: 10 }, callback: v => v + '%' },
                },
            },
        },
    });
})();
</script>
@endif
@endpush
