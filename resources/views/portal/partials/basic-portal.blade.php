@php
  $studentName = $student->user->full_name ?? 'Student';
  $firstName   = explode(' ', $studentName)[0];
  $programName = $student->program?->name ?? '—';
  $branchName  = $student->churchBranch?->name ?? '';

  // Calculate scores
  $totalCourses = $student->enrollments->count();
  $hasRecentScores = !empty($reportCardPreview['has_scores']);
  $overallAvg = $hasRecentScores ? ($reportCardPreview['overall_average'] ?? 0) : 0;
  $overallGrade = $hasRecentScores ? ($reportCardPreview['overall_grade_description'] ?? '—') : '—';
  $position = $hasRecentScores ? ($reportCardPreview['overall_position'] ?? '—') : '—';

  // Attendance
  $attRecords = $student->attendances;
  $totalAtt = $attRecords->count();
  $presentAtt = $attRecords->where('status', 'present')->count();
  $attRate = $totalAtt > 0 ? round(($presentAtt / $totalAtt) * 100) : 0;

  // Fees
  $balance = $fees['balance'] ?? 0;
  $totalPaid = $fees['total_paid'] ?? 0;
  $totalBilled = $fees['total_billed'] ?? 0;

  // Mode-specific labels
  $modeColors = [
    'basic' => ['primary' => '#f59e0b', 'light' => '#fef3c7', 'dark' => '#92400e', 'bg' => '#fffbeb'],
    'jhs'   => ['primary' => '#3b82f6', 'light' => '#dbeafe', 'dark' => '#1e3a8a', 'bg' => '#eff6ff'],
    'shs'   => ['primary' => '#7c3aed', 'light' => '#e9d5ff', 'dark' => '#4c1d95', 'bg' => '#f5f3ff'],
  ];
  $mc = $modeColors[$portalMode] ?? $modeColors['basic'];

  // Grade colors for badges
  function gradeColor($score) {
    if ($score >= 80) return ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Excellent'];
    if ($score >= 70) return ['bg' => '#d1fae5', 'text' => '#059669', 'label' => 'Very Good'];
    if ($score >= 60) return ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'Good'];
    if ($score >= 50) return ['bg' => '#fef3c7', 'text' => '#a16207', 'label' => 'Credit'];
    if ($score >= 40) return ['bg' => '#ffedd5', 'text' => '#c2410c', 'label' => 'Pass'];
    return ['bg' => '#fee2e2', 'text' => '#dc2626', 'label' => 'Needs Help'];
  }
@endphp

