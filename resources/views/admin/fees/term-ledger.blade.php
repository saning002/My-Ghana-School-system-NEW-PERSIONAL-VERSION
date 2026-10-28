@extends('layouts.app')
@section('title', 'Term Fee Ledger')

@section('content')
<style>
.tl-hero{background:linear-gradient(135deg,#0f3460 0%,#16213e 100%);border-radius:20px;padding:2rem 2.5rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;}
.tl-hero::before{content:'';position:absolute;right:-50px;top:-50px;width:250px;height:250px;background:rgba(255,255,255,.04);border-radius:50%;}
.tl-hero::after{content:'';position:absolute;right:80px;bottom:-70px;width:160px;height:160px;background:rgba(255,255,255,.03);border-radius:50%;}
.tl-stat{background:#fff;border-radius:14px;padding:1.25rem 1.5rem;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem;height:100%;}
.tl-stat-icon{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;}
.tl-card{background:#fff;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.5rem;}
.tl-card-hd{padding:1rem 1.5rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;}
.tl-thead th{font-size:.7rem;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;font-weight:700;padding:10px 16px;background:#f8fafc;border:none;}
.tl-row{transition:background .1s;}
.tl-row:hover{background:#f8fafc;}
.tl-row td{padding:13px 16px;border-color:#f1f5f9;vertical-align:middle;}
.paid-badge{background:#f0fdf4;color:#16a34a;font-weight:700;padding:4px 12px;border-radius:20px;font-size:.78rem;display:inline-block;}
.owed-badge{background:#fef2f2;color:#dc2626;font-weight:700;padding:4px 12px;border-radius:20px;font-size:.78rem;display:inline-block;}
.nobill-badge{background:#f1f5f9;color:#64748b;font-size:.7rem;padding:2px 8px;border-radius:10px;display:inline-block;margin-left:4px;}
.action-btn{width:30px;height:30px;border-radius:8px;border:none;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;font-size:.7rem;cursor:pointer;}
.action-btn:hover{transform:scale(1.1);}

/* Modal */
.rm-modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1055;display:flex;align-items:center;justify-content:center;padding:1rem;}
.rm-modal{background:#fff;border-radius:20px;width:100%;max-width:520px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.ch-pill{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:50px;border:2px solid #e2e8f0;cursor:pointer;font-size:.875rem;font-weight:600;background:#fff;user-select:none;transition:all .15s;}
.ch-pill:hover{border-color:#94a3b8;}
.ch-pill.on-sms{border-color:#16a34a;background:#f0fdf4;color:#15803d;}
.ch-pill.on-wa{border-color:#25d366;background:#f0fdf4;color:#15803d;}
.ch-pill.on-mail{border-color:#3b82f6;background:#eff6ff;color:#1d4ed8;}
</style>

<div class="container-fluid py-4" style="max-width:1300px;">

    {{-- Hero --}}
    <div class="tl-hero">
        <div style="position:relative;z-index:1;" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;opacity:.55;margin-bottom:4px;">Fees &rsaquo; Term Ledger</div>
                <h3 class="fw-black mb-1" style="font-size:1.6rem;">
                    <i class="fas fa-file-invoice-dollar me-2" style="color:var(--brand);"></i>Term Fee Ledger
                </h3>
                <p style="opacity:.65;font-size:.875rem;margin:0;">Track fees billed, paid and owed per student per term</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.fees.reminders.index') }}"
                   style="background:rgba(245,158,11,.2);color:#fde68a;border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:9px 18px;font-weight:700;font-size:.85rem;text-decoration:none;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-paper-plane"></i> Send Reminders
                </a>
                <a href="{{ route('admin.fees.index') }}"
                   style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:9px 18px;font-weight:600;font-size:.85rem;text-decoration:none;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-arrow-left"></i> Back to Fees
                </a>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-dismissible fade show mb-4" style="border-radius:12px;border:none;background:#f0fdf4;color:#166534;border-left:4px solid #16a34a;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-dismissible fade show mb-4" style="border-radius:12px;border:none;background:#fef2f2;color:#991b1b;border-left:4px solid #dc2626;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filter bar --}}
    <div class="tl-card">
        <div class="tl-card-hd">
            <span style="font-weight:700;font-size:.9rem;"><i class="fas fa-filter me-2 text-primary"></i>Filter</span>
        </div>
        <div style="padding:1rem 1.5rem;">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold" style="font-size:.8rem;">Academic Session</label>
                    <select name="session_id" class="form-select" style="border-radius:10px;" onchange="this.form.submit()">
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}" {{ $selectedSession?->id == $s->id ? 'selected' : '' }}>{{ $s->year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold" style="font-size:.8rem;">Programme</label>
                    <select name="program_id" class="form-select" style="border-radius:10px;">
                        <option value="">All Programmes</option>
                        @foreach($programs as $p)
                            <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="owing_only" value="1"
                            id="owingOnly" {{ request('owing_only') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="owingOnly" style="font-size:.85rem;">Owing only</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100 fw-bold" style="border-radius:10px;">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
                @if($selectedSession)
                <div class="col-md-2">
                    <form method="POST" action="{{ route('admin.fees.term.bulk-bill') }}"
                        onsubmit="return confirm('Auto-bill all students for {{ $selectedSession->year }}?')">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $selectedSession->id }}">
                        <input type="hidden" name="program_id" value="{{ request('program_id') }}">
                        <button class="btn btn-success w-100 fw-bold" style="border-radius:10px;">
                            <i class="fas fa-file-invoice-dollar me-1"></i> Auto-Bill
                        </button>
                    </form>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Stats --}}
    @if($summaries->count())
    @php
        $totalBilled = $summaries->sum('total_billed');
        $totalPaid   = $summaries->sum('total_paid');
        $totalOwed   = $summaries->sum(fn($s) => max(0, $s['balance']));
        $owingCount  = $summaries->filter(fn($s) => $s['is_owing'])->count();
        $paidCount   = $summaries->filter(fn($s) => $s['is_paid'])->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="tl-stat">
                <div class="tl-stat-icon" style="background:#eff6ff;color:#3b82f6;"><i class="fas fa-users"></i></div>
                <div>
                    <div style="font-size:1.5rem;font-weight:800;line-height:1;color:#1e293b;">{{ $summaries->count() }}</div>
                    <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-top:2px;">Students</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="tl-stat">
                <div class="tl-stat-icon" style="background:var(--primary-pale);color:var(--primary-dark);"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <div style="font-size:1.1rem;font-weight:800;line-height:1;color:#1e293b;">GHS {{ number_format($totalBilled,2) }}</div>
                    <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-top:2px;">Total Billed</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="tl-stat">
                <div class="tl-stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div style="font-size:1.1rem;font-weight:800;line-height:1;color:#16a34a;">GHS {{ number_format($totalPaid,2) }}</div>
                    <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-top:2px;">Total Paid ({{ $paidCount }} students)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="tl-stat">
                <div class="tl-stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="fas fa-exclamation-circle"></i></div>
                <div>
                    <div style="font-size:1.1rem;font-weight:800;line-height:1;color:#dc2626;">GHS {{ number_format($totalOwed,2) }}</div>
                    <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-top:2px;">Total Owed ({{ $owingCount }} students)</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Ledger table --}}
    <div class="tl-card">
        <div class="tl-card-hd">
            <span style="font-weight:700;font-size:.9rem;"><i class="fas fa-table me-2 text-primary"></i>Student Ledger – {{ $selectedSession?->year }}</span>
            <span style="font-size:.78rem;color:#64748b;">{{ $summaries->count() }} record(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="tl-thead">
                    <tr>
                        <th>Student</th>
                        <th>Programme</th>
                        <th class="text-end">Billed</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                        <th style="width:110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($summaries as $s)
                <tr class="tl-row">
                    <td>
                        <div style="font-weight:700;font-size:.875rem;color:#1e293b;">{{ $s['student']->user?->full_name ?? '–' }}</div>
                        <span style="background:#f1f5f9;color:#475569;font-size:.7rem;font-weight:600;padding:2px 8px;border-radius:10px;">
                            {{ $s['student']->student_id }}
                        </span>
                    </td>
                    <td style="font-size:.875rem;color:#475569;">{{ $s['student']->program?->name ?? '–' }}</td>
                    <td class="text-end" style="font-size:.875rem;">GHS {{ number_format($s['total_billed'],2) }}</td>
                    <td class="text-end" style="font-size:.875rem;font-weight:600;color:#16a34a;">GHS {{ number_format($s['total_paid'],2) }}</td>
                    <td class="text-end">
                        <span style="font-size:.9rem;font-weight:800;color:{{ $s['balance'] > 0 ? '#dc2626' : '#16a34a' }};">
                            GHS {{ number_format(max(0,$s['balance']),2) }}
                        </span>
                    </td>
                    <td>
                        @if($s['is_paid'])
                            <span class="paid-badge"><i class="fas fa-check me-1"></i>Paid</span>
                        @else
                            <span class="owed-badge"><i class="fas fa-clock me-1"></i>Owing</span>
                        @endif
                        @if(!$s['has_bill'])
                            <span class="nobill-badge">No Bill</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.fees.term.student-history', $s['student']) }}"
                               class="action-btn" style="background:#eff6ff;color:#3b82f6;" title="Full fee history">
                                <i class="fas fa-history"></i>
                            </a>
                            @if($selectedSession)
                            <button class="action-btn" style="background:var(--primary-pale);color:var(--primary-dark);" title="Send reminder"
                                onclick="openReminderModal({{ $s['student']->id }}, {{ $selectedSession->id }}, '{{ addslashes($s['student']->user?->full_name) }}')">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            @endif
                            <button class="action-btn" style="background:#f0fdf4;color:#16a34a;" title="Record payment"
                                onclick="openPaymentModal({{ $s['student']->id }}, '{{ addslashes($s['student']->user?->full_name) }}', {{ $selectedSession?->id ?? 'null' }})">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:3.5rem;color:#94a3b8;">
                        <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
                        No students found for this session and filter.
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- �"?�"? Reminder Modal �"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"? --}}
<div id="reminderModal" class="rm-modal-bg" style="display:none;" onclick="if(event.target===this)closeModals()">
    <div class="rm-modal">
        <div style="background:linear-gradient(135deg,#0f3460,#16213e);padding:1.25rem 1.5rem;color:#fff;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-weight:700;font-size:.95rem;"><i class="fas fa-paper-plane me-2" style="color:var(--brand);"></i>Send Fee Reminder</div>
                <div id="rmStudentName" style="font-size:.78rem;opacity:.6;margin-top:2px;"></div>
            </div>
            <button onclick="closeModals()" style="background:rgba(255,255,255,.1);border:none;color:#fff;width:30px;height:30px;border-radius:8px;cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.fees.reminders.send') }}" id="reminderForm">
            @csrf
            <input type="hidden" name="mode" value="selected">
            <input type="hidden" name="session_id" id="rmSessionId">
            <input type="hidden" name="student_ids[]" id="rmStudentId">
            <input type="hidden" name="custom_message" id="rmCustomMsg">

            <div style="padding:1.5rem;">
                {{-- Channels --}}
                <div style="margin-bottom:1.25rem;">
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;font-weight:700;margin-bottom:8px;">Send Via</div>
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="ch-pill" id="rmPillSms" onclick="toggleRmCh('sms',this)">
                            <input type="checkbox" name="channels[]" value="sms" id="rmChSms" style="display:none;">
                            <i class="fas fa-sms" style="color:#16a34a;"></i> SMS
                        </div>
                        <div class="ch-pill" id="rmPillWa" onclick="toggleRmCh('wa',this)">
                            <input type="checkbox" name="channels[]" value="whatsapp" id="rmChWa" style="display:none;">
                            <i class="fab fa-whatsapp" style="color:#25d366;"></i> WhatsApp
                        </div>
                        <div class="ch-pill" id="rmPillMail" onclick="toggleRmCh('mail',this)">
                            <input type="checkbox" name="channels[]" value="email" id="rmChEmail" style="display:none;">
                            <i class="fas fa-envelope" style="color:#3b82f6;"></i> Email
                        </div>
                    </div>
                    <div id="rmChWarn" style="display:none;font-size:.78rem;color:#dc2626;margin-top:6px;">
                        <i class="fas fa-exclamation-circle me-1"></i>Select at least one channel
                    </div>
                </div>

                {{-- Message preview / edit --}}
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;font-weight:700;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                        <span><i class="fas fa-mobile-alt me-1"></i> Message (Editable)</span>
                        <span style="text-transform:none;letter-spacing:0;font-weight:400;color:#3b82f6;cursor:pointer;" onclick="resetRmMsg()">
                            <i class="fas fa-undo me-1"></i>Reset
                        </span>
                    </div>
                    <div id="rmPreviewLoading" style="text-align:center;padding:1.5rem;color:#94a3b8;">
                        <i class="fas fa-spinner fa-spin"></i> Loading preview...
                    </div>
                    <textarea id="rmPreviewBox" rows="8"
                        style="display:none;width:100%;background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;padding:1rem;font-size:.82rem;line-height:1.75;color:#0c4a6e;resize:vertical;font-family:inherit;"
                        placeholder="Message will appear here..."></textarea>
                </div>
            </div>

            <div style="padding:1rem 1.5rem;background:#f8fafc;border-top:1px solid #f1f5f9;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeModals()"
                    style="background:#e2e8f0;border:none;border-radius:10px;padding:10px 20px;font-weight:600;font-size:.875rem;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" onclick="return submitRmForm()"
                    style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;border:none;border-radius:10px;padding:10px 24px;font-weight:700;font-size:.875rem;cursor:pointer;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-paper-plane"></i> Send Reminder
                </button>
            </div>
        </form>
    </div>
</div>

{{-- �"?�"? Payment Modal �"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"? --}}
<div id="paymentModal" class="rm-modal-bg" style="display:none;" onclick="if(event.target===this)closeModals()">
    <div class="rm-modal">
        <div style="background:linear-gradient(135deg,#16a34a,#15803d);padding:1.25rem 1.5rem;color:#fff;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-weight:700;font-size:.95rem;"><i class="fas fa-plus me-2"></i>Record Payment</div>
                <div id="pmStudentName" style="font-size:.78rem;opacity:.6;margin-top:2px;"></div>
            </div>
            <button onclick="closeModals()" style="background:rgba(255,255,255,.1);border:none;color:#fff;width:30px;height:30px;border-radius:8px;cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.fees.term.payment') }}">
            @csrf
            <input type="hidden" name="student_id" id="pmStudentId">
            <input type="hidden" name="session_id" id="pmSessionId">
            <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;display:block;margin-bottom:6px;">Amount (GHS) *</label>
                    <input type="number" name="amount_paid" step="0.01" min="0.01" required
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:.9rem;font-weight:600;">
                </div>
                <div>
                    <label style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;display:block;margin-bottom:6px;">Payment Date *</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:.875rem;">
                </div>
                <div>
                    <label style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;display:block;margin-bottom:6px;">Payment Method *</label>
                    <select name="payment_method" required
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:.875rem;">
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;display:block;margin-bottom:6px;">Payer Name</label>
                    <input type="text" name="payer_name"
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:.875rem;"
                        placeholder="Parent / Guardian name">
                </div>
                <div>
                    <label style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;display:block;margin-bottom:6px;">Notes</label>
                    <input type="text" name="notes"
                        style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:.875rem;"
                        placeholder="Optional note...">
                </div>
            </div>
            <div style="padding:1rem 1.5rem;background:#f8fafc;border-top:1px solid #f1f5f9;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeModals()"
                    style="background:#e2e8f0;border:none;border-radius:10px;padding:10px 20px;font-weight:600;font-size:.875rem;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                    style="background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border:none;border-radius:10px;padding:10px 24px;font-weight:700;font-size:.875rem;cursor:pointer;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-check"></i> Record & Print Receipt
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// �"?�"? Channel toggles for reminder modal �"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?
const rmCh = {sms:false, wa:false, mail:false};
function toggleRmCh(key, pill) {
    rmCh[key] = !rmCh[key];
    const cbMap = {sms:'rmChSms', wa:'rmChWa', mail:'rmChEmail'};
    const clMap = {sms:'on-sms', wa:'on-wa', mail:'on-mail'};
    document.getElementById(cbMap[key]).checked = rmCh[key];
    pill.classList.toggle(clMap[key], rmCh[key]);
    document.getElementById('rmChWarn').style.display = 'none';
}
function anyRmCh() { return Object.values(rmCh).some(Boolean); }

// �"?�"? Reminder modal �"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?
let rmOrigMsg = '';
function openReminderModal(studentId, sessionId, name) {
    document.getElementById('rmStudentId').value = studentId;
    document.getElementById('rmSessionId').value = sessionId;
    document.getElementById('rmStudentName').textContent = name;
    document.getElementById('rmPreviewLoading').style.display = 'block';
    document.getElementById('rmPreviewBox').style.display    = 'none';
    document.getElementById('reminderModal').style.display   = 'flex';

    fetch('{{ route('admin.fees.reminders.preview') }}?student_id=' + studentId + '&session_id=' + sessionId)
        .then(r => r.json())
        .then(d => {
            rmOrigMsg = d.message;
            document.getElementById('rmPreviewBox').value   = d.message;
            document.getElementById('rmPreviewLoading').style.display = 'none';
            document.getElementById('rmPreviewBox').style.display    = 'block';
        })
        .catch(() => {
            document.getElementById('rmPreviewBox').value   = 'Could not load preview.';
            document.getElementById('rmPreviewLoading').style.display = 'none';
            document.getElementById('rmPreviewBox').style.display    = 'block';
        });
}
function resetRmMsg() { document.getElementById('rmPreviewBox').value = rmOrigMsg; }
function submitRmForm() {
    if (!anyRmCh()) {
        document.getElementById('rmChWarn').style.display = 'block';
        return false;
    }
    document.getElementById('rmCustomMsg').value = document.getElementById('rmPreviewBox').value;
    return confirm('Send this reminder to the student?');
}

// �"?�"? Payment modal �"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?
function openPaymentModal(studentId, name, sessionId) {
    document.getElementById('pmStudentId').value  = studentId;
    document.getElementById('pmSessionId').value  = sessionId || '';
    document.getElementById('pmStudentName').textContent = name;
    document.getElementById('paymentModal').style.display = 'flex';
}

function closeModals() {
    document.getElementById('reminderModal').style.display = 'none';
    document.getElementById('paymentModal').style.display  = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModals(); });
</script>
@endpush
@endsection
