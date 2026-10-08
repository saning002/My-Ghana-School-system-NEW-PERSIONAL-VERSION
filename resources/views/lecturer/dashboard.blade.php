@extends('layouts.app')

@section('title', 'Teacher Dashboard')
@section('subtitle', 'Your teaching hub — classes, scores, and attendance at a glance')

@push('head')
<style>
    .royal-card {
        background: #ffffff;
        border: 1px solid rgba(148,163,184,.15);
        border-radius: 1.35rem;
        box-shadow: 0 4px 28px rgba(61,49,176,.07), 0 1px 4px rgba(0,0,0,.04);
    }
    .course-card {
        position: relative;
        border-radius: 1.2rem;
        background: #ffffff;
        border: 1px solid rgba(148,163,184,.15);
        transition: transform .18s ease, box-shadow .18s ease;
        overflow: hidden;
    }
    .course-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 1.2rem;
        padding: 2px;
        background: linear-gradient(135deg, var(--cc-a), var(--cc-b));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }
    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 48px rgba(61,49,176,.14);
    }
    .gold-bar::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #f5c842, #d4a017);
        border-radius: 0 0 1.35rem 1.35rem;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

{{-- ══════════════════════════════════════════════════════════
     WELCOME SECTION — SIMPLE, CLEAN, MOBILE-FRIENDLY
══════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    {{-- Mobile header --}}
    <div class="lg:hidden p-5 pb-0">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-amber-500 to-yellow-500 flex items-center justify-center text-white shadow-md">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
                <h1 class="text-lg font-extrabold text-slate-900 leading-tight">Hi, {{ auth()->user()->full_name }}</h1>
                <p class="text-xs text-slate-500 font-medium">{{ now()->format('l, M j, Y') }}</p>
            </div>
        </div>
        @php $activePeriod = \App\Models\AcademicSession::activePeriod(); @endphp
        @if($activePeriod)
        <div class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-3 py-1.5 text-[11px] font-bold text-amber-700 mb-4">
            <i class="fas fa-calendar-check text-amber-500 text-[10px]"></i>
            {{ $activePeriod->full_label }}
        </div>
        @else
        <div class="inline-flex items-center gap-1.5 rounded-full bg-red-50 border border-red-200 px-3 py-1.5 text-[11px] font-bold text-red-700 mb-4">
            <i class="fas fa-exclamation-triangle text-red-500 text-[10px]"></i>
            No Active Period
        </div>
        @endif
    </div>

    {{-- Desktop header --}}
    <div class="hidden lg:flex items-center justify-between px-8 py-6 border-b border-slate-100">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-amber-500 to-yellow-500 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-chalkboard-teacher text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 leading-tight">Welcome back, {{ auth()->user()->full_name }} 👋</h1>
                <p class="text-sm text-slate-500 font-medium mt-0.5">{{ now()->format('l, F j, Y') }} — Your teaching dashboard</p>
            </div>
        </div>
        @php $activePeriod = \App\Models\AcademicSession::activePeriod(); @endphp
        @if($activePeriod)
        <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 border border-amber-200 px-4 py-2 text-sm font-bold text-amber-700">
            <i class="fas fa-calendar-check text-amber-500"></i>
            {{ $activePeriod->full_label }}
        </div>
        @endif
    </div>

    {{-- 4 Stat Cards — always visible, no indigo colors --}}
    <div class="px-5 lg:px-8 py-6 grid grid-cols-2 gap-3 lg:gap-4">
        @php
        $isClassTeacher = \App\Models\ClassTeacherAssignment::isClassTeacher(auth()->id() ?? 0);
        $classAssignments = \App\Models\ClassTeacherAssignment::where('lecturer_id', auth()->id())->get();
        $classProgramIds = $classAssignments->pluck('program_id');
        $todayAtt = \App\Models\Attendance::whereNull('course_id')
            ->whereIn('program_id', $classProgramIds)
            ->whereDate('date', today())
            ->where('status', 'present')
            ->count();
        $markSubs = $scoreDistribution->sum('count');
        $cards = [
            ['label' => 'Courses',           'value' => $assignedCourses->count(), 'icon' => 'fa-book-open',       'bg' => 'bg-emerald-50',      'iconBg' => 'bg-emerald-500',      'iconColor' => 'text-white',   'valueColor' => 'text-emerald-700',  'labelColor' => 'text-emerald-600'],
            ['label' => 'Class Register',    'value' => $attendanceTotals['class_register_total'] ?? 0, 'icon' => 'fa-users',            'bg' => 'bg-blue-50',          'iconBg' => 'bg-blue-500',          'iconColor' => 'text-white',   'valueColor' => 'text-blue-700',     'labelColor' => 'text-blue-600'],
            ['label' => "Today's Attendance",'value' => $todayAtt,                  'icon' => 'fa-calendar-check',  'bg' => 'bg-amber-50',         'iconBg' => 'bg-amber-500',         'iconColor' => 'text-white',   'valueColor' => 'text-amber-700',    'labelColor' => 'text-amber-600'],
            ['label' => 'Mark Submissions',  'value' => $markSubs,                  'icon' => 'fa-file-signature',  'bg' => 'bg-rose-50',          'iconBg' => 'bg-rose-500',          'iconColor' => 'text-white',   'valueColor' => 'text-rose-700',     'labelColor' => 'text-rose-600'],
        ];
        @endphp
        @foreach($cards as $c)
        <div class="rounded-2xl {{ $c['bg'] }} p-4 lg:p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 lg:w-11 lg:h-11 rounded-xl {{ $c['iconBg'] }} flex items-center justify-center shadow-sm">
                    <i class="fas {{ $c['icon'] }} {{ $c['iconColor'] }} text-sm lg:text-base"></i>
                </div>
            </div>
            <div class="text-2xl lg:text-3xl font-black {{ $c['valueColor'] }} leading-none mb-1.5">{{ $c['value'] }}</div>
            <div class="text-[11px] lg:text-xs font-bold uppercase tracking-wide {{ $c['labelColor'] }} leading-tight">{{ $c['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Action Buttons — maintain all 4, responsive layout --}}
    <div class="px-5 lg:px-8 pb-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
        @if(\App\Models\Setting::lecturerCan('attendance'))
        <a href="{{ route('lecturer.attendance.create') }}"
           class="flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 hover:bg-emerald-600 p-4 text-white text-sm font-bold shadow-md transition-all active:scale-95">
            <i class="fas fa-clipboard-check"></i>
            <span class="hidden sm:inline">Take Attendance</span>
            <span class="sm:hidden">Attendance</span>
        </a>
        @else
        <div class="rounded-2xl bg-slate-100 p-4 flex items-center justify-center gap-2 opacity-60">
            <i class="fas fa-clipboard-check text-slate-400"></i>
            <span class="text-xs font-bold text-slate-400 text-center">Attendance Disabled</span>
        </div>
        @endif
        
        @php $isClassTeacher = \App\Models\ClassTeacherAssignment::isClassTeacher(auth()->id() ?? 0); @endphp
        @if($isClassTeacher)
        <a href="{{ route('lecturer.class-register.index') }}"
           class="flex items-center justify-center gap-2 rounded-2xl bg-teal-500 hover:bg-teal-600 p-4 text-white text-sm font-bold shadow-md transition-all active:scale-95">
            <i class="fas fa-clipboard-list"></i>
            <span class="hidden sm:inline">Class Register</span>
            <span class="sm:hidden">Register</span>
        </a>
        @else
        <div class="rounded-2xl bg-slate-100 p-4 flex items-center justify-center gap-2 opacity-60">
            <i class="fas fa-clipboard-list text-slate-400"></i>
            <span class="text-xs font-bold text-slate-400 text-center">Not Class Teacher</span>
        </div>
        @endif
        
        @if(\App\Models\Setting::lecturerCan('exams'))
        <a href="{{ route('lecturer.exams.create') }}"
           class="flex items-center justify-center gap-2 rounded-2xl bg-amber-500 hover:bg-amber-600 p-4 text-white text-sm font-bold shadow-md transition-all active:scale-95">
            <i class="fas fa-pen-to-square"></i>
            <span class="hidden sm:inline">Enter Scores</span>
            <span class="sm:hidden">Scores</span>
        </a>
        @else
        <div class="rounded-2xl bg-slate-100 p-4 flex items-center justify-center gap-2 opacity-60">
            <i class="fas fa-pen-to-square text-slate-400"></i>
            <span class="text-xs font-bold text-slate-400 text-center">Scores Disabled</span>
        </div>
        @endif
        
        <a href="{{ route('lecturer.reports.index') }}"
           class="flex items-center justify-center gap-2 rounded-2xl bg-slate-700 hover:bg-slate-800 p-4 text-white text-sm font-bold shadow-md transition-all active:scale-95">
            <i class="fas fa-chart-bar"></i>
            <span class="hidden sm:inline">View Reports</span>
            <span class="sm:hidden">Reports</span>
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     CHARTS ROW
══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Attendance trend line --}}
    <div class="royal-card p-6 lg:col-span-2 gold-bar relative">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Attendance Trend</h3>
                <p class="text-xs text-slate-400 mt-0.5">Present vs absent — latest records</p>
            </div>
            <span class="flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Live
            </span>
        </div>
        <div class="h-60 w-full">
            <canvas id="lecturerAttendanceChart"></canvas>
        </div>
    </div>

    {{-- Student status doughnut --}}
    <div class="royal-card p-6 gold-bar relative">
        <div class="mb-5">
            <h3 class="text-base font-extrabold text-slate-900">Student Status</h3>
            <p class="text-xs text-slate-400 mt-0.5">Breakdown across your classes</p>
        </div>
        <div class="h-60 w-full flex items-center justify-center">
            @if(array_sum(array_values($studentStatusDistribution)) > 0)
                <canvas id="lecturerStatusChart"></canvas>
            @else
                <div class="text-center text-slate-300">
                    <i class="fas fa-chart-pie text-4xl mb-2"></i>
                    <p class="text-xs font-semibold">No student data yet</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     COURSE CARDS
══════════════════════════════════════════════════════════ --}}
<div>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Your Courses</h2>
            <p class="text-xs text-slate-500 mt-0.5">Quick-access to batch scores and attendance per class</p>
        </div>
        @if(\App\Models\Setting::lecturerCan('exams'))
        <a href="{{ route('lecturer.exams.index') }}" class="text-xs font-bold text-amber-600 hover:text-amber-800 transition-colors">All exams →</a>
        @endif
    </div>

    @php
    $palettes = [
        ['#d97706','#dc2626', 'from-amber-500 to-red-600',      'bg-amber-50 text-amber-800'],
        ['#059669','#0891b2', 'from-emerald-600 to-cyan-600',   'bg-emerald-50 text-emerald-800'],
        ['#db2777','#c026d3', 'from-pink-600 to-fuchsia-600',   'bg-pink-50 text-pink-800'],
        ['#2563eb','#0891b2', 'from-blue-600 to-cyan-600',      'bg-blue-50 text-blue-800'],
        ['#ea580c','#d97706', 'from-orange-600 to-amber-500',   'bg-orange-50 text-orange-800'],
        ['#0891b2','#0e7490', 'from-cyan-600 to-teal-700',      'bg-cyan-50 text-cyan-800'],
    ];
    @endphp

    @if($assignedCourses->isEmpty())
    <div class="royal-card p-14 text-center">
        <div class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 mb-4">
            <i class="fas fa-chalkboard-teacher text-2xl text-slate-300"></i>
        </div>
        <p class="text-sm font-semibold text-slate-500">No courses assigned yet.</p>
        <p class="text-xs text-slate-400 mt-1">Ask your admin to assign you to a course.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($assignedCourses as $i => $assignment)
        @php
            [$ca, $cb, $grad, $badge] = $palettes[$i % count($palettes)];
            $sCount = $students->filter(fn($s) => $s->program_id == $assignment->program_id)->count();
            $xCount = $scoreDistribution->get($assignment->course_id)['count'] ?? 0;
        @endphp
        <div class="course-card p-5 flex flex-col gap-4" style="--cc-a:{{ $ca }};--cc-b:{{ $cb }};">

            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $grad }} rounded-t-[1.2rem]"></div>

            <div class="flex items-start justify-between pt-1">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-white shadow-lg shrink-0">
                    <i class="fas fa-book text-base"></i>
                </div>
                <span class="rounded-full {{ $badge }} px-2.5 py-0.5 text-[11px] font-extrabold uppercase tracking-wider border border-current/10">
                    {{ $assignment->course->code ?? '—' }}
                </span>
            </div>

            <div>
                <h3 class="text-base font-extrabold text-slate-900 leading-snug">{{ $assignment->course->name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $assignment->program->name }} &bull; Year {{ $assignment->year }}</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                    <i class="fas fa-users text-slate-400 text-[10px]"></i>
                    <span class="font-bold text-slate-700">{{ $sCount }}</span> students
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                    <i class="fas fa-file-pen text-slate-400 text-[10px]"></i>
                    <span class="font-bold text-slate-700">{{ $xCount }}</span> scores
                </div>
            </div>

            <div class="flex gap-2 pt-1 border-t border-slate-100">
                @if(\App\Models\Setting::lecturerCan('exams'))
                <a href="{{ route('lecturer.courses.scores', ['courseId'=>$assignment->course_id,'programId'=>$assignment->program_id]) }}"
                   class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-br {{ $grad }} text-white text-xs font-bold px-3 py-2.5 shadow hover:opacity-90 transition-opacity active:scale-95">
                    <i class="fas fa-table-cells-large text-[10px]"></i> Batch Entry
                </a>
                @endif
                @if(\App\Models\Setting::lecturerCan('attendance') && $classProgramIds->contains($assignment->program_id))
                <a href="{{ route('lecturer.attendance.create') }}?assignment={{ $assignment->program_id }}"
                   class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-3 py-2.5 transition-colors active:scale-95">
                    <i class="fas fa-clipboard-check text-[10px]"></i> Attendance
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     BOTTOM ROW: Recent Attendance + Score Bars
══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 pb-4">

    {{-- Recent Attendance --}}
    <div class="royal-card p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Recent Attendance</h3>
                <p class="text-xs text-slate-400 mt-0.5">Latest entries across your classes</p>
            </div>
            @if(\App\Models\Setting::lecturerCan('attendance'))
            <a href="{{ route('lecturer.attendance.create') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-800">Record more →</a>
            @endif
        </div>

        @if($recentAttendances->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
            <i class="fas fa-calendar-xmark text-3xl text-slate-200 mb-2 block"></i>
            <p class="text-xs font-semibold text-slate-400">No attendance records yet.</p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($recentAttendances as $att)
            <div class="flex items-center gap-3 rounded-xl bg-slate-50 hover:bg-slate-100 px-4 py-3 transition-colors">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-white text-xs font-extrabold uppercase shadow">
                    {{ strtoupper(substr($att->student?->user?->full_name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ $att->student?->user?->full_name ?? 'Unknown' }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ $att->course?->code ?? '—' }} &bull; {{ $att->date ? \Carbon\Carbon::parse($att->date)->format('M d, Y') : '—' }}</p>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-extrabold
                    {{ $att->status === 'present'
                        ? 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200'
                        : 'bg-red-100 text-red-700 ring-1 ring-red-200' }}">
                    {{ ucfirst($att->status) }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Score Distribution --}}
    <div class="royal-card p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Score Distribution</h3>
                <p class="text-xs text-slate-400 mt-0.5">Scores recorded per course</p>
            </div>
            @if(\App\Models\Setting::lecturerCan('exams'))
            <a href="{{ route('lecturer.exams.index') }}" class="text-xs font-bold text-amber-600 hover:text-amber-800">Manage →</a>
            @endif
        </div>

        @if($scoreDistribution->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
            <i class="fas fa-file-circle-xmark text-3xl text-slate-200 mb-2 block"></i>
            <p class="text-xs font-semibold text-slate-400">No scores entered yet.</p>
        </div>
        @else
        <div class="space-y-3.5">
            @php
            $maxCount = $scoreDistribution->max('count') ?: 1;
            $barPalette = [
                'from-amber-400 to-orange-500',
                'from-emerald-500 to-teal-500',
                'from-pink-500 to-rose-500',
                'from-blue-500 to-cyan-500',
                'from-fuchsia-500 to-purple-600',
                'from-orange-500 to-red-500',
            ];
            @endphp
            @foreach($scoreDistribution as $cId => $dist)
            @php
                $pct = round(($dist['count'] / $maxCount) * 100);
                $bar = $barPalette[$loop->index % count($barPalette)];
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-xs font-bold text-slate-700 truncate max-w-[70%]">{{ $dist['course_name'] }}</p>
                    <span class="text-[11px] font-extrabold text-slate-500">{{ $dist['count'] }}</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r {{ $bar }} transition-all duration-700"
                         style="width:{{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

</div>{{-- /space-y-6 --}}
@endsection

@push('scripts')
<script>
(function () {
    const trend  = @json($attendanceTrend);
    const sLabels = @json(array_keys($studentStatusDistribution));
    const sValues = @json(array_values($studentStatusDistribution));

    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#64748b';

    const attEl = document.getElementById('lecturerAttendanceChart');
    if (attEl) {
        new Chart(attEl, {
            type: 'line',
            data: {
                labels: trend.map(r => r.day),
                datasets: [
                    {
                        label: 'Present',
                        data: trend.map(r => r.present),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.10)',
                        pointBackgroundColor: '#10b981',
                        pointRadius: 4, borderWidth: 2.5, tension: 0.38, fill: true,
                    },
                    {
                        label: 'Absent',
                        data: trend.map(r => r.absent),
                        borderColor: '#e11d48',
                        backgroundColor: 'rgba(225,29,72,0.07)',
                        pointBackgroundColor: '#e11d48',
                        pointRadius: 4, borderWidth: 2.5, tension: 0.38, fill: true,
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, pointStyleWidth: 8, font: { size: 11, weight: '700' } } },
                    tooltip: { backgroundColor: 'rgba(13,10,46,.92)', padding: 10, cornerRadius: 10 },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.5)' }, ticks: { stepSize: 1, font: { size: 10 } } },
                },
            },
        });
    }

    const statEl = document.getElementById('lecturerStatusChart');
    if (statEl) {
        new Chart(statEl, {
            type: 'doughnut',
            data: {
                labels: sLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                datasets: [{ data: sValues, backgroundColor: ['#10b981','#ea580c','#f59e0b','#e11d48','#8b5cf6','#0891b2'], borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, pointStyleWidth: 8, font: { size: 11, weight: '600' }, padding: 12 } },
                    tooltip: { backgroundColor: 'rgba(13,10,46,.92)', padding: 10, cornerRadius: 10 },
                },
            },
        });
    }
})();
</script>
@endpush
