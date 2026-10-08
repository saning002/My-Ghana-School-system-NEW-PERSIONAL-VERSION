@extends('layouts.app')
@section('title','Dashboard')
@section('subtitle', ($schoolName ?? 'School') . ' — Admin Overview')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @php
    if (!isset($stats['total_lecturers']) || $stats['total_lecturers'] == 0) {
        $lecturerCount = App\Models\User::query()
            ->where(function($q) {
                $q->whereIn('role', ['lecturer', 'lecturers', 'teacher'])
                  ->orWhere('role', 'like', '%lecturer%')
                  ->orWhere('role', 'like', '%teacher%');
            })
            ->count();
    } else {
        $lecturerCount = $stats['total_lecturers'];
    }
    $cards = [
        ['label'=> $sidebarLabels['students'] ?? 'Students',    'value'=>$stats['active_students'] ?? 0, 'icon'=>'fa-user-graduate',      'bg'=>'#fef9c3', 'ic'=>'#92680a'],
        ['label'=> $sidebarLabels['teachers'] ?? 'Teachers',    'value'=>number_format((int) ($lecturerCount ?? 0)), 'icon'=>'fa-chalkboard-teacher', 'bg'=>'#dcfce7', 'ic'=>'#166534'],
        ['label'=> $sidebarLabels['programs'] ?? 'Programs',    'value'=>$stats['total_programs'] ?? 0,  'icon'=>'fa-graduation-cap',     'bg'=>'#dbeafe', 'ic'=>'#1e40af'],
        ['label'=> $sidebarLabels['courses']  ?? 'Courses',     'value'=>$stats['total_courses'] ?? 0,   'icon'=>'fa-book-open',          'bg'=>'#fce7f3', 'ic'=>'#9d174d'],
        ['label'=> $sidebarLabels['attendance'] ?? 'Attendance','value'=>($attendanceRate ?? 0).'%',      'icon'=>'fa-clipboard-check',    'bg'=>'#f3e8ff', 'ic'=>'#6b21a8'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="card card-hover p-5 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-20 group-hover:scale-150 transition-transform duration-300 blur-2xl" style="background:{{ $c['ic'] }}"></div>
        <div class="w-11 h-11 rounded-2xl flex items-center justify-center mb-3 shadow-sm" style="background:{{ $c['bg'] }}">
            <i class="fas {{ $c['icon'] }} text-lg" style="color:{{ $c['ic'] }}"></i>
        </div>
        <p class="text-3xl font-extrabold text-slate-800 tracking-tight">{{ $c['value'] }}</p>
        <p class="text-[11px] text-slate-500 mt-1.5 font-semibold uppercase tracking-wider">{{ $c['label'] }}</p>
    </div>
    @endforeach
</div>

@if (!($storageHealth['public_disk_writable'] ?? false) || !($storageHealth['uploads_writable'] ?? false))
<div class="card border-l-4 border-red-500 bg-red-50 p-5 mb-6">
    <div class="flex items-start gap-4">
        <div class="text-red-600 text-xl mt-1"><i class="fas fa-exclamation-triangle"></i></div>
        <div>
            <p class="font-bold text-red-800">Storage health issue detected</p>
            <p class="text-sm text-red-700 mt-1">The application cannot write files to one or more storage locations. Please verify permissions for the paths below:</p>
            <ul class="text-sm text-red-700 mt-3 space-y-1 list-disc list-inside">
                <li><strong>Public disk:</strong> {{ $storageHealth['public_disk_path'] ?? 'N/A' }} — {{ $storageHealth['public_disk_writable'] ? 'Writable' : 'Not writable' }}</li>
                <li><strong>Fallback uploads:</strong> {{ $storageHealth['uploads_path'] ?? 'N/A' }} — {{ $storageHealth['uploads_writable'] ? 'Writable' : 'Not writable' }}</li>
            </ul>
            <p class="text-sm text-red-700 mt-3">Fix permissions for these directories or contact your server administrator before uploading student photos.</p>
        </div>
    </div>
</div>
@endif

{{-- Fees Summary --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="card p-5 relative overflow-hidden group">
        <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNENEEwMTciLz48L3N2Zz4=')]"></div>
        <div class="absolute inset-0 bg-yellow-50/70"></div>
        <div class="relative z-10 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-yellow-100 text-yellow-700 shadow-sm"><i class="fas fa-file-invoice-dollar text-xl"></i></div>
            <div>
                <p class="text-xs text-slate-500 mb-0.5 font-bold uppercase tracking-wider">Total Billed</p>
                <p class="text-2xl font-extrabold text-slate-800 tracking-tight">GH₵ {{ number_format($fees['billed'],2) }}</p>
            </div>
        </div>
    </div>
    <div class="card p-5 relative overflow-hidden group">
        <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiMxNmEzNGEiLz48L3N2Zz4=')]"></div>
        <div class="absolute inset-0 bg-green-50/70"></div>
        <div class="relative z-10 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-green-100 text-green-700 shadow-sm"><i class="fas fa-wallet text-xl"></i></div>
            <div>
                <p class="text-xs text-slate-500 mb-0.5 font-bold uppercase tracking-wider">Collected</p>
                <p class="text-2xl font-extrabold text-green-700 tracking-tight">GH₵ {{ number_format($fees['collected'],2) }}</p>
            </div>
        </div>
    </div>
    <div class="card p-5 relative overflow-hidden group">
        <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNkYzI2MjYiLz48L3N2Zz4=')]"></div>
        <div class="absolute inset-0 bg-red-50/70"></div>
        <div class="relative z-10 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-red-100 text-red-600 shadow-sm"><i class="fas fa-exclamation-circle text-xl"></i></div>
            <div>
                <p class="text-xs text-slate-500 mb-0.5 font-bold uppercase tracking-wider">Outstanding</p>
                <p class="text-2xl font-extrabold text-red-600 tracking-tight">GH₵ {{ number_format($fees['outstanding'],2) }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="card p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Quick Actions</h3>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['href'=>route('admin.students.create'), 'icon'=>'fa-user-plus',   'label'=>'Add Student',  'bg'=>'#fef9c3','ic'=>'#78520a'],
            ['href'=>route('admin.exams.create'),    'icon'=>'fa-file-alt',    'label'=>'Enter Scores', 'bg'=>'#dbeafe','ic'=>'#1e40af'],
            ['href'=>route('admin.fees.index'),      'icon'=>'fa-coins',       'label'=>'Record Payment','bg'=>'#dcfce7','ic'=>'#166534'],
            ['href'=>route('admin.attendance.create'),'icon'=>'fa-clipboard-check','label'=>'Mark Attendance','bg'=>'#fce7f3','ic'=>'#9d174d'],
        ] as $a)
        <a href="{{ $a['href'] }}" class="group rounded-2xl p-4 flex flex-col items-center gap-3 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] active:scale-95 border border-slate-100" style="background:#fff">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-sm" style="background:{{ $a['bg'] }}">
                <i class="fas {{ $a['icon'] }} text-lg" style="color:{{ $a['ic'] }}"></i>
            </div>
            <span class="text-[13px] font-bold text-slate-700 tracking-tight text-center leading-tight group-hover:text-slate-900">{{ $a['label'] }}</span>
        </a>
        @endforeach
    </div>
</div>

{{-- Dashboard Analytics --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Enrollment by Program</h3>
                <p class="text-xs text-slate-500 mt-1">Current student counts by program</p>
            </div>
        </div>
        <div class="h-[320px]">
            <canvas id="dashboard-programs-chart"></canvas>
        </div>
    </div>

    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Student Status Distribution</h3>
                <p class="text-xs text-slate-500 mt-1">Active, graduated, suspended and more</p>
            </div>
        </div>
        <div class="h-[320px]">
            <canvas id="dashboard-status-chart"></canvas>
        </div>
    </div>

    {{-- Attendance Trend — styled like teacher portal --}}
    <div class="overflow-hidden rounded-2xl" style="background:linear-gradient(135deg,#0d0a2e 0%,#1a1550 25%,#2d2480 55%,#5647d6 80%,#7c6ee8 100%)">
        {{-- Header --}}
        <div class="px-5 pt-5 pb-3 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-300 mb-0.5">Live Data</p>
                <h3 class="text-sm font-extrabold text-white">Attendance Trend</h3>
                <p class="text-[11px] text-indigo-200 mt-0.5">Present vs absent — recent records</p>
            </div>
            <span class="flex items-center gap-1.5 rounded-full border border-emerald-400/40 bg-emerald-500/20 px-3 py-1 text-[10px] font-extrabold text-emerald-300 uppercase tracking-wider">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Live
            </span>
        </div>
        {{-- Stats strip --}}
        <div class="px-5 pb-3 flex gap-4">
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 inline-block"></span>
                <span class="text-[11px] font-bold text-emerald-200">Present: <span id="admin-total-present">—</span></span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>
                <span class="text-[11px] font-bold text-red-300">Absent: <span id="admin-total-absent">—</span></span>
            </div>
        </div>
        {{-- Chart on white background panel --}}
        <div class="mx-3 mb-3 rounded-xl bg-white/95 p-4" style="height:220px">
            <canvas id="dashboard-attendance-chart"></canvas>
        </div>
    </div>
</div>

{{-- Recent Students + Attendance --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
            <h3 class="text-[15px] font-extrabold text-slate-800 tracking-tight">Recent Students</h3>
            <a href="{{ route('admin.students.index') }}" class="text-xs font-bold transition-colors hover:text-yellow-600" style="color:#D4A017">View all →</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentStudents as $s)
            <a href="{{ route('admin.students.show',$s) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/80 transition-all duration-200">
                <div class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-[0_2px_10px_rgba(212,160,23,0.2)]">
                    @if($s->photo)
                        <img src="{{ filter_var($s->photo, FILTER_VALIDATE_URL) ? $s->photo : route('admin.students.photo', $s) }}" class="w-full h-full object-cover" alt="{{ $s->user?->full_name ?? '—' }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-yellow-600 text-white font-bold text-sm">
                            {{ strtoupper(substr($s->user?->full_name??'?',0,1)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[14px] font-bold text-slate-800 truncate tracking-tight">{{ $s->user?->full_name ?? '—' }}</p>
                    <p class="text-[12px] text-slate-500 truncate mt-0.5 font-medium">{{ $s->program?->name??'—' }}</p>
                </div>
                <span class="text-[11px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider flex-shrink-0
                    {{ $s->status==='active'?'bg-green-50 text-green-700 ring-1 ring-green-600/20':($s->status==='graduated'?'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20':($s->status==='manifestation'?'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20':'bg-red-50 text-red-700 ring-1 ring-red-600/20')) }}">
                    {{ ucfirst($s->status) }}
                </span>
            </a>
            @empty
            <div class="px-6 py-10 text-center text-slate-400 text-sm font-medium">No students yet.</div>
            @endforelse
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
            <h3 class="text-[15px] font-extrabold text-slate-800 tracking-tight">Recent Attendance</h3>
            <a href="{{ route('admin.attendance.index') }}" class="text-xs font-bold transition-colors hover:text-yellow-600" style="color:#D4A017">View all →</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentAttendances as $att)
            <div class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/80 transition-all duration-200">
                <div class="w-10 h-10 rounded-2xl {{ $att->status==='present'?'bg-green-50 text-green-600':'bg-red-50 text-red-500' }} flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fas {{ $att->status==='present'?'fa-check':'fa-times' }} text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[14px] font-bold text-slate-800 truncate tracking-tight">{{ $att->student?->user?->full_name??'—' }}</p>
                    <p class="text-[12px] text-slate-500 mt-0.5 font-medium">{{ $att->course?->code??'—' }} · {{ \Carbon\Carbon::parse($att->date)->format('M d') }}</p>
                </div>
                <span class="text-[11px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider flex-shrink-0 {{ $att->status==='present'?'bg-green-50 text-green-700 ring-1 ring-green-600/20':'bg-red-50 text-red-700 ring-1 ring-red-600/20' }}">
                    {{ ucfirst($att->status) }}
                </span>
            </div>
            @empty
            <div class="px-6 py-10 text-center text-slate-400 text-sm font-medium">No records yet.</div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    const dashboardPrograms = @json($programEnrollment->map(fn($p) => ['label' => $p->name, 'value' => $p->students_count]));
    const dashboardStatus = @json($statusDistribution);
    const dashboardAttendance = @json($attendanceTrend);

    const programLabels = dashboardPrograms.map(item => item.label);
    const programCounts = dashboardPrograms.map(item => item.value);

    const attendanceLabels = dashboardAttendance.map(item => item.day);
    const presentCounts = dashboardAttendance.map(item => item.present);
    const absentCounts = dashboardAttendance.map(item => item.absent);

    function createChart(context, config) {
        if (!context) return;
        new Chart(context, config);
    }

    Chart.defaults.font.family = 'Poppins';
    Chart.defaults.color = '#64748b';

    createChart(document.getElementById('dashboard-programs-chart'), {
        type: 'bar',
        data: {
            labels: programLabels,
            datasets: [{
                label: 'Students',
                data: programCounts,
                backgroundColor: ['#2563eb', '#0B1121', '#D4A017', '#16a34a', '#7c3aed', '#0891b2'],
                borderRadius: 10,
                borderSkipped: false,
                barPercentage: 0.8,
                categoryPercentage: 0.75,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#0B1121', titleColor: '#fff', bodyColor: '#fff', callbacks: { label: ctx => `${ctx.parsed.y} students` } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b' } },
                y: { beginAtZero: true, ticks: { color: '#64748b' }, grid: { color: 'rgba(148,163,184,0.15)' } }
            }
        }
    });

    createChart(document.getElementById('dashboard-status-chart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(dashboardStatus).map(status => status.charAt(0).toUpperCase() + status.slice(1)),
            datasets: [{
                data: Object.values(dashboardStatus),
                backgroundColor: ['#16a34a', '#2563eb', '#D4A017', '#ef4444', '#7c3aed'],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { position: 'bottom', labels: { color: '#475569', padding: 16 } },
                tooltip: { backgroundColor: '#0B1121', titleColor: '#fff', bodyColor: '#fff', callbacks: { label: ctx => `${ctx.label}: ${ctx.parsed} students` } }
            }
        }
    });

    createChart(document.getElementById('dashboard-attendance-chart'), {
        type: 'line',
        data: {
            labels: attendanceLabels,
            datasets: [
                {
                    label: 'Present',
                    data: presentCounts,
                    borderColor: '#10b981',
                    backgroundColor: function(ctx) {
                        const chart = ctx.chart;
                        const {ctx: c, chartArea} = chart;
                        if (!chartArea) return 'rgba(16,185,129,0.12)';
                        const grad = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        grad.addColorStop(0, 'rgba(16,185,129,0.28)');
                        grad.addColorStop(1, 'rgba(16,185,129,0.02)');
                        return grad;
                    },
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2.5,
                    tension: 0.42,
                    fill: true,
                },
                {
                    label: 'Absent',
                    data: absentCounts,
                    borderColor: '#f43f5e',
                    backgroundColor: function(ctx) {
                        const chart = ctx.chart;
                        const {ctx: c, chartArea} = chart;
                        if (!chartArea) return 'rgba(244,63,94,0.08)';
                        const grad = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        grad.addColorStop(0, 'rgba(244,63,94,0.20)');
                        grad.addColorStop(1, 'rgba(244,63,94,0.01)');
                        return grad;
                    },
                    pointBackgroundColor: '#f43f5e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2.5,
                    tension: 0.42,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: 'rgba(13,10,46,0.92)',
                    titleColor: '#a5b4fc',
                    bodyColor: '#e0e7ff',
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#64748b', font: { size: 10 }, stepSize: 1 },
                    grid: { color: 'rgba(148,163,184,0.12)' }
                }
            }
        }
    });

    // Update totals strip
    const tp = presentCounts.reduce((a,b)=>a+b,0);
    const ta = absentCounts.reduce((a,b)=>a+b,0);
    const elP = document.getElementById('admin-total-present');
    const elA = document.getElementById('admin-total-absent');
    if (elP) elP.textContent = tp;
    if (elA) elA.textContent = ta;
</script>
@endpush
@endsection
