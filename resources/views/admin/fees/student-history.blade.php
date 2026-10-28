@extends('layouts.app')
@section('title', 'Fee History �?" ' . ($student->user?->full_name ?? 'Student'))

@section('content')
<style>
.sh-hero{background:linear-gradient(135deg,#0f3460 0%,#16213e 100%);border-radius:20px;padding:2rem 2.5rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;}
.sh-hero::before{content:'';position:absolute;right:-50px;top:-50px;width:220px;height:220px;background:rgba(255,255,255,.04);border-radius:50%;}
.sh-stat{background:#fff;border-radius:14px;padding:1.25rem 1.5rem;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,.06);text-align:center;height:100%;}
.sh-card{background:#fff;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden;margin-bottom:1.25rem;}
.sh-card-hd{padding:.875rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;}
</style>

<div class="container-fluid py-4" style="max-width:860px;">

    {{-- Hero --}}
    <div class="sh-hero">
        <div style="position:relative;z-index:1;" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:56px;height:56px;background:rgba(255,255,255,.12);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;flex-shrink:0;">
                    {{ strtoupper(substr($student->user?->full_name ?? '?', 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;opacity:.55;margin-bottom:2px;">Fee History</div>
                    <h4 class="fw-black mb-0">{{ $student->user?->full_name ?? 'Student' }}</h4>
                    <div style="opacity:.65;font-size:.82rem;margin-top:2px;">
                        <span style="background:rgba(255,255,255,.12);padding:2px 10px;border-radius:10px;margin-right:6px;">{{ $student->student_id }}</span>
                        {{ $student->program?->name ?? '' }}
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.fees.term.index') }}"
               style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:10px;padding:9px 18px;font-weight:600;font-size:.85rem;text-decoration:none;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-arrow-left"></i> Back to Ledger
            </a>
        </div>
    </div>

    {{-- Lifetime summary --}}
    @php
        $totalEverBilled = collect($history)->sum('total_billed');
        $totalEverPaid   = collect($history)->sum('total_paid');
        $totalEverOwed   = max(0, $totalEverBilled - $totalEverPaid);
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="sh-stat">
                <div style="font-size:1.3rem;font-weight:800;color:#1e293b;">GHS {{ number_format($totalEverBilled, 2) }}</div>
                <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-top:4px;">Total Billed</div>
            </div>
        </div>
        <div class="col-4">
            <div class="sh-stat">
                <div style="font-size:1.3rem;font-weight:800;color:#16a34a;">GHS {{ number_format($totalEverPaid, 2) }}</div>
                <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-top:4px;">Total Paid</div>
            </div>
        </div>
        <div class="col-4">
            <div class="sh-stat">
                <div style="font-size:1.3rem;font-weight:800;color:{{ $totalEverOwed > 0 ? '#dc2626' : '#16a34a' }};">
                    GHS {{ number_format($totalEverOwed, 2) }}
                </div>
                <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-top:4px;">Outstanding</div>
            </div>
        </div>
    </div>

    {{-- Per-term breakdown --}}
    @foreach($history as $h)
    @if($h['total_billed'] > 0 || $h['payments']->count() > 0)
    <div class="sh-card">
        {{-- Term header --}}
        <div class="sh-card-hd"
             style="background:{{ $h['is_paid'] ? '#f0fdf4' : '#fef2f2' }};border-bottom-color:{{ $h['is_paid'] ? '#bbf7d0' : '#fecaca' }};">
            <div class="d-flex align-items-center gap-2">
                <span style="width:10px;height:10px;border-radius:50%;background:{{ $h['is_paid'] ? '#16a34a' : '#dc2626' }};flex-shrink:0;"></span>
                <span style="font-weight:700;font-size:.95rem;color:#1e293b;">{{ $h['session']->year }}</span>
                @if($h['carry_forward'] > 0)
                    <span style="background:var(--primary-pale);color:var(--primary-text);font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:10px;margin-left:4px;">
                        <i class="fas fa-arrow-right me-1"></i>Carry-forward: GHS {{ number_format($h['carry_forward'], 2) }}
                    </span>
                @endif
            </div>
            @if($h['is_paid'])
                <span style="background:#f0fdf4;color:#16a34a;font-weight:700;padding:4px 14px;border-radius:20px;font-size:.8rem;">
                    <i class="fas fa-check me-1"></i>Paid
                </span>
            @else
                <span style="background:#fef2f2;color:#dc2626;font-weight:700;padding:4px 14px;border-radius:20px;font-size:.8rem;">
                    <i class="fas fa-clock me-1"></i>Owes GHS {{ number_format(max(0,$h['balance']), 2) }}
                </span>
            @endif
        </div>

        <div class="row g-0">
            {{-- Fee breakdown --}}
            <div class="col-md-5" style="padding:1.25rem;border-right:1px solid #f1f5f9;">
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;font-weight:700;margin-bottom:12px;">
                    <i class="fas fa-file-invoice me-1"></i> Fees Billed
                </div>
                @if($h['program_fee'] > 0)
                <div class="d-flex justify-content-between mb-2" style="font-size:.875rem;">
                    <span style="color:#64748b;">Programme Fee</span>
                    <strong>GHS {{ number_format($h['program_fee'], 2) }}</strong>
                </div>
                @endif
                @if($h['exam_fee'] > 0)
                <div class="d-flex justify-content-between mb-2" style="font-size:.875rem;">
                    <span style="color:#64748b;">Exam Fee</span>
                    <strong>GHS {{ number_format($h['exam_fee'], 2) }}</strong>
                </div>
                @endif
                @if($h['other_fees'] > 0)
                <div class="d-flex justify-content-between mb-2" style="font-size:.875rem;">
                    <span style="color:#64748b;">{{ $h['other_label'] ?? 'Other Fees' }}</span>
                    <strong>GHS {{ number_format($h['other_fees'], 2) }}</strong>
                </div>
                @endif
                <div style="height:1px;background:#f1f5f9;margin:10px 0;"></div>
                <div class="d-flex justify-content-between mb-1" style="font-size:.875rem;font-weight:700;">
                    <span>Total Billed</span>
                    <span>GHS {{ number_format($h['total_billed'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:.875rem;font-weight:600;color:#16a34a;">
                    <span>Total Paid</span>
                    <span>GHS {{ number_format($h['total_paid'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.95rem;font-weight:800;color:{{ $h['balance'] > 0 ? '#dc2626' : '#16a34a' }};">
                    <span>Balance</span>
                    <span>GHS {{ number_format(max(0,$h['balance']), 2) }}</span>
                </div>
            </div>

            {{-- Payments --}}
            <div class="col-md-7" style="padding:1.25rem;">
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;font-weight:700;margin-bottom:12px;">
                    <i class="fas fa-receipt me-1"></i> Payments ({{ $h['payments']->count() }})
                </div>
                @forelse($h['payments'] as $p)
                <div style="background:#f8fafc;border-radius:10px;padding:.875rem;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-size:.9rem;font-weight:700;color:#1e293b;">GHS {{ number_format($p->amount_paid, 2) }}</div>
                        <div style="font-size:.72rem;color:#94a3b8;margin-top:2px;">
                            <i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}
                            &nbsp;·&nbsp;{{ ucfirst(str_replace('_', ' ', $p->payment_method ?? 'cash')) }}
                            @if($p->receipt_number)
                                &nbsp;·&nbsp;<code style="font-size:.68rem;background:#e2e8f0;padding:1px 5px;border-radius:4px;">{{ $p->receipt_number }}</code>
                            @endif
                            @if($p->notes)
                                &nbsp;·&nbsp;<span style="font-style:italic;">{{ $p->notes }}</span>
                            @endif
                        </div>
                    </div>
                    @if($p->receipt_number ?? false)
                    <a href="{{ route('admin.fees.receipt', $p->id) }}" target="_blank"
                       style="background:#eff6ff;color:#3b82f6;border:none;border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;text-decoration:none;"
                       title="View receipt">
                        <i class="fas fa-receipt" style="font-size:.72rem;"></i>
                    </a>
                    @endif
                </div>
                @empty
                <div style="font-size:.83rem;color:#94a3b8;font-style:italic;">No payments recorded for this term.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
    @endforeach

    @if(collect($history)->every(fn($h) => $h['total_billed'] == 0 && $h['payments']->count() == 0))
    <div style="text-align:center;padding:4rem;color:#94a3b8;">
        <i class="fas fa-inbox" style="font-size:2.5rem;display:block;margin-bottom:12px;"></i>
        <div style="font-weight:600;font-size:1rem;color:#475569;">No fee history</div>
        <div style="font-size:.875rem;margin-top:4px;">This student has no fee records yet.</div>
    </div>
    @endif

</div>
@endsection
