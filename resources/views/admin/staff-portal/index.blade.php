@extends('layouts.app')
@section('title','Staff Portals')
@section('subtitle','Manage Accountant, Headmaster, Headteacher, Deputy Head and Secretary portal accounts')

@section('content')
<div class="space-y-5">

@if(session('success'))<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}</div>@endif
@if(session('error'))<div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3 text-sm font-semibold text-red-800 flex items-center gap-2"><i class="fas fa-circle-exclamation text-red-500"></i>{{ session('error') }}</div>@endif

{{-- Hero --}}
<div class="rounded-3xl relative overflow-hidden p-6 lg:p-8 text-white"
     style="background:linear-gradient(135deg,#1e1b4b 0%,#312e81 35%,#4c1d95 65%,#7c3aed 100%)">
    <div class="pointer-events-none absolute -top-16 -right-16 h-56 w-56 rounded-full bg-white/4 blur-3xl"></div>
    <div class="relative z-10">
        <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">Staff Portal Accounts</h1>
        <p class="text-indigo-200 text-sm mt-2 max-w-2xl leading-relaxed">
            Create portal accounts for <strong class="text-white">Accountants</strong>, <strong class="text-white">Headmasters</strong>,
            <strong class="text-white">Headteachers</strong>, <strong class="text-white">Deputies</strong> and <strong class="text-white">Secretaries</strong>.
            All permissions are <strong class="text-yellow-300">ON by default</strong> — admin can turn individual ones off.
        </p>
        <div class="flex flex-wrap gap-3 mt-4">
            @foreach(\App\Models\StaffPortalUser::$roleLabels as $role => $label)
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-3 py-1 text-xs font-bold text-white/80">
                <i class="fas fa-user-shield text-purple-300 text-[10px]"></i>
                <a href="{{ route('staff-portal.login.role', $role) }}" target="_blank" class="hover:text-white transition-colors">
                    /{{ $role }}-portal/login
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

@php
// Group permissions by category for display
$permGroups = [
    'Dashboard'              => ['dashboard'],
    'Student Reports'        => ['student_reports','student_reports_edit'],
    'Students'               => ['students','students_create','students_edit','students_delete','students_approve','students_promote','students_import','students_id_card'],
    'Attendance'             => ['attendance','attendance_mark','attendance_edit','attendance_import','attendance_reports'],
    'Exams & Scores'         => ['exams','exams_enter','exams_report_card','exams_bulk_download','exams_import'],
    'Exam Questions'         => ['exam_questions','exam_questions_approve','exam_questions_download','exam_questions_delete'],
    'Fees & Payments'        => ['fees','fees_record','fees_edit','fees_daily','fees_exemptions','fees_reports'],
    'Daily Fees'             => ['daily_fees','daily_fees_manage'],
    'Reports'                => ['reports','reports_academic','reports_attendance','reports_financial','reports_export'],
    'Programs & Courses'     => ['programs','programs_manage','courses','courses_manage'],
    'Lecturers / Teachers'   => ['lecturers','lecturers_manage','lecturers_assign','class_teachers'],
    'Scheme of Learning'     => ['scheme_of_learning','scheme_manage','scheme_upload_pdf'],
    'Timetable & Calendar'   => ['timetable','timetable_manage','calendar','calendar_manage'],
    'Notifications'          => ['notifications','notifications_send'],
    'Branches'               => ['branches','branches_manage'],
    'Work Monitoring'        => ['work_logs','work_logs_manage','work_logs_comment'],
    'Promotions'             => ['promotions','promotions_manage'],
    'Academic Sessions'      => ['academic_sessions','academic_sessions_manage'],
    'Settings'               => ['settings','settings_manage'],
];
$allPerms = \App\Models\StaffPortalUser::$allPermissions;
$defaults = \App\Models\StaffPortalUser::$defaultPermissions;
@endphp

