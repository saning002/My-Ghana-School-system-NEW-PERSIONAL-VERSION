extends('layouts.app')
@section('title','Reports Hub')
@section('subtitle','Generate, download and verify all school reports')

@section('content')
<div class="space-y-6">

@if(session('error'))
<div class="rounded-2xl bg-[var(--primary-pale)] border border-[var(--brand)] px-5 py-3 text-sm font-semibold text-[var(--primary-text)] flex items-center gap-2">
    <i class="fas fa-exclamation-triangle text-[var(--brand)]"></i> {{ session('error'?'val'=>$summary['total_students'], 'label'=>'Students',   'icon'=>'fa-user-graduate'],
                    ['val'=>$summary['total_programs'],  'label'=>'Programs',   'icon'=>'fa-graduation-cap'],
                    ['val'=>$summary['attendance_rate'].'%','label'=>'Attendance','icon'=>'fa-clipboard-check'],
                    ['val'=>'GH₵'.number_format($summary['fees']['outstanding']??0,0),'label'=>'Outstanding','icon'=>'fa-coins'],
                ] as $s)
                <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3 text-white">
                    <p class="text-xl font-extrabold leading-none">{{ $s['val'] }}</p>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-white/55 mt-1">{{ $s['label'?'title' => 'Academic Reports',
        'icon'  => 'fa-graduation-cap',
        'color' => 'from-indigo-500 to-violet-600',
        'badge' => 'bg-indigo-50 text-indigo-700',
        'items' => [
            ['href'=>route('admin.reports.student-academic'),    'icon'=>'fa-user-graduate', 'label'=>'Student Report Card',    'sub'=>'Per student, per program, with attendance & grade'],
            ['href'=>route('admin.reports.student-performance'), 'icon'=>'fa-chart-line',    'label'=>'Student Performance',    'sub'=>'Academic trend across programs and attempts'],
            ['href'=>route('admin.reports.class-performance'),   'icon'=>'fa-chart-bar',     'label'=>'Class Performance',      'sub'=>'Average, ranking, subject breakdown, pass/fail'],
            ['href'=>route('admin.reports.transcript'),          'icon'=>'fa-scroll',         'label'=>'Academic Transcript',    'sub'=>'Full academic history across all programs'],
            ['href'=>route('admin.exams.index'),                 'icon'=>'fa-file-alt',       'label'=>'Exam Scores',           'sub'=>'View and download individual exam records'],
        ],
    ],
    [
        'title' => 'Attendance Reports',
        'icon'  => 'fa-clipboard-check',
        'color' => 'from-emerald-500 to-teal-600',
        'badge' => 'bg-emerald-50 text-emerald-700',
        'items' => [
            ['href'=>route('admin.reports.attendance-summary'),'icon'=>'fa-calendar-check','label'=>'Attendance Summary',   'sub'=>'Present/absent per student per period'],
            ['href'=>route('admin.reports.attendance'),        'icon'=>'fa-table',          'label'=>'Attendance Log',       'sub'=>'Full attendance records with filters'],
        ],
    ],
    [
        'title' => 'Financial Reports',
        'icon'  => 'fa-coins',
        'color' => 'from-amber-500 to-orange-600',
        'badge' => 'bg-[var(--primary-pale)] text-[var(--primary-text)]',
        'items' => [
            ['href'=>route('admin.reports.fee-statement'), 'icon'=>'fa-file-invoice',  'label'=>'Student Fee Statement', 'sub'=>'Charges, payments, and balance per student'],
            ['href'=>route('admin.reports.financial'),     'icon'=>'fa-chart-pie',     'label'=>'Financial Overview',   'sub'=>'Collection summary, outstanding fees'],
        ],
    ],
    [
        'title' => 'Teacher Reports',
        'icon'  => 'fa-chalkboard-teacher',
        'color' => 'from-pink-500 to-rose-600',
        'badge' => 'bg-pink-50 text-pink-700',
        'items' => [
            ['href'=>route('admin.reports.mark-submission'),'icon'=>'fa-tasks',          'label'=>'Mark Submission Status','sub'=>'Who has submitted scores vs pending'],
        ],
    ],
    [
        'title' => 'Student Reports',
        'icon'  => 'fa-users',
        'color' => 'from-blue-500 to-cyan-600',
        'badge' => 'bg-blue-50 text-blue-700',
        'items' => [
            ['href'=>route('admin.reports.students'),  'icon'=>'fa-user-graduate', 'label'=>'Student List',       'sub'=>'Filter by program, status, branch'],
            ['href'=>route('admin.reports.programs'),  'icon'=>'fa-graduation-cap','label'=>'Program Enrollment', 'sub'=>'Enrollment stats per program'],
            ['href'=>route('admin.reports.courses'),   'icon'=>'fa-book-open',     'label'=>'Course Enrollment',  'sub'=>'Students per course breakdown'],
        ],
    ],
    [
        'title' => 'Documents & Audit',
        'icon'  => 'fa-shield-halved',
        'color' => 'from-slate-500 to-slate-700',
        'badge' => 'bg-slate-100 text-slate-700',
        'items' => [
            ['href'=>route('admin.reports.documents'), 'icon'=>'fa-history',       'label'=>'Document History',   'sub'=>'All generated documents with QR verification'],
        ],
    ],
];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($categories as $cat)
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        {{-- Category header --}}
        <div class="flex items-center gap-3 px-5 py-4 bg-gradient-to-r {{ $cat['color'] }}">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/20">
                <i class="fas {{ $cat['icon'] }} text-white text-sm"></i>
            </div>
            <h3 class="text-sm font-extrabold text-white">{{ $cat['title'] }}</h3>
        </div>
        {{-- Items --}}
        <div class="divide-y divide-slate-50">
            @foreach($cat['items'] as $item)
            <a href="{{ $item['href'] }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors group">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $cat['badge'] }}">
                    <i class="fas {{ $item['icon'] }} text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 leading-tight">{{ $item['label'] }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $item['sub'] }}</p>
                </div>
                <i class="fas fa-chevron-right text-slate-300 group-hover:text-slate-500 text-xs transition-colors shrink-0"></i>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

</div>
@endsection
