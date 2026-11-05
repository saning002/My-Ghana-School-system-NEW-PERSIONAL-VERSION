@extends('owner.layout')
@section('title','School Credit Balances')
@section('page-title','Messaging Credit Balances')

@section('topbar-actions')
    <a href="{{ route('owner.messaging.packages') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-box me-1"></i> Packages
    </a>
@endsection

@section('title','School Credit Balances')
@section('page-title','Messaging Credit Balances')

@section('topbar-actions')
    <a href="{{ route('owner.messaging.packages') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-box me-1"></i> Packages
    </a>
@endsection

@section('content')

{{-- ── Flash messages ───────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 rounded-3" role="alert">
    <i class="fas fa-check-circle flex-shrink-0"></i>
    <span>{{ session('success') }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 rounded-3" role="alert">
    <i class="fas fa-times-circle flex-shrink-0"></i>
    <span>{{ session('error') }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── PENDING ACTIVATION PANEL ─────────────────────────────────────────── --}}
@php
    $pendingPayments = collect();
    try {
        $pendingPayments = \App\Models\MessagingCreditPayment::with('tenant')
            ->where('credits_pending', true)
            ->orderByDesc('created_at')
            ->get();
    } catch (\Throwable $e) {}
@endphp

@if($pendingPayments->count())
<div class="owner-card mb-4" style="border:2px solid #dc2626;background:#fffbeb;">
    <div class="card-header d-flex align-items-center gap-2"
         style="background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;border-radius:.75rem .75rem 0 0;">
        <i class="fas fa-exclamation-triangle"></i>
        <h5 class="mb-0 fw-bold">{{ $pendingPayments->count() }} Payment{{ $pendingPayments->count() > 1 ? 's' : '' }} Awaiting Credit Activation</h5>
        <span class="ms-auto badge bg-white text-danger fw-bold">ACTION REQUIRED</span>
    </div>
    <div class="p-3">
        <p style="font-size:.85rem;color:#92400e;margin-bottom:1rem;">
            <i class="fas fa-info-circle me-1"></i>
            These schools paid for SMS credits but their credits are on hold because your Arkesel balance was insufficient at the time.
            Top up your <a href="https://arkesel.com/login" target="_blank" class="fw-bold text-danger">Arkesel account</a>,
            then click <strong>Activate</strong> for each school below.
        </p>
        <div class="table-responsive">
            <table class="table table-sm" style="margin:0;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:#64748b;">School</th>
                        <th style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:#64748b;">SMS Credits</th>
                        <th style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:#64748b;">Amount Paid</th>
                        <th style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:#64748b;">Date Paid</th>
                        <th style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:#64748b;">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pendingPayments as $pp)
                <tr style="border-bottom:1px solid #fef3c7;">
                    <td class="fw-semibold" style="vertical-align:middle;">
                        {{ $pp->tenant?->name ?? '—' }}
                    </td>
                    <td style="vertical-align:middle;">
                        <span class="badge bg-warning text-dark fw-bold" style="font-size:.8rem;">
                            {{ number_format($pp->sms_credits_added) }} SMS
                        </span>
                        @if($pp->whatsapp_credits_added > 0)
                        <span class="badge bg-success ms-1 fw-bold" style="font-size:.8rem;">
                            +{{ number_format($pp->whatsapp_credits_added) }} WA
                        </span>
                        @endif
                    </td>
                    <td style="vertical-align:middle;font-weight:700;color:#16a34a;">
                        GHS {{ number_format($pp->amount, 2) }}
                    </td>
                    <td style="vertical-align:middle;color:#64748b;font-size:.82rem;">
                        {{ $pp->created_at?->format('d M Y, g:ia') ?? '—' }}
                    </td>
                    <td style="vertical-align:middle;">
                        <form method="POST"
                              action="{{ route('owner.messaging.activate', $pp) }}"
                              onsubmit="return confirm('Activate {{ $pp->sms_credits_added }} SMS credits for {{ addslashes($pp->tenant?->name ?? '') }}?\n\nMake sure you have topped up Arkesel first.')">
                            @csrf
                            <button type="submit"
                                    class="btn btn-sm fw-bold"
                                    style="background:#16a34a;color:#fff;border:none;padding:.35rem .9rem;border-radius:.5rem;">
                                <i class="fas fa-check me-1"></i> Activate
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ── School balances table ─────────────────────────────────────────────── --}}
<div class="owner-table">
    <table class="table">
        <thead>
            <tr>
                <th>School</th>
                <th>SMS Credits</th>
                <th>WhatsApp Credits</th>
                <th>SMS Used</th>
                <th>WA Used</th>
                <th>Last Top-Up</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($tenants as $tenant)
        @php $credit = $tenant->messagingCredit; @endphp
        <tr>
            <td>
                <a href="{{ route('owner.schools.show', $tenant) }}" style="font-weight:600;color:#1e293b;text-decoration:none;">
                    {{ $tenant->name }}
                </a>
            </td>
            <td>
                <span style="font-weight:700;color:{{ ($credit?->sms_credits ?? 0) > 0 ? '#16a34a' : '#ef4444' }};">
                    {{ number_format($credit?->sms_credits ?? 0) }}
                </span>
            </td>
            <td>
                <span style="font-weight:700;color:{{ ($credit?->whatsapp_credits ?? 0) > 0 ? '#16a34a' : '#ef4444' }};">
                    {{ number_format($credit?->whatsapp_credits ?? 0) }}
                </span>
            </td>
            <td><small class="text-muted">{{ number_format($credit?->sms_used ?? 0) }}</small></td>
            <td><small class="text-muted">{{ number_format($credit?->whatsapp_used ?? 0) }}</small></td>
            <td><small class="text-muted">{{ $credit?->last_topped_up_at?->format('d M Y') ?? '—' }}</small></td>
            <td>
                <button class="btn btn-sm btn-owner-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addCredits{{ $tenant->id }}">
                    <i class="fas fa-plus me-1"></i> Add Credits
                </button>
            </td>
        </tr>

        {{-- Add Credits Modal --}}
        <div class="modal fade" id="addCredits{{ $tenant->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('owner.messaging.add-credits', $tenant) }}">
                        @csrf
                        <div class="modal-header">
                            <h6 class="modal-title fw-bold">Add Credits — {{ $tenant->name }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Branch selector --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Allocate To</label>
                                <select name="branch_id" class="form-select">
                                    <option value="">School-wide Shared Pool (all branches draw from here)</option>
                                    @php
                                        try {
                                            // Get branches from tenant DB
                                            \Illuminate\Support\Facades\DB::purge('tenant');
                                            \Illuminate\Support\Facades\Config::set('database.connections.tenant', $tenant->dbConfig());
                                            \Illuminate\Support\Facades\DB::setDefaultConnection('tenant');
                                            $tenantBranches = \Illuminate\Support\Facades\DB::connection('tenant')->table('church_branches')->orderBy('name')->get(['id','name']);
                                            \Illuminate\Support\Facades\DB::setDefaultConnection('pgsql');
                                        } catch (\Throwable $e) {
                                            $tenantBranches = collect();
                                            \Illuminate\Support\Facades\DB::setDefaultConnection('pgsql');
                                        }
                                    @endphp
                                    @foreach($tenantBranches as $b)
                                    <option value="{{ $b->id }}">Branch: {{ $b->name }} (dedicated pool)</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Choose a branch to give it its own private credit pool, or leave as School-wide to top up the shared pool.</div>
                            </div>
                            {{-- Quick package select --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Quick Select Package</label>
                                <select class="form-select" id="pkgSelect{{ $tenant->id }}"
                                    onchange="fillCredits({{ $tenant->id }}, this)">
                                    <option value="">— Select a package —</option>
                                    @foreach($packages as $pkg)
                                    <option value="{{ $pkg->id }}"
                                        data-sms="{{ $pkg->sms_credits }}"
                                        data-wa="{{ $pkg->whatsapp_credits }}"
                                        data-price="{{ $pkg->price }}">
                                        {{ $pkg->name }} — GHS {{ number_format($pkg->price, 2) }}
                                        ({{ $pkg->sms_credits }} SMS + {{ $pkg->whatsapp_credits }} WA)
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">SMS Credits</label>
                                    <input type="number" name="sms_credits" id="smsInput{{ $tenant->id }}" class="form-control" value="0" min="0" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">WhatsApp Credits</label>
                                    <input type="number" name="whatsapp_credits" id="waInput{{ $tenant->id }}" class="form-control" value="0" min="0" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Amount Paid (GHS)</label>
                                    <input type="number" name="amount" id="amtInput{{ $tenant->id }}" class="form-control" value="0" step="0.01" min="0" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        @foreach(['cash','mobile_money','bank_transfer','card','paystack','other'] as $m)
                                        <option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Notes</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Optional notes...">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-owner-primary">
                                <i class="fas fa-plus me-1"></i> Add Credits
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script>
function fillCredits(tenantId, select) {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) return;
    document.getElementById('smsInput' + tenantId).value = opt.dataset.sms;
    document.getElementById('waInput'  + tenantId).value = opt.dataset.wa;
    document.getElementById('amtInput' + tenantId).value = opt.dataset.price;
}
</script>
@endpush
@endsection
