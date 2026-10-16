@extends('owner.layout')
@section('title','Messaging Packages')
@section('page-title','Messaging Credit Packages')

@section('topbar-actions')
    <a href="{{ route('owner.messaging.credits') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-coins me-1"></i> School Balances
    </a>
@endsection

@section('content')
<div class="row g-4">
    {{-- Add Package --}}
    <div class="col-lg-4">
        <div class="owner-card">
            <div class="card-header">Add New Package</div>
            <div class="card-body">
                <form method="POST" action="{{ route('owner.messaging.packages.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Package Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Starter Pack" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Price (GHS) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">SMS Credits</label>
                            <input type="number" name="sms_credits" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">WhatsApp Credits</label>
                            <input type="number" name="whatsapp_credits" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="What's included..."></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ $packages->count() + 1 }}">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-owner-primary w-100">
                        <i class="fas fa-plus me-1"></i> Create Package
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Package List --}}
    <div class="col-lg-8">
        <div class="row g-3">
            @forelse($packages as $pkg)
            <div class="col-md-6">
                <div class="owner-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="fw-bold mb-0">{{ $pkg->name }}</h6>
                                <small class="text-muted">{{ $pkg->description }}</small>
                            </div>
                            <span class="badge {{ $pkg->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $pkg->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div style="font-size:2rem;font-weight:800;color:#e94560;">
                            GHS {{ number_format($pkg->price, 2) }}
                        </div>

                        <div class="d-flex gap-3 my-3">
                            <div class="text-center">
                                <div style="font-size:1.2rem;font-weight:700;color:#3b82f6;">{{ number_format($pkg->sms_credits) }}</div>
                                <div style="font-size:0.72rem;color:#94a3b8;">SMS</div>
                            </div>
                            <div class="text-center">
                                <div style="font-size:1.2rem;font-weight:700;color:#25d366;">{{ number_format($pkg->whatsapp_credits) }}</div>
                                <div style="font-size:0.72rem;color:#94a3b8;">WhatsApp</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary flex-fill"
                                data-bs-toggle="modal" data-bs-target="#editPkg{{ $pkg->id }}">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('owner.messaging.packages.destroy', $pkg) }}"
                                  onsubmit="return confirm('Delete this package?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Edit Modal --}}
                <div class="modal fade" id="editPkg{{ $pkg->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('owner.messaging.packages.update', $pkg) }}">
                                @csrf @method('PUT')
                                <div class="modal-header"><h6 class="modal-title fw-bold">Edit: {{ $pkg->name }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <div class="mb-2"><label class="form-label fw-semibold">Name</label><input type="text" name="name" class="form-control" value="{{ $pkg->name }}" required></div>
                                    <div class="mb-2"><label class="form-label fw-semibold">Price (GHS)</label><input type="number" name="price" class="form-control" value="{{ $pkg->price }}" step="0.01" required></div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6"><label class="form-label fw-semibold">SMS Credits</label><input type="number" name="sms_credits" class="form-control" value="{{ $pkg->sms_credits }}"></div>
                                        <div class="col-6"><label class="form-label fw-semibold">WhatsApp Credits</label><input type="number" name="whatsapp_credits" class="form-control" value="{{ $pkg->whatsapp_credits }}"></div>
                                    </div>
                                    <div class="mb-2"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="2">{{ $pkg->description }}</textarea></div>
                                    <div class="row g-2">
                                        <div class="col-6"><label class="form-label fw-semibold">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ $pkg->sort_order }}"></div>
                                        <div class="col-6 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $pkg->is_active ? 'checked' : '' }}><label class="form-check-label">Active</label></div></div>
                                    </div>
                                </div>
                                <div class="modal-footer"><button type="submit" class="btn btn-owner-primary">Save</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-muted py-5">No packages yet. Create one.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
