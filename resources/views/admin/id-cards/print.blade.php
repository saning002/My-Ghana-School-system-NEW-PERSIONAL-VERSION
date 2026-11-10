<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ID Cards — {{ $schoolName }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family:'Inter','Segoe UI',Arial,sans-serif;
    background:#cbd5e1;
    padding:0;
}

/* ══ Toolbar ══ */
.tb {
    position:fixed; top:0; left:0; right:0; z-index:999;
    background:#0f172a; color:#fff;
    padding:10px 20px;
    display:flex; align-items:center; justify-content:space-between;
    box-shadow:0 4px 20px rgba(0,0,0,.3);
}
.tb h1 { font-size:13px; font-weight:700; }
.tb h1 span { color:#64748b; font-weight:400; margin-left:8px; font-size:12px; }
.tb .btns { display:flex; gap:8px; }
.btn { border:none; padding:7px 16px; border-radius:7px; font-size:12px; font-weight:700; cursor:pointer; font-family:inherit; }
.btn:hover { filter:brightness(1.1); }
.btn-g { background:#16a34a; color:#fff; }
.btn-b { background:#2563eb; color:#fff; }
.btn-s { background:#475569; color:#fff; }

/* ══ A4 Page ══ */
.a4 {
    width:210mm;
    margin:52px auto 20px;
    background:#fff;
    min-height:297mm;
}

/* ══ Card row ══ */
/*  Each row = one front card (left half) + one back card (right half)
    4 rows per A4 page — 60mm height each row  */
.row {
    display:flex;
    width:210mm;
    height:60mm;
    position:relative;
    /* Dashed bottom cut line */
    border-bottom:0.5mm dashed #94a3b8;
}
.row:last-child {
    border-bottom:none;
}

/* Dashed vertical centre cut line */
.row::after {
    content:'';
    position:absolute;
    top:0; bottom:0;
    left:50%;
    border-left:0.5mm dashed #94a3b8;
    pointer-events:none;
}

/* Corner tick marks — 4 corners per row */
.row::before {
    content:'';
    position:absolute;
    top:0; left:0;
    width:4mm; height:4mm;
    border-left:0.4mm solid #64748b;
    border-top:0.4mm solid #64748b;
}
.row .tr-mark {
    position:absolute; top:0; right:0;
    width:4mm; height:4mm;
    border-right:0.4mm solid #64748b;
    border-top:0.4mm solid #64748b;
    pointer-events:none;
}
.row .bl-mark {
    position:absolute; bottom:0; left:0;
    width:4mm; height:4mm;
    border-left:0.4mm solid #64748b;
    border-bottom:0.4mm solid #64748b;
    pointer-events:none;
}
.row .br-mark {
    position:absolute; bottom:0; right:0;
    width:4mm; height:4mm;
    border-right:0.4mm solid #64748b;
    border-bottom:0.4mm solid #64748b;
    pointer-events:none;
}

/* ══ Card base ══ */
.card {
    width:50%;
    height:60mm;
    overflow:hidden;
    display:flex;
    flex-direction:column;
    position:relative;
}

/* ══════════════════════════════════════════════════
   FRONT
══════════════════════════════════════════════════ */
.cf {
    background:#fff;
}

/* Header */
.cf-hd {
    height:16mm;
    background:linear-gradient(135deg,
        {{ $navColor }} 0%,
        color-mix(in srgb, {{ $navColor }} 60%, {{ $primaryColor }}) 100%
    );
    display:flex; align-items:center; padding:0 3mm; gap:2.5mm;
    position:relative; overflow:hidden; flex-shrink:0;
}
.cf-hd::before {
    content:''; position:absolute; right:-8mm; top:-8mm;
    width:26mm; height:26mm; border-radius:50%;
    background:{{ $primaryColor }}; opacity:.12; filter:blur(5mm); pointer-events:none;
}
.cf-hd::after {
    content:''; position:absolute; inset:0;
    background-image:radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
    background-size:2mm 2mm; pointer-events:none;
}

.cf-logo {
    width:11mm; height:11mm; border-radius:2mm; background:#fff;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; overflow:hidden;
    box-shadow:0 1mm 3mm rgba(0,0,0,.2);
    z-index:1;
}
.cf-logo img { width:100%; height:100%; object-fit:contain; padding:.8mm; }
.cf-logo .init-logo { font-size:5mm; font-weight:900; color:{{ $navColor }}; line-height:1; }

.cf-sch { z-index:1; min-width:0; flex:1; }
.cf-sch .nm { color:#fff; font-size:2.9mm; font-weight:800; line-height:1.1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; letter-spacing:.05mm; }
.cf-sch .sb { color:{{ $primaryColor }}; font-size:1.7mm; font-weight:600; margin-top:.4mm; letter-spacing:.4mm; text-transform:uppercase; }

/* Body */
.cf-body { flex:1; display:flex; padding:2mm 3mm; gap:3mm; position:relative; min-height:0; }

/* Watermark letter */
.cf-body::before {
    content:'{{ strtoupper(substr($schoolName,0,1)) }}';
    position:absolute; top:50%; left:55%;
    transform:translate(-50%,-50%) rotate(-25deg);
    font-size:28mm; font-weight:900; color:#f1f5f9;
    pointer-events:none; z-index:0; line-height:1; opacity:.5;
}

/* Photo / Initials box */
.cf-photo {
    width:16mm; height:19mm; border-radius:1.8mm;
    overflow:hidden; flex-shrink:0; z-index:1;
    border:.5mm solid {{ $primaryColor }};
    box-shadow:0 .5mm 2mm rgba(0,0,0,.18);
    align-self:center;
}
.cf-photo img { width:100%; height:100%; object-fit:cover; }
.cf-photo .initials-box {
    width:100%; height:100%;
    display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg, {{ $navColor }}, color-mix(in srgb, {{ $navColor }} 70%, {{ $primaryColor }}));
}
.cf-photo .initials-box span {
    font-size:5.5mm; font-weight:900; color:#fff; letter-spacing:-.3mm;
}

/* Info */
.cf-info { flex:1; z-index:1; display:flex; flex-direction:column; justify-content:center; gap:1mm; min-width:0; }

.cf-name {
    font-size:3.2mm; font-weight:800; color:#0f172a;
    line-height:1.1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    padding-bottom:1mm; margin-bottom:.5mm;
    border-bottom:.4mm solid {{ $primaryColor }};
}

.cf-row { display:flex; gap:1.2mm; align-items:baseline; font-size:2mm; line-height:1.4; }
.cf-row .l { color:#94a3b8; font-weight:600; min-width:10mm; font-size:1.5mm; text-transform:uppercase; letter-spacing:.2mm; flex-shrink:0; }
.cf-row .v { color:#1e293b; font-weight:600; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cf-row .v.badge {
    display:inline-block; padding:0 1.5mm;
    background:color-mix(in srgb, {{ $primaryColor }} 15%, #fff);
    color:{{ $primaryColor }}; border:.3mm solid color-mix(in srgb, {{ $primaryColor }} 40%, #fff);
    border-radius:.8mm; font-size:1.7mm; font-weight:700;
}

/* Footer */
.cf-ft {
    height:5.5mm; background:{{ $primaryColor }};
    display:flex; align-items:center; justify-content:space-between;
    padding:0 3mm; flex-shrink:0;
}
.cf-ft .fl { color:#fff; font-size:1.6mm; font-weight:800; letter-spacing:.6mm; text-transform:uppercase; }
.cf-ft .fr { color:rgba(255,255,255,.8); font-size:1.6mm; font-weight:700; }

/* ══════════════════════════════════════════════════
   BACK  —  Dark premium
══════════════════════════════════════════════════ */
.cb {
    background:linear-gradient(155deg,
        {{ $navColor }} 0%,
        color-mix(in srgb, {{ $navColor }} 80%, #000) 55%,
        color-mix(in srgb, {{ $navColor }} 65%, #000) 100%
    );
    position:relative; overflow:hidden;
}
/* Dot texture */
.cb::after {
    content:''; position:absolute; inset:0;
    background-image:radial-gradient(circle, rgba(255,255,255,.045) 1px, transparent 1px);
    background-size:2.5mm 2.5mm; pointer-events:none;
}
/* Glow orb top-right */
.cb::before {
    content:''; position:absolute; border-radius:50%;
    width:30mm; height:30mm; top:-12mm; right:-12mm;
    background:{{ $primaryColor }}; opacity:.10; filter:blur(8mm); pointer-events:none;
}
/* Second orb bottom-left */
.cb .orb2 {
    position:absolute; border-radius:50%;
    width:20mm; height:20mm; bottom:-8mm; left:-6mm;
    background:{{ $primaryColor }}; opacity:.06; filter:blur(6mm); pointer-events:none;
    z-index:0;
}

/* Top gold bar */
.cb-top {
    height:5mm; background:{{ $primaryColor }};
    display:flex; align-items:center; justify-content:space-between;
    padding:0 3mm; flex-shrink:0; position:relative; z-index:2;
}
.cb-top .tl { color:#fff; font-size:1.7mm; font-weight:800; letter-spacing:.5mm; text-transform:uppercase; }
.cb-top .tr { color:rgba(255,255,255,.7); font-size:1.5mm; font-weight:600; }

/* Body */
.cb-body {
    flex:1; padding:1.6mm 3mm 1.2mm; display:flex; flex-direction:column;
    justify-content:space-between; position:relative; z-index:2; min-height:0;
}

/* School row */
.cb-sch {
    display:flex; align-items:center; gap:1.5mm;
    padding-bottom:1.2mm; margin-bottom:1.2mm;
    border-bottom:.2mm solid rgba(255,255,255,.12);
}
.cb-sch-logo {
    width:7mm; height:7mm; border-radius:1.2mm;
    background:rgba(255,255,255,.12); border:.2mm solid rgba(255,255,255,.2);
    display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden;
}
.cb-sch-logo img { width:100%; height:100%; object-fit:contain; padding:.5mm; }
.cb-sch-logo .li { font-size:3.5mm; font-weight:900; color:{{ $primaryColor }}; }
.cb-sch-info { flex:1; min-width:0; }
.cb-sch-info .n { color:#fff; font-size:1.9mm; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cb-sch-info .s { color:{{ $primaryColor }}; font-size:1.4mm; font-weight:600; letter-spacing:.3mm; text-transform:uppercase; margin-top:.2mm; }

/* Info panel */
.cb-panel {
    background:rgba(255,255,255,.08);
    border:.2mm solid rgba(255,255,255,.12);
    border-radius:1mm; padding:1.1mm 1.8mm;
    margin-bottom:1.2mm;
}
.cb-pt { font-size:1.2mm; font-weight:700; text-transform:uppercase; letter-spacing:.4mm; color:{{ $primaryColor }}; margin-bottom:.7mm; }
.cb-row { display:flex; gap:.8mm; font-size:1.7mm; line-height:1.4; }
.cb-row .l { color:rgba(255,255,255,.45); font-size:1.3mm; font-weight:700; text-transform:uppercase; letter-spacing:.15mm; min-width:14mm; flex-shrink:0; }
.cb-row .v { color:rgba(255,255,255,.88); font-weight:500; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* Contact section */
.cb-contact { display:flex; flex-direction:column; gap:.6mm; margin-bottom:1mm; }
.cb-ci { display:flex; gap:1.2mm; align-items:flex-start; font-size:1.6mm; line-height:1.3; }
.cb-ci i { color:{{ $primaryColor }}; font-size:1.6mm; flex-shrink:0; width:2.5mm; text-align:center; margin-top:.1mm; }
.cb-ci span { color:rgba(255,255,255,.65); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; }

/* School URL — neat and compact */
.cb-url {
    display:flex; align-items:center; gap:1mm;
    padding:1mm 1.5mm;
    background:rgba(255,255,255,.07);
    border:.2mm solid rgba(255,255,255,.12);
    border-radius:.8mm; margin-bottom:1mm;
}
.cb-url i { color:{{ $primaryColor }}; font-size:1.6mm; flex-shrink:0; }
.cb-url span { color:rgba(255,255,255,.65); font-size:1.6mm; font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* Signatures */
.cb-sigs { display:flex; justify-content:space-between; gap:2mm; margin-top:.5mm; }
.cb-sig { flex:1; text-align:center; }
.cb-sig .ln { height:3.5mm; border-bottom:.3mm solid rgba(255,255,255,.3); margin-bottom:.5mm; }
.cb-sig .lb { font-size:1.1mm; color:rgba(255,255,255,.4); font-weight:600; text-transform:uppercase; letter-spacing:.2mm; }

/* Bottom gold bar */
.cb-ft {
    height:4.5mm; background:{{ $primaryColor }};
    display:flex; align-items:center; justify-content:center;
    gap:1.5mm; flex-shrink:0; position:relative; z-index:2;
}
.cb-ft-t { color:rgba(255,255,255,.9); font-size:1.4mm; font-weight:800; letter-spacing:.4mm; text-transform:uppercase; }
.cb-ft-d { color:rgba(255,255,255,.35); font-size:1.4mm; }

/* ══ Print ══ */
@media print {
    body { background:#fff; padding:0; margin:0; }
    .tb { display:none !important; }
    .a4 { margin:0; width:210mm; }
    @page { size:A4 portrait; margin:0; }
    .pg-brk { page-break-after:always; }
    * { -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; }
}
@media screen { .pg-brk { display:none; } }
</style>
</head>
<body>

<div class="tb">
    <h1>ID Cards <span>{{ $schoolName }} — {{ $people->count() }} card(s), {{ ceil($people->count()/4) }} page(s)</span></h1>
    <div class="btns">
        <button onclick="window.print()" class="btn btn-b"><i class="fas fa-download"></i> Save as PDF</button>
        <button onclick="window.print()" class="btn btn-g"><i class="fas fa-print"></i> Print</button>
        <button onclick="window.close()" class="btn btn-s"><i class="fas fa-times"></i> Close</button>
    </div>
</div>

<div class="a4">

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
    $isStudent  = $p['type'] === 'student';
    $logoUrl    = $siteLogoUrl ? Storage::disk('public')->url($siteLogoUrl) : null;
    $photoUrl   = $photoPath ? Storage::disk('public')->url($photoPath) : null;

    // Initials from name
    $nameParts = array_filter(explode(' ', trim($name)));
    $initials  = implode('', array_map(fn($w) => strtoupper(substr($w,0,1)), array_slice($nameParts,0,2)));
    if (empty($initials)) $initials = '?';

    $count++;
    $isNewPage = $count > 4 && ($count - 1) % 4 === 0;
@endphp

@if($isNewPage)
<div class="pg-brk"></div>
@endif

<div class="row">
    {{-- Corner tick marks --}}
    <div class="tr-mark"></div>
    <div class="bl-mark"></div>
    <div class="br-mark"></div>

    {{-- ══ FRONT ══ --}}
    <div class="card cf">

        <div class="cf-hd">
            <div class="cf-logo">
                @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo">
                @else
                <span class="init-logo">{{ strtoupper(substr($schoolName,0,1)) }}</span>
                @endif
            </div>
            <div class="cf-sch">
                <div class="nm">{{ $schoolName }}</div>
                @if($schoolSubtitle)
                <div class="sb">{{ $schoolSubtitle }}</div>
                @endif
            </div>
        </div>

        <div class="cf-body">
            <div class="cf-photo">
                @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $name }}">
                @else
                <div class="initials-box"><span>{{ $initials }}</span></div>
                @endif
            </div>
            <div class="cf-info">
                <div class="cf-name">{{ $name }}</div>
                <div class="cf-row">
                    <span class="l">{{ $isStudent ? 'ID No' : 'Staff ID' }}</span>
                    <span class="v">{{ $idNumber }}</span>
                </div>
                <div class="cf-row">
                    <span class="l">{{ $isStudent ? 'Class' : 'Role' }}</span>
                    <span class="v{{ $isStudent ? '' : ' badge' }}">{{ $isStudent ? ($dept ?? '—') : $role }}</span>
                </div>
                @if($dob)
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
                @if(!$isStudent && $phone)
                <div class="cf-row">
                    <span class="l">Phone</span>
                    <span class="v">{{ $phone }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="cf-ft">
            <span class="fl">{{ $isStudent ? 'Student ID Card' : 'Staff ID Card' }}</span>
            <span class="fr">{{ $academicYear }}</span>
        </div>
    </div>

    {{-- ══ BACK ══ --}}
    <div class="card cb">
        <div class="orb2"></div>

        <div class="cb-top">
            <span class="tl">{{ $isStudent ? 'Student Card' : 'Staff Card' }}</span>
            <span class="tr">{{ $academicYear }}</span>
        </div>

        <div class="cb-body">

            {{-- School info --}}
            <div class="cb-sch">
                <div class="cb-sch-logo">
                    @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="">
                    @else
                    <span class="li">{{ strtoupper(substr($schoolName,0,1)) }}</span>
                    @endif
                </div>
                <div class="cb-sch-info">
                    <div class="n">{{ $schoolName }}</div>
                    @if($schoolSubtitle)
                    <div class="s">{{ $schoolSubtitle }}</div>
                    @endif
                </div>
            </div>

            {{-- Emergency/Staff info panel --}}
            <div class="cb-panel">
                <div class="cb-pt">{{ $isStudent ? 'Emergency Contact' : 'Staff Information' }}</div>
                @if($isStudent)
                    @if($guardian)
                    <div class="cb-row"><span class="l">Guardian</span><span class="v">{{ $guardian }}</span></div>
                    @else
                    <div class="cb-row"><span class="l">Guardian</span><span class="v" style="opacity:.4;font-style:italic;">Not provided</span></div>
                    @endif
                    @if($guardianPh)
                    <div class="cb-row"><span class="l">Phone</span><span class="v">{{ $guardianPh }}</span></div>
                    @endif
                    @if($dept)
                    <div class="cb-row"><span class="l">Class</span><span class="v">{{ $dept }}</span></div>
                    @endif
                    @if($gender)
                    <div class="cb-row"><span class="l">Gender</span><span class="v">{{ ucfirst($gender) }}</span></div>
                    @endif
                @else
                    @if($phone)
                    <div class="cb-row"><span class="l">Phone</span><span class="v">{{ $phone }}</span></div>
                    @endif
                    @if($email)
                    <div class="cb-row"><span class="l">Email</span><span class="v">{{ $email }}</span></div>
                    @endif
                    @if($dept)
                    <div class="cb-row"><span class="l">Dept</span><span class="v">{{ $dept }}</span></div>
                    @endif
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

            {{-- School URL — neat text box, no QR --}}
            @php
                $displayUrl = rtrim(preg_replace('#^https?://#', '', $schoolWebsite), '/');
            @endphp
            <div class="cb-url">
                <i class="fas fa-globe"></i>
                <span>{{ $displayUrl }}</span>
            </div>

            {{-- Signatures --}}
            <div class="cb-sigs">
                <div class="cb-sig">
                    <div class="ln"></div>
                    <div class="lb">{{ $isStudent ? 'Student' : 'Staff' }}</div>
                </div>
                <div class="cb-sig">
                    <div class="ln"></div>
                    <div class="lb">Headmaster</div>
                </div>
            </div>

        </div>

        <div class="cb-ft">
            <span class="cb-ft-t">If found, return to school</span>
            <span class="cb-ft-d">·</span>
            <span class="cb-ft-t">{{ $schoolName }}</span>
        </div>
    </div>

</div>{{-- end row --}}

@endforeach

</div>{{-- end a4 --}}

</body>
</html>
