<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; font-size:14px; color:#1e293b; background:#f8fafc; margin:0; padding:0; }
.wrapper { max-width:560px; margin:30px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); }
.header { background: linear-gradient(135deg,#e94560,#c62a47); padding:28px 32px; color:#fff; }
.header h2 { margin:0; font-size:20px; }
.header p  { margin:4px 0 0; opacity:0.85; font-size:13px; }
.body { padding:28px 32px; }
.body p { line-height:1.7; color:#475569; }
.detail-box { background:#f8fafc; border-radius:8px; padding:16px 20px; margin:20px 0; }
.detail-box table { width:100%; border-collapse:collapse; }
.detail-box td { padding:6px 0; font-size:13px; }
.detail-box td:first-child { color:#94a3b8; width:45%; }
.detail-box td:last-child { font-weight:600; color:#1e293b; }
.amount-highlight { background:linear-gradient(135deg,#e94560,#c62a47); color:#fff; border-radius:8px; padding:16px 20px; text-align:center; margin:20px 0; }
.amount-highlight .label { font-size:12px; opacity:0.85; }
.amount-highlight .value { font-size:28px; font-weight:800; }
.footer { background:#f8fafc; padding:20px 32px; text-align:center; font-size:12px; color:#94a3b8; border-top:1px solid #e2e8f0; }
</style></head>
<body>
<div class="wrapper">
    <div class="header">
        <h2>Payment Receipt</h2>
        <p>{{ $payment->receipt_number }} · {{ $payment->payment_date->format('d F Y') }}</p>
    </div>
    <div class="body">
        <p>Dear <strong>{{ $payment->tenant->admin_name }}</strong>,</p>
        <p>Thank you for your payment. Please find the details below. The PDF receipt is attached to this email.</p>

        <div class="amount-highlight">
            <div class="label">Amount Paid</div>
            <div class="value">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</div>
        </div>

        <div class="detail-box">
            <table>
                <tr><td>Receipt Number</td><td>{{ $payment->receipt_number }}</td></tr>
                <tr><td>School</td><td>{{ $payment->tenant->name }}</td></tr>
                <tr><td>Payment Date</td><td>{{ $payment->payment_date->format('d F Y') }}</td></tr>
                <tr><td>Payment Method</td><td>{{ ucfirst(str_replace('_',' ',$payment->payment_method)) }}</td></tr>
                @if($payment->reference)<tr><td>Reference</td><td>{{ $payment->reference }}</td></tr>@endif
                <tr><td>Months Covered</td><td>{{ $payment->months_paid }} month{{ $payment->months_paid > 1 ? 's' : '' }}</td></tr>
                <tr><td>Subscription Valid Until</td><td>{{ $payment->tenant->subscription_ends_at?->format('d M Y') ?? 'N/A' }}</td></tr>
            </table>
        </div>

        <p>Your subscription is now <strong style="color:#16a34a;">active</strong> and valid until <strong>{{ $payment->tenant->subscription_ends_at?->format('d M Y') ?? 'N/A' }}</strong>.</p>
        <p>If you have any questions, please contact us at <a href="mailto:support@schoolsystem.com">support@schoolsystem.com</a>.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} School Management Platform. All rights reserved.
    </div>
</div>
</body>
</html>
