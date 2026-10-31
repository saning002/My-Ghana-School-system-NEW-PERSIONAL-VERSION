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
