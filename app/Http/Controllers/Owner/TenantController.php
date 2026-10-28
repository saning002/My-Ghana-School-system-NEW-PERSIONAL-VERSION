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
            'db_password'   => 'required|string|max:500',
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
        ]);

        $validated['slug'] = Str::slug($validated['subdomain']);

        $tenant = Tenant::create($validated);

        // If a plan is assigned, sync plan features as tenant features
        if ($tenant->plan_id) {
            $this->syncPlanFeaturesToTenant($tenant);
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

        // Load payments safely
        try {
            $tenant->load(['payments' => fn($q) => $q->orderByDesc('payment_date')->limit(10)]);
        } catch (\Throwable $e) {}

        // Load backups safely
        try {
            $tenant->load(['backups' => fn($q) => $q->orderByDesc('created_at')->limit(5)]);
        } catch (\Throwable $e) {}

        // Get features grouped
        $features = collect();
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

        // Collections for view
        $paymentsCollection = $tenant->relationLoaded('payments') ? $tenant->payments : collect();
        $backupsCollection  = $tenant->relationLoaded('backups')  ? $tenant->backups  : collect();

        return view('owner.schools.show', compact(
            'tenant', 'features', 'planFeatureIds', 'tenantOverrides',
            'totalPaid', 'paymentsCollection', 'backupsCollection'
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
        ]);

        $validated['slug'] = Str::slug($validated['subdomain']);

        // Don't overwrite password if left blank
        if (empty($validated['db_password'])) {
            unset($validated['db_password']);
        }

        $oldValues = $tenant->toArray();
        $tenant->update($validated);

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
}
