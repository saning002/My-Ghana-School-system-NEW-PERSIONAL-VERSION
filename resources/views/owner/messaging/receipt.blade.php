<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->receipt_number }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; font-size:13px; color:#1e293b; background:#f8fafc; }
        .wrapper { max-width:620px; margin:30px auto; background:#fff; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.08); overflow:hidden; }
        .header { background:linear-gradient(135deg,#1a1a2e,#0f3460); padding:24px 28px; color:#fff; display:flex; justify-content:space-between; align-items:center; }
        .header h1 { font-size:18px; font-weight:800; }
        .header .rno { font-size:14px; font-weight:700; color:#fbbf24; }
        .paid-stamp { text-align:center; padding:10px; background:#dcfce7; }
        .paid-stamp span { color:#16a34a; font-weight:800; font-size:13px; letter-spacing:2px; }
        .body { padding:24px 28px; }
        .section-title { font-size:10px; text-transform:uppercase; letter-spacing:1px; color:#94a3b8; font-weight:700; margin:16px 0 8px; }
        .row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:13px; }
        .row:last-child { border-bottom:none; }
        .row .label { color:#64748b; }
        .row .value { font-weight:600; }
        .credits-box { background:#f8fafc; border-radius:10px; padding:16px; margin:16px 0; display:flex; gap:24px; justify-content:center; }
        .credit-item { text-align:center; }
        .credit-item .num { font-size:2rem; font-weight:800; }
        .credit-item .type { font-size:11px; color:#94a3b8; text-transform:uppercase; }
        .amount-box { background:linear-gradient(135deg,#1a1a2e,#0f3460); color:#fff; border-radius:10px; padding:14px 20px; margin-top:16px; display:flex; justify-content:space-between; align-items:center; }
        .footer { background:#f8fafc; border-top:1px solid #e2e8f0; padding:14px 28px; text-align:center; font-size:11px; color:#94a3b8; }
        .print-actions { text-align:center; padding:16px; }
        .btn-print { background:#0f3460; color:#fff; border:none; padding:9px 24px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; margin:0 6px; }
        @media print { body{background:#fff;} .wrapper{box-shadow:none;margin:0;border-radius:0;} .print-actions{display:none;} }
    </style>
</head>
<body>
<div class="print-actions">
    <button class="btn-print" onclick="window.print()">🖨 Print Receipt</button>
    <a href="{{ url()->previous() }}" style="background:#f1f5f9;color:#475569;border:none;padding:9px 24px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">← Back</a>
</div>
<div class="wrapper">
    <div class="header">
        <div>
            <h1>Messaging Credits Receipt</h1>
            <div style="font-size:12px;opacity:0.6;margin-top:2px;">School Management Platform</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;opacity:0.6;">Receipt No.</div>
            <div class="rno">{{ $payment->receipt_number }}</div>
            <div style="font-size:11px;opacity:0.6;margin-top:2px;">{{ $payment->created_at->format('d M Y') }}</div>
        </div>
    </div>
    <div class="paid-stamp"><span>✓ PAYMENT CONFIRMED</span></div>
    <div class="body">

        <div class="section-title">School</div>
        <div class="row"><span class="label">School Name</span><span class="value">{{ $payment->tenant->name }}</span></div>
        <div class="row"><span class="label">Admin Email</span><span class="value">{{ $payment->tenant->admin_email }}</span></div>

        <div class="section-title">Package</div>
        <div class="row"><span class="label">Package</span><span class="value">{{ $payment->package?->name ?? 'Manual Top-up' }}</span></div>
        <div class="row"><span class="label">Payment Method</span><span class="value">{{ ucfirst(str_replace('_',' ',$payment->payment_method)) }}</span></div>
        @if($payment->paystack_reference)
        <div class="row"><span class="label">Reference</span><span class="value"><code style="font-size:11px;">{{ $payment->paystack_reference }}</code></span></div>
        @endif

        <div class="section-title">Credits Purchased</div>
        <div class="credits-box">
            <div class="credit-item">
                <div class="num" style="color:#3b82f6;">{{ number_format($payment->sms_credits_added) }}</div>
                <div class="type"><i>📱</i> SMS Credits</div>
            </div>
            <div class="credit-item">
                <div class="num" style="color:#25d366;">{{ number_format($payment->whatsapp_credits_added) }}</div>
                <div class="type"><i>💬</i> WhatsApp Credits</div>
            </div>
        </div>

        <div class="amount-box">
            <div>
                <div style="font-size:11px;opacity:0.7;">Total Paid</div>
                <div style="font-size:22px;font-weight:800;color:#fbbf24;">GHS {{ number_format($payment->amount, 2) }}</div>
            </div>
            <div style="font-size:22px;">✅</div>
        </div>
    </div>
    <div class="footer">School Management Platform · Messaging Credits Receipt · {{ $payment->created_at->format('d M Y H:i') }}</div>
</div>
<div class="print-actions">
    <button class="btn-print" onclick="window.print()">🖨 Print Receipt</button>
</div>
</body>
</html>
