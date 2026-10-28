!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#2563eb">
    <title>@yield('title','Dashboard') — {{ $schoolName ?? 'School' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#fef8e8',100:'#fdeecf',200:'#fbd88b',300:'#f6c74b',400:'#f3af20',500:'#f59e0b',600:'#d97706',700:'#b45309',800:'#92400e',900:'#6b2f0b' },
                        gold:    { 50:'#fef8e8',100:'#fdeecf',200:'#fbd88b',300:'#f6c74b',400:'#f3af20',500:'#f59e0b',600:'#d97706',700:'#b45309',800:'#92400e',900:'#6b2f0b' },
                        surface:'#f4f8ff', ink:'#0f172a',
                    },
                    fontFamily: { sans:['Inter','sans-serif'] },
                }
            }
        }
    </script>
    <style>
        @php
            // Read admin theme from tenant DB — SiteSetting has TenantAwareConnection
            $__adminPrimary = '#D4A017';
            $__adminNav     = '#0B1121';
            $__accent       = '#0891b2';
            $__primary      = '#f59e0b';
            $__primaryDark  = '#d97706';
            $__primaryPale  = '#fef8e8';
            $__primaryText  = '#f3af20';
            $__surface      = '#f4f8ff';
            $__text         = '#0f172a';
            try {
                $__t = \App\Models\Website\SiteSetting::instance()->theme;
                $__adminPrimary = $__t['adminPrimary'] ?? $__adminPrimary;
                $__adminNav     = $__t['adminNav']     ?? $__adminNav;
                $__accent       = $__t['accent']       ?? $__accent;
                $__primary      = $__t['primary']      ?? $__primary;
                $__primaryDark  = \App\Http\Controllers\Admin\ThemeController::darken($__primary, 15);
                $__primaryPale  = \App\Http\Controllers\Admin\ThemeController::lighten($__primary, 90);
                $__primaryText  = \App\Http\Controllers\Admin\ThemeController::lighten($__primary, 25);
                $__surface      = $__t['bg']            ?? $__surface;
                $__text         = $__t['text']          ?? $__text;
            } catch (\Throwable $__e) {}
            // Owner-panel theme override takes final precedence
            try {
                $__ownerTheme = app()->bound('owner_theme_override') ? app('owner_theme_override') : null;
                if ($__ownerTheme) {
                    $__adminPrimary = $__ownerTheme['adminPrimary'] ?? $__adminPrimary;
                    $__adminNav     = $__ownerTheme['adminNav']     ?? $__adminNav;
                    $__primary      = $__ownerTheme['primary']      ?? $__primary;
                    $__accent       = $__ownerTheme['accent']       ?? $__accent;
                    $__primaryDark  = \App\Http\Controllers\Admin\ThemeController::darken($__primary, 15);
                    $__primaryPale  = \App\Http\Controllers\Admin\ThemeController::lighten($__primary, 90);
                    $__primaryText  = \App\Http\Controllers\Admin\ThemeController::lighten($__primary, 25);
                }
            } catch (\Throwable $__e2) {}
        @endphp
        :root{
            --brand:{{ $__adminPrimary }};
            --brand-2:{{ $__accent }};
            --admin-nav:{{ $__adminNav }};
            --primary:{{ $__primary }};
            --primary-dark:{{ $__primaryDark }};
            --primary-pale:{{ $__primaryPale }};
            --primary-text:{{ $__primaryText }};
            --surface:{{ $__surface }};
            --text:{{ $__text }};
            --muted:#64748b;
            --border:rgba(148,163,184,.22)
        }
        /* ── Dynamic sidebar background ─────────────────────────────── */
        aside { background: var(--admin-nav) !important; }
        /* ── Dynamic gold buttons & accents ─────────────────────────── */
        .btn-gold { background: var(--brand) !important; }
        .btn-gold:hover { filter: brightness(.9); }
        .sidebar-link.active { background: rgba(255,255,255,.12) !important; border-left-color: var(--brand) !important; }
        *{-webkit-tap-highlight-color:transparent}
        body{font-family:'Inter',sans-serif;background:var(--surface);color:var(--text);letter-spacing:-0.01em;min-height:100vh;}
        h1,h2,h3,h4,h5,h6{letter-spacing:-0.025em;font-weight:700;}
        .sidebar-link{transition:all .12s cubic-bezier(.4,0,.2,1);border-left:3px solid transparent}
        .sidebar-link:hover{background:rgba(255,255,255,.12);color:#fff;border-left-color:rgba(255,255,255,.35);transform:translateX(4px)}
        .sidebar-link.active{background:color-mix(in srgb, var(--primary) 16%, transparent);color:var(--primary-text);border-left-color:var(--primary);box-shadow:inset 0 1px 0 rgba(255,255,255,.06)}
        .card{background:#ffffff;border-radius:1.35rem;border:1px solid rgba(148,163,184,.18);box-shadow:0 10px 30px -16px rgba(15,23,42,.16)}
        .card-hover{transition:all .12s cubic-bezier(.4,0,.2,1)}
        .card-hover:active{transform:scale(.98)}
        @media(min-width:1024px){.card-hover:hover{transform:translateY(-4px);box-shadow:0 22px 44px -16px rgba(15,23,42,.12),0 2px 6px rgba(15,23,42,.04)}}
        .bottom-nav{background:#ffffff;border-top:1px solid rgba(15,23,42,.08);padding-bottom:env(safe-area-inset-bottom,0px)}
        .bottom-nav-item.active .nav-icon{color:var(--brand-2);transform:scale(1.08)}
        .bottom-nav-item.active .nav-label{color:var(--brand-2);font-weight:700}
        .bottom-nav-item .nav-icon{color:#64748b;font-size:1.1rem;transition:all .12s}
        .bottom-nav-item .nav-label{color:#64748b;font-size:.6rem;margin-top:2px;transition:all .12s}
        ::-webkit-scrollbar{width:5px;height:5px}
        ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px}
        ::-webkit-scrollbar-thumb:hover{background:#94a3b8}
        input,select,textarea,button{font-size:16px!important;transition:all .16s}
        .drawer{transition:transform .22s cubic-bezier(.4,0,.2,1)}
        [x-cloak]{display:none!important}
        .btn-gold{background:var(--primary);color:#fff;border:1px solid rgba(255,255,255,.2);transition:all .2s cubic-bezier(.4,0,.2,1)}
        .btn-gold:hover{background:var(--primary-dark);box-shadow:0 8px 20px -8px rgba(15,23,42,.24);transform:translateY(-1px)}
        .table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
        .input-premium{background:#f8fbff;border:1px solid #dbeafe;border-radius:0.75rem;transition:all .16s;box-shadow:inset 0 2px 4px 0 rgba(15,23,42,.02)}
        .input-premium:focus{background:#fff;border-color:var(--brand-2);box-shadow:0 0 0 4px color-mix(in srgb, var(--brand-2) 14%, transparent),inset 0 1px 2px 0 rgba(15,23,42,.02)}
        /* ── Dynamic theme utility classes ─────────────────────────────── */
        .text-brand { color: var(--brand) !important; }
        .bg-brand { background: var(--brand) !important; }
        .text-primary-theme { color: var(--primary) !important; }
        .bg-primary-theme { background: var(--primary) !important; }
        .bg-primary-pale { background: var(--primary-pale) !important; }
        .text-primary-text { color: var(--primary-text) !important; }
        .text-primary-dark { color: var(--primary-dark) !important; }
        .border-brand { border-color: var(--brand) !important; }
        .border-primary-pale { border-color: var(--primary-pale) !important; }
        .ring-brand { --tw-ring-color: var(--brand) !important; }
        .text-accent { color: var(--brand-2) !important; }
        .bg-accent { background: var(--brand-2) !important; }
        .divide-primary-pale > * + * { border-color: var(--primary-pale) !important; }
        .hover\:bg-primary-dark:hover { background: var(--primary-dark) !important; }
        .hover\:bg-primary-pale:hover { background: var(--primary-pale) !important; }
        .hover\:text-brand:hover { color: var(--brand) !important; }
        .from-primary { --tw-gradient-from: var(--primary) !important; }
        .to-primary-dark { --tw-gradient-to: var(--primary-dark) !important; }
        .from-primary-pale { --tw-gradient-from: var(--primary-pale) !important; }
        .to-primary-pale { --tw-gradient-to: var(--primary-pale) !important; }
        .peer:checked ~ .peer-checked\:border-brand { border-color: var(--brand) !important; }
        .peer:checked ~ .peer-checked\:bg-primary-pale { background: var(--primary-pale) !important; }
        .file\:bg-primary-pale::file-selector-button { background: var(--primary-pale) !important; }
        .file\:text-brand::file-selector-button { color: var(--brand) !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <script>
        window.createChart = function(context, config) {
            if (!context) return null;
            return new Chart(context, config);
        };
    </script>
    @stack('styles')
</head>
<body x-data="{drawerOpen:false}" class="bg-surface" @keydown.escape.window="drawerOpen=false">

{{-- Mobile menu scrim: only while drawer is open (avoids stray full-screen overlays) --}}
<div x-show="drawerOpen" x-cloak x-transition.opacity.duration.200ms
     @click="drawerOpen=false"
     class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

<aside x-bind:class="drawerOpen?'translate-x-0':'-translate-x-full'"
       class="drawer fixed inset-y-0 left-0 z-50 w-72 flex flex-col shadow-2xl lg:translate-x-0 lg:w-64"
       style="background:#0f172a;border-right:1px solid rgba(255,255,255,0.05)">

    <div class="flex items-center gap-3 px-5 py-6 border-b border-white/5">
        <div class="w-12 h-12 rounded-2xl flex-shrink-0 flex items-center justify-center" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12)">
            <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="{{ $schoolName ?? 'Logo' }}" class="w-8 h-8 object-contain">
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-white font-bold text-sm tracking-tight leading-tight">{{ $schoolName ?? 'School' }}</p>
            <p class="text-white/50 text-[11px] font-medium uppercase tracking-wider mt-0.5">{{ $schoolSubtitle ?? '' }}</p>
        </div>
        <button @click="drawerOpen=false" class="text-white/40 hover:text-white lg:hidden p-1">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <nav class="flex-1 px-3 py-5 space-y-0.5 overflow-y-auto">
        <p class="text-xs font-semibold text-yellow-300/30 uppercase tracking-widest px-3 mb-3">Main Menu</p>
        @php
            // Staff portal users (accountant/headmaster) have no auth()->user()
            // They get the admin menu filtered by their permissions
            $staffPortalNav = $staffPortalUser ?? null;
            $menuItems = (!$staffPortalNav && auth()->check() && auth()->user()->isLecturer())
                ? array_values(array_filter([
                    ['route' => 'lecturer.dashboard',            'icon' => 'fa-chart-pie',       'label' => 'Dashboard',    'show' => true],
                    ['route' => 'lecturer.attendance.create',    'icon' => 'fa-clipboard-check', 'label' => 'Attendance',     'show' => \App\Models\Setting::lecturerCan('attendance')],
                    ['route' => 'lecturer.class-register.index', 'icon' => 'fa-clipboard-list',  'label' => 'Class Register',  'show' => \App\Models\ClassTeacherAssignment::isClassTeacher(auth()->id() ?? 0)],
                    ['route' => 'lecturer.exams.index',          'icon' => 'fa-file-alt',        'label' => 'Exam Scores',  'show' => \App\Models\Setting::lecturerCan('exams')],
                    ['route' => 'lecturer.exam-questions.index', 'icon' => 'fa-file-upload',     'label' => 'Exam Questions', 'show' => true],
                    ['route' => 'lecturer.work-logs.index',      'icon' => 'fa-tasks',           'label' => 'Work Logs',    'show' => true],
                    ['route' => 'lecturer.student-reports.index','icon' => 'fa-file-signature', 'label' => 'Student Reports', 'show' => true],
                    ['route' => 'lecturer.timetable.index',      'icon' => 'fa-table-cells',     'label' => 'My Timetable', 'show' => true],
                    ['route' => 'lecturer.calendar.index',       'icon' => 'fa-calendar-days',   'label' => 'Calendar',     'show' => true],
                    ['route' => 'lecturer.scheme-of-learning.index','icon'=>'fa-book-open',       'label' => 'Scheme of Learning','show' => true],
                    ['route' => 'lecturer.class-performance.index','icon'=>'fa-chart-bar',        'label' => 'Class Report', 'show' => \App\Models\ClassTeacherAssignment::isClassTeacher(auth()->id() ?? 0)],
                    ['route' => 'lecturer.reports.index',        'icon' => 'fa-chart-bar',       'label' => 'Reports',      'show' => \App\Models\Setting::lecturerCan('reports')],
                    ['route' => 'lecturer.notifications.index',  'icon' => 'fa-bell',            'label' => 'Notifications','show' => \App\Models\Setting::lecturerCan('notifications')],
                    ['route' => 'lecturer.profile.edit',         'icon' => 'fa-user',            'label' => 'Profile',      'show' => \App\Models\Setting::lecturerCan('edit_profile')],
                ], fn($item) => $item['show']))
                : [
                    ['route'=>'admin.dashboard',                'icon'=>'fa-chart-pie',          'label'=>'Dashboard',                   'perm'=>'dashboard',      'module'=>null],
                    ['route'=>'admin.students.index',           'icon'=>'fa-user-graduate',      'label'=>$sidebarLabels['students'],     'perm'=>'students',       'module'=>'students'],
                    ['route'=>'admin.lecturers.index',          'icon'=>'fa-chalkboard-user',    'label'=>$sidebarLabels['teachers'],     'perm'=>'lecturers',      'module'=>'teachers'],
                    ['route'=>'admin.class-teachers.index',     'icon'=>'fa-user-tie',           'label'=>'Class Teachers',              'perm'=>'class_teachers', 'module'=>'teachers'],
                    ['route'=>'admin.work-monitoring.index',    'icon'=>'fa-tasks',              'label'=>'Teacher Work Logs',           'perm'=>'work_logs',      'module'=>'teachers'],
                    ['route'=>'admin.student-reports.index',    'icon'=>'fa-file-signature',     'label'=>'Student Reports',             'perm'=>'student_reports','module'=>'student_reports'],
                    ['route'=>'admin.staff-portal-users.index', 'icon'=>'fa-user-shield',        'label'=>'Staff Portals',               'perm'=>null,             'module'=>'staff_portal'],
                    ['route'=>'admin.programs.index',           'icon'=>'fa-graduation-cap',     'label'=>$sidebarLabels['programs'],    'perm'=>'programs',       'module'=>'programs'],
                    ['route'=>'admin.courses.index',            'icon'=>'fa-book-open',          'label'=>$sidebarLabels['courses'],     'perm'=>'courses',        'module'=>'courses'],
                    ['route'=>'admin.attendance.index',         'icon'=>'fa-clipboard-check',    'label'=>$sidebarLabels['attendance'],  'perm'=>'attendance',     'module'=>'attendance'],
                    ['route'=>'admin.academic-sessions.index',  'icon'=>'fa-calendar-alt',       'label'=>'Academic Sessions',           'perm'=>'academic_sessions','module'=>'academic_sessions'],
                    ['route'=>'admin.timetable.index',          'icon'=>'fa-table-cells',        'label'=>'Timetable',                  'perm'=>'timetable',      'module'=>'timetable'],
                    ['route'=>'admin.scheme-of-learning.index', 'icon'=>'fa-book-open',          'label'=>'Scheme of Learning',          'perm'=>'scheme_of_learning','module'=>'scheme_of_learning'],
                    ['route'=>'admin.calendar.index',           'icon'=>'fa-calendar-days',      'label'=>'School Calendar',            'perm'=>'calendar',       'module'=>'calendar'],
                    ['route'=>'admin.exams.index',              'icon'=>'fa-file-alt',           'label'=>$sidebarLabels['exams'],       'perm'=>'exams',          'module'=>'exams'],
                    ['route'=>'admin.exams.sheet',              'icon'=>'fa-table',              'label'=>'Exams Sheet',                'perm'=>'exams',          'module'=>'exams'],
                    ['route'=>'admin.exam-questions.index',     'icon'=>'fa-file-upload',        'label'=>'Exam Questions',              'perm'=>'exam_questions', 'module'=>'exams'],
                    ['route'=>'admin.fees.index',               'icon'=>'fa-coins',              'label'=>$sidebarLabels['fees'],        'perm'=>'fees',           'module'=>'fees'],
                    ['route'=>'admin.fees.term.index',          'icon'=>'fa-file-invoice-dollar', 'label'=>'Term Ledger',                'perm'=>'fees',           'module'=>'fees'],
                    ['route'=>'admin.fees.reminders.index',     'icon'=>'fa-bell',               'label'=>'Fee Reminders',              'perm'=>'fees',           'module'=>'fee_reminders'],
                    ['route'=>'admin.daily-fees.index',         'icon'=>'fa-calendar-check',     'label'=>'Daily Fees',                 'perm'=>'daily_fees',     'module'=>'daily_fees'],
                    ['route'=>'admin.reports.index',            'icon'=>'fa-chart-bar',          'label'=>$sidebarLabels['reports'],    'perm'=>'reports',        'module'=>'reports'],
                    ['route'=>'admin.notifications.index',      'icon'=>'fa-bell',               'label'=>'Notifications',              'perm'=>'notifications',  'module'=>'notifications'],
                    ['route'=>'admin.portal-settings',          'icon'=>'fa-graduation-cap',     'label'=>$sidebarLabels['portals'],    'perm'=>null,             'module'=>'portals'],
                    ['route'=>'admin.settings',                 'icon'=>'fa-sliders-h',          'label'=>$sidebarLabels['settings'],   'perm'=>null,             'module'=>null],
                    ['route'=>'admin.guide',                    'icon'=>'fa-question-circle',    'label'=>$sidebarLabels['guide'],      'perm'=>null,             'module'=>null],
                    ['route'=>'admin.subscription.show',        'icon'=>'fa-shield-alt',         'label'=>'Subscription',              'perm'=>null,             'module'=>null],
                    ['route'=>'admin.profile',                  'icon'=>'fa-user-circle',        'label'=>'My Profile',                'perm'=>'__always__',     'module'=>null],
                ];

            // If it's a staff portal user, filter out the ones they don't have permission for.
            // Items with 'perm' => null are admin-only, they shouldn't see them at all.
            if ($staffPortalNav) {
                $menuItems = array_filter($menuItems, function($item) use ($staffPortalNav) {
                    if ($item['perm'] === '__always__') return true;
                    if ($item['perm'] === null) return false;
                    return $staffPortalNav->can_access($item['perm']) || ($item['perm'] === 'dashboard' && count($staffPortalNav->permissions) > 0);
                });
            }
        @endphp
        @foreach($menuItems as $item)
        @php
            // Skip items whose module is disabled for this tenant
            $moduleKey = $item['module'] ?? null;
            if ($moduleKey && isset($enabledModules) && $enabledModules !== null && !in_array($moduleKey, $enabledModules, true)) continue;
        @endphp
        <a href="{{ route($item['route']) }}" @click="drawerOpen=false"
           class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-xl text-white/65 text-sm font-medium
                  {{ request()->routeIs(str_replace('.index','',$item['route']).'*')||request()->routeIs($item['route'])?'active':'' }}">
            <i class="fas {{ $item['icon'] }} w-5 text-center text-base flex-shrink-0"></i>
            <span>{{ $item['label'] }}</span>
        </a>
        @endforeach

        @if(!$staffPortalNav && auth()->check() && auth()->user()->isSuperAdmin())
        <a href="{{ route('admin.branches.index') }}" @click="drawerOpen=false"
           class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-xl text-white/65 text-sm font-medium
                  {{ request()->routeIs('admin.branches*') ? 'active' : '' }}">
            <i class="fas fa-code-branch w-5 text-center text-base flex-shrink-0"></i>
            <span>Branches</span>
        </a>
        <a href="{{ route('admin.admins.index') }}" @click="drawerOpen=false"
           class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-xl text-white/65 text-sm font-medium
                  {{ request()->routeIs('admin.admins*') ? 'active' : '' }}">
            <i class="fas fa-users-cog w-5 text-center text-base flex-shrink-0"></i>
            <span>Admins</span>
        </a>
        @endif

        {{-- ── Website CMS ─────────────────────────────────────────── --}}
        @if(!$staffPortalNav && auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isBranchAdmin()))
        @php $websiteEnabled = !isset($enabledModules) || $enabledModules === null || in_array('website', $enabledModules ?? [], true); @endphp
        @if($websiteEnabled)
        <p class="text-white/20 text-[10px] font-semibold uppercase tracking-widest px-3 mt-4 mb-1">Website</p>
        <a href="{{ route('admin.website.dashboard') }}" @click="drawerOpen=false"
           class="sidebar-link flex items-center gap-3 px-3 py-3 rounded-xl text-white/65 text-sm font-medium
                  {{ request()->routeIs('admin.website.*') ? 'active' : '' }}">
            <i class="fas fa-globe w-5 text-center text-base flex-shrink-0"></i>
            <span>Website CMS</span>
        </a>
        @if(request()->routeIs('admin.website.*'))
        <div class="pl-8 space-y-0.5">
            <a href="{{ route('admin.website.blog.index') }}" @click="drawerOpen=false"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-white/50 text-xs font-medium transition-all hover:text-white/80 {{ request()->routeIs('admin.website.blog*') ? 'text-white/90 bg-white/5' : '' }}">
                <i class="fas fa-newspaper w-4 text-center"></i> Blog
            </a>
            <a href="{{ route('admin.website.events.index') }}" @click="drawerOpen=false"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-white/50 text-xs font-medium transition-all hover:text-white/80 {{ request()->routeIs('admin.website.events*') ? 'text-white/90 bg-white/5' : '' }}">
                <i class="fas fa-calendar w-4 text-center"></i> Events
            </a>
            <a href="{{ route('admin.website.gallery.index') }}" @click="drawerOpen=false"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-white/50 text-xs font-medium transition-all hover:text-white/80 {{ request()->routeIs('admin.website.gallery*') ? 'text-white/90 bg-white/5' : '' }}">
                <i class="fas fa-images w-4 text-center"></i> Gallery
            </a>
            <a href="{{ route('admin.website.staff.index') }}" @click="drawerOpen=false"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-white/50 text-xs font-medium transition-all hover:text-white/80 {{ request()->routeIs('admin.website.staff*') ? 'text-white/90 bg-white/5' : '' }}">
                <i class="fas fa-users w-4 text-center"></i> Staff
            </a>
            <a href="{{ route('admin.website.applications.index') }}" @click="drawerOpen=false"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-white/50 text-xs font-medium transition-all hover:text-white/80 {{ request()->routeIs('admin.website.applications*') ? 'text-white/90 bg-white/5' : '' }}">
                <i class="fas fa-file-alt w-4 text-center"></i> Applications
            </a>
            <a href="{{ route('admin.website.messages.index') }}" @click="drawerOpen=false"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-white/50 text-xs font-medium transition-all hover:text-white/80 {{ request()->routeIs('admin.website.messages*') ? 'text-white/90 bg-white/5' : '' }}">
                <i class="fas fa-envelope w-4 text-center"></i> Messages
            </a>
            <a href="{{ route('admin.website.site-settings') }}" @click="drawerOpen=false"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-white/50 text-xs font-medium transition-all hover:text-white/80 {{ request()->routeIs('admin.website.site-settings*') ? 'text-white/90 bg-white/5' : '' }}">
                <i class="fas fa-cog w-4 text-center"></i> Site Settings
            </a>
        </div>
        @endif
        @endif
        @endif
    </nav>

    <div class="px-4 py-5 border-t border-white/5 relative overflow-hidden">
        <div class="absolute inset-0 bg-black/10 pointer-events-none"></div>
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-2xl relative z-10" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08)">
            <div class="w-10 h-10 rounded-xl bg-gold-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background:#D4A017;box-shadow:0 4px 10px -2px rgba(212,160,23,.24)">
                {{ strtoupper(substr(($staffPortalUser ?? null)?->full_name ?? auth()->user()->full_name ?? 'A', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-bold truncate tracking-tight">
                    {{ ($staffPortalUser ?? null)?->full_name ?? auth()->user()->full_name ?? 'Admin' }}
                </p>
                <p class="text-white/40 text-[11px] font-medium uppercase tracking-wider mt-0.5">
                    @if($staffPortalUser ?? null)
                        {{ $staffPortalUser->role_label }}
                    @elseif(auth()->check())
                        {{ auth()->user()->isLecturer() ? 'Teacher' : 'Administrator' }}
                    @endif
                </p>
            </div>
            @if($staffPortalUser ?? null)
            <form method="POST" action="{{ route('staff-portal.logout') }}">
                @csrf
                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 transition-colors" title="Logout">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 transition-colors" title="Logout">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
</aside>

<div class="lg:pl-64 min-h-screen flex flex-col relative z-0">
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200/70 shadow-[0_2px_12px_-4px_rgba(15,23,42,0.12)]">
        <div class="flex items-center justify-between px-4 py-3 lg:px-6">
            <div class="flex items-center gap-3">
                <button @click="drawerOpen=true" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl bg-gold-50 text-gold-700 active:bg-gold-100" style="background:#fef9c3;color:#78520a">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="text-base font-bold text-ink leading-tight">@yield('title','Dashboard')</h1>
                    <p class="text-xs text-gray-400 hidden sm:block">@yield('subtitle', ($schoolName ?? 'School') . ' — ' . (($staffPortalUser ?? null) ? ($staffPortalUser->role_label.' Portal') : (auth()->check() && auth()->user()->isLecturer() ? 'Teacher Overview' : 'Admin Overview')))</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="hidden md:flex items-center gap-1.5 text-xs text-gray-400 bg-yellow-50 px-3 py-1.5 rounded-lg border border-yellow-100">
                    <i class="fas fa-calendar-alt" style="color:#D4A017"></i>
                    {{ now()->format('M d, Y') }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-xs font-semibold transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="flex-1 p-4 lg:p-6 pb-24 lg:pb-10">

        @if(session('success'))
        <div x-data="{show:true}" x-show="show" x-cloak x-init="setTimeout(()=>show=false,5000)"
             x-transition:leave="transition-opacity duration-100" x-transition:leave-end="opacity-0"
             class="flex items-center gap-3 p-4 mb-5 rounded-2xl text-sm font-medium border"
             style="background:#fef9c3;border-color:#D4A017;color:#78520a">
            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background:#D4A017">
                <i class="fas fa-check text-white text-xs"></i>
            </div>
            <span class="flex-1">{{ session('success') }}</span>
            <button @click="show=false" class="text-xs opacity-70 hover:opacity-100 font-bold ml-auto">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div x-data="{show:true}" x-show="show" x-cloak
             class="flex items-center gap-3 p-4 mb-5 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-medium">
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation text-red-600 text-xs"></i>
            </div>
            <span class="flex-1 font-semibold">{{ session('error') }}</span>
            <button @click="show=false" class="text-xs text-red-600 hover:text-red-900 font-bold ml-auto px-2 py-1">&times;</button>
        </div>
        @endif

        @if($errors->any())
        <div class="flex items-start gap-3 p-4 mb-5 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm">
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fas fa-exclamation text-red-600 text-xs"></i>
            </div>
            <div>
                <p class="font-semibold mb-1">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
        @endif

        @yield('content')
    </main>

    {{-- Powered-by footer — desktop only (mobile uses bottom-nav) --}}
    <footer class="hidden lg:block border-t border-slate-100 bg-white/60 px-6 py-3">
        <p class="text-center text-[11px] text-slate-400">
            &copy; {{ date('Y') }} {{ $schoolName ?? 'School' }}
            &nbsp;&middot;&nbsp;
            Powered by <a href="https://saningtech.com" target="_blank" rel="noopener" class="font-semibold text-slate-500 hover:text-[var(--brand)] transition-colors">SaningTech Solutions</a>
        </p>
    </footer>

</div>

<nav class="bottom-nav fixed bottom-0 left-0 right-0 z-30 lg:hidden">
    <div class="flex items-center justify-around px-2 pt-2 pb-1">
        @php
            $bottomItems = (!$staffPortalNav && auth()->check() && auth()->user()->isLecturer())
                ? array_values(array_filter([
                    ['route'=>'lecturer.dashboard',           'icon'=>'fa-chart-pie',       'label'=>'Home',       'show'=>true],
                    ['route'=>'lecturer.attendance.create',   'icon'=>'fa-clipboard-check', 'label'=>'Attendance', 'show'=>\App\Models\Setting::lecturerCan('attendance')],
                    ['route'=>'lecturer.exams.index',         'icon'=>'fa-file-alt',        'label'=>'Exams',      'show'=>\App\Models\Setting::lecturerCan('exams')],
                    ['route'=>'lecturer.reports.index',       'icon'=>'fa-chart-bar',       'label'=>'Reports',    'show'=>\App\Models\Setting::lecturerCan('reports')],
                ], fn($item) => $item['show']))
                : [
                    ['route'=>'admin.dashboard',       'icon'=>'fa-chart-pie',     'label'=>'Home'],
                    ['route'=>'admin.students.index',   'icon'=>'fa-user-graduate', 'label'=>'Students'],
                    ['route'=>'admin.exams.index',      'icon'=>'fa-file-alt',      'label'=>'Exams'],
                    ['route'=>'admin.fees.index',       'icon'=>'fa-coins',         'label'=>'Fees'],
                ];
        @endphp
        @foreach($bottomItems as $item)
        <a href="{{ route($item['route']) }}"
           class="bottom-nav-item flex flex-col items-center px-3 py-1 rounded-xl
                  {{ request()->routeIs(str_replace('.index','',$item['route']).'*')||request()->routeIs($item['route'])?'active':'' }}">
            <i class="fas {{ $item['icon'] }} nav-icon"></i>
            <span class="nav-label">{{ $item['label'] }}</span>
        </a>
        @endforeach
        <button @click="drawerOpen=true" class="bottom-nav-item flex flex-col items-center px-3 py-1 rounded-xl">
            <i class="fas fa-grid-2 nav-icon"></i>
            <span class="nav-label">More</span>
        </button>
    </div>
</nav>

@stack('scripts')
</body>
</html>
