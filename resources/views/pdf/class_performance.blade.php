<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e1b4b; background: #fff; }
    .page { padding: 18px 22px; }
    .school-header { text-align:center; border-bottom: 3px solid #5647d6; padding-bottom: 10px; margin-bottom: 12px; }
    .school-logo-wrap { text-align:center; margin-bottom:8px; }
    .school-logo { width:60px; height:60px; object-fit:contain; border-radius:50%; background:#1e1b4b; padding:4px; }
    .school-name { font-size:16px; font-weight:800; color:#1e1b4b; }
    .school-tagline { font-size:9px; color:#64748b; margin-top:1px; }
    .doc-title { font-size:12px; font-weight:700; color:#5647d6; margin-top:3px; text-transform:uppercase; letter-spacing:.05em; }
    .school-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .kpi-table { width:100%; border-collapse:collapse; margin-bottom:12px; }
    .kpi-table td { text-align:center; padding:7px 4px; background:#f1f5f9; border:2px solid #fff; }
    .kpi-val { font-size:14px; font-weight:800; color:#1e1b4b; }
    .kpi-lab { font-size:8px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:.04em; margin-top:2px; }
    table.data { width:100%; border-collapse:collapse; margin-bottom:12px; font-size:9.5px; }
    table.data th { background:#1e1b4b; color:#fff; font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:5px 7px; }
    table.data th.left { text-align:left; }
    table.data td { padding:4px 7px; border-bottom:1px solid #f1f5f9; }
    table.data tr:nth-child(even) td { background:#f8fafc; }
    .chip { display:inline-block; padding:1px 6px; border-radius:999px; font-size:8px; font-weight:800; }
    .pass { background:#dcfce7; color:#166534; }
    .fail { background:#fee2e2; color:#991b1b; }
    .qr-block { position:absolute; bottom:16px; right:16px; text-align:center; }
    .qr-block img { width:60px; height:60px; }
    .qr-text { font-size:7px; color:#94a3b8; margin-top:2px; }
    h4 { font-size:10px; font-weight:700; color:#1e1b4b; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
</style>
</head>
<body>
<div class="page" style="position:relative;">
@php
    $logoData = $siteLogoData ?? '';
    if (empty($logoData)) {
        $logoData = \App\Support\ReportCardPdfAssets::logoBase64();
    }
@endphp
    <div class="school-header">
        @if($logoData)
        <div class="school-logo-wrap">
            <img src="{{ $logoData }}" alt="Logo" class="school-logo">
        </div>
        @endif
        <div class="school-name">{{ $schoolName ?? \App\Models\Setting::get('school_name', config('app.name')) }}</div>
        @if(!empty($schoolSubtitle))
        <div class="school-tagline">{{ $schoolSubtitle }}</div>
        @endif
        <div class="doc-title">Class Performance Report</div>
        <div class="school-sub">
            {{ $data['program']->name }}
            @if($data['period']) &bull; {{ $data['period']->full_label }} @endif
            &bull; Attempt {{ $data['attempt'] }}
            &bull; Ref: {{ $doc?->uuid ?? 'N/A' }} &bull; {{ now()->format('d M Y') }}
        </div>
    </div>

    @if(!$data['has_data'])
    <div style="text-align:center;padding:30px;color:#94a3b8;">No data available for the selected filters.</div>
    @else

    {{-- KPI row --}}
    <table class="kpi-table">
        <tr>
            @foreach([
                ['Total Students', $data['total_students']],
                ['Class Average',  $data['class_average'].'%'],
                ['Highest',        $data['highest_score'].'%'],
                ['Lowest',         $data['lowest_score'].'%'],
                ['Pass Rate',      $data['pass_rate'].'%'],
                ['Fail Rate',      $data['fail_rate'].'%'],
            ] as $k)
            <td>
                <div class="kpi-val">{{ $k[1] }}</div>
                <div class="kpi-lab">{{ $k[0] }}</div>
            </td>
            @endforeach
        </tr>
    </table>

    {{-- Subject Stats --}}
    <h4>Subject Performance</h4>
    <table class="data">
        <thead>
            <tr>
                <th class="left">Subject</th>
                <th>Average</th>
                <th>Highest</th>
                <th>Lowest</th>
                <th>Pass Rate</th>
                <th>Fail Rate</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['subject_stats'] as $s)
            <tr>
                <td>{{ $s['course']->name }}</td>
                <td style="text-align:center;font-weight:700;">{{ $s['average'] }}%</td>
                <td style="text-align:center;color:#166534;">{{ $s['highest'] }}%</td>
                <td style="text-align:center;color:#991b1b;">{{ $s['lowest'] }}%</td>
                <td style="text-align:center;"><span class="chip pass">{{ $s['pass_rate'] }}%</span></td>
                <td style="text-align:center;"><span class="chip fail">{{ $s['fail_rate'] }}%</span></td>
                <td style="text-align:center;">{{ $s['count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Student Rankings --}}
    <h4>Student Rankings</h4>
    <table class="data">
        <thead>
            <tr>
                <th>Rank</th>
                <th class="left">Student</th>
                <th>Admission No.</th>
                <th>Average</th>
                <th>Grade</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['ranked_students'] as $r)
            <tr>
                <td style="text-align:center;font-weight:800;">{{ $r['rank'] }}</td>
                <td>{{ $r['student']->user->full_name }}</td>
                <td style="text-align:center;font-size:8.5px;color:#64748b;">{{ $r['student']->student_id }}</td>
                <td style="text-align:center;font-weight:800;">{{ $r['average'] }}%</td>
                <td style="text-align:center;"><span class="chip {{ $r['passed']?'pass':'fail' }}">{{ $r['grade'] }}</span></td>
                <td style="text-align:center;"><span class="chip {{ $r['passed']?'pass':'fail' }}">{{ $r['passed']?'Pass':'Fail' }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(!empty($qrUrl))
    <div class="qr-block">
        <img src="{{ $qrUrl }}" alt="Verify">
        <div class="qr-text">Scan to verify</div>
    </div>
    @endif
</div>
</body>
</html>
