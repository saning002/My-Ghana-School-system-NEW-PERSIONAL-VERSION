<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ID Cards — {{ $schoolName }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body { font-family:'Inter',Arial,sans-serif; background:#94a3b8; padding:0; }

/* ══ Toolbar ══════════════════════════════════════════════════════ */
.tb {
    position:fixed; top:0; left:0; right:0; z-index:999;
    background:#0f172a; color:#fff; padding:10px 20px;
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

/* ══ A4 ══════════════════════════════════════════════════════════ */
.a4 {
    width:210mm; min-height:297mm; margin:52px auto 20px;
    background:#fff;
}

/* ══ Row = one front + one back ══════════════════════════════════ */
.row {
    display:flex; width:210mm; height:62mm;
    position:relative;
    border-bottom:0.4mm dashed #94a3b8;
}
.row:last-child { border-bottom:none; }
/* Centre cut line */
.row .cv {
    position:absolute; top:0; bottom:0; left:50%;
    border-left:0.4mm dashed #94a3b8; pointer-events:none;
}
/* Corner ticks */
.row::before { content:''; position:absolute; top:0; left:0; width:4mm; height:4mm; border-left:.4mm solid #64748b; border-top:.4mm solid #64748b; }
.c-tr { position:absolute; top:0; right:0; width:4mm; height:4mm; border-right:.4mm solid #64748b; border-top:.4mm solid #64748b; pointer-events:none; }
.c-bl { position:absolute; bottom:0; left:0; width:4mm; height:4mm; border-left:.4mm solid #64748b; border-bottom:.4mm solid #64748b; pointer-events:none; }
.c-br { position:absolute; bottom:0; right:0; width:4mm; height:4mm; border-right:.4mm solid #64748b; border-bottom:.4mm solid #64748b; pointer-events:none; }

.card { width:50%; height:62mm; overflow:hidden; display:flex; flex-direction:column; position:relative; }

/* ══════════════════════════════════════════════════════════════════
   FRONT  — matches the reference image exactly
══════════════════════════════════════════════════════════════════ */
.cf {
    background:#f5f5e8;  /* cream/off-white like the reference */
    position:relative;
}

/* Dark green header band */
.cf-top {
    height:12mm;
    background:{{ $navColor }};
    position:relative;
    display:flex; align-items:center;
    padding:0 2.5mm;
    overflow:hidden;
    flex-shrink:0;
}
/* Wavy right side shape — the decorative wave from the reference */
.cf-top::after {
    content:'';
    position:absolute; right:0; top:0; bottom:0;
    width:30mm;
    background:{{ $primaryColor }};
    clip-path:ellipse(50% 100% at 100% 50%);
    opacity:.35;
}
/* Second wave layer */
.cf-top::before {
    content:'';
    position:absolute; right:5mm; top:0; bottom:0;
    width:22mm;
    background:rgba(255,255,255,.08);
    clip-path:ellipse(50% 100% at 100% 50%);
}

.cf-sch-name {
    color:#fff; font-size:3.4mm; font-weight:900;
    letter-spacing:.2mm; text-transform:uppercase;
    line-height:1.1; z-index:1; flex:1;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}
.cf-sch-sub {
    color:rgba(255,255,255,.65); font-size:1.6mm; font-weight:500;
    letter-spacing:1.5mm; text-transform:uppercase; margin-top:.5mm; z-index:1;
}

/* Logo — top right of header */
.cf-logo {
    width:11mm; height:11mm; border-radius:50%;
    background:#fff; border:.5mm solid {{ $primaryColor }};
    display:flex; align-items:center; justify-content:center;
    overflow:hidden; flex-shrink:0; z-index:2;
    box-shadow:0 .5mm 2mm rgba(0,0,0,.2);
}
.cf-logo img { width:100%; height:100%; object-fit:contain; padding:.8mm; }
.cf-logo .li { font-size:4.5mm; font-weight:900; color:{{ $navColor }}; }

/* Body */
.cf-body {
    flex:1; display:flex; padding:2mm 2.5mm; gap:2.5mm;
    position:relative;
}

/* Faint watermark crest behind everything */
.cf-body::before {
    content:'{{ strtoupper(substr($schoolName,0,1)) }}';
    position:absolute; top:50%; right:12mm;
    transform:translateY(-50%);
    font-size:22mm; font-weight:900;
    color:rgba(0,0,0,.04);
    pointer-events:none; z-index:0; line-height:1;
}

/* Left column: photo + QR */
.cf-left {
    display:flex; flex-direction:column; gap:1.5mm;
    align-items:center; flex-shrink:0; z-index:1;
}

.cf-photo {
    width:16mm; height:19mm;
    border:.4mm solid {{ $navColor }};
    border-radius:.8mm; overflow:hidden;
    background:#e8e8d8;
}
.cf-photo img { width:100%; height:100%; object-fit:cover; }
.cf-photo .ini {
    width:100%; height:100%;
    display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg, {{ $navColor }}, color-mix(in srgb, {{ $navColor }} 70%, {{ $primaryColor }}));
}
.cf-photo .ini span { font-size:5mm; font-weight:900; color:#fff; }

/* QR code box below photo */
.cf-qr-box {
    width:11mm; height:11mm; background:#fff;
    border:.3mm solid #ccc; border-radius:.5mm;
    display:flex; align-items:center; justify-content:center;
    overflow:hidden;
}
.cf-qr-box canvas { width:100% !important; height:100% !important; }

/* Right column: name + fields */
.cf-right { flex:1; min-width:0; z-index:1; display:flex; flex-direction:column; justify-content:flex-start; padding-top:.5mm; }

.cf-name {
    font-size:3.6mm; font-weight:900; color:{{ $primaryColor }};
    text-transform:uppercase; letter-spacing:.1mm;
    line-height:1.1; margin-bottom:1.5mm;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}

.cf-field {
    display:flex; align-items:baseline; gap:0;
    font-size:1.75mm; line-height:1.6;
    border-bottom:.1mm solid rgba(0,0,0,.07);
    padding-bottom:.2mm; margin-bottom:.2mm;
}
.cf-field:last-child { border-bottom:none; }
.cf-field .fl {
    font-size:1.5mm; font-weight:800; color:#1a1a1a;
    text-transform:uppercase; letter-spacing:.2mm;
    min-width:17mm; flex-shrink:0;
}
.cf-field .fv {
    font-size:1.75mm; font-weight:600; color:#1a1a1a;
    flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}
.cf-field .fv::before { content:': '; font-weight:400; color:#555; }

/* "STUDENT IDENTIFICATION" button bar */
.cf-id-bar {
    height:5.5mm; background:{{ $navColor }};
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; margin:1mm 2.5mm;
    border-radius:.8mm;
}
.cf-id-bar span {
    color:#fff; font-size:2mm; font-weight:900;
    letter-spacing:1.5mm; text-transform:uppercase;
}

/* Bottom info strip */
.cf-strip {
    height:5.5mm; background:{{ $navColor }};
    display:flex; align-items:center; justify-content:space-around;
    padding:0 2mm; flex-shrink:0;
}
.cf-strip .si {
    display:flex; align-items:center; gap:.8mm;
    color:#fff; font-size:1.6mm; font-weight:600;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    max-width:33%;
}
.cf-strip .si i { color:{{ $primaryColor }}; font-size:1.8mm; flex-shrink:0; }

/* ══════════════════════════════════════════════════════════════════
   BACK  — cream/white with rules and regulations
══════════════════════════════════════════════════════════════════ */
.cb {
    background:#fafaf2;
    display:flex; flex-direction:column;
    border:.3mm solid #d0d0b8;
    position:relative;
}

/* Faint watermark crest in the centre of back */
.cb::before {
    content:'{{ strtoupper(substr($schoolName,0,1)) }}';
    position:absolute; top:50%; left:50%;
    transform:translate(-50%,-50%);
    font-size:30mm; font-weight:900;
    color:rgba(0,0,0,.03);
    pointer-events:none; z-index:0; line-height:1;
}

/* Top statement */
.cb-top {
    text-align:center; padding:1.8mm 3mm 1mm; z-index:1;
    border-bottom:.2mm solid #d0d0b8;
}
.cb-top .bearer {
    font-size:1.6mm; font-weight:400; color:#333; line-height:1.4;
}
.cb-top .bearer strong {
    font-weight:800; text-transform:uppercase; color:#1a1a1a;
    display:block; font-size:1.9mm; margin-top:.3mm;
}

/* Rules section */
.cb-rules { padding:1.5mm 4mm; z-index:1; flex:1; }
.cb-rules .rtitle {
    text-align:center; font-size:1.9mm; font-weight:800;
    letter-spacing:3mm; text-transform:uppercase; color:#1a1a1a;
    margin-bottom:1.2mm;
}
.cb-rule {
    font-size:1.6mm; color:#333; line-height:1.5;
    margin-bottom:.4mm; text-align:center;
}

/* Signature */
.cb-sig { padding:.5mm 4mm 0; z-index:1; }
.cb-sig .sig-row {
    display:flex; align-items:flex-end; gap:1mm;
    font-size:1.6mm; color:#333; margin-bottom:.5mm;
}
.cb-sig .sig-line {
    flex:1; border-bottom:.4mm solid #333; min-width:25mm; height:4mm;
}

/* Return notice */
.cb-return {
    text-align:center; padding:.8mm 3mm;
    border-top:.2mm solid #d0d0b8; z-index:1;
}
.cb-return .rt {
    font-size:1.7mm; font-weight:800; text-transform:uppercase;
    color:#1a1a1a; letter-spacing:.3mm;
}
.cb-return .rc {
    font-size:1.6mm; font-weight:600; color:#333; margin-top:.2mm;
}

/* Dark bottom bar */
.cb-bar {
    height:4.5mm; background:{{ $navColor }};
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; z-index:1;
}
.cb-bar span { color:#fff; font-size:1.5mm; font-weight:800; letter-spacing:.4mm; text-transform:uppercase; }

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
    $photoPath  = $p['photo'];
    $guardian   = $p['guardian'];
    $guardianPh = $p['guardianPh'];
    $isStudent  = $p['type'] === 'student';

    $logoUrl  = $siteLogoUrl ? Storage::disk('public')->url($siteLogoUrl) : null;
    $photoUrl = $photoPath ? Storage::disk('public')->url($photoPath) : null;

    // Initials (up to 2 letters)
    $parts    = array_filter(explode(' ', trim($name)));
    $initials = implode('', array_map(fn($w) => strtoupper(substr($w,0,1)), array_slice($parts,0,2)));
    if (!$initials) $initials = '?';

    // Issuance / expiry
    $issued  = now()->format('d/m/Y');
    $expires = now()->addYear()->format('d/m/Y');

    $qrId = 'qr_' . $loop->index;
    $count++;
    $isNewPage = $count > 4 && ($count - 1) % 4 === 0;
@endphp

@if($isNewPage)<div class="pg-brk"></div>@endif

<div class="row">
    <div class="cv"></div>
    <div class="c-tr"></div>
    <div class="c-bl"></div>
    <div class="c-br"></div>

    {{-- ════ FRONT ════ --}}
    <div class="card cf">

        {{-- Dark header --}}
        <div class="cf-top">
            <div style="flex:1;min-width:0;z-index:1;">
                <div class="cf-sch-name">{{ $schoolName }}</div>
                @if($schoolSubtitle)
                <div class="cf-sch-sub">{{ $schoolSubtitle }}</div>
                @endif
            </div>
            <div class="cf-logo">
                @if($logoUrl)<img src="{{ $logoUrl }}" alt="Logo">
                @else<span class="li">{{ strtoupper(substr($schoolName,0,1)) }}</span>
                @endif
            </div>
        </div>

        {{-- Body --}}
        <div class="cf-body">

            {{-- Left: photo + QR --}}
            <div class="cf-left">
                <div class="cf-photo">
                    @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $name }}">
                    @else
                    <div class="ini"><span>{{ $initials }}</span></div>
                    @endif
                </div>
                <div class="cf-qr-box">
                    <canvas id="{{ $qrId }}"></canvas>
                </div>
            </div>

            {{-- Right: name + fields --}}
            <div class="cf-right">
                <div class="cf-name">{{ $name }}</div>

                @if($gender)
                <div class="cf-field"><span class="fl">Gender</span><span class="fv">{{ ucfirst($gender) }}</span></div>
                @endif
                <div class="cf-field"><span class="fl">{{ $isStudent ? 'Student ID' : 'Staff ID' }}</span><span class="fv">{{ $idNumber }}</span></div>
                @if($dept)
                <div class="cf-field"><span class="fl">{{ $isStudent ? 'Department' : 'Department' }}</span><span class="fv">{{ $dept }}</span></div>
                @endif
                @if($isStudent && $dept)
                <div class="cf-field"><span class="fl">Class</span><span class="fv">{{ $dept }}</span></div>
                @endif
                <div class="cf-field"><span class="fl">Date Issued</span><span class="fv">{{ $issued }}</span></div>
                <div class="cf-field"><span class="fl">Date Expiry</span><span class="fv">{{ $expires }}</span></div>

                {{-- Headmaster signature line --}}
                <div style="display:flex;align-items:flex-end;justify-content:flex-end;margin-top:1mm;">
                    <div style="text-align:center;">
                        <div style="width:16mm;border-bottom:.3mm solid #333;height:3.5mm;"></div>
                        <div style="font-size:1.3mm;font-weight:700;color:#333;text-align:center;margin-top:.3mm;text-transform:uppercase;letter-spacing:.2mm;">Headmaster</div>
                    </div>
                </div>

                {{-- ID bar --}}
                <div class="cf-id-bar">
                    <span>{{ $isStudent ? 'Student Identification' : 'Staff Identification' }}</span>
                </div>
            </div>
        </div>

        {{-- Bottom info strip --}}
        <div class="cf-strip">
            @if($schoolAddress)
            <div class="si"><i class="fas fa-map-marker-alt"></i>{{ Str::limit($schoolAddress, 18) }}</div>
            @endif
            @if($schoolPhone)
            <div class="si"><i class="fas fa-phone"></i>{{ $schoolPhone }}</div>
            @endif
            @if($schoolWebsite)
            <div class="si"><i class="fas fa-globe"></i>{{ rtrim(preg_replace('#^https?://#','',$schoolWebsite),'/') }}</div>
            @endif
        </div>

    </div>

    {{-- ════ BACK ════ --}}
    <div class="card cb">

        {{-- Bearer statement --}}
        <div class="cb-top">
            <div class="bearer">
                The Bearer of this card is a registered {{ $isStudent ? 'student' : 'staff member' }} of
                <strong>{{ strtoupper($schoolName) }}</strong>
            </div>
        </div>

        {{-- Rules --}}
        <div class="cb-rules">
            <div class="rtitle">R U L E S &nbsp; A N D &nbsp; R E G U L A T I O N S</div>
            <div class="cb-rule">1. Always carry this card along to school premises.</div>
            <div class="cb-rule">2. Carry this card to exams hall during examinations.</div>
            <div class="cb-rule">3. Any altercation, duplication and misuse of this card is prohibited.</div>
            @if($isStudent && $guardian)
            <div class="cb-rule" style="margin-top:.5mm;font-weight:600;">Guardian: {{ $guardian }} &nbsp;|&nbsp; {{ $guardianPh }}</div>
            @endif
        </div>

        {{-- Signature --}}
        <div class="cb-sig">
            <div class="sig-row">
                <span style="font-weight:600;">Signature:</span>
                <div class="sig-line"></div>
            </div>
        </div>

        {{-- Return notice --}}
        <div class="cb-return">
            <div class="rt">Please return to school authority if found</div>
            <div class="rc">Call: {{ $schoolPhone ?: 'XXX-XXXX-XXX' }}</div>
        </div>

        {{-- Dark bottom bar --}}
        <div class="cb-bar">
            <span>{{ $schoolName }}</span>
        </div>

    </div>

</div>{{-- end row --}}

@endforeach

</div>{{-- end a4 --}}

<script>
document.addEventListener('DOMContentLoaded', function() {
    @foreach($people as $p)
    @php $qrId = 'qr_' . $loop->index; @endphp
    (function() {
        var c = document.getElementById('{{ $qrId }}');
        if (!c) return;
        QRCode.toCanvas(c, '{{ addslashes($p['qr_data']) }}', {
            width: 44, margin: 1,
            color: { dark:'#0f172a', light:'#ffffff' },
            errorCorrectionLevel:'M'
        });
    })();
    @endforeach
});
</script>

</body>
</html>
