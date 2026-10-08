<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e1b4b; }
    .page { padding: 20px 24px; position:relative; }
    .school-header { text-align:center; border-bottom:3px solid #1e1b4b; padding-bottom:10px; margin-bottom:14px; }
    .school-name { font-size:16px; font-weight:800; color:#1e1b4b; }
    .doc-title { font-size:12px; font-weight:700; color:#5647d6; margin-top:3px; text-transform:uppercase; letter-spacing:.06em; }
    .school-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .student-info { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:8px 12px; margin-bottom:14px; display:table; width:100%; }
    .si-cell { display:table-cell; padding-right:20px; }
    .si-label { font-size:8px; font-weight:700; text-transform:uppercase; color:#64748b; }
    .si-val { font-size:12px; font-weight:800; color:#1e1b4b; margin-top:1px; }
    .prog-block { margin-bottom:12px; }
    .prog-header { background:#1e1b4b; color:#fff; padding:5px 10px; font-size:10px; font-weight:700; display:table; width:100%; }
    .prog-meta { display:table-cell; }
    .prog-result { display:table-cell; text-align:right; font-size:9px; }
    table.scores { width:100%; border-collapse:collapse; }
    table.scores th { background:#f1f5f9; color:#64748b; font-size:8.5px; font-weight:700; text-transform:uppercase; padding:4px 7px; border-bottom:1px solid #e2e8f0; }
    table.scores th.left { text-align:left; }
    table.scores td { padding:3.5px 7px; border-bottom:1px solid #f8fafc; font-size:9.5px; }
    table.scores tr:last-child td { border-bottom:none; }
    .chip { display:inline-block; padding:1px 5px; border-radius:999px; font-size:8px; font-weight:800; }
    .pass { background:#dcfce7; color:#166534; }
    .fail { background:#fee2e2; color:#991b1b; }
    .qr-block { position:absolute; top:20px; right:22px; text-align:center; }
    .qr-block img { width:65px; height:65px; }
    .qr-text { font-size:7px; color:#94a3b8; margin-top:2px; }
    .watermark { font-size:9px; color:#94a3b8; text-align:center; margin-top:10px; border-top:1px solid #e2e8f0; padding-top:8px; }
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
        <div class="doc-title">Academic Transcript / Statement of Results</div>
        <div class="school-sub">Ref: {{ $doc?->uuid ?? "N/A" }} &bull; Issued: {{ now()->format('d M Y') }}</div>
    </div>

    <div class="student-info">
        <div class="si-cell">
            <div class="si-label">Full Name</div>
            <div class="si-val">{{ $student->user->full_name }}</div>
        </div>
        <div class="si-cell">
            <div class="si-label">Admission No.</div>
            <div class="si-val">{{ $student->student_id }}</div>
        </div>
        <div class="si-cell">
            <div class="si-label">Admission Date</div>
            <div class="si-val">{{ $student->admission_date?->format('d M Y') ?? '—' }}</div>
        </div>
        <div class="si-cell">
            <div class="si-label">Current Program</div>
            <div class="si-val">{{ $student->program?->name ?? '—' }}</div>
        </div>
    </div>

    @if(empty($data['history']))
    <div style="text-align:center;padding:20px;color:#94a3b8;">No academic records found.</div>
    @else
    @foreach($data['history'] as $entry)
    @php $card = $entry['card']; @endphp
    <div class="prog-block">
        <div class="prog-header">
            <div style="display:table;width:100%;">
                <div style="display:table-cell;">{{ $entry['program']->name }} — Attempt {{ $entry['attempt'] }}</div>
                <div style="display:table-cell;text-align:right;font-size:9px;opacity:.8;">
                    Avg: {{ $card['overall_average'] }}% &nbsp;|&nbsp;
                    Grade: {{ $card['overall_grade'] }} &nbsp;|&nbsp;
                    <span style="color:{{ $card['result']==='PASS'?'#6ee7b7':'#fca5a5' }}">{{ $card['result'] }}</span>
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
                @foreach($card['courses'] as $row)
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
    @endif

    <div class="watermark">
        This transcript was officially generated by {{ $schoolName ?? \App\Models\Setting::get('school_name', config('app.name')) }} on {{ now()->format('d M Y') }}.
        Verify at: {{ $doc?->verify_url ?? "" }}
    </div>
</div>
</body>
</html>

