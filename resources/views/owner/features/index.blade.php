@extends('owner.layout')
@section('title','Features')
@section('page-title','System Features')

@section('content')
<div class="row g-4">
    {{-- Add Feature form --}}
    <div class="col-lg-4">
        <div class="owner-card">
            <div class="card-header">Add New Feature</div>
            <div class="card-body">
                <form method="POST" action="{{ route('owner.features.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Feature Key <span class="text-danger">*</span></label>
                        <input type="text" name="key" class="form-control @error('key') is-invalid @enderror" value="{{ old('key') }}" placeholder="e.g. attendance" required>
                        <div class="form-text">Lowercase, underscores only.</div>
                        @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Label <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control" value="{{ old('label') }}" placeholder="Attendance Management" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Group <span class="text-danger">*</span></label>
                        <input type="text" name="group" class="form-control" value="{{ old('group') }}" placeholder="academics / finance / core" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                    </div>
                    <button type="submit" class="btn btn-owner-primary w-100"><i class="fas fa-plus me-1"></i> Add Feature</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Features list --}}
    <div class="col-lg-8">
        @foreach($features as $group => $groupFeatures)
        <div class="owner-card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;font-weight:700;">{{ ucfirst($group) }}</span>
                <span class="badge bg-secondary ms-auto">{{ $groupFeatures->count() }}</span>
            </div>
            <div class="card-body p-0">
                @foreach($groupFeatures as $feature)
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <div>
                        <code style="font-size:0.8rem;background:#f1f5f9;padding:0.15rem 0.4rem;border-radius:4px;">{{ $feature->key }}</code>
                        <span style="font-size:0.875rem;font-weight:500;margin-left:0.5rem;">{{ $feature->label }}</span>
                        <div style="font-size:0.75rem;color:#94a3b8;">{{ $feature->plans_count }} plan(s) · {{ $feature->tenants_count }} school(s)</div>
                    </div>
                    <div class="d-flex gap-1 align-items-center">
                        <span class="badge {{ $feature->is_active ? 'bg-success' : 'bg-secondary' }}" style="font-size:0.7rem;">{{ $feature->is_active ? 'Active' : 'Off' }}</span>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editFeature{{ $feature->id }}"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="{{ route('owner.features.destroy', $feature) }}" onsubmit="return confirm('Delete feature {{ $feature->key }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>

                {{-- Edit modal --}}
                <div class="modal fade" id="editFeature{{ $feature->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('owner.features.update', $feature) }}">
                                @csrf @method('PUT')
                                <div class="modal-header"><h6 class="modal-title fw-bold">Edit: {{ $feature->key }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <div class="mb-3"><label class="form-label fw-semibold">Label</label><input type="text" name="label" class="form-control" value="{{ $feature->label }}" required></div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Group</label><input type="text" name="group" class="form-control" value="{{ $feature->group }}" required></div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="2">{{ $feature->description }}</textarea></div>
                                    <div class="mb-3"><label class="form-label fw-semibold">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ $feature->sort_order }}"></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $feature->is_active ? 'checked' : '' }}><label class="form-check-label">Active</label></div>
                                </div>
                                <div class="modal-footer"><button type="submit" class="btn btn-owner-primary">Save</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
