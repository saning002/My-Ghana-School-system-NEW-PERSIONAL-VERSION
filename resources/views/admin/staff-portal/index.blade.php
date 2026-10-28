extends('layouts.app')
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
            All permissions are <strong class="text-yellow-300">ON by default</strong> – admin can turn individual ones off.
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
    'Settings'               => ['settings','settings_manage'?'admin.staff-portal-users.store'?'checked' : ''?'accountant'=>'bg-[var(--primary-pale)] text-[var(--primary-text)]','headmaster'=>'bg-indigo-100 text-indigo-800','headteacher'=>'bg-emerald-100 text-emerald-800','deputy'=>'bg-blue-100 text-blue-800','secretary'=>'bg-pink-100 text-pink-800'];
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
                        <i class="fas text-xs" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'?'admin.staff-portal-users.update', $user) }}" class="space-y-3">
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
                                <button type="button" onclick="setAllEdit('edit-{{ $user->id }}'?'edit-{{ $user->id }}'?'checked' : '' }}
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
