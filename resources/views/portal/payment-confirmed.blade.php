@php
$themeMap = [
    'gold'   => ['accent'=>'#f59e0b','accent2'=>'#d97706','header'=>'#f59e0b','text'=>'#ffffff'],
    'blue'   => ['accent'=>'#3b82f6','accent2'=>'#2563eb','header'=>'#3b82f6','text'=>'#ffffff'],
    'green'  => ['accent'=>'#16a34a','accent2'=>'#15803d','header'=>'#16a34a','text'=>'#ffffff'],
    'purple' => ['accent'=>'#7c3aed','accent2'=>'#6d28d9','header'=>'#7c3aed','text'=>'#ffffff'],
    'red'    => ['accent'=>'#dc2626','accent2'=>'#b91c1c','header'=>'#dc2626','text'=>'#ffffff'],
    'teal'   => ['accent'=>'#0d9488','accent2'=>'#0f766e','header'=>'#0d9488','text'=>'#ffffff'],
];
$tc = $themeMap[$portalTheme ?? 'gold'] ?? $themeMap['gold'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmed — {{ $schoolName }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: "Inter", sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #f0fdf4 0%, #f7fee7 50%, #ecfdf5 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 24px 16px;
        }
        .card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.10);
            width: 100%; max-width: 480px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, {{ $tc['accent'] }}, {{ $tc['accent2'] }});
            padding: 32px 28px 28px;
            text-align: center;
        }
        .card-header .logo {
            width: 48px; height: 48px; border-radius: 12px;
            background: rgba(255,255,255,0.2); padding: 6px;
            object-fit: contain; margin: 0 auto 16px; display: block;
        }
        .card-header .school { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.85); margin-bottom: 20px; }
        .success-ring {
            width: 80px; height: 80px; border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            border: 3px solid rgba(255,255,255,0.4);
        }
        .success-ring i { font-size: 36px; color: #fff; }
        .card-header h1 { font-size: 22px; font-weight: 900; color: #fff; margin-bottom: 6px; }
        .card-header p  { font-size: 13px; color: rgba(255,255,255,0.80); font-weight: 500; }

        .card-body { padding: 28px; }

        .amount-block {
            text-align: center;
            padding: 20px;
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 16px;
            margin-bottom: 24px;
        }
        .amount-block .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; margin-bottom: 6px; }
        .amount-block .amount { font-size: 36px; font-weight: 900; color: #15803d; line-height: 1; }
        .amount-block .sub { font-size: 12px; color: #6b7280; margin-top: 6px; font-weight: 500; }

        .detail-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }
        .detail-row:last-of-type { border-bottom: none; }
        .detail-row .lbl { color: #6b7280; font-weight: 600; }
        .detail-row .val { font-weight: 700; color: #111827; text-align: right; max-width: 60%; word-break: break-all; }
        .detail-row .val.receipt {
            font-family: monospace; font-size: 12px;
            background: #f3f4f6; padding: 4px 10px;
            border-radius: 8px; color: #374151;
        }

        .info-banner {
            margin-top: 20px;
            padding: 14px 16px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 14px;
            font-size: 12px;
            color: #1e40af;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .info-banner i { font-size: 14px; flex-shrink: 0; margin-top: 1px; }

        .actions { display: flex; flex-direction: column; gap: 10px; margin-top: 24px; }
        .btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 14px 20px; border-radius: 14px;
            font-size: 13px; font-weight: 700;
            text-decoration: none; border: none; cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary { background: linear-gradient(135deg, {{ $tc['accent'] }}, {{ $tc['accent2'] }}); color: #fff; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
        .btn-secondary { background: #f9fafb; color: #374151; border: 1.5px solid #e5e7eb; }
        .btn-secondary:hover { background: #f3f4f6; }

        .print-only { display: none; }
        @media print {
            body { background: #fff !important; padding: 0; }
            .card { box-shadow: none; border: 1px solid #ddd; }
            .actions { display: none; }
            .print-only { display: block; }
        }
        @media (min-width: 480px) {
            .actions { flex-direction: row; }
            .btn { flex: 1; }
        }
    </style>
</head>
<body>

<div class="card">
    {{-- Header --}}
    <div class="card-header">
        @if($logoSrc)
        <img src="{{ $logoSrc }}" alt="{{ $schoolName }}" class="logo">
        @else
        <div style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i class="fas fa-school" style="font-size:22px;color:#fff;"></i>
        </div>
        @endif
        <div class="school">{{ $schoolName }}@if($schoolSubtitle) · {{ $schoolSubtitle }}@endif</div>
        <div class="success-ring">
            <i class="fas fa-check"></i>
        </div>
        <h1>Payment Confirmed!</h1>
        <p>Your fee payment has been received successfully.</p>
    </div>

    {{-- Body --}}
    <div class="card-body">
        {{-- Amount --}}
        <div class="amount-block">
            <div class="label">Amount Paid</div>
            <div class="amount">GH₵ {{ $amount }}</div>
            <div class="sub">via {{ ucfirst($channel ?? 'Paystack') }}</div>
        </div>

        {{-- Details --}}
        <div>
            <div class="detail-row">
                <span class="lbl">Student</span>
                <span class="val">{{ $student->user->full_name }}</span>
            </div>
            <div class="detail-row">
                <span class="lbl">Student ID</span>
                <span class="val">{{ $student->student_id }}</span>
            </div>
            <div class="detail-row">
                <span class="lbl">Date &amp; Time</span>
                <span class="val">{{ now()->format('d M Y, g:i A') }}</span>
            </div>
            <div class="detail-row">
                <span class="lbl">Receipt No.</span>
                <span class="val receipt">{{ $receipt }}</span>
            </div>
            <div class="detail-row">
                <span class="lbl">Reference</span>
                <span class="val receipt" style="font-size:11px;">{{ $reference }}</span>
            </div>
            <div class="detail-row">
                <span class="lbl">Status</span>
                <span class="val" style="color:#15803d;display:flex;align-items:center;gap:5px;">
                    <i class="fas fa-check-circle text-green-600" style="font-size:13px;color:#15803d;"></i> Successful
                </span>
            </div>
        </div>

        {{-- Info banner --}}
        <div class="info-banner">
            <i class="fas fa-info-circle"></i>
            <span>This payment has been recorded on your account. Your fee balance has been updated. Keep your receipt number <strong>{{ $receipt }}</strong> for your records.</span>
        </div>

        {{-- Actions --}}
        <div class="actions">
            <a href="{{ route('portal.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-home"></i> Back to Portal
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Receipt
            </button>
        </div>
    </div>
</div>

{{-- Confetti burst on load --}}
<script>
(function() {
    var colors = ['{{ $tc['accent'] }}','#ffffff','#bbf7d0','#6ee7b7'];
    var container = document.body;
    for (var i = 0; i < 48; i++) {
        var el = document.createElement('div');
        el.style.cssText = [
            'position:fixed',
            'pointer-events:none',
            'width:' + (Math.random()*8+4) + 'px',
            'height:' + (Math.random()*8+4) + 'px',
            'background:' + colors[Math.floor(Math.random()*colors.length)],
            'border-radius:' + (Math.random() > 0.5 ? '50%' : '2px'),
            'left:' + Math.random()*100 + 'vw',
            'top:-10px',
            'opacity:1',
            'transition:top ' + (Math.random()*1.5+1) + 's ease-out ' + Math.random()*0.5 + 's, opacity 0.5s ease ' + (Math.random()*1.5+1.5) + 's',
            'z-index:9999'
        ].join(';');
        container.appendChild(el);
        setTimeout(function(e) {
            e.style.top = (Math.random()*80+20) + 'vh';
            e.style.opacity = '0';
            setTimeout(function(e2) { e2.remove(); }, 3000);
        }, 50, el);
    }
})();
</script>
</body>
</html>
