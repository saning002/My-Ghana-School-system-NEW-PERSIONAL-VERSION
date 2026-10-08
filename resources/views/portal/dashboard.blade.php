<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Portal — {{ $student->user->full_name }}</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    /* ── THEME VARIABLES ─────────────────────────────────────────── */
    :root {
      --bg:           #F5F0E8;
      --surface:      #ffffff;
      --surface2:     #fffbf0;
      --border:       #fef3c7;
      --text:         #111827;
      --text-muted:   #6b7280;
      --text-sub:     #78350f;
      --accent:       #fbbf24;
      --accent2:      #f59e0b;
      --accent-dark:  #D4A017;
      --accent-text:  #111827;
      --hero-bg:      #fef9c3;
      --hero-text:    #111827;
      --hero-sub:     #78350f;
      --header-bg:    #fbbf24;
      --sidebar-bg:   rgba(11,17,33,0.92);
      --card-shadow:  0 4px 12px rgba(0,0,0,0.06);
      --analytics-val:#fbbf24;
      --badge-ok-bg:  #dcfce7; --badge-ok-txt:#166534;
      --badge-warn-bg:#fef3c7; --badge-warn-txt:#92400e;
      --badge-err-bg: #fee2e2; --badge-err-txt:#b91c1c;
      --progress-bg:  #e5e7eb;
      --th-bg:        #fffbf0;
      --td-hover:     #fffbf0;
      --toggle-icon:  "🌙";
      --portal-dim-bg: rgba(245, 240, 232, 0.75);
    }
    [data-theme="dark"] {
      --bg:           #0f1117;
      --surface:      #1a1d27;
      --surface2:     #22263a;
      --border:       #2e3347;
      --text:         #e2e8f0;
      --text-muted:   #94a3b8;
      --text-sub:     #fbbf24;
      --accent:       #6366f1;
      --accent2:      #4f46e5;
      --accent-dark:  #818cf8;
      --accent-text:  #ffffff;
      --hero-bg:      #1e1b4b;
      --hero-text:    #e2e8f0;
      --hero-sub:     #a5b4fc;
      --header-bg:    #1e1b4b;
      --sidebar-bg:   rgba(10,10,20,0.97);
      --card-shadow:  0 4px 16px rgba(0,0,0,0.4);
      --analytics-val:#818cf8;
      --badge-ok-bg:  #14532d; --badge-ok-txt:#86efac;
      --badge-warn-bg:#451a03; --badge-warn-txt:#fcd34d;
      --badge-err-bg: #450a0a; --badge-err-txt:#fca5a5;
      --progress-bg:  #2e3347;
      --th-bg:        #22263a;
      --td-hover:     #22263a;
      --toggle-icon:  "☀️";
      --portal-dim-bg: rgba(15, 17, 23, 0.82);
    }
    /* ─────────────────────────────────────────────────────────────── */

    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; min-height: 100%; }
    body { font-family: "Inter", sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; position: relative; transition: background 0.3s, color 0.3s; }

    .portal-bg {
      position: fixed; inset: 0; z-index: 0;
      background-size: cover; background-position: center; background-repeat: no-repeat;
      background-attachment: fixed; background-color: var(--bg);
      pointer-events: none;
    }
    .portal-bg-dim {
      position: fixed; inset: 0; z-index: 1;
      background: var(--portal-dim-bg);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      pointer-events: none;
    }

    a { color: inherit; text-decoration: none; }
    button { font: inherit; cursor: pointer; border: none; }
    input, select { font: inherit; }

    .app-shell { position: relative; z-index: 2; min-height: 100vh; display: flex; flex-direction: column; }

    /* SIDEBAR */
    .sidebar {
      position: fixed; left: 0; top: 0; height: 100vh; width: 280px; z-index: 1000;
      background: var(--sidebar-bg); color: #fff;
      overflow-y: auto; transform: translateX(-100%); transition: transform 0.3s ease;
      box-shadow: 3px 0 24px rgba(0,0,0,0.3);
      backdrop-filter: blur(14px);
    }
    .sidebar.open { transform: translateX(0); }
    .sidebar-header { padding: 28px 24px 22px; border-bottom: 2px solid var(--accent); display: flex; gap: 12px; align-items: center; }
    .sidebar-header img { width: 40px; height: 40px; border-radius: 10px; background: var(--accent); padding: 3px; object-fit: contain; }
    .sidebar-header .title { font-size: 14px; font-weight: 900; color: var(--accent-dark); line-height: 1.2; }
    .sidebar-header .subtitle { font-size: 10px; color: rgba(255,255,255,0.75); margin-top: 4px; }
    .sidebar-nav { padding: 16px 0; }
    .sidebar-nav a {
      display: flex; align-items: center; gap: 12px; padding: 14px 20px; margin: 4px 12px;
      border-radius: 12px; color: rgba(255,255,255,0.75); font-size: 13px; font-weight: 600;
      transition: all 0.25s;
    }
    .sidebar-nav a:hover, .sidebar-nav a.active {
      background: rgba(99,102,241,0.15); color: var(--accent-dark);
    }
    .sidebar-nav a.active { border-left: 3px solid var(--accent); padding-left: 17px; }
    .sidebar-nav a i { width: 18px; text-align: center; font-size: 13px; }
    .sidebar-footer { padding: 16px 12px 24px; border-top: 1px solid rgba(255,255,255,0.1); margin: 12px 0 0; }
    .sidebar-footer form { margin: 0; }
    .sidebar-footer button {
      width: 100%; background: var(--accent); border: none;
      border-radius: 10px; padding: 12px; color: var(--accent-text); font-weight: 700; font-size: 12px;
      transition: all 0.2s;
    }
    .sidebar-footer button:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(251,191,36,0.3); }

    .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; }
    .sidebar-overlay.open { display: block; }

    /* HEADER */
    .header {
      background: var(--header-bg); padding: 16px 16px;
      display: flex; justify-content: space-between; align-items: center; gap: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.15); position: sticky; top: 0; z-index: 50;
    }
    .header-left { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
    .student-avatar { width: 48px; height: 48px; border-radius: 12px; background: var(--accent-dark); display: grid; place-items: center; overflow: hidden; flex-shrink: 0; border: 2px solid rgba(255,255,255,0.4); }
    .student-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .student-avatar .avatar-icon { font-size: 22px; color: #fff; font-weight: 900; line-height: 1; }
    .student-info { flex: 1; min-width: 0; }
    .student-info .name { font-size: 13px; font-weight: 800; color: var(--accent-text); line-height: 1.2; }
    .student-info .id { font-size: 11px; color: rgba(255,255,255,0.75); font-weight: 600; }
    .header-right { display: flex; align-items: center; gap: 8px; }
    .hamburger {
      background: rgba(255,255,255,0.2); border: none; border-radius: 8px; padding: 8px 10px;
      color: var(--accent-text); cursor: pointer; font-size: 16px; transition: all 0.2s;
    }
    .hamburger:active { transform: scale(0.95); }
    /* Theme toggle button */
    .theme-toggle {
      background: rgba(255,255,255,0.2); border: none; border-radius: 8px; padding: 7px 10px;
      color: var(--accent-text); cursor: pointer; font-size: 15px; transition: all 0.2s;
      display: flex; align-items: center; gap: 5px; font-weight: 700; font-size: 11px;
    }
    .theme-toggle:active { transform: scale(0.95); }

    /* MAIN CONTENT */
    .main-content { flex: 1; padding: 20px 16px; overflow-y: auto; }
    .main-content::-webkit-scrollbar { width: 6px; }
    .main-content::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); }
    .main-content::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 3px; }

    .page-header { margin-bottom: 20px; }
    .page-title { margin: 0 0 4px; font-size: 18px; font-weight: 900; color: var(--text); }
    .page-subtitle { margin: 0; font-size: 12px; color: var(--text-muted); font-weight: 500; }

    .page-content { display: none; }
    .page-content.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    /* CARDS */
    .card { background: var(--surface); border-radius: 14px; padding: 18px; box-shadow: var(--card-shadow); border: 1px solid var(--border); margin-bottom: 16px; transition: background 0.3s, border-color 0.3s; }
    .card h2 { margin: 0 0 14px; font-size: 14px; font-weight: 800; color: var(--text); }
    .card h3 { margin: 0 0 10px; font-size: 12px; font-weight: 700; color: var(--text); }
    .card p { margin: 0; color: var(--text-muted); font-size: 12px; }

    .hero-card { background: var(--hero-bg); color: var(--hero-text); padding: 20px; border-radius: 14px; margin-bottom: 16px; box-shadow: var(--card-shadow); }
    .hero-card h2 { margin: 0 0 8px; font-size: 16px; font-weight: 900; color: var(--hero-text); }
    .hero-card p { margin: 4px 0 0; color: var(--hero-sub); font-size: 12px; font-weight: 600; }

    .analytics-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
    .analytics-box { background: var(--surface); border-radius: 14px; padding: 16px; border: 2px solid var(--border); text-align: center; transition: background 0.3s; }
    .analytics-box .label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 8px; }
    .analytics-box .value { font-size: 24px; font-weight: 900; color: var(--analytics-val); margin: 0; }
    .analytics-box .detail { font-size: 11px; color: var(--text-muted); margin-top: 6px; }

    .circular-progress { width: 120px; height: 120px; border-radius: 50%; background: conic-gradient(var(--accent) var(--percentage), var(--progress-bg) 0); display: grid; place-items: center; margin: 0 auto 12px; }
    .circular-progress-inner { width: 108px; height: 108px; border-radius: 50%; background: var(--surface); display: grid; place-items: center; }
    .circular-progress-text { text-align: center; }
    .circular-progress-text .num { font-size: 22px; font-weight: 900; color: var(--accent); }
    .circular-progress-text .pct { font-size: 10px; color: var(--text-muted); }

    .stats-row { display: flex; justify-content: space-around; gap: 8px; margin-top: 12px; }
    .stat-item { text-align: center; flex: 1; }
    .stat-item strong { display: block; font-size: 16px; color: var(--accent); font-weight: 900; }
    .stat-item small { display: block; font-size: 10px; color: var(--text-muted); margin-top: 2px; }

    .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .info-row:last-child { border-bottom: none; }
    .info-row .label { font-size: 11px; font-weight: 600; color: var(--text-muted); }
    .info-row .value { font-size: 12px; font-weight: 700; color: var(--text); }

    .table-wrap { overflow-x: auto; margin-top: 12px; }
    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    th { background: var(--th-bg); padding: 10px 8px; text-align: left; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 2px solid var(--border); }
    td { padding: 12px 8px; border-bottom: 1px solid var(--border); color: var(--text); }
    tbody tr:hover { background: var(--td-hover); }

    .badge { display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 16px; font-size: 10px; font-weight: 700; }
    .badge.success { background: var(--badge-ok-bg); color: var(--badge-ok-txt); }
    .badge.warning { background: var(--badge-warn-bg); color: var(--badge-warn-txt); }
    .badge.danger  { background: var(--badge-err-bg); color: var(--badge-err-txt); }

    .progress-bar { height: 8px; background: var(--progress-bg); border-radius: 10px; overflow: hidden; margin-top: 8px; }
    .progress-fill { height: 100%; background: var(--accent); border-radius: 10px; }

    .empty-state { text-align: center; padding: 40px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 36px; margin-bottom: 12px; color: var(--border); }
    .empty-state p { font-size: 12px; margin: 0; }

    .button { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 14px; border-radius: 10px; border: none; font-weight: 700; font-size: 12px; transition: all 0.2s; }
    .button-primary { background: var(--accent); color: var(--accent-text); }
    .button-primary:active { transform: scale(0.98); }
    .button-secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
    .button-secondary:active { transform: scale(0.98); }

    /* RESPONSIVE */
    @media (min-width: 769px) {
      .app-shell { flex-direction: row; }
      .sidebar { position: relative; transform: translateX(0); width: 260px; box-shadow: 2px 0 16px rgba(0,0,0,0.1); }
      .sidebar-overlay { display: none !important; }
      .hamburger { display: none; }
      .main-content { flex: 1; padding: 24px; max-width: calc(100% - 260px); }
      .header { position: sticky; top: 0; }
      .analytics-grid { grid-template-columns: repeat(4, 1fr); }
    }

    @media (max-width: 480px) {
      .header { padding: 12px; }
      .student-avatar { width: 40px; height: 40px; font-size: 18px; border-radius: 10px; }
      .student-info .name { font-size: 12px; }
      .student-info .id { font-size: 10px; }
      .main-content { padding: 16px; }
      .page-title { font-size: 16px; }
      .card { padding: 14px; margin-bottom: 12px; }
      .analytics-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    }
  </style>
  {{-- Apply saved theme before paint to avoid flash --}}
  <script>
    (function(){
      var t = localStorage.getItem('portal_theme') || 'gold';
      document.documentElement.setAttribute('data-theme', t === 'dark' ? 'dark' : '');
    })();
  </script>
