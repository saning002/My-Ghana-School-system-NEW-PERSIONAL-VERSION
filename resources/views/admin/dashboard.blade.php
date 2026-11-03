@extends('layouts.app')
@section('title','Dashboard')
@section('subtitle', ($schoolName ?? 'School') . ' – Admin Overview')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   SUPER-PREMIUM DASHBOARD  —  Ghana School Admin
   ═══════════════════════════════════════════════════════════ */

/* ── Greeting banner ──────────────────────────────────────── */
.dash-hero {
    background: linear-gradient(120deg,#1e1b4b 0%,#312e81 40%,#4338ca 70%,#6366f1 100%);
    border-radius: 1.5rem;
    padding: 2rem 2.2rem;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
    box-shadow: 0 20px 60px rgba(99,102,241,.35);
}
.dash-hero::before {
    content:'';
    position:absolute; inset:0;
    background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.dash-hero-orb1 {
    position:absolute; width:260px; height:260px; border-radius:50%;
    background:rgba(165,180,252,.12); filter:blur(50px);
    right:-60px; top:-60px; pointer-events:none;
}
.dash-hero-orb2 {
    position:absolute; width:160px; height:160px; border-radius:50%;
    background:rgba(52,211,153,.1); filter:blur(35px);
    left:20%; bottom:-40px; pointer-events:none;
}

/* ── KPI stat tile ────────────────────────────────────────── */
.kpi-tile {
    background:#fff;
    border-radius:1.25rem;
    padding:1.5rem 1.6rem;
    border:1px solid rgba(0,0,0,.055);
    box-shadow:0 2px 8px rgba(0,0,0,.04), 0 0 0 0 transparent;
    position:relative; overflow:hidden;
    transition:transform .22s ease, box-shadow .22s ease;
    cursor:default;
}
.kpi-tile:hover {
    transform:translateY(-4px);
    box-shadow:0 16px 48px rgba(0,0,0,.10);
}
.kpi-tile-shine {
    position:absolute; inset:0;
    background:linear-gradient(135deg,rgba(255,255,255,.6) 0%,transparent 60%);
    border-radius:inherit; pointer-events:none;
}
.kpi-glow {
    position:absolute; width:150px; height:150px;
    border-radius:50%; top:-45px; right:-45px;
    filter:blur(36px); opacity:.13; pointer-events:none;
    transition:opacity .3s;
}
.kpi-tile:hover .kpi-glow { opacity:.22; }
.kpi-icon {
    width:48px; height:48px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.15rem; margin-bottom:1rem; flex-shrink:0;
}
.kpi-val {
    font-size:2.1rem; font-weight:900;
    letter-spacing:-.04em; line-height:1;
    color:#0f172a; margin-bottom:.3rem;
}
.kpi-lbl {
    font-size:.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.08em; color:#94a3b8;
}
.kpi-hint {
    margin-top:.65rem;
    display:inline-flex; align-items:center; gap:.3rem;
    font-size:.7rem; font-weight:700;
    border-radius:99px; padding:.2rem .65rem;
}
.kpi-hint.good  { background:#dcfce7; color:#15803d; }
.kpi-hint.warn  { background:#fef3c7; color:#b45309; }
.kpi-hint.bad   { background:#fee2e2; color:#dc2626; }
.kpi-hint.info  { background:#f1f5f9; color:#475569; }

/* ── Section card ─────────────────────────────────────────── */
.s-card {
    background:#fff;
    border-radius:1.25rem;
    border:1px solid rgba(0,0,0,.055);
    box-shadow:0 2px 8px rgba(0,0,0,.04);
    overflow:hidden;
}
.s-card-hd {
    padding:1.3rem 1.6rem 1rem;
    border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between; gap:.75rem;
}
.s-card-title { font-size:.95rem; font-weight:800; color:#0f172a; letter-spacing:-.015em; }
.s-card-sub   { font-size:.72rem; color:#94a3b8; margin-top:.2rem; font-weight:500; }
.s-card-body  { padding:1.4rem 1.6rem 1.6rem; }
.s-pill {
    display:inline-flex; align-items:center; gap:.35rem;
    border-radius:99px; padding:.22rem .85rem;
    font-size:.65rem; font-weight:800;
    text-transform:uppercase; letter-spacing:.09em;
    white-space:nowrap; flex-shrink:0;
    background:var(--primary-pale,#eef2ff);
    color:var(--primary,#6366f1);
}

/* ── Fee meter card ────────────────────────────────────────── */
.fee-meter-card {
    border-radius:1.25rem;
    padding:1.6rem 1.7rem;
    position:relative; overflow:hidden;
    border:1px solid rgba(255,255,255,.15);
    box-shadow:0 8px 32px rgba(0,0,0,.14);
    color:#fff;
}
.fee-meter-icon {
    width:44px; height:44px; border-radius:13px;
    background:rgba(255,255,255,.18);
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; margin-bottom:1rem;
    backdrop-filter:blur(6px);
}
.fee-meter-amount {
    font-size:1.9rem; font-weight:900;
    letter-spacing:-.035em; line-height:1.1;
}
.fee-meter-label {
    font-size:.68rem; font-weight:800;
    text-transform:uppercase; letter-spacing:.09em;
    opacity:.65; margin-bottom:.85rem;
}
.fee-track {
    height:9px; border-radius:99px;
    background:rgba(255,255,255,.2); overflow:hidden; margin-top:.7rem;
}
.fee-fill {
    height:100%; border-radius:99px;
    background:rgba(255,255,255,.75);
    transition:width .9s cubic-bezier(.4,0,.2,1);
}
.fee-caption { font-size:.68rem; opacity:.6; margin-top:.45rem; font-weight:600; }

/* ── Quick actions ────────────────────────────────────────── */
.qa-tile {
    display:flex; flex-direction:column; align-items:center; gap:.7rem;
    padding:1.25rem 1rem;
    border-radius:1.1rem; border:1.5px solid #f1f5f9;
    background:#fff; cursor:pointer; text-decoration:none;
    transition:all .22s ease;
}
.qa-tile:hover {
    border-color:var(--primary,#6366f1);
    box-shadow:0 8px 32px rgba(99,102,241,.13);
    transform:translateY(-3px);
}
.qa-tile:active { transform:scale(.95); }
.qa-tile-icon {
    width:50px; height:50px; border-radius:1rem;
    display:flex; align-items:center; justify-content:center;
    font-size:1.2rem;
    transition:transform .22s ease;
}
.qa-tile:hover .qa-tile-icon { transform:scale(1.12) rotate(-5deg); }
.qa-tile-lbl {
    font-size:.73rem; font-weight:700;
    color:#374151; text-align:center; line-height:1.35;
}
.qa-tile-sub {
    font-size:.63rem; color:#9ca3af; font-weight:500; text-align:center;
}

/* ── Attendance dark card ─────────────────────────────────── */
.att-dark-card {
    background:linear-gradient(150deg,#0a0720 0%,#150e40 35%,#1e1872 65%,#3730a3 100%);
    border-radius:1.25rem; overflow:hidden;
    border:1px solid rgba(255,255,255,.06);
    box-shadow:0 20px 60px rgba(10,7,32,.5);
}
.att-live-badge {
    display:inline-flex; align-items:center; gap:.4rem;
    background:rgba(52,211,153,.14);
    border:1px solid rgba(52,211,153,.35);
    border-radius:99px;
    padding:.25rem .85rem;
    font-size:.63rem; font-weight:900;
    text-transform:uppercase; letter-spacing:.12em;
    color:#6ee7b7;
}
.att-pill {
    display:inline-flex; align-items:center; gap:.45rem;
    padding:.35rem .9rem; border-radius:.65rem;
    background:rgba(255,255,255,.09);
    font-size:.7rem; font-weight:700;
}
.att-chart-panel {
    margin:0 1rem 1rem;
    background:rgba(255,255,255,.97);
    border-radius:1rem; padding:.9rem 1rem;
}

/* ── Activity feed ────────────────────────────────────────── */
.feed-row {
    display:flex; align-items:center; gap:1rem;
    padding:.95rem 1.6rem;
    border-bottom:1px solid #f8fafc;
    transition:background .15s;
}
.feed-row:last-child { border-bottom:none; }
.feed-row:hover { background:#fafafa; }
.feed-icon {
    width:40px; height:40px; border-radius:.875rem;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; font-size:.85rem;
}
.feed-title { font-size:.8rem; font-weight:700; color:#1e293b; }
.feed-detail { font-size:.69rem; color:#94a3b8; margin-top:.12rem; font-weight:500; }
.feed-badge {
    font-size:.6rem; font-weight:800;
    padding:.15rem .6rem; border-radius:99px;
    text-transform:uppercase; letter-spacing:.05em;
    white-space:nowrap;
}
.feed-time { font-size:.65rem; color:#cbd5e1; font-weight:600; white-space:nowrap; }

/* ── Responsive chart heights ─────────────────────────────── */
.chart-h-md  { height:340px; }
.chart-h-lg  { height:400px; }
.chart-h-sm  { height:220px; }
@media(max-width:768px) {
    .chart-h-md  { height:260px; }
    .chart-h-lg  { height:300px; }
    .chart-h-sm  { height:180px; }
    .dash-hero   { padding:1.35rem 1.25rem; }
    .kpi-val     { font-size:1.6rem; }
    .fee-meter-amount { font-size:1.5rem; }
}
</style>
@endpush

@section('content')
@php
/* ─── PHP setup ────────────────────────────────────────────── */
$lecturerCount = (!isset($stats['total_lecturers']) || $stats['total_lecturers'] == 0)
    ? App\Models\User::query()
        ->where(fn($q) => $q->whereIn('role',['lecturer','lecturers','teacher'])
            ->orWhere('role','like','%lecturer%')->orWhere('role','like','%teacher%'))
        ->count()
    : $stats['total_lecturers'];

$billed      = (float)($fees['billed']      ?? 0);
$collected   = (float)($fees['collected']   ?? 0);
$outstanding = (float)($fees['outstanding'] ?? 0);
$collPct     = $billed > 0 ? min(100, round(($collected / $billed) * 100)) : 0;
$outPct      = $billed > 0 ? min(100, round(($outstanding / $billed) * 100)) : 0;
$rate        = (int)($attendanceRate ?? 0);
$hour        = now()->hour;
$greeting    = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$adminName   = auth()->user()->full_name ?? 'Administrator';
@endphp

{{-- ══════════════════════════════════════════════════════════
     HERO GREETING BANNER
     ══════════════════════════════════════════════════════════ --}}
<div class="dash-hero mb-6">
    <div class="dash-hero-orb1"></div>
    <div class="dash-hero-orb2"></div>
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-indigo-300 text-xs font-bold uppercase tracking-widest mb-1">
                <i class="fas fa-calendar-day mr-1"></i>{{ now()->format('l, d F Y') }}
            </p>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                {{ $greeting }}, {{ explode(' ', $adminName)[0] }} 👋
            </h1>
            <p class="text-indigo-200 text-sm mt-1.5 font-medium">
                Here is what is happening at <strong class="text-white">{{ $schoolName ?? 'your school' }}</strong> today.
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.students.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-white text-sm font-bold transition-all backdrop-blur-sm">
                <i class="fas fa-user-plus text-xs"></i> Add Student
            </a>
            <a href="{{ route('admin.fees.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500/80 hover:bg-emerald-500 border border-emerald-400/40 text-white text-sm font-bold transition-all">
                <i class="fas fa-coins text-xs"></i> Record Payment
            </a>
        </div>
    </div>
</div>

{{-- ── Storage warning ─────────────────────────────────────── --}}
@if (!($storageHealth['public_disk_writable'] ?? false) || !($storageHealth['uploads_writable'] ?? false))
<div class="rounded-2xl border-l-4 border-red-500 bg-red-50 p-4 mb-6 flex items-start gap-3">
    <div class="w-9 h-9 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-exclamation-triangle text-red-500"></i>
    </div>
    <div>
        <p class="font-bold text-red-800 text-sm">File upload folder is not writable</p>
        <p class="text-xs text-red-600 mt-1">Student photos and documents may not upload correctly. Contact your server administrator.</p>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     KPI STAT TILES  (5 cards)
     ══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">

    {{-- Active Students --}}
    <div class="kpi-tile">
        <div class="kpi-tile-shine"></div>
        <div class="kpi-glow" style="background:#6366f1"></div>
        <div class="kpi-icon" style="background:#eef2ff;color:#6366f1">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="kpi-val">{{ number_format($stats['active_students'] ?? 0) }}</div>
        <div class="kpi-lbl">{{ $sidebarLabels['students'] ?? 'Active Students' }}</div>
        <div class="kpi-hint info">
            <i class="fas fa-users" style="font-size:.6rem"></i>
            {{ number_format($stats['total_students'] ?? 0) }} total enrolled
        </div>
    </div>

    {{-- Teachers --}}
    <div class="kpi-tile">
        <div class="kpi-tile-shine"></div>
        <div class="kpi-glow" style="background:#0891b2"></div>
        <div class="kpi-icon" style="background:#ecfeff;color:#0891b2">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="kpi-val">{{ number_format((int)($lecturerCount ?? 0)) }}</div>
        <div class="kpi-lbl">{{ $sidebarLabels['teachers'] ?? 'Teaching Staff' }}</div>
        <div class="kpi-hint info">
            <i class="fas fa-id-badge" style="font-size:.6rem"></i>
            On the staff list
        </div>
    </div>

    {{-- Programs --}}
    <div class="kpi-tile">
        <div class="kpi-tile-shine"></div>
        <div class="kpi-glow" style="background:#7c3aed"></div>
        <div class="kpi-icon" style="background:#f5f3ff;color:#7c3aed">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="kpi-val">{{ number_format($stats['total_programs'] ?? 0) }}</div>
        <div class="kpi-lbl">{{ $sidebarLabels['programs'] ?? 'Programmes' }}</div>
        <div class="kpi-hint info">
            <i class="fas fa-list-ul" style="font-size:.6rem"></i>
            {{ number_format($stats['total_courses'] ?? 0) }} courses running
        </div>
    </div>

    {{-- Fee Collection Rate --}}
    <div class="kpi-tile">
        <div class="kpi-tile-shine"></div>
        <div class="kpi-glow" style="background:{{ $collPct >= 75 ? '#16a34a' : ($collPct >= 50 ? '#f59e0b' : '#ef4444') }}"></div>
        <div class="kpi-icon" style="background:{{ $collPct >= 75 ? '#f0fdf4' : ($collPct >= 50 ? '#fffbeb' : '#fef2f2') }};color:{{ $collPct >= 75 ? '#16a34a' : ($collPct >= 50 ? '#d97706' : '#dc2626') }}">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div class="kpi-val">{{ $collPct }}%</div>
        <div class="kpi-lbl">Fees Collected</div>
        <div class="kpi-hint {{ $collPct >= 75 ? 'good' : ($collPct >= 50 ? 'warn' : 'bad') }}">
            <i class="fas {{ $collPct >= 75 ? 'fa-check-circle' : 'fa-clock' }}" style="font-size:.6rem"></i>
            {{ $collPct >= 75 ? 'On track' : ($collPct >= 50 ? 'Needs follow-up' : 'Critical — many unpaid') }}
        </div>
    </div>

    {{-- Attendance Rate --}}
    <div class="kpi-tile">
        <div class="kpi-tile-shine"></div>
        <div class="kpi-glow" style="background:{{ $rate >= 80 ? '#16a34a' : ($rate >= 60 ? '#f59e0b' : '#ef4444') }}"></div>
        <div class="kpi-icon" style="background:{{ $rate >= 80 ? '#f0fdf4' : ($rate >= 60 ? '#fffbeb' : '#fef2f2') }};color:{{ $rate >= 80 ? '#16a34a' : ($rate >= 60 ? '#d97706' : '#dc2626') }}">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <div class="kpi-val">{{ $rate }}%</div>
        <div class="kpi-lbl">Attendance Rate</div>
        <div class="kpi-hint {{ $rate >= 80 ? 'good' : ($rate >= 60 ? 'warn' : 'bad') }}">
            <i class="fas {{ $rate >= 80 ? 'fa-thumbs-up' : 'fa-exclamation' }}" style="font-size:.6rem"></i>
            {{ $rate >= 80 ? 'Students attending well' : ($rate >= 60 ? 'Moderate — monitor closely' : 'Poor — urgent action needed') }}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     FEES OVERVIEW  (3 gradient cards + collection meter)
     ══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- Total Expected --}}
    <div class="fee-meter-card" style="background:linear-gradient(140deg,#4f46e5 0%,#7c3aed 100%)">
        <div class="fee-meter-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="fee-meter-label">Total Fees Expected</div>
        <div class="fee-meter-amount">GH₵ {{ number_format($billed, 2) }}</div>
        <div class="fee-track">
            <div class="fee-fill" style="width:100%"></div>
        </div>
        <div class="fee-caption">All fees billed to students this term</div>
    </div>

    {{-- Collected --}}
    <div class="fee-meter-card" style="background:linear-gradient(140deg,#059669 0%,#10b981 100%)">
        <div class="fee-meter-icon"><i class="fas fa-wallet"></i></div>
        <div class="fee-meter-label">Amount Collected</div>
        <div class="fee-meter-amount">GH₵ {{ number_format($collected, 2) }}</div>
        <div class="fee-track">
            <div class="fee-fill" id="fee-bar-collected" style="width:0%"></div>
        </div>
        <div class="fee-caption">
            <strong class="text-white/90">{{ $collPct }}%</strong> of total collected so far
        </div>
    </div>

    {{-- Outstanding --}}
    <div class="fee-meter-card" style="background:linear-gradient(140deg,#d97706 0%,#f59e0b 100%)">
        <div class="fee-meter-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="fee-meter-label">Still Outstanding</div>
        <div class="fee-meter-amount">GH₵ {{ number_format($outstanding, 2) }}</div>
        <div class="fee-track">
            <div class="fee-fill" id="fee-bar-outstanding" style="width:0%"></div>
        </div>
        <div class="fee-caption">
            <strong class="text-white/90">{{ $outPct }}%</strong> of fees not yet paid
            @if($outstanding > 0) — follow up with parents @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     QUICK ACTIONS
     ══════════════════════════════════════════════════════════ --}}
<div class="s-card mb-6">
    <div class="s-card-hd">
        <div>
            <p class="s-card-title">Quick Actions</p>
            <p class="s-card-sub">The most common tasks — tap to go straight there</p>
        </div>
        <span class="s-pill"><i class="fas fa-bolt"></i> Shortcuts</span>
    </div>
    <div class="s-card-body">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach([
                ['href'=>route('admin.students.create'),    'icon'=>'fa-user-plus',       'ibg'=>'#ede9fe','ic'=>'#7c3aed','label'=>'Admit Student',   'sub'=>'Register new student'],
                ['href'=>route('admin.exams.create'),       'icon'=>'fa-file-alt',        'ibg'=>'#dbeafe','ic'=>'#1d4ed8','label'=>'Enter Scores',    'sub'=>'Input exam & SBA marks'],
                ['href'=>route('admin.fees.index'),         'icon'=>'fa-coins',           'ibg'=>'#dcfce7','ic'=>'#15803d','label'=>'Record Payment',  'sub'=>'Log fee payment received'],
                ['href'=>route('admin.attendance.create'),  'icon'=>'fa-clipboard-check', 'ibg'=>'#fce7f3','ic'=>'#9d174d','label'=>'Mark Attendance', 'sub'=>'Take register for class'],
            ] as $qa)
            <a href="{{ $qa['href'] }}" class="qa-tile">
                <div class="qa-tile-icon" style="background:{{ $qa['ibg'] }};color:{{ $qa['ic'] }}">
                    <i class="fas {{ $qa['icon'] }}"></i>
                </div>
                <div>
                    <div class="qa-tile-lbl">{{ $qa['label'] }}</div>
                    <div class="qa-tile-sub">{{ $qa['sub'] }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     CHARTS  ROW 1  —  Enrolment (wide) + Status Donut
     ══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-4">

    {{-- Enrolment by Programme — takes 3/5 width --}}
    <div class="s-card lg:col-span-3">
        <div class="s-card-hd">
            <div>
                <p class="s-card-title">Students Per Programme</p>
                <p class="s-card-sub">How many students are in each programme right now</p>
            </div>
            <span class="s-pill"><i class="fas fa-graduation-cap"></i> Enrolment</span>
        </div>
        <div class="s-card-body">
            <div class="chart-h-lg">
                <canvas id="chart-enrolment"></canvas>
            </div>
        </div>
    </div>

    {{-- Student Status Donut — takes 2/5 width --}}
    <div class="s-card lg:col-span-2">
        <div class="s-card-hd">
            <div>
                <p class="s-card-title">Student Status Breakdown</p>
                <p class="s-card-sub">Active, deferred, graduated & more</p>
            </div>
            <span class="s-pill"><i class="fas fa-chart-pie"></i> Status</span>
        </div>
        <div class="s-card-body">
            {{-- Donut with center number --}}
            <div class="relative flex items-center justify-center" style="height:240px">
                <canvas id="chart-status"></canvas>
                <div class="absolute text-center pointer-events-none">
                    <div class="text-3xl font-black text-slate-800 leading-none" id="donut-centre-val">–</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Students</div>
                </div>
            </div>
            {{-- Legend --}}
            <div class="mt-4 space-y-2.5" id="status-legend"></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     CHARTS  ROW 2  —  Attendance (wide dark card) + Fee Bar
     ══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-6">

    {{-- Attendance Trend dark card — 3/5 --}}
    <div class="att-dark-card lg:col-span-3">
        {{-- Header --}}
        <div class="px-6 pt-6 pb-2 flex items-start justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300 mb-1">Last 14 Days</p>
                <h3 class="text-base font-extrabold text-white">Daily Attendance Record</h3>
                <p class="text-[11px] text-indigo-300 mt-0.5">How many students were present or absent each day</p>
            </div>
            <span class="att-live-badge">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Live
            </span>
        </div>
        {{-- Stats strip --}}
        <div class="px-6 pb-4 flex flex-wrap gap-3 mt-2">
            <div class="att-pill">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                <span class="text-emerald-200 text-[11px]">Present this period: <strong id="att-p-total">–</strong></span>
            </div>
            <div class="att-pill">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-400 flex-shrink-0"></span>
                <span class="text-rose-200 text-[11px]">Absent this period: <strong id="att-a-total">–</strong></span>
            </div>
        </div>
        {{-- Chart --}}
        <div class="att-chart-panel" style="height:230px">
            <canvas id="chart-attendance"></canvas>
        </div>
    </div>

    {{-- Fee Collection Progress — 2/5 --}}
    <div class="s-card lg:col-span-2">
        <div class="s-card-hd">
            <div>
                <p class="s-card-title">Fee Collection Progress</p>
                <p class="s-card-sub">How much of the expected fees have been paid</p>
            </div>
            <span class="s-pill"><i class="fas fa-coins"></i> Fees</span>
        </div>
        <div class="s-card-body">
            {{-- Big donut for collection rate --}}
            <div class="relative flex items-center justify-center" style="height:200px">
                <canvas id="chart-fee-donut"></canvas>
                <div class="absolute text-center pointer-events-none">
                    <div class="text-3xl font-black leading-none" id="fee-donut-pct"
                         style="color:{{ $collPct >= 75 ? '#16a34a' : ($collPct >= 50 ? '#d97706' : '#dc2626') }}">
                        {{ $collPct }}%
                    </div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Collected</div>
                </div>
            </div>
            {{-- Breakdown rows --}}
            <div class="space-y-3 mt-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-slate-600 font-semibold">
                        <span class="w-3 h-3 rounded-sm inline-block" style="background:#6366f1"></span>Expected
                    </span>
                    <span class="font-bold text-slate-800">GH₵ {{ number_format($billed, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-slate-600 font-semibold">
                        <span class="w-3 h-3 rounded-sm inline-block" style="background:#10b981"></span>Collected
                    </span>
                    <span class="font-bold text-emerald-700">GH₵ {{ number_format($collected, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-slate-600 font-semibold">
                        <span class="w-3 h-3 rounded-sm inline-block" style="background:#f59e0b"></span>Outstanding
                    </span>
                    <span class="font-bold text-amber-700">GH₵ {{ number_format($outstanding, 2) }}</span>
                </div>
                {{-- Progress bar --}}
                <div class="mt-1">
                    <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1">
                        <span>Collection progress</span><span>{{ $collPct }}%</span>
                    </div>
                    <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div id="fee-prog-bar" class="h-full rounded-full transition-all duration-1000"
                             style="width:0%;background:{{ $collPct >= 75 ? '#10b981' : ($collPct >= 50 ? '#f59e0b' : '#ef4444') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     RECENT ACTIVITY FEED
     ══════════════════════════════════════════════════════════ --}}
<div class="s-card">
    <div class="s-card-hd">
        <div>
            <p class="s-card-title">Recent System Activity</p>
            <p class="s-card-sub">The latest admissions, payments, scores and attendance records</p>
        </div>
        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider
                     bg-slate-50 border border-slate-100 px-3 py-1.5 rounded-lg">
            {{ $recentActivities->count() }} actions
        </span>
    </div>

    <div class="max-h-[520px] overflow-y-auto divide-y divide-slate-50">
        @forelse($recentActivities as $a)
        <div class="feed-row">
            <div class="feed-icon" style="background:{{ $a['bg'] }}">
                <i class="fas {{ $a['icon'] }}" style="color:{{ $a['color'] }}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="feed-title truncate">{{ $a['title'] }}</p>
                <p class="feed-detail truncate">{{ $a['detail'] }}</p>
            </div>
            <div class="flex flex-col items-end gap-1 flex-shrink-0 ml-3">
                @if($a['badge'])
                <span class="feed-badge" style="background:{{ $a['bg'] }};color:{{ $a['color'] }}">
                    {{ $a['badge'] }}
                </span>
                @endif
                <span class="feed-time">{{ $a['time']->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-bolt text-3xl text-slate-200"></i>
            </div>
            <p class="text-sm font-semibold text-slate-400">No activity recorded yet</p>
            <p class="text-xs text-slate-300 mt-1">Activity will appear here as you use the system</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
(function() {
'use strict';

/* ── Raw data from PHP ──────────────────────────────────── */
const progData   = @json($programEnrollment->map(fn($p)=>['label'=>$p->name,'value'=>$p->students_count]));
const statusData = @json($statusDistribution);
const attData    = @json($attendanceTrend);

const progLabels = progData.map(d => d.label);
const progCounts = progData.map(d => d.value);

const attLabels  = attData.map(d => d.day);
const presentArr = attData.map(d => d.present);
const absentArr  = attData.map(d => d.absent);

/* ── Global Chart defaults ──────────────────────────────── */
Chart.defaults.font.family = "'Poppins','Inter',system-ui,sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#64748b';

/* ── Colour palette ─────────────────────────────────────── */
const PAL = [
    '#6366f1','#0ea5e9','#10b981','#f59e0b',
    '#ef4444','#a855f7','#14b8a6','#f97316',
    '#ec4899','#84cc16'
];

/* ── Shared tooltip style ───────────────────────────────── */
const TIP = {
    backgroundColor : 'rgba(15,23,42,.93)',
    titleColor      : '#e2e8f0',
    bodyColor       : '#94a3b8',
    padding         : 13,
    cornerRadius    : 12,
    borderColor     : 'rgba(255,255,255,.07)',
    borderWidth     : 1,
    caretSize       : 6,
    titleFont       : { weight:'bold', size:12 },
    bodyFont        : { size:11 },
};

/* ── Gradient helper ────────────────────────────────────── */
function vertGrad(chartCtx, r,g,b, a0=.3, a1=.02) {
    const {chartArea} = chartCtx.chart;
    if (!chartArea) return `rgba(${r},${g},${b},${a0})`;
    const g2 = chartCtx.chart.ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
    g2.addColorStop(0, `rgba(${r},${g},${b},${a0})`);
    g2.addColorStop(1, `rgba(${r},${g},${b},${a1})`);
    return g2;
}

/* ══════════════════════════════════════════════════════════
   CHART 1 — Students Per Programme (horizontal bar)
   ══════════════════════════════════════════════════════════ */
new Chart(document.getElementById('chart-enrolment'), {
    type: 'bar',
    data: {
        labels: progLabels,
        datasets: [{
            label: 'Number of Students',
            data: progCounts,
            backgroundColor: progLabels.map((_,i) => PAL[i % PAL.length] + 'dd'),
            borderColor    : progLabels.map((_,i) => PAL[i % PAL.length]),
            borderWidth    : 1.5,
            borderRadius   : 10,
            borderSkipped  : false,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: { right: 16 } },
        plugins: {
            legend: { display: false },
            tooltip: {
                ...TIP,
                callbacks: {
                    title: ctx => ctx[0].label,
                    label: ctx => `   ${ctx.parsed.x} student${ctx.parsed.x !== 1 ? 's' : ''} enrolled`
                }
            },
        },
        scales: {
            x: {
                beginAtZero: true,
                grid  : { color:'rgba(148,163,184,.1)', drawBorder:false },
                border: { display:false },
                ticks : {
                    color:'#94a3b8', font:{ size:10, weight:'600' },
                    stepSize: 1,
                    callback: v => v % 1 === 0 ? v : ''
                }
            },
            y: {
                grid  : { display:false },
                border: { display:false },
                ticks : {
                    color:'#475569',
                    font :{ size:11, weight:'700' },
                    padding: 6,
                    callback(val, i) {
                        const l = progLabels[i] ?? '';
                        return l.length > 20 ? l.slice(0,18)+'…' : l;
                    }
                }
            }
        }
    }
});

/* ══════════════════════════════════════════════════════════
   CHART 2 — Student Status Donut
   ══════════════════════════════════════════════════════════ */
const sLabels = Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1));
const sValues = Object.values(statusData);
const sTotal  = sValues.reduce((a,b)=>a+b, 0);

/* Centre value */
const centreEl = document.getElementById('donut-centre-val');
if (centreEl) centreEl.textContent = sTotal.toLocaleString();

/* Custom legend */
const legendEl = document.getElementById('status-legend');
if (legendEl) {
    sLabels.forEach((lbl, i) => {
        const pct = sTotal > 0 ? Math.round(sValues[i]/sTotal*100) : 0;
        legendEl.innerHTML += `
        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
            <div style="display:flex;align-items:center;gap:.55rem;">
                <span style="width:12px;height:12px;border-radius:4px;flex-shrink:0;background:${PAL[i%PAL.length]}"></span>
                <span style="font-size:.75rem;font-weight:600;color:#475569">${lbl}</span>
            </div>
            <div style="display:flex;align-items:center;gap:.6rem;">
                <span style="font-size:.75rem;font-weight:800;color:#0f172a">${sValues[i]}</span>
                <span style="font-size:.65rem;font-weight:700;color:#94a3b8;background:#f1f5f9;border-radius:99px;padding:.1rem .5rem">${pct}%</span>
            </div>
        </div>`;
    });
}

new Chart(document.getElementById('chart-status'), {
    type: 'doughnut',
    data: {
        labels: sLabels,
        datasets:[{
            data: sValues,
            backgroundColor: PAL.slice(0, sLabels.length),
            borderWidth: 3,
            borderColor:'#fff',
            hoverBorderWidth: 3,
            hoverOffset: 8,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '76%',
        plugins: {
            legend: { display:false },
            tooltip: {
                ...TIP,
                callbacks: {
                    label: ctx => {
                        const pct = sTotal > 0 ? Math.round(ctx.parsed/sTotal*100) : 0;
                        return `   ${ctx.label}: ${ctx.parsed} student${ctx.parsed!==1?'s':''} (${pct}%)`;
                    }
                }
            }
        }
    }
});

/* ══════════════════════════════════════════════════════════
   CHART 3 — Daily Attendance (stacked bar — clearest for GH admins)
   ══════════════════════════════════════════════════════════ */
new Chart(document.getElementById('chart-attendance'), {
    type: 'bar',
    data: {
        labels: attLabels,
        datasets: [
            {
                label: 'Present',
                data: presentArr,
                backgroundColor: '#10b981',
                borderRadius: { topLeft:5, topRight:5 },
                borderSkipped: false,
            },
            {
                label: 'Absent',
                data: absentArr,
                backgroundColor: '#f43f5e',
                borderRadius: { topLeft:5, topRight:5 },
                borderSkipped: false,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode:'index', intersect:false },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                align: 'end',
                labels: {
                    color:'#64748b',
                    boxWidth:10, boxHeight:10,
                    borderRadius:3,
                    useBorderRadius:true,
                    font:{ size:10, weight:'700' },
                    padding: 12,
                }
            },
            tooltip: {
                backgroundColor:'rgba(13,10,46,.94)',
                titleColor:'#a5b4fc',
                bodyColor:'#e0e7ff',
                padding:12, cornerRadius:10,
                callbacks: {
                    title: ctx => `Date: ${ctx[0].label}`,
                    label: ctx => `   ${ctx.dataset.label}: ${ctx.parsed.y} student${ctx.parsed.y!==1?'s':''}`
                }
            }
        },
        scales: {
            x: {
                stacked: false,
                grid: { display:false },
                border:{ display:false },
                ticks:{ color:'#64748b', font:{size:9}, maxRotation:0, maxTicksLimit:10 }
            },
            y: {
                beginAtZero:true,
                stacked: false,
                border:{ display:false },
                grid:{ color:'rgba(148,163,184,.1)' },
                ticks:{ color:'#64748b', font:{size:9}, stepSize:1,
                        callback: v => Number.isInteger(v) ? v : '' }
            }
        }
    }
});

/* Totals strip */
const tp = presentArr.reduce((a,b)=>a+b,0);
const ta = absentArr.reduce((a,b)=>a+b,0);
const elP = document.getElementById('att-p-total');
const elA = document.getElementById('att-a-total');
if (elP) elP.textContent = tp.toLocaleString();
if (elA) elA.textContent = ta.toLocaleString();

/* ══════════════════════════════════════════════════════════
   CHART 4 — Fee Collection Donut (right panel)
   ══════════════════════════════════════════════════════════ */
const collPct = {{ $collPct }};
const outPct  = 100 - collPct;

new Chart(document.getElementById('chart-fee-donut'), {
    type: 'doughnut',
    data: {
        labels: ['Collected','Outstanding'],
        datasets:[{
            data: [collPct, Math.max(0, outPct)],
            backgroundColor: [
                collPct >= 75 ? '#10b981' : (collPct >= 50 ? '#f59e0b' : '#ef4444'),
                '#e2e8f0'
            ],
            borderWidth: 0,
            hoverOffset: 4,
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        cutout:'80%',
        plugins:{
            legend:{ display:false },
            tooltip:{
                ...TIP,
                callbacks:{
                    label: ctx => `   ${ctx.label}: ${ctx.parsed.toFixed(1)}%`
                }
            }
        }
    }
});

/* ── Animate progress bars after page load ──────────────── */
setTimeout(() => {
    const cb = document.getElementById('fee-bar-collected');
    const ob = document.getElementById('fee-bar-outstanding');
    const pb = document.getElementById('fee-prog-bar');
    const fb = document.getElementById('fee-bar-collected'); // gradient card
    if (cb) cb.style.width = '{{ $collPct }}%';
    if (ob) ob.style.width = '{{ $outPct }}%';
    if (pb) pb.style.width = '{{ $collPct }}%';
}, 400);

})();
</script>
@endpush
@endsection
