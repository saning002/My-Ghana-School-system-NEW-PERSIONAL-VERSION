@extends('owner.layout')
@section('title','Audit Log')
@section('page-title','Audit Log')

@section('content')
{{-- Filters --}}
<div class="owner-card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <select name="tenant_id" class="form-select form-select-sm" style="max-width:200px;">
                <option value="">All Schools</option>
                @foreach($schools as $s)
                    <option value="{{ $s->id }}" {{ request('tenant_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <input type="text" name="action" class="form-control form-control-sm" style="max-width:200px;" placeholder="Filter by action..." value="{{ request('action') }}">
            <input type="date" name="from" class="form-control form-control-sm" style="max-width:150px;" value="{{ request('from') }}">
            <input type="date" name="to" class="form-control form-control-sm" style="max-width:150px;" value="{{ request('to') }}">
            <button class="btn btn-sm btn-secondary">Filter</button>
            <a href="{{ route('owner.audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="owner-table">
    <table class="table">
        <thead>
            <tr><th>Time</th><th>Action</th><th>School</th><th>Description</th><th>IP</th></tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
        <tr>
            <td>
                <div style="font-size:0.8rem;color:#1e293b;font-weight:500;">{{ $log->created_at->format('d M Y') }}</div>
                <div style="font-size:0.72rem;color:#94a3b8;">{{ $log->created_at->format('H:i:s') }}</div>
            </td>
            <td>
                <code style="font-size:0.75rem;background:#f1f5f9;padding:0.15rem 0.4rem;border-radius:4px;color:#e94560;">{{ $log->action }}</code>
            </td>
            <td>
                @if($log->tenant)
                    <a href="{{ route('owner.schools.show', $log->tenant_id) }}" style="font-size:0.875rem;color:#1e293b;text-decoration:none;font-weight:500;">{{ $log->tenant->name }}</a>
                @else
                    <span style="color:#94a3b8;font-size:0.875rem;">—</span>
                @endif
            </td>
            <td style="font-size:0.875rem;max-width:300px;">{{ $log->description }}</td>
            <td><small class="text-muted">{{ $log->ip_address }}</small></td>
        </tr>
        @if($log->old_values || $log->new_values)
        <tr style="background:#fafbfc;">
            <td colspan="5" style="padding:0.5rem 1rem 0.75rem 1rem;">
                <div class="d-flex gap-3" style="font-size:0.75rem;">
                    @if($log->old_values)
                    <div>
                        <div style="color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Before</div>
                        <code style="color:#ef4444;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</code>
                    </div>
                    @endif
                    @if($log->new_values)
                    <div>
                        <div style="color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">After</div>
                        <code style="color:#16a34a;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</code>
                    </div>
                    @endif
                </div>
            </td>
        </tr>
        @endif
        @empty
        <tr><td colspan="5" class="text-center text-muted py-4">No audit logs found.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($logs->hasPages())
    <div class="px-3 py-2">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
