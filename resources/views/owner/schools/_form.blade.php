{{-- Shared form partial for create and edit --}}
@php
    // Safe defaults — $tenant may be null on create page
    $t = $tenant ?? null;
@endphp
<div class="row g-4">
    {{-- Left column --}}
    <div class="col-lg-8">

        <div class="owner-card mb-4">
            <div class="card-header">School Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">School Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $t->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Subdomain <span class="text-danger">*</span></label>
                        <input type="text" name="subdomain" class="form-control @error('subdomain') is-invalid @enderror" value="{{ old('subdomain', $t->subdomain ?? '') }}" required placeholder="accra-academy">
                        <div class="form-text">Letters, numbers, hyphens only.</div>
                        @error('subdomain')<div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Custom Domain</label>
                        <input type="text" name="custom_domain" class="form-control" value="{{ old('custom_domain', $t->custom_domain ?? '') }}" placeholder="saningsschoolacademy.com">
                        <div class="form-text">Optional. School's own domain. They must point a CNAME to this server.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $t->phone ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $t->address ?? '') }}">
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
                        <input type="text" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror" value="{{ old('admin_name', $t->admin_name ?? '') }}" required>
                        @error('admin_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Admin Email <span class="text-danger">*</span></label>
                        <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email', $t->admin_email ?? '') }}" required>
                        @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Admin Phone</label>
                        <input type="text" name="admin_phone" class="form-control" value="{{ old('admin_phone', $t->admin_phone ?? '') }}">
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
                        <input type="text" name="db_host" class="form-control @error('db_host') is-invalid @enderror" value="{{ old('db_host', $t->db_host ?? '127.0.0.1') }}" required>
                        @error('db_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Port <span class="text-danger">*</span></label>
                        <input type="text" name="db_port" class="form-control" value="{{ old('db_port', $t->db_port ?? '5432') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Database Name <span class="text-danger">*</span></label>
                        <input type="text" name="db_name" class="form-control @error('db_name') is-invalid @enderror" value="{{ old('db_name', $t->db_name ?? '') }}" required placeholder="school_neon_db">
                        @error('db_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">DB Username <span class="text-danger">*</span></label>
                        <input type="text" name="db_username" class="form-control @error('db_username') is-invalid @enderror" value="{{ old('db_username', $t->db_username ?? '') }}" required>
                        @error('db_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            DB Password {{ $t ? '(leave blank to keep existing)' : '' }}
                            @if(!$t)<span class="text-danger">*</span>@endif
                        </label>
                        <input type="password" name="db_password" class="form-control @error('db_password') is-invalid @enderror" autocomplete="new-password" {{ !$t ? 'required' : '' }}>
                        @error('db_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="owner-card">
            <div class="card-header">Internal Notes</div>
            <div class="card-body">
                <textarea name="notes" class="form-control" rows="3" placeholder="Private notes about this school...">{{ old('notes', $t->notes ?? '') }}</textarea>
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
                            <option value="{{ $plan->id }}" {{ old('plan_id', $t->plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} (GHS {{ number_format($plan->price, 2) }}/{{ $plan->billing_cycle }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        @foreach(['active','trial','suspended','expired'] as $s)
                            <option value="{{ $s }}" {{ old('status', $t->status ?? 'trial') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Trial Ends At</label>
                    <input type="date" name="trial_ends_at" class="form-control"
                        value="{{ old('trial_ends_at', $t && $t->trial_ends_at ? $t->trial_ends_at->format('Y-m-d') : '') }}">
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Subscription Ends At</label>
                    <input type="date" name="subscription_ends_at" class="form-control"
                        value="{{ old('subscription_ends_at', $t && $t->subscription_ends_at ? $t->subscription_ends_at->format('Y-m-d') : '') }}">
                </div>
            </div>
        </div>

        <div class="owner-card">
            <div class="card-header">Backup Settings</div>
            <div class="card-body">
                <label class="form-label fw-semibold">Auto-Backup Frequency</label>
                <select name="backup_frequency" class="form-select">
                    @foreach(['daily','weekly','monthly','off'] as $f)
                        <option value="{{ $f }}" {{ old('backup_frequency', $t->backup_frequency ?? 'weekly') === $f ? 'selected' : '' }}>{{ ucfirst($f) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Cloudinary --}}
        <div class="owner-card mt-4">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-cloud" style="color:#3448c5;"></i> Cloudinary (Image Storage)
            </div>
            <div class="card-body">
                <div class="form-text mb-3" style="font-size:0.8rem;">
                    Each school gets their own Cloudinary account for storing student photos and website images.
                    Get credentials at <a href="https://cloudinary.com" target="_blank">cloudinary.com</a>.
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:0.85rem;">Cloud Name</label>
                    <input type="text" name="cloudinary_cloud_name" class="form-control form-control-sm"
                        value="{{ old('cloudinary_cloud_name', $t->cloudinary_cloud_name ?? '') }}"
                        placeholder="e.g. my-school-cloud">
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:0.85rem;">API Key</label>
                    <input type="text" name="cloudinary_api_key" class="form-control form-control-sm"
                        value="{{ old('cloudinary_api_key', $t->cloudinary_api_key ?? '') }}"
                        placeholder="123456789012345">
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold" style="font-size:0.85rem;">API Secret</label>
                    <input type="password" name="cloudinary_api_secret" class="form-control form-control-sm"
                        placeholder="{{ $t ? 'Leave blank to keep existing' : 'Enter API Secret' }}"
                        autocomplete="new-password">
                </div>
            </div>
        </div>

        {{-- Backblaze B2 --}}
        <div class="owner-card mt-4">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-hdd" style="color:#e87722;"></i> Backblaze B2 (File Storage)
            </div>
            <div class="card-body">
                <div class="form-text mb-3" style="font-size:0.8rem;">
                    Optional — for storing documents, exam files and backups.
                    Get credentials at <a href="https://www.backblaze.com/b2" target="_blank">backblaze.com/b2</a>.
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Key ID</label>
                        <input type="text" name="b2_key_id" class="form-control form-control-sm"
                            value="{{ old('b2_key_id', $t->b2_key_id ?? '') }}"
                            placeholder="Application Key ID">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Application Key</label>
                        <input type="password" name="b2_application_key" class="form-control form-control-sm"
                            placeholder="{{ $t ? 'Leave blank to keep' : 'Application Key' }}"
                            autocomplete="new-password">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Bucket Name</label>
                        <input type="text" name="b2_bucket_name" class="form-control form-control-sm"
                            value="{{ old('b2_bucket_name', $t->b2_bucket_name ?? '') }}"
                            placeholder="my-school-bucket">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Bucket ID</label>
                        <input type="text" name="b2_bucket_id" class="form-control form-control-sm"
                            value="{{ old('b2_bucket_id', $t->b2_bucket_id ?? '') }}"
                            placeholder="Bucket ID">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Endpoint URL</label>
                        <input type="text" name="b2_endpoint" class="form-control form-control-sm"
                            value="{{ old('b2_endpoint', $t->b2_endpoint ?? '') }}"
                            placeholder="https://s3.us-west-004.backblazeb2.com">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>