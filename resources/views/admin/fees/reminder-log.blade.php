@extends('layouts.app')
@section('title', 'Reminder Log')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">Reminder Log</h4>
        <a href="{{ route('admin.fees.reminders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="font-size:0.78rem;text-transform:uppercase;color:#64748b;padding:12px 16px;">Student</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;color:#64748b;">Channel</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;color:#64748b;">Sent To</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;color:#64748b;text-align:right;">Amount Owed</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;color:#64748b;">Status</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;color:#64748b;">Sent By</th>
                        <th style="font-size:0.78rem;text-transform:uppercase;color:#64748b;">Date</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($reminders as $r)
                <tr>
                    <td style="padding:10px 16px;font-size:0.875rem;">
                        {{ $r->recipient_name ?? 'Student #' . $r->student_id }}
                    </td>
                    <td>
                        @php
                            $icons = ['email'=>'envelope text-primary','sms'=>'sms text-success','whatsapp'=>'whatsapp text-success'];
                            $icon  = $icons[$r->channel] ?? 'bell';
                        @endphp
                        <span style="font-size:0.8rem;">
                            <i class="fas fa-{{ $icon }} me-1"></i>
                            {{ ucfirst($r->channel) }}
                        </span>
                    </td>
                    <td style="font-size:0.8rem;color:#64748b;">{{ $r->recipient }}</td>
                    <td style="text-align:right;font-size:0.875rem;font-weight:600;color:#ef4444;">
                        GHS {{ number_format($r->amount_owed, 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $r->status === 'sent' ? 'bg-success' : ($r->status === 'failed' ? 'bg-danger' : 'bg-secondary') }}" style="font-size:0.72rem;">
                            {{ ucfirst($r->status) }}
                        </span>
                        @if($r->error_message)
                            <div style="font-size:0.7rem;color:#ef4444;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $r->error_message }}">
                                {{ $r->error_message }}
                            </div>
                        @endif
                    </td>
                    <td style="font-size:0.8rem;color:#64748b;">{{ $r->sent_by ?? '—' }}</td>
                    <td style="font-size:0.8rem;color:#94a3b8;">
                        {{ $r->sent_at ? \Carbon\Carbon::parse($r->sent_at)->format('d M Y H:i') : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No reminders sent yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
