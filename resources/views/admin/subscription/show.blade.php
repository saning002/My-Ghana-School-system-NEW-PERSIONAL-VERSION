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

    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
        <div style="background:linear-gradient(135deg,#0f3460,#16213e);padding:1.75rem;color:#fff;">
            <h5 class="fw-bold mb-0"><i class="fas fa-shield-alt me-2"></i> Platform Subscription</h5>
            <p style="opacity:0.7;font-size:0.85rem;margin:0.25rem 0 0;">Manage your school's platform access</p>
        </div>
        <div class="card-body p-4">

            {{-- Status badge --}}
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

            {{-- Plan info --}}
            @if($tenant->plan)
            <div style="background:#f8fafc;border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;border:1px solid #e2e8f0;">
                <div class="d-flex justify-content-between align-items-center mb-3">
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
                <i class="fas fa-info-circle me-1"></i> No plan assigned. Contact the platform owner to get a plan.
            </div>
            @endif

            {{-- Subscription dates --}}
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
                            <div style="font-size:1.1rem;font-weight:700;color:{{ $tenant->isOnTrial() ? '#f59e0b' : '#94a3b8' }};">
                                {{ $tenant->trial_ends_at->format('d F Y') }}
                            </div>
                            @if($tenant->isOnTrial())
                            <div style="font-size:0.8rem;color:#f59e0b;">{{ $tenant->trialDaysRemaining() }} day{{ $tenant->trialDaysRemaining() === 1 ? '' : 's' }} remaining</div>
                            @endif
                        @else
                            <div style="font-size:0.9rem;color:#94a3b8;">No trial period</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Pay button --}}
            @if($tenant->plan && $tenant->plan->price > 0)
            <div class="text-center">
                <a href="{{ route('admin.subscription.pay') }}" class="btn btn-lg fw-bold px-4" style="background:linear-gradient(135deg,#0ba360,#3cba92);color:#fff;border:none;border-radius:12px;font-size:1rem;">
                    <i class="fas fa-credit-card me-2"></i> Pay Subscription Online
                </a>
                <div style="font-size:0.75rem;color:#94a3b8;margin-top:0.5rem;">
                    Secure payment via Paystack · Card · Mobile Money · Bank Transfer
                </div>
            </div>
            @elseif($tenant->plan && $tenant->plan->price == 0)
            <div class="alert alert-success" style="border-radius:10px;font-size:0.875rem;text-align:center;">
                <i class="fas fa-check-circle me-1"></i> Your plan is free — no payment required.
            </div>
            @endif

        </div>
    </div>

    {{-- Messaging Credits --}}
    @php
        $tenantSlug = session('tenant_slug');
        $msgTenant  = $tenantSlug ? \App\Models\Tenant::on('pgsql')->where('slug', $tenantSlug)->first() : null;
        $msgCredits = $msgTenant ? \App\Models\MessagingCredit::forTenant($msgTenant->id) : null;
    @endphp
    @if($msgTenant && $msgCredits)
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
        <div class="card-header bg-white py-3" style="border-bottom:1px solid #f1f5f9;">
            <strong><i class="fas fa-sms me-2"></i> Messaging Credits</strong>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <div style="background:#eff6ff;border-radius:10px;padding:1rem;text-align:center;">
                        <div style="font-size:2rem;font-weight:800;color:#3b82f6;">{{ number_format($msgCredits->sms_credits) }}</div>
                        <div style="font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">SMS Credits</div>
                        @if($msgCredits->sms_credits == 0)
                            <small style="color:#ef4444;">No credits remaining</small>
                        @elseif($msgCredits->sms_credits < 20)
                            <small style="color:#f59e0b;">Running low</small>
                        @endif
                    </div>
                </div>
                <div class="col-6">
                    <div style="background:#f0fdf4;border-radius:10px;padding:1rem;text-align:center;">
                        <div style="font-size:2rem;font-weight:800;color:#25d366;">{{ number_format($msgCredits->whatsapp_credits) }}</div>
                        <div style="font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">WhatsApp Credits</div>
                        @if($msgCredits->whatsapp_credits == 0)
                            <small style="color:#ef4444;">No credits remaining</small>
                        @elseif($msgCredits->whatsapp_credits < 10)
                            <small style="color:#f59e0b;">Running low</small>
                        @endif
                    </div>
                </div>
            </div>
            <p style="font-size:0.85rem;color:#64748b;">Credits are used when sending fee reminders to parents via SMS or WhatsApp.</p>
            <a href="{{ route('paystack.messaging.buy', $msgTenant) }}"
               class="btn fw-bold w-100" style="background:linear-gradient(135deg,#1a1a2e,#0f3460);color:#fff;border-radius:10px;">
                <i class="fas fa-plus me-2"></i> Buy More Credits
            </a>
        </div>
    </div>
    @endif

    {{-- Payment history --}}
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
@endsection
