@extends('layouts.app')
@section('title', 'Portals Management')
@section('subtitle', 'Manage student and teacher portal access')

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- Status Banners --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Student Banner --}}
        <div class="rounded-2xl p-5 flex items-center gap-4 shadow-sm"
             style="background:{{ $portalOpen ? 'linear-gradient(135deg,#16a34a,#22c55e)' : 'linear-gradient(135deg,#dc2626,#ef4444)' }}">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 bg-white/20">
                <i class="fas {{ $portalOpen ? 'fa-lock-open' : 'fa-lock' }} text-white text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-white font-black text-base truncate">
                    Student Portal: <span class="underline">{{ $portalOpen ? 'OPEN' : 'CLOSED' }}</span>
                </h2>
                <p class="text-white/80 text-xs mt-0.5">
                    {{ $portalOpen ? 'Students can log in & access their portal.' : 'Student login is currently blocked.' }}
                </p>
            </div>
        </div>

        {{-- Teacher Banner --}}
        <div class="rounded-2xl p-5 flex items-center gap-4 shadow-sm"
             style="background:{{ $teachersPortalOpen ? 'linear-gradient(135deg,#9333ea,#a855f7)' : 'linear-gradient(135deg,#dc2626,#ef4444)' }}">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 bg-white/20">
                <i class="fas {{ $teachersPortalOpen ? 'fa-lock-open' : 'fa-lock' }} text-white text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-white font-black text-base truncate">
                    Teachers Portal: <span class="underline">{{ $teachersPortalOpen ? 'OPEN' : 'CLOSED' }}</span>
                </h2>
                <p class="text-white/80 text-xs mt-0.5">
                    {{ $teachersPortalOpen ? 'Teachers can log in & enter scores.' : 'Teacher login is currently blocked.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Control Form --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-yellow-50" style="background:#fef9c3">
            <h3 class="text-sm font-bold" style="color:#78520a">
                <i class="fas fa-sliders-h mr-2"></i>Portal Access Control
            </h3>
        </div>
        <form method="POST" action="{{ route('admin.portal-settings.toggle') }}" class="px-6 py-6 space-y-8">
            @csrf

            {{-- ── 1. Student Portal Section ── --}}
            <div class="space-y-4 pb-6 border-b border-gray-100">
                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-user-graduate text-blue-600"></i> Student Portal Access
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="portal_access" value="1"
                               {{ $portalOpen ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all
                                    peer-checked:border-green-500 peer-checked:bg-green-50 border-gray-200 bg-white">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                 style="background:#dcfce7">
                                <i class="fas fa-lock-open text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">Open Student Portal</p>
                                <p class="text-xs text-gray-500">Students can log in</p>
                            </div>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="portal_access" value="0"
                               {{ !$portalOpen ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all
                                    peer-checked:border-red-500 peer-checked:bg-red-50 border-gray-200 bg-white">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                 style="background:#fee2e2">
                                <i class="fas fa-lock text-red-600"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">Close Student Portal</p>
                                <p class="text-xs text-gray-500">Block all student access</p>
                            </div>
                        </div>
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Message shown to students when portal is closed
                    </label>
                    <textarea name="portal_message" rows="2"
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white resize-none"
                              placeholder="e.g. The student portal is closed for maintenance. Please check back later.">{{ $portalMessage }}</textarea>
                </div>
            </div>

            {{-- ── 2. Teachers Portal Section ── --}}
            <div class="space-y-4">
                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chalkboard-user text-purple-600"></i> Teachers Portal Access
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="teachers_portal_access" value="1"
                               {{ $teachersPortalOpen ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all
                                    peer-checked:border-purple-500 peer-checked:bg-purple-50 border-gray-200 bg-white">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                 style="background:#f3e8ff">
                                <i class="fas fa-lock-open text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">Open Teachers Portal</p>
                                <p class="text-xs text-gray-500">Teachers can log in</p>
                            </div>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="teachers_portal_access" value="0"
                               {{ !$teachersPortalOpen ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all
                                    peer-checked:border-red-500 peer-checked:bg-red-50 border-gray-200 bg-white">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                 style="background:#fee2e2">
                                <i class="fas fa-lock text-red-600"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">Close Teachers Portal</p>
                                <p class="text-xs text-gray-500">Block all teacher access</p>
                            </div>
                        </div>
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Message shown to teachers when portal is closed
                    </label>
                    <textarea name="teachers_portal_message" rows="2"
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white resize-none"
                              placeholder="e.g. The teacher portal is closed for score entry compilation.">{{ $teachersPortalMessage }}</textarea>
                </div>
            </div>

            {{-- ── 3. Teacher Feature Permissions ── --}}
            <div class="space-y-4 pt-6 border-t border-gray-100">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-toggle-on text-emerald-600"></i> Teacher Feature Permissions
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            All permissions are <strong class="text-emerald-600">ON by default</strong>.
                            Uncheck to disable a feature for all teachers. Individual teachers cannot override these.
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold px-3 py-1 shrink-0">
                        <i class="fas fa-shield-check text-[10px]"></i> Default: All ON
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($teacherPermissions as $key => $label)
                    @php $isOn = $teacherPermValues[$key] ?? true; @endphp
                    <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all
                                  {{ $isOn ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 bg-white' }}
                                  hover:border-purple-300">
                        <input type="checkbox"
                               name="lecturer_can_{{ $key }}"
                               value="1"
                               {{ $isOn ? 'checked' : '' }}
                               class="w-4 h-4 rounded accent-purple-600 shrink-0"
                               onchange="this.closest('label').classList.toggle('border-emerald-300', this.checked);
                                         this.closest('label').classList.toggle('bg-emerald-50', this.checked);
                                         this.closest('label').classList.toggle('border-gray-200', !this.checked);
                                         this.closest('label').classList.toggle('bg-white', !this.checked);">
                        <div>
                            <p class="text-sm font-bold text-gray-800">{{ $label }}</p>
                            <p class="text-[10px] font-semibold mt-0.5 {{ $isOn ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $isOn ? '✅ Currently ON' : '❌ Currently OFF' }}
                            </p>
                        </div>
                    </label>
                    @endforeach
                </div>
                <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800">
                    <i class="fas fa-info-circle mr-1 text-amber-500"></i>
                    <strong>Note:</strong> These permissions control what features are visible to ALL teachers.
                    To give or restrict permissions for a specific staff member (Accountant, Headmaster etc.),
                    use <a href="{{ route('admin.staff-portal-users.index') }}" class="underline font-bold text-amber-700">Staff Portal Accounts</a>.
                </div>
            </div>

            <button type="submit" class="btn-gold w-full py-3 rounded-xl text-sm font-bold shadow-sm">
                <i class="fas fa-save mr-2"></i> Save Portal Settings
            </button>
        </form>
    </div>

    {{-- ── Report Card Orientation ────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100" style="background:#fef9c3">
            <h3 class="text-sm font-bold" style="color:#78520a">
                <i class="fas fa-file-alt mr-2"></i>Report Card Settings
            </h3>
        </div>
        <form method="POST" action="{{ route('admin.portal-settings.toggle') }}" class="px-6 py-6 space-y-4">
            @csrf
            {{-- Keep other fields so they don't get cleared --}}
            <input type="hidden" name="portal_access" value="{{ $portalOpen ? 1 : 0 }}">
            <input type="hidden" name="teachers_portal_access" value="{{ $teachersPortalOpen ? 1 : 0 }}">
            @foreach($teacherPermissions as $key => $label)
                @if($teacherPermValues[$key] ?? true)
                <input type="hidden" name="lecturer_can_{{ $key }}" value="1">
                @endif
            @endforeach

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-3">
                    <i class="fas fa-rotate text-indigo-500 mr-1.5"></i> Report Card Paper Orientation
                </label>
                @php
                    try {
                        $currentOrientation = \App\Models\Website\SiteSetting::instance()->report_card_orientation ?? 'landscape';
                    } catch (\Throwable $e) {
                        $currentOrientation = 'landscape';
                    }
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-lg">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="report_card_orientation" value="landscape"
                               {{ $currentOrientation === 'landscape' ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all
                                    peer-checked:border-indigo-500 peer-checked:bg-indigo-50 border-gray-200 bg-white">
                            <div class="w-10 h-8 rounded border-2 border-current flex-shrink-0 flex items-center justify-center text-indigo-600 bg-indigo-50"
                                 style="aspect-ratio:1.414">
                                <i class="fas fa-file-alt text-xs"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">Landscape (A4)</p>
                                <p class="text-xs text-gray-500">Wider — two columns side by side</p>
                            </div>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="report_card_orientation" value="portrait"
                               {{ $currentOrientation === 'portrait' ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all
                                    peer-checked:border-indigo-500 peer-checked:bg-indigo-50 border-gray-200 bg-white">
                            <div class="w-8 h-10 rounded border-2 border-current flex-shrink-0 flex items-center justify-center text-indigo-600 bg-indigo-50">
                                <i class="fas fa-file-alt text-xs"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">Portrait (A4)</p>
                                <p class="text-xs text-gray-500">Taller — traditional page layout</p>
                            </div>
                        </div>
                    </label>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    This applies to all report cards generated for students and teachers.
                </p>
            </div>
            <button type="submit" class="btn-gold px-6 py-2.5 rounded-xl text-sm font-bold shadow active:scale-95">
                <i class="fas fa-save mr-1.5"></i> Save Report Card Settings
            </button>
        </form>
    </div>

    {{-- Portal Links --}}
    <div class="space-y-4">
        <h3 class="text-sm font-bold text-gray-800">
            <i class="fas fa-link mr-2 text-gray-500"></i>All Portal URLs — Share with staff
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Student Portal --}}
            <div class="card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#dbeafe">
                        <i class="fas fa-user-graduate text-blue-600 text-sm"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Student Portal</p>
                </div>
                <div class="flex items-center gap-2">
                    <code class="flex-1 bg-gray-50 px-3 py-2.5 rounded-xl text-xs font-mono text-gray-700 border border-gray-200 truncate">{{ url('/student-portal/login') }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ url('/student-portal/login') }}');this.innerHTML='<i class=\'fas fa-check\'></i>'"
                            class="px-3 py-2.5 rounded-xl text-xs font-semibold text-white flex-shrink-0" style="background:#3b82f6">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>

            {{-- Teachers Portal --}}
            <div class="card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#f3e8ff">
                        <i class="fas fa-chalkboard-user text-purple-600 text-sm"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Teachers Portal</p>
                </div>
                <div class="flex items-center gap-2">
                    <code class="flex-1 bg-gray-50 px-3 py-2.5 rounded-xl text-xs font-mono text-gray-700 border border-gray-200 truncate">{{ url('/teachers-portal/login') }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ url('/teachers-portal/login') }}');this.innerHTML='<i class=\'fas fa-check\'></i>'"
                            class="px-3 py-2.5 rounded-xl text-xs font-semibold text-white flex-shrink-0" style="background:#a855f7">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>

            {{-- Accountant Portal --}}
            <div class="card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fef3c7">
                        <i class="fas fa-coins text-amber-600 text-sm"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Accountant Portal</p>
                </div>
                <div class="flex items-center gap-2">
                    <code class="flex-1 bg-gray-50 px-3 py-2.5 rounded-xl text-xs font-mono text-gray-700 border border-gray-200 truncate">{{ url('/accountant-portal/login') }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ url('/accountant-portal/login') }}');this.innerHTML='<i class=\'fas fa-check\'></i>'"
                            class="px-3 py-2.5 rounded-xl text-xs font-semibold text-white flex-shrink-0" style="background:#d97706">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <p class="text-[11px] text-gray-400 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Manage accounts at <a href="{{ route('admin.staff-portal-users.index') }}" class="underline font-semibold text-amber-700">Staff Portals</a>
                </p>
            </div>

            {{-- Headmaster Portal --}}
            <div class="card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#ede9fe">
                        <i class="fas fa-crown text-violet-600 text-sm"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Headmaster Portal</p>
                </div>
                <div class="flex items-center gap-2">
                    <code class="flex-1 bg-gray-50 px-3 py-2.5 rounded-xl text-xs font-mono text-gray-700 border border-gray-200 truncate">{{ url('/headmaster-portal/login') }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ url('/headmaster-portal/login') }}');this.innerHTML='<i class=\'fas fa-check\'></i>'"
                            class="px-3 py-2.5 rounded-xl text-xs font-semibold text-white flex-shrink-0" style="background:#7c3aed">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>

            {{-- Headteacher Portal --}}
            <div class="card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#ecfdf5">
                        <i class="fas fa-chalkboard-teacher text-teal-600 text-sm"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Headteacher Portal</p>
                </div>
                <div class="flex items-center gap-2">
                    <code class="flex-1 bg-gray-50 px-3 py-2.5 rounded-xl text-xs font-mono text-gray-700 border border-gray-200 truncate">{{ url('/headteacher-portal/login') }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ url('/headteacher-portal/login') }}');this.innerHTML='<i class=\'fas fa-check\'></i>'"
                            class="px-3 py-2.5 rounded-xl text-xs font-semibold text-white flex-shrink-0" style="background:#0891b2">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>

            {{-- Secretary Portal --}}
            <div class="card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fdf2f8">
                        <i class="fas fa-file-alt text-pink-600 text-sm"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Secretary Portal</p>
                </div>
                <div class="flex items-center gap-2">
                    <code class="flex-1 bg-gray-50 px-3 py-2.5 rounded-xl text-xs font-mono text-gray-700 border border-gray-200 truncate">{{ url('/secretary-portal/login') }}</code>
                    <button onclick="navigator.clipboard.writeText('{{ url('/secretary-portal/login') }}');this.innerHTML='<i class=\'fas fa-check\'></i>'"
                            class="px-3 py-2.5 rounded-xl text-xs font-semibold text-white flex-shrink-0" style="background:#db2777">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <p class="text-[11px] text-gray-400 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Manage accounts at <a href="{{ route('admin.staff-portal-users.index') }}" class="underline font-semibold text-pink-700">Staff Portals</a>
                </p>
            </div>

            {{-- Staff Portal hub --}}
            <div class="card p-5 border-2 border-dashed border-indigo-200 bg-indigo-50/30">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fas fa-user-shield text-indigo-500"></i>
                    <p class="text-xs font-bold text-indigo-800">All Staff Portals share one login page</p>
                </div>
                <p class="text-xs text-indigo-600 leading-relaxed mb-3">
                    Each staff member gets their own role-branded login. Manage all staff portal accounts, set permissions and reset passwords from:
                </p>
                <a href="{{ route('admin.staff-portal-users.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 transition-all active:scale-95">
                    <i class="fas fa-cog text-[10px]"></i> Manage Staff Portal Accounts →
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
