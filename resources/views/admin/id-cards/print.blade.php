<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ID Cards — {{ $schoolName }}</title>
{{-- Google Font: Inter (clean, professional) --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
{{-- QR code library --}}
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
    background: #cbd5e1;
    padding: 10px;
}

/* ── Toolbar ───────────────────────────────────────────────────────── */
.toolbar {
    position:fixed; top:0; left:0; right:0;
    background:#0f172a; color:#fff;
    padding:10px 24px;
    display:flex; align-items:center; justify-content:space-between;
    z-index:1000; box-shadow:0 4px 20px rgba(0,0,0,.3);
}
.toolbar h1 { font-size:13px; font-weight:700; }
.toolbar h1 span { color:#64748b; font-weight:400; margin-left:8px; }
.toolbar .btns { display:flex; gap:8px; }
.tbtn {
    border:none; padding:7px 18px; border-radius:7px;
    font-size:12px; font-weight:700; cursor:pointer;
    font-family:inherit; transition:filter .15s;
}
.tbtn:hover { filter:brightness(1.12); }
.tbtn-green  { background:#16a34a; color:#fff; }
.tbtn-blue   { background:#2563eb; color:#fff; }
.tbtn-slate  { background:#475569; color:#fff; }

/* ── A4 Sheet ──────────────────────────────────────────────────────── */
.a4-sheet {
    max-width:210mm; margin:56px auto 20px;
    background:#fff;
    padding:0;
    position:relative;
}

/* ── Cut lines grid ────────────────────────────────────────────────── */
/* Each row contains one front+back pair */
.cut-row {
    display:flex;
    border-bottom:1px dashed #94a3b8;
    position:relative;
}
.cut-row:last-child { border-bottom:none; }

/* Vertical cut line between front and back */
.cut-row .cut-mid {
    position:absolute; top:0; bottom:0;
    left:50%; width:0;
    border-left:1px dashed #94a3b8;
    pointer-events:none;
}

/* Cut corner marks */
.cut-row::before,
.cut-row::after {
    content:'';
    position:absolute;
    width:3mm; height:3mm;
    border-left:0.5mm solid #94a3b8;
    border-top:0.5mm solid #94a3b8;
}
.cut-row::before { top:0; left:0; }
.cut-row::after  { top:0; right:0; transform:scaleX(-1); }

/* ── ID Card base ──────────────────────────────────────────────────── */
.id-card {
    width:50%;       /* half the A4 width = one card per column */
    height:60mm;     /* slightly taller for readability */
    overflow:hidden;
    position:relative;
    display:flex;
    flex-direction:column;
}

/* ════════════════════════════════════════════════════════════════════
   FRONT
════════════════════════════════════════════════════════════════════ */
.cf {
    background:#fff;
}

/* Header */
.cf-hd {
    height:16mm;
    background:linear-gradient(140deg,
        {{ $navColor }} 0%,
        color-mix(in srgb, {{ $navColor }} 65%, {{ $primaryColor }}) 100%
    );
    display:flex; align-items:center; padding:0 3.5mm; gap:2.5mm;
    position:relative; overflow:hidden;
    flex-shrink:0;
}
/* Dot texture */
.cf-hd::after {
    content:''; position:absolute; inset:0;
    background-image:radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size:2.2mm 2.2mm; pointer-events:none;
}
/* Glow orb */
.cf-hd::before {
    content:''; position:absolute;
    right:-8mm; top:-8mm;
    width:24mm; height:24mm; border-radius:50%;
    background:{{ $primaryColor }}; opacity:.18; filter:blur(5mm);
    pointer-events:none;
}

.cf-logo {
    width:11mm; height:11mm; border-radius:2mm; background:#fff;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; overflow:hidden;
    box-shadow:0 1mm 3mm rgba(0,0,0,.25);
    position:relative; z-index:1;
}
.cf-logo img { width:100%; height:100%; object-fit:contain; padding:.8mm; }
.cf-logo .init { font-size:5.5mm; font-weight:900; color:{{ $navColor }}; }

.cf-sch { z-index:1; min-width:0; flex:1; }
.cf-sch .nm {
    color:#fff; font-size:2.7mm; font-weight:800;
    line-height:1.1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    letter-spacing:.05mm;
}
.cf-sch .sb {
    color:{{ $primaryColor }}; font-size:1.6mm; font-weight:600;
    margin-top:.4mm; letter-spacing:.4mm; text-transform:uppercase;
}

/* Body */
.cf-body {
    flex:1; display:flex; padding:2mm 3.5mm; gap:3mm; position:relative;
    min-height:0;
}

/* Faint watermark */
.cf-body::before {
    content:'{{ strtoupper(substr($schoolName,0,1)) }}';
    position:absolute; top:50%; left:50%;
    transform:translate(-50%,-50%) rotate(-25deg);
    font-size:30mm; font-weight:900; color:#f1f5f9;
    pointer-events:none; z-index:0; line-height:1;
    opacity:.6;
}

.cf-photo {
    width:16mm; height:19mm; border-radius:1.5mm;
    overflow:hidden; flex-shrink:0; z-index:1;
    border:.5mm solid {{ $primaryColor }};
    box-shadow:0 .5mm 2mm rgba(0,0,0,.18);
    background:#f8fafc; align-self:center;
}
.cf-photo img { width:100%; height:100%; object-fit:cover; }
.cf-photo .ph {
    width:100%; height:100%; display:flex; align-items:center; justify-content:center;
}
.cf-photo .ph i { font-size:7mm; color:#cbd5e1; }

.cf-info { flex:1; z-index:1; display:flex; flex-direction:column; justify-content:center; gap:.9mm; min-width:0; }

.cf-name {
    font-size:3mm; font-weight:800; color:#0f172a;
    line-height:1.1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    padding-bottom:1mm; margin-bottom:.5mm;
    border-bottom:.35mm solid {{ $primaryColor }};
}

.cf-row {
    display:flex; align-items:baseline; gap:1.2mm;
    font-size:1.9mm; line-height:1.35;
}
.cf-row .l {
    color:#94a3b8; font-weight:600; min-width:10mm;
    font-size:1.4mm; text-transform:uppercase; letter-spacing:.2mm; flex-shrink:0;
}
.cf-row .v {
    color:#1e293b; font-weight:600; flex:1;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}
.cf-row .v.role-badge {
    display:inline-block;
    background:{{ $primaryColor }}22;
    color:{{ $primaryColor }};
    border:0.3mm solid {{ $primaryColor }}55;
    border-radius:1mm; padding:0 1.5mm;
    font-size:1.6mm; font-weight:700;
}

/* Footer */
.cf-ft {
    height:5.5mm; background:{{ $primaryColor }};
    display:flex; align-items:center; justify-content:space-between;
    padding:0 3.5mm; flex-shrink:0;
}
.cf-ft .label {
    color:#fff; font-size:1.5mm; font-weight:800;
    letter-spacing:.6mm; text-transform:uppercase;
}
.cf-ft .yr {
    color:rgba(255,255,255,.8); font-size:1.6mm; font-weight:700;
}

/* ════════════════════════════════════════════════════════════════════
   BACK  —  Dark premium
════════════════════════════════════════════════════════════════════ */
.cb {
    background:linear-gradient(155deg,
        {{ $navColor }} 0%,
        color-mix(in srgb, {{ $navColor }} 85%, #000) 60%,
        color-mix(in srgb, {{ $navColor }} 70%, #000) 100%
    );
    position:relative; overflow:hidden;
}

/* Texture + orbs */
.cb::after {
    content:''; position:absolute; inset:0;
    background-image:radial-gradient(circle, rgba(255,255,255,.04) 1px, transparent 1px);
    background-size:2.5mm 2.5mm; pointer-events:none;
}
.cb::before {
    content:''; position:absolute; border-radius:50%;
    width:30mm; height:30mm;
    top:-12mm; right:-12mm;
    background:{{ $primaryColor }}; opacity:.10; filter:blur(8mm);
    pointer-events:none;
}

/* Top gold bar */
.cb-top {
    height:5mm; background:{{ $primaryColor }};
    display:flex; align-items:center; justify-content:space-between;
    padding:0 3mm; flex-shrink:0; position:relative; z-index:2;
}
.cb-top .tl { color:#fff; font-size:1.7mm; font-weight:800; letter-spacing:.5mm; text-transform:uppercase; }
.cb-top .tr { color:rgba(255,255,255,.75); font-size:1.5mm; font-weight:600; }

.cb-body {
    flex:1; padding:1.8mm 3mm 1.5mm; display:flex; gap:2mm;
    position:relative; z-index:2; min-height:0;
}

/* Left column: school info + details */
.cb-left { flex:1; display:flex; flex-direction:column; justify-content:space-between; min-width:0; }

/* School row */
.cb-sch-row {
    display:flex; align-items:center; gap:1.5mm;
    padding-bottom:1.2mm; margin-bottom:1.2mm;
    border-bottom:.2mm solid rgba(255,255,255,.12);
}
.cb-sch-logo {
    width:7mm; height:7mm; border-radius:1.2mm;
    background:rgba(255,255,255,.1); border:.2mm solid rgba(255,255,255,.2);
    display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden;
}
.cb-sch-logo img { width:100%; height:100%; object-fit:contain; padding:.5mm; }
.cb-sch-logo .i { font-size:3.5mm; font-weight:900; color:{{ $primaryColor }}; }
.cb-sch-name { color:#fff; font-size:1.9mm; font-weight:700; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* Info panels */
.cb-panel {
    background:rgba(255,255,255,.07);
    border:.2mm solid rgba(255,255,255,.1);
    border-radius:1mm; padding:1mm 1.5mm;
    margin-bottom:1.2mm;
}
.cb-panel-title {
    font-size:1.2mm; font-weight:700; text-transform:uppercase;
    letter-spacing:.4mm; color:{{ $primaryColor }}; margin-bottom:.6mm;
}
.cb-row {
    display:flex; gap:1mm; font-size:1.6mm; line-height:1.4;
}
.cb-row .l {
    color:rgba(255,255,255,.45); font-size:1.3mm; font-weight:700;
    text-transform:uppercase; letter-spacing:.15mm; min-width:14mm; flex-shrink:0;
}
.cb-row .v {
    color:rgba(255,255,255,.88); font-weight:500; flex:1;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}

/* Contact with icons */
.cb-contact { display:flex; flex-direction:column; gap:.6mm; }
.cb-ci {
    display:flex; gap:1.2mm; align-items:flex-start; font-size:1.6mm; line-height:1.3;
}
.cb-ci i { color:{{ $primaryColor }}; font-size:1.6mm; flex-shrink:0; width:2.5mm; text-align:center; margin-top:.1mm; }
.cb-ci span { color:rgba(255,255,255,.65); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* Signatures */
.cb-sigs {
    display:flex; justify-content:space-between; gap:2mm;
    margin-top:1mm;
}
.cb-sig { flex:1; text-align:center; }
.cb-sig .ln {
    height:3.5mm; border-bottom:.3mm solid rgba(255,255,255,.3); margin-bottom:.5mm;
}
.cb-sig .lb {
    font-size:1.1mm; color:rgba(255,255,255,.4); font-weight:600;
    text-transform:uppercase; letter-spacing:.2mm;
}

/* Right column: QR */
.cb-right {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    gap:1.2mm; flex-shrink:0; width:14mm;
}
.cb-qr-wrap {
    width:14mm; height:14mm; background:#fff; border-radius:1.5mm;
    display:flex; align-items:center; justify-content:center;
    padding:.5mm; border:.3mm solid rgba(255,255,255,.2);
}
.cb-qr-wrap canvas { width:100% !important; height:100% !important; }
.cb-qr-lbl {
    font-size:1.1mm; color:rgba(255,255,255,.4); font-weight:600;
    text-transform:uppercase; letter-spacing:.3mm; text-align:center;
}
.cb-qr-url {
    font-size:1mm; color:rgba(255,255,255,.3); text-align:center;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:14mm;
}

/* Bottom gold bar */
.cb-ft {
    height:4.5mm; background:{{ $primaryColor }};
    display:flex; align-items:center; justify-content:center;
    gap:2mm; flex-shrink:0; position:relative; z-index:2;
}
.cb-ft-t { color:rgba(255,255,255,.9); font-size:1.3mm; font-weight:700; letter-spacing:.3mm; text-transform:uppercase; }
.cb-ft-d { color:rgba(255,255,255,.35); }

/* ── Print ─────────────────────────────────────────────────────────── */
@media print {
    body { background:#fff; padding:0; margin:0; }
    .toolbar { display:none !important; }
    .a4-sheet { margin:0; max-width:100%; }

    @page { size:A4 portrait; margin:0; }
    .pg-break { page-break-after:always; }

    * { -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; }
}
@media screen { .pg-break { display:none; } }
</style>
</head>
<body>

{{-- Toolbar --}}
<div class="toolbar">
    <h1>ID Cards <span>{{ $schoolName }} — {{ $people->count() }} card(s), {{ ceil($people->count() / 4) }} page(s)</span></h1>
    <div class="btns">
        <button onclick="window.print()" class="tbtn tbtn-blue"><i class="fas fa-download"></i> Save as PDF</button>
        <button onclick="window.print()" class="tbtn tbtn-green"><i class="fas fa-print"></i> Print</button>
        <button onclick="window.close()" class="tbtn tbtn-slate"><i class="fas fa-times"></i> Close</button>
    </div>
</div>

{{-- A4 Sheet --}}
<div class="a4-sheet">

@php $count = 0; @endphp

@foreach($people as $p)
@php
    $name       = $p['name'];
    $idNumber   = $p['id_number'];
    $role       = $p['role'];
    $dept       = $p['dept'];
    $dob        = $p['dob'];
    $gender     = $p['gender'];
    $phone      = $p['phone'];
    $email      = $p['email'];
    $photoPath  = $p['photo'];
    $guardian   = $p['guardian'];
    $guardianPh = $p['guardianPh'];
    $qrData     = $p['qr_data'];
    $qrId       = 'qr_' . $loop->index;
    $isStudent  = $p['type'] === 'student';

    $photoUrl = $photoPath ? Storage::disk('public')->url($photoPath) : null;
    $logoUrl  = $siteLogoUrl ? Storage::disk('public')->url($siteLogoUrl) : null;

    $count++;
    $isNewPage = ($count > 4) && (($count - 1) % 4 === 0);
@endphp

@if($isNewPage)
<div class="pg-break"></div>
@endif

<div class="cut-row">
    <div class="cut-mid"></div>

    {{-- ════ FRONT ════ --}}
    <div class="id-card cf">

        {{-- Header --}}
        <div class="cf-hd">
            <div class="cf-logo">
                @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo">
                @else
                <span class="init">{{ strtoupper(substr($schoolName,0,1)) }}</span>
                @endif
            </div>
            <div class="cf-sch">
                <div class="nm">{{ $schoolName }}</div>
                @if($schoolSubtitle)
                <div class="sb">{{ $schoolSubtitle }}</div>
                @endif
            </div>
        </div>

        {{-- Body --}}
        <div class="cf-body">
            <div class="cf-photo">
                @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $name }}">
                @else
                <div class="ph"><i class="fas fa-user"></i></div>
                @endif
            </div>
            <div class="cf-info">
                <div class="cf-name">{{ $name }}</div>
                <div class="cf-row">
                    <span class="l">{{ $isStudent ? 'ID' : 'Staff ID' }}</span>
                    <span class="v">{{ $idNumber }}</span>
                </div>
                <div class="cf-row">
                    <span class="l">{{ $isStudent ? 'Class' : 'Role' }}</span>
                    <span class="v{{ $isStudent ? '' : ' role-badge' }}">{{ $isStudent ? ($dept ?? '—') : $role }}</span>
                </div>
                @if($isStudent && $dob)
                <div class="cf-row">
                    <span class="l">DOB</span>
                    <span class="v">{{ $dob }}</span>
                </div>
                @endif
                @if($gender)
                <div class="cf-row">
                    <span class="l">Gender</span>
                    <span class="v">{{ ucfirst($gender) }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="cf-ft">
            <span class="label">{{ $isStudent ? 'Student ID Card' : 'Staff ID Card' }}</span>
            <span class="yr">{{ $academicYear }}</span>
        </div>
    </div>

    {{-- ════ BACK ════ --}}
    <div class="id-card cb">

        {{-- Gold top bar --}}
        <div class="cb-top">
            <span class="tl">{{ $isStudent ? 'Student Information' : 'Staff Information' }}</span>
            <span class="tr">{{ $academicYear }}</span>
        </div>

        {{-- Body --}}
        <div class="cb-body">

            {{-- Left col --}}
            <div class="cb-left">

                {{-- School name mini row --}}
                <div class="cb-sch-row">
                    <div class="cb-sch-logo">
                        @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="">
                        @else
                        <span class="i">{{ strtoupper(substr($schoolName,0,1)) }}</span>
                        @endif
                    </div>
                    <span class="cb-sch-name">{{ $schoolName }}</span>
                </div>

                {{-- Personal info panel --}}
                <div class="cb-panel">
                    <div class="cb-panel-title">{{ $isStudent ? 'Emergency Contact' : 'Staff Details' }}</div>
                    @if($isStudent && $guardian)
                    <div class="cb-row"><span class="l">Guardian</span><span class="v">{{ $guardian }}</span></div>
                    @endif
                    @if($isStudent && $guardianPh)
                    <div class="cb-row"><span class="l">Phone</span><span class="v">{{ $guardianPh }}</span></div>
                    @endif
                    @if(!$isStudent && $phone)
                    <div class="cb-row"><span class="l">Phone</span><span class="v">{{ $phone }}</span></div>
                    @endif
                    @if(!$isStudent && $email)
                    <div class="cb-row"><span class="l">Email</span><span class="v">{{ $email }}</span></div>
                    @endif
                    @if($dept && !$isStudent)
                    <div class="cb-row"><span class="l">Dept</span><span class="v">{{ $dept }}</span></div>
                    @endif
                </div>

                {{-- School contact --}}
                <div class="cb-contact">
                    @if($schoolAddress)
                    <div class="cb-ci"><i class="fas fa-map-marker-alt"></i><span>{{ $schoolAddress }}</span></div>
                    @endif
                    @if($schoolPhone)
                    <div class="cb-ci"><i class="fas fa-phone"></i><span>{{ $schoolPhone }}</span></div>
                    @endif
                    @if($schoolEmail)
                    <div class="cb-ci"><i class="fas fa-envelope"></i><span>{{ $schoolEmail }}</span></div>
                    @endif
                </div>

                {{-- Signatures --}}
                <div class="cb-sigs">
                    <div class="cb-sig"><div class="ln"></div><div class="lb">{{ $isStudent ? 'Student' : 'Staff' }}</div></div>
                    <div class="cb-sig"><div class="ln"></div><div class="lb">Headmaster</div></div>
                </div>

            </div>

            {{-- Right col: QR --}}
            <div class="cb-right">
                <div class="cb-qr-wrap">
                    <canvas id="{{ $qrId }}"></canvas>
                </div>
                <div class="cb-qr-lbl">Scan</div>
                <div class="cb-qr-url">{{ rtrim(parse_url($schoolWebsite, PHP_URL_HOST) ?? $schoolWebsite, '/') }}</div>
            </div>

        </div>

        {{-- Gold bottom bar --}}
        <div class="cb-ft">
            <span class="cb-ft-t">{{ $schoolName }}</span>
            <span class="cb-ft-d">·</span>
            <span class="cb-ft-t">If found, return to school</span>
        </div>
    </div>

</div>{{-- end cut-row --}}

@endforeach

</div>{{-- end a4-sheet --}}

<script>
// Generate QR codes pointing to the school website
document.addEventListener('DOMContentLoaded', function() {
    @foreach($people as $p)
    @php $qrId = 'qr_' . $loop->index; @endphp
    (function() {
        var canvas = document.getElementById('{{ $qrId }}');
        if (!canvas) return;
        QRCode.toCanvas(canvas, '{{ addslashes($p['qr_data']) }}', {
            width: 56,
            margin: 1,
            color: { dark: '#0f172a', light: '#ffffff' },
            errorCorrectionLevel: 'M'
        }, function(err) {
            if (err) console.warn('QR error:', err);
        });
    })();
    @endforeach
});
</script>

</body>
</html>
