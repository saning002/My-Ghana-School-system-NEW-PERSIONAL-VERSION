@extends('owner.layout')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('topbar-actions')
    <a href="{{ route('owner.schools.create') }}" class="btn btn-sm btn-owner-primary">
        <i class="fas fa-plus me-1"></i> Add School
    </a>
@endsection

@section('content')

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:#eff6ff;color:#3b82f6;"><i class="fas fa-school"></i></div>
                <span class="badge badge-active rounded-pill">Total</span>
            </div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Schools</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-check-circle"></i></div>
                <span class="badge badge-active rounded-pill">{{ $stats['active'] }}</span>
            </div>
            <div class="stat-value">{{ $stats['active'] }}</div>
            <div class="stat-label">Active Schools</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:#fef9c3;color:#ca8a04;"><i class="fas fa-clock"></i></div>
                <span class="badge badge-trial rounded-pill">Trial</span>
            </div>
            <div class="stat-value">{{ $stats['trial'] }}</div>
            <div class="stat-label">On Trial</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="fas fa-ban"></i></div>
                <span class="badge badge-suspended rounded-pill">{{ $stats['suspended'] }}</span>
            </div>
            <div class="stat-value">{{ $stats['suspended'] }}</div>
            <div class="stat-label">Suspended</div>
        </div>
    </div>
</div>

