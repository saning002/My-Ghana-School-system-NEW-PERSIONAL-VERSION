@extends('owner.layout')
@section('title','Schools')
@section('page-title','Schools')

@section('topbar-actions')
    <a href="{{ route('owner.schools.create') }}" class="btn btn-sm btn-owner-primary">
        <i class="fas fa-plus me-1"></i> Add School
    </a>
@endsection

@section('content')
{{-- Filters --}}
<div class="owner-card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px;" placeholder="Search name, subdomain, email..." value="{{ request('search') }}">
            <select name="status" class="form-select form-select-sm" style="max-width:160px;">
                <option value="">All Statuses</option>
                @foreach(['active','trial','suspended','expired'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-secondary">Filter</button>
            <a href="{{ route('owner.schools.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="owner-table">
    <table class="table">
        <thead>
            <tr>
                <th>School</th>
                <th>Subdomain</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Subscription</th>
                <th>Payments</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($tenants as $tenant)
        <tr>
            <td>
                <a href="{{ route('owner.schools.show', $tenant) }}" style="font-weight:600;color:#1e293b;text-decoration:none;">{{ $tenant->name }}</a>
                <div style="font-size:0.75rem;color:#94a3b8;">{{ $tenant->admin_email }}</div>
            </td>
            <td><code style="font-size:0.8rem;">{{ $tenant->subdomain }}</code></td>
            <td><small>{{ $tenant->plan->name ?? '—' }}</small></td>
            <td>
                <span class="badge badge-{{ $tenant->status }}">{{ ucfirst($tenant->status) }}</span>
                @if($tenant->isOnTrial())
                    <div style="font-size:0.72rem;color:#ca8a04;">{{ $tenant->trialDaysRemaining() }}d left</div>
                @endif
            </td>
            <td>
                @if($tenant->subscription_ends_at)
                    <small class="{{ $tenant->subscriptionDaysRemaining() <= 14 ? 'text-danger' : 'text-muted' }}">
                        {{ $tenant->subscription_ends_at->format('d M Y') }}
                    </small>
                @else
                    <small class="text-muted">—</small>
                @endif
            </td>
            <td><small class="text-muted">{{ $tenant->payments_count }} payment{{ $tenant->payments_count !== 1 ? 's' : '' }}</small></td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('owner.schools.show', $tenant) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('owner.schools.edit', $tenant) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="{{ route('owner.schools.destroy', $tenant) }}" onsubmit="return confirm('Delete {{ $tenant->name }}? This is a soft delete.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-muted py-4">No schools found.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($tenants->hasPages())
    <div class="px-3 py-2">{{ $tenants->links() }}</div>
    @endif
</div>
@endsection
