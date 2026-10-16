@extends('layouts.app')
@section('title', 'Term Fee Ledger')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Term Fee Ledger</h4>
            <p class="text-muted small mb-0">Track fees billed, paid and owed per student per term</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fees.reminders.index') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-bell me-1"></i> Send Reminders
            </a>
            <a href="{{ route('admin.fees.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Fees
            </a>
        </div>
    </div>

    {{-- Session + Filter --}}
    <form method="GET" class="card card-body mb-4 border-0 shadow-sm" style="border-radius:12px;">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Academic Session</label>
                <select name="session_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($sessions as $s)
                        <option value="{{ $s->id }}" {{ $selectedSession?->id == $s->id ? 'selected' : '' }}>
                            {{ $s->year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Programme</label>
                <select name="program_id" class="form-select form-select-sm">
                    <option value="">All Programmes</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="owing_only" value="1" id="owingOnly" {{ request('owing_only') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="owingOnly">Show owing only</label>
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
            @if($selectedSession)
            <div class="col-md-2">
                <form method="POST" action="{{ route('admin.fees.term.bulk-bill') }}" onsubmit="return confirm('Bill all students for {{ $selectedSession->year }} from their programme fees?')">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                    <input type="hidden" name="program_id" value="{{ request('program_id') }}">
                    <button class="btn btn-success btn-sm w-100">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Auto-Bill
                    </button>
                </form>
            </div>
            @endif
        </div>
    </form>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;">
        {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats --}}
    @if($summaries->count())
    @php
        $totalBilled = $summaries->sum('total_billed');
        $totalPaid   = $summaries->sum('total_paid');
        $totalOwed   = $summaries->sum(fn($s) => max(0, $s['balance']));
        $owingCount  = $summaries->filter(fn($s) => $s['is_owing'])->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
                <div class="card-body py-3">
                    <div style="font-size:1.4rem;font-weight:800;color:#1e293b;">{{ $summaries->count() }}</div>
                    <div class="text-muted small">Students</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
                <div class="card-body py-3">
                    <div style="font-size:1.4rem;font-weight:800;color:#1e293b;">GHS {{ number_format($totalBilled, 2) }}</div>
                    <div class="text-muted small">Total Billed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
                <div class="card-body py-3">
                    <div style="font-size:1.4rem;font-weight:800;color:#16a34a;">GHS {{ number_format($totalPaid, 2) }}</div>
                    <div class="text-muted small">Total Paid</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
                <div class="card-body py-3">
                    <div style="font-size:1.4rem;font-weight:800;color:#ef4444;">GHS {{ number_format($totalOwed, 2) }}</div>
                    <div class="text-muted small">Total Owed ({{ $owingCount }} students)</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Ledger Table --}}
    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;padding:12px 16px;">Student</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">Programme</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;text-align:right;">Billed</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;text-align:right;">Paid</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;text-align:right;">Balance</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">Status</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($summaries as $s)
                <tr>
                    <td style="padding:12px 16px;">
                        <div style="font-weight:600;font-size:0.875rem;">{{ $s['student']->user?->full_name ?? '—' }}</div>
                        <div style="font-size:0.75rem;color:#94a3b8;">{{ $s['student']->student_id }}</div>
                    </td>
                    <td style="font-size:0.875rem;">{{ $s['student']->program?->name ?? '—' }}</td>
                    <td style="text-align:right;font-size:0.875rem;">GHS {{ number_format($s['total_billed'], 2) }}</td>
                    <td style="text-align:right;font-size:0.875rem;color:#16a34a;font-weight:600;">GHS {{ number_format($s['total_paid'], 2) }}</td>
                    <td style="text-align:right;font-size:0.875rem;font-weight:700;color:{{ $s['balance'] > 0 ? '#ef4444' : '#16a34a' }};">
                        GHS {{ number_format(max(0, $s['balance']), 2) }}
                    </td>
                    <td>
                        @if($s['is_paid'])
                            <span class="badge" style="background:#dcfce7;color:#16a34a;font-size:0.72rem;">Paid</span>
                        @else
                            <span class="badge" style="background:#fef2f2;color:#ef4444;font-size:0.72rem;">Owing</span>
                        @endif
                        @if(!$s['has_bill'])
                            <span class="badge bg-secondary" style="font-size:0.68rem;">Auto</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.fees.term.student-history', $s['student']) }}"
                               class="btn btn-xs btn-outline-secondary" style="font-size:0.72rem;padding:3px 8px;" title="Full History">
                                <i class="fas fa-history"></i>
                            </a>
                            @if($selectedSession)
                            <button class="btn btn-xs btn-outline-warning"
                                style="font-size:0.72rem;padding:3px 8px;"
                                title="Send Reminder"
                                onclick="sendOneReminder({{ $s['student']->id }}, {{ $selectedSession->id }}, '{{ addslashes($s['student']->user?->full_name) }}')">
                                <i class="fas fa-bell"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No students found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Quick Send Reminder Modal --}}
<div class="modal fade" id="quickReminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fas fa-bell me-2"></i>Send Reminder</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.fees.reminders.send') }}">
                @csrf
                <input type="hidden" name="mode" value="one">
                <input type="hidden" name="session_id" id="reminderSessionId" value="{{ $selectedSession?->id }}">
                <input type="hidden" name="student_ids[]" id="reminderStudentId">
                <div class="modal-body">
                    <p>Send fee reminder to: <strong id="reminderStudentName"></strong></p>
                    <div id="messagePreview" style="background:#f8fafc;border-radius:8px;padding:12px;font-size:0.8rem;white-space:pre-wrap;max-height:200px;overflow-y:auto;color:#475569;" class="mb-3">
                        Loading preview...
                    </div>
                    <label class="form-label fw-semibold">Send via:</label>
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
                            <label class="form-check-label" for="chWa"><i class="fab fa-whatsapp me-1 text-success"></i>WhatsApp</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning fw-semibold">
                        <i class="fas fa-paper-plane me-1"></i> Send Reminder
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sendOneReminder(studentId, sessionId, studentName) {
    document.getElementById('reminderStudentId').value   = studentId;
    document.getElementById('reminderSessionId').value   = sessionId;
    document.getElementById('reminderStudentName').textContent = studentName;
    document.getElementById('messagePreview').textContent = 'Loading preview...';

    fetch('{{ route('admin.fees.reminders.preview') }}?student_id=' + studentId + '&session_id=' + sessionId)
        .then(r => r.json())
        .then(data => {
            document.getElementById('messagePreview').textContent = data.message;
        })
        .catch(() => {
            document.getElementById('messagePreview').textContent = 'Could not load preview.';
        });

    new bootstrap.Modal(document.getElementById('quickReminderModal')).show();
}
</script>
@endpush
@endsection
