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

        // Get messaging credits from central DB
        $msgCredits = null;
        try {
            $msgCredits = \App\Models\MessagingCredit::on('pgsql')->forTenant($tenant->id);
        } catch (\Throwable $e) {}

        // Get messaging packages
        $msgPackages = collect();
        try {
            $msgPackages = \App\Models\MessagingCreditPackage::on('pgsql')->active()->get();
        } catch (\Throwable $e) {}

        return view('admin.subscription.show', compact('tenant', 'msgCredits', 'msgPackages'));
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
