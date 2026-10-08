<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e1b4b; }
    .page { padding: 24px 28px; position:relative; }
    .school-header { display:table; width:100%; border-bottom:3px solid #d97706; padding-bottom:12px; margin-bottom:16px; }
    .school-name { font-size:18px; font-weight:800; color:#1e1b4b; }
    .doc-title { font-size:13px; font-weight:700; color:#d97706; margin-top:3px; text-transform:uppercase; letter-spacing:.05em; }
    .school-sub { font-size:9px; color:#64748b; margin-top:2px; }

    .info-row { display:table; width:100%; margin-bottom:14px; }
    .info-box { display:table-cell; padding:10px 12px; background:#fffbeb; border:1px solid #fde68a; border-radius:6px; }
    .info-label { font-size:8.5px; font-weight:700; text-transform:uppercase; color:#92400e; letter-spacing:.04em; }
    .info-val { font-size:13px; font-weight:800; color:#1e1b4b; margin-top:2px; }

    .balance-row { display:table; width:100%; margin-bottom:14px; border-radius:6px; overflow:hidden; }
    .balance-cell { display:table-cell; text-align:center; padding:10px; }
    .balance-label { font-size:9px; font-weight:700; text-transform:uppercase; color:#fff; opacity:.8; }
    .balance-val { font-size:16px; font-weight:800; color:#fff; margin-top:3px; }

    table.payments { width:100%; border-collapse:collapse; margin-bottom:14px; }
    table.payments th { background:#1e1b4b; color:#fff; font-size:9px; font-weight:700; text-transform:uppercase; padding:6px 8px; }
    table.payments th.left { text-align:left; }
    table.payments th.right { text-align:right; }
    table.payments td { padding:5px 8px; border-bottom:1px solid #fef3c7; }
    table.payments tr:nth-child(even) td { background:#fffbeb; }
    table.payments td.right { text-align:right; }

    .total-row td { font-weight:800; font-size:12px; background:#fef3c7 !important; border-top:2px solid #d97706; }

    .footer-note { font-size:8.5px; color:#94a3b8; margin-top:8px; }
    .qr-block { position:absolute; bottom:20px; right:22px; text-align:center; }
    .qr-block img { width:70px; height:70px; }
    .qr-text { font-size:7px; color:#94a3b8; margin-top:2px; }
</style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="school-header">
        <div>
            <div class="school-name">{{ $schoolName }}</div>
            <div class="doc-title">Student Fee Statement</div>
            <div class="school-sub">Ref: {{ $doc?->uuid ?? 'N/A' }} &bull; Issued: {{ now()->format('d M Y H:i') }}</div>
        </div>
    </div>

    {{-- Student info --}}
    <table style="width:100%;margin-bottom:14px;border-collapse:collapse;">
        <tr>
            <td style="padding:8px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:4px;width:33%;">
                <div style="font-size:8.5px;font-weight:700;text-transform:uppercase;color:#92400e;">Student Name</div>
                <div style="font-size:13px;font-weight:800;margin-top:2px;">{{ $student->user->full_name }}</div>
            </td>
            <td style="padding:8px 12px;background:#fffbeb;border:1px solid #fde68a;border-left:none;width:25%;">
                <div style="font-size:8.5px;font-weight:700;text-transform:uppercase;color:#92400e;">Admission No.</div>
                <div style="font-size:13px;font-weight:800;margin-top:2px;">{{ $student->student_id }}</div>
            </td>
            <td style="padding:8px 12px;background:#fffbeb;border:1px solid #fde68a;border-left:none;width:42%;">
                <div style="font-size:8.5px;font-weight:700;text-transform:uppercase;color:#92400e;">Program</div>
                <div style="font-size:12px;font-weight:800;margin-top:2px;">{{ $student->program?->name ?? '—' }}</div>
            </td>
        </tr>
    </table>

    {{-- Balance row --}}
    <table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
        <tr>
            <td style="text-align:center;padding:10px;background:#1e40af;">
                <div class="balance-label">Total Billed</div>
                <div class="balance-val">GH₵ {{ number_format(($fees['billed'] ?? $fees['total'] ?? 0), 2) }}</div>
            </td>
            <td style="text-align:center;padding:10px;background:#065f46;">
                <div class="balance-label">Total Paid</div>
                <div class="balance-val">GH₵ {{ number_format($fees['paid']??0, 2) }}</div>
            </td>
            <td style="text-align:center;padding:10px;background:{{ (($fees['balance']??0)>0)?'#991b1b':'#065f46' }};">
                <div class="balance-label">Outstanding Balance</div>
                <div class="balance-val">GH₵ {{ number_format($fees['balance']??0, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Payments table --}}
    <table class="payments">
        <thead>
            <tr>
                <th class="left">Date</th>
                <th class="left">Description</th>
                <th class="right">Amount (GH₵)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($student->payments->sortByDesc('created_at') as $pay)
            <tr>
                <td>{{ \Carbon\Carbon::parse($pay->created_at)->format('d M Y') }}</td>
                <td>{{ $pay->description ?? 'School Fees Payment' }}</td>
                <td class="right" style="color:#166534;font-weight:700;">{{ number_format($pay->amount,2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align:center;color:#94a3b8;padding:12px;">No payments recorded.</td>
            </tr>
            @endforelse
            @if($student->payments->isNotEmpty())
            <tr class="total-row">
                <td colspan="2" style="text-align:right;">Total Paid</td>
                <td class="right">GH₵ {{ number_format($fees['paid']??0,2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer-note">
        This is an official fee statement generated on {{ now()->format('d M Y') }}. Document ID: {{ $doc?->uuid ?? 'N/A' }}.
        @if($doc?->verify_url)Verify authenticity at: {{ $doc->verify_url }}@endif
    </div>

    <div class="qr-block">
        <img src="{{ $qrUrl }}" alt="Verify">
        <div class="qr-text">Scan to verify</div>
    </div>

</div>
</body>
</html>