{{-- Revenue Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#fdf4ff;color:#a855f7;font-size:1.5rem;width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <div class="stat-value" style="font-size:1.2rem;">GHS {{ number_format($revenueThisMonth, 2) }}</div>
                <div class="stat-label">Revenue This Month</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#ecfdf5;color:#10b981;font-size:1.5rem;width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <div class="stat-value" style="font-size:1.2rem;">GHS {{ number_format($revenueTotal, 2) }}</div>
                <div class="stat-label">Total Revenue (All Time)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#eff6ff;color:#3b82f6;font-size:1.5rem;width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div class="stat-value" style="font-size:1.2rem;">GHS {{ number_format($subscriptionRevenueTotal, 2) }}</div>
                <div class="stat-label">Subscriptions (All Time)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;font-size:1.5rem;width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-sms"></i>
            </div>
            <div>
                <div class="stat-value" style="font-size:1.2rem;">GHS {{ number_format($messagingRevenueTotal, 2) }}</div>
                <div class="stat-label">Messaging Credits (All Time)</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Arkesel SMS Provider Balance ──────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    {{-- Your Arkesel balance (what YOU have left to sell) --}}
    <div class="col-md-4">
        @if($arkeselBalance && $arkeselBalance['sms_balance'] !== null)
        @php
            $bal = (float) $arkeselBalance['sms_balance'];
            $balColor = $bal > 500 ? '#16a34a' : ($bal > 100 ? '#d97706' : '#dc2626');
            $balBg    = $bal > 500 ? '#f0fdf4'  : ($bal > 100 ? '#fffbeb'  : '#fef2f2');
            $balLabel = $bal > 500 ? 'Good stock' : ($bal > 100 ? 'Running low' : 'Very low — top up now');
        @endphp
        <div class="owner-card p-0 overflow-hidden">
            <div class="p-4" style="background:linear-gradient(135deg,#0f172a,#1e3a5f);">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,.45);margin-bottom:.3rem;">
                            Your Arkesel Account
                        </div>
                        <div style="font-size:.9rem;font-weight:800;color:#fff;">SMS Credits Available to Sell</div>
                    </div>
                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-satellite-dish" style="color:#60a5fa;font-size:.9rem;"></i>
                    </div>
                </div>
                <div style="font-size:2.4rem;font-weight:900;color:#fff;letter-spacing:-.03em;line-height:1;">
                    {{ number_format($bal, 0) }}
                </div>
                <div style="font-size:.72rem;color:rgba(255,255,255,.45);margin-top:.2rem;">
                    credits in your Arkesel wallet
                </div>
                <div style="margin-top:.85rem;display:inline-flex;align-items:center;gap:.35rem;font-size:.7rem;font-weight:700;border-radius:99px;padding:.25rem .75rem;background:{{ $balBg }};color:{{ $balColor }};">
                    <i class="fas {{ $bal > 500 ? 'fa-check-circle' : ($bal > 100 ? 'fa-exclamation-circle' : 'fa-times-circle') }}" style="font-size:.65rem;"></i>
                    {{ $balLabel }}
                </div>
            </div>
            <div style="padding:.75rem 1rem;background:#f8fafc;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:.7rem;color:#94a3b8;font-weight:600;">
                    <i class="fas fa-sync-alt me-1"></i> Live from Arkesel API
                </span>
                <a href="https://arkesel.com" target="_blank"
                   style="font-size:.7rem;font-weight:700;color:#3b82f6;text-decoration:none;">
                    Top up →
                </a>
            </div>
        </div>
        @else
        <div class="owner-card p-4 text-center">
            <i class="fas fa-satellite-dish text-muted mb-2" style="font-size:1.8rem;opacity:.3;"></i>
            <p class="text-muted mb-1" style="font-size:.82rem;font-weight:600;">Arkesel balance unavailable</p>
            <p class="text-muted" style="font-size:.72rem;">
                Set <code>ARKESEL_API_KEY</code> in your Render environment variables.
            </p>
        </div>
        @endif
    </div>

    {{-- Credits allocated to all schools vs sent --}}
    <div class="col-md-4">
        <div class="owner-card p-4">
            <div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#94a3b8;margin-bottom:.85rem;">
                <i class="fas fa-chart-bar me-1"></i> Credits Across All Schools
            </div>
            <div class="d-flex flex-column gap-3">
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.3rem;">
                        <span style="font-size:.75rem;font-weight:700;color:#475569;">
                            <span style="width:10px;height:10px;border-radius:3px;background:#6366f1;display:inline-block;margin-right:6px;"></span>
                            Allocated to schools
                        </span>
                        <span style="font-size:.85rem;font-weight:900;color:#0f172a;">{{ number_format($totalCreditsAllocated) }}</span>
                    </div>
                    <div style="height:7px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:100%;background:#6366f1;border-radius:99px;"></div>
                    </div>
                </div>
                <div>
                    @php $usedPct = $totalCreditsAllocated > 0 ? min(100, round($totalCreditsSent / $totalCreditsAllocated * 100)) : 0; @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.3rem;">
                        <span style="font-size:.75rem;font-weight:700;color:#475569;">
                            <span style="width:10px;height:10px;border-radius:3px;background:#10b981;display:inline-block;margin-right:6px;"></span>
                            Messages sent by schools
                        </span>
                        <span style="font-size:.85rem;font-weight:900;color:#0f172a;">{{ number_format($totalCreditsSent) }}</span>
                    </div>
                    <div style="height:7px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:{{ $usedPct }}%;background:#10b981;border-radius:99px;transition:width .8s;"></div>
                    </div>
                    <p style="font-size:.67rem;color:#94a3b8;margin-top:.3rem;">{{ $usedPct }}% of allocated credits used</p>
                </div>
                <div style="border-top:1px solid #f1f5f9;padding-top:.85rem;">
                    @php $remaining = max(0, $totalCreditsAllocated - $totalCreditsSent); @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:.75rem;font-weight:700;color:#475569;">
                            <span style="width:10px;height:10px;border-radius:3px;background:#f59e0b;display:inline-block;margin-right:6px;"></span>
                            Still unused by schools
                        </span>
                        <span style="font-size:.85rem;font-weight:900;color:#d97706;">{{ number_format($remaining) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick SMS credit management links --}}
    <div class="col-md-4">
        <div class="owner-card p-4">
            <div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#94a3b8;margin-bottom:.85rem;">
                <i class="fas fa-bolt me-1"></i> SMS Management
            </div>
            <div class="d-flex flex-column gap-2">
                @foreach([
                    [route('owner.messaging.credits'),   'fa-coins',     'View all school balances',   '#ecfdf5','#16a34a'],
                    [route('owner.messaging.packages'),  'fa-box-open',  'Manage credit packages',     '#eff6ff','#3b82f6'],
                    [route('owner.payments.index'),      'fa-receipt',   'View all credit payments',   '#faf5ff','#7c3aed'],
                ] as [$href,$icon,$label,$bg,$clr])
                <a href="{{ $href }}"
                   style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:.875rem;background:{{ $bg }};text-decoration:none;border:1px solid transparent;transition:all .15s;"
                   onmouseover="this.style.borderColor='{{ $clr }}'"
                   onmouseout="this.style.borderColor='transparent'">
                    <div style="width:34px;height:34px;border-radius:.65rem;background:{{ $clr }}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas {{ $icon }}" style="color:{{ $clr }};font-size:.8rem;"></i>
                    </div>
                    <span style="font-size:.8rem;font-weight:700;color:#1e293b;">{{ $label }}</span>
                    <i class="fas fa-chevron-right ms-auto" style="color:#cbd5e1;font-size:.65rem;"></i>
                </a>
                @endforeach
                <a href="https://arkesel.com/login" target="_blank"
                   style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:.875rem;background:#fff7ed;text-decoration:none;border:1px solid transparent;transition:all .15s;"
                   onmouseover="this.style.borderColor='#f97316'"
                   onmouseout="this.style.borderColor='transparent'">
                    <div style="width:34px;height:34px;border-radius:.65rem;background:#f9731622;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-external-link-alt" style="color:#f97316;font-size:.8rem;"></i>
                    </div>
                    <span style="font-size:.8rem;font-weight:700;color:#1e293b;">Top up on Arkesel.com</span>
                    <i class="fas fa-chevron-right ms-auto" style="color:#cbd5e1;font-size:.65rem;"></i>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Alerts: Expiring soon --}}
