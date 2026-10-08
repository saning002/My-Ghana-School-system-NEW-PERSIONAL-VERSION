<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#1e1b4b; }
    .page { padding:20px 24px; position:relative; }
    .school-header { text-align:center; border-bottom:3px solid #5647d6; padding-bottom:10px; margin-bottom:14px; }
    .school-name { font-size:16px; font-weight:800; color:#1e1b4b; }
    .doc-title { font-size:12px; font-weight:700; color:#5647d6; margin-top:3px; text-transform:uppercase; letter-spacing:.05em; }
    .school-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .kpi-table { width:100%; border-collapse:collapse; margin-bottom:12px; }
    .kpi-table td { text-align:center; padding:7px 4px; background:#f1f5f9; border:2px solid #fff; }
    .kpi-val { font-size:14px; font-weight:800; color:#1e1b4b; }
    .kpi-lab { font-size:8px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:.04em; margin-top:2px; }
    .prog-block { margin-bottom:10px; }
    .prog-header { background:#1e1b4b; color:#fff; padding:5px 10px; font-size:10px; font-weight:700; display:table; width:100%; }
    table.scores { width:100%; border-collapse:collapse; }
    table.scores th { background:#f1f5f9; color:#64748b; font-size:8px; font-weight:700; text-transform:uppercase; padding:4px 7px; border-bottom:1px solid #e2e8f0; }
    table.scores th.left { text-align:left; }
    table.scores td { padding:3.5px 7px; border-bottom:1px solid #f8fafc; font-size:9.5px; }
    .chip { display:inline-block; padding:1px 5px; border-radius:999px; font-size:8px; font-weight:800; }
    .pass { background:#dcfce7; color:#166534; }
    .fail { background:#fee2e2; color:#991b1b; }
    .qr-block { position:absolute; top:20px; right:22px; text-align:center; }
    .qr-block img { width:65px; height:65px; }
    .qr-text { font-size:7px; color:#94a3b8; margin-top:2px; }
    .watermark { font-size:8.5px; color:#94a3b8; text-align:center; margin-top:10px; border-top:1px solid #e2e8f0; padding-top:6px; }
</style>
</head>
<body>
<div class="page">

    <div class="qr-block">
        <img src="{{ $qrUrl }}" alt="Verify">
        <div class="qr-text">Scan to verify</div>
    </div>

    <div class="school-header">
        <div class="school-name">{{ $schoolName ?? \App\Models\Setting::get('school_name', config('app.name')) }}</div>
        <div class="doc-title">Student Performance Statement</div>
        <div class="school-sub">Ref: {{ $doc?->uuid ?? "N/A" }} &bull; Issued: {{ now()->format('d M Y') }}</div>
    </div>

    {{-- Student info --}}
    <table style="width:100%;margin-bottom:12px;border-collapse:collapse;">
        <tr>
            <td style="padding:7px 10px;background:#eff6ff;border:1px solid #bfdbfe;width:33%;">
                <div style="font-size:8px;font-weight:700;text-transform:uppercase;color:#1e40af;">Name</div>
                <div style="font-size:12px;font-weight:800;margin-top:1px;">{{ $student->user->full_name }}</div>
            </td>
            <td style="padding:7px 10px;background:#eff6ff;border:1px solid #bfdbfe;border-left:none;width:22%;">
                <div style="font-size:8px;font-weight:700;text-transform:uppercase;color:#1e40af;">Admission No.</div>
                <div style="font-size:12px;font-weight:800;margin-top:1px;">{{ $student->student_id }}</div>
            </td>
            <td style="padding:7px 10px;background:#eff6ff;border:1px solid #bfdbfe;border-left:none;width:45%;">
                <div style="font-size:8px;font-weight:700;text-transform:uppercase;color:#1e40af;">Current Program</div>
                <div style="font-size:11px;font-weight:800;margin-top:1px;">{{ $student->program?->name ?? '—' }}</div>
            </td>
        </tr>
    </table>

    {{-- KPI summary --}}
    <table class="kpi-table">
        <tr>
            <td><div class="kpi-val">{{ count($data['trend']) }}</div><div class="kpi-lab">Attempts Recorded</div></td>
            <td><div class="kpi-val">{{ $data['overall_avg'] ?? 0 }}%</div><div class="kpi-lab">Overall Average</div></td>
            <td><div class="kpi-val">{{ $data['best'] ?? 0 }}%</div><div class="kpi-lab">Best Performance</div></td>
            <td><div class="kpi-val">{{ $data['worst'] ?? 0 }}%</div><div class="kpi-lab">Lowest Score</div></td>
        </tr>
    </table>

    {{-- Per-program records --}}
    @foreach($data['trend'] as $entry)
    <div class="prog-block">
        <div class="prog-header">
            <div style="display:table;width:100%;">
                <div style="display:table-cell;">{{ $entry['program']->name }} — Attempt {{ $entry['attempt'] }}</div>
                <div style="display:table-cell;text-align:right;font-size:9px;opacity:.8;">
                    Avg: {{ $entry['average'] }}% &nbsp;|&nbsp;
                    {{ $entry['grade'] }} &nbsp;|&nbsp;
                    <span style="color:{{ $entry['result']==='PASS'?'#6ee7b7':'#fca5a5' }}">{{ $entry['result'] }}</span>
                    &nbsp;|&nbsp; Rank: {{ $entry['position'] }} / {{ $entry['of'] }}
                </div>
            </div>
        </div>
        <table class="scores">
            <thead>
                <tr>
                    <th class="left">Subject</th>
                    <th>Class Score</th>
                    <th>Exam</th>
                    <th>Total</th>
                    <th>Grade</th>
                    <th>Pos.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entry['courses'] as $row)
                <tr>
                    <td>{{ $row['course']->name }}</td>
                    <td style="text-align:center;color:#64748b;">{{ $row['class_score'] }}</td>
                    <td style="text-align:center;color:#64748b;">{{ $row['exam_score'] }}</td>
                    <td style="text-align:center;font-weight:800;">{{ $row['aggregate'] }}</td>
                    <td style="text-align:center;"><span class="chip {{ $row['aggregate']>=50?'pass':'fail' }}">{{ $row['grade'] }}</span></td>
                    <td style="text-align:center;color:#64748b;">{{ $row['position'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="watermark">
        Student Performance Statement — officially issued by {{ $schoolName ?? \App\Models\Setting::get('school_name', config('app.name')) }} on {{ now()->format('d M Y') }}.
        Verify at: {{ $doc?->verify_url ?? "" }}
    </div>
</div>
</body>
</html>

