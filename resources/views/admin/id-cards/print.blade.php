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

/* ── Toolbar ──────────────────────────────────────────────────────── */
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

/* ── A4 Sheet ──────────────────────────────────────────────────────── */
.a4 { width:210mm; min-height:297mm; margin:52px auto 20px; background:#fff; }

/* ── Row = front (left) + back (right) ────────────────────────────── */
.row {
    display:flex; width:210mm; height:62mm;
    position:relative;
    border-bottom:.4mm dashed #94a3b8;
}
.row:last-child { border-bottom:none; }
/* Centre cut line */
.row .cv { position:absolute; top:0; bottom:0; left:50%; border-left:.4mm dashed #94a3b8; pointer-events:none; }
/* Corner ticks */
.row::before { content:''; position:absolute; top:0; left:0; width:4mm; height:4mm; border-left:.4mm solid #64748b; border-top:.4mm solid #64748b; }
.c-tr { position:absolute; top:0; right:0; width:4mm; height:4mm; border-right:.4mm solid #64748b; border-top:.4mm solid #64748b; pointer-events:none; }
.c-bl { position:absolute; bottom:0; left:0; width:4mm; height:4mm; border-left:.4mm solid #64748b; border-bottom:.4mm solid #64748b; pointer-events:none; }
.c-br { position:absolute; bottom:0; right:0; width:4mm; height:4mm; border-right:.4mm solid #64748b; border-bottom:.4mm solid #64748b; pointer-events:none; }

.card { width:50%; height:62mm; overflow:hidden; display:flex; flex-direction:column; position:relative; }

/* ══════════════════════════════════════════════════════════════════
   FRONT CARD — exact reference layout + premium feel
══════════════════════════════════════════════════════════════════ */
.cf {
    /* Premium cream-gold gradient body, not plain white */
    background: linear-gradient(160deg,
        #fefdf5 0%,
        #f7f4e8 40%,
        #f0ece0 100%
    );
    position:relative;
}

/* Faint diagonal school initial watermark */
.cf::before {
    content:'{{ strtoupper(substr($schoolName,0,1)) }}';
    position:absolute; bottom:-4mm; right:2mm;
    font-size:38mm; font-weight:900; line-height:1;
    color:rgba(0,0,0,.03); pointer-events:none; z-index:0;
    font-family:'Inter',Arial,sans-serif;
}

/* ── Header band ──────────────────────────────────────────────── */
.cf-hd {
    height:13mm; flex-shrink:0;
    background:{{ $navColor }};
    display:flex; align-items:center;
    padding:0 2.5mm; gap:2mm;
    position:relative; overflow:hidden; z-index:1;
}
/* Premium wave shape on right side — matches reference */
.cf-hd::after {
    content:'';
    position:absolute; right:0; top:0; bottom:0; width:28mm;
    background:color-mix(in srgb, {{ $navColor }} 50%, {{ $primaryColor }});
    clip-path:ellipse(55% 100% at 100% 50%);
    opacity:.5; pointer-events:none;
}
.cf-hd::before {
    content:'';
    position:absolute; right:3mm; top:0; bottom:0; width:18mm;
    background:rgba(255,255,255,.07);
    clip-path:ellipse(55% 100% at 100% 50%);
    pointer-events:none;
}
/* Dot texture in header */
.cf-hd-dots {
    position:absolute; inset:0;
    background-image:radial-gradient(circle, rgba(255,255,255,.06) 1px, transparent 1px);
    background-size:1.8mm 1.8mm; pointer-events:none; z-index:0;
}

.cf-hd-text { flex:1; min-width:0; z-index:2; }
.cf-hd-text .nm {
    color:#fff; font-size:3.4mm; font-weight:900;
    text-transform:uppercase; letter-spacing:.15mm;
    line-height:1.1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}
.cf-hd-text .sb {
    color:rgba(255,255,255,.65); font-size:1.5mm; font-weight:500;
    letter-spacing:1.6mm; text-transform:uppercase; margin-top:.4mm;
}

/* Logo top-right — matches reference */
.cf-hd-logo {
    width:11mm; height:11mm; border-radius:50%; z-index:2;
    background:#fff;
    border:.4mm solid {{ $primaryColor }};
    box-shadow:0 .5mm 2mm rgba(0,0,0,.2);
    display:flex; align-items:center; justify-content:center;
    overflow:hidden; flex-shrink:0;
}
.cf-hd-logo img { width:100%; height:100%; object-fit:contain; padding:.8mm; }
.cf-hd-logo .li { font-size:4.5mm; font-weight:900; color:{{ $navColor }}; }

/* ── Body ──────────────────────────────────────────────────────── */
.cf-body {
    flex:1; display:flex; padding:1.5mm 2mm; gap:2mm;
    position:relative; z-index:1; min-height:0;
}

/* Left column: photo only — QR moved to back */
.cf-left { display:flex; flex-direction:column; gap:0; align-items:center; justify-content:center; flex-shrink:0; padding-top:1.5mm; }

.cf-photo {
    width:16.5mm; height:19.5mm;
    border:1mm solid {{ $navColor }};
    border-radius:.6mm; overflow:hidden; background:#e8e4d8;
    box-shadow:1mm 1mm 3mm rgba(0,0,0,.2);
}
.cf-photo img { width:100%; height:100%; object-fit:cover; }
.cf-photo .ini {
    width:100%; height:100%;
    background:linear-gradient(140deg, {{ $navColor }} 0%, color-mix(in srgb, {{ $navColor }} 60%, {{ $primaryColor }}) 100%);
    display:flex; align-items:center; justify-content:center;
}
.cf-photo .ini span { font-size:5.5mm; font-weight:900; color:#fff; letter-spacing:-.3mm; }

.cf-qr {
    width:12mm; height:12mm;
    background:#fff; border:.3mm solid {{ $navColor }};
    border-radius:.4mm; overflow:hidden;
    display:flex; align-items:center; justify-content:center;
    box-shadow:.5mm .5mm 1.5mm rgba(0,0,0,.12);
}
.cf-qr canvas { width:100% !important; height:100% !important; }

/* Right column: pushed down for better spacing */
.cf-right {
    flex:1; min-width:0; z-index:1;
    display:flex; flex-direction:column;
    padding-top:2.5mm;
}

.cf-name {
    font-size:3.6mm; font-weight:900; color:{{ $primaryColor }};
    text-transform:uppercase; letter-spacing:.1mm; line-height:1.1;
    margin-bottom:1.2mm; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}

/* Field rows — reference style: LABEL (bold caps) : VALUE (lighter) */
.cf-field {
    display:flex; align-items:baseline; gap:0;
    font-size:1.75mm; line-height:1.65;
    border-bottom:.1mm solid rgba(0,0,0,.08);
}
.cf-field:last-of-type { border-bottom:none; }
.cf-field .fl {
    font-size:1.55mm; font-weight:800; color:#1a1a1a;
    text-transform:uppercase; letter-spacing:.18mm;
    min-width:19mm; flex-shrink:0;
}
.cf-field .fcolon { font-size:1.55mm; font-weight:400; color:#555; margin-right:1mm; }
.cf-field .fv { font-size:1.75mm; font-weight:600; color:#222; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* Signature + ID badge row — same line like reference */
.cf-sig-badge {
    display:flex; align-items:flex-end; justify-content:space-between;
    margin-top:auto; padding-top:.8mm;
}
.cf-sig-block { text-align:center; }
.cf-sig-block .sig-line { width:16mm; border-bottom:.35mm solid #333; height:3.5mm; }
.cf-sig-block .sig-lbl { font-size:1.2mm; font-weight:800; color:#333; text-transform:uppercase; letter-spacing:.2mm; margin-top:.2mm; }

.cf-id-badge {
    background:{{ $navColor }};
    color:#fff; font-size:1.7mm; font-weight:900;
    letter-spacing:1.2mm; text-transform:uppercase;
    padding:.5mm 2mm; border-radius:.4mm;
}

/* ── Bottom strip ──────────────────────────────────────────────── */
.cf-strip {
    height:5.5mm; background:{{ $navColor }};
    display:flex; align-items:center; justify-content:space-around;
    padding:0 2mm; flex-shrink:0; z-index:1;
}
.cf-strip .si {
    display:flex; align-items:center; gap:.8mm;
    color:#fff; font-size:1.6mm; font-weight:600;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    max-width:33%;
}
.cf-strip .si i { color:{{ $primaryColor }}; font-size:1.7mm; flex-shrink:0; }

/* ══════════════════════════════════════════════════════════════════
   BACK CARD — premium dark + reference layout
══════════════════════════════════════════════════════════════════ */
.cb {
    /* Cream/off-white — works on black-and-white printers, signature clearly visible */
    background: linear-gradient(160deg, #fefdf5 0%, #f7f4e8 50%, #f0ece0 100%);
    display:flex; flex-direction:column; position:relative; overflow:hidden;
}

/* Very faint dot texture — still visible on B&W */
.cb::after {
    content:''; position:absolute; inset:0;
    background-image:radial-gradient(circle, rgba(0,0,0,.04) 1px, transparent 1px);
    background-size:2.5mm 2.5mm; pointer-events:none; z-index:0;
}

/* Large faint watermark crest */
.cb::before {
    content:'{{ strtoupper(substr($schoolName,0,1)) }}';
    position:absolute; top:50%; left:50%;
    transform:translate(-50%,-50%);
    font-size:36mm; font-weight:900; line-height:1;
    color:rgba(0,0,0,.04); pointer-events:none; z-index:0;
    font-family:'Inter',Arial,sans-serif;
}

/* NO glow orbs on cream back — not needed */
.cb-orb, .cb-orb2 { display:none; }

/* Gold top bar */
.cb-top-bar {
    height:4.5mm; background:{{ $navColor }};
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; position:relative; z-index:2;
}
.cb-top-bar span { color:#fff; font-size:1.5mm; font-weight:800; letter-spacing:.6mm; text-transform:uppercase; }

/* Body */
.cb-body { flex:1; padding:1.5mm 3mm 1mm; display:flex; flex-direction:column; justify-content:space-between; position:relative; z-index:2; min-height:0; }

/* Bearer statement */
.cb-bearer {
    text-align:center; padding-bottom:1.2mm;
    border-bottom:.2mm solid rgba(0,0,0,.12);
    margin-bottom:1mm;
}
.cb-bearer .bt { font-size:1.6mm; color:#444; font-weight:400; line-height:1.5; }
.cb-bearer .bn { font-size:2mm; color:#111; font-weight:800; text-transform:uppercase; margin-top:.3mm; letter-spacing:.15mm; }

/* Rules */
.cb-rules-title {
    text-align:center; font-size:1.9mm; font-weight:800;
    letter-spacing:2.5mm; text-transform:uppercase;
    color:{{ $navColor }}; margin-bottom:.8mm;
}
.cb-rule {
    font-size:1.55mm; color:#333; line-height:1.5;
    text-align:center; margin-bottom:.3mm;
}
.cb-rule strong { color:{{ $navColor }}; font-weight:700; }

/* Signature */
.cb-sig {
    display:flex; align-items:flex-end; gap:1mm; margin-top:.5mm;
}
.cb-sig .sl { font-size:1.6mm; color:#333; font-weight:600; white-space:nowrap; }
.cb-sig .sline { flex:1; border-bottom:.4mm solid #333; height:3.5mm; }

/* Return notice */
.cb-return {
    text-align:center; margin-top:.5mm;
    padding:.6mm 0;
    border-top:.2mm solid rgba(0,0,0,.1);
}
.cb-return .rt { font-size:1.6mm; font-weight:800; color:#111; text-transform:uppercase; letter-spacing:.3mm; }
.cb-return .rc { font-size:1.6mm; font-weight:600; color:{{ $navColor }}; margin-top:.2mm; }

/* Dark bottom bar */
.cb-bot-bar {
    height:4.5mm; background:{{ $navColor }};
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; position:relative; z-index:2;
}
.cb-bot-bar span { color:#fff; font-size:1.4mm; font-weight:800; letter-spacing:.4mm; text-transform:uppercase; }

/* ── Print ──────────────────────────────────────────────────────── */
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
    $photoUrl = $photoPath   ? Storage::disk('public')->url($photoPath)   : null;

    // Initials — up to 2 letters
    $parts    = array_filter(explode(' ', trim($name)));
    $initials = implode('', array_map(fn($w) => strtoupper(substr($w,0,1)), array_slice($parts,0,2)));
    if (!$initials) $initials = '?';

    // Dates
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

    {{-- ════════════ FRONT ════════════ --}}
    <div class="card cf">

        {{-- Header band --}}
        <div class="cf-hd">
            <div class="cf-hd-dots"></div>
            <div class="cf-hd-text">
                <div class="nm">{{ $schoolName }}</div>
                @if($schoolSubtitle)<div class="sb">{{ $schoolSubtitle }}</div>@endif
            </div>
            <div class="cf-hd-logo">
                @if($logoUrl)<img src="{{ $logoUrl }}" alt="Logo">
                @else<span class="li">{{ strtoupper(substr($schoolName,0,1)) }}</span>@endif
            </div>
        </div>

        {{-- Body --}}
        <div class="cf-body">

            {{-- LEFT: photo only (QR removed from front) --}}
            <div class="cf-left">
                <div class="cf-photo">
                    @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $name }}">
                    @else
                    <div class="ini"><span>{{ $initials }}</span></div>
                    @endif
                </div>
            </div>

            {{-- RIGHT: name, fields, sig+badge --}}
            <div class="cf-right">
                <div class="cf-name">{{ $name }}</div>

                @if($gender)
                <div class="cf-field"><span class="fl">Gender</span><span class="fcolon">:</span><span class="fv">{{ ucfirst($gender) }}</span></div>
                @endif
                <div class="cf-field"><span class="fl">{{ $isStudent ? 'Student ID' : 'Staff ID' }}</span><span class="fcolon">:</span><span class="fv">{{ $idNumber }}</span></div>
                @if($dept)
                <div class="cf-field"><span class="fl">{{ $isStudent ? 'Programme' : 'Role' }}</span><span class="fcolon">:</span><span class="fv">{{ $dept }}</span></div>
                @endif
                @if($dob)
                <div class="cf-field"><span class="fl">Date of Birth</span><span class="fcolon">:</span><span class="fv">{{ $dob }}</span></div>
                @endif
                <div class="cf-field"><span class="fl">Date Issued</span><span class="fcolon">:</span><span class="fv">{{ $issued }}</span></div>
                <div class="cf-field"><span class="fl">Date Expiry</span><span class="fcolon">:</span><span class="fv">{{ $expires }}</span></div>

                {{-- Signature + ID badge on same line --}}
                <div class="cf-sig-badge">
                    <div class="cf-sig-block">
                        @if(!empty($headmasterSignatureUrl))
                        <div style="height:6mm;display:flex;align-items:flex-end;margin-bottom:.3mm;">
                            <img src="{{ $headmasterSignatureUrl }}" alt="Sig"
                                 style="max-height:5.5mm;max-width:18mm;object-fit:contain;opacity:.9;">
                        </div>
                        @else
                        <div class="sig-line"></div>
                        @endif
                        <div class="sig-lbl">{{ $isStudent ? 'Headmaster' : 'Director' }}</div>
                    </div>
                    <div class="cf-id-badge">{{ $isStudent ? 'Student Identification' : 'Staff Identification' }}</div>
                </div>
            </div>

        </div>

        {{-- Bottom strip: address | phone | website --}}
        <div class="cf-strip">
            @if($schoolAddress)
            <div class="si"><i class="fas fa-map-marker-alt"></i>{{ Str::limit($schoolAddress,20) }}</div>
            @endif
            @if($schoolPhone)
            <div class="si"><i class="fas fa-phone"></i>{{ $schoolPhone }}</div>
            @endif
            @if($schoolWebsite)
            <div class="si"><i class="fas fa-globe"></i>{{ Str::limit(preg_replace('#^https?://#','',$schoolWebsite),22) }}</div>
            @endif
        </div>

    </div>

    {{-- ════════════ BACK ════════════ --}}
    <div class="card cb">
        <div class="cb-orb"></div>
        <div class="cb-orb2"></div>

        {{-- Gold top bar --}}
        <div class="cb-top-bar">
            <span>{{ $isStudent ? 'Student Card' : 'Staff Card' }} &nbsp;·&nbsp; {{ $academicYear }}</span>
        </div>

        <div class="cb-body">

            {{-- Bearer statement --}}
            <div class="cb-bearer">
                <div class="bt">The Bearer of this card is a registered {{ $isStudent ? 'student' : 'staff member' }} of</div>
                <div class="bn">{{ $schoolName }}</div>
            </div>

            {{-- Rules --}}
            <div class="cb-rules-title">R U L E S &nbsp;&nbsp; A N D &nbsp;&nbsp; R E G U L A T I O N S</div>
            <div class="cb-rule"><strong>1.</strong> Always carry this card along to school premises.</div>
            <div class="cb-rule"><strong>2.</strong> Carry this card to exams hall during examinations.</div>
            <div class="cb-rule"><strong>3.</strong> Any altercation, duplication and misuse of this card is prohibited.</div>
            @if($isStudent && $guardian)
            <div class="cb-rule" style="margin-top:.4mm;"><strong>Guardian:</strong> {{ $guardian }} &nbsp;|&nbsp; {{ $guardianPh }}</div>
            @endif

            {{-- Signature --}}
            <div class="cb-sig">
                <span class="sl">Signature:</span>
                @if(!empty($headmasterSignatureUrl))
                <div style="flex:1;height:5mm;display:flex;align-items:flex-end;padding-bottom:.3mm;">
                    <img src="{{ $headmasterSignatureUrl }}" alt="Signature"
                         style="max-height:4.5mm;max-width:28mm;object-fit:contain;opacity:.85;">
                </div>
                @else
                <div class="sline"></div>
                @endif
            </div>

            {{-- Return notice --}}
            <div class="cb-return">
                <div class="rt">Please Return to School Authority if Found</div>
                <div class="rc">Call: {{ $idCardPhone ?: ($schoolPhone ?: 'XXX-XXXX-XXX') }}</div>
            </div>

        </div>

        {{-- Gold bottom bar --}}
        <div class="cb-bot-bar">
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
            width:48, margin:1,
            color:{ dark:'#0f172a', light:'#ffffff' },
            errorCorrectionLevel:'M'
        });
    })();
    @endforeach
});
</script>
</body>
</html>