{{-- ══ NEW STYLES FOR BASIC/JHS/SHS PORTAL ══ --}}
<style>
  .bp-hero {
    background:linear-gradient(135deg, {{ $mc['primary'] }} 0%, {{ $mc['dark'] }} 100%);
    border-radius:20px; padding:24px; color:#fff; margin-bottom:20px;
    display:flex; align-items:center; gap:16px; position:relative; overflow:hidden;
  }
  .bp-hero::after {
    content:''; position:absolute; right:-30px; top:-30px;
    width:140px; height:140px; background:rgba(255,255,255,.08); border-radius:50%;
  }
  .bp-avatar {
    width:56px; height:56px; border-radius:16px; background:rgba(255,255,255,.2);
    display:flex; align-items:center; justify-content:center;
    font-size:24px; font-weight:900; flex-shrink:0; border:2px solid rgba(255,255,255,.3);
  }
  .bp-stat-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:12px; margin-bottom:20px;
  }
  .bp-stat {
    background:var(--surface); border-radius:16px; padding:16px; text-align:center;
    border:2px solid var(--border); transition:all .2s;
  }
  .bp-stat:hover { border-color:{{ $mc['primary'] }}; transform:translateY(-2px); }
  .bp-stat-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; font-size:14px; }
  .bp-stat-val { font-size:22px; font-weight:900; color:var(--text); line-height:1; }
  .bp-stat-lbl { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin-top:4px; }

  .bp-card {
    background:var(--surface); border-radius:16px; padding:18px;
    border:2px solid var(--border); margin-bottom:16px;
  }
  .bp-card-title { font-size:14px; font-weight:800; color:var(--text); margin-bottom:12px; display:flex; align-items:center; gap:8px; }

  .bp-subject-row {
    display:flex; align-items:center; gap:12px; padding:12px 0;
    border-bottom:1px solid var(--border); flex-wrap:wrap;
  }
  .bp-subject-row:last-child { border-bottom:none; }
  .bp-subject-name { flex:1; min-width:120px; font-size:13px; font-weight:600; color:var(--text); }
  .bp-score-pill {
    display:inline-flex; align-items:center; gap:4px; padding:4px 10px;
    border-radius:20px; font-size:12px; font-weight:800;
  }
  .bp-progress-bar {
    width:100%; height:8px; background:var(--border); border-radius:10px; overflow:hidden; margin-top:4px;
  }
  .bp-progress-fill { height:100%; border-radius:10px; transition:width .3s; }

  .bp-att-circle {
    width:100px; height:100px; border-radius:50%; margin:0 auto 12px;
    background:conic-gradient({{ $mc['primary'] }} {{ $attRate }}%, var(--border) 0);
    display:grid; place-items:center; position:relative;
  }
  .bp-att-inner { width:84px; height:84px; border-radius:50%; background:var(--surface); display:grid; place-items:center; }
  .bp-att-num { font-size:24px; font-weight:900; color:{{ $mc['primary'] }}; }
  .bp-att-lbl { font-size:10px; color:var(--text-muted); }

  .bp-fee-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border); }
  .bp-fee-row:last-child { border-bottom:none; }
  .bp-fee-lbl { font-size:12px; color:var(--text-muted); }
  .bp-fee-val { font-size:13px; font-weight:700; color:var(--text); }

  .bp-notif {
    display:flex; gap:10px; padding:12px; border-radius:12px; background:var(--surface2);
    border:1px solid var(--border); margin-bottom:8px; align-items:flex-start;
  }
  .bp-notif-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .bp-notif-title { font-size:13px; font-weight:700; color:var(--text); }
  .bp-notif-msg { font-size:11px; color:var(--text-muted); margin-top:2px; line-height:1.4; }
  .bp-notif-time { font-size:10px; color:var(--text-muted); margin-top:4px; }
</style>

