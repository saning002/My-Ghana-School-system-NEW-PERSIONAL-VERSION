<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>@yield('title','Portal') — {{ \App\Models\Setting::get('school_name',config('app.name')) }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <style>
        body { font-family:'Inter',sans-serif; background:#f8fafc; }
        .sidebar-link { transition:all .12s; border-left:3px solid transparent; }
        .sidebar-link:hover { background:rgba(255,255,255,.1); color:#fff; border-left-color:rgba(255,255,255,.3); transform:translateX(3px); }
        .sidebar-link.active { background:rgba(255,255,255,.12); color:#fff; border-left-color:#f59e0b; }
        .card { background:#fff; border-radius:1rem; border:1px solid rgba(148,163,184,.15); box-shadow:0 4px 20px -8px rgba(15,23,42,.12); }
        ::-webkit-scrollbar{width:4px} ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px}
    </style>
    @stack('head')
</head>
<body x-data="{sidebarOpen:false}" @keydown.escape.window="sidebarOpen=false">

{{-- Mobile scrim --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
     class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

{{-- Sidebar --}}
@php
    // Always load fresh from session — guaranteed to work regardless of middleware
    $sessionUserId = session('staff_portal_user_id');
    $staffUser = $sessionUserId
        ? \App\Models\StaffPortalUser::with('permissions')->find($sessionUserId)
        : ($staffPortalUser ?? null);

    $perms = $staffUser
        ? ($staffUser->relationLoaded('permissions')
            ? $staffUser->permissions->pluck('permission')->toArray()
            : \App\Models\StaffPortalPermission::where('staff_portal_user_id', $staffUser->id)->pluck('permission')->toArray())
        : [];

    $branchId   = $staffUser?->church_branch_id;
    $roleColors = ['accountant'=>['#d97706','#b45309'],'headmaster'=>['#4f46e5','#6d28d9'],'headteacher'=>['#0891b2','#0e7490'],'deputy'=>['#059669','#047857'],'secretary'=>['#db2777','#be185d']];
    $rc = $roleColors[$staffUser?->role ?? 'headmaster'] ?? ['#4f46e5','#6d28d9'];
    $navLinks = [
        ['route'=>'staff-portal.dashboard',         'icon'=>'fa-chart-pie',         'label'=>'Dashboard',           'perm'=>null],
        ['route'=>'staff-portal.student-reports',    'icon'=>'fa-file-lines',         'label'=>'Student Reports',     'perm'=>'student_reports'],
        ['route'=>'staff-portal.students',           'icon'=>'fa-user-graduate',     'label'=>'Students',            'perm'=>'students'],
        ['route'=>'staff-portal.attendance',         'icon'=>'fa-clipboard-check',   'label'=>'Attendance',          'perm'=>'attendance'],
        ['route'=>'staff-portal.exams',              'icon'=>'fa-file-alt',          'label'=>'Exams & Scores',      'perm'=>'exams'],
        ['route'=>'staff-portal.fees',               'icon'=>'fa-coins',             'label'=>'Fees',                'perm'=>'fees'],
        ['route'=>'staff-portal.daily-fees',         'icon'=>'fa-calendar-check',    'label'=>'Daily Fees',          'perm'=>'daily_fees'],
        ['route'=>'staff-portal.reports',            'icon'=>'fa-chart-bar',         'label'=>'Reports',             'perm'=>'reports'],
        ['route'=>'staff-portal.programs',           'icon'=>'fa-graduation-cap',    'label'=>'Programs',            'perm'=>'programs'],
        ['route'=>'staff-portal.courses',            'icon'=>'fa-book-open',         'label'=>'Courses',             'perm'=>'courses'],
        ['route'=>'staff-portal.lecturers',          'icon'=>'fa-chalkboard-teacher','label'=>'Lecturers',           'perm'=>'lecturers'],
        ['route'=>'staff-portal.class-teachers',     'icon'=>'fa-user-tie',          'label'=>'Class Teachers',      'perm'=>'class_teachers'],
        ['url'=>'/admin/scheme-of-learning',         'icon'=>'fa-book',              'label'=>'Scheme of Learning',  'perm'=>'scheme_of_learning'],
        ['url'=>'/admin/work-monitoring',            'icon'=>'fa-clipboard-list',    'label'=>'Work Logs',           'perm'=>'work_logs'],
        ['url'=>'/admin/exam-questions',             'icon'=>'fa-question-circle',   'label'=>'Exam Questions',      'perm'=>'exam_questions'],
        ['route'=>'staff-portal.timetable',          'icon'=>'fa-table-cells',       'label'=>'Timetable',           'perm'=>'timetable'],
        ['route'=>'staff-portal.calendar',           'icon'=>'fa-calendar-days',     'label'=>'Calendar',            'perm'=>'calendar'],
        ['route'=>'staff-portal.notifications',      'icon'=>'fa-bell',              'label'=>'Notifications',       'perm'=>'notifications'],
        ['route'=>'staff-portal.promotions',         'icon'=>'fa-arrow-up',          'label'=>'Promotions',          'perm'=>'promotions'],
        ['route'=>'staff-portal.academic-sessions',  'icon'=>'fa-layer-group',       'label'=>'Academic Sessions',   'perm'=>'academic_sessions'],
        ['route'=>'staff-portal.branches',           'icon'=>'fa-code-branch',       'label'=>'Branches',            'perm'=>'branches'],
        ['route'=>'staff-portal.settings',           'icon'=>'fa-cog',               'label'=>'Settings',            'perm'=>'settings'],
        ['route'=>'staff-portal.profile',            'icon'=>'fa-user-circle',       'label'=>'My Profile',          'perm'=>'__always__'],
    ];
@endphp
<aside :class="sidebarOpen?'translate-x-0':'-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col shadow-2xl lg:translate-x-0 transition-transform duration-200 min-h-0"
       style="background:linear-gradient(160deg,{{ $rc[0] }} 0%,{{ $rc[1] }} 100%);">

    {{-- Brand --}}
    <div class="px-5 py-6 border-b border-white/10 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 shrink-0">
            <i class="fas fa-user-shield text-white"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-white font-extrabold text-sm truncate leading-tight">{{ \App\Models\Setting::get('school_name',config('app.name')) }}</p>
            <p class="text-white/50 text-[11px] font-semibold uppercase tracking-wider mt-0.5">{{ $staffUser?->role_label ?? 'Staff' }} Portal</p>
        </div>
        <button @click="sidebarOpen=false" class="text-white/50 hover:text-white lg:hidden"><i class="fas fa-times"></i></button>
    </div>

    {{-- Branch badge --}}
    @if($branchId)
    <div class="px-5 py-2">
        <div class="flex items-center gap-2 rounded-xl bg-white/10 border border-white/15 px-3 py-1.5 text-[11px] font-bold text-white/80">
            <i class="fas fa-code-branch text-[10px]"></i>
            @php $branch = \App\Models\ChurchBranch::find($branchId); @endphp
            {{ $branch?->name ?? 'Branch' }}
        </div>
    </div>
    @endif

    {{-- Nav --}}
    <nav class="flex-1 px-2 py-2 overflow-y-auto" style="scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.2) transparent;">
        @foreach($navLinks as $link)
        @if($link['perm'] === null || $link['perm'] === '__always__' || in_array($link['perm'], $perms))
        @php $href = isset($link['url']) ? url($link['url']) : route($link['route']); @endphp
        <a href="{{ $href }}" @click="sidebarOpen=false"
           class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-xl text-white/70 text-xs font-medium mb-0.5
                  {{ request()->is(ltrim($link['url'] ?? '', '/') . '*') || (isset($link['route']) && (request()->routeIs(str_replace(['.index','.dashboard'],'',$link['route']).'*') || request()->routeIs($link['route']))) ? 'active' : '' }}">
            <i class="fas {{ $link['icon'] }} w-4 text-center text-sm shrink-0"></i>
            <span>{{ $link['label'] }}</span>
        </a>
        @endif
        @endforeach
    </nav>

    {{-- User footer --}}
    <div class="px-4 py-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/20 text-white text-sm font-extrabold">
                {{ strtoupper(substr($staffUser?->full_name??'?',0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-bold truncate">{{ $staffUser?->full_name }}</p>
                <p class="text-white/40 text-[10px] uppercase tracking-wider">{{ $staffUser?->role_label }}</p>
            </div>
            <form method="POST" action="{{ route('staff-portal.logout') }}">
                @csrf
                <button class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-500/15 hover:bg-red-500/25 text-red-300 transition-colors">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Main --}}
<div class="lg:pl-64 min-h-screen flex flex-col">
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm">
        <div class="flex items-center justify-between px-4 py-3 lg:px-6">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen=true" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 leading-tight">@yield('title','Dashboard')</h1>
                    <p class="text-xs text-slate-400 hidden sm:block">@yield('subtitle','')</p>
                </div>
            </div>
            <span class="hidden md:flex items-center gap-1.5 text-xs text-slate-400 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                <i class="fas fa-calendar-alt text-slate-400"></i>{{ now()->format('M d, Y') }}
            </span>
        </div>
    </header>

    <main class="flex-1 p-4 lg:p-6 pb-8">
        @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 mb-5 text-sm font-semibold text-emerald-800 flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3 mb-5 text-sm font-semibold text-red-800 flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-red-500"></i>{{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3 mb-5 text-sm text-red-800">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        @yield('content')
    </main>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
