extends('layouts.app')
@section('title', 'Subscription')

@section('content')
<div class="container py-4" style="max-width:720px;">

    {{-- Alert if expired/suspended --}}
    @if($tenant->isExpired())
    <div class="alert alert-danger" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Your subscription has expired.</strong> Please renew to restore access.
    </div>
    @elseif($tenant->isOnTrial())
    <div class="alert alert-warning" style="border-radius:12px;">
        <i class="fas fa-clock me-2"></i>
        <strong>Trial period:</strong> {{ $tenant->trialDaysRemaining() }} day{{ $tenant->trialDaysRemaining() === 1 ? '' : 's'?'s platform access</p>
        </div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Current Status</div>
                    <span class="badge badge-{{ $tenant->status }} fs-6 px-3 py-2 mt-1">{{ ucfirst($tenant->status) }}</span>
                </div>
                <div class="text-end">
                    <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">School</div>
                    <strong>{{ $tenant->name }}</strong>
                </div>
            </div>

            @if($tenant->plan)
            <div style="background:#f8fafc;border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;border:1px solid #e2e8f0;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Your Plan</div>
                        <div style="font-size:1.2rem;font-weight:700;color:#1e293b;">{{ $tenant->plan->name }}</div>
                        @if($tenant->plan->description)
                        <div style="font-size:0.85rem;color:#64748b;">{{ $tenant->plan->description }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <div style="font-size:1.5rem;font-weight:800;color:#e94560;">GHS {{ number_format($tenant->plan->price, 2) }}</div>
                        <div style="font-size:0.8rem;color:#94a3b8;">per {{ $tenant->plan->billing_cycle }}</div>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning" style="border-radius:10px;font-size:0.875rem;">
                No plan assigned. Contact the platform owner.
            </div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div style="background:#f8fafc;border-radius:10px;padding:1rem;">
                        <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Subscription Expires</div>
                        @if($tenant->subscription_ends_at)
                            <div style="font-size:1.1rem;font-weight:700;color:{{ $tenant->subscriptionDaysRemaining() <= 14 ? '#ef4444' : '#1e293b' }};">
                                {{ $tenant->subscription_ends_at->format('d F Y') }}
                            </div>
                            <div style="font-size:0.8rem;color:{{ $tenant->subscriptionDaysRemaining() <= 14 ? '#ef4444' : '#64748b' }};">
                                {{ $tenant->subscriptionDaysRemaining() }} day{{ $tenant->subscriptionDaysRemaining() === 1 ? '' : 's' }} remaining
                            </div>
                        @else
                            <div style="font-size:0.9rem;color:#94a3b8;">Not set</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div style="background:#f8fafc;border-radius:10px;padding:1rem;">
                        <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Trial Ends</div>
                        @if($tenant->trial_ends_at)
                            <div style="font-size:1.1rem;font-weight:700;">{{ $tenant->trial_ends_at->format('d F Y') }}</div>
                        @else
                            <div style="font-size:0.9rem;color:#94a3b8;">No trial period</div>
                        @endif
                    </div>
                </div>
            </div>

            @if($tenant->plan && $tenant->plan->price > 0)
            <div class="text-center">
                <a href="{{ route('admin.subscription.pay'?'paystack.messaging.initialize'?'payment_date')->limit(10)->get();
            @endphp
            @if($payments->count() > 0)
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr><th>Receipt #</th><th>Date</th><th>Amount</th><th>Method</th><th>Months</th></tr>
                    </thead>
                    <tbody>
                    @foreach($payments as $p)
                    <tr>
                        <td><code style="font-size:0.8rem;">{{ $p->receipt_number }}</code></td>
                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                        <td><strong>GHS {{ number_format($p->amount, 2) }}</strong></td>
                        <td>{{ ucfirst(str_replace('_',' ',$p->payment_method)) }}</td>
                        <td>{{ $p->months_paid }}mo</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-muted py-4" style="font-size:0.875rem;">No payments recorded yet</div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
function selectPkg(id, el) {
    // Reset all
    document.querySelectorAll('[id^="pkgCard"]').forEach(c => {
        c.style.borderColor = '#e2e8f0';
        c.style.background  = '#fff';
    });
    document.querySelectorAll('[id^="pkgDot"]').forEach(d => {
        d.style.background   = 'transparent';
        d.style.borderColor  = '#e2e8f0';
    });

    // Highlight selected
    el.style.borderColor = '#0f3460';
    el.style.background  = '#f8fafc';
    document.getElementById('pkgDot' + id).style.background  = '#0f3460';
    document.getElementById('pkgDot' + id).style.borderColor = '#0f3460';
    document.getElementById('pkgRadio' + id).checked = true;

    // Enable button
    const btn = document.getElementById('buyBtn');
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor  = 'pointer';

    // Get price from the card text
    const priceEl = el.querySelector('[style*="e94560"]');
    const price   = priceEl ? priceEl.textContent.trim() : '';
    document.getElementById('buyBtnText').textContent = 'Pay ' + price + ' via Paystack';
}
</script>
@endpush
@endsection
