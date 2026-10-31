@extends('owner.layout')
@section('title', $tenant->name)
@section('page-title', $tenant->name)

@section('topbar-actions')
    <a href="{{ route('owner.schools.edit', $tenant) }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-edit me-1"></i> Edit
    </a>
    <a href="{{ route('owner.payments.create') }}?tenant_id={{ $tenant->id }}" class="btn btn-sm btn-owner-primary">
        <i class="fas fa-plus me-1"></i> Add Payment
    </a>
@endsection

@section('content')

{{-- School Access URL --}}
<div class="owner-card mb-4" style="border-left: 4px solid #3b82f6;">
    <div class="card-body py-3">
        <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.4rem;">
            <i class="fas fa-link me-1"></i> School Login URL — send this to the school
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <code id="schoolUrl" style="background:#f1f5f9;padding:0.4rem 0.75rem;border-radius:8px;font-size:0.875rem;flex:1;word-break:break-all;">
                {{ url('/school/' . $tenant->slug . '/login') }}
            </code>
            <button onclick="navigator.clipboard.writeText(document.getElementById('schoolUrl').innerText.trim()).then(()=>alert('Copied!'))" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
</div>

{{-- Header info --}}
<div class="owner-card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:56px;height:56px;background:#e94560;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;">
                        <i class="fas fa-school"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold">{{ $tenant->name }}</h4>
                        <div style="font-size:0.85rem;color:#64748b;">
                            <code>{{ $tenant->subdomain ?? $tenant->slug }}</code> ·
                            {{ $tenant->admin_email }} ·
                            {{ optional($tenant->relationLoaded('plan') ? $tenant->plan : null)->name ?? 'No Plan' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <span class="badge badge-{{ $tenant->status }} fs-6 px-3 py-2">{{ ucfirst($tenant->status) }}</span>
                <div class="mt-2">
                    <form method="POST" action="{{ route('owner.schools.status', $tenant) }}" class="d-inline-flex gap-2 align-items-center">
                        @csrf
                        <select name="status" class="form-select form-select-sm" style="width:auto;">
                            @foreach(['active','trial','suspended','expired'] as $s)
                                <option value="{{ $s }}" {{ $tenant->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-owner-primary">Change</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stats row --}}
@php
    // Use variables passed from controller — no relationship calls in the view
    $paymentsCollection       = $paymentsCollection       ?? collect();
    $creditPaymentsCollection = $creditPaymentsCollection ?? collect();
    $backupsCollection        = $backupsCollection        ?? collect();
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-value">GHS {{ number_format($totalPaid ?? 0, 2) }}</div>
            <div class="stat-label">Total Paid</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-value">{{ $paymentsCollection->count() + $creditPaymentsCollection->count() }}</div>
            <div class="stat-label">Payments</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-value">{{ $backupsCollection->count() }}</div>
            <div class="stat-label">Backups</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-value">{{ $tenant->subscriptionDaysRemaining() ?: '—' }}</div>
            <div class="stat-label">Days Left</div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3" id="schoolTabs">
    <li class="nav-item"><a class="nav-link {{ !session('active_tab') || session('active_tab') === 'modules' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-features">
        <i class="fas fa-toggle-on me-1"></i> Modules
    </a></li>
    <li class="nav-item"><a class="nav-link {{ session('active_tab') === 'theme' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-theme">
        <i class="fas fa-palette me-1"></i> Theme
    </a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-payments">Payments</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-backups">Backups</a></li>
    <li class="nav-item"><a class="nav-link {{ session('active_tab') === 'setup' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-setup">
        <i class="fas fa-tools me-1"></i> Setup
    </a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-info">Info</a></li>
</ul>

<div class="tab-content">

    {{-- Modules Tab --}}
    <div class="tab-pane fade {{ !session('active_tab') || session('active_tab') === 'modules' ? 'show active' : '' }}" id="tab-features">
        <div class="owner-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="fas fa-toggle-on me-2 text-success"></i> Module Access for {{ $tenant->name }}</span>
                <span style="font-size:0.78rem;color:#94a3b8;">Toggle which parts of the system this school can access</span>
            </div>
            <div class="card-body">

                @if(session('active_tab') === 'modules')
                <div class="alert alert-success alert-dismissible fade show" style="border-radius:8px;" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <p style="font-size:0.85rem;color:#64748b;margin-bottom:1.5rem;">
                    <i class="fas fa-info-circle text-info me-1"></i>
                    Enabled modules appear in the school's sidebar. Disabled modules are completely hidden and blocked.
                    <span class="text-success"><i class="fas fa-layer-group"></i></span> = included in their plan.
                    Changes take effect immediately — no restart needed.
                </p>

                <form method="POST" action="{{ route('owner.schools.features', $tenant) }}">
                    @csrf
                    @php
                        $moduleGroups = [
                            'Core'          => ['icon' => 'fa-school',         'color' => '#3b82f6'],
                            'Academic'      => ['icon' => 'fa-graduation-cap', 'color' => '#8b5cf6'],
                            'Finance'       => ['icon' => 'fa-coins',          'color' => '#f59e0b'],
                            'Communication' => ['icon' => 'fa-bell',           'color' => '#10b981'],
                            'Reports'       => ['icon' => 'fa-chart-bar',      'color' => '#f43f5e'],
                            'Website'       => ['icon' => 'fa-globe',          'color' => '#0891b2'],
                        ];
                    @endphp

                    @forelse($features as $group => $groupFeatures)
                    @php $gStyle = $moduleGroups[$group] ?? ['icon' => 'fa-puzzle-piece', 'color' => '#64748b']; @endphp
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:2px solid #f1f5f9;">
                            <div style="width:28px;height:28px;background:{{ $gStyle['color'] }}20;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas {{ $gStyle['icon'] }}" style="color:{{ $gStyle['color'] }};font-size:0.75rem;"></i>
                            </div>
                            <span style="font-size:0.78rem;text-transform:uppercase;letter-spacing:1px;color:#64748b;font-weight:700;">
                                {{ ucfirst($group) }}
                            </span>
                        </div>
                        <div class="row g-2">
                            @foreach($groupFeatures as $feature)
                            @php
                                $isEnabled = isset($tenantOverrides[$feature->id])
                                    ? (bool) $tenantOverrides[$feature->id]
                                    : in_array($feature->id, $planFeatureIds);
                            @endphp
                            <div class="col-md-4 col-sm-6">
                                <label for="feat_{{ $feature->id }}"
                                    style="display:flex;align-items:flex-start;gap:10px;padding:10px 14px;border-radius:10px;border:2px solid {{ $isEnabled ? '#dcfce7' : '#f1f5f9' }};background:{{ $isEnabled ? '#f0fdf4' : '#f8fafc' }};cursor:pointer;transition:all .15s;"
                                    class="feat-label">
                                    <input class="form-check-input mt-1 feat-check" type="checkbox"
                                        name="features[]"
                                        value="{{ $feature->id }}"
                                        id="feat_{{ $feature->id }}"
                                        {{ $isEnabled ? 'checked' : '' }}>
                                    <div>
                                        <div style="font-size:0.875rem;font-weight:600;color:#1e293b;">
                                            {{ $feature->label }}
                                            @if(in_array($feature->id, $planFeatureIds))
                                                <i class="fas fa-layer-group text-success ms-1" title="In plan" style="font-size:0.7rem;"></i>
                                            @endif
                                        </div>
                                        @if(!empty($feature->description))
                                        <div style="font-size:0.75rem;color:#94a3b8;margin-top:2px;">{{ $feature->description }}</div>
                                        @endif
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-puzzle-piece text-muted mb-2" style="font-size:2rem;"></i>
                        <p class="text-muted">No modules defined yet.</p>
                        <a href="{{ route('owner.features.index') }}" class="btn btn-sm btn-owner-primary">
                            <i class="fas fa-plus me-1"></i> Add Modules
                        </a>
                    </div>
                    @endforelse

                    @if($features->count())
                    <div class="d-flex gap-2 mt-3 pt-3" style="border-top:1px solid #f1f5f9;">
                        <button type="submit" class="btn btn-owner-primary">
                            <i class="fas fa-save me-1"></i> Save Module Access
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAll(true)">Enable All</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAll(false)">Disable All</button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Theme Tab --}}
    <div class="tab-pane fade {{ session('active_tab') === 'theme' ? 'show active' : '' }}" id="tab-theme">
        <div class="row g-4">
            {{-- Colour pickers --}}
            <div class="col-lg-7">
                <div class="owner-card">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="fas fa-palette text-info"></i>
                        Admin Panel Theme for {{ $tenant->name }}
                    </div>
                    <div class="card-body">

                        @if(session('active_tab') === 'theme')
                        <div class="alert alert-success alert-dismissible fade show" style="border-radius:8px;" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        <p style="font-size:0.85rem;color:#64748b;margin-bottom:1.5rem;">
                            Set the admin panel colours for this school. These override anything the school sets themselves,
                            so use it when you want to brand a school's panel differently.
                            Leave fields blank to let the school control their own colours.
                        </p>

                        <form method="POST" action="{{ route('owner.schools.theme', $tenant) }}">
                            @csrf
                            @php $th = $tenant->theme_json ?? []; @endphp

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Sidebar Accent Colour
                                        <small class="text-muted fw-normal">(top glow &amp; active indicator)</small>
                                    </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="adminPrimary" class="form-control form-control-color"
                                            value="{{ $th['adminPrimary'] ?? '#D4A017' }}" style="width:60px;height:38px;">
                                        <input type="text" class="form-control form-control-sm color-text"
                                            value="{{ $th['adminPrimary'] ?? '#D4A017' }}"
                                            placeholder="#D4A017" style="font-family:monospace;"
                                            oninput="syncColor(this, 'adminPrimary')">
                                        <input type="hidden" name="adminPrimary" id="inp_adminPrimary" value="{{ $th['adminPrimary'] ?? '#D4A017' }}">
                                    </div>
                                    <div class="form-text">Affects sidebar active link, card accents, and buttons.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Sidebar Background Colour
                                    </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="adminNav_color" class="form-control form-control-color"
                                            value="{{ $th['adminNav'] ?? '#0B1121' }}" style="width:60px;height:38px;">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $th['adminNav'] ?? '#0B1121' }}"
                                            placeholder="#0B1121" style="font-family:monospace;"
                                            oninput="syncColor(this, 'adminNav')">
                                        <input type="hidden" name="adminNav" id="inp_adminNav" value="{{ $th['adminNav'] ?? '#0B1121' }}">
                                    </div>
                                    <div class="form-text">The dark background of the left sidebar.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Primary Action Colour
                                        <small class="text-muted fw-normal">(buttons, badges)</small>
                                    </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="primary_color" class="form-control form-control-color"
                                            value="{{ $th['primary'] ?? '#f59e0b' }}" style="width:60px;height:38px;">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $th['primary'] ?? '#f59e0b' }}"
                                            placeholder="#f59e0b" style="font-family:monospace;"
                                            oninput="syncColor(this, 'primary')">
                                        <input type="hidden" name="primary" id="inp_primary" value="{{ $th['primary'] ?? '#f59e0b' }}">
                                    </div>
                                    <div class="form-text">Used for Save buttons, success states, and pill badges.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:0.85rem;">
                                        Accent / Link Colour
                                    </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="accent_color" class="form-control form-control-color"
                                            value="{{ $th['accent'] ?? '#0891b2' }}" style="width:60px;height:38px;">
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $th['accent'] ?? '#0891b2' }}"
                                            placeholder="#0891b2" style="font-family:monospace;"
                                            oninput="syncColor(this, 'accent')">
                                        <input type="hidden" name="accent" id="inp_accent" value="{{ $th['accent'] ?? '#0891b2' }}">
                                    </div>
                                    <div class="form-text">Used for active tab underlines and secondary UI elements.</div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-owner-primary">
                                    <i class="fas fa-save me-1"></i> Save Theme
                                </button>
                                @if(!empty($th))
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                    onclick="if(confirm('Reset theme to defaults?')) { document.getElementById('resetThemeForm').submit(); }">
                                    <i class="fas fa-undo me-1"></i> Reset to Default
                                </button>
                                @endif
                            </div>
                        </form>

                        {{-- Reset form (empties theme_json) --}}
                        <form id="resetThemeForm" method="POST" action="{{ route('owner.schools.theme', $tenant) }}" style="display:none;">
                            @csrf
                            {{-- send empty values to clear --}}
                        </form>
                    </div>
                </div>
            </div>

            {{-- Live preview --}}
            <div class="col-lg-5">
                <div class="owner-card">
                    <div class="card-header"><i class="fas fa-eye me-2"></i>Live Preview</div>
                    <div class="card-body p-0">
                        <div id="themePreview" style="border-radius:0 0 10px 10px;overflow:hidden;">
                            {{-- Sidebar preview --}}
                            <div id="prev_sidebar" style="background:#0B1121;padding:16px 14px;display:flex;align-items:center;gap:10px;">
                                <div id="prev_logo" style="width:36px;height:36px;background:#D4A017;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-school" style="color:#fff;font-size:0.9rem;"></i>
                                </div>
                                <div>
                                    <div style="color:#fff;font-size:0.85rem;font-weight:700;">{{ $tenant->name }}</div>
                                    <div style="color:rgba(255,255,255,.4);font-size:0.7rem;">Admin Panel</div>
                                </div>
                            </div>
                            {{-- Nav items preview --}}
                            <div id="prev_nav" style="background:#0B1121;padding:8px 8px 4px;">
                                <div id="prev_active_item" style="background:rgba(212,160,23,.14);border-left:3px solid #D4A017;border-radius:8px;padding:8px 12px;color:#fff;font-size:0.82rem;font-weight:600;display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                    <i class="fas fa-chart-pie" style="width:16px;text-align:center;"></i> Dashboard
                                </div>
                                <div style="padding:8px 12px;color:rgba(255,255,255,.5);font-size:0.82rem;display:flex;align-items:center;gap:8px;">
                                    <i class="fas fa-user-graduate" style="width:16px;text-align:center;"></i> Students
                                </div>
                                <div style="padding:8px 12px;color:rgba(255,255,255,.5);font-size:0.82rem;display:flex;align-items:center;gap:8px;">
                                    <i class="fas fa-coins" style="width:16px;text-align:center;"></i> Fees &amp; Payments
                                </div>
                            </div>
                            {{-- Content area preview --}}
                            <div style="background:#f4f8ff;padding:12px;">
                                <div id="prev_btn" style="display:inline-flex;align-items:center;gap:6px;background:#f59e0b;color:#fff;padding:6px 14px;border-radius:8px;font-size:0.8rem;font-weight:600;">
                                    <i class="fas fa-save"></i> Save Changes
                                </div>
                                <div style="margin-top:8px;background:#fff;border-radius:8px;padding:10px;border:1px solid #e2e8f0;">
                                    <div id="prev_badge" style="display:inline-block;background:#fef8e8;color:#D4A017;font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:20px;">Active</div>
                                    <span style="font-size:0.8rem;color:#64748b;margin-left:8px;">Student: John Mensah</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Current applied theme --}}
                @if(!empty($th))
                <div class="owner-card mt-3">
                    <div class="card-header" style="font-size:0.85rem;">Currently Applied</div>
                    <div class="card-body py-2">
                        @foreach(['adminPrimary' => 'Sidebar Accent', 'adminNav' => 'Sidebar BG', 'primary' => 'Primary', 'accent' => 'Accent'] as $k => $label)
                        @if(isset($th[$k]))
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div style="width:20px;height:20px;background:{{ $th[$k] }};border-radius:4px;border:1px solid rgba(0,0,0,.1);flex-shrink:0;"></div>
                            <span style="font-size:0.8rem;color:#64748b;">{{ $label }}</span>
                            <code style="font-size:0.75rem;color:#94a3b8;">{{ $th[$k] }}</code>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Payments Tab --}}
    <div class="tab-pane fade" id="tab-payments">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Payment History</h6>
            <a href="{{ route('owner.payments.create') }}?tenant_id={{ $tenant->id }}" class="btn btn-sm btn-owner-primary">
                <i class="fas fa-plus me-1"></i> Record Payment
            </a>
        </div>

        {{-- Subscription Payments --}}
        <p style="font-size:0.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:.5rem;">
            <i class="fas fa-shield-alt me-1"></i> Subscription Payments
        </p>
        <div class="owner-table mb-3">
            <table class="table">
                <thead>
                    <tr><th>Receipt #</th><th>Date</th><th>Amount</th><th>Method</th><th>Months</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($paymentsCollection as $p)
                <tr>
                    <td><code style="font-size:0.8rem;">{{ $p->receipt_number }}</code></td>
                    <td>{{ $p->payment_date ? $p->payment_date->format('d M Y') : '—' }}</td>
                    <td><strong style="color:#16a34a;">GHS {{ number_format($p->amount, 2) }}</strong></td>
                    <td>{{ ucfirst(str_replace('_',' ', $p->payment_method)) }}</td>
                    <td>{{ $p->months_paid ?? '—' }}</td>
                    <td>
                        <a href="{{ route('owner.payments.receipt', $p) }}" class="btn btn-sm btn-outline-secondary" title="Receipt">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No subscription payments yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Messaging Credit Payments --}}
        <p style="font-size:0.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:.5rem;">
            <i class="fas fa-sms me-1"></i> Messaging Credit Payments
        </p>
        <div class="owner-table">
            <table class="table">
                <thead>
                    <tr><th>Receipt #</th><th>Date</th><th>Amount</th><th>SMS</th><th>WhatsApp</th><th>Method</th><th>Status</th></tr>
                </thead>
                <tbody>
                @php $creditPaymentsCollection = $creditPaymentsCollection ?? collect(); @endphp
                @forelse($creditPaymentsCollection as $p)
                <tr>
                    <td><code style="font-size:0.8rem;">{{ $p->receipt_number }}</code></td>
                    <td>{{ $p->created_at->format('d M Y') }}</td>
                    <td><strong style="color:#16a34a;">GHS {{ number_format($p->amount, 2) }}</strong></td>
                    <td><span style="color:#2563eb;font-weight:700;">+{{ number_format($p->sms_credits_added) }}</span></td>
                    <td><span style="color:#16a34a;font-weight:700;">+{{ number_format($p->whatsapp_credits_added) }}</span></td>
                    <td>{{ ucfirst(str_replace('_',' ', $p->payment_method)) }}</td>
                    <td>
                        <span class="badge {{ $p->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size:0.7rem;">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No messaging credit payments yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Backups Tab --}}
    <div class="tab-pane fade" id="tab-backups">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Backup History</h6>
            <form method="POST" action="{{ route('owner.backups.create', $tenant) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-owner-primary">
                    <i class="fas fa-database me-1"></i> Backup Now
                </button>
            </form>
        </div>
        <div class="owner-table">
            <table class="table">
                <thead>
                    <tr><th>Date</th><th>File</th><th>Size</th><th>Trigger</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($backupsCollection as $b)
                <tr>
                    <td>{{ $b->created_at->format('d M Y H:i') }}</td>
                    <td><code style="font-size:0.72rem;">{{ $b->filename }}</code></td>
                    <td>{{ $b->file_size_human }}</td>
                    <td><span class="badge bg-secondary" style="font-size:0.7rem;">{{ $b->trigger }}</span></td>
                    <td>
                        <span class="badge {{ $b->status === 'completed' ? 'bg-success' : ($b->status === 'failed' ? 'bg-danger' : 'bg-secondary') }}" style="font-size:0.7rem;">
                            {{ ucfirst($b->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            @if($b->status === 'completed')
                                <a href="{{ route('owner.backups.download', $b) }}" class="btn btn-sm btn-outline-primary" title="Download SQL">
                                    <i class="fas fa-download"></i> SQL
                                </a>
                                <a href="{{ route('owner.backups.csv', $b) }}" class="btn btn-sm btn-outline-success" title="Export CSV/Excel ZIP">
                                    <i class="fas fa-file-excel"></i> CSV
                                </a>
                                <form method="POST" action="{{ route('owner.backups.restore', $b) }}" onsubmit="return confirm('Restore? This will OVERWRITE all current data.')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning" title="Restore"><i class="fas fa-undo"></i></button>
                                </form>
                                <form method="POST" action="{{ route('owner.backups.email', $b) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Email to School"><i class="fas fa-envelope"></i></button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('owner.backups.destroy', $b) }}" onsubmit="return confirm('Delete this backup?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No backups yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Setup Tab --}}
    <div class="tab-pane fade {{ session('active_tab') === 'setup' ? 'show active' : '' }}" id="tab-setup">

        {{-- Alerts --}}
        @if(session('setup_success'))
        <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('setup_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('setup_error'))
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px;" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('setup_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row g-4">

            {{-- Step 1: Run Migrations --}}
            <div class="col-md-6">
                <div class="owner-card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <span class="badge bg-primary">Step 1</span>
                        Run Database Migrations
                    </div>
                    <div class="card-body">
                        <p style="font-size:0.875rem;color:#64748b;">
                            Connects to <strong>{{ $tenant->name }}</strong>'s database and creates all tables + adds any missing columns. <strong>Run this any time a student/staff creation fails with a "column does not exist" error.</strong>
                        </p>
                        <div style="background:#f8fafc;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.8rem;">
                            <div style="color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;font-size:0.7rem;margin-bottom:0.25rem;">Target Database</div>
                            <code>{{ $tenant->db_host }}:{{ $tenant->db_port }} / {{ $tenant->db_name }}</code>
                        </div>
                        <form method="POST" action="{{ route('owner.schools.setup.migrate', $tenant) }}"
                              onsubmit="return confirm('Run migrations on {{ $tenant->name }}\'s database? This is safe to run multiple times.')">
                            @csrf
                            <button type="submit" class="btn btn-owner-primary w-100">
                                <i class="fas fa-database me-2"></i> Run Migrations &amp; Sync All Columns
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Step 3: Seed Test Data --}}
            <div class="col-md-12">
                <div class="owner-card">
                    <div class="card-header d-flex align-items-center gap-2">
                        <span class="badge bg-info">Step 3</span>
                        <i class="fas fa-seedling text-success"></i>
                        Seed Test Data (5 Students + 2 Teachers)
                    </div>
                    <div class="card-body">
                        <p style="font-size:0.875rem;color:#64748b;">
                            Creates 5 students with full records (exams, SBA scores, attendance, fees, payments) and 2 teachers with course assignments into <strong>{{ $tenant->name }}</strong>'s database. Safe to run multiple times — skips existing records.
                        </p>
                        <a href="{{ route('owner.schools.seed-test-data', $tenant) }}"
                           class="btn btn-success w-100"
                           onclick="return confirm('Seed test data into {{ $tenant->name }}\'s database? This will create 5 students, 2 teachers, exams, attendance, and fee records.')">
                            <i class="fas fa-seedling me-2"></i> Seed Test Data Now
                        </a>
                    </div>
                </div>
            </div>

            {{-- Step 2: Create Admin --}}
            <div class="col-md-6">
                <div class="owner-card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <span class="badge bg-success">Step 2</span>
                        Create Initial Super Admin
                    </div>
                    <div class="card-body">
                        <p style="font-size:0.875rem;color:#64748b;">
                            Creates the first super admin account for <strong>{{ $tenant->name }}</strong>. Run Step 1 first if the database is brand new.
                        </p>
                        <form method="POST" action="{{ route('owner.schools.setup.create-admin', $tenant) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label fw-semibold" style="font-size:0.85rem;">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="admin_full_name" class="form-control form-control-sm" placeholder="e.g. John Mensah" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-semibold" style="font-size:0.85rem;">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="admin_email" class="form-control form-control-sm" placeholder="admin@school.com" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-semibold" style="font-size:0.85rem;">Branch</label>
                                <select name="church_branch_id" class="form-select form-select-sm" id="createAdminBranch">
                                    <option value="">— No Branch (Global Super Admin) —</option>
                                </select>
                                <div class="form-text" style="font-size:0.78rem;">Leave empty for global access across all branches.</div>
                            </div>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_super_admin" value="1" id="createIsSuperAdmin" checked>
                                    <label class="form-check-label fw-semibold" for="createIsSuperAdmin" style="font-size:0.85rem;">Super Admin</label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-semibold" style="font-size:0.85rem;">Password <span class="text-danger">*</span></label>
                                <input type="password" name="admin_password" class="form-control form-control-sm" placeholder="Min 8 characters" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:0.85rem;">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="admin_password_confirmation" class="form-control form-control-sm" placeholder="Repeat password" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-user-plus me-2"></i> Create Admin
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Existing Admins --}}
            <div class="col-12">
                <div class="owner-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-users me-2"></i> Existing Admins in this School</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadAdmins()">
                            <i class="fas fa-sync me-1"></i> Load Admins
                        </button>
                    </div>
                    <div class="card-body p-0" id="adminsTableWrap">
                        <div class="text-center text-muted py-4" style="font-size:0.875rem;">
                            Click "Load Admins" to fetch the list from the school's database.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reset Password --}}
            <div class="col-md-6">
                <div class="owner-card">
                    <div class="card-header d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark">Tool</span>
                        Reset Admin Password
                    </div>
                    <div class="card-body">
                        <p style="font-size:0.875rem;color:#64748b;">Reset the password for any existing admin in this school's database.</p>
                        <form method="POST" action="{{ route('owner.schools.setup.reset-password', $tenant) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label fw-semibold" style="font-size:0.85rem;">Admin Email <span class="text-danger">*</span></label>
                                <input type="email" name="reset_email" class="form-control form-control-sm"
                                    placeholder="admin@school.com" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-semibold" style="font-size:0.85rem;">New Password <span class="text-danger">*</span></label>
                                <input type="password" name="reset_password" class="form-control form-control-sm"
                                    placeholder="Min 8 characters" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:0.85rem;">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" name="reset_password_confirmation" class="form-control form-control-sm"
                                    placeholder="Repeat password" required>
                            </div>
                            <button type="submit" class="btn btn-warning w-100"
                                onclick="return confirm('Reset this admin\'s password?')">
                                <i class="fas fa-key me-2"></i> Reset Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Login URL --}}
            <div class="col-md-6">
                <div class="owner-card">
                    <div class="card-header"><i class="fas fa-link me-2"></i> School Login URL</div>
                    <div class="card-body">
                        <p style="font-size:0.875rem;color:#64748b;">Send this URL to the school's admin so they can log in.</p>
                        <div style="background:#f1f5f9;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1rem;">
                            <code id="loginUrlSetup" style="font-size:0.875rem;word-break:break-all;">{{ url('/school/' . $tenant->slug . '/login') }}</code>
                        </div>
                        <button onclick="navigator.clipboard.writeText(document.getElementById('loginUrlSetup').innerText.trim()).then(()=>alert('Copied!'))"
                            class="btn btn-outline-primary w-100">
                            <i class="fas fa-copy me-2"></i> Copy Login URL
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Info Tab --}}
    <div class="tab-pane fade" id="tab-info">
        <div class="owner-card">
            <div class="card-body">
                <div class="row g-3">
                    @foreach([
                        'Admin Name'         => $tenant->admin_name ?? '—',
                        'Admin Email'        => $tenant->admin_email ?? '—',
                        'Admin Phone'        => $tenant->admin_phone ?? '—',
                        'School Phone'       => $tenant->phone ?? '—',
                        'Address'            => $tenant->address ?? '—',
                        'Plan'               => optional($tenant->relationLoaded('plan') ? $tenant->plan : null)->name ?? '—',
                        'Custom Domain'      => $tenant->custom_domain ?? 'Not set (using slug URL)',
                        'DB Host'            => ($tenant->db_host ?? '—') . ':' . ($tenant->db_port ?? '5432'),
                        'DB Name'            => $tenant->db_name ?? '—',
                        'DB Username'        => $tenant->db_username ?? '—',
                        'Backup Frequency'   => ucfirst($tenant->backup_frequency ?? '—'),
                        'Trial Ends'         => $tenant->trial_ends_at?->format('d M Y') ?? '—',
                        'Subscription Ends'  => $tenant->subscription_ends_at?->format('d M Y') ?? '—',
                        'Cloudinary'         => $tenant->cloudinary_cloud_name ? '✅ ' . $tenant->cloudinary_cloud_name : '❌ Not configured',
                        'Backblaze B2'       => $tenant->b2_bucket_name ? '✅ ' . $tenant->b2_bucket_name : '❌ Not configured',
                        'Created'            => $tenant->created_at->format('d M Y'),
                        'Notes'              => $tenant->notes ?? '—',
                    ] as $label => $value)
                    <div class="col-md-6">
                        <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">{{ $label }}</div>
                        <div style="font-size:0.9rem;font-weight:500;word-break:break-word;">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Custom Domain DNS Instructions --}}
                @if($tenant->custom_domain)
                <div class="mt-4 p-4 rounded-xl" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                    <h6 class="fw-bold text-success mb-3">
                        <i class="fas fa-globe me-2"></i> Custom Domain Setup — {{ $tenant->custom_domain }}
                    </h6>
                    <div style="font-size:0.85rem;color:#475569;line-height:1.6;">
                        <p class="mb-2"><strong>Step 1:</strong> The school needs to add a DNS record with their domain registrar:</p>
                        <div class="bg-white rounded-lg p-3 mb-3 border" style="font-family:monospace;font-size:0.8rem;">
                            <span class="text-muted">Type:</span> CNAME<br>
                            <span class="text-muted">Name/Host:</span> @ (or www)<br>
                            <span class="text-muted">Value/Target:</span> {{ config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'kmc-college-v136.onrender.com' }}
                        </div>
                        <p class="mb-2"><strong>Step 2:</strong> In Render dashboard, add the custom domain {{ $tenant->custom_domain }} to this web service's Custom Domains section.</p>
                        <p class="mb-2"><strong>Step 3:</strong> Wait for DNS to propagate (can take up to 48 hours, usually 15 minutes).</p>
                        <p class="mb-2"><strong>Step 4:</strong> Once live, visiting <code>{{ $tenant->custom_domain }}</code> will automatically load this school's website.</p>
                    </div>
                    <div class="mt-2">
                        <a href="http://{{ $tenant->custom_domain }}" target="_blank" class="btn btn-sm btn-success">
                            <i class="fas fa-external-link-alt me-1"></i> Test Domain
                        </a>
                    </div>
                </div>
                @else
                <div class="mt-4 p-3 rounded-xl" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <p style="font-size:0.8rem;color:#64748b;margin:0;">
                        <i class="fas fa-info-circle me-1 text-info"></i>
                        To give this school its own domain (e.g. <code>saningsschoolacademy.com</code>),
                        edit the school and set the Custom Domain field. The school must point a CNAME to this server.
                    </p>
                </div>
                @endif

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const TENANT_ID   = {{ $tenant->id }};
const CSRF        = '{{ csrf_token() }}';
const ADMINS_URL  = '{{ route('owner.schools.setup.admins',   $tenant) }}';
const BRANCHES_URL= '{{ route('owner.schools.setup.branches', $tenant) }}';
const DELETE_BASE = '/owner/schools/' + TENANT_ID + '/setup/admins/';
const UPDATE_BASE = '/owner/schools/' + TENANT_ID + '/setup/admins/';

