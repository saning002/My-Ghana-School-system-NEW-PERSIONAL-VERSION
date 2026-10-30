@extends('layouts.app')
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
            <div class="credit-tile" style="background:#eff6ff;border-color:#bfdbfe;">
                <div class="big" style="color:#3b82f6;">{{ number_format($smsQty) }}</div>
                <div class="tag" style="color:#3b82f6;"><i class="fas fa-sms me-1"></i> SMS Credits</div>
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
                @if($waQty == 0)
                    <div class="warn-txt" style="color:#ef4444;font-weight:600;">No credits &mdash; top up below</div>
                @elseif($waQty < 10)
                    <div class="warn-txt" style="color:var(--brand);font-weight:600;">Running low</div>
                @else
                    <div class="warn-txt" style="color:#64748b;">{{ number_format($waUsed) }} used lifetime</div>
                @endif
            </div>
        </div>

        {{-- Package selector --}}
        @if(isset($msgPackages) && $msgPackages->count())
        <p style="font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem;">
            Top Up &mdash; Choose a Package
        </p>
        <form method="POST" action="{{ route('paystack.messaging.initialize', $tenant) }}" id="msgForm">
            @csrf
            <input type="hidden" name="payer_email" value="{{ $tenant->admin_email }}">
            <input type="hidden" name="payer_name"  value="{{ $tenant->admin_name }}">

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
