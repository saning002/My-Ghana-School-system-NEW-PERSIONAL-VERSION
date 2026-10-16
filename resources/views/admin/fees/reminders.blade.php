@extends('layouts.app')
@section('title', 'Fee Reminders')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Fee Reminders</h4>
            <p class="text-muted small mb-0">Send personalised fee reminders to parents/students via Email, SMS or WhatsApp</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fees.reminders.log') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-history me-1"></i> Reminder Log
            </a>
            <a href="{{ route('admin.fees.term.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Term Ledger
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" style="border-radius:10px;">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px;">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">

        {{-- LEFT: Session selector + student list --}}
        <div class="col-lg-8">

            {{-- Session filter --}}
            <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;">
                <div class="card-body py-3">
                    <form method="GET" class="d-flex gap-3 align-items-center flex-wrap">
                        <div>
                            <label class="form-label fw-semibold small mb-1">Academic Session</label>
                            <select name="session_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:180px;">
                                @foreach($sessions as $s)
                                    <option value="{{ $s->id }}" {{ $selectedSession?->id == $s->id ? 'selected' : '' }}>{{ $s->year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ms-auto" style="font-size:0.85rem;color:#64748b;">
                            Showing <strong>{{ $summaries->count() }}</strong> students with outstanding fees
                        </div>
                    </form>
                </div>
            </div>

            {{-- Student table with checkboxes --}}
            <form method="POST" action="{{ route('admin.fees.reminders.send') }}" id="bulkReminderForm">
                @csrf
                <input type="hidden" name="session_id" value="{{ $selectedSession?->id }}">
                <input type="hidden" name="mode" value="selected" id="sendMode">

                <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                    <div class="card-header d-flex align-items-center justify-content-between py-2 px-3" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleAll(this)">
                            <label class="form-check-label fw-semibold small" for="selectAll">Select All</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="previewSelected()">
                                <i class="fas fa-eye me-1"></i> Preview
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th style="width:40px;padding:10px 16px;"></th>
                                    <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">Student</th>
                                    <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">Contact</th>
                                    <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;text-align:right;">Outstanding</th>
                                    <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">Preview</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($summaries as $s)
                            @php
                                $phone = $s['student']->user?->phone ?? $s['student']->guardian_phone ?? null;
                                $email = $s['student']->user?->email ?? $s['student']->guardian_email ?? null;
                            @endphp
                            <tr>
                                <td style="padding:10px 16px;">
                                    <input class="form-check-input student-checkbox" type="checkbox"
                                        name="student_ids[]"
                                        value="{{ $s['student']->id }}">
                                </td>
                                <td>
                                    <div style="font-weight:600;font-size:0.875rem;">{{ $s['student']->user?->full_name ?? '—' }}</div>
                                    <div style="font-size:0.75rem;color:#94a3b8;">{{ $s['student']->student_id }} · {{ $s['student']->program?->name }}</div>
                                </td>
                                <td>
                                    <div style="font-size:0.8rem;">
                                        @if($email) <div><i class="fas fa-envelope text-primary me-1" style="font-size:0.7rem;"></i>{{ $email }}</div> @endif
                                        @if($phone) <div><i class="fas fa-phone text-success me-1" style="font-size:0.7rem;"></i>{{ $phone }}</div> @endif
                                        @if(!$email && !$phone) <span class="text-muted">No contact</span> @endif
                                    </div>
                                </td>
                                <td style="text-align:right;font-weight:700;color:#ef4444;font-size:0.9rem;">
                                    GHS {{ number_format(max(0, $s['balance']), 2) }}
                                </td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-outline-secondary"
                                        style="font-size:0.72rem;padding:3px 8px;"
                                        onclick="showPreview({{ $s['student']->id }}, {{ $selectedSession?->id }}, '{{ addslashes($s['student']->user?->full_name) }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No students with outstanding fees for this session.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Channel selector + Send buttons --}}
                @if($summaries->count())
                <div class="card border-0 shadow-sm mt-3" style="border-radius:12px;">
                    <div class="card-body">
                        <div class="row align-items-center g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold small">Send via:</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="channels[]" value="email" id="chEmail" checked>
                                        <label class="form-check-label" for="chEmail"><i class="fas fa-envelope me-1 text-primary"></i>Email</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="channels[]" value="sms" id="chSms">
                                        <label class="form-check-label" for="chSms"><i class="fas fa-sms me-1 text-success"></i>SMS</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="channels[]" value="whatsapp" id="chWa">
                                        <label class="form-check-label" for="chWa"><i class="fab fa-whatsapp me-1" style="color:#25d366;"></i>WhatsApp</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7 d-flex gap-2 justify-content-end flex-wrap">
                                <button type="submit" class="btn btn-warning fw-semibold"
                                    onclick="document.getElementById('sendMode').value='selected'"
                                    onsubmit="return confirm('Send to selected students?')">
                                    <i class="fas fa-paper-plane me-1"></i> Send to Selected
                                </button>
                                <button type="submit" class="btn btn-danger fw-semibold"
                                    onclick="document.getElementById('sendMode').value='all'"
                                    onsubmit="return confirm('Send to ALL {{ $summaries->count() }} owing students?')">
                                    <i class="fas fa-broadcast-tower me-1"></i> Send to All ({{ $summaries->count() }})
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </form>
        </div>

        {{-- RIGHT: Message preview --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
                <div class="card-header py-3" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                    <h6 class="fw-bold mb-0"><i class="fas fa-eye me-2"></i>Message Preview</h6>
                    <p class="text-muted small mb-0" id="previewStudentName">Click preview on any student to see their personalised message</p>
                </div>
                <div class="card-body">
                    <div id="previewBox" style="background:#f8fafc;border-radius:8px;padding:14px;font-size:0.8rem;white-space:pre-wrap;color:#475569;min-height:300px;line-height:1.6;">
                        Select a student and click the eye icon to preview their message.
                    </div>
                    <div class="mt-3" id="previewContacts" style="display:none;">
                        <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Will be sent to:</div>
                        <div id="previewEmail" style="font-size:0.82rem;"></div>
                        <div id="previewPhone" style="font-size:0.82rem;"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function toggleAll(checkbox) {
    document.querySelectorAll('.student-checkbox').forEach(c => c.checked = checkbox.checked);
}

function showPreview(studentId, sessionId, name) {
    document.getElementById('previewStudentName').textContent = name;
    document.getElementById('previewBox').textContent = 'Loading...';
    document.getElementById('previewContacts').style.display = 'none';

    fetch('{{ route('admin.fees.reminders.preview') }}?student_id=' + studentId + '&session_id=' + sessionId)
        .then(r => r.json())
        .then(data => {
            document.getElementById('previewBox').textContent = data.message;
            document.getElementById('previewContacts').style.display = 'block';
            document.getElementById('previewEmail').innerHTML = data.email
                ? '<i class="fas fa-envelope text-primary me-1"></i>' + data.email
                : '<span class="text-muted">No email on file</span>';
            document.getElementById('previewPhone').innerHTML = data.phone
                ? '<i class="fas fa-phone text-success me-1"></i>' + data.phone
                : '<span class="text-muted">No phone on file</span>';
        })
        .catch(() => {
            document.getElementById('previewBox').textContent = 'Could not load preview.';
        });
}
</script>
@endpush
@endsection
