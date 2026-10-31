<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Show the school's subscription status and payment options.
     * Only super admins can access this.
     */
    public function show()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admins can manage subscriptions.');
        }

        $tenant = TenantService::current();

        if (!$tenant) {
            return view('admin.subscription.no-tenant');
        }

        $tenant->load('plan');

        // Load subscription payments from central DB
        // Exclude records that are actually messaging credit purchases
        // (identified by description starting with "Messaging Credits")
        $subscriptionPayments = \App\Models\OwnerPayment::on('pgsql')
            ->where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->whereNull('description')
                  ->orWhere('description', 'not like', 'Messaging Credits%');
            })
            ->orderByDesc('payment_date')
            ->limit(10)
            ->get();

        $creditPayments = \App\Models\MessagingCreditPayment::on('pgsql')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Get messaging credits — shared pool + all branch pools
        $msgCredits       = null;
        $allBranchCredits = collect();
        try {
            $msgCredits = \App\Models\MessagingCredit::forTenant($tenant->id);

            // Load branch-specific credit pools if any exist
            $allBranchCredits = \App\Models\MessagingCredit::allForTenant($tenant->id)
                ->filter(fn($c) => !$c->isShared()); // only branch-specific ones
        } catch (\Throwable $e) {
            \Log::error('Failed to load messaging credits: ' . $e->getMessage());
        }

        // Load branches for the branch credit display
        $branches = collect();
        try {
            $branches = \App\Models\ChurchBranch::orderBy('name')->get();
        } catch (\Throwable $e) {}

        // Is this a branch admin? They only see their own branch credits
        $currentBranchId = auth()->user()?->church_branch_id ?? null;
        if ($currentBranchId) {
            // Branch admin — show their branch pool (or shared if no dedicated pool)
            $branchMsgCredits = \App\Models\MessagingCredit::forBranch($tenant->id, $currentBranchId);
        } else {
            $branchMsgCredits = null; // Super admin sees all pools
        }

        // Get messaging packages — active() is a scope so on('pgsql') works here
        $msgPackages = collect();
        try {
            $msgPackages = \App\Models\MessagingCreditPackage::active()->get();
        } catch (\Throwable $e) {
            \Log::error('Failed to load messaging packages: ' . $e->getMessage());
        }

        return view('admin.subscription.show', compact(
            'tenant', 'msgCredits', 'msgPackages',
            'subscriptionPayments', 'creditPayments',
            'allBranchCredits', 'branches', 'branchMsgCredits', 'currentBranchId'
        ));
    }

    /**
     * Redirect the super admin to the Paystack payment page.
     */
    public function pay()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $tenant = TenantService::current();

        if (!$tenant) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Could not identify your school. Contact the platform owner.');
        }

        return redirect()->route('paystack.subscription.page', $tenant);
    }
}
