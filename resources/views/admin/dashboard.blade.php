@extends('layouts.app')
@section('title','Dashboard')
@section('subtitle', ($schoolName ?? 'School') . ' – Admin Overview')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   SUPER-PREMIUM DASHBOARD  v4
   All colours reference CSS theme variables — no hardcoded hues
   ═══════════════════════════════════════════════════════════ */

/* ── Hero greeting banner  (THEME-AWARE) ─────────────────── */
.dash-hero {
    background: linear-gradient(
        128deg,
        var(--admin-nav) 0%,
        color-mix(in srgb, var(--admin-nav) 80%, var(--brand)) 55%,
        color-mix(in srgb, var(--admin-nav) 60%, var(--brand)) 100%
    );
    border-radius: 1.5rem;
    padding: 1.9rem 2.2rem;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
    border: 1px solid rgba(255,255,255,.07);
    box-shadow: 0 20px 60px -10px rgba(0,0,0,.35);
}
/* Brand-coloured glow blobs */
.dash-hero-blob1 {
    position:absolute; width:300px; height:300px; border-radius:50%;
    background: var(--brand);
    filter: blur(70px); opacity:.08;
    right:-80px; top:-80px; pointer-events:none;
}
.dash-hero-blob2 {
    position:absolute; width:200px; height:200px; border-radius:50%;
    background: var(--primary);
    filter: blur(55px); opacity:.07;
    left:30%; bottom:-60px; pointer-events:none;
}
/* Dot-grid texture */
.dash-hero::before {
    content:'';
    position:absolute; inset:0;
    background-image: radial-gradient(circle, rgba(255,255,255,.06) 1px, transparent 1px);
    background-size: 24px 24px;
    pointer-events:none;
}
.dash-hero-btn-primary {
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.6rem 1.3rem; border-radius:.875rem;
    background: var(--brand); color:#fff;
    font-size:.82rem; font-weight:800;
    border: 1px solid rgba(255,255,255,.2);
    transition:all .18s ease; text-decoration:none;
    box-shadow: 0 4px 14px rgba(0,0,0,.25);
}
.dash-hero-btn-primary:hover { filter:brightness(1.1); transform:translateY(-1px); color:#fff; }
.dash-hero-btn-ghost {
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.6rem 1.3rem; border-radius:.875rem;
    background: rgba(255,255,255,.10); color:#fff;
    font-size:.82rem; font-weight:700;
    border: 1px solid rgba(255,255,255,.18);
    transition:all .18s ease; text-decoration:none;
    backdrop-filter:blur(6px);
}
.dash-hero-btn-ghost:hover { background:rgba(255,255,255,.18); color:#fff; }

/* ── KPI tiles ────────────────────────────────────────────── */
.kpi-tile {
    background:#fff;
    border-radius:1.25rem;
    padding:1.5rem 1.5rem 1.3rem;
    border:1px solid rgba(0,0,0,.055);
    box-shadow:0 2px 12px rgba(0,0,0,.05);
    position:relative; overflow:hidden;
    transition:transform .2s ease, box-shadow .2s ease;
    cursor:default;
}
.kpi-tile:hover { transform:translateY(-4px); box-shadow:0 16px 48px rgba(0,0,0,.10); }
/* Shine overlay */
.kpi-tile::after {
    content:'';
    position:absolute; inset:0;
    background:linear-gradient(135deg,rgba(255,255,255,.55) 0%,transparent 55%);
    pointer-events:none; border-radius:inherit;
}
.kpi-glow {
    position:absolute; width:160px; height:160px;
    border-radius:50%; top:-50px; right:-50px;
    filter:blur(38px); opacity:.12; pointer-events:none;
    transition:opacity .3s;
}
.kpi-tile:hover .kpi-glow { opacity:.2; }
.kpi-icon {
    width:48px; height:48px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.15rem; margin-bottom:1rem; flex-shrink:0;
    position:relative; z-index:1;
}
.kpi-val {
    font-size:2.1rem; font-weight:900;
    letter-spacing:-.04em; line-height:1; color:#0f172a;
    margin-bottom:.3rem; position:relative; z-index:1;
}
.kpi-lbl {
    font-size:.7rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.09em; color:#94a3b8;
    position:relative; z-index:1;
}
.kpi-badge {
    margin-top:.7rem; display:inline-flex; align-items:center; gap:.3rem;
    font-size:.68rem; font-weight:800; border-radius:99px; padding:.2rem .65rem;
    position:relative; z-index:1;
}
.kpi-badge.good  { background:#dcfce7; color:#15803d; }
.kpi-badge.warn  { background:#fef3c7; color:#b45309; }
.kpi-badge.bad   { background:#fee2e2; color:#dc2626; }
.kpi-badge.info  { background:#f1f5f9; color:#475569; }

/* ── Generic section card ─────────────────────────────────── */
.sc {
    background:#fff;
    border-radius:1.25rem;
    border:1px solid rgba(0,0,0,.055);
    box-shadow:0 2px 12px rgba(0,0,0,.05);
    overflow:hidden;
}
.sc-hd {
    padding:1.3rem 1.6rem 1rem;
    border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between; gap:.75rem;
}
.sc-title { font-size:.92rem; font-weight:800; color:#0f172a; letter-spacing:-.015em; }
.sc-sub   { font-size:.7rem; color:#94a3b8; margin-top:.2rem; font-weight:500; }
.sc-body  { padding:1.4rem 1.6rem 1.6rem; }
.sc-pill {
    display:inline-flex; align-items:center; gap:.35rem;
    border-radius:99px; padding:.22rem .85rem;
    font-size:.63rem; font-weight:800;
    text-transform:uppercase; letter-spacing:.08em;
    white-space:nowrap; flex-shrink:0;
    background: var(--primary-pale);
    color: var(--brand);
}

/* ── Fee gradient cards ───────────────────────────────────── */
.fee-card {
    border-radius:1.25rem;
    padding:1.6rem 1.7rem;
    position:relative; overflow:hidden;
    border:1px solid rgba(255,255,255,.15);
    box-shadow:0 8px 32px rgba(0,0,0,.14);
    color:#fff;
}
.fee-card-icon {
    width:44px; height:44px; border-radius:13px;
    background:rgba(255,255,255,.18);
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; margin-bottom:1rem;
}
.fee-card-lbl {
    font-size:.66rem; font-weight:800;
    text-transform:uppercase; letter-spacing:.1em;
    opacity:.65; margin-bottom:.7rem;
}
.fee-card-amount {
    font-size:1.85rem; font-weight:900;
    letter-spacing:-.035em; line-height:1.1;
}
.fee-track {
    height:8px; border-radius:99px;
    background:rgba(255,255,255,.2); overflow:hidden; margin-top:.8rem;
}
.fee-fill {
    height:100%; border-radius:99px;
    background:rgba(255,255,255,.75);
    transition:width .9s cubic-bezier(.4,0,.2,1);
}
.fee-caption { font-size:.67rem; opacity:.6; margin-top:.5rem; font-weight:600; }

/* ── Quick-action buttons ─────────────────────────────────── */
.qa-btn {
    display:flex; flex-direction:column; align-items:center; gap:.7rem;
    padding:1.25rem 1rem;
    border-radius:1.1rem; border:1.5px solid #f1f5f9;
    background:#fff; text-decoration:none;
    transition:all .2s ease;
}
.qa-btn:hover {
    border-color: var(--brand);
    box-shadow:0 8px 28px rgba(0,0,0,.09);
    transform:translateY(-3px);
}
.qa-btn:active { transform:scale(.95); }
.qa-icon {
    width:50px; height:50px; border-radius:1rem;
    display:flex; align-items:center; justify-content:center;
    font-size:1.2rem;
    transition:transform .2s ease;
}
.qa-btn:hover .qa-icon { transform:scale(1.1) rotate(-5deg); }
.qa-lbl  { font-size:.73rem; font-weight:700; color:#374151; text-align:center; line-height:1.3; }
.qa-sub  { font-size:.63rem; color:#9ca3af; font-weight:500; text-align:center; }

/* ── Attendance dark premium card  (theme-aware) ─────────── */
.att-card {
    background:linear-gradient(
        150deg,
        var(--admin-nav) 0%,
        color-mix(in srgb, var(--admin-nav) 70%, #1a0050) 50%,
        color-mix(in srgb, var(--admin-nav) 50%, #3730a3) 100%
    );
    border-radius:1.25rem; overflow:hidden;
    border:1px solid rgba(255,255,255,.07);
    box-shadow:0 20px 60px rgba(0,0,0,.4);
}
.att-live-dot {
    display:inline-flex; align-items:center; gap:.4rem;
    background:rgba(52,211,153,.14);
    border:1px solid rgba(52,211,153,.32);
    border-radius:99px;
    padding:.25rem .8rem;
    font-size:.63rem; font-weight:900;
    text-transform:uppercase; letter-spacing:.12em;
    color:#6ee7b7;
}
.att-stat {
    display:inline-flex; align-items:center; gap:.45rem;
    padding:.32rem .9rem; border-radius:.65rem;
    background:rgba(255,255,255,.08);
    font-size:.7rem; font-weight:700;
}
.att-chart-wrap {
    margin:0 1rem 1rem;
    background:rgba(255,255,255,.97);
    border-radius:1rem; padding:.9rem 1rem;
}

/* ── Activity feed ────────────────────────────────────────── */
.feed-item {
    display:flex; align-items:center; gap:1rem;
    padding:.95rem 1.6rem;
    border-bottom:1px solid #f8fafc;
    transition:background .15s;
}
.feed-item:last-child { border-bottom:none; }
.feed-item:hover { background:#fafafa; }
.feed-item-icon {
    width:40px; height:40px; border-radius:.875rem;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; font-size:.85rem;
}
.feed-title  { font-size:.8rem; font-weight:700; color:#1e293b; }
.feed-detail { font-size:.68rem; color:#94a3b8; margin-top:.12rem; font-weight:500; }
.feed-badge  {
    font-size:.59rem; font-weight:800;
    padding:.15rem .6rem; border-radius:99px;
    text-transform:uppercase; letter-spacing:.05em; white-space:nowrap;
}
.feed-time   { font-size:.64rem; color:#cbd5e1; font-weight:600; white-space:nowrap; }

/* ── Chart wrapper heights ────────────────────────────────── */
.ch-360 { height:360px; }
.ch-300 { height:300px; }
.ch-250 { height:250px; }
.ch-220 { height:220px; }
@media(max-width:768px){
    .ch-360{height:280px;} .ch-300{height:240px;}
    .ch-250{height:200px;} .ch-220{height:180px;}
    .dash-hero{padding:1.35rem 1.25rem;}
    .kpi-val{font-size:1.65rem;}
}
</style>
@endpush

@section('content')
@php
/* ── PHP pre-compute ──────────────────────────────────────── */
$lecturerCount = (!isset($stats['total_lecturers']) || $stats['total_lecturers'] == 0)
    ? \App\Models\User::query()
        ->where(fn($q) => $q->whereIn('role',['lecturer','lecturers','teacher'])
            ->orWhere('role','like','%lecturer%')
            ->orWhere('role','like','%teacher%'))
        ->count()
    : $stats['total_lecturers'];

$billed      = (float)($fees['billed']      ?? 0);
$collected   = (float)($fees['collected']   ?? 0);
$outstanding = (float)($fees['outstanding'] ?? 0);
$collPct     = $billed > 0 ? min(100, round(($collected  / $billed) * 100)) : 0;
$outPct      = $billed > 0 ? min(100, round(($outstanding / $billed) * 100)) : 0;
$rate        = (int)($attendanceRate ?? 0);

$hour        = now()->hour;
$greeting    = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$firstName   = explode(' ', auth()->user()->full_name ?? 'Administrator')[0];
@endphp

{{-- ══════════════════════════════════════════════════════
     HERO BANNER  — theme-aware gradient
     ══════════════════════════════════════════════════════ --}}
<div class="dash-hero">
    <div class="dash-hero-blob1"></div>
    <div class="dash-hero-blob2"></div>
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p style="color:rgba(255,255,255,.55);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;margin-bottom:.4rem;">
                <i class="fas fa-calendar-day" style="margin-right:.3rem;color:var(--brand)"></i>
                {{ now()->format('l, d F Y') }}
            </p>
            <h1 style="color:#fff;font-size:1.8rem;font-weight:900;letter-spacing:-.035em;line-height:1.15;margin:0 0 .5rem;">
                {{ $greeting }}, <span style="color:var(--brand)">{{ $firstName }}</span> 👋
            </h1>
            <p style="color:rgba(255,255,255,.5);font-size:.82rem;font-weight:500;margin:0;">
                Here is what is happening at
                <strong style="color:rgba(255,255,255,.85)">{{ $schoolName ?? 'your school' }}</strong> today.
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.students.create') }}" class="dash-hero-btn-primary">
                <i class="fas fa-user-plus" style="font-size:.75rem"></i> Admit Student
            </a>
            <a href="{{ route('admin.fees.index') }}" class="dash-hero-btn-ghost">
                <i class="fas fa-coins" style="font-size:.75rem"></i> Record Payment
            </a>
        </div>
    </div>
</div>

{{-- Storage warning ──────────────────────────────────────── --}}
@if (!($storageHealth['public_disk_writable'] ?? false) || !($storageHealth['uploads_writable'] ?? false))
<div class="rounded-2xl border-l-4 bg-red-50 p-4 mb-6 flex items-start gap-3" style="border-color:var(--brand)">
    <div class="w-9 h-9 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-exclamation-triangle text-red-500"></i>
    </div>
    <div>
        <p class="font-bold text-red-800 text-sm">File upload folder is not writable</p>
        <p class="text-xs text-red-600 mt-1">Student photos and documents may not upload. Contact your server administrator.</p>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     KPI STAT TILES
     ══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">

    <div class="kpi-tile">
        <div class="kpi-glow" style="background:var(--brand)"></div>
        <div class="kpi-icon" style="background:var(--primary-pale);color:var(--brand)">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="kpi-val">{{ number_format($stats['active_students'] ?? 0) }}</div>
        <div class="kpi-lbl">{{ $sidebarLabels['students'] ?? 'Active Students' }}</div>
        <div class="kpi-badge info">
            <i class="fas fa-users" style="font-size:.58rem"></i>
            {{ number_format($stats['total_students'] ?? 0) }} enrolled total
        </div>
    </div>

    <div class="kpi-tile">
        <div class="kpi-glow" style="background:#0891b2"></div>
        <div class="kpi-icon" style="background:#ecfeff;color:#0891b2">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="kpi-val">{{ number_format((int)($lecturerCount ?? 0)) }}</div>
        <div class="kpi-lbl">{{ $sidebarLabels['teachers'] ?? 'Teaching Staff' }}</div>
        <div class="kpi-badge info">
            <i class="fas fa-id-badge" style="font-size:.58rem"></i> On the staff list
        </div>
    </div>

    <div class="kpi-tile">
        <div class="kpi-glow" style="background:#7c3aed"></div>
        <div class="kpi-icon" style="background:#f5f3ff;color:#7c3aed">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="kpi-val">{{ number_format($stats['total_programs'] ?? 0) }}</div>
        <div class="kpi-lbl">{{ $sidebarLabels['programs'] ?? 'Programmes' }}</div>
        <div class="kpi-badge info">
            <i class="fas fa-book-open" style="font-size:.58rem"></i>
            {{ number_format($stats['total_courses'] ?? 0) }} courses running
        </div>
    </div>

    <div class="kpi-tile">
        <div class="kpi-glow" style="background:{{ $collPct >= 75 ? '#16a34a' : ($collPct >= 50 ? '#f59e0b' : '#ef4444') }}"></div>
        <div class="kpi-icon" style="background:{{ $collPct >= 75 ? '#f0fdf4' : ($collPct >= 50 ? '#fffbeb' : '#fef2f2') }};color:{{ $collPct >= 75 ? '#16a34a' : ($collPct >= 50 ? '#d97706' : '#dc2626') }}">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div class="kpi-val">{{ $collPct }}%</div>
        <div class="kpi-lbl">Fees Collected</div>
        <div class="kpi-badge {{ $collPct >= 75 ? 'good' : ($collPct >= 50 ? 'warn' : 'bad') }}">
            <i class="fas {{ $collPct >= 75 ? 'fa-check' : 'fa-clock' }}" style="font-size:.58rem"></i>
            {{ $collPct >= 75 ? 'On track' : ($collPct >= 50 ? 'Needs follow-up' : 'Critical — many unpaid') }}
        </div>
    </div>

    <div class="kpi-tile">
        <div class="kpi-glow" style="background:{{ $rate >= 80 ? '#16a34a' : ($rate >= 60 ? '#f59e0b' : '#ef4444') }}"></div>
        <div class="kpi-icon" style="background:{{ $rate >= 80 ? '#f0fdf4' : ($rate >= 60 ? '#fffbeb' : '#fef2f2') }};color:{{ $rate >= 80 ? '#16a34a' : ($rate >= 60 ? '#d97706' : '#dc2626') }}">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <div class="kpi-val">{{ $rate }}%</div>
        <div class="kpi-lbl">Attendance Rate</div>
        <div class="kpi-badge {{ $rate >= 80 ? 'good' : ($rate >= 60 ? 'warn' : 'bad') }}">
            <i class="fas {{ $rate >= 80 ? 'fa-thumbs-up' : 'fa-exclamation' }}" style="font-size:.58rem"></i>
            {{ $rate >= 80 ? 'Students attending well' : ($rate >= 60 ? 'Monitor closely' : 'Urgent — low turnout') }}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     FEE SUMMARY  (3 gradient cards)
     ══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    <div class="fee-card" style="background:linear-gradient(140deg,color-mix(in srgb,var(--admin-nav) 85%,#4f46e5) 0%,color-mix(in srgb,var(--admin-nav) 60%,#7c3aed) 100%)">
        <div class="fee-card-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="fee-card-lbl">Total Fees Expected This Term</div>
        <div class="fee-card-amount">GH₵ {{ number_format($billed, 2) }}</div>
        <div class="fee-track"><div class="fee-fill" style="width:100%"></div></div>
        <div class="fee-caption">All fees billed to students</div>
    </div>

    <div class="fee-card" style="background:linear-gradient(140deg,#047857 0%,#10b981 100%)">
        <div class="fee-card-icon"><i class="fas fa-wallet"></i></div>
        <div class="fee-card-lbl">Amount Collected So Far</div>
        <div class="fee-card-amount">GH₵ {{ number_format($collected, 2) }}</div>
        <div class="fee-track">
            <div class="fee-fill" id="fee-bar-c" style="width:0%"></div>
        </div>
        <div class="fee-caption"><strong style="color:rgba(255,255,255,.9)">{{ $collPct }}%</strong> of total collected</div>
    </div>

    <div class="fee-card" style="background:linear-gradient(140deg,#b45309 0%,#f59e0b 100%)">
        <div class="fee-card-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="fee-card-lbl">Still Owed By Students</div>
        <div class="fee-card-amount">GH₵ {{ number_format($outstanding, 2) }}</div>
        <div class="fee-track">
            <div class="fee-fill" id="fee-bar-o" style="width:0%"></div>
        </div>
        <div class="fee-caption">
            <strong style="color:rgba(255,255,255,.9)">{{ $outPct }}%</strong> unpaid
            @if($outstanding > 0) — remind parents @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     QUICK ACTIONS
     ══════════════════════════════════════════════════════ --}}
<div class="sc mb-6">
    <div class="sc-hd">
        <div>
            <p class="sc-title">Quick Actions</p>
            <p class="sc-sub">The most common tasks — tap to go straight there</p>
        </div>
        <span class="sc-pill"><i class="fas fa-bolt"></i> Shortcuts</span>
    </div>
    <div class="sc-body">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach([
                ['href'=>route('admin.students.create'),   'icon'=>'fa-user-plus',       'ibg'=>'var(--primary-pale)','ic'=>'var(--brand)',  'lbl'=>'Admit Student',   'sub'=>'Register a new student'],
                ['href'=>route('admin.exams.create'),      'icon'=>'fa-file-alt',        'ibg'=>'#dbeafe',            'ic'=>'#1d4ed8',        'lbl'=>'Enter Scores',    'sub'=>'Input exam & SBA marks'],
                ['href'=>route('admin.fees.index'),        'icon'=>'fa-coins',           'ibg'=>'#dcfce7',            'ic'=>'#15803d',        'lbl'=>'Record Payment',  'sub'=>'Log fee payment received'],
                ['href'=>route('admin.attendance.create'), 'icon'=>'fa-clipboard-check', 'ibg'=>'#fce7f3',            'ic'=>'#9d174d',        'lbl'=>'Mark Attendance', 'sub'=>'Take register for class'],
            ] as $qa)
            <a href="{{ $qa['href'] }}" class="qa-btn">
                <div class="qa-icon" style="background:{{ $qa['ibg'] }};color:{{ $qa['ic'] }}">
                    <i class="fas {{ $qa['icon'] }}"></i>
                </div>
                <div>
                    <div class="qa-lbl">{{ $qa['lbl'] }}</div>
                    <div class="qa-sub">{{ $qa['sub'] }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     CHART ROW 1
     Col 1 (3/5): Students Per Programme — RADAR chart
     Col 2 (2/5): Student Status — PIE with data labels
     ══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-4">

    {{-- CHART 1: Enrolment Radar ─────────────────────────── --}}
    <div class="sc lg:col-span-3">
        <div class="sc-hd">
            <div>
                <p class="sc-title">Students Per Programme</p>
                <p class="sc-sub">Enrolment spread across all programmes — hover a point for the exact count</p>
            </div>
            <span class="sc-pill"><i class="fas fa-graduation-cap"></i> Enrolment</span>
        </div>
        <div class="sc-body">
            <div class="ch-360"><canvas id="chart-prog"></canvas></div>
        </div>
    </div>

    {{-- CHART 2: Status Pie with inside labels ──────────────── --}}
    <div class="sc lg:col-span-2">
        <div class="sc-hd">
            <div>
                <p class="sc-title">Student Status Breakdown</p>
                <p class="sc-sub">How many are active, deferred, graduated or suspended</p>
            </div>
            <span class="sc-pill"><i class="fas fa-chart-pie"></i> Status</span>
        </div>
        <div class="sc-body">
            {{-- PolarArea chart — shows magnitude + category together --}}
            <div class="relative" style="height:260px">
                <canvas id="chart-status"></canvas>
            </div>
            {{-- Legend rows built by JS --}}
            <div class="mt-4 space-y-2.5" id="status-legend"></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     CHART ROW 2
     Col 1 (3/5): Attendance Trend (dark card) — AREA chart
     Col 2 (2/5): Fee Collection Gauge
     ══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-6">

    {{-- CHART 3: Attendance dark card ──────────────────────── --}}
    <div class="att-card lg:col-span-3">
        <div class="px-6 pt-6 pb-2 flex items-start justify-between">
            <div>
                <p style="font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.12em;color:var(--brand);margin-bottom:.35rem;">
                    Last 14 Days
                </p>
                <h3 style="font-size:.97rem;font-weight:900;color:#fff;margin:0 0 .2rem;">
                    Daily Attendance Overview
                </h3>
                <p style="font-size:.7rem;color:rgba(255,255,255,.45);font-weight:500;">
                    Each bar = one day — green shows students who came, red shows those absent
                </p>
            </div>
            <span class="att-live-dot">
                <span style="width:6px;height:6px;border-radius:50%;background:#6ee7b7;animation:pulse 1.5s infinite"></span>
                Live
            </span>
        </div>
        {{-- Summary pills --}}
        <div class="px-6 pb-4 flex flex-wrap gap-3 mt-2">
            <div class="att-stat">
                <span style="width:11px;height:11px;border-radius:3px;background:#34d399;flex-shrink:0"></span>
                <span style="color:#a7f3d0;font-size:.7rem;">Present (14 days): <strong id="att-p">–</strong></span>
            </div>
            <div class="att-stat">
                <span style="width:11px;height:11px;border-radius:3px;background:#f87171;flex-shrink:0"></span>
                <span style="color:#fca5a5;font-size:.7rem;">Absent (14 days): <strong id="att-a">–</strong></span>
            </div>
            <div class="att-stat">
                <span style="width:11px;height:11px;border-radius:50%;background:#facc15;flex-shrink:0"></span>
                <span style="color:#fde68a;font-size:.7rem;">Period rate: <strong id="att-rate-val">–</strong></span>
            </div>
        </div>
        {{-- Chart --}}
        <div class="att-chart-wrap" style="height:235px">
            <canvas id="chart-att"></canvas>
        </div>
    </div>

    {{-- CHART 4: Fee Gauge (right) ──────────────────────────── --}}
    <div class="sc lg:col-span-2">
        <div class="sc-hd">
            <div>
                <p class="sc-title">Fee Collection Rate</p>
                <p class="sc-sub">Percentage of fees received vs expected</p>
            </div>
            <span class="sc-pill"><i class="fas fa-coins"></i> Fees</span>
        </div>
        <div class="sc-body">
            <div class="relative flex items-center justify-center" style="height:210px">
                <canvas id="chart-fee-gauge"></canvas>
                <div style="position:absolute;text-align:center;pointer-events:none">
                    <div id="gauge-pct" style="font-size:2.4rem;font-weight:900;letter-spacing:-.04em;line-height:1;color:{{ $collPct >= 75 ? '#16a34a' : ($collPct >= 50 ? '#d97706' : '#dc2626') }}">
                        {{ $collPct }}%
                    </div>
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-top:.3rem">Collected</div>
                </div>
            </div>
            {{-- Breakdown --}}
            <div class="space-y-3 mt-3">
                @foreach([
                    ['label'=>'Expected (Total Billed)','amt'=>$billed,      'clr'=>'var(--brand)'],
                    ['label'=>'Collected (Paid)',        'amt'=>$collected,   'clr'=>'#10b981'],
                    ['label'=>'Outstanding (Still Owed)','amt'=>$outstanding, 'clr'=>'#f59e0b'],
                ] as $row)
                <div class="flex items-center justify-between text-sm gap-2">
                    <span class="flex items-center gap-2 text-slate-600 font-semibold" style="font-size:.75rem">
                        <span style="width:11px;height:11px;border-radius:3px;flex-shrink:0;background:{{ $row['clr'] }}"></span>
                        {{ $row['label'] }}
                    </span>
                    <span style="font-size:.78rem;font-weight:800;color:#0f172a;white-space:nowrap">
                        GH₵ {{ number_format($row['amt'], 2) }}
                    </span>
                </div>
                @endforeach
                {{-- Progress bar --}}
                <div style="margin-top:.5rem">
                    <div style="height:10px;background:#f1f5f9;border-radius:99px;overflow:hidden">
                        <div id="fee-prog" style="height:100%;border-radius:99px;width:0%;
                             background:{{ $collPct >= 75 ? '#10b981' : ($collPct >= 50 ? '#f59e0b' : '#ef4444') }};
                             transition:width 1s cubic-bezier(.4,0,.2,1)"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:.63rem;font-weight:700;color:#94a3b8;margin-top:.3rem">
                        <span>0%</span><span>{{ $collPct }}% collected</span><span>100%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     RECENT ACTIVITY FEED
     ══════════════════════════════════════════════════════ --}}
<div class="sc">
    <div class="sc-hd">
        <div>
            <p class="sc-title">Recent System Activity</p>
            <p class="sc-sub">Latest admissions, payments, exam scores and attendance records</p>
        </div>
        <span style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;
                     color:#64748b;background:#f8fafc;border:1px solid #f1f5f9;
                     padding:.3rem .9rem;border-radius:.65rem">
            {{ $recentActivities->count() }} actions
        </span>
    </div>
    <div style="max-height:520px;overflow-y:auto">
        @forelse($recentActivities as $a)
        <div class="feed-item">
            <div class="feed-item-icon" style="background:{{ $a['bg'] }}">
                <i class="fas {{ $a['icon'] }}" style="color:{{ $a['color'] }}"></i>
            </div>
            <div style="flex:1;min-width:0">
                <p class="feed-title" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $a['title'] }}</p>
                <p class="feed-detail" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $a['detail'] }}</p>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.3rem;flex-shrink:0;margin-left:.75rem">
                @if($a['badge'])
                <span class="feed-badge" style="background:{{ $a['bg'] }};color:{{ $a['color'] }}">{{ $a['badge'] }}</span>
                @endif
                <span class="feed-time">{{ $a['time']->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div style="padding:4rem 1rem;text-align:center">
            <div style="width:56px;height:56px;border-radius:1rem;background:#f8fafc;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                <i class="fas fa-bolt" style="font-size:1.5rem;color:#e2e8f0"></i>
            </div>
            <p style="font-size:.88rem;font-weight:600;color:#94a3b8">No activity recorded yet</p>
            <p style="font-size:.75rem;color:#cbd5e1;margin-top:.3rem">Activity will appear here as you use the system</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
(function(){
'use strict';

/* ── Raw PHP data ─────────────────────────────────────────── */
const progData   = @json($programEnrollment->map(fn($p)=>['label'=>$p->name,'value'=>$p->students_count]));
const statusData = @json($statusDistribution);
const attData    = @json($attendanceTrend);

const progLabels = progData.map(d => d.label);
const progCounts = progData.map(d => d.value);
const attLabels  = attData.map(d => d.day);
const presentArr = attData.map(d => d.present);
const absentArr  = attData.map(d => d.absent);

/* ── Read CSS theme variables ─────────────────────────────── */
const CS    = getComputedStyle(document.documentElement);
const BRAND = CS.getPropertyValue('--brand').trim()        || '#D4A017';
const NAV   = CS.getPropertyValue('--admin-nav').trim()    || '#0B1121';
const PALE  = CS.getPropertyValue('--primary-pale').trim() || '#fef8e8';

/* ── Palette ──────────────────────────────────────────────── */
const PAL = [BRAND,'#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#a855f7','#14b8a6','#f97316','#ec4899'];

/* ── Chart.js globals ─────────────────────────────────────── */
Chart.defaults.font.family = "'Inter','Poppins',system-ui,sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#64748b';

/* ── Shared tooltip style ─────────────────────────────────── */
const TIP = {
    backgroundColor:'rgba(15,23,42,.93)',
    titleColor:'#e2e8f0', bodyColor:'#94a3b8',
    padding:13, cornerRadius:12,
    borderColor:'rgba(255,255,255,.07)', borderWidth:1, caretSize:6,
    titleFont:{weight:'bold',size:12}, bodyFont:{size:11},
};

/* ── Gradient helper ──────────────────────────────────────── */
function vGrad(ctx, chart, r,g,b, a0=.32, a1=.02){
    const {chartArea} = chart;
    if(!chartArea) return `rgba(${r},${g},${b},${a0})`;
    const g2=chart.ctx.createLinearGradient(0,chartArea.top,0,chartArea.bottom);
    g2.addColorStop(0,`rgba(${r},${g},${b},${a0})`);
    g2.addColorStop(1,`rgba(${r},${g},${b},${a1})`);
    return g2;
}

/* ════════════════════════════════════════════════════════════
   CHART 1 — STUDENTS PER PROGRAMME
   Chart type: Polar Area
   Why: Shows BOTH the category name AND the magnitude in one
        visual — every programme gets a labelled wedge, bigger
        wedge = more students. Far more engaging than a plain bar.
   ════════════════════════════════════════════════════════════ */
new Chart(document.getElementById('chart-prog'),{
    type:'polarArea',
    data:{
        labels: progLabels,
        datasets:[{
            data: progCounts,
            backgroundColor: PAL.map(c=>c+'b0'),   // 69% opacity
            borderColor    : PAL,
            borderWidth    : 2,
            hoverBackgroundColor: PAL.map(c=>c+'dd'),
        }]
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{
            legend:{
                position:'right',
                labels:{
                    color:'#475569',
                    font:{size:11,weight:'600'},
                    padding:14, boxWidth:12, boxHeight:12,
                    usePointStyle:true, pointStyleWidth:10,
                }
            },
            tooltip:{
                ...TIP,
                callbacks:{
                    label: ctx=>`   ${ctx.label}: ${ctx.parsed.r} student${ctx.parsed.r!==1?'s':''}`
                }
            }
        },
        scales:{
            r:{
                ticks:{
                    backdropColor:'transparent',
                    color:'#94a3b8', font:{size:9},
                    stepSize:1, callback: v=>Number.isInteger(v)?v:''
                },
                grid:{ color:'rgba(148,163,184,.15)' },
                angleLines:{ color:'rgba(148,163,184,.12)' },
                pointLabels:{
                    display:false   // shown in legend instead
                }
            }
        }
    }
});

/* ════════════════════════════════════════════════════════════
   CHART 2 — STUDENT STATUS BREAKDOWN
   Chart type: Doughnut  (wide cutout 74%) + custom legend rows
   Why: Classic, instantly understood. Centre shows total headcount.
        Each segment label + % built in custom legend for precision.
   ════════════════════════════════════════════════════════════ */
const sLabels = Object.keys(statusData).map(s=>s.charAt(0).toUpperCase()+s.slice(1));
const sValues = Object.values(statusData);
const sTotal  = sValues.reduce((a,b)=>a+b,0);

/* Custom legend rows */
const legEl = document.getElementById('status-legend');
if(legEl){
    sLabels.forEach((lbl,i)=>{
        const pct = sTotal>0 ? Math.round(sValues[i]/sTotal*100) : 0;
        legEl.innerHTML+=`
        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
          <div style="display:flex;align-items:center;gap:.55rem;">
            <span style="width:11px;height:11px;border-radius:4px;background:${PAL[i%PAL.length]};flex-shrink:0"></span>
            <span style="font-size:.75rem;font-weight:600;color:#475569">${lbl}</span>
          </div>
          <div style="display:flex;align-items:center;gap:.55rem;">
            <span style="font-size:.75rem;font-weight:900;color:#0f172a">${sValues[i].toLocaleString()}</span>
            <span style="font-size:.63rem;font-weight:700;color:#94a3b8;background:#f1f5f9;
                         border-radius:99px;padding:.1rem .55rem">${pct}%</span>
          </div>
        </div>`;
    });
}

new Chart(document.getElementById('chart-status'),{
    type:'doughnut',
    data:{
        labels:sLabels,
        datasets:[{
            data:sValues,
            backgroundColor: PAL.slice(0,sLabels.length),
            borderWidth:3, borderColor:'#fff',
            hoverBorderWidth:3, hoverOffset:8,
        }]
    },
    plugins:[{
        id:'centreText',
        afterDraw(chart){
            const{ctx,chartArea:{left,top,right,bottom}}=chart;
            const cx=(left+right)/2, cy=(top+bottom)/2;
            ctx.save();
            ctx.font='900 1.85rem Inter,sans-serif';
            ctx.fillStyle='#0f172a';
            ctx.textAlign='center'; ctx.textBaseline='middle';
            ctx.fillText(sTotal.toLocaleString(), cx, cy-8);
            ctx.font='700 .6rem Inter,sans-serif';
            ctx.fillStyle='#94a3b8';
            ctx.letterSpacing='2px';
            ctx.fillText('STUDENTS', cx, cy+14);
            ctx.restore();
        }
    }],
    options:{
        responsive:true, maintainAspectRatio:false,
        cutout:'72%',
        plugins:{
            legend:{ display:false },
            tooltip:{
                ...TIP,
                callbacks:{
                    label:ctx=>{
                        const p=sTotal>0?Math.round(ctx.parsed/sTotal*100):0;
                        return `   ${ctx.label}: ${ctx.parsed} student${ctx.parsed!==1?'s':''} (${p}%)`;
                    }
                }
            }
        }
    }
});

/* ════════════════════════════════════════════════════════════
   CHART 3 — DAILY ATTENDANCE  (grouped column — clearest for GH admin)
   Each day = two side-by-side bars: Present (green) | Absent (red)
   Tooltip shows plain English: "Oct 08 — 42 present, 6 absent"
   ════════════════════════════════════════════════════════════ */
const attCtxEl = document.getElementById('chart-att');
new Chart(attCtxEl,{
    type:'bar',
    data:{
        labels:attLabels,
        datasets:[
            {
                label:'Present',
                data:presentArr,
                backgroundColor:'rgba(52,211,153,.85)',
                borderColor:'#10b981',
                borderWidth:1.5,
                borderRadius:{ topLeft:5,topRight:5 },
                borderSkipped:false,
                barPercentage:.8,
                categoryPercentage:.7,
            },
            {
                label:'Absent',
                data:absentArr,
                backgroundColor:'rgba(248,113,113,.85)',
                borderColor:'#ef4444',
                borderWidth:1.5,
                borderRadius:{ topLeft:5,topRight:5 },
                borderSkipped:false,
                barPercentage:.8,
                categoryPercentage:.7,
            }
        ]
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        interaction:{ mode:'index', intersect:false },
        plugins:{
            legend:{
                display:true, position:'top', align:'end',
                labels:{
                    color:'#64748b', boxWidth:11, boxHeight:11,
                    borderRadius:3, useBorderRadius:true,
                    font:{size:10,weight:'700'}, padding:14,
                }
            },
            tooltip:{
                backgroundColor:'rgba(13,10,46,.94)',
                titleColor:'#a5f3fc', bodyColor:'#e2e8f0',
                padding:13, cornerRadius:12,
                callbacks:{
                    title: ctx => `Date: ${ctx[0].label}`,
                    label: ctx => {
                        const n = ctx.parsed.y;
                        return `   ${ctx.dataset.label}: ${n} student${n!==1?'s':''}`;
                    },
                    afterBody: ctx => {
                        const p = ctx[0]?.parsed?.y ?? 0;
                        const a = ctx[1]?.parsed?.y ?? 0;
                        const tot = p + a;
                        if(tot === 0) return '';
                        const rate = Math.round(p/tot*100);
                        return [``, `   Attendance rate: ${rate}%`];
                    }
                }
            }
        },
        scales:{
            x:{
                grid:{ display:false },
                border:{ display:false },
                ticks:{ color:'#64748b', font:{size:9}, maxRotation:0, maxTicksLimit:14 }
            },
            y:{
                beginAtZero:true,
                border:{ display:false },
                grid:{ color:'rgba(148,163,184,.1)' },
                ticks:{
                    color:'#64748b', font:{size:9},
                    callback: v => Number.isInteger(v) ? v : ''
                }
            }
        }
    }
});

/* Totals + rate strip */
const tp=presentArr.reduce((a,b)=>a+b,0);
const ta=absentArr.reduce((a,b)=>a+b,0);
const elP=document.getElementById('att-p');
const elA=document.getElementById('att-a');
const elR=document.getElementById('att-rate-val');
if(elP) elP.textContent=tp.toLocaleString();
if(elA) elA.textContent=ta.toLocaleString();
if(elR){
    const tot14 = tp+ta;
    elR.textContent = tot14>0 ? Math.min(100,Math.round(tp/tot14*100))+'%' : '–';
}

/* ════════════════════════════════════════════════════════════
   CHART 4 — FEE COLLECTION GAUGE  (doughnut half-ring)
   Chart type: Doughnut (rotation -90deg, circumference 180deg)
   Why: Looks like a speedometer gauge — immediately shows
        how full the "tank" is. Administrators understand it
        even without reading the number.
   ════════════════════════════════════════════════════════════ */
const collPct = {{ $collPct }};
const gaugeColor = collPct>=75?'#10b981':(collPct>=50?'#f59e0b':'#ef4444');

new Chart(document.getElementById('chart-fee-gauge'),{
    type:'doughnut',
    data:{
        labels:['Collected','Remaining'],
        datasets:[{
            data:[ collPct, Math.max(0,100-collPct) ],
            backgroundColor:[ gaugeColor, '#e2e8f0' ],
            borderWidth:0,
            hoverBorderWidth:0,
            hoverOffset:0,
        }]
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        rotation:-90, circumference:180,
        cutout:'78%',
        plugins:{
            legend:{display:false},
            tooltip:{
                ...TIP,
                callbacks:{ label:ctx=>`   ${ctx.label}: ${ctx.parsed.toFixed(1)}%` }
            }
        }
    }
});

/* ── Animate fee bars ─────────────────────────────────────── */
setTimeout(()=>{
    const bc=document.getElementById('fee-bar-c');
    const bo=document.getElementById('fee-bar-o');
    const bp=document.getElementById('fee-prog');
    if(bc) bc.style.width='{{ $collPct }}%';
    if(bo) bo.style.width='{{ $outPct }}%';
    if(bp) bp.style.width='{{ $collPct }}%';
},400);

})();
</script>
@endpush
@endsection
