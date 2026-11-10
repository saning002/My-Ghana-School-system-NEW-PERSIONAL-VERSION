<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student ID Cards — {{ $schoolName }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
        background: #e2e8f0;
        padding: 10px;
    }

    /* ── Toolbar (screen only) ─────────────────────────────────────────── */
    .toolbar {
        position: fixed; top: 0; left: 0; right: 0;
        background: #0f172a; color: #fff;
        padding: 12px 24px; display: flex; align-items: center;
        justify-content: space-between; z-index: 1000;
        box-shadow: 0 4px 16px rgba(0,0,0,.25);
    }
    .toolbar h1 { font-size: 14px; font-weight: 700; }
    .toolbar h1 span { color: #94a3b8; font-weight: 400; margin-left: 8px; }
    .toolbar .actions { display: flex; gap: 10px; }
    .toolbar button {
        border: none; padding: 8px 20px; border-radius: 8px;
        font-size: 13px; font-weight: 700; cursor: pointer;
        transition: all .15s;
    }
    .toolbar button.print { background: #16a34a; color: #fff; }
    .toolbar button.save  { background: #2563eb; color: #fff; }
    .toolbar button.close { background: #475569; color: #fff; }
    .toolbar button:hover { filter: brightness(1.1); transform: translateY(-1px); }

    /* ── A4 Grid ───────────────────────────────────────────────────────── */
    .card-grid {
        max-width: 210mm; margin: 60px auto 20px;
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 4mm; padding: 8mm;
    }

    /* ── Card pair (front + back side by side) ─────────────────────────── */
    .id-card-pair {
        display: flex; gap: 2.5mm; margin-bottom: 1mm;
    }

    /* ── ID Card base ──────────────────────────────────────────────────── */
    .id-card {
        width: 85.6mm; height: 54mm;
        border-radius: 4mm; overflow: hidden;
        position: relative; background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }

    /* ════════════════════════════════════════════════════════════════════
       FRONT OF CARD
    ════════════════════════════════════════════════════════════════════ */
    .card-front { display: flex; flex-direction: column; }

    /* Header band — gradient from theme */
    .fc-header {
        height: 16mm;
        background: linear-gradient(135deg, {{ $navColor }} 0%, {{ $navColor }} 60%, color-mix(in srgb, {{ $navColor }} 70%, {{ $primaryColor }}) 100%);
        padding: 2mm 3mm; display: flex; align-items: center; gap: 2mm;
        position: relative;
    }
    /* Subtle dot pattern in header */
    .fc-header::after {
        content: ''; position: absolute; inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,.06) 1px, transparent 1px);
        background-size: 2mm 2mm; pointer-events: none;
    }

    .fc-logo {
        width: 10mm; height: 10mm; border-radius: 2mm;
        background: #fff; display: flex; align-items: center;
        justify-content: center; flex-shrink: 0; overflow: hidden;
        box-shadow: 0 1mm 2mm rgba(0,0,0,.2);
        z-index: 1;
    }
    .fc-logo img { width: 100%; height: 100%; object-fit: contain; padding: .5mm; }
    .fc-logo .initial {
        font-size: 5mm; font-weight: 900; color: {{ $navColor }};
    }

    .fc-school {
        z-index: 1; min-width: 0; flex: 1;
    }
    .fc-school .name {
        color: #fff; font-size: 2.6mm; font-weight: 800;
        line-height: 1.05; overflow: hidden; text-overflow: ellipsis;
        white-space: nowrap; letter-spacing: .1mm;
    }
    .fc-school .sub {
        color: {{ $primaryColor }}; font-size: 1.6mm; font-weight: 600;
        margin-top: .5mm; letter-spacing: .3mm; text-transform: uppercase;
    }

    /* Body section */
    .fc-body {
        flex: 1; display: flex; padding: 2mm 3mm; gap: 2.5mm; position: relative;
    }

    /* Watermark behind body */
    .fc-body::before {
        content: '{{ strtoupper($schoolName) }}';
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 6mm; font-weight: 900; color: #f1f5f9;
        white-space: nowrap; pointer-events: none; z-index: 0;
        opacity: .5;
    }

    .fc-photo {
        width: 14mm; height: 18mm; border-radius: 1.5mm;
        overflow: hidden; flex-shrink: 0; z-index: 1;
        border: .4mm solid {{ $primaryColor }};
        box-shadow: 0 .5mm 1.5mm rgba(0,0,0,.15);
        background: #f8fafc;
    }
    .fc-photo img { width: 100%; height: 100%; object-fit: cover; }
    .fc-photo .ph {
        width: 100%; height: 100%; display: flex;
        align-items: center; justify-content: center;
    }
    .fc-photo .ph i { font-size: 5mm; color: #cbd5e1; }

    .fc-info { flex: 1; z-index: 1; display: flex; flex-direction: column; justify-content: center; gap: 1mm; min-width: 0; }

    .fc-name {
        font-size: 2.8mm; font-weight: 800; color: #0f172a;
        line-height: 1.1; overflow: hidden; text-overflow: ellipsis;
        white-space: nowrap; padding-bottom: .8mm;
        border-bottom: .3mm solid {{ $primaryColor }};
        margin-bottom: .5mm;
    }

    .fc-row {
        display: flex; gap: 1mm; align-items: baseline;
        font-size: 1.8mm; line-height: 1.3;
    }
    .fc-row .lbl {
        color: #94a3b8; font-weight: 600; min-width: 10mm;
        text-transform: uppercase; font-size: 1.4mm; letter-spacing: .2mm;
    }
    .fc-row .val {
        color: #1e293b; font-weight: 600; flex: 1;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }

    /* Footer strip */
    .fc-footer {
        height: 5mm; background: {{ $primaryColor }};
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 3mm;
    }
    .fc-footer .label {
        color: #fff; font-size: 1.5mm; font-weight: 800;
        letter-spacing: .5mm; text-transform: uppercase;
    }
    .fc-footer .year {
        color: #fff; font-size: 1.8mm; font-weight: 700;
    }

    /* ════════════════════════════════════════════════════════════════════
       BACK OF CARD  —  Premium dark design
    ════════════════════════════════════════════════════════════════════ */
    .card-back {
        display: flex;
        flex-direction: column;
        background: linear-gradient(160deg, {{ $navColor }} 0%, color-mix(in srgb, {{ $navColor }} 80%, #000) 100%);
        position: relative;
        overflow: hidden;
    }

    /* Diagonal accent stripe */
    .card-back::before {
        content: '';
        position: absolute;
        top: -8mm; right: -8mm;
        width: 30mm; height: 30mm;
        background: {{ $primaryColor }};
        opacity: .12;
        border-radius: 50%;
        pointer-events: none;
    }

    /* Dot grid texture */
    .card-back::after {
        content: '';
        position: absolute; inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,.04) 1px, transparent 1px);
        background-size: 2.5mm 2.5mm;
        pointer-events: none;
    }

    .bc-top {
        height: 5mm;
        background: {{ $primaryColor }};
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 2.5mm; position: relative; z-index: 1;
    }
    .bc-top .title {
        color: #fff; font-size: 1.7mm; font-weight: 800;
        letter-spacing: .5mm; text-transform: uppercase;
    }
    .bc-top .year {
        color: rgba(255,255,255,.7); font-size: 1.5mm; font-weight: 600;
    }

    .bc-body {
        flex: 1; padding: 1.8mm 2.5mm 1mm;
        display: flex; flex-direction: column;
        justify-content: space-between;
        position: relative; z-index: 1;
    }

    /* School info row */
    .bc-school-row {
        display: flex; align-items: center; gap: 1.5mm;
        margin-bottom: 1.5mm; padding-bottom: 1mm;
        border-bottom: .2mm solid rgba(255,255,255,.12);
    }
    .bc-mini-logo {
        width: 7mm; height: 7mm; border-radius: 1mm;
        background: rgba(255,255,255,.12);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; overflow: hidden;
        border: .2mm solid rgba(255,255,255,.2);
    }
    .bc-mini-logo img { width: 100%; height: 100%; object-fit: contain; padding: .5mm; }
    .bc-mini-logo .init { font-size: 3.5mm; font-weight: 900; color: {{ $primaryColor }}; }
    .bc-school-name {
        color: #fff; font-size: 1.8mm; font-weight: 700;
        line-height: 1.1; flex: 1; min-width: 0;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }

    /* Contact items */
    .bc-contact {
        display: flex; flex-direction: column; gap: .7mm; margin-bottom: 1mm;
    }
    .bc-contact .item {
        display: flex; gap: 1mm; align-items: flex-start; font-size: 1.5mm; line-height: 1.3;
    }
    .bc-contact .item i {
        color: {{ $primaryColor }}; font-size: 1.6mm; flex-shrink: 0;
        width: 2.5mm; text-align: center; margin-top: .1mm;
    }
    .bc-contact .item span { color: rgba(255,255,255,.7); }

    /* Info section - student details on back */
    .bc-info {
        display: flex; flex-direction: column; gap: .5mm;
        padding: 1mm 1.5mm;
        background: rgba(255,255,255,.07);
        border-radius: 1mm;
        border: .2mm solid rgba(255,255,255,.1);
        margin-bottom: 1mm;
    }
    .bc-info-row {
        display: flex; gap: 1mm; font-size: 1.5mm; line-height: 1.3;
    }
    .bc-info-row .lbl {
        color: {{ $primaryColor }}; font-weight: 700;
        min-width: 12mm; font-size: 1.3mm;
        text-transform: uppercase; letter-spacing: .2mm;
    }
    .bc-info-row .val {
        color: rgba(255,255,255,.85); font-weight: 500;
        flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }

    /* Signature section */
    .bc-sigs {
        display: flex; justify-content: space-between;
        align-items: flex-end; gap: 2mm;
    }
    .bc-sig { flex: 1; text-align: center; }
    .bc-sig .line {
        height: 3.5mm;
        border-bottom: .3mm solid rgba(255,255,255,.35);
        margin-bottom: .5mm;
    }
    .bc-sig .label {
        font-size: 1.1mm; color: rgba(255,255,255,.45);
        font-weight: 600; text-transform: uppercase; letter-spacing: .2mm;
    }

    /* QR box */
    .bc-qr-box {
        width: 10mm; height: 10mm; flex-shrink: 0;
        background: rgba(255,255,255,.08);
        border: .2mm solid rgba(255,255,255,.2);
        border-radius: 1mm;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: .2mm;
    }
    .bc-qr-box i { font-size: 4.5mm; color: rgba(255,255,255,.5); }
    .bc-qr-box span { font-size: 1mm; color: rgba(255,255,255,.3); font-weight: 600; }

    /* Footer */
    .bc-footer {
        height: 4mm;
        background: {{ $primaryColor }};
        display: flex; align-items: center; justify-content: center;
        gap: 1mm; position: relative; z-index: 1;
    }
    .bc-footer-text {
        color: rgba(255,255,255,.9); font-size: 1.3mm; font-weight: 700;
        letter-spacing: .3mm; text-transform: uppercase;
    }
    .bc-footer-dot { color: rgba(255,255,255,.35); font-size: 1.5mm; }

    /* ── Print ─────────────────────────────────────────────────────────── */
    @media print {
        body { background: #fff; padding: 0; }
        .toolbar { display: none !important; }
        .card-grid { margin: 0; padding: 8mm; max-width: 100%; }
        .id-card { box-shadow: none; }

        @page { size: A4 portrait; margin: 0; }
        .page-break { page-break-after: always; }

        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }

    @media screen { .page-break { display: none; } }
</style>
</head>
<body>

<div class="toolbar">
    <h1>Student ID Cards <span>{{ $schoolName }} — {{ $students->count() }} students, {{ ceil($students->count() / 4) }} pages</span></h1>
    <div class="actions">
        <button onclick="window.print()" class="save"><i class="fas fa-download"></i> Save as PDF</button>
        <button onclick="window.print()" class="print"><i class="fas fa-print"></i> Print</button>
        <button onclick="window.close()" class="close"><i class="fas fa-times"></i> Close</button>
    </div>
</div>

<div class="card-grid">

@php $count = 0; $perPage = 4; @endphp

@foreach($students as $student)
@php
    $name       = $student->user?->full_name ?? 'Unknown Student';
    $idNumber   = $student->student_id ?? '—';
    $program    = $student->program?->name ?? '—';
    $dob        = $student->date_of_birth?->format('d/m/Y') ?? '—';
    $gender     = $student->gender ?? '—';
    $guardian   = $student->guardian_name ?? '—';
    $guardianPh = $student->guardian_phone ?? '—';
    $address    = $student->address ?? '—';
    $photo      = $student->photo;
    $photoUrl   = $photo ? Storage::disk('public')->url($photo) : null;
    $logoUrl    = $siteLogoUrl ? Storage::disk('public')->url($siteLogoUrl) : null;
    $count++;
    $isPageBreak = ($count % $perPage === 0) && ($count < $students->count());
@endphp

<div class="id-card-pair">

    {{-- ════ FRONT ════ --}}
    <div class="id-card card-front">

        {{-- Header with logo and school name --}}
        <div class="fc-header">
            <div class="fc-logo">
                @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo">
                @else
                <span class="initial">{{ strtoupper(substr($schoolName, 0, 1)) }}</span>
                @endif
            </div>
            <div class="fc-school">
                <div class="name">{{ $schoolName }}</div>
                @if($schoolSubtitle)
                <div class="sub">{{ $schoolSubtitle }}</div>
                @endif
            </div>
        </div>

        {{-- Body: photo + info --}}
        <div class="fc-body">
            <div class="fc-photo">
                @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $name }}">
                @else
                <div class="ph"><i class="fas fa-user"></i></div>
                @endif
            </div>
            <div class="fc-info">
                <div class="fc-name">{{ $name }}</div>
                <div class="fc-row">
                    <span class="lbl">ID</span>
                    <span class="val">{{ $idNumber }}</span>
                </div>
                <div class="fc-row">
                    <span class="lbl">Class</span>
                    <span class="val">{{ $program }}</span>
                </div>
                <div class="fc-row">
                    <span class="lbl">DOB</span>
                    <span class="val">{{ $dob }}</span>
                </div>
                <div class="fc-row">
                    <span class="lbl">Gender</span>
                    <span class="val">{{ ucfirst($gender) }}</span>
                </div>
            </div>
        </div>

        {{-- Footer strip --}}
        <div class="fc-footer">
            <span class="label">Student ID Card</span>
            <span class="year">{{ $academicYear }}</span>
        </div>
    </div>

    {{-- ════ BACK ════ --}}
    <div class="id-card card-back">

        {{-- Gold top bar --}}
        <div class="bc-top">
            <span class="title">Student Information</span>
            <span class="year">{{ $academicYear }}</span>
        </div>

        {{-- Body --}}
        <div class="bc-body">

            {{-- School mini-header --}}
            <div class="bc-school-row">
                <div class="bc-mini-logo">
                    @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo">
                    @else
                    <span class="init">{{ strtoupper(substr($schoolName, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="bc-school-name">{{ $schoolName }}</div>
            </div>

            {{-- Student info panel --}}
            <div class="bc-info">
                <div class="bc-info-row">
                    <span class="lbl">Guardian</span>
                    <span class="val">{{ $guardian }}</span>
                </div>
                <div class="bc-info-row">
                    <span class="lbl">Phone</span>
                    <span class="val">{{ $guardianPh }}</span>
                </div>
            </div>

            {{-- School contact --}}
            <div class="bc-contact">
                @if($schoolAddress)
                <div class="item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ $schoolAddress }}</span>
                </div>
                @endif
                @if($schoolPhone)
                <div class="item">
                    <i class="fas fa-phone"></i>
                    <span>{{ $schoolPhone }}</span>
                </div>
                @endif
                @if($schoolEmail)
                <div class="item">
                    <i class="fas fa-envelope"></i>
                    <span>{{ $schoolEmail }}</span>
                </div>
                @endif
            </div>

            {{-- Signatures + QR --}}
            <div style="display:flex;align-items:flex-end;gap:1.5mm;">
                <div class="bc-sigs" style="flex:1;">
                    <div class="bc-sig">
                        <div class="line"></div>
                        <div class="label">Student</div>
                    </div>
                    <div class="bc-sig">
                        <div class="line"></div>
                        <div class="label">Headmaster</div>
                    </div>
                </div>
                <div class="bc-qr-box">
                    <i class="fas fa-qrcode"></i>
                    <span>Scan</span>
                </div>
            </div>

        </div>

        {{-- Gold footer --}}
        <div class="bc-footer">
            <span class="bc-footer-text">If found, return to school</span>
            <span class="bc-footer-dot">·</span>
            <span class="bc-footer-text">SaningTech Solutions</span>
        </div>
    </div>

</div>

@if($isPageBreak)
<div class="page-break"></div>
@endif

@endforeach

</div>

</body>
</html>