<!-- DASHBOARD PAGE -->
<div id="page-dashboard" class="page-content active">
  <div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">{{ $modeLabel }}</p>
  </div>

  {{-- Hero --}}
  <div class="bp-hero">
    <div class="bp-avatar">{{ strtoupper(substr($studentName, 0, 1)) }}</div>
    <div style="position:relative;z-index:1;">
      <div style="font-size:18px; font-weight:900;">Welcome, {{ $firstName }}!</div>
      <div style="font-size:12px; opacity:.8; margin-top:2px;">
        {{ $programName }}{{ $branchName ? ' • ' . $branchName : '' }}
      </div>
    </div>
  </div>

  {{-- Stat Cards --}}
  <div class="bp-stat-grid">
    <div class="bp-stat">
      <div class="bp-stat-icon" style="background:{{ $mc['light'] }};color:{{ $mc['dark'] }};">
        <i class="fas fa-chart-bar"></i>
      </div>
      <div class="bp-stat-val">{{ round($overallAvg) }}%</div>
      <div class="bp-stat-lbl">{{ $portalMode === 'basic' ? 'Overall Score' : 'Term Average' }}</div>
    </div>
    <div class="bp-stat">
      <div class="bp-stat-icon" style="background:#dcfce7;color:#16a34a;">
        <i class="fas fa-calendar-check"></i>
      </div>
      <div class="bp-stat-val">{{ $attRate }}%</div>
      <div class="bp-stat-lbl">Attendance</div>
    </div>
    <div class="bp-stat">
      <div class="bp-stat-icon" style="background:#dbeafe;color:#2563eb;">
        <i class="fas fa-book-open"></i>
      </div>
      <div class="bp-stat-val">{{ $totalCourses }}</div>
      <div class="bp-stat-lbl">{{ $portalMode === 'basic' ? 'Subjects' : 'Courses' }}</div>
    </div>
    <div class="bp-stat">
      <div class="bp-stat-icon" style="background:#f3e8ff;color:#7c3aed;">
        <i class="fas fa-trophy"></i>
      </div>
      <div class="bp-stat-val">{{ $position !== '—' ? $position : '—' }}</div>
      <div class="bp-stat-lbl">Class Position</div>
    </div>
  </div>

  {{-- Latest Result Preview --}}
  @if($hasRecentScores && $reportCardPreview && !empty($reportCardPreview['courses']))
  <div class="bp-card">
    <div class="bp-card-title">
      <i class="fas fa-award" style="color:{{ $mc['primary'] }};"></i>
      Latest Results — {{ $reportCardPreview['program']->name ?? 'Current Term' }}
    </div>
    @php
      $courses = collect($reportCardPreview['courses'])->take(8);
    @endphp
    @foreach($courses as $row)
      @php
        $score = $row['total_score'] ?? 0;
        $gc = gradeColor($score);
        $grade = $row['grade'] ?? '—';
      @endphp
      <div class="bp-subject-row">
        <div class="bp-subject-name">{{ $row['course']->name ?? '—' }}</div>
        @if($portalMode !== 'basic')
        <span style="font-size:11px;color:var(--text-muted);">Class: {{ $row['class_score'] ?? '—' }}</span>
        <span style="font-size:11px;color:var(--text-muted);">Exam: {{ $row['exam_score'] ?? '—' }}</span>
        @endif
        <span class="bp-score-pill" style="background:{{ $gc['bg'] }};color:{{ $gc['text'] }};">
          {{ $score }}%
        </span>
        <span class="bp-score-pill" style="background:var(--border);color:var(--text-muted);font-size:11px;">
          {{ $grade }}
        </span>
      </div>
      <div class="bp-progress-bar">
        <div class="bp-progress-fill" style="width:{{ min($score, 100) }}%;background:{{ $mc['primary'] }};"></div>
      </div>
    @endforeach
    <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
      <a href="{{ route('portal.report-card', $reportCardPreview['program'] ?? $programs->first()) }}?attempt={{ $previewAttempt }}"
         class="button button-secondary" style="font-size:12px;padding:8px 16px;">View Full Report</a>
      @if($reportCardPreview['has_scores'])
      <a href="{{ route('portal.report-card.pdf', $reportCardPreview['program'] ?? $programs->first()) }}?attempt={{ $previewAttempt }}"
         target="_blank" class="button button-primary" style="font-size:12px;padding:8px 16px;">
         <i class="fas fa-file-pdf"></i> Download PDF
      </a>
      @endif
    </div>
  </div>
  @endif

  {{-- Attendance Summary --}}
  <div class="bp-card">
    <div class="bp-card-title">
      <i class="fas fa-calendar-check" style="color:#16a34a;"></i>
      Attendance Overview
    </div>
    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
      <div class="bp-att-circle">
        <div class="bp-att-inner">
          <div style="text-align:center;">
            <div class="bp-att-num">{{ $attRate }}%</div>
            <div class="bp-att-lbl">Present</div>
          </div>
        </div>
      </div>
      <div style="flex:1;min-width:150px;">
        <div class="bp-fee-row">
          <span class="bp-fee-lbl">Days Present</span>
          <span class="bp-fee-val" style="color:#16a34a;">{{ $presentAtt }}</span>
        </div>
        <div class="bp-fee-row">
          <span class="bp-fee-lbl">Days Absent</span>
          <span class="bp-fee-val" style="color:#dc2626;">{{ $totalAtt - $presentAtt }}</span>
        </div>
        <div class="bp-fee-row">
          <span class="bp-fee-lbl">Total Days</span>
          <span class="bp-fee-val">{{ $totalAtt }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Fee Summary --}}
  <div class="bp-card">
    <div class="bp-card-title">
      <i class="fas fa-coins" style="color:#f59e0b;"></i>
      Fee Summary
    </div>
    <div class="bp-fee-row">
      <span class="bp-fee-lbl">Total Billed</span>
      <span class="bp-fee-val">GH₵ {{ number_format($totalBilled, 2) }}</span>
    </div>
    <div class="bp-fee-row">
      <span class="bp-fee-lbl">Total Paid</span>
      <span class="bp-fee-val" style="color:#16a34a;">GH₵ {{ number_format($totalPaid, 2) }}</span>
    </div>
    <div class="bp-fee-row">
      <span class="bp-fee-lbl">Outstanding Balance</span>
      <span class="bp-fee-val" style="color:{{ $balance > 0 ? '#dc2626' : '#16a34a' }};">
        GH₵ {{ number_format($balance, 2) }}
      </span>
    </div>
  </div>
