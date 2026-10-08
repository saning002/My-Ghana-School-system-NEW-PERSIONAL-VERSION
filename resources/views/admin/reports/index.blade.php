@extends('layouts.app')
@section('title','Reports')
@section('subtitle','Analytics, insights and exports')

@section('content')

{{-- Summary --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    @foreach([
        ['label'=>'Total Students','value'=>$summary['total_students'],'icon'=>'fa-user-graduate','bg'=>'#fef9c3','ic'=>'#78520a'],
        ['label'=>'Graduated',     'value'=>$summary['graduated'],     'icon'=>'fa-graduation-cap','bg'=>'#dbeafe','ic'=>'#1e40af'],
        ['label'=>'Attendance',    'value'=>$summary['attendance_rate'].'%','icon'=>'fa-clipboard-check','bg'=>'#dcfce7','ic'=>'#166534'],
        ['label'=>'Outstanding',   'value'=>'GH₵ '.number_format($summary['fees']['outstanding'],0),'icon'=>'fa-coins','bg'=>'#fce7f3','ic'=>'#9d174d'],
    ] as $c)
    <div class="card p-4">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style="background:{{ $c['bg'] }}">
            <i class="fas {{ $c['icon'] }}" style="color:{{ $c['ic'] }}"></i>
        </div>
        <p class="text-2xl font-bold text-ink">{{ $c['value'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5 font-medium">{{ $c['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Report Links --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
    @foreach([
        ['href'=>route('admin.reports.students'),   'icon'=>'fa-user-graduate',   'label'=>'Student Report',     'sub'=>'Filter by '.strtolower($programLabelSingular).', status, branch', 'bg'=>'#fef9c3','ic'=>'#78520a'],
        ['href'=>route('admin.reports.attendance'),  'icon'=>'fa-clipboard-check', 'label'=>'Attendance Report',  'sub'=>'Per student & per course',          'bg'=>'#dcfce7','ic'=>'#166534'],
        ['href'=>route('admin.reports.financial'),   'icon'=>'fa-coins',           'label'=>'Financial Report',   'sub'=>'Fees, payments & balances',         'bg'=>'#fce7f3','ic'=>'#9d174d'],
        ['href'=>route('admin.reports.programs'),    'icon'=>'fa-graduation-cap',  'label'=>$programLabelSingular.' Performance','sub'=>'Enrollment & progression stats',    'bg'=>'#dbeafe','ic'=>'#1e40af'],
        ['href'=>route('admin.reports.courses'),     'icon'=>'fa-book-open',       'label'=>'Course Enrollment',  'sub'=>'Students per course',               'bg'=>'#fef3c7','ic'=>'#92400e'],
    ] as $r)
    <a href="{{ $r['href'] }}" class="card card-hover p-5 flex items-center gap-4 active:scale-95 transition-transform">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:{{ $r['ic'] }}">
            <i class="fas {{ $r['icon'] }} text-white text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-ink">{{ $r['label'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $r['sub'] }}</p>
        </div>
        <i class="fas fa-chevron-right text-gray-300 flex-shrink-0"></i>
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
    <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
        <h3 class="text-sm font-bold" style="color:#78520a">{{ $programLabelSingular }} Enrollment Breakdown</h3>
    </div>
    <div class="divide-y divide-yellow-50">
        @foreach($programStats as $stat)
        <div class="px-5 py-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-ink">{{ $stat['program']->name }}</p>
                <span class="text-xs font-bold" style="color:#78520a">{{ $stat['enrolled'] }} students</span>
            </div>
            @php $max = collect($programStats)->max('enrolled'); $pct = $max > 0 ? round(($stat['enrolled']/$max)*100) : 0; @endphp
            <div class="w-full bg-yellow-100 rounded-full h-2 mb-2">
                <div class="h-2 rounded-full transition-all" style="width:{{ $pct }}%;background:#D4A017"></div>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <span><i class="fas fa-book-open mr-1" style="color:#D4A017"></i>{{ $stat['program']->courses->count() }} courses</span>
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

    // Chart 2: Student Status Distribution (doughnut — shows real data)
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