<div class="grid grid-cols-1 xl:grid-cols-5 gap-5">

    {{-- ── Create form ──────────────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm p-5 self-start">
        <h3 class="text-sm font-extrabold text-slate-800 mb-1">
            <i class="fas fa-user-plus text-violet-500 mr-1.5"></i>Create Account
        </h3>
        <p class="text-[11px] text-slate-400 mb-4">All permissions are ON by default. Uncheck any you want to restrict.</p>

        <form method="POST" action="{{ route('admin.staff-portal-users.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Full Name *</label>
                    <input type="text" name="full_name" required placeholder="e.g. Mr. Kofi Mensah"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Role *</label>
                    <select name="role" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        @foreach(\App\Models\StaffPortalUser::$roleLabels as $v => $l)
                        <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Email *</label>
                    <input type="email" name="email" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Phone</label>
                    <input type="text" name="phone" placeholder="Optional"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Password *</label>
                    <input type="password" name="password" required minlength="6"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
            </div>

            {{-- Permissions grouped --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold uppercase tracking-wide text-slate-600">Permissions</label>
                    <div class="flex gap-2">
                        <button type="button" onclick="setAllNew(true)"
                            class="text-[10px] font-bold text-violet-600 hover:text-violet-800 px-2 py-1 bg-violet-50 rounded-lg">✓ All ON</button>
                        <button type="button" onclick="setAllNew(false)"
                            class="text-[10px] font-bold text-slate-500 hover:text-slate-700 px-2 py-1 bg-slate-100 rounded-lg">✗ All OFF</button>
                    </div>
                </div>
                <div class="space-y-2 max-h-[400px] overflow-y-auto pr-1" id="new-perm-container">
                    @foreach($permGroups as $groupName => $groupKeys)
                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between bg-slate-50 px-3 py-2 border-b border-slate-100 cursor-pointer"
                             onclick="toggleGroup(this)">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600">{{ $groupName }}</span>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="event.stopPropagation(); setGroup(this, true)"
                                    class="text-[9px] font-bold text-violet-500 hover:text-violet-700">All</button>
                                <button type="button" onclick="event.stopPropagation(); setGroup(this, false)"
                                    class="text-[9px] font-bold text-slate-400 hover:text-slate-600">None</button>
                                <i class="fas fa-chevron-down text-[9px] text-slate-400 transition-transform group-chev"></i>
                            </div>
                        </div>
                        <div class="p-2 space-y-1 group-perms">
                            @foreach($groupKeys as $perm)
                            @if(isset($allPerms[$perm]))
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-700 hover:text-slate-900 px-1 py-0.5 rounded hover:bg-slate-50">
                                <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                    {{ in_array($perm, $defaults) ? 'checked' : '' }}
                                    class="new-perm-chk rounded accent-violet-600 w-3.5 h-3.5 shrink-0">
                                <span>{{ $allPerms[$perm] }}</span>
                                @if(in_array($perm, $defaults))
                                <span class="ml-auto text-[9px] font-bold text-emerald-500 bg-emerald-50 px-1.5 py-0.5 rounded-full">DEFAULT</span>
                                @endif
                            </label>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-sm font-bold py-2.5 shadow transition-all active:scale-95">
                <i class="fas fa-plus mr-1"></i> Create Account
            </button>
        </form>
    </div>

    {{-- ── Existing accounts ─────────────────────────────────────────────────── --}}
    <div class="xl:col-span-3 space-y-4">
        <h3 class="text-sm font-extrabold text-slate-800">
            <i class="fas fa-users text-violet-400 mr-1.5"></i>Existing Accounts ({{ $staffUsers->count() }})
        </h3>

        @forelse($staffUsers as $user)
        @php
            $roleColors = ['accountant'=>'bg-amber-100 text-amber-800','headmaster'=>'bg-indigo-100 text-indigo-800','headteacher'=>'bg-emerald-100 text-emerald-800','deputy'=>'bg-blue-100 text-blue-800','secretary'=>'bg-pink-100 text-pink-800'];
            $rc = $roleColors[$user->role] ?? 'bg-slate-100 text-slate-700';
            $userPerms = $user->permission_list;
        @endphp
        <div class="rounded-2xl border {{ $user->is_active ? 'border-slate-200' : 'border-red-200 opacity-70' }} bg-white shadow-sm overflow-hidden"
             x-data="{ expanded: false }">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 bg-slate-50 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-400 to-indigo-500 text-white text-sm font-extrabold uppercase shadow">
                        {{ strtoupper(substr($user->full_name,0,1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-extrabold text-slate-900">{{ $user->full_name }}</p>
                        <p class="text-xs text-slate-400">{{ $user->email }}@if($user->phone) · {{ $user->phone }}@endif</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full {{ $rc }} text-[11px] font-extrabold px-2.5 py-0.5">{{ $user->role_label }}</span>
                    @if(!$user->is_active)
                    <span class="rounded-full bg-red-100 text-red-700 text-[11px] font-extrabold px-2.5 py-0.5">Inactive</span>
                    @endif
                    <button @click="expanded = !expanded"
                        class="ml-1 w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-violet-600 hover:border-violet-300 transition-colors">
                        <i class="fas text-xs" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                </div>
            </div>

            {{-- Permissions summary (always visible) --}}
            <div class="px-5 py-3 border-b border-slate-100">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Permissions ({{ count($userPerms) }} / {{ count($allPerms) }} active)
                </p>
                <div class="flex flex-wrap gap-1">
                    @forelse($userPerms as $p)
                    <span class="rounded-full bg-violet-100 text-violet-800 text-[10px] font-bold px-2 py-0.5">
                        {{ $allPerms[$p] ?? $p }}
                    </span>
                    @empty
                    <span class="text-xs text-red-400 font-semibold italic">⚠ No permissions — this account cannot access anything</span>
                    @endforelse
                </div>
            </div>

            {{-- Edit form (expandable) --}}
            <div x-show="expanded" x-cloak class="px-5 py-4">
                <form method="POST" action="{{ route('admin.staff-portal-users.update', $user) }}" class="space-y-3">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="full_name" value="{{ $user->full_name }}" required placeholder="Full name"
                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <select name="role" required class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-violet-400">
                            @foreach(\App\Models\StaffPortalUser::$roleLabels as $v => $l)
                            <option value="{{ $v }}" {{ $user->role==$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                        <input type="password" name="password" placeholder="New password (leave blank to keep)"
                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-600 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <input type="checkbox" name="is_active" value="1" {{ $user->is_active?'checked':'' }} class="rounded accent-emerald-600"> Active
                        </label>
                    </div>

                    {{-- Grouped permissions --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Permissions</p>
                            <div class="flex gap-2">
                                <button type="button" onclick="setAllEdit('edit-{{ $user->id }}', true)"
                                    class="text-[10px] font-bold text-violet-600 hover:text-violet-800 px-2 py-1 bg-violet-50 rounded-lg">✓ All ON</button>
                                <button type="button" onclick="setAllEdit('edit-{{ $user->id }}', false)"
                                    class="text-[10px] font-bold text-slate-500 hover:text-slate-700 px-2 py-1 bg-slate-100 rounded-lg">✗ All OFF</button>
                            </div>
                        </div>
                        <div class="space-y-1.5 max-h-[360px] overflow-y-auto pr-1" id="edit-{{ $user->id }}">
                            @foreach($permGroups as $groupName => $groupKeys)
                            <div class="rounded-xl border border-slate-200 overflow-hidden">
                                <div class="flex items-center justify-between bg-slate-50 px-3 py-1.5 border-b border-slate-100 cursor-pointer" onclick="toggleGroup(this)">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-600">{{ $groupName }}</span>
                                    <i class="fas fa-chevron-down text-[8px] text-slate-400 group-chev"></i>
                                </div>
                                <div class="p-2 grid grid-cols-2 gap-x-3 gap-y-0.5 group-perms">
                                    @foreach($groupKeys as $perm)
                                    @if(isset($allPerms[$perm]))
                                    <label class="flex items-center gap-1.5 cursor-pointer text-[11px] font-medium text-slate-600 hover:text-slate-900 py-0.5">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                            {{ in_array($perm, $userPerms) ? 'checked' : '' }}
                                            class="rounded accent-violet-600 w-3 h-3 shrink-0">
                                        <span class="leading-tight">{{ $allPerms[$perm] }}</span>
                                    </label>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold py-2 transition-colors active:scale-95">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.staff-portal-users.destroy', $user) }}"
                      class="mt-2"
                      onsubmit="return confirm('Permanently delete {{ addslashes($user->full_name) }}\'s account?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-full rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold py-2 border border-red-200 transition-colors">
                        <i class="fas fa-trash mr-1"></i> Delete Account
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
            <i class="fas fa-user-shield text-4xl text-slate-200 block mb-3"></i>
            <p class="text-sm font-semibold text-slate-400">No staff portal accounts yet.</p>
        </div>
        @endforelse
    </div>
</div>

</div>

@push('scripts')
<script>
function toggleGroup(header) {
    const perms = header.nextElementSibling;
    const chev  = header.querySelector('.group-chev');
    const isHidden = perms.style.display === 'none';
    perms.style.display = isHidden ? '' : 'none';
    if (chev) chev.style.transform = isHidden ? '' : 'rotate(-90deg)';
}

function setGroup(btn, checked) {
    const container = btn.closest('.rounded-xl');
    container.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = checked);
}

function setAllNew(checked) {
    document.querySelectorAll('#new-perm-container input[type=checkbox]').forEach(cb => cb.checked = checked);
}

function setAllEdit(containerId, checked) {
    document.querySelectorAll('#' + containerId + ' input[type=checkbox]').forEach(cb => cb.checked = checked);
}
</script>
@endpush
@endsection
