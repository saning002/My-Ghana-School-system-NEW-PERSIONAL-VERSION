@extends('staff-portal.layout')
@section('title','Reports')
@section('subtitle','Academic and financial reports')

@section('content')
<div class="space-y-5">

{{-- Summary KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach([
        ['Active Students', $summary['active_students']??0,  'from-indigo-500 to-violet-600', 'fa-user-graduate'],
        ['Attendance Rate', ($summary['attendance_rate']??0).'%', 'from-emerald-500 to-teal-600','fa-clipboard-check'],
        ['Collected',       'GH₵ '.number_format($summary['fees']['collected']??0,0), 'from-amber-500 to-orange-500','fa-coins'],
        ['Outstanding',     'GH₵ '.number_format($summary['fees']['outstanding']??0,0),'from-rose-500 to-red-600','fa-exclamation-circle'],
    ] as [$l,$v,$g,$i])
    <div class="rounded-2xl bg-gradient-to-br {{ $g }} p-4 text-white shadow-sm">
        <div class="flex items-center justify-between mb-1.5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/70">{{ $l }}</p>
            <i class="fas {{ $i }} text-white/50 text-sm"></i>
        </div>
        <p class="text-2xl font-extrabold">{{ $v }}</p>
    </div>
    @endforeach
</div>

{{-- Report links --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <a href="{{ route('staff-portal.reports.class') }}"
       class="card p-5 flex items-center gap-4 hover:-translate-y-1 hover:shadow-lg transition-all group">
        <div class="h-12 w-12 rounded-2xl flex items-center justify-center shrink-0" style="background:#ede9fe">
            <i class="fas fa-chart-bar text-violet-600 text-lg"></i>
        </div>
        <div>
            <p class="text-sm font-extrabold text-slate-800">Class Performance</p>
            <p class="text-xs text-slate-400 mt-0.5">Rankings, averages and subject breakdown</p>
        </div>
        <i class="fas fa-chevron-right text-slate-300 group-hover:text-slate-500 ml-auto transition-colors"></i>
    </a>

    <a href="{{ route('staff-portal.fees') }}"
       class="card p-5 flex items-center gap-4 hover:-translate-y-1 hover:shadow-lg transition-all group">
        <div class="h-12 w-12 rounded-2xl flex items-center justify-center shrink-0" style="background:#fef3c7">
            <i class="fas fa-file-invoice text-amber-600 text-lg"></i>
        </div>
        <div>
            <p class="text-sm font-extrabold text-slate-800">Fee Statements</p>
            <p class="text-xs text-slate-400 mt-0.5">Per-student fee statements and PDFs</p>
        </div>
        <i class="fas fa-chevron-right text-slate-300 group-hover:text-slate-500 ml-auto transition-colors"></i>
    </a>

    <a href="{{ route('staff-portal.attendance') }}"
       class="card p-5 flex items-center gap-4 hover:-translate-y-1 hover:shadow-lg transition-all group">
        <div class="h-12 w-12 rounded-2xl flex items-center justify-center shrink-0" style="background:#dcfce7">
            <i class="fas fa-calendar-check text-emerald-600 text-lg"></i>
        </div>
        <div>
            <p class="text-sm font-extrabold text-slate-800">Attendance Records</p>
            <p class="text-xs text-slate-400 mt-0.5">View attendance logs by date and course</p>
        </div>
        <i class="fas fa-chevron-right text-slate-300 group-hover:text-slate-500 ml-auto transition-colors"></i>
    </a>
</div>

</div>
@endsection
