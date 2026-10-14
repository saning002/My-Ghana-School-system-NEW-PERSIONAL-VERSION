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
    $paymentsCollection = $paymentsCollection ?? collect();
    $backupsCollection  = $backupsCollection  ?? collect();
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
            <div class="stat-value">{{ $paymentsCollection->count() }}</div>
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
    <li class="nav-item"><a class="nav-link {{ session('active_tab') === 'setup' ? '' : 'active' }}" data-bs-toggle="tab" href="#tab-features">Features</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-payments">Payments</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-backups">Backups</a></li>
    <li class="nav-item"><a class="nav-link {{ session('active_tab') === 'setup' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-setup">
        <i class="fas fa-tools me-1"></i> Setup
    </a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-info">Info</a></li>
</ul>

<div class="tab-content">

    {{-- Features Tab --}}
    <div class="tab-pane fade show active" id="tab-features">
        <div class="owner-card">
            <div class="card-header">Feature Overrides for {{ $tenant->name }}</div>
            <div class="card-body">
                <p style="font-size:0.85rem;color:#64748b;">
                    Check to <strong>enable</strong> a feature for this school. Uncheck to <strong>disable</strong>.
                    <span class="text-success"><i class="fas fa-layer-group"></i></span> = included in their plan.
                </p>
                <form method="POST" action="{{ route('owner.schools.features', $tenant) }}">
                    @csrf
                    @forelse($features as $group => $groupFeatures)
                    <div class="mb-4">
                        <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;font-weight:700;margin-bottom:0.75rem;">
                            {{ ucfirst($group) }}
                        </div>
                        <div class="row g-2">
                            @foreach($groupFeatures as $feature)
                            @php
                                $isEnabled = isset($tenantOverrides[$feature->id])
                                    ? (bool) $tenantOverrides[$feature->id]
                                    : in_array($feature->id, $planFeatureIds);
                            @endphp
                            <div class="col-md-4 col-sm-6">
                                <div class="form-check d-flex align-items-center gap-2 p-2 rounded" style="background:#f8fafc;">
                                    <input class="form-check-input" type="checkbox"
                                        name="features[]"
                                        value="{{ $feature->id }}"
                                        id="feat_{{ $feature->id }}"
                                        {{ $isEnabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="feat_{{ $feature->id }}" style="font-size:0.875rem;cursor:pointer;">
                                        {{ $feature->label }}
                                        @if(in_array($feature->id, $planFeatureIds))
                                            <small class="text-success ms-1" title="In plan"><i class="fas fa-layer-group"></i></small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <p class="text-muted">No features defined yet. <a href="{{ route('owner.features.index') }}">Add features</a>.</p>
                    @endforelse
                    @if($features->count())
                    <button type="submit" class="btn btn-owner-primary mt-2">
                        <i class="fas fa-save me-1"></i> Save Feature Overrides
                    </button>
                    @endif
                </form>
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
        <div class="owner-table">
            <table class="table">
                <thead>
                    <tr><th>Receipt #</th><th>Date</th><th>Amount</th><th>Method</th><th>Description</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($paymentsCollection as $p)
                <tr>
                    <td><code style="font-size:0.8rem;">{{ $p->receipt_number }}</code></td>
                    <td>{{ $p->payment_date ? $p->payment_date->format('d M Y') : '—' }}</td>
                    <td><strong style="color:#16a34a;">GHS {{ number_format($p->amount, 2) }}</strong></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</td>
                    <td>{{ $p->description ?? '—' }}</td>
                    <td>
                        <a href="{{ route('owner.payments.receipt', $p) }}" class="btn btn-sm btn-outline-secondary" title="Receipt">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No payments recorded yet</td></tr>
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
                                <a href="{{ route('owner.backups.download', $b) }}" class="btn btn-sm btn-outline-primary" title="Download"><i class="fas fa-download"></i></a>
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
                            Connects to <strong>{{ $tenant->name }}</strong>'s database and runs all Laravel migrations to set up the school's tables. Also seeds default settings.
                        </p>
                        <div style="background:#f8fafc;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.8rem;">
                            <div style="color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;font-size:0.7rem;margin-bottom:0.25rem;">Target Database</div>
                            <code>{{ $tenant->db_host }}:{{ $tenant->db_port }} / {{ $tenant->db_name }}</code>
                        </div>
                        <form method="POST" action="{{ route('owner.schools.setup.migrate', $tenant) }}"
                              onsubmit="return confirm('Run migrations on {{ $tenant->name }}\'s database? This is safe to run multiple times.')">
                            @csrf
                            <button type="submit" class="btn btn-owner-primary w-100">
                                <i class="fas fa-database me-2"></i> Run Migrations & Seed Settings
                            </button>
                        </form>
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
                        'DB Host'            => ($tenant->db_host ?? '—') . ':' . ($tenant->db_port ?? '5432'),
                        'DB Name'            => $tenant->db_name ?? '—',
                        'DB Username'        => $tenant->db_username ?? '—',
                        'Backup Frequency'   => ucfirst($tenant->backup_frequency ?? '—'),
                        'Trial Ends'         => $tenant->trial_ends_at?->format('d M Y') ?? '—',
                        'Subscription Ends'  => $tenant->subscription_ends_at?->format('d M Y') ?? '—',
                        'Created'            => $tenant->created_at->format('d M Y'),
                        'Notes'              => $tenant->notes ?? '—',
                    ] as $label => $value)
                    <div class="col-md-6">
                        <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">{{ $label }}</div>
                        <div style="font-size:0.9rem;font-weight:500;word-break:break-word;">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>
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
    document.querySelector('a[href="#tab-setup"]')?.addEventListener('shown.bs.tab', function () {
        populateBranchSelect(document.getElementById('createAdminBranch'), null);
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
