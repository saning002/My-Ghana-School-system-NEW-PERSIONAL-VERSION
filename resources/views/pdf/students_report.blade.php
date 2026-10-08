<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body{font-family:Arial,sans-serif;font-size:10px;color:#000}
    h1{font-size:14px;text-align:center;margin-bottom:4px}
    p.sub{text-align:center;font-size:10px;margin-bottom:12px;color:#555}
    table{width:100%;border-collapse:collapse;margin-top:8px}
    th{background:#D4A017;color:#000;padding:6px 8px;text-align:left;font-size:9px;text-transform:uppercase;border:1px solid #000}
    td{padding:5px 8px;border:1px solid #ccc;font-size:10px}
    tr:nth-child(even) td{background:#fef9c3}
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
<p class="sub">Student Report — Generated {{ now()->format('M d, Y') }}</p>
<table>
    <thead>
        <tr><th>#</th><th>Student ID</th><th>Full Name</th><th>{{ $programLabelSingular ?? 'Program' }}</th><th>Branch</th><th>Admitted</th><th>Status</th></tr>
    </thead>
    <tbody>
        @foreach($students as $i => $s)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $s->student_id }}</td>
            <td>{{ $s->user->full_name }}</td>
            <td>{{ $s->program->name??'—' }}</td>
            <td>{{ $s->churchBranch->name??'—' }}</td>
            <td>{{ \Carbon\Carbon::parse($s->admission_date)->format('M d, Y') }}</td>
            <td>{{ ucfirst($s->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
