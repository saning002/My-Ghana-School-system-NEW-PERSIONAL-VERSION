<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #000; }
    h1 { font-size: 14px; text-align: center; margin-bottom: 4px; }
    p.sub { text-align: center; font-size: 10px; margin-bottom: 12px; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th { background: #D4A017; color: #000; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; border: 1px solid #000; }
    td { padding: 5px 8px; border: 1px solid #ccc; font-size: 10px; }
    tr:nth-child(even) td { background: #fef9c3; }
    .center { text-align: center; }
</style>
</head>
<body>
@php
    $logoDataInline = $siteLogoData ?? '';
    $progLabel = $programLabel ?? \App\Models\Setting::get('sidebar_programs_label', 'Programs');
    $progLabelSingular = \Illuminate\Support\Str::singular($progLabel);
@endphp
@if($logoDataInline)
    <img src="{{ $logoDataInline }}" alt="{{ $schoolName ?? 'Logo' }}" style="width:60px;height:60px;object-fit:contain;display:block;margin:0 auto 10px;background:#000;border-radius:50%;padding:2px;">
@endif
<h1>{{ strtoupper($schoolName ?? 'School Name') }}</h1>
<p class="sub">COURSE ENROLLMENT REPORT — Generated {{ now()->format('M d, Y') }}</p>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Code</th>
            <th>Course Name</th>
            <th>{{ $progLabelSingular }}</th>
            <th class="center">Enrolled Students</th>
        </tr>
    </thead>
    <tbody>
        @foreach($courses as $i => $course)
        <tr>
            <td>{{ $i+1 }}</td>
            <td style="font-family:monospace;font-weight:bold">{{ $course->code }}</td>
            <td style="font-weight:bold">{{ $course->name }}</td>
            <td>{{ $course->program->name ?? '—' }}</td>
            <td class="center" style="font-weight:bold">{{ $course->enrollments_count }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
