<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Results — {{ $student->user->full_name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; font-family: 'Inter', sans-serif; background: #f4f5fb; color: #111827; }
        body { padding: 24px; }
        a { color: inherit; text-decoration: none; }

        .page-shell { max-width: 1200px; margin: 0 auto; }
        .page-header { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; align-items: end; }
        .panel { background: #ffffff; border-radius: 24px; padding: 22px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08); }
        .panel h1 { font-size: 18px; font-weight: 900; margin-bottom: 8px; }
        .panel p { color: #6b7280; font-size: 13px; }
        .field-card { display: flex; flex-direction: column; gap: 10px; }
        .field-card .label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.12em; }
        .field-card .value { font-size: 22px; font-weight: 900; color: #111827; }
        .field-card .secondary { font-size: 13px; color: #4b5563; }

        .actions { display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap; }
        .button { display: inline-flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 14px; font-size: 13px; font-weight: 700; transition: transform .2s ease, box-shadow .2s ease; }
        .button:hover { transform: translateY(-1px); }
        .button.primary { background: #2563eb; color: #ffffff; }
        .button.secondary { background: #ffffff; color: #111827; border: 1px solid #d1d5db; }

        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .result-card { background: #ffffff; border-radius: 24px; padding: 24px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08); }
        .result-card .card-title { font-size: 15px; font-weight: 900; margin-bottom: 12px; }
        .result-card .meta-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
        .pill { display: inline-flex; align-items: center; justify-content: center; padding: 8px 14px; border-radius: 999px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em; }
        .pill.success { background: #dcfce7; color: #166534; }
        .pill.warning { background: #fef3c7; color: #a16109; }
        .result-table { width: 100%; border-collapse: collapse; }
        .result-table th, .result-table td { padding: 14px 16px; border-bottom: 1px solid #e5e7eb; }
        .result-table th { text-align: left; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em; color: #4b5563; }
        .result-table td { font-size: 13px; color: #111827; }
        .result-table td.course { width: 48%; }
        .result-table td.center { text-align: center; }
        .summary-panel { display: grid; gap: 18px; }
        .summary-box { background: #2563eb; color: #ffffff; border-radius: 24px; padding: 26px; position: relative; overflow: hidden; }
        .summary-box::after { content: ''; position: absolute; right: -14px; top: -14px; width: 94px; height: 94px; background: rgba(255,255,255,0.12); border-radius: 50%; }
        .summary-box h2 { font-size: 16px; font-weight: 900; margin-bottom: 14px; }
        .summary-box p { color: rgba(255,255,255,0.84); font-size: 13px; line-height: 1.6; }
        .chip { display: inline-flex; gap: 8px; align-items: center; padding: 10px 14px; border-radius: 999px; background: rgba(255,255,255,0.12); font-size: 12px; font-weight: 700; }
        .large-number { font-size: 44px; font-weight: 900; line-height: 1; }
        .stat-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .stat-card { background: #ffffff; border-radius: 18px; padding: 16px; }
        .stat-card strong { display: block; font-size: 24px; margin-bottom: 6px; }
        .stat-card span { color: #6b7280; font-size: 12px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .info-card { background: #ffffff; border-radius: 24px; padding: 22px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08); }
        .info-card h2 { font-size: 14px; font-weight: 900; margin-bottom: 14px; }
        .info-card .info-row { display: flex; justify-content: space-between; gap: 18px; margin-bottom: 14px; }
        .info-card .info-row span { font-size: 12px; color: #6b7280; }
        .info-card .info-row strong { font-size: 15px; color: #111827; }

        .empty-state { background: #ffffff; border-radius: 24px; padding: 48px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08); text-align: center; }
        .empty-state i { display: block; font-size: 38px; color: #cbd5e1; margin-bottom: 16px; }
        .empty-state p { font-size: 14px; color: #64748b; margin-top: 8px; }

        @media (max-width: 1024px) {
            .page-header { grid-template-columns: 1fr; }
            .content-grid { grid-template-columns: 1fr; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="page-header">
            <div class="panel field-card">
                <span class="label">Student ID</span>
                <span class="value">{{ $student->student_id }}</span>
                <span class="secondary">Use this to verify your result.</span>
            </div>
            <div class="panel field-card">
                <span class="label">Selected Semester</span>
                <span class="value">{{ $program->name }}</span>
                @if(isset($attempts) && $attempts->count() > 1)
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px">
                    @foreach($attempts as $att)
                        <a href="{{ route('portal.report-card', $program) }}?attempt={{ $att }}"
                           style="padding:5px 14px;border-radius:999px;font-size:11px;font-weight:700;border:1.5px solid {{ $att == $currentAttempt ? '#d97706' : '#d1d5db' }};background:{{ $att == $currentAttempt ? '#fde68a' : '#fff' }};color:{{ $att == $currentAttempt ? '#78350f' : '#6b7280' }}">
                            Attempt {{ $att }}
                        </a>
                    @endforeach
                </div>
                @elseif(isset($currentAttempt) && $currentAttempt > 1)
                <span class="secondary">Attempt {{ $currentAttempt }}</span>
                @endif
            </div>
            <div class="panel actions">
                <a href="{{ route('portal.dashboard') }}" class="button secondary"><i class="fas fa-arrow-left"></i> Dashboard</a>
                @if($has_scores)
                    <a href="{{ route('portal.report-card.pdf', $program) }}?attempt={{ $currentAttempt ?? 1 }}" target="_blank" class="button primary"><i class="fas fa-file-pdf"></i> Download PDF</a>
                @endif
            </div>
        </div>

        @if(!$has_scores)
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No scores recorded for this program yet.</p>
                <p>Please check back after results have been entered.</p>
            </div>
        @else
            <div class="content-grid">
                <div class="result-card">
                    <div class="card-title">Live Result</div>
                    <div class="meta-row">
                        <span class="pill {{ $overall_average >= 40 ? 'success' : 'warning' }}">{{ $overall_average >= 40 ? 'Passed' : 'Pending' }}</span>
                        <span class="chip"><i class="fas fa-graduation-cap"></i> {{ count($courses) }} Courses Completed</span>
                    </div>
                    <table class="result-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th class="center">Credit</th>
                                <th class="center">Grade</th>
                                <th class="center">Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $row)
                                <tr>
                                    <td>{{ strtoupper($row['course']->code ?? '—') }}</td>
                                    <td class="course">{{ $row['course']->name }}</td>
                                    <td class="center">{{ $row['credit'] }}</td>
                                    <td class="center">{{ strtoupper($row['grade']) }}</td>
                                    <td class="center">{{ number_format($row['grade_point'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="summary-panel">
                    <div class="summary-box">
                        <h2>Performance Snapshot</h2>
                        <p>Live results updated from the system grade engine.</p>
                        <div class="large-number">{{ number_format($overall_average, 2) }}%</div>
                        <div class="chip"><i class="fas fa-chart-line"></i> {{ $overall_grade_description }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-card">
                            <strong>{{ number_format($overall_sgpa, 2) }}</strong>
                            <span>SGPA</span>
                        </div>
                        <div class="stat-card">
                            <strong>{{ number_format($cgpa ?? 0, 2) }}</strong>
                            <span>CGPA</span>
                        </div>
                        <div class="stat-card">
                            <strong>{{ $total_credit_taken }}</strong>
                            <span>Total Credits</span>
                        </div>
                    </div>
                    @if(isset($overall_position) && $overall_position !== '—')
                    <div class="stat-card" style="display: flex; align-items: center; justify-content: space-between; background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 18px; padding: 16px; margin-top: -6px;">
                        <div>
                            <span style="display: block; font-size: 11px; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Overall Class Position</span>
                            <strong style="display: block; font-size: 24px; font-weight: 900; color: #78350f;">
                                {{ $overall_position }}<span style="font-size: 14px; font-weight: 700; color: #b45309; text-transform: uppercase; vertical-align: super; margin-left: 2px;">{{ is_numeric($overall_position) ? ((int)$overall_position === 1 ? 'st' : ((int)$overall_position === 2 ? 'nd' : ((int)$overall_position === 3 ? 'rd' : 'th'))) : '' }}</span>
                                <span style="font-size: 14px; font-weight: 500; color: #9a3412;">out of {{ $total_students_in_exam }}</span>
                            </strong>
                        </div>
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706; font-size: 18px;">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h2>Student Basic Info</h2>
                    <div class="info-row"><span>Name</span><strong>{{ $student->user->full_name }}</strong></div>
                    <div class="info-row"><span>Department</span><strong>{{ $program->name }}</strong></div>
                    <div class="info-row"><span>Student ID</span><strong>{{ $student->student_id }}</strong></div>
                    <div class="info-row"><span>Enrollment</span><strong>{{ $student->enrollment_year ?? date('Y') }}</strong></div>
                    <div class="info-row"><span>Campus</span><strong>{{ $student->churchBranch->name ?? '—' }}</strong></div>
                </div>
                <div class="info-card">
                    <h2>Your Overall Performance This Semester</h2>
                    <div class="info-row"><span>Average Marks</span><strong>{{ number_format($overall_average, 2) }}%</strong></div>
                    <div class="info-row"><span>Grade</span><strong>{{ $overall_grade }}</strong></div>
                    @if(isset($overall_position) && $overall_position !== '—')
                    <div class="info-row" style="background: #fffbeb; padding: 8px 10px; border-radius: 8px; margin: 4px 0;">
                        <span style="color: #b45309; font-weight: 700;">Overall Position</span>
                        <strong style="color: #92400e;">{{ $overall_position }} / {{ $total_students_in_exam }}</strong>
                    </div>
                    @endif
                    <div class="info-row"><span>Remarks</span><strong>{{ $remarks }}</strong></div>
                    <div class="info-row"><span>Result</span><strong>{{ $result }}</strong></div>
                    <div class="info-row"><span>Credit Taken</span><strong>{{ $total_credit_taken }}</strong></div>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
