<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->receipt_number ?? 'N/A' }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size:13px; color:#1e293b; background:#f8fafc; }

        .receipt-wrapper { max-width:680px; margin:30px auto; background:#fff; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.08); overflow:hidden; }

        /* Header */
        .receipt-header { background:linear-gradient(135deg,var(--admin-nav),var(--admin-nav)); padding:28px 32px; color:#fff; display:flex; justify-content:space-between; align-items:center; }
        .school-info h1 { font-size:20px; font-weight:800; margin-bottom:4px; }
        .school-info p  { font-size:12px; opacity:0.7; }
        .receipt-badge { text-align:right; }
        .receipt-badge .label { font-size:11px; opacity:0.7; text-transform:uppercase; letter-spacing:1px; }
        .receipt-badge .number { font-size:18px; font-weight:800; color:var(--brand); }
        .receipt-badge .date   { font-size:12px; opacity:0.7; margin-top:2px; }

        /* Status stamp */
        .paid-stamp { text-align:center; padding:12px; background:#dcfce7; border-bottom:1px solid #bbf7d0; }
        .paid-stamp span { color:#16a34a; font-weight:800; font-size:14px; letter-spacing:2px; text-transform:uppercase; }

        /* Body */
        .receipt-body { padding:28px 32px; }

        .section-title { font-size:10px; text-transform:uppercase; letter-spacing:1px; color:#94a3b8; font-weight:700; margin-bottom:10px; margin-top:20px; }

        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:4px; }
        .info-item .label { font-size:11px; color:#94a3b8; margin-bottom:2px; }
        .info-item .value { font-size:13px; font-weight:600; color:#1e293b; }

        /* Fee table */
        .fee-table { width:100%; border-collapse:collapse; margin-top:8px; }
        .fee-table th { background:#f8fafc; padding:9px 12px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#64748b; border-bottom:2px solid #e2e8f0; }
        .fee-table td { padding:10px 12px; border-bottom:1px solid #f1f5f9; font-size:13px; }
        .fee-table tr:last-child td { border-bottom:none; }
        .fee-table .amount { text-align:right; font-weight:600; }
        .fee-table tfoot td { font-weight:700; background:#f8fafc; border-top:2px solid #e2e8f0; padding:12px; font-size:14px; }

        /* Amount highlight */
        .amount-box { background:linear-gradient(135deg,var(--admin-nav),var(--admin-nav)); color:#fff; border-radius:10px; padding:16px 20px; margin-top:20px; display:flex; justify-content:space-between; align-items:center; }
        .amount-box .label { font-size:12px; opacity:0.7; }
        .amount-box .value { font-size:24px; font-weight:800; color:var(--brand); }

        /* Footer */
        .receipt-footer { background:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 32px; display:flex; justify-content:space-between; align-items:center; font-size:11px; color:#94a3b8; }

        /* Print actions */
        .print-actions { text-align:center; padding:20px; }
        .btn-print { background:var(--admin-nav); color:#fff; border:none; padding:10px 28px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; margin:0 6px; }
        .btn-back  { background:#f1f5f9; color:#475569; border:none; padding:10px 28px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; margin:0 6px; }

        @media print {
            body { background:#fff; }
            .receipt-wrapper { box-shadow:none; margin:0; border-radius:0; }
            .print-actions { display:none; }
        }
    </style>
</head>
<body>

<div class="print-actions">
    <button class="btn-print" onclick="window.print()"><i>�Y-�</i> Print Receipt</button>
    <a href="{{ url()->previous() }}" class="btn-back">–� Back</a>
</div>

<div class="receipt-wrapper">

    {{-- Header --}}
    <div class="receipt-header">
        <div class="school-info">
            <h1>{{ $schoolName }}</h1>
            <p>Official Payment Receipt</p>
        </div>
        <div class="receipt-badge">
            <div class="label">Receipt No.</div>
            <div class="number">{{ $payment->receipt_number ?? 'N/A' }}</div>
            <div class="date">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</div>
        </div>
    </div>

    <div class="paid-stamp">
        <span>�o" Payment Received</span>
    </div>

    <div class="receipt-body">

        {{-- Student Info --}}
        <div class="section-title">Student Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Full Name</div>
                <div class="value">{{ $student?->user?->full_name ?? '–' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Student ID</div>
                <div class="value">{{ $student?->student_id ?? '–' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Programme</div>
                <div class="value">{{ $student?->program?->name ?? '–' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Academic Session</div>
                <div class="value">{{ $session?->year ?? 'Not specified' }}</div>
            </div>
        </div>

        {{-- Payer Info --}}
        @if($payment->payer_name || $payment->payer_phone)
        <div class="section-title">Paid By</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Payer Name</div>
                <div class="value">{{ $payment->payer_name ?? '–' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Phone</div>
                <div class="value">{{ $payment->payer_phone ?? '–' }}</div>
            </div>
        </div>
        @endif

        {{-- Payment Details --}}
        <div class="section-title">Payment Details</div>
        <table class="fee-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Payment Method</th>
                    <th class="amount">Amount (GHS)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        School Fees Payment
                        @if($session) – {{ $session->year }} @endif
                        @if($payment->notes) <br><small style="color:#94a3b8;">{{ $payment->notes }}</small> @endif
                    </td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'cash')) }}</td>
                    <td class="amount">{{ number_format($payment->amount_paid, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total Amount Paid</td>
                    <td class="amount">GHS {{ number_format($payment->amount_paid, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Amount highlight --}}
        <div class="amount-box">
            <div>
                <div class="label">Amount Paid</div>
                <div style="font-size:11px;opacity:0.6;margin-top:2px;">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'cash')) }}</div>
            </div>
            <div class="value">GHS {{ number_format($payment->amount_paid, 2) }}</div>
        </div>

    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
        <div>{{ $schoolName }} – Official Receipt</div>
        <div>Generated: {{ now()->format('d M Y H:i') }}</div>
        <div>Receipt: {{ $payment->receipt_number ?? 'N/A' }}</div>
    </div>

</div>

<div class="print-actions">
    <button class="btn-print" onclick="window.print()"><i>�Y-�</i> Print Receipt</button>
    <a href="{{ url()->previous() }}" class="btn-back">–� Back</a>
</div>

</body>
</html>
