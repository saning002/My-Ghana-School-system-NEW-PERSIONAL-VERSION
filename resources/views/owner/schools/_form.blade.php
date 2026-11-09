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

        {{-- ── Billing Model ─────────────────────────────────────────────── --}}
        <div class="owner-card mb-4" x-data="billingForm({
            model: '{{ old('billing_model', $t->billing_model ?? 'flat') }}',
            rate: '{{ old('per_student_rate', $t->per_student_rate ?? 0) }}',
            terms: '{{ old('billing_terms_per_year', $t->billing_terms_per_year ?? 3) }}',
            tiers: {{ json_encode(
                ($t && $t->exists)
                    ? \App\Models\BillingTier::forTenant($t->id)->map(fn($t) => [
                        'min' => $t->min_students,
                        'max' => $t->max_students ?? '',
                        'rate' => $t->rate_per_student,
                    ])->toArray()
                    : [['min'=>1,'max'=>100,'rate'=>8],['min'=>101,'max'=>200,'rate'=>7],['min'=>201,'max'=>'','rate'=>6]]
            ) }}
        })">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fas fa-file-invoice-dollar" style="color:#16a34a;"></i> Billing Model
            </div>
            <div class="card-body">

                {{-- Model selector --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold mb-2">How does this school pay?</label>
                    <div class="d-flex gap-2">
                        <label class="cursor-pointer flex-grow-1" style="display:block;">
                            <input type="radio" name="billing_model" value="flat" x-model="model" class="sr-only peer">
                            <div class="p-3 rounded-xl border-2 transition-all peer-checked:border-success peer-checked:bg-light"
                                 style="border-color:#e2e8f0;border-radius:.75rem;"
                                 :style="model==='flat' ? 'border-color:#16a34a;background:#f0fdf4;' : ''">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-calendar-check text-success"></i>
                                    <div>
                                        <p class="fw-bold mb-0" style="font-size:.85rem;">Flat Rate (Annual)</p>
                                        <p class="text-muted mb-0" style="font-size:.72rem;">Fixed yearly subscription per plan</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <label class="cursor-pointer flex-grow-1" style="display:block;">
                            <input type="radio" name="billing_model" value="per_student" x-model="model" class="sr-only peer">
                            <div class="p-3 rounded-xl border-2 transition-all peer-checked:border-success peer-checked:bg-light"
                                 style="border-color:#e2e8f0;border-radius:.75rem;"
                                 :style="model==='per_student' ? 'border-color:#16a34a;background:#f0fdf4;' : ''">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-user-graduate text-success"></i>
                                    <div>
                                        <p class="fw-bold mb-0" style="font-size:.85rem;">Per Student Per Term</p>
                                        <p class="text-muted mb-0" style="font-size:.72rem;">Charged per active student each term</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Per-student config — only shown when per_student selected --}}
                <div x-show="model === 'per_student'" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="border-top pt-3" style="border-color:#f1f5f9;">

                    {{-- Default flat rate (used if no tiers set) --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Default Rate per Student per Term (GHS)
                        </label>
                        <div class="input-group" style="max-width:200px;">
                            <span class="input-group-text fw-bold">GHS</span>
                            <input type="number" name="per_student_rate" x-model="rate"
                                   class="form-control" step="0.5" min="0" max="100"
                                   value="{{ old('per_student_rate', $t->per_student_rate ?? 8) }}"
                                   placeholder="8.00">
                        </div>
                        <div class="form-text" style="font-size:.75rem;">
                            Used if no tiers are set below. Set any amount per school.
                        </div>
                    </div>

                    {{-- Tiered pricing ──────────────────────────────────────────── --}}
                    <div class="mb-3">

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-semibold mb-0">
                                Billing Tiers <span class="text-muted" style="font-size:.72rem;font-weight:400;">
                                    (set student ranges with different rates)
                                </span>
                            </label>
                            <button type="button" @click="addTier()"
                                class="btn btn-sm btn-outline-success" style="font-size:.75rem;">
                                <i class="fas fa-plus me-1"></i> Add Tier
                            </button>
                        </div>

                        {{-- Tier rows --}}
                        <template x-for="(tier, i) in tiers" :key="i">
                            <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded"
                                 style="background:#f8fafc;border:1px solid #f1f5f9;">
                                <span class="badge bg-secondary" style="font-size:.7rem;min-width:28px;" x-text="'Tier ' + (i+1)"></span>

                                {{-- Min --}}
                                <div style="width:90px;">
                                    <label style="font-size:.65rem;color:#94a3b8;font-weight:700;text-transform:uppercase;">Min</label>
                                    <input type="number" :value="tier.min" @input="tier.min = parseInt($event.target.value) || 0" name="tier_min[]"
                                        class="form-control form-control-sm" min="0" placeholder="0">
                                </div>

                                {{-- Max --}}
                                <div style="width:90px;">
                                    <label style="font-size:.65rem;color:#94a3b8;font-weight:700;text-transform:uppercase;">Max</label>
                                    <input type="number" :value="tier.max" @input="tier.max = $event.target.value === '' ? '' : parseInt($event.target.value)" name="tier_max[]"
                                        class="form-control form-control-sm" min="0" placeholder="blank = unlimited">
                                </div>

                                {{-- Rate --}}
                                <div style="width:100px;">
                                    <label style="font-size:.65rem;color:#94a3b8;font-weight:700;text-transform:uppercase;">GHS/Student</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="font-size:.7rem;">₵</span>
                                        <input type="number" :value="tier.rate" @input="tier.rate = parseFloat($event.target.value) || 0" name="tier_rate[]"
                                            class="form-control form-control-sm" step="0.5" min="0" placeholder="8">
                                    </div>
                                </div>

                                {{-- Remove button --}}
                                <button type="button" @click="removeTier(i)" x-show="tiers.length > 1"
                                    class="btn btn-sm text-danger ms-1" style="font-size:.7rem;padding:.2rem .4rem;"
                                    title="Remove tier">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>

                        <p class="text-muted mb-0" style="font-size:.72rem;">
                            <i class="fas fa-info-circle me-1"></i>
                            Leave "Max" blank on the last tier to include all students above that range.
                            Mid-term admissions within the same tier don't change the bill until next term.
                        </p>

                        {{-- Live preview --}}
                        <div class="mt-3 p-3 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                            <p class="fw-bold mb-2" style="font-size:.8rem;color:#15803d;">
                                <i class="fas fa-calculator me-1"></i> Billing Preview
                            </p>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div style="flex:1;">
                                    <label class="mb-0" style="font-size:.72rem;color:#64748b;">Active Students</label>
                                    <input type="number" x-model.number="previewStudents" class="form-control form-control-sm" style="max-width:120px;" min="0">
                                </div>
                                <div class="text-center" style="flex:1;">
                                    <p class="mb-0" style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Matched Tier Rate</p>
                                    <p class="fw-bold mb-0" style="font-size:1.1rem;color:#16a34a;" x-text="'GHS ' + previewRate().toFixed(2)"></p>
                                </div>
                                <div class="text-center" style="flex:1;">
                                    <p class="mb-0" style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Per Term</p>
                                    <p class="fw-bold mb-0" style="font-size:1.15rem;color:#16a34a;" x-text="'GHS ' + previewAmount().toFixed(2)"></p>
                                </div>
                                <div class="text-center" style="flex:1;">
                                    <p class="mb-0" style="font-size:.68rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;">Per Year</p>
                                    <p class="fw-bold mb-0" style="font-size:1.15rem;color:#0f172a;" x-text="'GHS ' + previewAnnual().toFixed(2)"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Terms per year --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Terms per Academic Year</label>
                        <select name="billing_terms_per_year" x-model="terms" class="form-select" style="max-width:200px;">
                            <option value="2">2 terms per year</option>
                            <option value="3">3 terms per year</option>
                            <option value="4">4 terms per year</option>
                        </select>
                    </div>

                    {{-- SMS add-on toggle --}}
                    <div class="mb-3 d-flex align-items-center gap-2 p-2.5 rounded-lg"
                         style="background:#f0fdf4;border:1px solid #bbf7d0;">
                        <input type="checkbox" name="billing_includes_sms" value="1"
                               class="form-check-input"
                               {{ old('billing_includes_sms', $t->billing_includes_sms ?? false) ? 'checked' : '' }}>
                        <div>
                            <p class="fw-bold mb-0" style="font-size:.82rem;color:#15803d;">
                                Include SMS Attendance Alerts
                            </p>
                            <p class="text-muted mb-0" style="font-size:.72rem;">
                                SMS costs bundled into the per-student rate. School doesn't buy credits separately.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Flat billing note — only shown when flat selected --}}
                <div x-show="model === 'flat'" x-cloak class="text-muted" style="font-size:.8rem;">
                    <i class="fas fa-info-circle me-1"></i>
                    School pays the plan price listed above. No per-student charges.
                </div>
            </div>
        </div>

        <script>
        function billingForm(data) {
            return {
                model: data.model || 'flat',
                rate: data.rate || 8,
                terms: data.terms || 3,
                tiers: data.tiers || [],
                previewStudents: 150,

                init() {
                    if (!this.tiers || !this.tiers.length) {
                        this.tiers = [
                            {min: 1, max: 100, rate: 8},
                            {min: 101, max: 200, rate: 7},
                            {min: 201, max: '', rate: 6}
                        ];
                    }
                },

                addTier() {
                    var lastMax = this.tiers.length ? (this.tiers[this.tiers.length-1].max || 100) : 0;
                    this.tiers.push({min: parseInt(lastMax)+1, max: '', rate: 5});
                },

                removeTier(i) {
                    this.tiers.splice(i, 1);
                },

                matchingTier() {
                    var s = this.previewStudents;
                    for (var i = 0; i < this.tiers.length; i++) {
                        var t = this.tiers[i];
                        var max = (t.max === '' || t.max === null) ? 999999 : parseInt(t.max);
                        if (s >= parseInt(t.min) && s <= max) return t;
                    }
                    return this.tiers[0] || {rate: parseFloat(this.rate) || 0};
                },

                previewRate() {
                    var t = this.matchingTier();
                    return parseFloat(t.rate) || parseFloat(this.rate) || 0;
                },

                previewAmount() {
                    return this.previewStudents * this.previewRate();
                },

                previewAnnual() {
                    return this.previewAmount() * parseInt(this.terms || 3);
                }
            };
        }
        </script>

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