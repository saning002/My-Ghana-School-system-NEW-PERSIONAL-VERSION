@extends('layouts.app')
@section('title','Reports')
@section('subtitle','Analytics, insights and exports')

@section('content')

<div class="page-hero-banner">
    <div class="hero-left">
        <h1><i class="fas fa-chart-bar mr-2" style="color:var(--primary);"></i>{{ $sidebarLabels['reports'] ?? 'Reports' }}</h1>
        <p>Analytics, insights and data exports for your school</p>
    </div>
</div>

{{-- Summary --}}
<div class="stats-grid mb-5">
    @foreach([
        ['label'=>'Total Students','value'=>$summary['total_students'],'icon'=>'fa-user-graduate','cls'=>'stat-icon-brand'],
        ['label'=>'Graduated',     'value'=>$summary['graduated'],     'icon'=>'fa-graduation-cap','cls'=>'stat-icon-blue'],
        ['label'=>'Attendance',    'value'=>$summary['attendance_rate'].'%','icon'=>'fa-clipboard-check','cls'=>'stat-icon-green'],
        ['label'=>'Outstanding',   'value'=>'GH₵ '.number_format($summary['fees']['outstanding'],0),'icon'=>'fa-coins','cls'=>'stat-icon-red'],
    ] as $c)
    <div class="stat-tile">
        <div class="st-icon {{ $c['cls'] }}"><i class="fas {{ $c['icon'] }}"></i></div>
        <p class="st-val">{{ $c['value'] }}</p>
        <p class="st-lbl">{{ $c['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Report Links --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
    @foreach([
        ['href'=>route('admin.reports.students'),   'icon'=>'fa-user-graduate',   'label'=>'Student Report',              'sub'=>'Filter by '.strtolower($programLabelSingular).', status, branch', 'cls'=>'stat-icon-brand'],
        ['href'=>route('admin.reports.attendance'),  'icon'=>'fa-clipboard-check', 'label'=>'Attendance Report',           'sub'=>'Per student & per course',          'cls'=>'stat-icon-green'],
        ['href'=>route('admin.reports.financial'),   'icon'=>'fa-coins',           'label'=>'Financial Report',            'sub'=>'Fees, payments & balances',         'cls'=>'stat-icon-red'],
        ['href'=>route('admin.reports.programs'),    'icon'=>'fa-graduation-cap',  'label'=>$programLabelSingular.' Performance','sub'=>'Enrollment & progression stats','cls'=>'stat-icon-blue'],
        ['href'=>route('admin.reports.courses'),     'icon'=>'fa-book-open',       'label'=>'Course Enrollment',           'sub'=>'Students per course',               'cls'=>'stat-icon-teal'],
        ['href'=>route('admin.reports.hub'),         'icon'=>'fa-chart-pie',       'label'=>'Reports Hub',                 'sub'=>'All reports in one place',          'cls'=>'stat-icon-purple'],
    ] as $r)
    <a href="{{ $r['href'] }}" class="card card-hover p-5 flex items-center gap-4 active:scale-95 transition-transform" style="text-decoration:none;">
        <div class="st-icon {{ $r['cls'] }} flex-shrink-0" style="width:48px;height:48px;border-radius:14px;">
            <i class="fas {{ $r['icon'] }} text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-ink">{{ $r['label'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $r['sub'] }}</p>
        </div>
        <i class="fas fa-chevron-right text-gray-300 flex-shrink-0 text-xs"></i>
    </a>
    @endforeach
</div>

{{-- Program Breakdown --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-5">
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">{{ $programLabelSingular }} Enrollment</h3>
                <p class="text-xs text-slate-500 mt-1">Enrolled vs graduated by {{ strtolower($programLabelSingular) }}</p>
            </div>
        </div>
        <div class="h-[320px]">
            <canvas id="reports-programs-chart"></canvas>
        </div>
    </div>
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Student Status Distribution</h3>
                <p class="text-xs text-slate-500 mt-1">Breakdown of all {{ $summary['total_students'] }} students by current status</p>
            </div>
        </div>
        <div class="h-[320px]">
            <canvas id="reports-fees-chart"></canvas>
        </div>
    </div>
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Course Enrollment Spread</h3>
                <p class="text-xs text-slate-500 mt-1">How many courses fall into each enrollment size bracket</p>
            </div>
        </div>
        <div class="h-[320px]">
            <canvas id="reports-courses-histogram"></canvas>
        </div>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-[var(--primary-pale)]" style="background:var(--primary-pale)">
        <h3 class="text-sm font-bold" style="color:var(--primary-text)">{{ $programLabelSingular }} Enrollment Breakdown</h3>
    </div>
    <div class="divide-y divide-[var(--primary-pale)]">
        @foreach($programStats as $stat)
        <div class="px-5 py-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-ink">{{ $stat['program']->name }}</p>
                <span class="text-xs font-bold" style="color:var(--primary-text)">{{ $stat['enrolled'] }} students</span>
            </div>
            @php $max = collect($programStats)->max('enrolled'); $pct = $max > 0 ? round(($stat['enrolled']/$max)*100) : 0; @endphp
            <div class="w-full bg-[var(--primary-pale)] rounded-full h-2 mb-2">
                <div class="h-2 rounded-full transition-all" style="width:{{ $pct }}%;background:var(--brand)"></div>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <span><i class="fas fa-book-open mr-1" style="color:var(--brand)"></i>{{ $stat['program']->courses->count() }} courses</span>
                <span><i class="fas fa-graduation-cap mr-1 text-blue-400"></i>{{ $stat['graduated'] }} graduated</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    const reportProgramLabels    = @json($programStats->pluck('program.name'));
    const reportProgramEnrolled  = @json($programStats->pluck('enrolled'));
    const reportProgramGraduated = @json($programStats->pluck('graduated'));
    const reportStatusLabels = @json($statusDistribution->keys()->map(function($s) { return ucfirst($s); })->values());
    const reportStatusCounts = @json($statusDistribution->values());
    const reportCourseBucketLabels = @json($courseEnrollmentBucketLabels);
    const reportCourseBuckets = @json($courseEnrollmentBucketCounts);
    const reportFees = @json([
        'Collected'   => $summary['fees']['collected'],
        'Outstanding' => $summary['fees']['outstanding'],
    ]);

    // Chart 1: Program Enrollment (enrolled vs graduated side-by-side)
    createChart(document.getElementById('reports-programs-chart'), {
        type: 'bar',
        data: {
            labels: reportProgramLabels,
            datasets: [
                {
                    label: 'Enrolled',
                    data: reportProgramEnrolled,
                    backgroundColor: 'rgba(37, 99, 235, 0.85)',
                    borderRadius: 8,
                },
                {
                    label: 'Graduated',
                    data: reportProgramGraduated,
                    backgroundColor: 'rgba(22, 163, 74, 0.75)',
                    borderRadius: 8,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { color: '#475569', usePointStyle: true } }
            },
            scales: {
                x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { color: '#64748b', stepSize: 1 }, grid: { color: 'rgba(148,163,184,0.15)' } }
            }
        }
    });

    // Chart 2: Student Status Distribution (doughnut – shows real data)
    createChart(document.getElementById('reports-fees-chart'), {
        type: 'doughnut',
        data: {
            labels: reportStatusLabels,
            datasets: [{
                data: reportStatusCounts,
                backgroundColor: [
                    '#2563eb', '#16a34a', '#d97706', '#dc2626', '#7c3aed', '#0891b2'
                ],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { color: '#475569', usePointStyle: true, padding: 12 } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                            const pct   = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });

    // Chart 3: Course Enrollment Histogram
    createChart(document.getElementById('reports-courses-histogram'), {
        type: 'bar',
        data: {
            labels: reportCourseBucketLabels,
            datasets: [{
                label: 'Courses',
                data: reportCourseBuckets,
                backgroundColor: [
                    'rgba(148,163,184,0.7)',
                    'rgba(245,158,11,0.8)',
                    'rgba(249,115,22,0.8)',
                    'rgba(239,68,68,0.8)',
                    'rgba(168,85,247,0.8)',
                ],
                borderRadius: 10,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { title: ctx => `${ctx[0].label} students enrolled`, label: ctx => ` ${ctx.parsed.y} course(s)` } }
            },
            scales: {
                x: { title: { display: true, text: 'Enrolled Students', color: '#94a3b8', font: { size: 10 } }, ticks: { color: '#64748b' }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { color: '#64748b', stepSize: 1 }, grid: { color: 'rgba(148,163,184,0.15)' } }
            }
        }
    });
</script>
@endpush
@endsection
