<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order of Merit</title>
<style>
@page { margin: 12mm; size: A4 landscape; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10pt;
    color: #111;
    background: #fff;
}
.page-wrapper {
    width: 100%;
    padding: 10pt;
}
.header {
    width: 100%;
    border-bottom: 2pt solid #1F4E79;
    padding-bottom: 10pt;
    margin-bottom: 12pt;
}
.branding {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12pt;
}
.branding .title {
    text-transform: uppercase;
}
.college-name {
    font-size: 14pt;
    font-weight: 800;
    color: #1F4E79;
}
.subtitle {
    font-size: 9.5pt;
    font-weight: 700;
    color: #333;
    margin-top: 4pt;
}
.context-details {
    margin-top: 10pt;
    display: flex;
    gap: 8pt;
    flex-wrap: wrap;
}
.context-card {
    background: #f5f8ff;
    border: 1pt solid #d7e3f2;
    padding: 8pt 10pt;
    border-radius: 6pt;
    min-width: 180pt;
}
.context-card strong {
    display: block;
    font-size: 8.5pt;
    color: #4b5563;
    text-transform: uppercase;
    margin-bottom: 3pt;
}
.context-card span {
    font-size: 10pt;
    color: #111827;
}
.table-section {
    margin-top: 14pt;
}
.table-title {
    font-size: 11pt;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 8pt;
    color: #1F4E79;
}
.merit-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12pt;
}
.merit-table th,
.merit-table td {
    border: 1pt solid #d1d5db;
    padding: 6pt 8pt;
    text-align: center;
    vertical-align: middle;
}
.merit-table th {
    background: #1F4E79;
    color: #ffffff;
    font-size: 9pt;
    font-weight: 700;
    text-transform: uppercase;
}
.merit-table td {
    font-size: 9pt;
    color: #111827;
}
.merit-table td.name-cell {
    text-align: left;
    font-weight: 700;
    color: #0f172a;
}
.footer-note {
    margin-top: 8pt;
    font-size: 8.5pt;
    color: #4b5563;
}
</style>
</head>
<body>
<div class="page-wrapper">
    <div class="header">
        <div class="branding">
            <div class="title">
                <div class="college-name">{{ $schoolName ?? 'School Name' }}</div>
                <div class="subtitle">Order of Merit · {{ strtoupper($program->name ?? 'Program') }} · {{ strtoupper($branchName) }}</div>
            </div>
            <div style="text-align:right; font-size: 9pt; color: #4b5563;">
                Generated: {{ $generatedAt }}
            </div>
        </div>
        <div class="context-details">
            <div class="context-card">
                <strong>Branch</strong>
                <span>{{ $branchName }}</span>
            </div>
            <div class="context-card">
                <strong>Attempt</strong>
                <span>{{ $attemptLabel }}</span>
            </div>
            <div class="context-card">
                <strong>Student count</strong>
                <span>{{ number_format($students->count()) }}</span>
            </div>
        </div>
    </div>

    <div class="table-section">
        <div class="table-title">Program Exam Performance</div>
        <table class="merit-table">
            <thead>
                <tr>
                    <th style="width: 6%;">Pos</th>
                    <th style="width: 28%; text-align:left;">Student Name</th>
                    @php $colWidth = $courses->count() > 0 ? round(66 / $courses->count(), 1) : 10; @endphp
                    @foreach($courses as $course)
                        <th style="width: {{ $colWidth }}%;">{{ $course->name }}</th>
                    @endforeach
                    <th style="width: 10%;">Total</th>
                    <th style="width: 10%;">Average</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['position'] }}</td>
                        <td class="name-cell">{{ $row['name'] }}</td>
                        @foreach($row['course_scores'] as $score)
                            <td style="padding: 5pt 6pt;">
                                <div>{{ $score['score'] }}</div>
                                @if(!empty($score['grade']))
                                    <div style="font-size:8pt;color:#334155;margin-top:3pt;">{{ $score['grade'] }}</div>
                                @endif
                            </td>
                        @endforeach
                        <td>{{ number_format($row['total'], 2) }}</td>
                        <td>{{ number_format($row['average'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer-note">
        This Order of Merit sheet reflects exam attempt scores for the selected program and branch. Student registration numbers are intentionally omitted for privacy.
    </div>
</div>
</body>
</html>
