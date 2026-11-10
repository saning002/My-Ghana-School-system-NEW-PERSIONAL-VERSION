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
       BACK OF CARD
    ════════════════════════════════════════════════════════════════════ */
    .card-back { display: flex; flex-direction: column; background: #fff; }

    .bc-top {
        height: 4mm; background: {{ $navColor }};
        display: flex; align-items: center; justify-content: center;
    }
    .bc-top span {
        color: #fff; font-size: 1.6mm; font-weight: 700;
        letter-spacing: .5mm; text-transform: uppercase;
    }

    .bc-body {
        flex: 1; padding: 1.8mm 3mm; display: flex; flex-direction: column;
        justify-content: space-between; position: relative;
    }

    /* Contact section */
    .bc-contact { display: flex; flex-direction: column; gap: .6mm; }
    .bc-contact .item {
        display: flex; gap: 1mm; font-size: 1.6mm; line-height: 1.3;
    }
    .bc-contact .item i {
        color: {{ $primaryColor }}; font-size: 1.8mm; flex-shrink: 0;
        width: 2.5mm; text-align: center;
    }
    .bc-contact .item span { color: #475569; }

    /* Guardian info section */
    .bc-guardian {
        margin-top: 1.5mm; padding-top: 1mm;
        border-top: .2mm dashed #e2e8f0;
    }
    .bc-guardian .title {
        font-size: 1.3mm; font-weight: 700; color: #94a3b8;
        text-transform: uppercase; letter-spacing: .3mm; margin-bottom: .5mm;
    }
    .bc-guardian .row {
        display: flex; gap: 1mm; font-size: 1.6mm; color: #475569; line-height: 1.3;
    }
    .bc-guardian .row strong { color: #1e293b; font-weight: 600; min-width: 14mm; }

    /* Signature section */
    .bc-sigs {
        display: flex; justify-content: space-between;
        align-items: flex-end; margin-top: 1mm;
    }
    .bc-sig {
        width: 22mm; text-align: center;
    }
    .bc-sig .line {
        border-top: .3mm solid #64748b; margin-bottom: .5mm;
        height: 4mm;
    }
    .bc-sig .label {
        font-size: 1.2mm; color: #94a3b8; font-weight: 600;
        text-transform: uppercase; letter-spacing: .2mm;
    }

    /* QR area */
    .bc-qr {
        position: absolute; bottom: 5mm; right: 2.5mm;
        width: 8mm; height: 8mm; background: #f8fafc;
        border: .2mm solid #e2e8f0; border-radius: .5mm;
        display: flex; align-items: center; justify-content: center;
    }
    .bc-qr i { font-size: 5mm; color: #cbd5e1; }

    /* Footer bar */
    .bc-footer {
        height: 4mm; background: {{ $navColor }};
        display: flex; align-items: center; justify-content: center; gap: 1mm;
    }
    .bc-footer span {
        color: {{ $primaryColor }}; font-size: 1.3mm; font-weight: 700;
        letter-spacing: .2mm;
    }
    .bc-footer .dot { color: rgba(255,255,255,.3); }

    /* Terms text */
    .bc-terms {
        font-size: 1.2mm; color: #94a3b8; text-align: center;
        padding: .5mm 2mm; line-height: 1.2; font-style: italic;
    }

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

        {{-- Top bar --}}
        <div class="bc-top">
            <span>{{ $schoolName }}</span>
        </div>

        {{-- Body --}}
        <div class="bc-body">

            {{-- Contact section --}}
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

            {{-- Guardian info --}}
            <div class="bc-guardian">
                <div class="title">Emergency Contact</div>
                <div class="bc-guardian-rows">
                    <div class="row"><strong>Guardian:</strong> {{ $guardian }}</div>
                    <div class="row"><strong>Phone:</strong> {{ $guardianPh }}</div>
                </div>
            </div>

            {{-- Terms --}}
            <div class="bc-terms">
                This card is the property of {{ $schoolName }}. If found, please return to the school.
            </div>

            {{-- Signatures --}}
            <div class="bc-sigs">
                <div class="bc-sig">
                    <div class="line"></div>
                    <div class="label">Student</div>
                </div>
                <div class="bc-sig">
                    <div class="line"></div>
                    <div class="label">Headmaster</div>
                </div>
            </div>

            {{-- QR placeholder --}}
            <div class="bc-qr">
                <i class="fas fa-qrcode"></i>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bc-footer">
            <span>Powered by SaningTech Solutions</span>
            <span class="dot">·</span>
            <span style="color:rgba(255,255,255,.5);">{{ $academicYear }}</span>
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
