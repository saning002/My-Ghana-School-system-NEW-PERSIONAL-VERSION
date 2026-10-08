<div class="row g-4">
    <div class="col-lg-7">
        <div class="owner-card mb-4">
            <div class="card-header">Plan Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $plan->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Price (GHS) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" value="{{ old('price', $plan->price ?? '0') }}" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Billing Cycle</label>
                        <select name="billing_cycle" class="form-select">
                            @foreach(['monthly','quarterly','yearly'] as $c)
                                <option value="{{ $c }}" {{ old('billing_cycle', $plan->billing_cycle ?? 'monthly') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $plan->description ?? '') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Max Students</label>
                        <input type="number" name="max_students" class="form-control" value="{{ old('max_students', $plan->max_students ?? '') }}" placeholder="Leave blank = unlimited">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Max Lecturers</label>
                        <input type="number" name="max_lecturers" class="form-control" value="{{ old('max_lecturers', $plan->max_lecturers ?? '') }}" placeholder="Leave blank = unlimited">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">Active Plan</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="owner-card">
            <div class="card-header">Included Features</div>
            <div class="card-body" style="max-height:450px;overflow-y:auto;">
                @foreach($features as $group => $groupFeatures)
                <div class="mb-3">
                    <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;font-weight:700;margin-bottom:0.5rem;">{{ ucfirst($group) }}</div>
                    @foreach($groupFeatures as $feature)
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="features[]" value="{{ $feature->id }}" id="pf_{{ $feature->id }}"
                            {{ in_array($feature->id, $selectedFeatureIds ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label" for="pf_{{ $feature->id }}" style="font-size:0.875rem;">{{ $feature->label }}</label>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
