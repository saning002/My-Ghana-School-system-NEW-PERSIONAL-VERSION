<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 8.5pt; color: #1a1a2e; }
  .page-header { text-align:center; padding:10pt 0 6pt; border-bottom:2pt solid #0a1f44; margin-bottom:8pt; }
  .page-header h1 { font-size:13pt; font-weight:bold; color:#0a1f44; }
  .page-header h2 { font-size:10pt; color:#374151; margin-top:3pt; }
  .meta { font-size:7.5pt; color:#6b7280; margin-top:2pt; }
  table { width:100%; border-collapse:collapse; margin-bottom:10pt; }
  thead tr { background:#0a1f44; color:#fff; }
  thead th { padding:5pt 4pt; text-align:center; font-size:7.5pt; border:1pt solid #162d60; }
  thead th.left { text-align:left; }
  tbody tr:nth-child(even) { background:#f0f4ff; }
  tbody tr:nth-child(odd)  { background:#ffffff; }
  tbody td { padding:4pt 3pt; border:0.5pt solid #e0e0e0; text-align:center; font-size:8pt; }
  tbody td.left { text-align:left; }
  .sub-col { background:#f0fdf4; }
  .class-col { background:#eff6ff; font-weight:bold; }
  .exam-col { background:#fff7ed; }
  .agg-col { background:#fefce8; font-weight:bold; }
  .grade-col { font-weight:bold; }
  tfoot td { padding:5pt; background:#f9fafb; font-weight:bold; font-size:8pt; border-top:1.5pt solid #374151; }
  .pos { color:#0a1f44; font-weight:bold; }
  .footer { font-size:7pt; color:#9ca3af; text-align:right; margin-top:6pt; }
</style>
</head>
<body>

<div class="page-header">
  <h1>{{ strtoupper($program->name ?? '') }} — SBA SCORES SHEET</h1>
  <h2>
    Attempt {{ $attempt }}
    @if($include_exam)
      &nbsp;|&nbsp; Includes Exam Scores & Aggregate
    @endif
  </h2>
  <div class="meta">
    Generated: {{ $generated_at }} &nbsp;|&nbsp; Total Sub-Weight: {{ $total_sub_w }} &nbsp;|&nbsp; SBA: {{ $sba_pct }}% &nbsp;|&nbsp; Exam: {{ $exam_pct }}%
  </div>
</div>

<table>
  <thead>
    <tr>
      <th class="left" style="width:3%">#</th>
      <th class="left" style="width:15%">Student</th>
      <th class="left" style="width:7%">ID</th>
      @foreach($courses as $course)
        @if($split_sba)
          <th class="sub-col" colspan="{{ $include_exam ? 8 : 5 }}">{{ $course->name }} ({{ $course->code }})</th>
        @else
          <th class="class-col" colspan="{{ $include_exam ? 4 : 1 }}">{{ $course->name }} ({{ $course->code }})</th>
        @endif
      @endforeach
      @if($include_exam)
        <th style="width:6%">Total Agg</th>
        <th style="width:5%">Avg</th>
        <th style="width:4%">Pos</th>
      @endif
    </tr>
    <tr>
      <th></th>
      <th class="left">Name</th>
      <th class="left">Student ID</th>
      @foreach($courses as $course)
        @if($split_sba)
          <th class="sub-col">{{ $labels['test1'] ?? 'Test 1' }}<br><small>/{{ $sub_weights['test1'] ?? 25 }}</small></th>
          <th class="sub-col">{{ $labels['groupwork'] ?? 'Group Work' }}<br><small>/{{ $sub_weights['groupwork'] ?? 25 }}</small></th>
          <th class="sub-col">{{ $labels['test2'] ?? 'Test 2' }}<br><small>/{{ $sub_weights['test2'] ?? 25 }}</small></th>
          <th class="sub-col">{{ $labels['project'] ?? 'Project Work' }}<br><small>/{{ $sub_weights['project'] ?? 25 }}</small></th>
        @endif
        <th class="class-col">Class Score<br><small>({{ $sba_pct }}%)</small></th>
        @if($include_exam)
          <th class="exam-col">Exam<br><small>({{ $exam_pct }}%)</small></th>
          <th class="agg-col">Aggregate</th>
          <th class="grade-col">Grade</th>
        @endif
      @endforeach
      @if($include_exam)
        <th></th>
        <th></th>
        <th></th>
      @endif
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $idx => $row)
    <tr>
      <td>{{ $idx + 1 }}</td>
      <td class="left">{{ $row['name'] }}</td>
      <td class="left">{{ $row['student_id'] }}</td>
      @foreach($row['courses'] as $c)
        @if($split_sba)
          <td class="sub-col">{{ $c['test1'] !== null ? number_format((float)$c['test1'],1) : '—' }}</td>
          <td class="sub-col">{{ $c['groupwork'] !== null ? number_format((float)$c['groupwork'],1) : '—' }}</td>
          <td class="sub-col">{{ $c['test2'] !== null ? number_format((float)$c['test2'],1) : '—' }}</td>
          <td class="sub-col">{{ $c['project'] !== null ? number_format((float)$c['project'],1) : '—' }}</td>
        @endif
        <td class="class-col">{{ $c['class_score'] > 0 ? number_format((float)$c['class_score'],2) : '—' }}</td>
        @if($include_exam)
          <td class="exam-col">{{ $c['exam_score'] !== null ? number_format((float)$c['exam_score'],1) : '—' }}</td>
          <td class="agg-col">{{ $c['aggregate'] !== null ? number_format((float)$c['aggregate'],2) : '—' }}</td>
          <td class="grade-col">{{ $c['grade'] }}</td>
        @endif
      @endforeach
      @if($include_exam)
        <td>{{ number_format((float)$row['total_agg'],2) }}</td>
        <td>{{ number_format((float)$row['avg'],2) }}</td>
        <td class="pos">{{ $row['position'] }}</td>
      @endif
    </tr>
    @endforeach
  </tbody>
</table>

<div class="footer">{{ $program->name ?? '' }} — Attempt {{ $attempt }} — {{ $generated_at }}</div>
</body>
</html>
