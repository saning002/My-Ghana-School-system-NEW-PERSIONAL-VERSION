<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#1e1b4b; }
    .page { padding:20px 24px; }
    .header { text-align:center; border-bottom:3px solid #10b981; padding-bottom:10px; margin-bottom:14px; }
    .school-name { font-size:16px; font-weight:800; }
    .doc-title   { font-size:12px; font-weight:700; color:#10b981; margin-top:3px; text-transform:uppercase; letter-spacing:.05em; }
    .sub         { font-size:9px; color:#64748b; margin-top:2px; }
    .kpi-table { width:100%; border-collapse:collapse; margin-bottom:12px; }
    .kpi-table td { text-align:center; padding:7px; background:#f1f5f9; border:2px solid #fff; }
    .kpi-val { font-size:14px; font-weight:800; }
    .kpi-lab { font-size:8px; font-weight:700; text-transform:uppercase; color:#64748b; margin-top:2px; }
    table.data { width:100%; border-collapse:collapse; margin-bottom:12px; font-size:9.5px; }
    table.data th { background:#064e3b; color:#fff; font-size:8.5px; font-weight:700; text-transform:uppercase; padding:5px 7px; }
    table.data th.left { text-align:left; }
    table.data td { padding:4px 7px; border-bottom:1px solid #f0fdf4; }
    table.data tr:nth-child(even) td { background:#f0fdf4; }
    h4 { font-size:10px; font-weight:800; color:#1e1b4b; margin-bottom:5px; text-transform:uppercase; letter-spacing:.04em; }
    .unpaid-name { color:#991b1b; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="school-name">{{ $schoolName }}</div>
        <div class="doc-title">Daily Fee Collection Report</div>
        <div class="sub">Date: {{ \Carbon\Carbon::parse($date)->format('l, F j Y') }} &bull; Rate: GH₵ {{ number_format($dailyRate,2) }} / student &bull; Generated: {{ now()->format('d M Y H:i') }}</div>
    </div>

    <table class="kpi-table">
        <tr>
            <td><div class="kpi-val" style="color:#10b981">{{ $records->count() }}</div><div class="kpi-lab">Paid</div></td>
            <td><div class="kpi-val" style="color:#ef4444">{{ $unpaid->count() }}</div><div class="kpi-lab">Unpaid</div></td>
            <td><div class="kpi-val" style="color:#10b981">GH₵ {{ number_format($records->sum('amount'),2) }}</div><div class="kpi-lab">Total Collected</div></td>
            <td><div class="kpi-val" style="color:#f59e0b">GH₵ {{ number_format($unpaid->count()*$dailyRate,2) }}</div><div class="kpi-lab">Estimated Outstanding</div></td>
        </tr>
    </table>

    <h4>Paid Students</h4>
    <table class="data">
        <thead>
            <tr>
                <th class="left">#</th>
                <th class="left">Student</th>
                <th class="left">Admission No.</th>
                <th class="left">Program</th>
                <th style="text-align:right">Amount</th>
                <th class="left">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $i => $r)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $r->student?->user?->full_name ?? '—' }}</td>
                <td style="font-size:8.5px;color:#64748b;">{{ $r->student?->student_id }}</td>
                <td style="font-size:8.5px;color:#64748b;">{{ $r->student?->program?->name }}</td>
                <td style="text-align:right;font-weight:700;color:#166534;">GH₵ {{ number_format($r->amount,2) }}</td>
                <td style="font-size:8.5px;color:#64748b;">{{ $r->notes ?? '—' }}</td>
            </tr>
            @endforeach
            @if($records->isEmpty())
            <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:10px;">No payments recorded.</td></tr>
            @endif
        </tbody>
    </table>

    @if($unpaid->isNotEmpty())
    <h4 style="color:#991b1b;margin-top:8px;">Unpaid Students</h4>
    <table class="data">
        <thead>
            <tr>
                <th class="left">#</th>
                <th class="left">Student</th>
                <th class="left">Admission No.</th>
                <th class="left">Program</th>
                <th style="text-align:right">Amount Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unpaid->values() as $i => $s)
            <tr>
                <td>{{ $i+1 }}</td>
                <td class="unpaid-name">{{ $s->user->full_name }}</td>
                <td style="font-size:8.5px;color:#64748b;">{{ $s->student_id }}</td>
                <td style="font-size:8.5px;color:#64748b;">{{ $s->program?->name }}</td>
                <td style="text-align:right;font-weight:700;color:#991b1b;">GH₵ {{ number_format($dailyRate,2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div style="margin-top:10px;font-size:8.5px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:7px;text-align:center;">
        Daily Fee Report — {{ $schoolName }} — {{ now()->format('d M Y H:i') }}
    </div>
</div>
</body>
</html>
