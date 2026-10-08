<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body{font-family:Arial,sans-serif;font-size:9px;color:#000}
    h1{font-size:13px;text-align:center;margin-bottom:4px}
    p.sub{text-align:center;font-size:9px;margin-bottom:10px;color:#555}
    table{width:100%;border-collapse:collapse}
    th{background:#D4A017;color:#000;padding:5px 6px;text-align:left;font-size:8px;text-transform:uppercase;border:1px solid #000}
    td{padding:4px 6px;border:1px solid #ccc;font-size:9px}
    tr:nth-child(even) td{background:#fef9c3}
</style>
</head>
<body>
@php
    $logoDataInline = $siteLogoData ?? '';
@endphp
<div style="text-align:center;margin-bottom:12px">
    @if($logoDataInline)
        <img src="{{ $logoDataInline }}" style="width:64px;height:64px;object-fit:contain;margin:0 auto;display:block" />
    @endif
    <h2 style="margin:6px 0 0;">{{ $schoolName ?? 'School Name' }}</h2>
</div>
<h1>{{ strtoupper($schoolName ?? 'School Name') }}</h1>
<p class="sub">Attendance Report — Generated {{ now()->format('M d, Y') }}</p>
<table>
    <thead>
        <tr><th>#</th><th>Student</th><th>Course</th><th>Date</th><th>Status</th></tr>
    </thead>
    <tbody>
        @foreach($attendances as $i => $att)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $att->student->user->full_name??'—' }}</td>
            <td>{{ $att->course->code??'—' }}</td>
            <td>{{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</td>
            <td style="font-weight:bold;color:{{ $att->status==='present'?'#16a34a':'#dc2626' }}">{{ ucfirst($att->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
