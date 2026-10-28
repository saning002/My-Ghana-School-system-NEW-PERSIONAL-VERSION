@extends('layouts.app')
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
        <strong>Trial period:</strong> {{ $tenant->trialDaysRemaining() }} day{{ $tenant->trialDaysRemaining() === 1 ? '' : 's' }} remaining.
    </div>
    @endif

    {{-- �"?�"? SUBSCRIPTION CARD �"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"? --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
        <div style="background:linear-gradient(135deg,#0f3460,#16213e);padding:1.75rem;color:#fff;">
            <h5 class="fw-bold mb-0"><i class="fas fa-shield-alt me-2"></i> Platform Subscription</h5>
            <p style="opacity:0.7;font-size:0.85rem;margin:0.25rem 0 0;">Manage your school's platform access</p>
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
                <a href="{{ route('admin.subscription.pay') }}" class="btn btn-lg fw-bold px-4"
                   style="background:linear-gradient(135deg,#0ba360,#3cba92);color:#fff;border:none;border-radius:12px;font-size:1rem;">
                    <i class="fas fa-credit-card me-2"></i> Renew Subscription via Paystack
                </a>
                <div style="font-size:0.75rem;color:#94a3b8;margin-top:0.5rem;">
                    Card · Mobile Money · Bank Transfer
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- �"?�"? MESSAGING CREDITS CARD �"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"? --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
        <div style="background:linear-gradient(135deg,#1a1a2e,#0f3460);padding:1.5rem;color:#fff;">
            <h5 class="fw-bold mb-0"><i class="fas fa-sms me-2"></i> Messaging Credits</h5>
            <p style="opacity:0.7;font-size:0.85rem;margin:0.25rem 0 0;">Buy credits to send fee reminders via SMS & WhatsApp</p>
        </div>
        <div class="card-body p-4">

            {{-- Current balance --}}
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div style="background:#eff6ff;border-radius:12px;padding:1.25rem;text-align:center;border:1px solid #bfdbfe;">
                        <div style="font-size:2.5rem;font-weight:800;color:#3b82f6;line-height:1;">
                            {{ number_format(($msgCredits ?? null)?->sms_credits ?? 0) }}
                        </div>
                        <div style="font-size:0.72rem;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px;">
                            <i class="fas fa-sms me-1"></i> SMS Credits
                        </div>
                        @if(($msgCredits ?? null)?->sms_credits == 0)
                            <small style="color:#ef4444;font-size:0.72rem;">No credits �?" top up below</small>
                        @elseif(($msgCredits ?? null)?->sms_credits < 20)
                            <small style="color:var(--brand);font-size:0.72rem;">Running low</small>
                        @endif
                    </div>
                </div>
                <div class="col-6">
                    <div style="background:#f0fdf4;border-radius:12px;padding:1.25rem;text-align:center;border:1px solid #bbf7d0;">
                        <div style="font-size:2.5rem;font-weight:800;color:#25d366;line-height:1;">
                            {{ number_format(($msgCredits ?? null)?->whatsapp_credits ?? 0) }}
                        </div>
                        <div style="font-size:0.72rem;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px;">
                            <i class="fab fa-whatsapp me-1"></i> WhatsApp Credits
                        </div>
                        @if(($msgCredits ?? null)?->whatsapp_credits == 0)
                            <small style="color:#ef4444;font-size:0.72rem;">No credits �?" top up below</small>
                        @elseif(($msgCredits ?? null)?->whatsapp_credits < 10)
                            <small style="color:var(--brand);font-size:0.72rem;">Running low</small>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Package selector + payment --}}
            @if(isset($msgPackages) && $msgPackages->count())
            <p class="fw-semibold mb-3" style="font-size:0.875rem;">Select a package to top up:</p>
            <form method="POST" action="{{ route('paystack.messaging.initialize', $tenant) }}">
                @csrf
                <input type="hidden" name="payer_email" value="{{ $tenant->admin_email }}">
                <input type="hidden" name="payer_name" value="{{ $tenant->admin_name }}">

                <div class="row g-2 mb-4">
                    @foreach($msgPackages as $pkg)
                    <div class="col-12">
                        <label style="cursor:pointer;display:block;margin:0;">
                            <div class="d-flex align-items-center gap-3 p-3 rounded border"
                                 style="transition:all 0.15s;"
                                 id="pkgCard{{ $pkg->id }}"
                                 onclick="selectPkg({{ $pkg->id }}, this)">
                                <input type="radio" name="package_id" value="{{ $pkg->id }}"
                                       id="pkgRadio{{ $pkg->id }}" style="display:none;">
                                <div style="width:18px;height:18px;border-radius:50%;border:2px solid #e2e8f0;flex-shrink:0;" id="pkgDot{{ $pkg->id }}"></div>
                                <div class="flex-fill">
                                    <div style="font-weight:700;font-size:0.875rem;">{{ $pkg->name }}</div>
                                    <div style="font-size:0.75rem;color:#94a3b8;">
                                        <span style="color:#3b82f6;font-weight:600;">{{ number_format($pkg->sms_credits) }} SMS</span>
                                        &nbsp;+&nbsp;
                                        <span style="color:#25d366;font-weight:600;">{{ number_format($pkg->whatsapp_credits) }} WhatsApp</span>
                                    </div>
                                </div>
                                <div style="font-weight:800;color:#e94560;font-size:1.1rem;white-space:nowrap;">
                                    GHS {{ number_format($pkg->price, 2) }}
                                </div>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>

                <button type="submit" id="buyBtn" disabled
                    class="btn fw-bold w-100 py-2"
                    style="background:linear-gradient(135deg,#1a1a2e,#0f3460);color:#fff;border:none;border-radius:12px;font-size:0.95rem;opacity:0.5;cursor:not-allowed;">
                    <i class="fas fa-lock me-2"></i>
                    <span id="buyBtnText">Select a package to continue</span>
                </button>
                <div style="text-align:center;font-size:0.72rem;color:#94a3b8;margin-top:0.5rem;">
                    Secured by Paystack · Card · Mobile Money · Bank Transfer
                </div>
            </form>
            @else
            <div class="alert alert-info" style="border-radius:10px;font-size:0.875rem;">
                <i class="fas fa-info-circle me-1"></i>
                No credit packages available. Contact the platform owner to set up messaging packages.
            </div>
            @endif

            @if(($msgCredits ?? null)?->sms_used > 0 || ($msgCredits ?? null)?->whatsapp_used > 0)
            <div class="d-flex justify-content-between mt-3 pt-3 border-top" style="font-size:0.78rem;color:#94a3b8;">
                <span>SMS sent (lifetime): {{ number_format(($msgCredits ?? null)?->sms_used ?? 0) }}</span>
                <span>WhatsApp sent (lifetime): {{ number_format(($msgCredits ?? null)?->whatsapp_used ?? 0) }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- �"?�"? RECENT SUBSCRIPTION PAYMENTS �"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"?�"? --}}
    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
        <div class="card-header bg-white py-3" style="border-bottom:1px solid #f1f5f9;">
            <strong><i class="fas fa-receipt me-2"></i> Recent Subscription Payments</strong>
        </div>
        <div class="card-body p-0">
            @php
                $payments = $tenant->payments()->orderByDesc('payment_date')->limit(10)->get();
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