// ── Module toggle helpers ────────────────────────────────────────────────────
function toggleAll(state) {
    document.querySelectorAll('.feat-check').forEach(cb => {
        cb.checked = state;
        const label = cb.closest('.feat-label');
        if (label) {
            label.style.background = state ? '#f0fdf4' : '#f8fafc';
            label.style.borderColor = state ? '#dcfce7' : '#f1f5f9';
        }
    });
}

document.querySelectorAll('.feat-check').forEach(cb => {
    cb.addEventListener('change', function () {
        const label = this.closest('.feat-label');
        if (label) {
            label.style.background  = this.checked ? '#f0fdf4' : '#f8fafc';
            label.style.borderColor = this.checked ? '#dcfce7' : '#f1f5f9';
        }
    });
});

// ── Theme live preview ───────────────────────────────────────────────────────
const colorInputs = {
    adminPrimary: { el: null, targets: ['prev_logo', 'prev_active_item', 'prev_badge'] },
    adminNav:     { el: null, targets: ['prev_sidebar', 'prev_nav'] },
    primary:      { el: null, targets: ['prev_btn'] },
    accent:       { el: null, targets: [] },
};

function syncColor(textInput, key) {
    const val = textInput.value.trim();
    const hidden = document.getElementById('inp_' + key);
    if (hidden) hidden.value = val;
    // sync the color picker on the same row
    const colorPicker = textInput.closest('.d-flex')?.querySelector('input[type="color"]');
    if (colorPicker && /^#[0-9A-Fa-f]{6}$/.test(val)) {
        colorPicker.value = val;
        updatePreview(key, val);
    }
}