</div>

<!-- RESULTS PAGE -->
<div id="page-results" class="page-content">
  <div class="page-header">
    <h1 class="page-title">My Results</h1>
    <p class="page-subtitle">{{ $portalMode === 'basic' ? 'Your subjects and scores' : 'Examination results and report cards' }}</p>
  </div>

  @if($student->results_blocked)
  <div class="bp-card" style="text-align:center;background:#fee2e2;border-color:#fca5a5;">
    <i class="fas fa-lock" style="font-size:32px;color:#dc2626;margin-bottom:12px;"></i>
    <h3 style="font-size:16px;font-weight:800;color:#dc2626;">Results Locked</h3>
    <p style="font-size:12px;color:#991b1b;margin-top:6px;">
      Please settle your outstanding fees at the admin office to unlock your results.
    </p>
  </div>
  @else
    @if($reportCardPreview)
    <div class="bp-card" style="background:{{ $mc['light'] }};border-color:{{ $mc['primary'] }};">
      <div class="bp-card-title">
        <i class="fas fa-star" style="color:{{ $mc['dark'] }};"></i>
        Most Recent Report
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px;">
        <div style="background:rgba(255,255,255,.5);padding:12px;border-radius:10px;text-align:center;">
          <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:{{ $mc['dark'] }};">Average</div>
          <div style="font-size:18px;font-weight:900;color:{{ $mc['dark'] }};">{{ round($overallAvg) }}%</div>
        </div>
        <div style="background:rgba(255,255,255,.5);padding:12px;border-radius:10px;text-align:center;">
          <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:{{ $mc['dark'] }};">Grade</div>
          <div style="font-size:18px;font-weight:900;color:{{ $mc['dark'] }};">{{ $overallGrade }}</div>
        </div>
        <div style="background:rgba(255,255,255,.5);padding:12px;border-radius:10px;text-align:center;">
          <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:{{ $mc['dark'] }};">Position</div>
          <div style="font-size:18px;font-weight:900;color:{{ $mc['dark'] }};">
            {{ $position }}{{ is_numeric($position) ? (intval($position) === 1 ? 'st' : (intval($position) === 2 ? 'nd' : (intval($position) === 3 ? 'rd' : 'th'))) : '' }}
          </div>
        </div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('portal.report-card', $reportCardPreview['program'] ?? $programs->first()) }}?attempt={{ $previewAttempt }}"
           class="button button-secondary" style="font-size:12px;padding:10px 18px;">View Full Report</a>
        @if($reportCardPreview['has_scores'])
        <a href="{{ route('portal.report-card.pdf', $reportCardPreview['program'] ?? $programs->first()) }}?attempt={{ $previewAttempt }}"
           target="_blank" class="button button-primary" style="font-size:12px;padding:10px 18px;">
           <i class="fas fa-file-pdf"></i> Download PDF</a>
        @endif
      </div>
    </div>
    @endif

    @if($programs->isEmpty())
    <div class="bp-card" style="text-align:center;padding:40px;">
      <i class="fas fa-file-alt" style="font-size:36px;color:var(--text-muted);opacity:.4;margin-bottom:12px;"></i>
      <p style="color:var(--text-muted);font-size:13px;">No report cards yet.</p>
    </div>
    @else
      @foreach($programs as $program)
        @php
          $hasScores = $student->examScores->where('program_id', $program->id)->isNotEmpty();
          $latestAttempt = $student->examScores->where('program_id', $program->id)->max('attempt') ?? 1;
        @endphp
        <div class="bp-card">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <div>
              <h3 style="font-size:15px;font-weight:800;color:var(--text);">{{ $program->name }}</h3>
              <p style="font-size:11px;color:var(--text-muted);margin-top:2px;">Term {{ $program->sequence }}</p>
            </div>
            <span class="bp-score-pill" style="background:{{ $hasScores ? '#dcfce7' : '#fef3c7' }};color:{{ $hasScores ? '#16a34a' : '#92400e' }};">
              {{ $hasScores ? 'Ready' : 'Pending' }}
            </span>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('portal.report-card', $program) }}?attempt={{ $latestAttempt }}"
               class="button button-secondary" style="font-size:12px;padding:8px 16px;">View Report</a>
            @if($hasScores)
            <a href="{{ route('portal.report-card.pdf', $program) }}?attempt={{ $latestAttempt }}"
               target="_blank" class="button button-primary" style="font-size:12px;padding:8px 16px;">
               <i class="fas fa-file-pdf"></i> PDF</a>
            @endif
          </div>
        </div>
      @endforeach
    @endif
  @endif
