@extends('owner.layout')
@section('title','Plans')
@section('page-title','Plans')
@section('topbar-actions')
    <a href="{{ route('owner.plans.create') }}" class="btn btn-sm btn-owner-primary"><i class="fas fa-plus me-1"></i> New Plan</a>
@endsection

@section('content')
<div class="row g-4">
@forelse($plans as $plan)
<div class="col-md-4">
    <div class="owner-card h-100">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="fw-bold mb-0">{{ $plan->name }}</h5>
                <span class="badge {{ $plan->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="mb-3">
                <span style="font-size:1.75rem;font-weight:800;color:#1e293b;">GHS {{ number_format($plan->price, 2) }}</span>
                <span style="color:#94a3b8;font-size:0.85rem;">/ {{ $plan->billing_cycle }}</span>
            </div>
            @if($plan->description)
                <p style="font-size:0.875rem;color:#64748b;">{{ $plan->description }}</p>
            @endif
            <div class="mb-3">
                <div style="font-size:0.75rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.5rem;">Features ({{ $plan->features->count() }})</div>
                @foreach($plan->features->take(5) as $f)
                    <span class="badge bg-light text-dark border me-1 mb-1" style="font-size:0.75rem;">{{ $f->label }}</span>
                @endforeach
                @if($plan->features->count() > 5)
                    <span style="font-size:0.75rem;color:#94a3b8;">+{{ $plan->features->count() - 5 }} more</span>
                @endif
            </div>
            <div style="font-size:0.8rem;color:#64748b;" class="mb-3">
                <i class="fas fa-school me-1"></i> {{ $plan->tenants_count }} school{{ $plan->tenants_count !== 1 ? 's' : '' }}
                @if($plan->max_students) · <i class="fas fa-user-graduate me-1"></i> Max {{ $plan->max_students }} students @endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('owner.plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary flex-fill">Edit</a>
                <form method="POST" action="{{ route('owner.plans.destroy', $plan) }}" onsubmit="return confirm('Delete plan {{ $plan->name }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@empty
<div class="col-12 text-center text-muted py-5">No plans yet. <a href="{{ route('owner.plans.create') }}">Create one</a>.</div>
@endforelse
</div>
@endsection
