<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feature;
use App\Models\OwnerPayment;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('plan')->withCount(['payments', 'backups']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('subdomain', 'like', '%' . $request->search . '%')
                  ->orWhere('admin_email', 'like', '%' . $request->search . '%');
            });
        }

        $tenants = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('owner.schools.index', compact('tenants'));
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)->get();
        return view('owner.schools.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'subdomain'     => 'required|string|max:100|unique:tenants,subdomain|alpha_dash',
            'custom_domain' => 'nullable|string|max:255|unique:tenants,custom_domain',
            'admin_name'    => 'required|string|max:255',
            'admin_email'   => 'required|email|max:255',
            'admin_phone'   => 'nullable|string|max:30',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:500',
            'plan_id'       => 'nullable|exists:plans,id',
            'status'        => 'required|in:active,suspended,trial,expired',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'db_host'       => env('VPS_POSTGRES_HOST') ? 'nullable|string|max:255' : 'required|string|max:255',
            'db_port'       => env('VPS_POSTGRES_HOST') ? 'nullable|string|max:10'  : 'required|string|max:10',
            'db_name'       => env('VPS_POSTGRES_HOST') ? 'nullable|string|max:255' : 'required|string|max:255',
            'db_username'   => env('VPS_POSTGRES_HOST') ? 'nullable|string|max:255' : 'required|string|max:255',
            'db_password'   => env('VPS_POSTGRES_HOST') ? 'nullable|string|max:500' : 'required|string|max:500',
            'backup_frequency' => 'required|in:daily,weekly,monthly,off',
            'notes'         => 'nullable|string',
            // Storage credentials
            'cloudinary_cloud_name'  => 'nullable|string|max:255',
            'cloudinary_api_key'     => 'nullable|string|max:255',
            'cloudinary_api_secret'  => 'nullable|string|max:500',
            'b2_key_id'              => 'nullable|string|max:255',
            'b2_application_key'     => 'nullable|string|max:500',
            'b2_bucket_name'         => 'nullable|string|max:255',
            'b2_bucket_id'           => 'nullable|string|max:255',
            'b2_endpoint'            => 'nullable|string|max:255',
            // Billing model
            'billing_model'          => 'nullable|in:flat,per_student',
            'per_student_rate'       => 'nullable|numeric|min:0|max:100',
            'billing_terms_per_year' => 'nullable|integer|min:1|max:4',
            'billing_includes_sms'   => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['subdomain']);

        // Set billing defaults
        $validated['billing_model']          = $validated['billing_model'] ?? 'flat';
        $validated['per_student_rate']         = $validated['per_student_rate'] ?? 0;
        $validated['billing_terms_per_year']   = $validated['billing_terms_per_year'] ?? 3;
        $validated['billing_includes_sms']     = ($validated['billing_model'] === 'per_student') ? ($request->boolean('billing_includes_sms')) : false;

        // ── Auto-provision local database if VPS Postgres is configured ─────
        // When VPS_POSTGRES_HOST is set, create the DB automatically instead of
        // requiring the owner to manually fill in db credentials.
        if (env('VPS_POSTGRES_HOST') && empty($validated['db_host'] ?? '')) {
            try {
                $dbResult = $this->provisionLocalDatabase($validated['subdomain']);
                $validated['db_host']     = $dbResult['host'];
                $validated['db_port']     = $dbResult['port'];
                $validated['db_name']     = $dbResult['db_name'];
                $validated['db_username'] = $dbResult['username'];
                $validated['db_password'] = $dbResult['password'];
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Failed to create school database: ' . $e->getMessage());
            }
        }

        $tenant = Tenant::create($validated);

        // If a plan is assigned, sync plan features as tenant features
        if ($tenant->plan_id) {
            $this->syncPlanFeaturesToTenant($tenant);
        }

        // Save billing tiers if per_student billing model
        if ($validated['billing_model'] === 'per_student' && $request->has('tier_rate')) {
            $this->syncBillingTiers($request, $tenant->id);
        }

        AuditLog::record(
            'tenant.created',
            "School '{$tenant->name}' created with subdomain '{$tenant->subdomain}'",
            $tenant->id
        );

        return redirect()->route('owner.schools.show', $tenant)
            ->with('success', "School '{$tenant->name}' created successfully.");
    }

    public function show(Tenant $tenant)
    {
        // Explicitly re-fetch to ensure we have a clean model with correct ID
        $tenant = Tenant::findOrFail($tenant->id);

        // Load plan safely
        try { $tenant->load('plan'); } catch (\Throwable $e) {}

        // Load subscription payments from central DB — exclude messaging credit payments
        $paymentsCollection = collect();
        try {
            $paymentsCollection = \App\Models\OwnerPayment::on('pgsql')
                ->where('tenant_id', $tenant->id)
                ->where(function ($q) {
                    $q->whereNull('description')
                      ->orWhere('description', 'not like', 'Messaging Credits%');
                })
                ->orderByDesc('payment_date')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {}

        // Load messaging credit payments separately
        $creditPaymentsCollection = collect();
        try {
            $creditPaymentsCollection = \App\Models\MessagingCreditPayment::on('pgsql')
                ->where('tenant_id', $tenant->id)
                ->where('status', 'completed')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {}

        // Load backups safely
        $backupsCollection = collect();
        try {
            $backupsCollection = \App\Models\Backup::on('pgsql')
                ->where('tenant_id', $tenant->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        } catch (\Throwable $e) {}

        // Total paid (subscription only, not messaging credits)
        $totalPaid = 0;
        try {
            $totalPaid = DB::table('owner_payments')
                ->where('tenant_id', (int) $tenant->id)
                ->where(function ($q) {
                    $q->whereNull('description')
                      ->orWhere('description', 'not like', 'Messaging Credits%');
                })
                ->sum('amount');
        } catch (\Throwable $e) {}
        try {
            $rawFeatures = Feature::orderBy('group')->orderBy('sort_order')->get();
            $features    = $rawFeatures->groupBy('group');
        } catch (\Throwable $e) {}

        // Plan feature IDs
        $planFeatureIds = [];
        try {
            if ($tenant->plan_id && $tenant->relationLoaded('plan') && $tenant->plan) {
                $planFeatureIds = $tenant->plan->features()->pluck('features.id')->toArray();
            }
        } catch (\Throwable $e) {}

        // Tenant overrides — direct DB query using explicit tenant ID
        $tenantOverrides = [];
        try {
            $tenantOverrides = DB::table('tenant_features')
                ->where('tenant_id', (int) $tenant->id)
                ->get(['feature_id', 'enabled'])
                ->mapWithKeys(fn($row) => [(int)$row->feature_id => (bool) $row->enabled])
                ->toArray();
        } catch (\Throwable $e) {}

        // Total paid using explicit tenant ID
        $totalPaid = 0;
        try {
            $totalPaid = DB::table('owner_payments')
                ->where('tenant_id', (int) $tenant->id)
                ->sum('amount');
        } catch (\Throwable $e) {}

        // Collections for view — already loaded above
        return view('owner.schools.show', compact(
            'tenant', 'features', 'planFeatureIds', 'tenantOverrides',
            'totalPaid', 'paymentsCollection', 'creditPaymentsCollection', 'backupsCollection'
        ));
    }

    public function edit(Tenant $tenant)
    {
        $plans = collect();
        try {
            $plans = Plan::where('is_active', true)->get();
        } catch (\Throwable $e) {}

        return view('owner.schools.edit', compact('tenant', 'plans'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'subdomain'     => 'required|string|max:100|alpha_dash|unique:tenants,subdomain,' . $tenant->id,
            'custom_domain' => 'nullable|string|max:255|unique:tenants,custom_domain,' . $tenant->id,
            'admin_name'    => 'required|string|max:255',
            'admin_email'   => 'required|email|max:255',
            'admin_phone'   => 'nullable|string|max:30',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:500',
            'plan_id'       => 'nullable|exists:plans,id',
            'status'        => 'required|in:active,suspended,trial,expired',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'db_host'       => 'required|string|max:255',
            'db_port'       => 'required|string|max:10',
            'db_name'       => 'required|string|max:255',
            'db_username'   => 'required|string|max:255',
            'db_password'   => 'nullable|string|max:500',
            'backup_frequency' => 'required|in:daily,weekly,monthly,off',
            'notes'         => 'nullable|string',
            'cloudinary_cloud_name'  => 'nullable|string|max:255',
            'cloudinary_api_key'     => 'nullable|string|max:255',
            'cloudinary_api_secret'  => 'nullable|string|max:500',
            'b2_key_id'              => 'nullable|string|max:255',
            'b2_application_key'     => 'nullable|string|max:500',
            'b2_bucket_name'         => 'nullable|string|max:255',
            'b2_bucket_id'           => 'nullable|string|max:255',
            'b2_endpoint'            => 'nullable|string|max:255',
            // Billing model
            'billing_model'          => 'nullable|in:flat,per_student',
            'per_student_rate'       => 'nullable|numeric|min:0|max:100',
            'billing_terms_per_year' => 'nullable|integer|min:1|max:4',
            'billing_includes_sms'   => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['subdomain']);

        // Don't overwrite password if left blank
        if (empty($validated['db_password'])) {
            unset($validated['db_password']);
        }

        // Don't overwrite Cloudinary secret if left blank (password field — never pre-filled)
        if (empty($validated['cloudinary_api_secret'])) {
            unset($validated['cloudinary_api_secret']);
        }

        // Set billing defaults
        $validated['billing_model']          = $validated['billing_model'] ?? 'flat';
        $validated['per_student_rate']       = $validated['per_student_rate'] ?? 0;
        $validated['billing_terms_per_year'] = $validated['billing_terms_per_year'] ?? 3;
        $validated['billing_includes_sms']   = ($validated['billing_model'] === 'per_student')
            ? $request->boolean('billing_includes_sms')
            : false;

        $oldValues = $tenant->toArray();
        $tenant->update($validated);

        // Save billing tiers if per_student billing model
        if (($validated['billing_model'] ?? 'flat') === 'per_student' && $request->has('tier_rate')) {
            $this->syncBillingTiers($request, $tenant->id);
        } elseif (($validated['billing_model'] ?? 'flat') === 'flat') {
            // Clear tiers if switching back to flat
            \App\Models\BillingTier::where('tenant_id', $tenant->id)->delete();
        }

        AuditLog::record('tenant.updated', "School '{$tenant->name}' updated", $tenant->id, $oldValues, $validated);

        return redirect()->route('owner.schools.show', $tenant)
            ->with('success', 'School updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        $name = $tenant->name;
        $tenant->delete(); // SoftDelete

        AuditLog::record('tenant.deleted', "School '{$name}' deleted (soft)");

        return redirect()->route('owner.schools.index')
            ->with('success', "School '{$name}' deleted.");
    }

    // ── Private: sync billing tiers from form input ──────────────────────

    private function syncBillingTiers(Request $request, int $tenantId): void
    {
        $mins  = $request->input('tier_min', []);
        $maxs  = $request->input('tier_max', []);
        $rates = $request->input('tier_rate', []);

        $tiers = [];
        for ($i = 0; $i < count($rates); $i++) {
            if (empty($rates[$i]) || $rates[$i] <= 0) {
                continue; // Skip empty
            }
            $tiers[] = [
                'min_students'     => (int) ($mins[$i] ?? 0),
                'max_students'     => isset($maxs[$i]) && $maxs[$i] !== '' ? (int) $maxs[$i] : null,
                'rate_per_student' => (float) $rates[$i],
            ];
        }

        \App\Models\BillingTier::syncForTenant($tenantId, $tiers);
    }

    public function updateStatus(Request $request, Tenant $tenant)
    {
        $request->validate(['status' => 'required|in:active,suspended,trial,expired']);

        $old = $tenant->status;
        $tenant->update(['status' => $request->status]);

        AuditLog::record(
            'tenant.status_changed',
            "School '{$tenant->name}' status changed from '{$old}' to '{$request->status}'",
            $tenant->id,
            ['status' => $old],
            ['status' => $request->status]
        );

        return back()->with('success', "Status updated to '{$request->status}'.");
    }

    public function updateFeatures(Request $request, Tenant $tenant)
    {
        $request->validate(['features' => 'nullable|array', 'features.*' => 'exists:features,id']);

        $enabledIds  = $request->input('features', []);
        $allFeatures = Feature::pluck('id');

        $sync = [];
        foreach ($allFeatures as $featureId) {
            $sync[$featureId] = ['enabled' => in_array($featureId, $enabledIds)];
        }

        $tenant->features()->sync($sync);

        // Rebuild fast-lookup cache
        $tenant->rebuildModulesCache();

        AuditLog::record('tenant.features_updated', "Features updated for '{$tenant->name}'", $tenant->id);

        return back()->with('success', 'Module settings saved.')->with('active_tab', 'modules');
    }

    /**
     * Save admin panel theme colours for this tenant (from owner panel).
     */
    public function updateTheme(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'adminPrimary' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'adminNav'     => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'primary'      => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent'       => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $existing = $tenant->theme_json ?? [];
        $merged   = array_merge($existing, array_filter($validated, fn($v) => $v !== null));

        $tenant->update(['theme_json' => $merged]);

        AuditLog::record('tenant.theme_updated', "Admin theme updated for '{$tenant->name}'", $tenant->id);

        return back()->with('success', 'Theme colours saved.')->with('active_tab', 'theme');
    }

    private function syncPlanFeaturesToTenant(Tenant $tenant): void
    {
        $planFeatureIds = $tenant->plan->features()->pluck('features.id');
        $allFeatures    = Feature::pluck('id');

        $sync = [];
        foreach ($allFeatures as $id) {
            $sync[$id] = ['enabled' => $planFeatureIds->contains($id)];
        }
        $tenant->features()->sync($sync);
    }

    /**
     * Auto-provision a new PostgreSQL database on the local VPS Postgres instance.
     * Called when VPS_POSTGRES_HOST env var is set.
     * Creates a dedicated DB + user for the school, returns connection details.
     */
    private function provisionLocalDatabase(string $subdomain): array
    {
        $host     = env('VPS_POSTGRES_HOST', 'postgres');
        $port     = env('VPS_POSTGRES_PORT', '5432');
        $superUser = env('VPS_POSTGRES_SUPERUSER', 'postgres');
        $superPass = env('VPS_POSTGRES_SUPERUSER_PASSWORD', '');

        // Sanitise subdomain to safe DB name: lowercase, letters/numbers/underscores only
        $safeName = 'school_' . preg_replace('/[^a-z0-9_]/', '_', strtolower($subdomain));

        // Generate a strong random password for this school's DB user
        $dbPassword = \Illuminate\Support\Str::random(24);

        // Connect as superuser to the postgres maintenance DB
        $pdo = new \PDO(
            "pgsql:host={$host};port={$port};dbname=postgres",
            $superUser,
            $superPass,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );

        // Create the user (role) if it doesn't already exist
        $userExists = $pdo->query("SELECT 1 FROM pg_roles WHERE rolname = '{$safeName}'")->fetchColumn();
        if (!$userExists) {
            $pdo->exec("CREATE USER \"{$safeName}\" WITH PASSWORD " . $pdo->quote($dbPassword));
        } else {
            // User exists (maybe a retry) — reset the password
            $pdo->exec("ALTER USER \"{$safeName}\" WITH PASSWORD " . $pdo->quote($dbPassword));
        }

        // Create the database if it doesn't already exist
        $dbExists = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '{$safeName}'")->fetchColumn();
        if (!$dbExists) {
            $pdo->exec("CREATE DATABASE \"{$safeName}\" OWNER \"{$safeName}\"");
        }

        // Grant all privileges
        $pdo->exec("GRANT ALL PRIVILEGES ON DATABASE \"{$safeName}\" TO \"{$safeName}\"");

        return [
            'host'     => $host,
            'port'     => $port,
            'db_name'  => $safeName,
            'username' => $safeName,
            'password' => $dbPassword,
        ];
    }
}