</div>

<!-- ATTENDANCE PAGE -->
<div id="page-attendance" class="page-content">
  <div class="page-header">
    <h1 class="page-title">Attendance</h1>
    <p class="page-subtitle">Your class attendance records</p>
  </div>

  @if($attendanceSummary->isEmpty())
  <div class="bp-card" style="text-align:center;padding:40px;">
    <i class="fas fa-calendar-times" style="font-size:36px;color:var(--text-muted);opacity:.4;margin-bottom:12px;"></i>
    <p style="color:var(--text-muted);font-size:13px;">No attendance records yet.</p>
  </div>
  @else
  @foreach($attendanceSummary as $summary)
    <div class="bp-card">
      <div class="bp-card-title">
        <i class="fas fa-book" style="color:{{ $mc['primary'] }};"></i>
        {{ $summary['course'] ?? 'Unknown' }}
        <span style="font-size:10px;color:var(--text-muted);margin-left:auto;">{{ $summary['code'] ?? '' }}</span>
      </div>
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <div style="flex:1;min-width:100px;">
          <div class="bp-progress-bar" style="height:10px;">
            <div class="bp-progress-fill" style="width:{{ $summary['percentage'] }}%;background:{{ $summary['percentage'] >= 75 ? '#16a34a' : ($summary['percentage'] >= 50 ? '#f59e0b' : '#dc2626') }};"></div>
          </div>
        </div>
        <span class="bp-score-pill" style="background:{{ $summary['percentage'] >= 75 ? '#dcfce7' : ($summary['percentage'] >= 50 ? '#fef3c7' : '#fee2e2' }};color:{{ $summary['percentage'] >= 75 ? '#16a34a' : ($summary['percentage'] >= 50 ? '#92400e' : '#dc2626') }};">
          {{ $summary['percentage'] }}%
        </span>
      </div>
      <div style="display:flex;gap:16px;margin-top:10px;">
        <span style="font-size:11px;color:#16a34a;font-weight:700;"><i class="fas fa-check"></i> {{ $summary['present'] }} present</span>
        <span style="font-size:11px;color:#dc2626;font-weight:700;"><i class="fas fa-times"></i> {{ $summary['absent'] }} absent</span>
        <span style="font-size:11px;color:var(--text-muted);">{{ $summary['total'] }} total</span>
      </div>
    </div>
  @endforeach
  @endif
</div>

<!-- NOTIFICATIONS PAGE -->
<div id="page-notifications" class="page-content">
  <div class="page-header">
    <h1 class="page-title">Notifications</h1>
    <p class="page-subtitle">Messages from your school</p>
  </div>

  @if($notifications->isEmpty())
  <div class="bp-card" style="text-align:center;padding:40px;">
    <i class="fas fa-bell-slash" style="font-size:36px;color:var(--text-muted);opacity:.4;margin-bottom:12px;"></i>
    <p style="color:var(--text-muted);font-size:13px;">No notifications yet.</p>
  </div>
  @else
  @foreach($notifications as $notif)
    @php
      $icons = [
        'general'    => ['icon' => 'fa-bullhorn',          'bg' => '#ede9fe', 'color' => '#7c3aed'],
        'fee'        => ['icon' => 'fa-coins',             'bg' => '#fef3c7', 'color' => '#d97706'],
        'emergency'  => ['icon' => 'fa-exclamation-circle', 'bg' => '#fee2e2', 'color' => '#dc2626'],
        'result'     => ['icon' => 'fa-file-alt',          'bg' => '#d1fae5', 'color' => '#059669'],
        'attendance' => ['icon' => 'fa-clipboard-check',    'bg' => '#cffafe', 'color' => '#0891b2'],
      ];
      $ic = $icons[$notif->type] ?? ['icon' => 'fa-bell', 'bg' => '#f3f4f6', 'color' => '#6b7280'];
    @endphp
    <div class="bp-notif">
      <div class="bp-notif-icon" style="background:{{ $ic['bg'] }};">
        <i class="fas {{ $ic['icon'] }}" style="color:{{ $ic['color'] }};"></i>
      </div>
      <div style="flex:1;min-width:0;">
        <div class="bp-notif-title">{{ $notif->title }}</div>
        <div class="bp-notif-msg">{{ $notif->message }}</div>
        <div class="bp-notif-time">{{ $notif->created_at->diffForHumans() }}</div>
      </div>
    </div>
  @endforeach
  @endif
