<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family:Arial,sans-serif; font-size:10px; color:#000; }
    h1 { font-size:14px; text-align:center; margin-bottom:4px; }
    p.sub { text-align:center; font-size:10px; margin-bottom:12px; color:#555; }
    table { width:100%; border-collapse:collapse; margin-top:8px; }
    th { background:#D4A017; color:#000; padding:6px 8px; text-align:left; font-size:9px; text-transform:uppercase; border:1px solid #000; }
    td { padding:5px 8px; border:1px solid #ccc; font-size:10px; }
    tr:nth-child(even) td { background:#fef9c3; }
    .summary { margin-bottom:12px; display:flex; gap:20px; }
    .sum-box { border:2px solid #D4A017; padding:8px 12px; border-radius:6px; text-align:center; }
    .sum-box .val { font-size:14px; font-weight:900; color:#78520a; }
    .sum-box .lbl { font-size:9px; color:#555; text-transform:uppercase; }
</style>
</head>
<body>
@php
    $logoDataInline = $siteLogoData ?? '';
@endphp
@if($logoDataInline)
    <img src="{{ $logoDataInline }}" alt="{{ $schoolName ?? 'Logo' }}" style="width:60px;height:60px;object-fit:contain;display:block;margin:0 auto 10px;background:#000;border-radius:50%;padding:2px;">
@endif
<h1>{{ strtoupper($schoolName ?? 'School Name') }}</h1>
<p class="sub">Financial Report — Generated {{ now()->format('M d, Y') }}</p>

<div class="summary">
    <div class="sum-box"><div class="val">GH₵ {{ number_format($global['billed'],2) }}</div><div class="lbl">Total Billed</div></div>
    <div class="sum-box"><div class="val">GH₵ {{ number_format($global['collected'],2) }}</div><div class="lbl">Collected</div></div>
    <div class="sum-box"><div class="val">GH₵ {{ number_format($global['outstanding'],2) }}</div><div class="lbl">Outstanding</div></div>
</div>

<table>
    <thead>
        <tr><th>Student</th><th>Student ID</th><th>{{ $programLabelSingular ?? 'Program' }}</th><th>Total Fees</th><th>Paid</th><th>Balance</th></tr>
    </thead>
    <tbody>
        @foreach($studentFees as $row)
        <tr>
            <td>{{ $row['student']->user->full_name }}</td>
            <td>{{ $row['student']->student_id }}</td>
            <td>{{ $row['student']->program->name??'—' }}</td>
            <td>GH₵ {{ number_format($row['total'],2) }}</td>
            <td>GH₵ {{ number_format($row['paid'],2) }}</td>
            <td style="font-weight:bold;color:{{ $row['balance']>0?'#dc2626':'#16a34a' }}">GH₵ {{ number_format($row['balance'],2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
