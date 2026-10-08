<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:13px; color:#1e293b; background:#fff; padding:40px; }
    .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:32px; border-bottom:3px solid #e94560; padding-bottom:20px; }
    .brand h1 { font-size:22px; font-weight:800; color:#e94560; margin-bottom:2px; }
    .brand p  { font-size:11px; color:#64748b; }
    .receipt-title { text-align:right; }
    .receipt-title h2 { font-size:18px; font-weight:700; color:#1e293b; }
    .receipt-title .rcp-no { font-size:12px; color:#64748b; margin-top:4px; }
    .receipt-title .date { font-size:11px; color:#94a3b8; }
    .to-from { display:flex; gap:40px; margin-bottom:28px; }
    .to-from .block h4 { font-size:10px; text-transform:uppercase; letter-spacing:1px; color:#94a3b8; margin-bottom:8px; }
    .to-from .block p  { font-size:13px; font-weight:600; color:#1e293b; margin-bottom:3px; }
    .to-from .block small { font-size:11px; color:#64748b; display:block; }
    table { width:100%; border-collapse:collapse; margin-bottom:24px; }
    thead th { background:#f8fafc; padding:10px 12px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#64748b; border-bottom:2px solid #e2e8f0; }
    tbody td { padding:12px; border-bottom:1px solid #f1f5f9; font-size:13px; }
    .total-row { background:#f8fafc; font-weight:700; }
    .amount-box { background: linear-gradient(135deg,#e94560,#c62a47); color:#fff; padding:20px 24px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; }
    .amount-box .label { font-size:12px; opacity:0.85; }
    .amount-box .value { font-size:26px; font-weight:800; }
    .footer { border-top:1px solid #e2e8f0; padding-top:16px; display:flex; justify-content:space-between; align-items:flex-end; }
    .footer .note { font-size:11px; color:#94a3b8; max-width:300px; line-height:1.5; }
    .footer .status { text-align:right; }
    .stamp { display:inline-block; border:2px solid #16a34a; color:#16a34a; padding:6px 14px; border-radius:6px; font-weight:700; font-size:12px; letter-spacing:1px; }
</style>
</head>
<body>

<div class="header">
    <div class="brand">
        <h1>School System</h1>
        <p>Multi-School Management Platform</p>
        <p style="margin-top:4px;font-size:11px;color:#94a3b8;">support@schoolsystem.com</p>
    </div>
    <div class="receipt-title">
        <h2>PAYMENT RECEIPT</h2>
        <div class="rcp-no">{{ $payment->receipt_number }}</div>
        <div class="date">{{ $payment->payment_date->format('d F Y') }}</div>
    </div>
</div>

<div class="to-from">
    <div class="block">
        <h4>Bill To</h4>
        <p>{{ $payment->tenant->name }}</p>
        <small>{{ $payment->tenant->admin_name }}</small>
        <small>{{ $payment->tenant->admin_email }}</small>
        @if($payment->tenant->phone)<small>{{ $payment->tenant->phone }}</small>@endif
        @if($payment->tenant->address)<small>{{ $payment->tenant->address }}</small>@endif
    </div>
    <div class="block">
        <h4>Payment Details</h4>
        <p>{{ ucfirst(str_replace('_',' ', $payment->payment_method)) }}</p>
        @if($payment->reference)<small>Ref: {{ $payment->reference }}</small>@endif
        <small>Plan: {{ $payment->tenant->plan->name ?? 'N/A' }}</small>
        <small>Period: {{ $payment->months_paid }} month{{ $payment->months_paid > 1 ? 's' : '' }}</small>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Description</th>
            <th>Period (Months)</th>
            <th style="text-align:right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $payment->description ?: 'System Subscription — ' . $payment->tenant->name }}</td>
            <td>{{ $payment->months_paid }} month{{ $payment->months_paid > 1 ? 's' : '' }}</td>
            <td style="text-align:right;">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="2" style="text-align:right;">Total</td>
            <td style="text-align:right;">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="amount-box">
    <div>
        <div class="label">Amount Paid</div>
        <div class="value">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</div>
    </div>
    <div style="text-align:right;">
        <div class="label">Subscription Extended To</div>
        <div style="font-size:14px;font-weight:700;">{{ $payment->tenant->subscription_ends_at?->format('d M Y') ?? 'N/A' }}</div>
    </div>
</div>

<div class="footer">
    <div class="note">
        This is an official payment receipt for school system subscription services.
        Keep this receipt for your records. For queries contact support@schoolsystem.com.
    </div>
    <div class="status">
        <div class="stamp">PAID</div>
        <div style="font-size:10px;color:#94a3b8;margin-top:6px;">Generated {{ now()->format('d M Y H:i') }}</div>
    </div>
</div>

</body>
</html>