</div>

<!-- FEES PAGE -->
<div id="page-fees" class="page-content">
  <div class="page-header">
    <h1 class="page-title">Fees</h1>
    <p class="page-subtitle">Your fee statement</p>
  </div>

  <div class="bp-card">
    <div class="bp-card-title">
      <i class="fas fa-file-invoice-dollar" style="color:#f59e0b;"></i>
      Fee Summary
    </div>
    <div class="bp-fee-row">
      <span class="bp-fee-lbl">Total Billed</span>
      <span class="bp-fee-val">GH₵ {{ number_format($totalBilled, 2) }}</span>
    </div>
    <div class="bp-fee-row">
      <span class="bp-fee-lbl">Total Paid</span>
      <span class="bp-fee-val" style="color:#16a34a;">GH₵ {{ number_format($totalPaid, 2) }}</span>
    </div>
    <div class="bp-fee-row">
      <span class="bp-fee-lbl">Outstanding Balance</span>
      <span class="bp-fee-val" style="color:{{ $balance > 0 ? '#dc2626' : '#16a34a' }};">
        GH₵ {{ number_format($balance, 2) }}
      </span>
    </div>
    @if($balance > 0)
    <div style="margin-top:14px;padding:12px;background:#fef3c7;border-radius:10px;font-size:12px;color:#92400e;text-align:center;">
      <i class="fas fa-info-circle"></i> Please visit the admin office to settle your outstanding balance.
    </div>
    @else
    <div style="margin-top:14px;padding:12px;background:#dcfce7;border-radius:10px;font-size:12px;color:#166534;text-align:center;">
      <i class="fas fa-check-circle"></i> Your fees are fully settled. Well done!
    </div>
    @endif
  </div>
</div>

<!-- HELP PAGE -->
<div id="page-help" class="page-content">
  <div class="page-header">
    <h1 class="page-title">Help & Guide</h1>
    <p class="page-subtitle">How to use your portal</p>
  </div>

  <div class="bp-card">
    <div class="bp-card-title"><i class="fas fa-compass" style="color:{{ $mc['primary'] }};"></i> Portal Guide</div>
    <div style="font-size:12px;color:var(--text-muted);line-height:1.6;">
      <p style="margin-bottom:10px;"><strong style="color:var(--text);">Dashboard:</strong> See your latest scores, attendance and fees at a glance.</p>
      <p style="margin-bottom:10px;"><strong style="color:var(--text);">Results:</strong> View your report cards for each term and download PDF copies.</p>
      <p style="margin-bottom:10px;"><strong style="color:var(--text);">Attendance:</strong> Check how many classes you've attended per subject.</p>
      <p style="margin-bottom:10px;"><strong style="color:var(--text);">Notifications:</strong> Read important messages from your school.</p>
      <p><strong style="color:var(--text);">Fees:</strong> See how much you've paid and what's outstanding.</p>
    </div>
  </div>

  <div class="bp-card">
    <div class="bp-card-title"><i class="fas fa-question-circle" style="color:{{ $mc['primary'] }};"></i> FAQs</div>
    <div style="font-size:12px;color:var(--text-muted);line-height:1.6;">
      <p style="margin-bottom:8px;"><strong style="color:var(--text);">My results are locked — what do I do?</strong><br>Visit the admin office to settle any outstanding fees.</p>
      <p style="margin-bottom:8px;"><strong style="color:var(--text);">A subject score is missing — why?</strong><br>Your teacher may not have uploaded the scores yet. Check with them or the admin office.</p>
      <p><strong style="color:var(--text);">How is my class position calculated?</strong><br>It's based on your total score across all subjects compared to your classmates.</p>
    </div>
  </div>
</div>
