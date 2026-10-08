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
            <code id="schoolUrl" style="background:#f1f5f9;padding:0.4rem 0.75rem;border-radius:8px;font-size:0.875rem;flex:1;word-break:break-all;">{{ url('/school/' . $tenant->slug . '/login') }}</code>
            <button onclick="navigator.clipboard.writeText(document.getElementById('schoolUrl').innerText).then(()=>alert('Copied!'))" class="btn btn-sm btn-outline-primary"><i class="fas fa-copy"></i> Copy</button>
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
                            <code>{{ $tenant->subdomain }}</code> ·
                            {{ $tenant->admin_email }} ·
                            {{ $tenant->plan->name ?? 'No Plan' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <span class="badge badge-{{ $tenant->status }} fs-6 px-3 py-2">{{ ucfirst($tenant->status) }}</span>

                {{-- Quick status change --}}
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
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-value">GHS {{ number_format($totalPaid, 2) }}</div>
            <div class="stat-label">Total Paid</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-value">{{ $tenant->payments->count() }}</div>
            <div class="stat-label">Payments</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-value">{{ $tenant->backups->count() }}</div>
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
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-features">Features</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-payments">Payments</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-backups">Backups</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-info">Info</a></li>
</ul>

<div class="tab-content">

    {{-- Features Tab --}}
    <div class="tab-pane fade show active" id="tab-features">
        <div class="owner-card">
            <div class="card-header">Feature Overrides for {{ $tenant->name }}</div>
            <div class="card-body">
                <p style="font-size:0.85rem;color:#64748b;">Check features to <strong>enable</strong> them for this school regardless of plan. Uncheck to <strong>disable</strong>.</p>
                <form method="POST" action="{{ route('owner.schools.features', $tenant) }}">
                    @csrf
                    @foreach($features as $group => $groupFeatures)
                    <div class="mb-4">
                        <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;font-weight:700;margin-bottom:0.75rem;">{{ ucfirst($group) }}</div>
                        <div class="row g-2">
                            @foreach($groupFeatures as $feature)
                            <div class="col-md-4 col-sm-6">
                                <div class="form-check d-flex align-items-center gap-2 p-2 rounded" style="background:#f8fafc;">
                                    <input class="form-check-input" type="checkbox"
                                        name="features[]"
                                        value="{{ $feature->id }}"
                                        id="feat_{{ $feature->id }}"
                                        {{ (isset($tenantOverrides[$feature->id]) ? $tenantOverrides[$feature->id] : in_array($feature->id, $planFeatureIds)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="feat_{{ $feature->id }}" style="font-size:0.875rem;cursor:pointer;">
                                        {{ $feature->label }}
                                        @if(in_array($feature->id, $planFeatureIds))
                                            <small class="text-success ms-1" title="Included in plan"><i class="fas fa-layer-group"></i></small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    <button type="submit" class="btn btn-owner-primary mt-2"><i class="fas fa-save me-1"></i> Save Feature Overrides</button>
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
                <thead><tr><th>Receipt #</th><th>Date</th><th>Amount</th><th>Method</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($tenant->payments as $p)
                <tr>
                    <td><code style="font-size:0.8rem;">{{ $p->receipt_number }}</code></td>
                    <td>{{ $p->payment_date->format('d M Y') }}</td>
                    <td><strong style="color:#16a34a;">GHS {{ number_format($p->amount, 2) }}</strong></td>
                    <td>{{ ucfirst(str_replace('_',' ',$p->payment_method)) }}</td>
                    <td>{{ $p->description ?? '—' }}</td>
                    <td>
                        <a href="{{ route('owner.payments.receipt', $p) }}" class="btn btn-sm btn-outline-secondary" title="Download Receipt"><i class="fas fa-file-pdf"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No payments recorded</td></tr>
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
                <thead><tr><th>Date</th><th>File</th><th>Size</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($tenant->backups as $b)
                <tr>
                    <td>{{ $b->created_at->format('d M Y H:i') }}</td>
                    <td><code style="font-size:0.75rem;">{{ $b->filename }}</code></td>
                    <td>{{ $b->file_size_human }}</td>
                    <td><span class="badge bg-secondary">{{ $b->trigger }}</span></td>
                    <td>
                        <span class="badge {{ $b->status === 'completed' ? 'bg-success' : ($b->status === 'failed' ? 'bg-danger' : 'bg-secondary') }}">
                            {{ ucfirst($b->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($b->status === 'completed')
                            <a href="{{ route('owner.backups.download', $b) }}" class="btn btn-sm btn-outline-primary" title="Download"><i class="fas fa-download"></i></a>
                            <form method="POST" action="{{ route('owner.backups.restore', $b) }}" onsubmit="return confirm('Restore this backup? This will OVERWRITE all current data for {{ $tenant->name }}.')">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning" title="Restore"><i class="fas fa-undo"></i></button>
                            </form>
                            <form method="POST" action="{{ route('owner.backups.email', $b) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" title="Email to School"><i class="fas fa-envelope"></i></button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('owner.backups.destroy', $b) }}" onsubmit="return confirm('Delete this backup file?')">
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

    {{-- Info Tab --}}
    <div class="tab-pane fade" id="tab-info">
        <div class="owner-card">
            <div class="card-body">
                <div class="row g-3">
                    @foreach([
                        'Admin Name'    => $tenant->admin_name,
                        'Admin Email'   => $tenant->admin_email,
                        'Admin Phone'   => $tenant->admin_phone ?? '—',
                        'Phone'         => $tenant->phone ?? '—',
                        'Address'       => $tenant->address ?? '—',
                        'DB Host'       => $tenant->db_host . ':' . $tenant->db_port,
                        'DB Name'       => $tenant->db_name,
                        'DB Username'   => $tenant->db_username,
                        'Created'       => $tenant->created_at->format('d M Y'),
                        'Notes'         => $tenant->notes ?? '—',
                    ] as $label => $value)
                    <div class="col-md-6">
                        <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">{{ $label }}</div>
                        <div style="font-size:0.9rem;font-weight:500;">{{ $value }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
