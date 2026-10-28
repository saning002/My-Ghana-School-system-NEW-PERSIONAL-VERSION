extends('layouts.app')
@section('title', 'Fee Reminders')

@section('content'?'';
    position: absolute;
    right: -50px; top: -50px;
    width: 250px; height: 250px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
}
.rm-hero::after {
    content: ''?'s own balance – no copy-pasting needed.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.fees.reminders.log') }}"
                   style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:9px 18px;font-weight:600;font-size:.85rem;text-decoration:none;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-history"></i> Reminder Log
                </a>
                <a href="{{ route('admin.fees.term.index'?'success'))
    <div class="alert alert-dismissible fade show mb-4"
         style="border-radius:12px;border:none;background:#f0fdf4;color:#166534;border-left:4px solid #16a34a;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-dismissible fade show mb-4"
         style="border-radius:12px;border:none;background:var(--primary-pale);color:#854d0e;border-left:4px solid #ca8a04;">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-dismissible fade show mb-4"
         style="border-radius:12px;border:none;background:#fef2f2;color:#991b1b;border-left:4px solid #dc2626;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error'?'tenant_id', 0)); } catch(\Throwable $e) {}
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="rm-stat">
                <div class="rm-stat-icon" style="background:#fef2f2;color:#dc2626;">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div style="font-size:1.6rem;font-weight:800;line-height:1;color:#1e293b;">{{ $summaries->count() }}</div>
                    <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-top:2px;">Owing Students</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="rm-stat">
                <div class="rm-stat-icon" style="background:#fff7ed;color:var(--primary-dark);">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <div style="font-size:1.15rem;font-weight:800;line-height:1;color:#1e293b;">
                        GHS {{ number_format($summaries->sum(fn($s) => max(0, $s['balance'])), 2) }}
                    </div>
                    <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-top:2px;">Total Outstanding</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="rm-stat">
                <div class="rm-stat-icon" style="background:#f0fdf4;color:#16a34a;">
                    <i class="fas fa-sms"></i>
                </div>
                <div>
                    <div style="font-size:1.6rem;font-weight:800;line-height:1;color:{{ ($msgCredits?->sms_credits ?? 0) < 10 ? '#dc2626' : '#1e293b' }};">
                        {{ number_format($msgCredits?->sms_credits ?? 0) }}
                    </div>
                    <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-top:2px;">SMS Credits</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="rm-stat">
                <div class="rm-stat-icon" style="background:#f0fdf4;color:#25d366;">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div>
                    <div style="font-size:1.6rem;font-weight:800;line-height:1;color:{{ ($msgCredits?->whatsapp_credits ?? 0) < 10 ? '#dc2626' : '#1e293b'?'selected' : '' }}>
                                    {{ $s->year }}
                                </option>
                            @endforeach
                        </select>
                        <span style="font-size:.83rem;color:#64748b;">
                            <i class="fas fa-filter me-1 text-primary"></i>
                            Showing <strong>{{ $summaries->count() }}</strong> student(s) with outstanding fees
                        </span>
                    </form>
                </div>
            </div>

            {{-- The big form --}}
            <form method="POST" action="{{ route('admin.fees.reminders.send') }}" id="rmForm">
                @csrf
                <input type="hidden" name="session_id" value="{{ $selectedSession?->id }}">
                <input type="hidden" name="mode" value="selected" id="sendMode">

                {{-- Step 1: Choose channels --}}
                <div class="rm-card">
                    <div class="rm-card-hd">
                        <span style="font-weight:700;font-size:.9rem;">
                            <span style="background:var(--primary);color:#fff;width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;margin-right:8px;">1</span>
                            Choose Channels
                        </span>
                        <span id="chWarnText" style="display:none;font-size:.78rem;color:#dc2626;">
                            <i class="fas fa-exclamation-circle me-1"></i>Pick at least one
                        </span>
                    </div>
                    <div style="padding:1.25rem 1.5rem;display:flex;gap:12px;flex-wrap:wrap;">
                        <div class="ch-pill" id="pillSms" onclick="toggleCh('sms', this)">
                            <input type="checkbox" name="channels[]" value="sms" id="chSms" style="display:none;">
                            <span style="width:30px;height:30px;background:#f0fdf4;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-sms" style="color:#16a34a;"></i>
                            </span>
                            <span>SMS</span>
                            <span style="font-size:.7rem;color:#94a3b8;font-weight:400;">({{ number_format($msgCredits?->sms_credits ?? 0) }} left)</span>
                        </div>

                        <div class="ch-pill" id="pillWa" onclick="toggleCh('wa', this)">
                            <input type="checkbox" name="channels[]" value="whatsapp" id="chWa" style="display:none;">
                            <span style="width:30px;height:30px;background:#f0fdf4;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="fab fa-whatsapp" style="color:#25d366;font-size:1.1rem;"></i>
                            </span>
                            <span>WhatsApp</span>
                            <span style="font-size:.7rem;color:#94a3b8;font-weight:400;">({{ number_format($msgCredits?->whatsapp_credits ?? 0) }} left)</span>
                        </div>

                        <div class="ch-pill" id="pillMail" onclick="toggleCh('mail', this)">
                            <input type="checkbox" name="channels[]" value="email" id="chEmail" style="display:none;">
                            <span style="width:30px;height:30px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-envelope" style="color:#3b82f6;"></i>
                            </span>
                            <span>Email</span>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Select students --}}
                <div class="rm-card">
                    <div class="rm-card-hd" style="background:#f8fafc;">
                        <div class="d-flex align-items-center gap-3">
                            <span style="font-weight:700;font-size:.9rem;">
                                <span style="background:#0f3460;color:#fff;width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;margin-right:8px;">2</span>
                                Select Students
                            </span>
                            <div class="form-check mb-0 ms-1">
                                <input class="form-check-input" type="checkbox" id="selectAll"
                                    onchange="toggleAll(this)"
                                    style="width:17px;height:17px;cursor:pointer;">
                                <label class="form-check-label" for="selectAll"
                                    style="font-size:.83rem;cursor:pointer;color:#64748b;">
                                    Select All
                                </label>
                            </div>
                        </div>
                        <span id="countBadge"
                            style="background:#e2e8f0;color:#64748b;padding:3px 12px;border-radius:20px;font-size:.78rem;font-weight:600;">
                            0 selected
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="rm-thead">
                                <tr>
                                    <th style="width:44px;"></th>
                                    <th>Student</th>
                                    <th>Contact</th>
                                    <th class="text-end">Balance</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($summaries as $s)
                            @php
                                $phone = $s['student']->user?->phone ?? $s['student']->guardian_phone ?? null;
                                $email = $s['student']->user?->email ?? $s['student']->guardian_email ?? null;
                                $name  = $s['student']->user?->full_name ?? '–';
                            @endphp
                            <tr class="rm-row" id="row-{{ $s['student']->id }}"
                                onclick="toggleRow({{ $s['student']->id }})">
                                <td onclick="event.stopPropagation()">
                                    <input class="form-check-input student-cb" type="checkbox"
                                        name="student_ids[]"
                                        value="{{ $s['student']->id }}"
                                        id="cb-{{ $s['student']->id }}"
                                        onchange="syncRow({{ $s['student']->id }}); recount();"
                                        style="width:17px;height:17px;cursor:pointer;">
                                </td>
                                <td>
                                    <div style="font-weight:700;font-size:.875rem;color:#1e293b;">
                                        {{ $name }}
                                    </div>
                                    <div style="margin-top:3px;">
                                        <span style="background:#f1f5f9;color:#475569;font-size:.7rem;font-weight:600;padding:2px 8px;border-radius:10px;">
                                            {{ $s['student']->student_id }}
                                        </span>
                                        @if($s['student']->program?->name)
                                        <span style="font-size:.72rem;color:#94a3b8;margin-left:4px;">
                                            · {{ $s['student']->program->name }}
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($email)
                                    <div style="font-size:.78rem;color:#475569;margin-bottom:2px;">
                                        <i class="fas fa-envelope me-1" style="color:#3b82f6;font-size:.65rem;"></i>{{ $email }}
                                    </div>
                                    @endif
                                    @if($phone)
                                    <div style="font-size:.78rem;color:#475569;">
                                        <i class="fas fa-phone me-1" style="color:#16a34a;font-size:.65rem;"></i>{{ $phone }}
                                    </div>
                                    @endif
                                    @if(!$email && !$phone)
                                    <span style="font-size:.72rem;background:var(--primary-pale);color:var(--primary-text);padding:2px 8px;border-radius:10px;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>No contact
                                    </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="balance-tag">GHS {{ number_format(max(0, $s['balance']), 2) }}</span>
                                </td>
                                <td onclick="event.stopPropagation()">
                                    <button type="button" class="peek-btn" title="Preview message"
                                        onclick="loadPreview({{ $s['student']->id }}, {{ $selectedSession?->id }}, '{{ addslashes($name) }}')">
                                        <i class="fas fa-eye" style="font-size:.7rem;"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:3.5rem 1rem;color:#94a3b8;">
                                    <div style="width:64px;height:64px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                                        <i class="fas fa-check-circle" style="font-size:1.5rem;color:#16a34a;"></i>
                                    </div>
                                    <div style="font-weight:700;color:#1e293b;margin-bottom:4px;">All Clear!</div>
                                    <div style="font-size:.83rem;">No students have outstanding fees for this session.</div>
                                </td>
                            </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Step 3: Send bar --}}
                    @if($summaries->count())
                    <div style="padding:1.25rem 1.5rem;background:#f8fafc;border-top:1px solid #f1f5f9;">
                        <div style="font-size:.8rem;color:#64748b;margin-bottom:14px;">
                            <span style="background:#0f3460;color:#fff;width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;margin-right:8px;">3</span>
                            <strong>Send</strong> – select students above, pick your channels, then fire.
                        </div>
                        <div class="d-flex gap-3 flex-wrap">
                            <button type="submit" class="btn-send-sel"
                                onclick="document.getElementById('sendMode').value='selected'; return validate()">
                                <i class="fas fa-paper-plane"></i>
                                Send to Selected
                                <span id="selBadge"
                                    style="background:rgba(0,0,0,.15);padding:1px 9px;border-radius:12px;font-size:.75rem;">
                                    0
                                </span>
                            </button>
                            <button type="submit" class="btn-send-all"
                                onclick="document.getElementById('sendMode').value='all'?'re running low on messaging credits.
                        <a href="{{ route('admin.subscription.show') }}" style="color:var(--primary-text);font-weight:700;text-decoration:underline;">
                            Top up here
                        </a>.
                    </div>
                </div>
                @endif

                <div class="rm-card">
                    <div class="rm-card-hd"
                        style="background:linear-gradient(135deg,#1e293b,#0f3460);color:#fff;border:none;">
                        <div>
                            <div style="font-weight:700;font-size:.9rem;">
                                <i class="fas fa-comment-dots me-2" style="color:var(--brand);"></i>Message Preview
                            </div>
                            <div id="pvName"
                                style="font-size:.75rem;opacity:.55;margin-top:3px;">
                                Click the <i class="fas fa-eye"></i> icon on any student
                            </div>
                        </div>
                    </div>

                    <div style="padding:1.5rem;">

                        {{-- Empty --}}
                        <div id="pvEmpty" style="text-align:center;padding:2rem 0;color:#94a3b8;">
                            <div style="width:56px;height:56px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                                <i class="fas fa-eye" style="color:#cbd5e1;font-size:1.25rem;"></i>
                            </div>
                            <div style="font-weight:600;font-size:.85rem;color:#475569;margin-bottom:4px;">No preview yet</div>
                            <div style="font-size:.78rem;line-height:1.5;">Click the eye icon next to any student to see their personalised message.</div>
                        </div>

                        {{-- Loading --}}
                        <div id="pvLoading" style="display:none;text-align:center;padding:2rem 0;color:#94a3b8;">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2" style="color:#0f3460;"></i>
                            <div style="font-size:.8rem;margin-top:8px;">Building preview...</div>
                        </div>

                        {{-- Content --}}
                        <div id="pvContent" style="display:none;">
                            <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;font-weight:700;margin-bottom:8px;">
                                <i class="fas fa-mobile-alt me-1"></i> SMS &amp; WhatsApp Message
                                <span style="float:right;text-transform:none;letter-spacing:0;font-weight:400;color:#3b82f6;cursor:pointer;" onclick="resetMsg()">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </span>
                            </div>
                            <textarea id="pvMsg"
                                rows="10"
                                style="width:100%;background:#f0f9ff;border:1px solid #bae6fd;border-radius:14px;padding:1.25rem;font-size:.83rem;line-height:1.8;color:#0c4a6e;font-family:inherit;resize:vertical;"
                                placeholder="Message will appear here..."></textarea>

                            <div style="background:#f8fafc;border-radius:12px;padding:1rem;margin-top:1rem;">
                                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;font-weight:700;margin-bottom:8px;">
                                    Sending to:
                                </div>
                                <div id="pvEmailRow" style="display:none;font-size:.82rem;color:#475569;margin-bottom:4px;">
                                    <i class="fas fa-envelope me-2" style="color:#3b82f6;width:14px;"></i>
                                    <span id="pvEmail"></span>
                                </div>
                                <div id="pvPhoneRow" style="display:none;font-size:.82rem;color:#475569;">
                                    <i class="fas fa-phone me-2" style="color:#16a34a;width:14px;"></i>
                                    <span id="pvPhone"></span>
                                </div>
                                <div id="pvNone" style="display:none;font-size:.8rem;color:#ef4444;">
                                    <i class="fas fa-exclamation-circle me-1"></i>No contact info on file
                                </div>
                            </div>

                            <button type="button" id="pvSendBtn"
                                onclick="quickSend()"
                                style="margin-top:1rem;width:100%;background:linear-gradient(135deg,#0f3460,#16213e);color:#fff;border:none;border-radius:12px;padding:11px;font-weight:700;font-size:.875rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .15s;"
                                onmouseover="this.style.opacity='.85'"
                                onmouseout="this.style.opacity='1'">
                                <i class="fas fa-paper-plane"></i> Send to This Student Only
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="pvStudentId" value="">
<input type="hidden" id="pvSessionId" value="{{ $selectedSession?->id }}">
<input type="hidden" name="custom_message" id="customMsgInput" form="rmForm" value="">

