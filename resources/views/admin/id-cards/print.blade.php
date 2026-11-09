<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student ID Cards — {{ $schoolName }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Arial', sans-serif;
        background: #e2e8f0;
        padding: 10px;
    }

    /* ── Page toolbar ────────────────────────────────────────────────── */
    .toolbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        background: #0f172a;
        color: #fff;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,.2);
    }
    .toolbar h1 {
        font-size: 14px;
        font-weight: 700;
    }
    .toolbar h1 span { color: #94a3b8; font-weight: 400; }
    .toolbar .actions { display: flex; gap: 10px; }
    .toolbar button {
        background: #16a34a; color: #fff; border: none;
        padding: 8px 18px; border-radius: 8px;
        font-size: 13px; font-weight: 700; cursor: pointer;
    }
    .toolbar button.back {
        background: #475569;
    }
    .toolbar button:hover { filter: brightness(.9); }

    /* ── Card grid ─────────────────────────────────────────────────────── */
    .card-grid {
        max-width: 210mm;
        margin: 60px auto 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5mm;
        padding: 10mm 10mm;
    }

    /* ── ID Card ──────────────────────────────────────────────────────── */
    .id-card-pair {
        display: flex;
        gap: 3mm;
        margin-bottom: 2mm;
    }

    .id-card {
        width: 85.6mm;
        height: 54mm;
        border-radius: 3mm;
        overflow: hidden;
        position: relative;
        box-shadow: 0 2px 6px rgba(0,0,0,.15);
        background: #fff;
    }

    /* ── Front of card ──────────────────────────────────────────────────── */
    .card-front {
        display: flex;
        flex-direction: column;
    }

    .card-header {
        height: 14mm;
        background: {{ $navColor }};
        display: flex;
        align-items: center;
        padding: 0 2.5mm;
        gap: 2mm;
    }

    .card-logo {
        width: 8mm;
        height: 8mm;
        border-radius: 1.2mm;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
    }
    .card-logo img { width: 100%; height: 100%; object-fit: contain; }
    .card-logo .initial {
        font-size: 5mm; font-weight: 900;
        color: {{ $navColor }};
    }

    .card-school-name {
        color: #fff;
        font-size: 2.5mm;
        font-weight: 700;
        line-height: 1.1;
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .card-school-sub {
        color: {{ $primaryColor }};
        font-size: 1.8mm;
        font-weight: 600;
        margin-top: .3mm;
    }

    .card-body {
        flex: 1;
        display: flex;
        padding: 1.5mm 2.5mm;
        gap: 2mm;
    }

    .card-photo {
        width: 14mm;
        height: 18mm;
        border-radius: 1mm;
        overflow: hidden;
        background: #f1f5f9;
        flex-shrink: 0;
        border: 0.3mm solid #e2e8f0;
    }
    .card-photo img { width: 100%; height: 100%; object-fit: cover; }
    .card-photo .silhouette {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
    }
    .card-photo .silhouette i { font-size: 4mm; color: #cbd5e1; }

    .card-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: .5mm;
        min-width: 0;
    }

    .card-name {
        font-size: 2.5mm;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .card-field {
        font-size: 1.8mm;
        color: #475569;
        line-height: 1.3;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .card-field strong { color: #0f172a; font-weight: 600; }

    .card-footer {
        height: 4mm;
        background: {{ $primaryColor }};
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card-footer span {
        color: #fff;
        font-size: 1.5mm;
        font-weight: 700;
        letter-spacing: .3mm;
        text-transform: uppercase;
    }

    /* ── Back of card ─────────────────────────────────────────────────── */
    .card-back {
        background: #fff;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .back-header {
        height: 5mm;
        background: {{ $navColor }};
        display: flex;
        align-items: center;
        padding: 0 2.5mm;
    }
    .back-header span {
        color: #fff;
        font-size: 2mm;
        font-weight: 700;
    }

    .back-body {
        flex: 1;
        padding: 1.5mm 2.5mm;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .back-field {
        font-size: 1.8mm;
        color: #475569;
        line-height: 1.4;
    }
    .back-field strong { color: #0f172a; font-weight: 600; font-size: 1.8mm; }

    .back-signature {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 1mm;
    }
    .back-sig-line {
        border-top: 0.3mm solid #64748b;
        width: 20mm;
        text-align: center;
        font-size: 1.3mm;
        color: #94a3b8;
        padding-top: .5mm;
    }

    .back-qr {
        position: absolute;
        bottom: 1.5mm;
        right: 2mm;
        width: 8mm;
        height: 8mm;
    }

    .back-footer-bar {
        height: 3mm;
        background: {{ $primaryColor }};
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .back-footer-bar span {
        color: #fff;
        font-size: 1.2mm;
        font-weight: 700;
    }

    /* ── Print styles ─────────────────────────────────────────────────── */
    @media print {
        body { background: #fff; padding: 0; }
        .toolbar { display: none; }
        .card-grid {
            margin: 0;
            padding: 10mm 10mm;
            max-width: 100%;
        }
        .id-card { box-shadow: none; }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        .page-break {
            page-break-after: always;
        }

        /* Ensure colours print */
        * {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    @media screen {
        .page-break { display: none; }
    }
</style>
</head>
<body>

{{-- Toolbar (only visible on screen, hidden in print) --}}
<div class="toolbar">
    <h1>ID Cards — {{ $schoolName }} <span>({{ $students->count() }} students, {{ ceil($students->count() / 4) }} pages)</span></h1>
    <div class="actions">
        <button onclick="window.print()" class="back">
            <i class="fas fa-download"></i> Download / Save as PDF
        </button>
        <button onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <button onclick="window.close()" class="back">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</div>

{{-- Card grid — 4 pairs (front+back) per A4 page --}}
<div class="card-grid">

@php
    $count = 0;
    $perPage = 4;
@endphp

@foreach($students as $student)
@php
    $name       = $student->user?->full_name ?? 'Unknown';
    $idNumber   = $student->student_id ?? '—';
    $program    = $student->program?->name ?? '—';
    $dob        = $student->date_of_birth?->format('d/m/Y') ?? '—';
    $gender     = $student->gender ?? '—';
    $guardian   = $student->guardian_name ?? '—';
    $guardianPh = $student->guardian_phone ?? '—';
    $address    = $student->address ?? '—';
    $photo      = $student->photo;
    $photoUrl   = $photo ? Storage::disk('public')->url($photo) : null;
    $qrData     = urlencode(json_encode(['id' => $student->student_id, 'name' => $name, 'school' => $schoolName]));
    $count++;
    $isPageBreak = ($count % $perPage === 0) && ($count < $students->count());
@endphp

<div class="id-card-pair">

    {{-- FRONT --}}
    <div class="id-card card-front">
        <div class="card-header">
            <div class="card-logo">
                @if($siteLogoUrl)
                <img src="{{ Storage::disk('public')->url($siteLogoUrl) }}" alt="Logo">
                @else
                <span class="initial">{{ strtoupper(substr($schoolName, 0, 1)) }}</span>
                @endif
            </div>
            <div style="min-width:0;flex:1;">
                <div class="card-school-name">{{ $schoolName }}</div>
                @if($schoolSubtitle)
                <div class="card-school-sub">{{ $schoolSubtitle }}</div>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="card-photo">
                @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $name }}">
                @else
                <div class="silhouette"><i class="fas fa-user"></i></div>
                @endif
            </div>
            <div class="card-info">
                <div class="card-name">{{ $name }}</div>
                <div class="card-field"><strong>ID:</strong> {{ $idNumber }}</div>
                <div class="card-field"><strong>Class:</strong> {{ $program }}</div>
                <div class="card-field"><strong>DOB:</strong> {{ $dob }}</div>
            </div>
        </div>
        <div class="card-footer">
            <span>Student ID Card &nbsp;·&nbsp; {{ $academicYear }}</span>
        </div>
    </div>

    {{-- BACK --}}
    <div class="id-card card-back">
        <div class="back-header">
            <span>{{ $schoolName }}</span>
        </div>
        <div class="back-body">
            <div>
                <div class="back-field"><strong>Name:</strong> {{ $name }}</div>
                <div class="back-field"><strong>ID No:</strong> {{ $idNumber }}</div>
                <div class="back-field"><strong>Gender:</strong> {{ ucfirst($gender) }}</div>
                <div class="back-field"><strong>Guardian:</strong> {{ $guardian }}</div>
                <div class="back-field"><strong>Contact:</strong> {{ $guardianPh }}</div>
                @if($schoolAddress)
                <div class="back-field"><strong>School:</strong> {{ $schoolAddress }}</div>
                @endif
            </div>
            <div class="back-signature">
                <div class="back-sig-line">Student Sign</div>
                <div class="back-sig-line">Headmaster</div>
            </div>
        </div>
        <div class="back-footer-bar">
            <span>Powered by SaningTech Solutions · {{ $schoolPhone ?? '' }}</span>
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
