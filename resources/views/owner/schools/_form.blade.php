{{-- Shared form partial for create and edit --}}
<div class="row g-4">
    {{-- Left column --}}
    <div class="col-lg-8">

        <div class="owner-card mb-4">
            <div class="card-header">School Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">School Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $tenant->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Subdomain <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="subdomain" class="form-control @error('subdomain') is-invalid @enderror" value="{{ old('subdomain', $tenant->subdomain ?? '') }}" required placeholder="accra-academy">
                        </div>
                        <div class="form-text">Letters, numbers, hyphens only.</div>
                        @error('subdomain')<div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $tenant->phone ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $tenant->address ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="owner-card mb-4">
            <div class="card-header">School Admin Contact</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Admin Name <span class="text-danger">*</span></label>
                        <input type="text" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror" value="{{ old('admin_name', $tenant->admin_name ?? '') }}" required>
                        @error('admin_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Admin Email <span class="text-danger">*</span></label>
                        <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email', $tenant->admin_email ?? '') }}" required>
                        @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Admin Phone</label>
                        <input type="text" name="admin_phone" class="form-control" value="{{ old('admin_phone', $tenant->admin_phone ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="owner-card mb-4">
            <div class="card-header">Database Connection</div>
            <div class="card-body">
                <div class="alert alert-warning" style="font-size:0.83rem; border-radius:8px;">
                    <i class="fas fa-lock me-1"></i> Database password is stored <strong>encrypted</strong>. Leave password blank on edit to keep existing.
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">DB Host <span class="text-danger">*</span></label>
                        <input type="text" name="db_host" class="form-control @error('db_host') is-invalid @enderror" value="{{ old('db_host', $tenant->db_host ?? '127.0.0.1') }}" required>
                        @error('db_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Port <span class="text-danger">*</span></label>
                        <input type="text" name="db_port" class="form-control" value="{{ old('db_port', $tenant->db_port ?? '3306') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Database Name <span class="text-danger">*</span></label>
                        <input type="text" name="db_name" class="form-control @error('db_name') is-invalid @enderror" value="{{ old('db_name', $tenant->db_name ?? '') }}" required placeholder="school_accra_db">
                        @error('db_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">DB Username <span class="text-danger">*</span></label>
                        <input type="text" name="db_username" class="form-control @error('db_username') is-invalid @enderror" value="{{ old('db_username', $tenant->db_username ?? '') }}" required>
                        @error('db_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">DB Password {{ isset($tenant) ? '(leave blank to keep)' : '' }} <span class="text-danger">*</span></label>
                        <input type="password" name="db_password" class="form-control @error('db_password') is-invalid @enderror" autocomplete="new-password" {{ !isset($tenant) ? 'required' : '' }}>
                        @error('db_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="owner-card">
            <div class="card-header">Internal Notes</div>
            <div class="card-body">
                <textarea name="notes" class="form-control" rows="3" placeholder="Private notes about this school...">{{ old('notes', $tenant->notes ?? '') }}</textarea>
            </div>
        </div>

    </div>

    {{-- Right column --}}
    <div class="col-lg-4">
        <div class="owner-card mb-4">
            <div class="card-header">Plan & Status</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Plan</label>
                    <select name="plan_id" class="form-select">
                        <option value="">— No Plan —</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id', $tenant->plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} (GHS {{ number_format($plan->price, 2) }}/{{ $plan->billing_cycle }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        @foreach(['active','trial','suspended','expired'] as $s)
                            <option value="{{ $s }}" {{ old('status', $tenant->status ?? 'trial') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Trial Ends At</label>
                    <input type="date" name="trial_ends_at" class="form-control" value="{{ old('trial_ends_at', isset($tenant) && $tenant->trial_ends_at ? $tenant->trial_ends_at->format('Y-m-d') : '') }}">
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Subscription Ends At</label>
                    <input type="date" name="subscription_ends_at" class="form-control" value="{{ old('subscription_ends_at', isset($tenant) && $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('Y-m-d') : '') }}">
                </div>
            </div>
        </div>

        <div class="owner-card">
            <div class="card-header">Backup Settings</div>
            <div class="card-body">
                <label class="form-label fw-semibold">Auto-Backup Frequency</label>
                <select name="backup_frequency" class="form-select">
                    @foreach(['daily','weekly','monthly','off'] as $f)
                        <option value="{{ $f }}" {{ old('backup_frequency', $tenant->backup_frequency ?? 'weekly') === $f ? 'selected' : '' }}>{{ ucfirst($f) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
