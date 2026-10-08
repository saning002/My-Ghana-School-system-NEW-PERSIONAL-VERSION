<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $staffUser->role_label }} Dashboard — {{ \App\Models\Setting::get('school_name', config('app.name')) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="min-h-screen bg-slate-50">

{{-- Top nav --}}
<header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
    <div class="flex items-center gap-3">
        <div class="flex h-8 w-8 items-center justify-center rounded-xl" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
            <i class="fas fa-user-shield text-white text-xs"></i>
        </div>
        <div>
            <p class="text-sm font-extrabold text-slate-900">{{ \App\Models\Setting::get('school_name', config('app.name')) }}</p>
            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">{{ $staffUser->role_label }}</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-sm font-bold text-slate-700 hidden sm:block">{{ $staffUser->full_name }}</span>
        <form method="POST" action="{{ route('staff-portal.logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-xs font-bold transition-colors">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</header>

<main class="max-w-6xl mx-auto px-4 lg:px-6 py-6 space-y-6">

    {{-- Welcome hero --}}
    <div class="rounded-3xl relative overflow-hidden p-6 lg:p-8 text-white"
         style="background:linear-gradient(135deg,#1e1b4b 0%,#312e81 35%,#4c1d95 65%,#7c3aed 100%)">
        <div class="pointer-events-none absolute -top-12 -right-12 h-48 w-48 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-300 mb-1">{{ $staffUser->role_label }}</p>
                <h1 class="text-2xl font-extrabold">Welcome, {{ $staffUser->full_name }}</h1>
                @if($activePeriod)
                <div class="inline-flex items-center gap-1.5 rounded-full border border-yellow-400/40 bg-yellow-400/15 px-3 py-1 text-xs font-bold text-yellow-200 mt-2">
                    <i class="fas fa-calendar-check text-yellow-300 text-[10px]"></i>
                    {{ $activePeriod->full_label }}
                </div>
                @endif
            </div>
            <div class="text-right text-xs text-white/50">
                {{ now()->format('l, F j Y') }}
            </div>
        </div>
    </div>

    {{-- Stats (only if permitted) --}}
    @if(isset($stats) && count($stats))
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @if(isset($stats['active_students']))
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 mb-3">
                <i class="fas fa-user-graduate text-blue-600"></i>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ $stats['active_students'] }}</p>
            <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wide">Active Students</p>
        </div>
        @endif
        @if(isset($stats['fees']))
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 mb-3">
                <i class="fas fa-coins text-emerald-600"></i>
            </div>
            <p class="text-lg font-extrabold text-emerald-700">GH₵ {{ number_format($stats['fees']['collected'] ?? 0, 0) }}</p>
            <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wide">Collected</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-red-50 p-5 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 mb-3">
                <i class="fas fa-exclamation-circle text-red-600"></i>
            </div>
            <p class="text-lg font-extrabold text-red-700">GH₵ {{ number_format($stats['fees']['outstanding'] ?? 0, 0) }}</p>
            <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wide">Outstanding</p>
        </div>
        @endif
        @if(isset($stats['attendance_rate']))
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 mb-3">
                <i class="fas fa-clipboard-check text-purple-600"></i>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ $stats['attendance_rate'] }}%</p>
            <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wide">Attendance Rate</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Quick links based on permissions --}}
    @php
    $links = [
        'students'   => ['href'=>route('admin.students.index'),    'icon'=>'fa-user-graduate',    'label'=>'Students',       'bg'=>'#fef9c3','ic'=>'#92680a'],
        'attendance' => ['href'=>route('admin.attendance.index'),  'icon'=>'fa-clipboard-check',  'label'=>'Attendance',     'bg'=>'#dcfce7','ic'=>'#166534'],
        'exams'      => ['href'=>route('admin.exams.index'),       'icon'=>'fa-file-alt',          'label'=>'Exams & Scores', 'bg'=>'#dbeafe','ic'=>'#1e40af'],
        'fees'       => ['href'=>route('admin.fees.index'),        'icon'=>'fa-coins',             'label'=>'Fees',           'bg'=>'#fef3c7','ic'=>'#92400e'],
        'daily_fees' => ['href'=>route('admin.daily-fees.index'),  'icon'=>'fa-calendar-check',   'label'=>'Daily Fees',     'bg'=>'#f0fdf4','ic'=>'#166534'],
        'reports'    => ['href'=>route('admin.reports.hub'),        'icon'=>'fa-chart-bar',         'label'=>'Reports',        'bg'=>'#fce7f3','ic'=>'#9d174d'],
        'timetable'  => ['href'=>route('admin.timetable.index'),   'icon'=>'fa-table-cells',       'label'=>'Timetable',      'bg'=>'#f5f3ff','ic'=>'#6d28d9'],
        'calendar'   => ['href'=>route('admin.calendar.index'),    'icon'=>'fa-calendar-days',     'label'=>'Calendar',       'bg'=>'#eff6ff','ic'=>'#1d4ed8'],
        'programs'   => ['href'=>route('admin.programs.index'),    'icon'=>'fa-graduation-cap',    'label'=>'Programs',       'bg'=>'#fdf2f8','ic'=>'#9d174d'],
        'courses'    => ['href'=>route('admin.courses.index'),     'icon'=>'fa-book-open',         'label'=>'Courses',        'bg'=>'#ecfdf5','ic'=>'#065f46'],
    ];
    $allowedLinks = array_filter($links, fn($k) => in_array($k, $perms), ARRAY_FILTER_USE_KEY);
    @endphp

    @if(count($allowedLinks))
    <div>
        <h2 class="text-sm font-extrabold text-slate-800 mb-4">Quick Access</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($allowedLinks as $key => $a)            <a href="{{ $a['href'] }}"
               class="group rounded-2xl p-4 flex flex-col items-center gap-3 transition-all hover:-translate-y-1 hover:shadow-lg border border-slate-100 bg-white">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform"
                     style="background:{{ $a['bg'] }}">
                    <i class="fas {{ $a['icon'] }} text-lg" style="color:{{ $a['ic'] }}"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 text-center leading-tight">{{ $a['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @else
    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
        <i class="fas fa-lock text-4xl text-slate-200 block mb-3"></i>
        <p class="text-sm font-semibold text-slate-400">No sections assigned yet.</p>
        <p class="text-xs text-slate-400 mt-1">Contact your administrator to assign permissions to your account.</p>
    </div>
    @endif

</main>
</body>
</html>
