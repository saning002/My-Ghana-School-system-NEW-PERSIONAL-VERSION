@extends('layouts.app')
@section('title', 'Fee History — ' . ($student->user?->full_name ?? 'Student'))

@section('content')
<div class="container-fluid py-4" style="max-width:900px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Fee History</h4>
            <p class="text-muted small mb-0">{{ $student->user?->full_name }} · {{ $student->student_id }} · {{ $student->program?->name }}</p>
        </div>
        <a href="{{ route('admin.fees.term.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @php
        $totalEverBilled = collect($history)->sum('total_billed');
        $totalEverPaid   = collect($history)->sum('total_paid');
        $totalEverOwed   = max(0, $totalEverBilled - $totalEverPaid);
    @endphp

    {{-- Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
                <div class="card-body py-3">
                    <div style="font-size:1.3rem;font-weight:800;">GHS {{ number_format($totalEverBilled, 2) }}</div>
                    <div class="text-muted small">Total Billed (All Time)</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
                <div class="card-body py-3">
                    <div style="font-size:1.3rem;font-weight:800;color:#16a34a;">GHS {{ number_format($totalEverPaid, 2) }}</div>
                    <div class="text-muted small">Total Paid (All Time)</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
                <div class="card-body py-3">
                    <div style="font-size:1.3rem;font-weight:800;color:#ef4444;">GHS {{ number_format($totalEverOwed, 2) }}</div>
                    <div class="text-muted small">Current Outstanding</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Per-term breakdown --}}
    @foreach($history as $h)
    @if($h['total_billed'] > 0 || $h['payments']->count() > 0)
    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;overflow:hidden;">
        <div class="card-header d-flex align-items-center justify-content-between py-3"
             style="background:{{ $h['is_paid'] ? '#f0fdf4' : '#fef2f2' }};border-bottom:1px solid {{ $h['is_paid'] ? '#bbf7d0' : '#fecaca' }};">
            <div>
                <strong>{{ $h['session']->year }}</strong>
                @if($h['carry_forward'] > 0)
                    <span class="badge bg-warning text-dark ms-2" style="font-size:0.72rem;">
                        Carry forward: GHS {{ number_format($h['carry_forward'], 2) }}
                    </span>
                @endif
            </div>
            <span class="badge {{ $h['is_paid'] ? 'bg-success' : 'bg-danger' }}" style="font-size:0.78rem;">
                {{ $h['is_paid'] ? 'Paid' : 'Outstanding: GHS ' . number_format(max(0,$h['balance']), 2) }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                {{-- Fee breakdown --}}
                <div class="col-md-5 p-3 border-end">
                    <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Fees Billed</div>
                    @if($h['program_fee'] > 0)
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;">
                        <span>Programme Fee</span><strong>GHS {{ number_format($h['program_fee'], 2) }}</strong>
                    </div>
                    @endif
                    @if($h['exam_fee'] > 0)
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;">
                        <span>Exam Fee</span><strong>GHS {{ number_format($h['exam_fee'], 2) }}</strong>
                    </div>
                    @endif
                    @if($h['other_fees'] > 0)
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;">
                        <span>{{ $h['other_label'] ?? 'Other' }}</span><strong>GHS {{ number_format($h['other_fees'], 2) }}</strong>
                    </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between" style="font-size:0.875rem;font-weight:700;">
                        <span>Total Billed</span><span>GHS {{ number_format($h['total_billed'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size:0.875rem;color:#16a34a;font-weight:600;">
                        <span>Total Paid</span><span>GHS {{ number_format($h['total_paid'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-1" style="font-size:0.9rem;font-weight:800;color:{{ $h['balance'] > 0 ? '#ef4444' : '#16a34a' }};">
                        <span>Balance</span><span>GHS {{ number_format(max(0,$h['balance']), 2) }}</span>
                    </div>
                </div>

                {{-- Payment history --}}
                <div class="col-md-7 p-3">
                    <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                        Payments ({{ $h['payments']->count() }})
                    </div>
                    @forelse($h['payments'] as $p)
                    <div class="d-flex align-items-center justify-content-between mb-2 p-2 rounded" style="background:#f8fafc;">
                        <div>
                            <div style="font-size:0.82rem;font-weight:600;">GHS {{ number_format($p->amount_paid, 2) }}</div>
                            <div style="font-size:0.72rem;color:#94a3b8;">
                                {{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }} ·
                                {{ ucfirst(str_replace('_',' ',$p->payment_method ?? 'cash')) }}
                                @if($p->receipt_number) · <code style="font-size:0.7rem;">{{ $p->receipt_number }}</code> @endif
                            </div>
                        </div>
                        @if($p->receipt_number ?? false)
                        <a href="{{ route('admin.fees.receipt', $p->id) }}" class="btn btn-xs btn-outline-secondary"
                           style="font-size:0.72rem;padding:3px 8px;" target="_blank" title="View Receipt">
                            <i class="fas fa-receipt"></i>
                        </a>
                        @endif
                    </div>
                    @empty
                    <div class="text-muted small">No payments recorded for this term.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach

</div>
@endsection
