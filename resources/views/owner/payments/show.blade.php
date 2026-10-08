@extends('owner.layout')
@section('title', 'Payment ' . $payment->receipt_number)
@section('page-title', 'Payment — ' . $payment->receipt_number)

@section('topbar-actions')
    <a href="{{ route('owner.payments.receipt', $payment) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
        <i class="fas fa-file-pdf me-1"></i> View Receipt
    </a>
    <form method="POST" action="{{ route('owner.payments.email-receipt', $payment) }}" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-owner-primary"><i class="fas fa-envelope me-1"></i> Email Receipt</button>
    </form>
    <a href="{{ route('owner.payments.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="owner-card">
            <div class="card-header">Payment Details</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach([
                        'Receipt Number'  => $payment->receipt_number,
                        'School'          => $payment->tenant->name,
                        'Amount'          => $payment->currency . ' ' . number_format($payment->amount, 2),
                        'Date'            => $payment->payment_date->format('d F Y'),
                        'Payment Method'  => ucfirst(str_replace('_',' ',$payment->payment_method)),
                        'Reference'       => $payment->reference ?? '—',
                        'Months Paid'     => $payment->months_paid . ' month(s)',
                        'Description'     => $payment->description ?? '—',
                        'Notes'           => $payment->notes ?? '—',
                        'Receipt Emailed' => $payment->receipt_emailed ? 'Yes — ' . $payment->receipt_emailed_at?->format('d M Y H:i') : 'No',
                    ] as $label => $value)
                    <div class="col-md-6">
                        <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">{{ $label }}</div>
                        <div style="font-size:0.95rem;font-weight:500;">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="owner-card mb-3">
            <div class="card-body text-center">
                <div style="font-size:0.8rem;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.5rem;">Amount Paid</div>
                <div style="font-size:2.5rem;font-weight:800;color:#e94560;">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</div>
                <span class="badge bg-success px-3 py-2 mt-2" style="font-size:0.85rem;">PAID</span>
            </div>
        </div>
        <div class="owner-card">
            <div class="card-header">School Subscription After Payment</div>
            <div class="card-body">
                @php $tenant = $payment->tenant; @endphp
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:#64748b;font-size:0.875rem;">Status</span>
                    <span class="badge badge-{{ $tenant->status }}">{{ ucfirst($tenant->status) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:#64748b;font-size:0.875rem;">Subscription ends</span>
                    <strong>{{ $tenant->subscription_ends_at?->format('d M Y') ?? '—' }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span style="color:#64748b;font-size:0.875rem;">Days remaining</span>
                    <strong>{{ $tenant->subscriptionDaysRemaining() ?: '—' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
