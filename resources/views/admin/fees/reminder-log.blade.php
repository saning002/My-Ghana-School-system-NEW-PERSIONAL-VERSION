@extends('layouts.app')
@section('title', 'Reminder Log')

@section('content')
<style>
.rl-hero{background:linear-gradient(135deg,#1e293b 0%,var(--admin-nav) 100%);border-radius:20px;padding:2rem 2.5rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;}
.rl-hero::before{content:'';position:absolute;right:-50px;top:-50px;width:220px;height:220px;background:rgba(255,255,255,.04);border-radius:50%;}
.rl-card{background:#fff;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden;}
.rl-thead th{font-size:.7rem;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;font-weight:700;padding:10px 16px;background:#f8fafc;border:none;}
.rl-row:hover{background:#f8fafc;}
.rl-row td{padding:12px 16px;border-color:#f1f5f9;vertical-align:middle;}
.ch-sms  {background:#f0fdf4;color:#15803d;font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:5px;}
.ch-wa   {background:#f0fdf4;color:#15803d;font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:5px;}
.ch-email{background:#eff6ff;color:#1d4ed8;font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:5px;}
.st-sent  {background:#f0fdf4;color:#16a34a;font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:20px;}
.st-failed{background:#fef2f2;color:#dc2626;font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:20px;}
.st-other {background:#f1f5f9;color:#64748b;font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:20px;}
</style>

<div class="container-fluid py-4" style="max-width:1200px;">

    {{-- Hero --}}
    <div class="rl-hero">
        <div style="position:relative;z-index:1;" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;opacity:.55;margin-bottom:4px;">Fees &rsaquo; Reminder Log</div>
                <h3 class="fw-black mb-1" style="font-size:1.5rem;">
                    <i class="fas fa-history me-2" style="color:var(--brand);"></i>Reminder Log
                </h3>
                <p style="opacity:.65;font-size:.875rem;margin:0;">
                    Full history of every reminder sent – {{ count($reminders) }} record(s)
                </p>
            </div>
            <a href="{{ route('admin.fees.reminders.index') }}"
               style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:9px 18px;font-weight:600;font-size:.85rem;text-decoration:none;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-arrow-left"></i> Back to Reminders
            </a>
        </div>
    </div>

    <div class="rl-card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="rl-thead">
                    <tr>
                        <th>Student</th>
                        <th>Channel</th>
                        <th>Sent To</th>
                        <th class="text-end">Amount Owed</th>
                        <th>Status</th>
                        <th>Sent By</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($reminders as $r)
                <tr class="rl-row">
                    <td>
                        <div style="font-weight:600;font-size:.875rem;color:#1e293b;">
                            {{ $r->recipient_name ?? 'Student #' . $r->student_id }}
                        </div>
                    </td>
                    <td>
                        @if($r->channel === 'sms')
                            <span class="ch-sms"><i class="fas fa-sms"></i>SMS</span>
                        @elseif($r->channel === 'whatsapp')
                            <span class="ch-wa"><i class="fab fa-whatsapp"></i>WhatsApp</span>
                        @elseif($r->channel === 'email')
                            <span class="ch-email"><i class="fas fa-envelope"></i>Email</span>
                        @else
                            <span class="st-other">{{ ucfirst($r->channel) }}</span>
                        @endif
                    </td>
                    <td style="font-size:.8rem;color:#64748b;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $r->recipient }}">
                        {{ $r->recipient }}
                    </td>
                    <td class="text-end" style="font-size:.875rem;font-weight:700;color:#dc2626;">
                        GHS {{ number_format($r->amount_owed, 2) }}
                    </td>
                    <td>
                        @if($r->status === 'sent')
                            <span class="st-sent"><i class="fas fa-check me-1"></i>Sent</span>
                        @elseif($r->status === 'failed')
                            <span class="st-failed"><i class="fas fa-times me-1"></i>Failed</span>
                            @if($r->error_message)
                                <div style="font-size:.68rem;color:#dc2626;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:2px;" title="{{ $r->error_message }}">
                                    {{ $r->error_message }}
                                </div>
                            @endif
                        @else
                            <span class="st-other">{{ ucfirst($r->status) }}</span>
                        @endif
                    </td>
                    <td style="font-size:.8rem;color:#64748b;">{{ $r->sent_by ?? '–' }}</td>
                    <td style="font-size:.78rem;color:#94a3b8;white-space:nowrap;">
                        @if($r->sent_at)
                            <div>{{ \Carbon\Carbon::parse($r->sent_at)->format('d M Y') }}</div>
                            <div style="color:#cbd5e1;">{{ \Carbon\Carbon::parse($r->sent_at)->format('H:i') }}</div>
                        @else
                            –
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:4rem;color:#94a3b8;">
                        <i class="fas fa-paper-plane" style="font-size:2rem;display:block;margin-bottom:12px;opacity:.4;"></i>
                        <div style="font-weight:600;color:#475569;">No reminders sent yet</div>
                        <div style="font-size:.83rem;margin-top:4px;">
                            Go to <a href="{{ route('admin.fees.reminders.index') }}" style="color:var(--admin-nav);font-weight:700;">Fee Reminders</a> to send your first reminder.
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
