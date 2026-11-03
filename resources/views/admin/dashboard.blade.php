@extends('layouts.app')
@section('title','Dashboard')
@section('subtitle', ($schoolName ?? 'School') . ' – Admin Overview')

@push('styles')
<style>
/* ── Premium Dashboard Styles ─────────────────────────────── */
.dash-stat-card {
    position: relative;
    overflow: hidden;
    border-radius: 1.25rem;
    padding: 1.35rem 1.4rem;
    background: #fff;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.04);
    transition: transform .22s ease, box-shadow .22s ease;
    cursor: default;
}
.dash-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 32px rgba(0,0,0,.10);
}
.dash-stat-card .dsc-glow {
    position: absolute;
    width: 130px; height: 130px;
    border-radius: 50%;
    top: -35px; right: -35px;
    opacity: .12;
    filter: blur(28px);
    transition: opacity .3s;
}
.dash-stat-card:hover .dsc-glow { opacity: .22; }
.dash-stat-card .dsc-icon {
    width: 44px; height: 44px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    margin-bottom: .85rem;
    flex-shrink: 0;
}
.dash-stat-card .dsc-value {
    font-size: 1.9rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -.03em;
    color: #0f172a;
    margin-bottom: .3rem;
}
.dash-stat-card .dsc-label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #94a3b8;
}
.dash-stat-card .dsc-delta {
    display: inline-flex; align-items: center; gap: .28rem;
    font-size: .7rem; font-weight: 700;
    border-radius: 99px;
    padding: .18rem .6rem;
    margin-top: .5rem;
}
.dsc-delta.up   { background: #dcfce7; color: #16a34a; }
.dsc-delta.down { background: #fee2e2; color: #dc2626; }
.dsc-delta.neu  { background: #f1f5f9; color: #64748b; }

/* ── Chart cards ──────────────────────────────────────────── */
.chart-card {
    background: #fff;
    border-radius: 1.25rem;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}
.chart-card-header {
    padding: 1.25rem 1.4rem .9rem;
    display: flex; align-items: flex-start; justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
}
.chart-card-title {
    font-size: .88rem; font-weight: 800; color: #0f172a; letter-spacing: -.01em;
}
.chart-card-sub {
    font-size: .7rem; color: #94a3b8; margin-top: .2rem; font-weight: 500;
}
.chart-card-body { padding: 1.1rem 1.3rem 1.3rem; }

/* ── Fee progress card ────────────────────────────────────── */
.fee-card {
    border-radius: 1.25rem;
    padding: 1.3rem 1.4rem;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.04);
    background: #fff;
}
.fee-progress-track {
    height: 8px; border-radius: 99px; background: #f1f5f9;
    overflow: hidden; margin-top: .6rem;
}
.fee-progress-fill {
    height: 100%; border-radius: 99px;
    transition: width .8s cubic-bezier(.4,0,.2,1);
}

/* ── Quick actions ────────────────────────────────────────── */
.qa-btn {
    display: flex; flex-direction: column; align-items: center; gap: .65rem;
    padding: 1.1rem .8rem;
    border-radius: 1rem;
    border: 1.5px solid #f1f5f9;
    background: #fff;
    transition: all .22s ease;
    text-decoration: none;
}
.qa-btn:hover {
    border-color: var(--primary, #6366f1);
    box-shadow: 0 6px 24px rgba(99,102,241,.12);
    transform: translateY(-2px);
}
.qa-btn:active { transform: scale(.96); }
.qa-icon {
    width: 46px; height: 46px; border-radius: .875rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    transition: transform .22s ease;
}
.qa-btn:hover .qa-icon { transform: scale(1.1) rotate(-4deg); }
.qa-label {
    font-size: .72rem; font-weight: 700; color: #475569;
    text-align: center; line-height: 1.3;
}

/* ── Attendance trend card ────────────────────────────────── */
.att-card {
    background: linear-gradient(145deg,#0f0c29 0%,#1e1a5e 40%,#302b8f 70%,#5a4fcf 100%);
    border-radius: 1.25rem;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.07);
    box-shadow: 0 8px 32px rgba(15,12,41,.35);
}
.att-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    background: rgba(52,211,153,.15);
    border: 1px solid rgba(52,211,153,.3);
    border-radius: 99px;
    padding: .22rem .75rem;
    font-size: .65rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .1em;
    color: #6ee7b7;
}
.att-stat-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .3rem .8rem;
    border-radius: .6rem;
    background: rgba(255,255,255,.08);
    font-size: .7rem; font-weight: 700;
}
.att-chart-wrap {
    margin: 0 .9rem .9rem;
    background: rgba(255,255,255,.96);
    border-radius: .875rem;
    padding: .85rem 1rem;
}

/* ── Activities feed ──────────────────────────────────────── */
.act-feed-item {
    display: flex; align-items: center; gap: .9rem;
    padding: .85rem 1.4rem;
    border-bottom: 1px solid #f8fafc;
    transition: background .15s;
}
.act-feed-item:last-child { border-bottom: none; }
.act-feed-item:hover { background: #f8fafc; }
.act-icon-wrap {
    width: 38px; height: 38px; border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: .82rem;
}
.act-title { font-size: .78rem; font-weight: 700; color: #1e293b; }
.act-detail { font-size: .68rem; color: #94a3b8; margin-top: .15rem; }
.act-badge {
    font-size: .6rem; font-weight: 800;
    padding: .15rem .55rem; border-radius: 99px;
    text-transform: uppercase; letter-spacing: .05em;
}
.act-time { font-size: .65rem; color: #cbd5e1; font-weight: 600; white-space: nowrap; }

/* ── Donut legend ─────────────────────────────────────────── */
.donut-legend-item {
    display: flex; align-items: center; gap: .5rem;
    font-size: .73rem; font-weight: 600; color: #475569;
}
.donut-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

/* ── Section heading ──────────────────────────────────────── */
.section-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    background: var(--primary-pale, #eef2ff);
    color: var(--primary, #6366f1);
    border-radius: 99px;
    padding: .2rem .75rem;
    font-size: .65rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .1em;
}
</style>
@endpush

@section('content')

{{-- ── 1. STAT TILES ─────────────────────────────────────────────────────── --}}
@php
$lecturerCount = (!isset($stats['total_lecturers']) || $stats['total_lecturers'] == 0)
    ? App\Models\User::query()
        ->where(fn($q) => $q->whereIn('role',['lecturer','lecturers','teacher'])
            ->orWhere('role','like','%lecturer%')->orWhere('role','like','%teacher%'))
        ->count()
    : $stats['total_lecturers'];

$collectionPct = ($fees['billed'] ?? 0) > 0
    ? min(100, round(($fees['collected'] / $fees['billed']) * 100))
    : 0;

$statCards = [
    [
        'label'  => $sidebarLabels['students'] ?? 'Active Students',
        'value'  => number_format($stats['active_students'] ?? 0),
        'icon'   => 'fa-user-graduate',
        'glow'   => '#6366f1',
        'ibg'    => '#eef2ff',
        'iclr'   => '#6366f1',
        'delta'  => ($stats['active_students'] ?? 0) . ' enrolled',
        'dclass' => 'neu',
    ],
    [
        'label'  => $sidebarLabels['teachers'] ?? 'Teachers',
        'value'  => number_format((int)($lecturerCount ?? 0)),
        'icon'   => 'fa-chalkboard-teacher',
        'glow'   => '#0891b2',
        'ibg'    => '#ecfeff',
        'iclr'   => '#0891b2',
        'delta'  => 'Staff members',
        'dclass' => 'neu',
    ],
    [
        'label'  => $sidebarLabels['programs'] ?? 'Programs',
        'value'  => number_format($stats['total_programs'] ?? 0),
        'icon'   => 'fa-graduation-cap',
        'glow'   => '#7c3aed',
        'ibg'    => '#f5f3ff',
        'iclr'   => '#7c3aed',
        'delta'  => 'Active programs',
        'dclass' => 'neu',
    ],
    [
        'label'  => $sidebarLabels['courses'] ?? 'Courses',
        'value'  => number_format($stats['total_courses'] ?? 0),
        'icon'   => 'fa-book-open',
        'glow'   => '#059669',
        'ibg'    => '#ecfdf5',
        'iclr'   => '#059669',
        'delta'  => 'Running courses',
        'dclass' => 'neu',
    ],
    [
        'label'  => $sidebarLabels['attendance'] ?? 'Attendance Rate',
        'value'  => ($attendanceRate ?? 0) . '%',
        'icon'   => 'fa-clipboard-check',
        'glow'   => ($attendanceRate ?? 0) >= 80 ? '#16a34a' : '#f59e0b',
        'ibg'    => ($attendanceRate ?? 0) >= 80 ? '#f0fdf4' : '#fffbeb',
        'iclr'   => ($attendanceRate ?? 0) >= 80 ? '#16a34a' : '#d97706',
        'delta'  => ($attendanceRate ?? 0) >= 80 ? 'Good standing' : 'Needs attention',
        'dclass' => ($attendanceRate ?? 0) >= 80 ? 'up' : 'down',
    ],
];
@endphp

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @foreach($statCards as $c)
    <div class="dash-stat-card">
        <div class="dsc-glow" style="background:{{ $c['glow'] }}"></div>
        <div class="dsc-icon" style="background:{{ $c['ibg'] }};color:{{ $c['iclr'] }}">
            <i class="fas {{ $c['icon'] }}"></i>
        </div>
        <div class="dsc-value">{{ $c['value'] }}</div>
        <div class="dsc-label">{{ $c['label'] }}</div>
        <div class="dsc-delta {{ $c['dclass'] }}">
            <i class="fas {{ $c['dclass'] === 'up' ? 'fa-arrow-up' : ($c['dclass'] === 'down' ? 'fa-arrow-down' : 'fa-minus') }} text-[8px]"></i>
            {{ $c['delta'] }}
        </div>
    </div>
    @endforeach
</div>

{{-- ── Storage warning ──────────────────────────────────────────────────── --}}
@if (!($storageHealth['public_disk_writable'] ?? false) || !($storageHealth['uploads_writable'] ?? false))
<div class="rounded-xl border-l-4 border-red-500 bg-red-50 p-4 mb-6 flex items-start gap-3">
    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
    <div>
        <p class="font-bold text-red-800 text-sm">Storage health issue detected</p>
        <p class="text-xs text-red-600 mt-1">One or more storage directories are not writable. File uploads may fail.</p>
        <ul class="text-xs text-red-600 mt-2 space-y-0.5 list-disc list-inside">
            <li>Public disk: {{ $storageHealth['public_disk_path'] ?? 'N/A' }} — {{ $storageHealth['public_disk_writable'] ? 'OK' : 'Not writable' }}</li>
            <li>Uploads: {{ $storageHealth['uploads_path'] ?? 'N/A' }} — {{ $storageHealth['uploads_writable'] ? 'OK' : 'Not writable' }}</li>
        </ul>
    </div>
</div>
@endif

{{-- ── 2. FEES SUMMARY ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    {{-- Total Billed --}}
    <div class="fee-card" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%)">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fas fa-file-invoice-dollar text-white text-base"></i>
            </div>
            <p class="text-xs font-bold uppercase tracking-widest text-white/70">Total Billed</p>
        </div>
        <p class="text-2xl font-extrabold text-white tracking-tight">GH₵ {{ number_format($fees['billed'],2) }}</p>
        <div class="fee-progress-track mt-3" style="background:rgba(255,255,255,.2)">
            <div class="fee-progress-fill" style="width:100%;background:rgba(255,255,255,.6)"></div>
        </div>
        <p class="text-[10px] text-white/60 mt-1.5 font-semibold">Total fees expected</p>
    </div>

    {{-- Collected --}}
    <div class="fee-card" style="background:linear-gradient(135deg,#11998e 0%,#38ef7d 100%)">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fas fa-wallet text-white text-base"></i>
            </div>
            <p class="text-xs font-bold uppercase tracking-widest text-white/70">Collected</p>
        </div>
        <p class="text-2xl font-extrabold text-white tracking-tight">GH₵ {{ number_format($fees['collected'],2) }}</p>
        <div class="fee-progress-track mt-3" style="background:rgba(255,255,255,.2)">
            <div class="fee-progress-fill" id="fee-collected-bar" style="width:0%;background:rgba(255,255,255,.75)"></div>
        </div>
        <p class="text-[10px] text-white/60 mt-1.5 font-semibold" id="fee-collection-pct">{{ $collectionPct }}% of total collected</p>
    </div>

    {{-- Outstanding --}}
    <div class="fee-card" style="background:linear-gradient(135deg,#f7971e 0%,#ffd200 100%)">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fas fa-exclamation-circle text-white text-base"></i>
            </div>
            <p class="text-xs font-bold uppercase tracking-widest text-white/70">Outstanding</p>
        </div>
        <p class="text-2xl font-extrabold text-white tracking-tight">GH₵ {{ number_format($fees['outstanding'],2) }}</p>
        <div class="fee-progress-track mt-3" style="background:rgba(255,255,255,.2)">
            @php $outPct = ($fees['billed'] ?? 0) > 0 ? min(100, round(($fees['outstanding'] / $fees['billed']) * 100)) : 0; @endphp
            <div class="fee-progress-fill" style="width:{{ $outPct }}%;background:rgba(255,255,255,.75)"></div>
        </div>
        <p class="text-[10px] text-white/60 mt-1.5 font-semibold">{{ $outPct }}% unpaid</p>
    </div>
</div>

{{-- ── 3. QUICK ACTIONS ─────────────────────────────────────────────────── --}}
<div class="chart-card mb-6">
    <div class="chart-card-header">
        <div>
            <p class="chart-card-title">Quick Actions</p>
            <p class="chart-card-sub">Frequently used operations — one click away</p>
        </div>
        <span class="section-pill"><i class="fas fa-bolt"></i> Fast Access</span>
    </div>
    <div class="chart-card-body">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['href'=>route('admin.students.create'),      'icon'=>'fa-user-plus',        'label'=>'Add Student',     'ibg'=>'#ede9fe','iclr'=>'#7c3aed'],
                ['href'=>route('admin.exams.create'),         'icon'=>'fa-file-alt',         'label'=>'Enter Scores',    'ibg'=>'#dbeafe','iclr'=>'#1d4ed8'],
                ['href'=>route('admin.fees.index'),           'icon'=>'fa-coins',            'label'=>'Record Payment',  'ibg'=>'#dcfce7','iclr'=>'#15803d'],
                ['href'=>route('admin.attendance.create'),    'icon'=>'fa-clipboard-check',  'label'=>'Mark Attendance', 'ibg'=>'#fce7f3','iclr'=>'#9d174d'],
            ] as $a)
            <a href="{{ $a['href'] }}" class="qa-btn">
                <div class="qa-icon" style="background:{{ $a['ibg'] }};color:{{ $a['iclr'] }}">
                    <i class="fas {{ $a['icon'] }}"></i>
                </div>
                <span class="qa-label">{{ $a['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- ── 4. CHARTS ROW ────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- 4a. Enrollment by Program — horizontal bar --}}
    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <p class="chart-card-title">Enrollment by Program</p>
                <p class="chart-card-sub">Students per program</p>
            </div>
            <span class="section-pill"><i class="fas fa-graduation-cap"></i> Programs</span>
        </div>
        <div class="chart-card-body">
            <div style="height:300px">
                <canvas id="dash-prog-chart"></canvas>
            </div>
        </div>
    </div>

    {{-- 4b. Student Status Donut --}}
    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <p class="chart-card-title">Student Status</p>
                <p class="chart-card-sub">Distribution by enrolment status</p>
            </div>
            <span class="section-pill"><i class="fas fa-chart-pie"></i> Status</span>
        </div>
        <div class="chart-card-body">
            {{-- Donut + center label --}}
            <div class="relative flex items-center justify-center" style="height:210px">
                <canvas id="dash-status-chart"></canvas>
                <div class="absolute text-center pointer-events-none" id="donut-center">
                    <p class="text-2xl font-extrabold text-slate-800" id="donut-total">–</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total</p>
                </div>
            </div>
            {{-- Custom legend --}}
            <div class="flex flex-wrap gap-x-4 gap-y-2 mt-3 px-1" id="donut-legend"></div>
        </div>
    </div>

    {{-- 4c. Attendance Trend — dark premium card --}}
    <div class="att-card">
        <div class="px-5 pt-5 pb-2 flex items-start justify-between">
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-indigo-300 mb-1">14-Day Overview</p>
                <h3 class="text-sm font-extrabold text-white">Attendance Trend</h3>
                <p class="text-[10px] text-indigo-300 mt-0.5">Present vs Absent records</p>
            </div>
            <span class="att-badge">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Live
            </span>
        </div>
        <div class="px-5 pb-3 flex gap-3 mt-1">
            <div class="att-stat-pill">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="text-emerald-200 text-[11px]">Present: <strong id="att-total-p">–</strong></span>
            </div>
            <div class="att-stat-pill">
                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                <span class="text-rose-200 text-[11px]">Absent: <strong id="att-total-a">–</strong></span>
            </div>
        </div>
        <div class="att-chart-wrap" style="height:200px">
            <canvas id="dash-att-chart"></canvas>
        </div>
    </div>
</div>

{{-- ── 5. RECENT ACTIVITIES FEED ────────────────────────────────────────── --}}
<div class="chart-card">
    <div class="chart-card-header">
        <div>
            <p class="chart-card-title">Recent Activity</p>
            <p class="chart-card-sub">Latest actions across the system</p>
        </div>
        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider bg-slate-50 px-3 py-1.5 rounded-lg">
            {{ $recentActivities->count() }} recent
        </span>
    </div>

    <div class="divide-y divide-slate-50 max-h-[480px] overflow-y-auto">
        @forelse($recentActivities as $idx => $a)
        <div class="act-feed-item">
            {{-- Left: icon --}}
            <div class="act-icon-wrap" style="background:{{ $a['bg'] }}">
                <i class="fas {{ $a['icon'] }}" style="color:{{ $a['color'] }}"></i>
            </div>

            {{-- Middle: text --}}
            <div class="flex-1 min-w-0">
                <p class="act-title truncate">{{ $a['title'] }}</p>
                <p class="act-detail truncate">{{ $a['detail'] }}</p>
            </div>

            {{-- Right: badge + time --}}
            <div class="flex flex-col items-end gap-1 flex-shrink-0 ml-2">
                @if($a['badge'])
                <span class="act-badge" style="background:{{ $a['bg'] }};color:{{ $a['color'] }}">{{ $a['badge'] }}</span>
                @endif
                <span class="act-time">{{ $a['time']->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="py-14 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-bolt text-2xl text-slate-300"></i>
            </div>
            <p class="text-sm font-semibold text-slate-400">No activity yet</p>
            <p class="text-xs text-slate-300 mt-1">Actions will appear here as the system is used</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
(function() {
    // ── Data from controller ──────────────────────────────────────────────
    const progData    = @json($programEnrollment->map(fn($p) => ['label' => $p->name, 'value' => $p->students_count]));
    const statusData  = @json($statusDistribution);
    const attData     = @json($attendanceTrend);

    const progLabels  = progData.map(d => d.label);
    const progCounts  = progData.map(d => d.value);
    const attLabels   = attData.map(d => d.day);
    const presentArr  = attData.map(d => d.present);
    const absentArr   = attData.map(d => d.absent);

    Chart.defaults.font.family = "'Poppins', 'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    const COLORS = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#a855f7','#14b8a6','#f97316'];

    // ── Helpers ───────────────────────────────────────────────────────────
    function makeGrad(ctx, chart, r, g, b) {
        const {chartArea} = chart;
        if (!chartArea) return `rgba(${r},${g},${b},0.15)`;
        const grad = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        grad.addColorStop(0, `rgba(${r},${g},${b},0.28)`);
        grad.addColorStop(1, `rgba(${r},${g},${b},0.02)`);
        return grad;
    }

    const tooltipDefaults = {
        backgroundColor: 'rgba(15,23,42,.92)',
        titleColor: '#e2e8f0',
        bodyColor: '#94a3b8',
        padding: 12,
        cornerRadius: 10,
        borderColor: 'rgba(255,255,255,.06)',
        borderWidth: 1,
        caretSize: 5,
    };

    // ── 1. Horizontal Bar — Enrollment by Program ─────────────────────────
    new Chart(document.getElementById('dash-prog-chart'), {
        type: 'bar',
        data: {
            labels: progLabels,
            datasets: [{
                label: 'Students',
                data: progCounts,
                backgroundColor: progLabels.map((_, i) => COLORS[i % COLORS.length] + 'cc'),
                borderColor:     progLabels.map((_, i) => COLORS[i % COLORS.length]),
                borderWidth: 1.5,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',          // horizontal bars — program names readable
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipDefaults,
                    callbacks: { label: ctx => `  ${ctx.parsed.x} students` }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,.1)', drawBorder: false },
                    ticks: { color: '#94a3b8', font: { size: 10 }, stepSize: 1 }
                },
                y: {
                    grid: { display: false },
                    ticks: {
                        color: '#475569',
                        font: { size: 11, weight: '600' },
                        callback: (val, i) => {
                            const lbl = progLabels[i] ?? '';
                            return lbl.length > 16 ? lbl.slice(0,15) + '…' : lbl;
                        }
                    }
                }
            }
        }
    });

    // ── 2. Donut — Student Status ─────────────────────────────────────────
    const statusLabels = Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1));
    const statusValues = Object.values(statusData);
    const totalStudents = statusValues.reduce((a,b) => a+b, 0);

    document.getElementById('donut-total').textContent = totalStudents.toLocaleString();

    // Build custom legend
    const legendEl = document.getElementById('donut-legend');
    statusLabels.forEach((lbl, i) => {
        legendEl.innerHTML += `
            <div class="donut-legend-item">
                <span class="donut-dot" style="background:${COLORS[i % COLORS.length]}"></span>
                <span>${lbl}</span>
                <span class="ml-auto font-bold text-slate-700">${statusValues[i]}</span>
            </div>`;
    });

    new Chart(document.getElementById('dash-status-chart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                backgroundColor: COLORS.slice(0, statusLabels.length),
                borderWidth: 3,
                borderColor: '#fff',
                hoverBorderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '76%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipDefaults,
                    callbacks: {
                        label: ctx => `  ${ctx.label}: ${ctx.parsed} students (${totalStudents > 0 ? Math.round(ctx.parsed/totalStudents*100) : 0}%)`
                    }
                }
            }
        }
    });

    // ── 3. Area Line — Attendance Trend ──────────────────────────────────
    const attCtx = document.getElementById('dash-att-chart');
    const attChart = new Chart(attCtx, {
        type: 'line',
        data: {
            labels: attLabels,
            datasets: [
                {
                    label: 'Present',
                    data: presentArr,
                    borderColor: '#10b981',
                    backgroundColor(ctx) { return makeGrad(attCtx.getContext('2d'), ctx.chart, 16,185,129); },
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    borderWidth: 2.5,
                    tension: 0.45,
                    fill: true,
                },
                {
                    label: 'Absent',
                    data: absentArr,
                    borderColor: '#f43f5e',
                    backgroundColor(ctx) { return makeGrad(attCtx.getContext('2d'), ctx.chart, 244,63,94); },
                    pointBackgroundColor: '#f43f5e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    borderWidth: 2.5,
                    tension: 0.45,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(13,10,46,.94)',
                    titleColor: '#a5b4fc',
                    bodyColor: '#e0e7ff',
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => `  ${ctx.dataset.label}: ${ctx.parsed.y}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { size: 9 }, maxRotation: 0 }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#64748b', font: { size: 9 }, stepSize: 1 },
                    grid: { color: 'rgba(148,163,184,.1)', drawBorder: false }
                }
            }
        }
    });

    // Fill totals strip
    const tp = presentArr.reduce((a,b) => a+b, 0);
    const ta = absentArr.reduce((a,b) => a+b, 0);
    document.getElementById('att-total-p').textContent = tp.toLocaleString();
    document.getElementById('att-total-a').textContent = ta.toLocaleString();

    // Animate fee progress bar
    const feeBar = document.getElementById('fee-collected-bar');
    if (feeBar) setTimeout(() => { feeBar.style.width = '{{ $collectionPct }}%'; }, 300);
})();
</script>
@endpush
@endsection
