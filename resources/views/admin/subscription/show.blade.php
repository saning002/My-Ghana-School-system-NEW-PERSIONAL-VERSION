@extends('layouts.app')
@section('title', 'Subscription & Credits')
@section('subtitle', 'Manage your platform access and messaging credits')

@section('content')
@php
    $daysLeft   = $tenant->subscriptionDaysRemaining();
    $isExpired  = $tenant->isExpired();
    $isTrial    = $tenant->isOnTrial();
    $isActive   = $tenant->isActive() && !$isExpired;
    $smsQty     = ($msgCredits ?? null)?->sms_credits      ?? 0;
    $waQty      = ($msgCredits ?? null)?->whatsapp_credits ?? 0;
    $smsUsed    = ($msgCredits ?? null)?->sms_used         ?? 0;
    $waUsed     = ($msgCredits ?? null)?->whatsapp_used    ?? 0;
@endphp

<style>
    /* ── Subscription page local styles ─── */
    .sub-hero{
        background:linear-gradient(135deg,var(--admin-nav) 0%,color-mix(in srgb,var(--admin-nav) 70%,#1e40af) 100%);
        border-radius:1.5rem;padding:2rem 2.25rem;
        display:flex;align-items:center;justify-content:space-between;gap:1.5rem;
        flex-wrap:wrap;margin-bottom:1.75rem;position:relative;overflow:hidden;
    }
    .sub-hero::before{
        content:'';position:absolute;right:-60px;top:-60px;
        width:220px;height:220px;background:rgba(255,255,255,.04);border-radius:50%;
    }
    .sub-hero::after{
        content:'';position:absolute;right:60px;bottom:-80px;
        width:160px;height:160px;background:rgba(255,255,255,.03);border-radius:50%;
    }
    .sub-badge{
        display:inline-flex;align-items:center;gap:6px;
        padding:5px 14px;border-radius:30px;font-size:.7rem;font-weight:800;
        text-transform:uppercase;letter-spacing:.06em;
    }
    .sub-badge.active  {background:rgba(74,222,128,.18);color:#4ade80;}
    .sub-badge.trial   {background:rgba(250,204,21,.18);color:#fbbf24;}
    .sub-badge.expired {background:rgba(248,113,113,.18);color:#f87171;}
    .sub-badge.suspended{background:rgba(148,163,184,.18);color:#94a3b8;}

    .sub-section{
        background:#fff;border-radius:1.25rem;
        border:1px solid rgba(148,163,184,.15);
        box-shadow:0 2px 16px -6px rgba(15,23,42,.1);
        overflow:hidden;margin-bottom:1.5rem;
    }
    .sub-section-hd{
        padding:1.1rem 1.5rem;
        border-bottom:1px solid #f1f5f9;
        display:flex;align-items:center;gap:.75rem;
    }
    .sub-section-hd .hd-icon{
        width:34px;height:34px;border-radius:10px;
        display:flex;align-items:center;justify-content:center;
        font-size:.85rem;flex-shrink:0;
    }
    .sub-section-hd h3{
        font-size:.9rem;font-weight:800;color:#1e293b;margin:0;
        letter-spacing:-.01em;
    }
    .sub-section-hd p{font-size:.75rem;color:#94a3b8;margin:0;margin-top:1px;}
    .sub-section-body{padding:1.5rem;}

    .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;}
    .info-tile{
        background:#f8fafc;border-radius:1rem;padding:1.1rem 1.25rem;
        border:1px solid #f1f5f9;
    }
    .info-tile .lbl{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:6px;}
    .info-tile .val{font-size:1.05rem;font-weight:800;color:#1e293b;line-height:1.2;}
    .info-tile .sub{font-size:.72rem;color:#64748b;margin-top:3px;}
    .info-tile.warn .val{color:#ef4444;}
    .info-tile.warn .sub{color:#ef4444;}
    .info-tile.ok   .val{color:#16a34a;}

    .credit-tile{
        border-radius:1rem;padding:1.5rem;text-align:center;
        border:1px solid;flex:1;
    }
    .credit-tile .big{font-size:2.4rem;font-weight:900;line-height:1;}
    .credit-tile .tag{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-top:6px;}
    .credit-tile .warn-txt{font-size:.68rem;margin-top:4px;}

    .pkg-card{
        border:2px solid #e2e8f0;border-radius:1rem;
        padding:1rem 1.25rem;cursor:pointer;
        display:flex;align-items:center;gap:1rem;
        transition:all .15s cubic-bezier(.4,0,.2,1);
        background:#fff;
    }
    .pkg-card:hover{border-color:var(--brand);background:#fafafa;}
    .pkg-card.selected{border-color:var(--brand);background:color-mix(in srgb,var(--primary-pale) 60%,#fff);}
    .pkg-radio{
        width:20px;height:20px;border-radius:50%;
        border:2.5px solid #e2e8f0;flex-shrink:0;
        transition:all .15s;position:relative;
    }
    .pkg-radio::after{
        content:'';position:absolute;inset:3px;border-radius:50%;
        background:var(--brand);opacity:0;transition:opacity .15s;
    }
    .pkg-card.selected .pkg-radio{border-color:var(--brand);}
    .pkg-card.selected .pkg-radio::after{opacity:1;}

    .pay-btn{
        display:flex;align-items:center;justify-content:center;gap:.6rem;
        width:100%;padding:.9rem 1.5rem;border-radius:.9rem;border:none;
        font-size:.95rem;font-weight:800;letter-spacing:-.01em;cursor:pointer;
        transition:all .18s cubic-bezier(.4,0,.2,1);
        background:var(--primary);color:#fff;
    }
    .pay-btn:not(:disabled):hover{filter:brightness(.92);transform:translateY(-1px);box-shadow:0 8px 24px -8px rgba(0,0,0,.25);}
    .pay-btn:disabled{opacity:.45;cursor:not-allowed;filter:none;transform:none;box-shadow:none;}
    .pay-btn.renew{background:linear-gradient(135deg,var(--brand) 0%,var(--primary) 100%);}

    .pmt-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;
             padding:.85rem 1.5rem;border-bottom:1px solid #f8fafc;}
    .pmt-row:last-child{border-bottom:none;}
    .pmt-badge{font-size:.65rem;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.04em;}
</style>

{{-- ── HERO ────────────────────────────────────────────────────────── --}}
<div class="sub-hero">
    <div style="position:relative;z-index:1;">
        <span class="sub-badge {{ $tenant->status }}">
            <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
            {{ ucfirst($tenant->status) }}
        </span>
        <h1 style="color:#fff;font-size:1.45rem;font-weight:800;margin:.6rem 0 .3rem;letter-spacing:-.02em;">
            {{ $tenant->name }}
        </h1>
        <p style="color:rgba(255,255,255,.5);font-size:.82rem;margin:0;">
            @if($isExpired)
                Your subscription has expired — renew below to restore full access.
            @elseif($isTrial)
                Trial period — {{ $tenant->trialDaysRemaining() }} day{{ $tenant->trialDaysRemaining() === 1 ? '' : 's' }} remaining.
            @elseif($isActive && $daysLeft <= 14)
                Subscription expiring soon — {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left.
            @elseif($isActive)
                Your subscription is active and in good standing.
            @else
                Contact your platform provider for assistance.
            @endif
        </p>
    </div>
    <div style="position:relative;z-index:1;text-align:right;">
        @if($tenant->plan)
        <div style="color:rgba(255,255,255,.45);font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Current Plan</div>
        <div style="color:#fff;font-size:1.2rem;font-weight:800;margin-top:2px;">{{ $tenant->plan->name }}</div>
        <div style="color:rgba(255,255,255,.5);font-size:.8rem;">GHS {{ number_format($tenant->plan->price, 2) }} / {{ $tenant->plan->billing_cycle }}</div>
        @endif
    </div>
</div>

{{-- ── PER-STUDENT BILLING INFO ─────────────────────────────────────── --}}
@php
    $billing = $tenant->calculateTermAmount();
@endphp
@if($billing['model'] === 'per_student')
<div class="sub-section" style="border:2px solid #16a34a;">
    <div class="sub-section-hd" style="background:#f0fdf4;">
        <div class="hd-icon" style="background:#dcfce7;color:#16a34a;">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div>
            <h3>Per-Student Billing</h3>
            <p>You are billed per active student each term</p>
        </div>
        <span class="sub-badge active ms-auto">Per-Student</span>
    </div>
    <div class="sub-section-body">

        {{-- Tier table ──────────────────────────────────────────────────── --}}
        @if(!empty($billing['all_tiers']))
        <div class="mb-4">
            <p class="fw-bold mb-2" style="font-size:.82rem;color:#475569;">
                <i class="fas fa-layer-group me-1"></i> Your Billing Tiers
            </p>
            <div class="table-responsive">
                <table class="table table-sm" style="margin:0;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.5rem .75rem;">Tier</th>
                            <th style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.5rem .75rem;">Students</th>
                            <th style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.5rem .75rem;">Rate / Student</th>
                            <th style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.5rem .75rem;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($billing['all_tiers'] as $tierData)
                        @php
                            $isCurrent = !empty($billing['tier']) && $billing['tier']['id'] === $tierData['id'];
                            $range = $tierData['max_students']
                                ? $tierData['min_students'] . ' – ' . $tierData['max_students']
                                : $tierData['min_students'] . '+';
                        @endphp
                        <tr style="border-bottom:1px solid #f8fafc;{{ $isCurrent ? 'background:#f0fdf4;' : '' }}">
                            <td style="padding:.6rem .75rem;">
                                <span class="badge {{ $isCurrent ? 'bg-success' : 'bg-secondary' }}" style="font-size:.68rem;">
                                    Tier {{ $loop->iteration }}
                                </span>
                            </td>
                            <td style="padding:.6rem .75rem;font-weight:600;color:#475569;font-size:.82rem;">{{ $range }}</td>
                            <td style="padding:.6rem .75rem;font-weight:700;color:#0f172a;font-size:.85rem;">GHS {{ number_format($tierData['rate_per_student'], 2) }}</td>
                            <td style="padding:.6rem .75rem;">
                                @if($isCurrent)
                                <span class="sub-badge active" style="font-size:.62rem;">Current</span>
                                @else
                                <span style="font-size:.62rem;color:#cbd5e1;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Billing summary ──────────────────────────────────────────────── --}}
        <div class="info-grid">
            <div class="info-tile ok">
                <div class="lbl">Matched Rate</div>
                <div class="val">GHS {{ number_format($billing['rate'], 2) }}</div>
                <div class="sub">per student per term</div>
            </div>
            <div class="info-tile">
                <div class="lbl">Active Students</div>
                <div class="val">{{ number_format($billing['student_count']) }}</div>
                <div class="sub">currently enrolled</div>
            </div>
            <div class="info-tile">
                <div class="lbl">Amount This Term</div>
                <div class="val" style="color:#16a34a;">GHS {{ number_format($billing['term_amount'], 2) }}</div>
                <div class="sub">{{ $billing['student_count'] }} × GHS {{ number_format($billing['rate'], 2) }}</div>
            </div>
            <div class="info-tile">
                <div class="lbl">Estimated Annual</div>
                <div class="val">GHS {{ number_format($billing['annual_amount'], 2) }}</div>
                <div class="sub">{{ $tenant->billing_terms_per_year ?? 3 }} terms per year</div>
            </div>
        </div>

        @if($billing['includes_sms'])
        <div class="alert alert-success d-flex align-items-center gap-2 mb-0" style="font-size:.82rem;border-radius:.75rem;">
            <i class="fas fa-sms text-success"></i>
            <span><strong>SMS Attendance Alerts included</strong> — automated parent SMS alerts are part of your subscription. No separate credit top-up needed.</span>
        </div>
        @endif

        <p class="text-muted mb-0 mt-3" style="font-size:.78rem;">
            <i class="fas fa-info-circle me-1"></i>
            Your bill is calculated at the start of each term based on the number of active students enrolled.
            Mid-term admissions within the same tier don't change your bill until the next term.
            @if(!empty($billing['all_tiers']))
            Your school currently falls in the tier highlighted above.
            @endif
        </p>
    </div>
</div>

{{-- ── PER-STUDENT INVOICE SECTION ─────────────────────────────────────── --}}
@if($tenant->isPerStudentBilling())

{{-- Generate invoice button --}}
@if(!$currentInvoice)
<div class="sub-section mb-4" style="border:2px solid #f59e0b;">
    <div class="sub-section-body text-center py-4">
        <i class="fas fa-file-invoice-dollar" style="font-size:2rem;color:#f59e0b;margin-bottom:.5rem;"></i>
        <h4 style="font-size:1rem;font-weight:800;color:#1e293b;margin-bottom:.3rem;">No invoice generated for this term yet</h4>
        <p style="font-size:.82rem;color:#94a3b8;margin-bottom:1.2rem;">
            Click below to generate your term invoice based on your current active student count.
        </p>
        <form method="POST" action="{{ route('admin.subscription.generate-invoice') }}">
            @csrf
            <button type="submit" class="pay-btn renew" style="max-width:280px;margin:0 auto;">
                <i class="fas fa-file-invoice me-1"></i> Generate Term Invoice
            </button>
        </form>
    </div>
</div>
@else
{{-- Current pending invoice ─────────────────────────────────────────────── --}}
<div class="sub-section" style="border:2px solid #f59e0b;background:#fffbeb;">
    <div class="sub-section-hd" style="background:#fef3c7;">
        <div class="hd-icon" style="background:#fde68a;color:#d97706;">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div>
            <h3>Current Term Invoice</h3>
            <p>Invoice {{$currentInvoice->invoice_number}} — due {{$currentInvoice->due_date?->format('d M Y') ?? '—'}}</p>
        </div>
        <span class="sub-badge" style="background:#fef3c7;color:#b45309;">Pending Payment</span>
    </div>
    <div class="sub-section-body">

        <div class="info-grid">
            <div class="info-tile">
                <div class="lbl">Invoice Number</div>
                <div class="val" style="font-size:.95rem;">{{$currentInvoice->invoice_number}}</div>
            </div>
            <div class="info-tile">
                <div class="lbl">Term</div>
                <div class="val" style="font-size:.95rem;">{{$currentInvoice->term_label ?? 'Current Term'}}</div>
            </div>
            <div class="info-tile">
                <div class="lbl">Students Counted</div>
                <div class="val">{{number_format($currentInvoice->student_count)}}</div>
            </div>
            <div class="info-tile">
                <div class="lbl">Rate Applied</div>
                <div class="val">GHS {{number_format($currentInvoice->rate_per_student, 2)}}</div>
            </div>
            <div class="info-tile ok">
                <div class="lbl">Amount Due</div>
                <div class="val" style="font-size:1.3rem;color:#d97706;">GHS {{number_format($currentInvoice->amount, 2)}}</div>
                <div class="sub">{{$currentInvoice->student_count}} × GHS {{number_format($currentInvoice->rate_per_student, 2)}}</div>
            </div>
        </div>

        {{-- Overdue warning --}}
        @if($currentInvoice->due_date && $currentInvoice->due_date < now()->toDateString())
        <div class="alert alert-danger d-flex align-items-center gap-2" style="font-size:.82rem;border-radius:.75rem;">
            <i class="fas fa-exclamation-triangle"></i>
            <span><strong>This invoice is overdue.</strong> Please pay immediately to avoid suspension of access.</span>
        </div>
        @endif

        {{-- Pay Now button --}}
        <a href="{{ route('admin.subscription.pay-invoice', $currentInvoice) }}"
           class="pay-btn renew" style="max-width:100%;margin-top:1rem;">
            <i class="fas fa-credit-card me-1"></i> Pay GHS {{number_format($currentInvoice->amount, 2)}} Now via Paystack
        </a>
        <p class="text-muted text-center mt-2" style="font-size:.72rem;">
            <i class="fas fa-shield-alt me-1"></i> Secure payment via Paystack — Card, MoMo, Bank Transfer
        </p>
    </div>
</div>
@endif

{{-- Invoice history ──────────────────────────────────────────────────────── --}}
@if($perStudentInvoices->count() > 0)
<div class="sub-section">
    <div class="sub-section-hd">
        <div class="hd-icon" style="background:#eff6ff;color:#3b82f6;">
            <i class="fas fa-history"></i>
        </div>
        <div>
            <h3>Invoice History</h3>
            <p>All per-student invoices for your school</p>
        </div>
    </div>
    <div class="sub-section-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.6rem .75rem;">Invoice #</th>
                        <th style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.6rem .75rem;">Term</th>
                        <th style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.6rem .75rem;">Students</th>
                        <th style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.6rem .75rem;">Rate</th>
                        <th style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.6rem .75rem;">Amount</th>
                        <th style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.6rem .75rem;">Date</th>
                        <th style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;padding:.6rem .75rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($perStudentInvoices as $inv)
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:.5rem .75rem;font-weight:600;font-size:.78rem;">{{$inv->invoice_number}}</td>
                        <td style="padding:.5rem .75rem;font-size:.78rem;color:#64748b;">{{$inv->term_label ?? '—'}}</td>
                        <td style="padding:.5rem .75rem;font-size:.78rem;">{{number_format($inv->student_count)}}</td>
                        <td style="padding:.5rem .75rem;font-size:.78rem;">GHS {{number_format($inv->rate_per_student, 2)}}</td>
                        <td style="padding:.5rem .75rem;font-weight:700;font-size:.82rem;">GHS {{number_format($inv->amount, 2)}}</td>
                        <td style="padding:.5rem .75rem;font-size:.72rem;color:#94a3b8;">{{$inv->created_at->format('d M Y')}}</td>
                        <td style="padding:.5rem .75rem;">
                            @if($inv->status === 'paid')
                            <span class="sub-badge active" style="font-size:.6rem;">Paid</span>
                            @elseif($inv->status === 'pending' && $inv->due_date && $inv->due_date < now()->toDateString())
                            <span class="sub-badge expired" style="font-size:.6rem;">Overdue</span>
                            @elseif($inv->status === 'pending')
                            <span class="sub-badge trial" style="font-size:.6rem;">Pending</span>
                            @else
                            <span class="sub-badge suspended" style="font-size:.6rem;">{{ucfirst($inv->status)}}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endif
@endif

{{-- ── SUBSCRIPTION DETAILS ────────────────────────────────────────── --}}
<div class="sub-section">
    <div class="sub-section-hd">
        <div class="hd-icon" style="background:color-mix(in srgb,var(--primary-pale) 80%,#fff);color:var(--brand);">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div>
            <h3>Subscription Details</h3>
            <p>Your platform access and renewal information</p>
        </div>
    </div>
    <div class="sub-section-body">

        <div class="info-grid">
            <div class="info-tile {{ $daysLeft <= 14 && $tenant->subscription_ends_at ? 'warn' : ($daysLeft > 14 ? 'ok' : '') }}">
                <div class="lbl">Expires</div>
                @if($tenant->subscription_ends_at)
                    <div class="val">{{ $tenant->subscription_ends_at->format('d M Y') }}</div>
                    <div class="sub">{{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} remaining</div>
                @else
                    <div class="val" style="color:#94a3b8;">Not set</div>
                    <div class="sub">Contact owner</div>
                @endif
            </div>

            @if($tenant->trial_ends_at)
            <div class="info-tile">
                <div class="lbl">Trial Ends</div>
                <div class="val">{{ $tenant->trial_ends_at->format('d M Y') }}</div>
                <div class="sub">{{ $tenant->trialDaysRemaining() }}d left</div>
            </div>
            @endif

            @if($tenant->plan)
            <div class="info-tile">
                <div class="lbl">Plan Price</div>
                <div class="val" style="color:var(--brand);">GHS {{ number_format($tenant->plan->price, 2) }}</div>
                <div class="sub">per {{ $tenant->plan->billing_cycle }}</div>
            </div>

            @if($tenant->plan->max_students)
            <div class="info-tile">
                <div class="lbl">Max Students</div>
                <div class="val">{{ number_format($tenant->plan->max_students) }}</div>
                <div class="sub">on this plan</div>
            </div>
            @endif
            @endif
        </div>

        @if($tenant->plan && $tenant->plan->price > 0)
        <a href="{{ route('admin.subscription.pay') }}"
           class="pay-btn renew" style="text-decoration:none;">
            <i class="fas fa-credit-card"></i>
            Renew Subscription via Paystack
        </a>
        <p style="text-align:center;font-size:.72rem;color:#94a3b8;margin-top:.6rem;">
            <i class="fas fa-lock" style="font-size:.6rem;"></i>
            Secured by Paystack &nbsp;·&nbsp; Card &nbsp;·&nbsp; Mobile Money &nbsp;·&nbsp; Bank Transfer
        </p>
        @elseif(!$tenant->plan)
        <div class="flex items-center gap-3 p-4 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-sm">
            <i class="fas fa-info-circle"></i>
            No plan assigned to your school. Contact the platform owner to assign a plan.
        </div>
        @endif

    </div>
</div>

{{-- ── MESSAGING CREDITS ───────────────────────────────────────────── --}}
<div class="sub-section">
    <div class="sub-section-hd">
        <div class="hd-icon" style="background:#f0fdf4;color:#16a34a;">
            <i class="fas fa-comment-dots"></i>
        </div>
        <div>
            <h3>Messaging Credits</h3>
            <p>SMS &amp; WhatsApp credits for fee reminders and announcements</p>
        </div>
    </div>
    <div class="sub-section-body">

        {{-- Credit balance tiles --}}
        <div style="display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
            @php
                // For branch admin — show their branch pool; for super admin — show shared pool
                $displayCredits = ($currentBranchId ?? null) && $branchMsgCredits
                    ? $branchMsgCredits
                    : $msgCredits;
                $smsQty  = ($displayCredits ?? null)?->sms_credits      ?? 0;
                $waQty   = ($displayCredits ?? null)?->whatsapp_credits  ?? 0;
                $smsUsed = ($displayCredits ?? null)?->sms_used          ?? 0;
                $waUsed  = ($displayCredits ?? null)?->whatsapp_used     ?? 0;
                $poolLabel = ($currentBranchId ?? null) && $branchMsgCredits && !$branchMsgCredits->isShared()
                    ? 'Branch Pool'
                    : 'School-wide Shared Pool';
            @endphp
            <div class="credit-tile" style="background:#eff6ff;border-color:#bfdbfe;">
                <div class="big" style="color:#3b82f6;">{{ number_format($smsQty) }}</div>
                <div class="tag" style="color:#3b82f6;"><i class="fas fa-sms me-1"></i> SMS Credits</div>
                <div class="warn-txt" style="color:#64748b;font-size:.65rem;">{{ $poolLabel }}</div>
                @if($smsQty == 0)
                    <div class="warn-txt" style="color:#ef4444;font-weight:600;">No credits &mdash; top up below</div>
                @elseif($smsQty < 20)
                    <div class="warn-txt" style="color:var(--brand);font-weight:600;">Running low</div>
                @else
                    <div class="warn-txt" style="color:#64748b;">{{ number_format($smsUsed) }} used lifetime</div>
                @endif
            </div>
            <div class="credit-tile" style="background:#f0fdf4;border-color:#bbf7d0;">
                <div class="big" style="color:#16a34a;">{{ number_format($waQty) }}</div>
                <div class="tag" style="color:#16a34a;"><i class="fab fa-whatsapp me-1"></i> WhatsApp Credits</div>
                <div class="warn-txt" style="color:#64748b;font-size:.65rem;">{{ $poolLabel }}</div>
                @if($waQty == 0)
                    <div class="warn-txt" style="color:#ef4444;font-weight:600;">No credits &mdash; top up below</div>
                @elseif($waQty < 10)
                    <div class="warn-txt" style="color:var(--brand);font-weight:600;">Running low</div>
                @else
                    <div class="warn-txt" style="color:#64748b;">{{ number_format($waUsed) }} used lifetime</div>
                @endif
            </div>
        </div>

        {{-- Branch credit pools (super admin only) --}}
        @if(!($currentBranchId ?? null) && isset($allBranchCredits) && $allBranchCredits->count())
        <div style="background:#f8fafc;border-radius:.9rem;border:1px solid #e2e8f0;padding:1rem 1.25rem;margin-bottom:1.25rem;">
            <p style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem;">
                <i class="fas fa-code-branch mr-1"></i> Branch-Specific Credit Pools
            </p>
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                @foreach($allBranchCredits as $bc)
                @php
                    $bcBranch = ($branches ?? collect())->firstWhere('id', $bc->branch_id);
                @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.6rem .75rem;background:#fff;border-radius:.6rem;border:1px solid #f1f5f9;">
                    <div>
                        <span style="font-size:.82rem;font-weight:700;color:#1e293b;">
                            {{ $bcBranch->name ?? 'Branch ' . $bc->branch_id }}
                        </span>
                    </div>
                    <div style="display:flex;gap:1rem;align-items:center;">
                        <span style="font-size:.78rem;color:#3b82f6;font-weight:700;">
                            <i class="fas fa-sms mr-1"></i>{{ number_format($bc->sms_credits) }} SMS
                        </span>
                        <span style="font-size:.78rem;color:#16a34a;font-weight:700;">
                            <i class="fab fa-whatsapp mr-1"></i>{{ number_format($bc->whatsapp_credits) }} WA
                        </span>
                        <span style="font-size:.65rem;color:#94a3b8;">
                            {{ number_format($bc->sms_used + $bc->whatsapp_used) }} used
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            <p style="font-size:.7rem;color:#94a3b8;margin-top:.6rem;">
                <i class="fas fa-info-circle mr-1"></i>
                Top up individual branches from the <strong>Owner Panel → Messaging Credits</strong>.
                When a branch has its own pool, it uses that pool exclusively. Otherwise it draws from the school-wide shared pool above.
            </p>
        </div>
        @endif

        {{-- Package selector --}}
        @if(isset($msgPackages) && $msgPackages->count())
        <p style="font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem;">
            Top Up &mdash; Choose a Package
        </p>
        <form method="POST" action="{{ route('paystack.messaging.initialize', $tenant) }}" id="msgForm">
            @csrf
            <input type="hidden" name="payer_email" value="{{ $tenant->admin_email }}">
            <input type="hidden" name="payer_name"  value="{{ $tenant->admin_name }}">

            {{-- Branch selector — shown if school has branches --}}
            @if(isset($branches) && $branches->count())
            <div style="margin-bottom:1rem;">
                <p style="font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">
                    Allocate Credits To
                </p>
                <select name="branch_id" style="width:100%;padding:.65rem 1rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.85rem;background:#fff;">
                    <option value="">School-wide shared pool (all branches)</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ ($currentBranchId ?? null) == $b->id ? 'selected' : '' }}>
                        {{ $b->name }} only
                    </option>
                    @endforeach
                </select>
                <p style="font-size:.68rem;color:#94a3b8;margin-top:.4rem;">
                    Choosing a branch gives it a private credit pool that other branches cannot access.
                </p>
            </div>
            @endif

            <div style="display:flex;flex-direction:column;gap:.6rem;margin-bottom:1.25rem;">
                @foreach($msgPackages as $pkg)
                <div class="pkg-card" id="pkgCard{{ $pkg->id }}" onclick="selectPkg({{ $pkg->id }},this)">
                    <input type="radio" name="package_id" value="{{ $pkg->id }}" id="pkgRadio{{ $pkg->id }}" style="display:none;">
                    <div class="pkg-radio" id="pkgDot{{ $pkg->id }}"></div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:800;font-size:.875rem;color:#1e293b;">{{ $pkg->name }}</div>
                        <div style="font-size:.75rem;color:#64748b;margin-top:2px;">
                            <span style="color:#3b82f6;font-weight:700;">{{ number_format($pkg->sms_credits) }} SMS</span>
                            <span style="color:#94a3b8;margin:0 4px;">+</span>
                            <span style="color:#16a34a;font-weight:700;">{{ number_format($pkg->whatsapp_credits) }} WhatsApp</span>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:1.1rem;font-weight:900;color:var(--brand);">GHS {{ number_format($pkg->price, 2) }}</div>
                        @if($pkg->description)
                        <div style="font-size:.68rem;color:#94a3b8;">{{ $pkg->description }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <button type="submit" id="buyBtn" class="pay-btn" disabled>
                <i class="fas fa-lock" id="buyIcon"></i>
                <span id="buyBtnText">Select a package above to continue</span>
            </button>
            <p style="text-align:center;font-size:.7rem;color:#94a3b8;margin-top:.5rem;">
                <i class="fas fa-shield-alt" style="font-size:.6rem;"></i>
                Secured by Paystack &nbsp;·&nbsp; Card &nbsp;·&nbsp; Mobile Money
            </p>
        </form>

        @else
        <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:.9rem;padding:1rem 1.25rem;font-size:.85rem;color:#92400e;display:flex;align-items:center;gap:.75rem;">
            <i class="fas fa-info-circle" style="font-size:1.1rem;flex-shrink:0;"></i>
            <span>No credit packages available yet. Contact the platform owner to set up messaging packages.</span>
        </div>
        @endif

    </div>
</div>

{{-- ── PAYMENT HISTORY ─────────────────────────────────────────────── --}}
<div class="sub-section">
    <div class="sub-section-hd">
        <div class="hd-icon" style="background:color-mix(in srgb,var(--primary-pale) 80%,#fff);color:var(--brand);">
            <i class="fas fa-receipt"></i>
        </div>
        <div>
            <h3>Payment History</h3>
            <p>Your last 10 subscription and messaging credit payments</p>
        </div>
    </div>

    @if($subscriptionPayments->count() || $creditPayments->count())

    {{-- Subscription payments --}}
    @if($subscriptionPayments->count())
    <div style="padding:.75rem 1.5rem;background:#f8fafc;border-bottom:1px solid #f1f5f9;">
        <span style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;">
            <i class="fas fa-shield-alt me-1"></i> Subscription Payments
        </span>
    </div>
    @foreach($subscriptionPayments as $p)
    <div class="pmt-row">
        <div>
            <div style="font-size:.875rem;font-weight:700;color:#1e293b;">
                GHS {{ number_format($p->amount, 2) }}
            </div>
            <div style="font-size:.72rem;color:#94a3b8;margin-top:2px;">
                <code style="background:#f1f5f9;padding:1px 6px;border-radius:4px;font-size:.65rem;">{{ $p->receipt_number }}</code>
                &nbsp; {{ $p->payment_date->format('d M Y') }}
                @if($p->paid_online ?? false)
                <span style="background:#eff6ff;color:#2563eb;font-size:.6rem;font-weight:700;padding:1px 6px;border-radius:4px;margin-left:4px;">Online</span>
                @endif
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:.6rem;">
            <span class="pmt-badge" style="background:var(--primary-pale);color:var(--brand);">
                {{ ucfirst(str_replace('_',' ', $p->payment_method)) }}
            </span>
            @if($p->months_paid)
            <span class="pmt-badge" style="background:#f1f5f9;color:#64748b;">
                {{ $p->months_paid }}mo
            </span>
            @endif
        </div>
    </div>
    @endforeach
    @endif

    {{-- Messaging credit payments --}}
    @if($creditPayments->count())
    <div style="padding:.75rem 1.5rem;background:#f8fafc;border-bottom:1px solid #f1f5f9;border-top:1px solid #f1f5f9;">
        <span style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;">
            <i class="fas fa-sms me-1"></i> Messaging Credit Payments
        </span>
    </div>
    @foreach($creditPayments as $p)
    <div class="pmt-row">
        <div>
            <div style="font-size:.875rem;font-weight:700;color:#1e293b;">
                GHS {{ number_format($p->amount, 2) }}
                <span style="font-size:.72rem;color:#3b82f6;font-weight:600;margin-left:6px;">
                    +{{ number_format($p->sms_credits_added) }} SMS
                </span>
                <span style="font-size:.72rem;color:#16a34a;font-weight:600;margin-left:4px;">
                    +{{ number_format($p->whatsapp_credits_added) }} WA
                </span>
            </div>
            <div style="font-size:.72rem;color:#94a3b8;margin-top:2px;">
                <code style="background:#f1f5f9;padding:1px 6px;border-radius:4px;font-size:.65rem;">{{ $p->receipt_number }}</code>
                &nbsp; {{ $p->created_at->format('d M Y') }}
                @if($p->payment_method === 'paystack')
                <span style="background:#eff6ff;color:#2563eb;font-size:.6rem;font-weight:700;padding:1px 6px;border-radius:4px;margin-left:4px;">Online</span>
                @endif
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:.6rem;">
            <span class="pmt-badge" style="background:#fef3c7;color:#d97706;">
                {{ ucfirst(str_replace('_',' ', $p->payment_method)) }}
            </span>
            <span class="pmt-badge" style="background:{{ $p->status === 'completed' ? '#dcfce7' : '#fef2f2' }};color:{{ $p->status === 'completed' ? '#16a34a' : '#dc2626' }};">
                {{ ucfirst($p->status) }}
            </span>
        </div>
    </div>
    @endforeach
    @endif

    @else
    <div style="text-align:center;padding:2.5rem 1rem;color:#94a3b8;">
        <i class="fas fa-receipt" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
        <div style="font-size:.875rem;">No payments recorded yet.</div>
        <div style="font-size:.75rem;margin-top:.25rem;">Your subscription payments will appear here.</div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function selectPkg(id, el) {
    document.querySelectorAll('.pkg-card').forEach(function(c) {
        c.classList.remove('selected');
    });
    el.classList.add('selected');
    document.getElementById('pkgRadio' + id).checked = true;

    var btn  = document.getElementById('buyBtn');
    var icon = document.getElementById('buyIcon');
    var txt  = document.getElementById('buyBtnText');
    var priceEl = el.querySelector('[style*="var(--brand)"]');
    var price   = priceEl ? priceEl.textContent.trim() : '';

    btn.disabled = false;
    icon.className = 'fas fa-credit-card';
    txt.textContent = 'Pay ' + price + ' via Paystack';
}
</script>
@endpush
@endsection
