@extends('layouts.app')
@section('title','Notification Center')
@section('subtitle','Send alerts and messages to students and teachers')

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
<div class="page-hero-banner mb-5">
    <div class="hero-left">
        <h1><i class="fas fa-bell mr-2" style="color:var(--brand)"></i>{{ $sidebarLabels['notifications'] ?? 'Notifications' }}</h1>
        <p>Send alerts and messages to students and teachers</p>
    </div>
    <div class="hero-actions">
        <a href="{{ route('admin.notifications.templates.index') }}" class="btn-secondary-action">
            <i class="fas fa-file-alt"></i> Message Templates
        </a>
    </div>
</div>

{{-- ── Flash ────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-green-50 border border-green-200 text-green-800 text-sm font-semibold mb-5">
    <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i> {{ session('success') }}
</div>
@endif
@if(session('warning'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-semibold mb-5">
    <i class="fas fa-exclamation-triangle text-amber-500 flex-shrink-0"></i> {{ session('warning') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5"
     x-data="{
        recipientType: '{{ old('recipient_type','student') }}',
        audience: '{{ old('audience','all') }}',
        showPicker: false,
     }">

    {{-- ════════════════════════════════════════════════════════════════════════
         SEND FORM  — left 2 cols
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:col-span-2">
        <div class="sc sticky top-20">

            {{-- Card header --}}
            <div class="sc-hd">
                <div>
                    <p class="sc-title">Send Notification</p>
                    <p class="sc-sub">Compose or pick a saved template</p>
                </div>
                @if($templates->isNotEmpty())
                <button type="button" @click="showPicker = !showPicker"
                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg border transition-all whitespace-nowrap"
                        :class="showPicker
                            ? 'border-[var(--brand)] bg-[var(--primary-pale)] text-[var(--brand)]'
                            : 'border-slate-200 bg-white text-slate-500 hover:border-[var(--brand)]'">
                    <i class="fas fa-file-alt text-[10px]"></i> Use Template
                    <i class="fas fa-chevron-down text-[9px] transition-transform duration-200"
                       :class="showPicker ? 'rotate-180':''"></i>
                </button>
                @endif
            </div>

            {{-- ── Template picker ──────────────────────────────────────── --}}
            @if($templates->isNotEmpty())
            <div x-show="showPicker" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="border-b border-slate-100 bg-slate-50 px-5 py-4">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2.5">
                    Choose a template — it will fill the form instantly
                </p>
                <div class="space-y-1.5 max-h-52 overflow-y-auto pr-0.5">
                    @php
                    $cc = [
                        'general'    => ['#ede9fe','#7c3aed'],
                        'attendance' => ['#cffafe','#0891b2'],
                        'fee'        => ['#fef3c7','#d97706'],
                        'results'    => ['#dcfce7','#16a34a'],
                        'custom'     => ['#f1f5f9','#475569'],
                    ];
                    @endphp
                    @foreach($templates as $tpl)
                    @php [$cbg,$clr] = $cc[$tpl->category] ?? $cc['custom']; @endphp
                    <button type="button"
                            @click="showPicker = false"
                            onclick="applyTemplate({{ json_encode($tpl->title) }}, {{ json_encode($tpl->body) }})"
                            class="w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-xl border border-transparent
                                   hover:border-[var(--brand)] hover:bg-white transition-all group">
                        <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full"
                              style="background:{{ $cbg }};color:{{ $clr }}">{{ ucfirst($tpl->category) }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 truncate group-hover:text-[var(--brand)] transition-colors">{{ $tpl->name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ Str::limit($tpl->title, 52) }}</p>
                        </div>
                        <i class="fas fa-arrow-right text-[10px] text-slate-300 group-hover:text-[var(--brand)] flex-shrink-0 transition-colors"></i>
                    </button>
                    @endforeach
                </div>
                <a href="{{ route('admin.notifications.templates.index') }}"
                   class="mt-3 inline-flex items-center gap-1.5 text-[11px] font-semibold hover:underline"
                   style="color:var(--brand)">
                    <i class="fas fa-plus-circle text-[10px]"></i> Create a new template
                </a>
            </div>
            @endif

            {{-- ── The form ─────────────────────────────────────────────── --}}
            <form method="POST" action="{{ route('admin.notifications.store') }}" class="px-5 py-5 space-y-4">
                @csrf

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-xs text-red-700 space-y-0.5">
                    @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                </div>
                @endif

                {{-- Who to notify --}}
                <div>
                    <label class="form-label">Notify <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([['student','fa-user-graduate','Students','Student portal'],['lecturer','fa-chalkboard-user','Teachers','Teacher portal']] as [$val,$icon,$lbl,$sub])
                        <label class="cursor-pointer">
                            <input type="radio" name="recipient_type" value="{{ $val }}"
                                   x-model="recipientType" class="sr-only peer"
                                   {{ old('recipient_type','student') === $val ? 'checked' : '' }}>
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition-all
                                        peer-checked:border-[var(--brand)] peer-checked:bg-[var(--primary-pale)] border-slate-200">
                                <i class="fas {{ $icon }} text-sm" style="color:var(--brand)"></i>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $lbl }}</p>
                                    <p class="text-xs text-slate-400">{{ $sub }}</p>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Audience --}}
                <div>
                    <label class="form-label">Send To <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([['all','fa-users','All'],['individual','fa-user','Individual']] as [$val,$icon,$lbl])
                        <label class="cursor-pointer">
                            <input type="radio" name="audience" value="{{ $val }}"
                                   x-model="audience" class="sr-only peer"
                                   {{ old('audience','all') === $val ? 'checked' : '' }}>
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 transition-all
                                        peer-checked:border-[var(--brand)] peer-checked:bg-[var(--primary-pale)] border-slate-200">
                                <i class="fas {{ $icon }} text-sm" style="color:var(--brand)"></i>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $lbl }}</p>
                                    @if($val === 'all')
                                    <p class="text-xs text-slate-400" x-text="recipientType==='lecturer' ? 'All teachers' : 'All students'"></p>
                                    @else
                                    <p class="text-xs text-slate-400">One person</p>
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="mt-3" x-show="audience==='individual' && recipientType==='student'" x-cloak>
                        <x-student-search name="student_id" placeholder="Search student..." :required="false" />
                    </div>
                    <div class="mt-3" x-show="audience==='individual' && recipientType==='lecturer'" x-cloak>
                        <label class="form-label">Select Teacher <span class="text-red-400">*</span></label>
                        <select name="lecturer_id" class="form-input">
                            <option value="">— Choose a teacher —</option>
                            @foreach($lecturers as $lecturer)
                            <option value="{{ $lecturer->id }}" {{ old('lecturer_id')==$lecturer->id ? 'selected':'' }}>
                                {{ $lecturer->full_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Channel --}}
                <div>
                    <label class="form-label">Delivery Channel <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="channel" value="in_app" class="sr-only peer" checked>
                            <div class="flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 text-center transition-all
                                        peer-checked:border-[var(--brand)] peer-checked:bg-[var(--primary-pale)] border-slate-200">
                                <i class="fas fa-bell text-sm" style="color:var(--brand)"></i>
                                <p class="text-xs font-bold text-slate-800">In-App</p>
                                <p class="text-[10px] text-slate-400">Portal only</p>
                            </div>
                        </label>
                        <label class="cursor-pointer {{ $smsAvailable ? '' : 'opacity-50' }}">
                            <input type="radio" name="channel" value="sms"
                                   class="sr-only peer" {{ $smsAvailable ? '' : 'disabled' }}>
                            <div class="flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 text-center transition-all
                                        peer-checked:border-emerald-500 peer-checked:bg-emerald-50 border-slate-200">
                                <i class="fas fa-sms text-sm text-emerald-600"></i>
                                <p class="text-xs font-bold text-slate-800">SMS Only</p>
                                <p class="text-[10px] text-slate-400">
                                    {{ $smsAvailable ? $smsCredits.' credits' : 'No credits' }}
                                </p>
                            </div>
                        </label>
                        <label class="cursor-pointer {{ $smsAvailable ? '' : 'opacity-50' }}">
                            <input type="radio" name="channel" value="both"
                                   class="sr-only peer" {{ $smsAvailable ? '' : 'disabled' }}>
                            <div class="flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 text-center transition-all
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 border-slate-200">
                                <i class="fas fa-share-alt text-sm text-blue-600"></i>
                                <p class="text-xs font-bold text-slate-800">Both</p>
                                <p class="text-[10px] text-slate-400">App + SMS</p>
                            </div>
                        </label>
                    </div>
                    @if(!$smsAvailable)
                    <p class="text-[10px] text-amber-600 mt-1.5 flex items-center gap-1">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ $smsCredits === 0 ? 'Top up SMS credits in Subscription' : 'No SMS provider configured' }}
                    </p>
                    @endif
                </div>

                {{-- Type --}}
                <div>
                    <label class="form-label">Notification Type <span class="text-red-400">*</span></label>
                    <select name="type" required class="form-input">
                        <option value="general">General Announcement</option>
                        <option value="fee">Fee Reminder</option>
                        <option value="emergency">Emergency Alert</option>
                        <option value="result">Result Notice</option>
                        <option value="attendance">Attendance Notice</option>
                    </select>
                </div>

                {{-- Title --}}
                <div>
                    <label class="form-label">Title / Subject <span class="text-red-400">*</span></label>
                    <input type="text" name="title" id="notif-title"
                           value="{{ old('title','') }}" required
                           class="form-input"
                           placeholder="e.g. Important Announcement">
                </div>

                {{-- Message --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="form-label mb-0">Message <span class="text-red-400">*</span></label>
                        <span class="text-[10px] text-slate-400 font-semibold" id="msg-counter">
                            {{ strlen(old('message','')) }} / 3000
                        </span>
                    </div>
                    <textarea name="message" id="notif-message" rows="5" required
                              class="form-input resize-none"
                              oninput="document.getElementById('msg-counter').textContent=this.value.length+' / 3000'"
                              placeholder="Write your message here...">{{ old('message','') }}</textarea>
                    <p class="text-[10px] text-slate-400 mt-1">
                        <i class="fas fa-info-circle mr-0.5"></i>
                        To use smart placeholders like {student_name}, save a template first.
                    </p>
                </div>

                <button type="submit"
                        class="btn-primary-action w-full justify-center py-3 rounded-xl text-sm font-bold">
                    <i class="fas fa-paper-plane mr-1.5"></i> Send Notification
                </button>

                @if($templates->isEmpty())
                <a href="{{ route('admin.notifications.templates.index') }}"
                   class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-semibold text-slate-500
                          border border-dashed border-slate-300 hover:border-[var(--brand)] hover:text-[var(--brand)] transition-all">
                    <i class="fas fa-plus-circle"></i> Create message templates
                </a>
                @endif
            </form>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         NOTIFICATIONS LIST  — right 3 cols
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:col-span-3">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-700">
                Sent Notifications
                <span class="ml-2 text-xs px-2 py-0.5 rounded-full font-bold"
                      style="background:var(--primary-pale);color:var(--brand)">
                    {{ $notifications->total() }}
                </span>
            </h3>
        </div>

        @if($notifications->isEmpty())
        <div class="sc">
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>
                <p class="empty-state-title">No notifications sent yet</p>
                <p class="empty-state-sub">Use the form to send your first notification.</p>
            </div>
        </div>
        @else
        <div class="space-y-3">
            @foreach($notifications as $notif)
            @php
                $tc = [
                    'general'    => ['fa-bullhorn',            '#6366f1','#ede9fe','General'],
                    'fee'        => ['fa-coins',               '#d97706','#fef3c7','Fee'],
                    'emergency'  => ['fa-exclamation-triangle','#dc2626','#fee2e2','Emergency'],
                    'result'     => ['fa-file-alt',            '#059669','#d1fae5','Result'],
                    'attendance' => ['fa-clipboard-check',     '#0891b2','#cffafe','Attendance'],
                ][$notif->type] ?? ['fa-bell','#6b7280','#f3f4f6','Notice'];
                [$tIcon,$tColor,$tBg,$tLabel] = $tc;
                $isTeacher = ($notif->recipient_type ?? 'student') === 'lecturer';
            @endphp
            <div class="sc p-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:{{ $tBg }}">
                        <i class="fas {{ $tIcon }}" style="color:{{ $tColor }};font-size:.85rem"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center flex-wrap gap-1.5 mb-1">
                            <span class="text-sm font-bold text-slate-800">{{ $notif->title }}</span>
                            <span class="badge" style="background:{{ $tBg }};color:{{ $tColor }}">{{ $tLabel }}</span>
                            {{-- Recipient badge --}}
                            @if($isTeacher)
                                <span class="badge badge-purple">
                                    <i class="fas fa-chalkboard-user"></i>
                                    {{ $notif->audience === 'all' ? 'All Teachers' : ($notif->lecturer->full_name ?? 'Teacher') }}
                                </span>
                            @else
                                <span class="badge badge-blue">
                                    <i class="fas fa-user-graduate"></i>
                                    {{ $notif->audience === 'all' ? 'All Students' : ($notif->student->user->full_name ?? 'Student') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed mb-2">{{ $notif->message }}</p>
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <p class="text-xs text-slate-400">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $notif->created_at->diffForHumans() }}
                                — by {{ $notif->sender->full_name ?? 'Admin' }}
                            </p>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                {{-- Channel badges --}}
                                @if(in_array($notif->channel ?? 'in_app', ['in_app','both']))
                                <span class="badge badge-purple">
                                    <i class="fas fa-bell"></i> In-App
                                </span>
                                @endif
                                @if(in_array($notif->channel ?? 'in_app', ['sms','both']))
                                @php
                                    $ss = $notif->sms_status ?? '';
                                    $smsClass = $ss==='sent' ? 'badge-green' : ($ss==='failed' ? 'badge-red' : 'badge-gray');
                                @endphp
                                <span class="badge {{ $smsClass }}">
                                    <i class="fas fa-sms"></i>
                                    SMS
                                    @if(($notif->sms_sent_count ?? 0) > 0)
                                    ({{ $notif->sms_sent_count }} sent
                                    @if(($notif->sms_failed_count ?? 0) > 0), {{ $notif->sms_failed_count }} failed @endif)
                                    @elseif($ss === 'failed')
                                    (Failed)
                                    @endif
                                </span>
                                @endif
                                {{-- Delete --}}
                                <form method="POST"
                                      action="{{ route('admin.notifications.destroy', $notif) }}"
                                      onsubmit="return confirm('Delete this notification?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-delete">
                                        <i class="fas fa-trash" style="font-size:.7rem"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($notifications->hasPages())
        <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
/**
 * Fill the send form's title + message fields from a saved template.
 * Called when admin clicks a template in the picker.
 */
function applyTemplate(title, body) {
    const titleEl = document.getElementById('notif-title');
    const msgEl   = document.getElementById('notif-message');
    const counter = document.getElementById('msg-counter');

    if (titleEl) titleEl.value = title;
    if (msgEl) {
        msgEl.value = body;
        if (counter) counter.textContent = body.length + ' / 3000';
        // Scroll into view so the admin sees the filled form
        msgEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        msgEl.focus();
    }
}

// Pre-fill from URL param if coming from "Use in notification" on templates page
@if(request('template_id'))
(function() {
    fetch('{{ url('admin/notifications/templates') }}/{{ request('template_id') }}/load')
        .then(r => r.json())
        .then(data => applyTemplate(data.title, data.body))
        .catch(() => {}); // Silent fail — form still usable
})();
@endif
</script>
@endpush

@endsection