@if($expiringSoon->count() || $trialsExpiringSoon->count())
<div class="row g-3 mb-4">
    @if($expiringSoon->count())
    <div class="col-md-6">
        <div class="owner-card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                Subscriptions Expiring (30 days)
                <span class="badge bg-warning text-dark ms-auto">{{ $expiringSoon->count() }}</span>
            </div>
            <div class="card-body p-0">
                @foreach($expiringSoon as $school)
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <div>
                        <div style="font-weight:600;font-size:0.875rem;">{{ $school->name }}</div>
                        <small class="text-muted">Expires {{ $school->subscription_ends_at->format('d M Y') }}</small>
                    </div>
                    <span class="badge" style="background:#fef9c3;color:#92400e;">{{ $school->subscriptionDaysRemaining() }}d left</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($trialsExpiringSoon->count())
    <div class="col-md-6">
        <div class="owner-card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-hourglass-half text-info"></i>
                Trials Expiring (7 days)
                <span class="badge bg-info ms-auto">{{ $trialsExpiringSoon->count() }}</span>
            </div>
            <div class="card-body p-0">
                @foreach($trialsExpiringSoon as $school)
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <div>
                        <div style="font-weight:600;font-size:0.875rem;">{{ $school->name }}</div>
                        <small class="text-muted">Trial ends {{ $school->trial_ends_at->format('d M Y') }}</small>
                    </div>
                    <span class="badge badge-trial">{{ $school->trialDaysRemaining() }}d</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- Revenue Chart + Recent Payments --}}
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="owner-card h-100">
            <div class="card-header">Revenue (Last 12 Months)</div>
            <div class="card-body">
                <canvas id="revenueChart" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="owner-card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                Recent Payments
                <a href="{{ route('owner.payments.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:0.75rem;">View All</a>
            </div>
            <div class="card-body p-0">
            @php
                // Merge both payment types into one chronological list (most recent first)
                $allRecent = collect();

                foreach ($recentPayments as $p) {
                    $allRecent->push([
                        'school'      => $p->tenant->name ?? '—',
                        'date'        => $p->payment_date,
                        'amount'      => $p->amount,
                        'receipt'     => $p->receipt_number,
                        'type'        => 'subscription',
                        'type_label'  => 'Subscription',
                        'type_color'  => '#eff6ff',
                        'type_text'   => '#2563eb',
                        'method'      => $p->payment_method,
                    ]);
                }

                foreach ($recentMessagingPayments as $mp) {
                    $allRecent->push([
                        'school'      => $mp->tenant->name ?? '—',
                        'date'        => $mp->created_at,
                        'amount'      => $mp->amount,
                        'receipt'     => $mp->receipt_number,
                        'type'        => 'messaging',
                        'type_label'  => 'Messaging',
                        'type_color'  => '#fef3c7',
                        'type_text'   => '#d97706',
                        'method'      => $mp->payment_method ?? 'paystack',
                    ]);
                }

                // Sort by date descending, take 10
                $allRecent = $allRecent->sortByDesc('date')->take(10)->values();
            @endphp

            @forelse($allRecent as $p)
            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                <div style="min-width:0;flex:1;">
                    <div style="font-weight:600;font-size:0.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $p['school'] }}
                    </div>
                    <div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;margin-top:2px;">
                        <small class="text-muted" style="font-size:0.72rem;">
                            {{ $p['date'] ? $p['date']->format('d M Y') : '—' }}
                        </small>
                        <span class="badge" style="background:{{ $p['type_color'] }};color:{{ $p['type_text'] }};font-size:0.62rem;">
                            {{ $p['type_label'] }}
                        </span>
                        <span style="font-size:0.65rem;color:#94a3b8;text-transform:capitalize;">
                            {{ str_replace('_',' ', $p['method']) }}
                        </span>
                    </div>
                </div>
                <span style="font-weight:800;color:#16a34a;font-size:0.9rem;white-space:nowrap;margin-left:8px;">
                    GHS {{ number_format($p['amount'], 2) }}
                </span>
            </div>
            @empty
            <div class="text-center text-muted py-4" style="font-size:0.875rem;">No payments yet</div>
            @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Recent Schools + Recent Backups --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="owner-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                Recent Schools
                <a href="{{ route('owner.schools.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:0.75rem;">View All</a>
            </div>
            <div class="owner-table">
                <table class="table">
                    <thead><tr><th>School</th><th>Plan</th><th>Status</th><th>Added</th></tr></thead>
                    <tbody>
                    @forelse($recentSchools as $s)
                    <tr>
                        <td>
                            <a href="{{ route('owner.schools.show', $s) }}" style="font-weight:600;color:#1e293b;text-decoration:none;">{{ $s->name }}</a>
                            <div style="font-size:0.75rem;color:#94a3b8;">{{ $s->subdomain }}.yourdomain.com</div>
                        </td>
                        <td><small>{{ $s->plan->name ?? '—' }}</small></td>
                        <td><span class="badge badge-{{ $s->status }}">{{ ucfirst($s->status) }}</span></td>
                        <td><small class="text-muted">{{ $s->created_at->diffForHumans() }}</small></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No schools yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="owner-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                Recent Backups
                <a href="{{ route('owner.backups.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:0.75rem;">View All</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentBackups as $b)
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <div>
                        <div style="font-weight:600;font-size:0.8rem;">{{ $b->tenant->name ?? '—' }}</div>
                        <small class="text-muted">{{ $b->created_at->format('d M Y H:i') }}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge {{ $b->status === 'completed' ? 'bg-success' : ($b->status === 'failed' ? 'bg-danger' : 'bg-secondary') }}" style="font-size:0.7rem;">{{ ucfirst($b->status) }}</span>
                        <div style="font-size:0.72rem;color:#94a3b8;">{{ $b->file_size_human }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4" style="font-size:0.875rem;">No backups yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyRevenue->pluck('month')) !!},
        datasets: [{
            label: 'Subscriptions',
            data: {!! json_encode($monthlyRevenue->pluck('subscriptions')) !!},
            backgroundColor: 'rgba(233,69,96,0.6)',
            borderColor: '#e94560',
            borderWidth: 2,
            borderRadius: 6,
        }, {
            label: 'Messaging Credits',
            data: {!! json_encode($monthlyRevenue->pluck('messaging')) !!},
            backgroundColor: 'rgba(217,119,6,0.6)',
            borderColor: '#d97706',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { display: true, position: 'top', labels: { font: { size: 11 } } } },
        scales: {
            x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { stacked: true, beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
        }
    }
});
</script>
@endpush
