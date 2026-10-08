<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e1b4b; }
    .page { padding: 18px 22px; position:relative; }
    .school-header { text-align:center; border-bottom:3px solid #059669; padding-bottom:10px; margin-bottom:12px; }
    .school-name { font-size:16px; font-weight:800; color:#1e1b4b; }
    .doc-title { font-size:12px; font-weight:700; color:#059669; margin-top:3px; text-transform:uppercase; letter-spacing:.05em; }
    .school-sub { font-size:9px; color:#64748b; margin-top:2px; }
    table.data { width:100%; border-collapse:collapse; }
    table.data th { background:#064e3b; color:#fff; font-size:8.5px; font-weight:700; text-transform:uppercase; padding:5px 8px; }
    table.data th.left { text-align:left; }
    table.data td { padding:4px 8px; border-bottom:1px solid #f0fdf4; }
    table.data tr:nth-child(even) td { background:#f0fdf4; }
    .chip { display:inline-block; padding:1px 6px; border-radius:999px; font-size:8px; font-weight:800; }
    .good    { background:#dcfce7; color:#166534; }
    .average { background:#fef9c3; color:#854d0e; }
    .poor    { background:#fee2e2; color:#991b1b; }
    .qr-block { position:absolute; bottom:16px; right:16px; text-align:center; }
    .qr-block img { width:60px; height:60px; }
    .qr-text { font-size:7px; color:#94a3b8; margin-top:2px; }
    .bar-wrap { width:60px; height:5px; background:#e2e8f0; border-radius:3px; display:inline-block; vertical-align:middle; }
    .bar-fill { height:5px; border-radius:3px; }
</style>
</head>
<body>
<div class="page">
    <div class="school-header">
        <div class="school-name">{{ $schoolName ?? \App\Models\Setting::get('school_name', config('app.name')) }}</div>
        <div class="doc-title">Attendance Summary Report</div>
        <div class="school-sub">
            {{ $data['program']->name }}
            @if($data['period']) &bull; {{ $data['period']->full_label }} @endif
            &bull; School Days: {{ $data['school_days'] }}
            &bull; Ref: {{ $doc?->uuid ?? 'N/A' }} &bull; {{ now()->format('d M Y') }}
        </div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th class="left">Student</th>
                <th class="left">Admission No.</th>
                <th>School Days</th>
                <th>Present</th>
                <th>Absent</th>
                <th>% Attendance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['student_summaries']->sortByDesc('percentage') as $row)
            <tr>
                <td>{{ $row['student']->user->full_name }}</td>
                <td style="font-size:8.5px;color:#64748b;">{{ $row['student']->student_id }}</td>
                <td style="text-align:center;">{{ $row['school_days'] }}</td>
                <td style="text-align:center;color:#166534;font-weight:700;">{{ $row['present'] }}</td>
                <td style="text-align:center;color:#991b1b;font-weight:700;">{{ $row['absent'] }}</td>
                <td style="text-align:center;">
                    <span style="font-weight:800;">{{ $row['percentage'] }}%</span>
                    <span class="bar-wrap">
                        <span class="bar-fill" style="width:{{ $row['percentage'] }}%;background:{{ $row['percentage']>=75?'#10b981':($row['percentage']>=50?'#f59e0b':'#ef4444') }};"></span>
                    </span>
                </td>
                <td style="text-align:center;"><span class="chip {{ $row['status'] }}">{{ ucfirst($row['status']) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="qr-block">
        <img src="{{ $qrUrl }}" alt="Verify">
        <div class="qr-text">Scan to verify</div>
    </div>
</div>
</body>
</html>