</head>
<body>
  @php
    // Use direct Cloudinary URLs if configured, otherwise fall back to controller routes
    $portalBgUrl    = $student->background_photo ? (filter_var($student->background_photo, FILTER_VALIDATE_URL) ? $student->background_photo : route('portal.background', $student, false)) : null;
    $portalPhotoSrc = $student->photo            ? (filter_var($student->photo,            FILTER_VALIDATE_URL) ? $student->photo            : route('portal.photo',       $student, false)) : null;
  @endphp
  @if($portalBgUrl)
    <div class="portal-bg" style='background-image:url({{ json_encode($portalBgUrl) }});'></div>
    <div class="portal-bg-dim" aria-hidden="true"></div>
  @endif
  <div class="app-shell">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar" @if($portalBgUrl) style="background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), url('{{ $portalBgUrl }}') no-repeat center center / cover;" @endif>
      <div class="sidebar-header">
        <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="{{ $schoolName ?? 'Logo' }}">
        <div>
          <div class="title">{{ $schoolName ?? 'School' }}</div>
          <div class="subtitle">{{ $schoolSubtitle ?? '' }}</div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <a class="sidebar-link active" data-page="dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a class="sidebar-link" data-page="results"><i class="fas fa-file-alt"></i> Results</a>
        <a class="sidebar-link" data-page="attendance"><i class="fas fa-calendar-check"></i> Attendance</a>
        <a class="sidebar-link" data-page="fees"><i class="fas fa-receipt"></i> Fees</a>
        <a class="sidebar-link" data-page="profile"><i class="fas fa-user"></i> Profile</a>
        <a class="sidebar-link" href="{{ route('portal.change-password') }}"><i class="fas fa-key"></i> Change Password</a>
        <a class="sidebar-link" data-page="notifications"><i class="fas fa-bell"></i> Notifications</a>
        <a class="sidebar-link" data-page="guide"><i class="fas fa-question-circle"></i> Portal Guide</a>
      </nav>
      <div class="sidebar-footer">
        <form method="POST" action="{{ route('portal.logout') }}">
          @csrf
          <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
      </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- MAIN -->
    <div class="main-content">
      <!-- HEADER -->
      <div class="header">
        <div class="header-left">
          <div class="student-avatar">
            @if($portalPhotoSrc)
              <img src="{{ e($portalPhotoSrc) }}" alt="{{ $student->user->full_name }}" width="48" height="48" loading="eager"
                   onerror="this.style.display='none';var f=this.nextElementSibling;if(f)f.style.removeProperty('display');">
              <div class="avatar-icon" style="display:none">{{ strtoupper(substr($student->user->full_name, 0, 1)) }}</div>
            @else
              <div class="avatar-icon">{{ strtoupper(substr($student->user->full_name, 0, 1)) }}</div>
            @endif
          </div>
          <div class="student-info">
            <div class="name">{{ $student->user->full_name }}</div>
            <div class="id">{{ $student->student_id }}</div>
          </div>
        </div>
        <button class="hamburger" id="hamburgerBtn"><i class="fas fa-bars"></i></button>
        <button class="theme-toggle" id="themeToggleBtn" title="Switch theme">
          <span id="themeIcon">🌙</span>
        </button>
        <a href="{{ route('portal.change-password') }}" class="button button-secondary" style="margin-left:8px; padding:8px 10px; font-size:12px; display:inline-flex; align-items:center; gap:8px;"><i class="fas fa-key"></i> Change Password</a>
      </div>

      @php
        $totalPresent = $student->attendances->where('status', 'present')->count();
        $totalAtt = $student->attendances->count();
        $attRate = $totalAtt > 0 ? round($totalPresent / $totalAtt * 100) : 0;
        
        $totalScores = $student->examScores->sum('grade_point');
        $totalCourses = $student->enrollments->count();
        $avgScore = $totalCourses > 0 ? round($totalScores / $totalCourses, 1) : 0;

        // Most recent semester/exams own SGPA
        $hasRecentScores = !empty($reportCardPreview['has_scores']);
        $mostRecentSgpa = $hasRecentScores ? (float)$reportCardPreview['overall_sgpa'] : (float)$avgScore;
        $mostRecentProgramName = $reportCardPreview ? ($reportCardPreview['program']->name ?? '—') : ($student->program->name ?? '—');
        $cgpa = $cgpa ?? 0;
      @endphp

      <!-- DASHBOARD PAGE -->
      <div id="page-dashboard" class="page-content active">
        <div class="page-header">
          <h1 class="page-title">Dashboard</h1>
          <p class="page-subtitle">Your academic overview</p>
        </div>

        <div class="hero-card">
          <h2>Welcome, {{ explode(' ', $student->user->full_name)[0] }}! 👋</h2>
          <p>{{ $student->program->name ?? '—' }} • {{ $student->churchBranch->name ?? '—' }}</p>
        </div>

        <div class="card">
          <h2><i class="fas fa-chart-line" style="color:#fbbf24;margin-right:6px;"></i>Overall Performance</h2>
          <div style="text-align:center;">
            @if($student->results_blocked)
              <div style="padding: 24px 0 16px;">
                <div style="width: 70px; height: 70px; background: rgba(239, 68, 68, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; border: 1px dashed rgba(239, 68, 68, 0.3);">
                  <i class="fas fa-lock" style="font-size: 26px; color: #ef4444;"></i>
                </div>
                <h3 style="font-size: 14px; font-weight: 800; color: #ef4444; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.05em;">GPA Locked</h3>
                <p style="font-size: 11px; color: var(--text-muted); max-width: 200px; margin: 0 auto;">Please settle pending fees to unlock your results.</p>
              </div>
            @else
              <div class="circular-progress" style="--percentage:{{ min((float)$mostRecentSgpa * 25, 100) }}%;">
                <div class="circular-progress-inner">
                  <div class="circular-progress-text">
                    <div class="num">{{ number_format($mostRecentSgpa, 2) }}</div>
                    <div class="pct">/ 4.0</div>
                  </div>
                </div>
              </div>
              <p style="margin:0;color:#6b7280;font-size:11px;">Grade Point Average ({{ $mostRecentProgramName }})</p>
            @endif
            <div class="stats-row">
              <div class="stat-item">
                <strong>{{ $totalCourses }}</strong>
                <small>Courses</small>
              </div>
              <div class="stat-item">
                <strong>{{ $attRate }}%</strong>
                <small>Attendance</small>
              </div>
              <div class="stat-item">
                <strong>{{ $programs->count() }}</strong>
                <small>Programs</small>
              </div>
            </div>
          </div>
        </div>

        <div class="analytics-grid">
          <div class="analytics-box">
            <div class="label">Outstanding</div>
            <p class="value">GH₵{{ number_format($fees['balance'] ?? 0, 0) }}</p>
            <div class="detail">Fee Balance</div>
          </div>
          <div class="analytics-box">
            <div class="label">Attendance</div>
            <p class="value">{{ $attRate }}%</p>
            <div class="detail">Present</div>
          </div>
          <div class="analytics-box">
            <div class="label">Courses</div>
            <p class="value">{{ $totalCourses }}</p>
            <div class="detail">Enrolled</div>
          </div>
          <div class="analytics-box">
            <div class="label">GPA</div>
            <p class="value">{{ number_format($mostRecentSgpa, 2) }}</p>
            <div class="detail">SGPA ({{ $mostRecentProgramName }})</div>
          </div>
          <div class="analytics-box">
            <div class="label">CGPA</div>
            <p class="value">{{ number_format($cgpa, 2) }}</p>
            <div class="detail">Cumulative GPA</div>
          </div>
        </div>
      </div>

      <!-- RESULTS PAGE -->
      <div id="page-results" class="page-content">
        <div class="page-header">
          <h1 class="page-title">Report Cards</h1>
          <p class="page-subtitle">Your exam results</p>
        </div>

        @if($student->results_blocked)
          <div class="card" style="background: #ef4444; color: #fff; text-align: center; padding: 40px 20px; border-radius: 16px; box-shadow: 0 10px 30px rgba(239, 68, 68, 0.2); border: none;">
            <div style="width: 70px; height: 70px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
              <i class="fas fa-lock" style="font-size: 32px; color: #fff;"></i>
            </div>
            <h2 style="margin: 0 0 10px; font-size: 20px; font-weight: 800; tracking-tight: -0.025em; color: #fff;">Results Access Locked</h2>
            <p style="margin: 0 0 20px; font-size: 14px; color: #fecaca; line-height: 1.5; max-width: 420px; margin-left: auto; margin-right: auto;">
              Your academic report cards and examination results are currently locked by the college administration.
            </p>
            <div style="background: rgba(0, 0, 0, 0.2); padding: 12px 18px; border-radius: 10px; display: inline-block; font-size: 13px; font-weight: 700; border: 1px solid rgba(255, 255, 255, 0.1);">
              <i class="fas fa-info-circle" style="margin-right: 6px; color: #fecaca;"></i> Please settle outstanding program/exams fees or contact the admin office.
            </div>
          </div>
        @else
          @if($reportCardPreview)
          <!-- Auto-Preview of Most Recent Result -->
          <div class="card" style="background: #fbbf24; color: #111827; margin-bottom: 20px;">
            <h2 style="margin: 0 0 10px; font-size: 14px; font-weight: 800;">📊 Your Most Recent Result</h2>
            <p style="margin: 0 0 14px; font-size: 12px; color: #78350f;">{{ $reportCardPreview['program']->name ?? 'Current Program' }}</p>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px;">
              <div style="background: rgba(255,255,255,0.2); padding: 12px 6px; border-radius: 10px; text-align: center;">
                <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #78350f; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">SGPA</div>
                <div style="font-size: 16px; font-weight: 900;">{{ $reportCardPreview['overall_average'] ?? '0' }}%</div>
              </div>
              <div style="background: rgba(255,255,255,0.2); padding: 12px 6px; border-radius: 10px; text-align: center;">
                <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #78350f; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Grade</div>
                <div style="font-size: 16px; font-weight: 900; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $reportCardPreview['overall_grade_description'] ?? '—' }}</div>
              </div>
              <div style="background: rgba(255,255,255,0.2); padding: 12px 6px; border-radius: 10px; text-align: center;">
                <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; color: #78350f; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Position</div>
                <div style="font-size: 16px; font-weight: 900;">{{ $reportCardPreview['overall_position'] ?? '—' }}{{ is_numeric($reportCardPreview['overall_position'] ?? null) ? ((int)$reportCardPreview['overall_position'] === 1 ? 'st' : ((int)$reportCardPreview['overall_position'] === 2 ? 'nd' : ((int)$reportCardPreview['overall_position'] === 3 ? 'rd' : 'th'))) : '' }}</div>
              </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
              <a href="{{ route('portal.report-card', $reportCardPreview['program'] ?? $programs->first()) }}?attempt={{ $previewAttempt }}" class="button button-secondary" style="text-align: center; background: rgba(255,255,255,0.3); color: #111827; border: none; padding: 10px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none;">View Full Report</a>
              @if($reportCardPreview['has_scores'])
              <a href="{{ route('portal.report-card.pdf', $reportCardPreview['program'] ?? $programs->first()) }}?attempt={{ $previewAttempt }}" target="_blank" class="button button-primary" style="text-align: center; background: #fff; color: #fbbf24; border: none; padding: 10px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none;"><i class="fas fa-file-pdf"></i> Download PDF</a>
              @endif
            </div>
          </div>
          @endif

          @if($programs->isEmpty())
            <div class="card" style="text-align:center;">
              <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <p>No report cards yet</p>
              </div>
            </div>
          @else
            @foreach($programs as $program)
              @php
                $hasScores   = $student->examScores->where('program_id', $program->id)->isNotEmpty();
                $latestAttempt = $student->examScores->where('program_id', $program->id)->max('attempt') ?? 1;
              @endphp
              <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                  <h3>{{ $program->name }}</h3>
                  <span class="badge {{ $hasScores ? 'success' : 'warning' }}">{{ $hasScores ? 'Ready' : 'Pending' }}</span>
                </div>
                <p style="margin-bottom:12px;">Semester {{ $program->sequence }}</p>
                <div style="display:flex;gap:8px;">
                  <a href="{{ route('portal.report-card', $program) }}?attempt={{ $latestAttempt }}" class="button button-secondary">View</a>
                  @if($hasScores)
                    <a href="{{ route('portal.report-card.pdf', $program) }}?attempt={{ $latestAttempt }}" target="_blank" class="button button-primary"><i class="fas fa-file-pdf"></i> PDF</a>
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
          <p class="page-subtitle">Your class records</p>
        </div>

        @if($attendanceSummary->isEmpty())
          <div class="card" style="text-align:center;">
            <div class="empty-state">
              <i class="fas fa-calendar-times"></i>
              <p>No records yet</p>
            </div>
          </div>
        @else
          @foreach($attendanceSummary as $code => $att)
            @php $pct = $att['percentage']; @endphp
            <div class="card">
              <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:10px;">
                <div>
                  <h3>{{ $att['course'] }}</h3>
                  <p style="margin:0;font-size:11px;color:#6b7280;">{{ $code }}</p>
                </div>
                <span class="badge {{ $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') }}">{{ $pct }}%</span>
              </div>
              <div class="progress-bar">
                <div class="progress-fill" style="width:{{ $pct }}%;"></div>
              </div>
              <div class="stats-row">
                <div class="stat-item">
                  <strong style="color:#fbbf24;">{{ $att['present'] }}</strong>
                  <small>Present</small>
                </div>
                <div class="stat-item">
                  <strong style="color:#dc2626;">{{ $att['absent'] }}</strong>
                  <small>Absent</small>
                </div>
                <div class="stat-item">
                  <strong>{{ $att['total'] }}</strong>
                  <small>Total</small>
                </div>
              </div>
            </div>
          @endforeach
        @endif
      </div>

      <!-- FEES PAGE -->
      <div id="page-fees" class="page-content">
        <div class="page-header">
          <h1 class="page-title">Fee Statement</h1>
          <p class="page-subtitle">Your billing details</p>
        </div>

        <div class="analytics-grid" style="grid-template-columns:repeat(2,1fr); gap:12px; margin-bottom:16px;">
          <div class="analytics-box">
            <div class="label">Program Tuition</div>
            <p class="value">GH₵{{ number_format($fees['program_total'] ?? 0, 0) }}</p>
            <div class="detail">Billed Tuition</div>
          </div>
          <div class="analytics-box">
            <div class="label">Exams Fee</div>
            <p class="value" style="color:#fbbf24;">GH₵{{ number_format($fees['exam_total'] ?? 0, 0) }}</p>
            <div class="detail">Billed Examinations</div>
          </div>
          <div class="analytics-box">
            <div class="label">Total Paid</div>
            <p class="value" style="color:#059669;">GH₵{{ number_format($fees['paid'] ?? 0, 0) }}</p>
            <div class="detail">Received Payments</div>
          </div>
          <div class="analytics-box">
            <div class="label">Outstanding</div>
            <p class="value" style="color:{{ ($fees['balance'] ?? 0) > 0 ? '#dc2626' : '#fbbf24' }};">GH₵{{ number_format($fees['balance'] ?? 0, 0) }}</p>
            <div class="detail">Net Balance</div>
          </div>
        </div>

        <div class="card">
          <h2><i class="fas fa-history" style="color:#fbbf24;margin-right:6px;"></i>Payments</h2>
          @if($student->payments->isEmpty())
            <div class="empty-state" style="padding:30px;">
              <i class="fas fa-wallet"></i>
              <p>No payments yet</p>
            </div>
          @else
            <div class="table-wrap">
              <table>
                <thead><tr><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                  @foreach($student->payments->sortByDesc('payment_date')->take(8) as $payment)
                    <tr>
                      <td>{{ optional($payment->payment_date)->format('M d') ?? '—' }}</td>
                      <td><strong>GH₵{{ number_format($payment->amount_paid, 0) }}</strong></td>
                      <td><span class="badge success">Paid</span></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>

      <!-- PROFILE PAGE -->
      <div id="page-profile" class="page-content">
        <div class="page-header">
          <h1 class="page-title">Profile</h1>
          <p class="page-subtitle">Your information</p>
        </div>

        {{-- Profile photo card --}}
        <div class="card" style="text-align:center; padding: 24px 18px;">
          <div style="width:90px;height:90px;border-radius:50%;overflow:hidden;margin:0 auto 12px;border:3px solid #fbbf24;box-shadow:0 4px 16px rgba(212,160,23,0.25);background:#D4A017;display:flex;align-items:center;justify-content:center;">
            @if($portalPhotoSrc ?? null)
              <img src="{{ e($portalPhotoSrc) }}" alt="{{ $student->user->full_name }}"
                   style="width:100%;height:100%;object-fit:cover;display:block;"
                   onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
              <span style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-size:36px;font-weight:900;color:#fff;">{{ strtoupper(substr($student->user->full_name, 0, 1)) }}</span>
            @else
              <span style="font-size:36px;font-weight:900;color:#fff;">{{ strtoupper(substr($student->user->full_name, 0, 1)) }}</span>
            @endif
          </div>
          <div style="font-size:16px;font-weight:900;color:#111827;">{{ $student->user->full_name }}</div>
          <div style="font-size:12px;color:#6b7280;margin-top:4px;">{{ $student->student_id }}</div>
        </div>

        <div class="card">
          <h2><i class="fas fa-user" style="color:#fbbf24;margin-right:6px;"></i>Personal</h2>
          <div class="info-row"><span class="label">Name</span><span class="value">{{ $student->user->full_name }}</span></div>
          <div class="info-row"><span class="label">ID</span><span class="value">{{ $student->student_id }}</span></div>
          <div class="info-row"><span class="label">Email</span><span class="value" style="font-size:11px;">{{ $student->user->email }}</span></div>
          <div class="info-row"><span class="label">Phone</span><span class="value">{{ $student->user->phone ?? '—' }}</span></div>
        </div>

        <div class="card">
          <h2><i class="fas fa-graduation-cap" style="color:#fbbf24;margin-right:6px;"></i>Academic</h2>
          <div class="info-row"><span class="label">Program</span><span class="value">{{ $student->program->name ?? '—' }}</span></div>
          <div class="info-row"><span class="label">Branch</span><span class="value">{{ $student->churchBranch->name ?? '—' }}</span></div>
          <div class="info-row"><span class="label">Status</span><span class="value"><span class="badge success">{{ ucfirst($student->status) }}</span></span></div>
          <div class="info-row"><span class="label">Admitted</span><span class="value">{{ optional($student->admission_date)->format('M d, Y') ?? '—' }}</span></div>
        </div>
      </div>

      <!-- NOTIFICATIONS PAGE -->
      <div id="page-notifications" class="page-content">
        <div class="page-header">
          <h1 class="page-title">Notifications</h1>
          <p class="page-subtitle">Updates from college</p>
        </div>

        @if($notifications->isEmpty())
          <div class="card" style="text-align:center;">
            <div class="empty-state">
              <i class="fas fa-bell-slash"></i>
              <p>No notifications</p>
            </div>
          </div>
        @else
          @foreach($notifications->take(10) as $notif)
            @php $isNew = !$notif->is_read; @endphp
            <div class="card" style="{{ $isNew ? 'border-left: 3px solid var(--accent);' : '' }}">
              <div style="display:flex;align-items:flex-start;gap:10px;">
                <div style="width:36px;height:36px;border-radius:10px;background:var(--border);display:grid;place-items:center;color:var(--text-sub);flex-shrink:0;font-size:14px;">
                  <i class="fas fa-bell"></i>
                </div>
                <div style="flex:1;min-width:0;">
                  <h3 style="margin:0;font-size:12px;font-weight:700;">{{ $notif->title }}</h3>
                  @if($isNew)<span class="badge warning" style="margin-top:4px;">New</span>@endif
                  <p style="margin:6px 0 0;font-size:11px;color:var(--text-muted);line-height:1.4;">{{ Str::limit($notif->message, 100) }}</p>
                  <p style="margin:6px 0 0;font-size:10px;color:var(--text-muted);">{{ optional($notif->created_at)->diffForHumans() }}</p>
                </div>
              </div>
            </div>
          @endforeach
        @endif
      </div>

      <!-- GUIDE PAGE -->
      <div id="page-guide" class="page-content">
        <div class="page-header">
          <h1 class="page-title">Portal Guide</h1>
          <p class="page-subtitle">Learn how to navigate your portal and understand the GPA system</p>
        </div>

        <div class="card" style="background: var(--accent); color: var(--accent-text); border: none; padding: 24px;">
          <h2 style="color: var(--accent-text); font-size: 18px; margin-bottom: 8px;">Welcome to Your Student Portal! 👋</h2>
          <p style="color: var(--hero-sub); font-size: 13px; line-height: 1.5; font-weight: 500; max-width: 600px;">
            This portal is your personal academic assistant. Designed to be clean, premium, and fully responsive, it enables you to monitor your performance, verify your attendance, manage your financial statements, and track your path to graduation with complete clarity.
          </p>
        </div>

        <div class="card">
          <h2><i class="fas fa-compass" style="color:#fbbf24;margin-right:6px;"></i>Portal Sections Tour</h2>
          <div style="display: grid; grid-template-columns: 1fr; gap: 14px; margin-top: 10px;">
            <div style="background: var(--surface2); padding: 14px; border-radius: 10px; border: 1px solid var(--border);">
              <h3 style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: var(--text); margin-bottom: 6px;">
                <i class="fas fa-chart-pie" style="color: var(--accent);"></i> Dashboard
              </h3>
              <p style="margin: 0; color: var(--text-muted); font-size: 12px; line-height: 1.4;">
                Your landing page provides a quick overview. It features an **Overall Performance** card showing your most recent semester's SGPA (with an interactive circular indicator), overall counts of courses and active programs, recent alerts/notifications, and a quick summary of your billing status.
              </p>
            </div>
            
            <div style="background: var(--surface2); padding: 14px; border-radius: 10px; border: 1px solid var(--border);">
              <h3 style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: var(--text); margin-bottom: 6px;">
                <i class="fas fa-file-alt" style="color: var(--accent);"></i> Results (Report Cards)
              </h3>
              <p style="margin: 0; color: var(--text-muted); font-size: 12px; line-height: 1.4;">
                This tab lets you view your academic achievements by semester. You will see a detailed grid with your courses, final exam scores, grades, and your **personal class position/ranking**. You can also download official PDF report cards to your device.
              </p>
            </div>

            <div style="background: var(--surface2); padding: 14px; border-radius: 10px; border: 1px solid var(--border);">
              <h3 style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: var(--text); margin-bottom: 6px;">
                <i class="fas fa-calendar-check" style="color: var(--accent);"></i> Attendance
              </h3>
              <p style="margin: 0; color: var(--text-muted); font-size: 12px; line-height: 1.4;">
                Shows your presence rate across all courses in real time. We use color-coded badges to indicate safety: **Green** for optimal attendance (75% or higher), **Orange** for caution (50%-74%), and **Red** for critical levels (below 50%) that require immediate attention.
              </p>
            </div>

            <div style="background: var(--surface2); padding: 14px; border-radius: 10px; border: 1px solid var(--border);">
              <h3 style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: var(--text); margin-bottom: 6px;">
                <i class="fas fa-receipt" style="color: var(--accent);"></i> Fees Statement
              </h3>
              <p style="margin: 0; color: var(--text-muted); font-size: 12px; line-height: 1.4;">
                Allows you to keep tabs on your finances. View your exact program tuition, exams fees, cumulative payments made, and any outstanding balance. Keeping your balance clear ensures that your grades and transcripts remain unlocked.
              </p>
            </div>
          </div>
        </div>

        <div class="card">
          <h2><i class="fas fa-graduation-cap" style="color:#fbbf24;margin-right:6px;"></i>Understanding the GPA & SGPA System</h2>
          <p style="margin-bottom: 12px; line-height: 1.4; color: var(--text-muted); font-size: 12px;">
            Academic performance is evaluated using the **GPA (Grade Point Average)** system. It measures the quality of your academic work on a scale of **0.00 to 4.00**. There are two main concepts you will see:
          </p>
          <ul style="margin: 0 0 16px 20px; font-size: 12px; color: var(--text-muted); line-height: 1.5;">
            <li style="margin-bottom: 6px;"><strong style="color: var(--text);">SGPA (Semester Grade Point Average):</strong> Your average for a <em>single</em> specific semester/program based on the courses completed in that block.</li>
            <li><strong style="color: var(--text);">CGPA (Cumulative Grade Point Average):</strong> Your overall average computed across <em>all</em> semesters/programs since your admission.</li>
          </ul>

          <h3 style="margin-bottom: 8px; font-size: 13px; font-weight: 800;">Official College Grading Scale</h3>
          <p style="margin-bottom: 10px; color: var(--text-muted); font-size: 11px;">Each course score from 0 to 100 corresponds to a letter grade and specific Grade Points:</p>
          
          <div class="table-wrap" style="margin-bottom: 20px;">
            <table style="border: 1px solid var(--border); min-width: 320px;">
              <thead>
                <tr>
                  <th style="font-size: 10px; padding: 8px;">Score Range</th>
                  <th style="font-size: 10px; padding: 8px;">Grade</th>
                  <th style="font-size: 10px; padding: 8px;">Points</th>
                  <th style="font-size: 10px; padding: 8px;">Classification</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="padding: 8px; font-weight: 700;">80 - 100</td>
                  <td style="padding: 8px;"><span class="badge success" style="padding: 3px 8px; font-size: 9px;">A1</span></td>
                  <td style="padding: 8px; font-weight: 800; color: var(--accent-dark);">4.0</td>
                  <td style="padding: 8px; color: var(--text-muted);">Distinction</td>
                </tr>
                <tr>
                  <td style="padding: 8px; font-weight: 700;">70 - 79</td>
                  <td style="padding: 8px;"><span class="badge success" style="padding: 3px 8px; font-size: 9px; background: rgba(5,150,105,0.15); color: #059669;">A2</span></td>
                  <td style="padding: 8px; font-weight: 800; color: var(--accent-dark);">3.7</td>
                  <td style="padding: 8px; color: var(--text-muted);">Upper Division</td>
                </tr>
                <tr>
                  <td style="padding: 8px; font-weight: 700;">60 - 69</td>
                  <td style="padding: 8px;"><span class="badge success" style="padding: 3px 8px; font-size: 9px; background: rgba(5,150,105,0.15); color: #059669;">A3</span></td>
                  <td style="padding: 8px; font-weight: 800; color: var(--accent-dark);">3.3</td>
                  <td style="padding: 8px; color: var(--text-muted);">Lower Division</td>
                </tr>
                <tr>
                  <td style="padding: 8px; font-weight: 700;">50 - 59</td>
                  <td style="padding: 8px;"><span class="badge warning" style="padding: 3px 8px; font-size: 9px;">B1</span></td>
                  <td style="padding: 8px; font-weight: 800; color: var(--accent-dark);">3.0</td>
                  <td style="padding: 8px; color: var(--text-muted);">Credit</td>
                </tr>
                <tr>
                  <td style="padding: 8px; font-weight: 700;">40 - 49</td>
                  <td style="padding: 8px;"><span class="badge warning" style="padding: 3px 8px; font-size: 9px; background: rgba(245,158,11,0.15); color: #d97706;">B2</span></td>
                  <td style="padding: 8px; font-weight: 800; color: var(--accent-dark);">2.0</td>
                  <td style="padding: 8px; color: var(--text-muted);">Pass</td>
                </tr>
                <tr>
                  <td style="padding: 8px; font-weight: 700;">Below 40</td>
                  <td style="padding: 8px;"><span class="badge danger" style="padding: 3px 8px; font-size: 9px;">F</span></td>
                  <td style="padding: 8px; font-weight: 800; color: #dc2626;">0.0</td>
                  <td style="padding: 8px; color: var(--text-muted);">Fail</td>
                </tr>
              </tbody>
            </table>
          </div>

          <h3 style="margin-bottom: 8px; font-size: 13px; font-weight: 800;">How is SGPA Calculated? (Example)</h3>
          <p style="margin-bottom: 12px; color: var(--text-muted); font-size: 12px; line-height: 1.4;">
            Every course has a certain number of **Credits** (weight) associated with it. To find your SGPA:
            <br>
            1. Multiply each course's credit weight by the **Grade Points** you earned in it. This gives your **Credit Points**.
            <br>
            2. Add all your earned Credit Points together.
            <br>
            3. Divide that sum by the **Total Credits taken** during that semester.
          </p>

          <div style="background: var(--surface2); padding: 14px; border-radius: 10px; border: 1px dashed var(--border); font-size: 12px; color: var(--text-muted); line-height: 1.5;">
            <strong style="color: var(--text); display: block; margin-bottom: 6px;">💡 Step-by-Step Calculation:</strong>
            Let's say you took 3 courses in a semester, each worth 3 credits:
            <ul style="margin: 6px 0 10px 18px; padding: 0;">
              <li><strong>Course 1:</strong> Score of 82% = Grade A1 (4.0 Points). Credit Points = 3 cr × 4.0 = <strong>12.0</strong></li>
              <li><strong>Course 2:</strong> Score of 74% = Grade A2 (3.7 Points). Credit Points = 3 cr × 3.7 = <strong>11.1</strong></li>
              <li><strong>Course 3:</strong> Score of 55% = Grade B1 (3.0 Points). Credit Points = 3 cr × 3.0 = <strong>9.0</strong></li>
            </ul>
            <strong>Total Credits Taken:</strong> 3 + 3 + 3 = <strong>9 Credits</strong>
            <br>
            <strong>Total Credit Points Earned:</strong> 12.0 + 11.1 + 9.0 = <strong>32.1 Points</strong>
            <br>
            <strong>Your Semester SGPA:</strong> 32.1 / 9 = <strong style="color: var(--accent-dark); font-size: 13px;">3.57 out of 4.00</strong>
          </div>
        </div>

        <div class="card">
          <h2><i class="fas fa-shield-alt" style="color:#fbbf24;margin-right:6px;"></i>Confidentiality & Ranks</h2>
          <p style="margin: 0; line-height: 1.4; color: var(--text-muted); font-size: 12px;">
            🛡️ **Your class ranking is strictly personal and secure.** 
            To maintain privacy and foster a constructive learning environment, your relative class position is exclusively visible to you in the live portal. Official printed report cards, academic spreadsheets, and downloaded PDF files **will never** contain your class rank.
          </p>
        </div>

        <div class="card">
          <h2><i class="fas fa-question-circle" style="color:#fbbf24;margin-right:6px;"></i>Frequently Asked Questions</h2>
          <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 8px;">
            <div>
              <strong style="font-size: 12px; color: var(--text); display: block; margin-bottom: 4px;">❓ Why are my results showing as "Locked"?</strong>
              <p style="margin: 0; color: var(--text-muted); font-size: 12px; line-height: 1.4;">
                If your results are locked, it is because there is a pending balance on your program tuition or examination fees. Head over to the **Fees** tab to view your statement. Settle your outstanding balance at the admin office to instantly unlock all report cards and PDF downloads!
              </p>
            </div>
            <div style="border-top: 1px solid var(--border); padding-top: 12px;">
              <strong style="font-size: 12px; color: var(--text); display: block; margin-bottom: 4px;">❓ What should I do if a course score is missing?</strong>
              <p style="margin: 0; color: var(--text-muted); font-size: 12px; line-height: 1.4;">
                Some examination records can take time to be uploaded by the administration. If your report card shows a pending status or missing course, please verify with your course teacher or check in with the administration office.
              </p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
    // ── Sidebar ──────────────────────────────────────────────────
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    hamburgerBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('open');
    });

    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('open');
    });

    document.querySelectorAll('.sidebar-link').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const page = link.getAttribute('data-page');

        // Update active link
        document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        // Hide all pages
        document.querySelectorAll('.page-content').forEach(p => p.classList.remove('active'));

        // Show selected page
        document.getElementById(`page-${page}`).classList.add('active');

        // Close sidebar on mobile
        if (window.innerWidth < 769) {
          sidebar.classList.remove('open');
          overlay.classList.remove('open');
        }

        // Update page title
        const titles = {
          'dashboard': 'Dashboard',
          'results': 'Report Cards',
          'attendance': 'Attendance',
          'fees': 'Fee Statement',
          'profile': 'Profile',
          'notifications': 'Notifications',
          'guide': 'Portal Guide'
        };
        document.querySelector('.page-title').textContent = titles[page] || 'Portal';
      });
    });

    // ── Theme toggle ─────────────────────────────────────────────
    const themeBtn  = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');

    function applyTheme(theme) {
      document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'dark' : '');
      themeIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
      localStorage.setItem('portal_theme', theme);
    }

    // Sync icon with current theme on load
    applyTheme(localStorage.getItem('portal_theme') || 'gold');

    themeBtn.addEventListener('click', () => {
      const current = localStorage.getItem('portal_theme') || 'gold';
      applyTheme(current === 'dark' ? 'gold' : 'dark');
    });
  </script>
</body>
</html>