function updatePreview(key, val) {
    if (key === 'adminNav') {
        const sidebar = document.getElementById('prev_sidebar');
        const nav     = document.getElementById('prev_nav');
        if (sidebar) sidebar.style.background = val;
        if (nav)     nav.style.background     = val;
    }
    if (key === 'adminPrimary') {
        const logo = document.getElementById('prev_logo');
        if (logo) logo.style.background = val;
        const activeItem = document.getElementById('prev_active_item');
        if (activeItem) {
            activeItem.style.borderLeftColor = val;
            activeItem.style.background = val + '22';
        }
        const badge = document.getElementById('prev_badge');
        if (badge) {
            badge.style.color = val;
            badge.style.background = val + '22';
        }
    }
    if (key === 'primary') {
        const btn = document.getElementById('prev_btn');
        if (btn) btn.style.background = val;
    }
}

// Wire color pickers
document.querySelectorAll('input[type="color"]').forEach(picker => {
    picker.addEventListener('input', function () {
        const row    = this.closest('.d-flex');
        const text   = row?.querySelector('input[type="text"]');
        const name   = this.getAttribute('name');
        // Determine key from the name (strip _color suffix)
        const key = name?.replace('_color', '').replace('_', '');
        if (text) text.value = this.value;
        // find the hidden input
        const keyMap = { adminPrimary: 'adminPrimary', adminNav_color: 'adminNav', primary_color: 'primary', accent_color: 'accent' };
        const hKey = keyMap[name] || name;
        const hidden = document.getElementById('inp_' + hKey);
        if (hidden) hidden.value = this.value;
        // update preview
        const previewKey = hKey === 'adminNav_color' ? 'adminNav' : hKey === 'primary_color' ? 'primary' : hKey === 'accent_color' ? 'accent' : hKey;
        updatePreview(previewKey, this.value);
    });
});