@push('scripts'?'on-sms', wa: 'on-wa', mail: 'on-mail' };
    const cbMap = { sms: 'chSms', wa: 'chWa', mail: 'chEmail' };
    document.getElementById(cbMap[key]).checked = chActive[key];
    if (chActive[key]) {
        pill.classList.add(classMap[key]);
    } else {
        pill.classList.remove(classMap[key]);
    }
    document.getElementById('chWarnText').style.display = 'none'?'cb-' + id);
    cb.checked = !cb.checked;
    syncRow(id);
    recount();
}
function syncRow(id) {
    const cb  = document.getElementById('cb-' + id);
    const row = document.getElementById('row-' + id);
    if (row) row.classList.toggle('picked', cb.checked);
}
function toggleAll(master) {
    document.querySelectorAll('.student-cb').forEach(cb => {
        cb.checked = master.checked;
        const row = document.getElementById('row-' + cb.value);
        if (row) row.classList.toggle('picked', master.checked);
    });
    recount();
}
function recount() {
    const n = document.querySelectorAll('.student-cb:checked').length;
    document.getElementById('countBadge').textContent = n + ' selected';
    document.getElementById('selBadge'?'chWarnText').style.display = 'inline';
        return false;
    }
    const n = document.querySelectorAll('.student-cb:checked').length;
    if (n === 0) { alert('Please select at least one student.'); return false; }
    return confirm('Send reminder to ' + n + ' selected student(s)?');
}
function validateAll() {
    if (!anyChannel()) {
        document.getElementById('chWarnText').style.display = 'inline';
        return false;
    }
    const total = document.querySelectorAll('.student-cb').length;
    return confirm('Send reminders to ALL ' + total + ' owing student(s)?'?'';

function loadPreview(studentId, sessionId, name) {
    document.getElementById('pvStudentId').value = studentId;
    document.getElementById('pvName').textContent = name;
    document.getElementById('pvEmpty').style.display   = 'none';
    document.getElementById('pvContent').style.display = 'none';
    document.getElementById('pvLoading').style.display = 'block';

    fetch('{{ route('admin.fees.reminders.preview') }}?student_id=' + studentId + '&session_id=' + sessionId)
        .then(r => r.json())
        .then(d => {
            document.getElementById('pvLoading').style.display = 'none';
            document.getElementById('pvContent').style.display = 'block';
            // Editable textarea
            originalMsg = d.message;
            document.getElementById('pvMsg').value = d.message;

            const hasEmail = !!d.email;
            const hasPhone = !!d.phone;

            document.getElementById('pvEmailRow').style.display = hasEmail ? 'block' : 'none';
            if (hasEmail) document.getElementById('pvEmail').textContent = d.email;

            document.getElementById('pvPhoneRow').style.display = hasPhone ? 'block' : 'none';
            if (hasPhone) document.getElementById('pvPhone').textContent = d.phone;

            document.getElementById('pvNone').style.display = (!hasEmail && !hasPhone) ? 'block' : 'none';
        })
        .catch(() => {
            document.getElementById('pvLoading').style.display = 'none';
            document.getElementById('pvContent').style.display = 'block';
            document.getElementById('pvMsg').value = 'Could not load preview. Please try again.';
        });
}

function resetMsg() {
    document.getElementById('pvMsg'?'Please select at least one channel (SMS, WhatsApp, or Email) first.');
        return;
    }
    const id = document.getElementById('pvStudentId').value;
    if (!id) { alert('No student selected for preview.'); return; }

    // Pass the (possibly edited) message
    document.getElementById('customMsgInput').value = document.getElementById('pvMsg').value;

    // Deselect all, select only this one
    document.querySelectorAll('.student-cb').forEach(cb => {
        cb.checked = false;
        const row = document.getElementById('row-' + cb.value);
        if (row) row.classList.remove('picked');
    });
    const cb = document.getElementById('cb-' + id);
    if (cb) { cb.checked = true; syncRow(id); recount(); }
    document.getElementById('sendMode').value = 'selected';

    if (confirm('Send this message to the student now?')) {
        document.getElementById('rmForm').submit();
    }
}
</script>
@endpush
@endsection
