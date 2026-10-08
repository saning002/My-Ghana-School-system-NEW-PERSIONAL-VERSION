@extends('owner.layout')
@section('title','Backups')
@section('page-title','Database Backups')

@section('content')

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-value">{{ $backups->total() }}</div>
            <div class="stat-label">Total Backups</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($totalSize / 1024 / 1024, 1) }} MB</div>
            <div class="stat-label">Total Storage Used</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-value">{{ $backups->where('status','failed')->count() }}</div>
            <div class="stat-label">Failed Backups</div>
        </div>
    </div>
</div>

{{-- Quick backup by school --}}
<div class="owner-card mb-4">
    <div class="card-header">Quick Backup</div>
    <div class="card-body">
        <form method="POST" id="quickBackupForm" class="d-flex gap-2 align-items-center flex-wrap">
            @csrf
            <select id="quickBackupSchool" class="form-select form-select-sm" style="max-width:280px;">
                <option value="">— Select School —</option>
                @foreach($schools as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm btn-owner-primary" onclick="triggerBackup()">
                <i class="fas fa-database me-1"></i> Backup Now
            </button>
            <small class="text-muted">Runs mysqldump, compresses to .sql.gz and saves to local storage.</small>
        </form>
    </div>
</div>

{{-- Filters --}}
<div class="owner-card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <select name="tenant_id" class="form-select form-select-sm" style="max-width:220px;">
                <option value="">All Schools</option>
                @foreach($schools as $s)
                    <option value="{{ $s->id }}" {{ request('tenant_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" style="max-width:140px;">
                <option value="">All Statuses</option>
                @foreach(['pending','completed','failed'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-secondary">Filter</button>
            <a href="{{ route('owner.backups.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="owner-table">
    <table class="table">
        <thead>
            <tr>
                <th>School</th>
                <th>Filename</th>
                <th>Size</th>
                <th>Trigger</th>
                <th>Storage</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($backups as $b)
        <tr>
            <td>
                <a href="{{ route('owner.schools.show', $b->tenant_id) }}" style="font-weight:600;color:#1e293b;text-decoration:none;font-size:0.875rem;">
                    {{ $b->tenant->name ?? '—' }}
                </a>
            </td>
            <td><code style="font-size:0.72rem;word-break:break-all;">{{ $b->filename }}</code></td>
            <td><small>{{ $b->file_size_human }}</small></td>
            <td><span class="badge bg-light text-dark border" style="font-size:0.72rem;">{{ $b->trigger }}</span></td>
            <td>
                <span class="badge {{ $b->cloud_synced ? 'bg-info' : 'bg-secondary' }}" style="font-size:0.72rem;">
                    {{ $b->cloud_synced ? 'S3' : 'Local' }}
                </span>
            </td>
            <td>
                <span class="badge {{ $b->status === 'completed' ? 'bg-success' : ($b->status === 'failed' ? 'bg-danger' : 'bg-secondary') }}" style="font-size:0.72rem;">
                    {{ ucfirst($b->status) }}
                </span>
                @if($b->error_message)
                    <div style="font-size:0.7rem;color:#ef4444;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $b->error_message }}">{{ $b->error_message }}</div>
                @endif
            </td>
            <td><small class="text-muted">{{ $b->created_at->format('d M Y H:i') }}</small></td>
            <td>
                <div class="d-flex gap-1 flex-wrap">
                    @if($b->status === 'completed')
                        <a href="{{ route('owner.backups.download', $b) }}" class="btn btn-xs btn-outline-primary" title="Download" style="padding:0.2rem 0.5rem;font-size:0.75rem;"><i class="fas fa-download"></i></a>
                        @if(!$b->cloud_synced)
                        <form method="POST" action="{{ route('owner.backups.cloud-push', $b) }}">
                            @csrf
                            <button class="btn btn-xs btn-outline-info" title="Push to S3" style="padding:0.2rem 0.5rem;font-size:0.75rem;"><i class="fas fa-cloud-upload-alt"></i></button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('owner.backups.email', $b) }}">
                            @csrf
                            <button class="btn btn-xs btn-outline-secondary" title="Email to School" style="padding:0.2rem 0.5rem;font-size:0.75rem;"><i class="fas fa-envelope"></i></button>
                        </form>
                        <form method="POST" action="{{ route('owner.backups.restore', $b) }}" onsubmit="return confirm('⚠️ Restore will OVERWRITE all current data for {{ $b->tenant->name ?? 'this school' }}. A pre-restore backup will be taken first. Continue?')">
                            @csrf
                            <button class="btn btn-xs btn-outline-warning" title="Restore DB" style="padding:0.2rem 0.5rem;font-size:0.75rem;"><i class="fas fa-undo"></i></button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('owner.backups.destroy', $b) }}" onsubmit="return confirm('Delete this backup file?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-outline-danger" title="Delete" style="padding:0.2rem 0.5rem;font-size:0.75rem;"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted py-4">No backups found.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($backups->hasPages())
    <div class="px-3 py-2">{{ $backups->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function triggerBackup() {
    const tenantId = document.getElementById('quickBackupSchool').value;
    if (!tenantId) { alert('Please select a school first.'); return; }
    if (!confirm('Start backup now for selected school?')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/owner/backups/' + tenantId;
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