let branchCache = null;

function loadBranches(callback) {
    if (branchCache) { callback(branchCache); return; }
    fetch(BRANCHES_URL)
        .then(r => r.json())
        .then(b => { branchCache = b; callback(b); })
        .catch(() => callback([]));
}

function populateBranchSelect(selectEl, selectedId) {
    loadBranches(branches => {
        selectEl.innerHTML = '<option value="">— No Branch (Global) —</option>';
        branches.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.id;
            opt.textContent = b.name;
            if (selectedId && parseInt(b.id) === parseInt(selectedId)) opt.selected = true;
            selectEl.appendChild(opt);
        });
    });
}

function loadAdmins() {
    const wrap = document.getElementById('adminsTableWrap');
    wrap.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary"></div> Loading...</div>';

    fetch(ADMINS_URL)
        .then(r => {
            if (!r.ok) return r.text().then(t => { throw new Error('Server error: ' + t.substring(0, 200)); });
            return r.json();
        })
        .then(admins => {
            if (admins.error) {
                wrap.innerHTML = '<div class="text-center text-danger py-4" style="font-size:0.875rem;">Error: ' + admins.message + '</div>';
                return;
            }
            if (!admins.length) {
                wrap.innerHTML = '<div class="text-center text-muted py-4" style="font-size:0.875rem;">No admins found. Run migrations first, then create an admin using Step 2.</div>';
                return;
            }
            let html = `<table class="table mb-0">
                <thead><tr>
                    <th>Name</th><th>Email</th><th>Role</th><th>Branch</th><th>Created</th><th>Actions</th>
                </tr></thead><tbody>`;
            admins.forEach(a => {
                const branchLabel = a.branch_name
                    ? `<span class="badge bg-light text-dark border" style="font-size:0.72rem;">${a.branch_name}</span>`
                    : '<span style="font-size:0.75rem;color:#94a3b8;">Global</span>';
                html += `<tr id="admin-row-${a.id}">
                    <td style="font-size:0.875rem;font-weight:500;">${a.full_name}</td>
                    <td style="font-size:0.875rem;">${a.email}</td>
                    <td><span class="badge ${a.is_super_admin ? 'bg-danger' : 'bg-secondary'}" style="font-size:0.72rem;">
                        ${a.is_super_admin ? 'Super Admin' : 'Admin'}
                    </span></td>
                    <td>${branchLabel}</td>
                    <td style="font-size:0.8rem;color:#94a3b8;">${a.created_at ? a.created_at.substring(0,10) : '—'}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-warning"
                                onclick="openEditModal(${a.id},'${a.full_name.replace(/'/g,"\\'")}','${a.email}',${a.is_super_admin ? 1 : 0},${a.church_branch_id || 0})"
                                title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger"
                                onclick="deleteAdmin(${a.id},'${a.full_name.replace(/'/g,"\\'")}',${a.is_super_admin ? 1 : 0})"
                                title="Delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            wrap.innerHTML = html;
        })
        .catch(e => {
            wrap.innerHTML = '<div class="text-center text-danger py-4" style="font-size:0.875rem;">Failed: ' + e.message + '</div>';
        });
}

function deleteAdmin(userId, name, isSuperAdmin) {
    if (!confirm(`Delete admin "${name}"? This cannot be undone.`)) return;
    fetch(DELETE_BASE + userId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId })
    })
    .then(r => r.json())
    .then(res => {
        if (res.error) { alert('Error: ' + res.error); return; }
        document.getElementById('admin-row-' + userId)?.remove();
        showSetupAlert('success', res.message || 'Admin deleted.');
    })
    .catch(e => alert('Delete failed: ' + e.message));
}

function openEditModal(userId, fullName, email, isSuperAdmin, branchId) {
    document.getElementById('editAdminId').value       = userId;
    document.getElementById('editFullName').value      = fullName;
    document.getElementById('editEmail').value         = email;
    document.getElementById('editSuperAdmin').checked  = !!isSuperAdmin;
    document.getElementById('editPassword').value      = '';

    // Populate branch dropdown then select current
    const branchSel = document.getElementById('editBranchId');
    populateBranchSelect(branchSel, branchId);

    const modal = new bootstrap.Modal(document.getElementById('editAdminModal'));
    modal.show();
}

function submitEditAdmin() {
    const userId       = document.getElementById('editAdminId').value;
    const fullName     = document.getElementById('editFullName').value;
    const email        = document.getElementById('editEmail').value;
    const isSuperAdmin = document.getElementById('editSuperAdmin').checked;
    const newPassword  = document.getElementById('editPassword').value;
    const branchId     = document.getElementById('editBranchId').value;

    fetch(UPDATE_BASE + userId, {
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({
            user_id:          userId,
            full_name:        fullName,
            email:            email,
            is_super_admin:   isSuperAdmin,
            new_password:     newPassword || null,
            church_branch_id: branchId    || null,
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.error) { alert('Error: ' + res.error); return; }
        bootstrap.Modal.getInstance(document.getElementById('editAdminModal')).hide();
        showSetupAlert('success', res.message || 'Admin updated.');
        loadAdmins();
    })
    .catch(e => alert('Update failed: ' + e.message));
}

function showSetupAlert(type, message) {
    const div = document.createElement('div');
    div.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    div.style.cssText = 'border-radius:10px;margin-bottom:1rem;';
    div.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    const setupTab = document.getElementById('tab-setup');
    if (setupTab) setupTab.insertBefore(div, setupTab.firstChild);
    setTimeout(() => div.remove(), 5000);
}

// Pre-load branches when Setup tab is clicked
document.addEventListener('DOMContentLoaded', function () {
    // Load branches immediately so they're ready when the tab opens
    loadBranches(function(branches) {
        populateBranchSelect(document.getElementById('createAdminBranch'), null);
    });

    // Also reload on tab click in case it wasn't loaded yet
    document.querySelectorAll('a[href="#tab-setup"]').forEach(el => {
        el.addEventListener('click', function () {
            setTimeout(() => {
                populateBranchSelect(document.getElementById('createAdminBranch'), null);
            }, 100);
        });
    });
});
</script>

{{-- Edit Admin Modal --}}
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Admin</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editAdminId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" id="editFullName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" id="editEmail" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Branch</label>
                    <select id="editBranchId" class="form-select">
                        <option value="">— No Branch (Global) —</option>
                    </select>
                    <div class="form-text">Assign to a specific branch, or leave empty for global access.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                    <input type="password" id="editPassword" class="form-control" placeholder="Min 8 characters" minlength="8">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="editSuperAdmin">
                    <label class="form-check-label fw-semibold" for="editSuperAdmin">Super Admin</label>
                    <div class="form-text">Super admins have full access including creating other admins.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-owner-primary" onclick="submitEditAdmin()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endpush
