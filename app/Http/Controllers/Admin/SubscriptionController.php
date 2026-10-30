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

        // Load payments from CENTRAL DB (pgsql) — not tenant DB
        // OwnerPayment and MessagingCreditPayment always use pgsql connection
        $subscriptionPayments = \App\Models\OwnerPayment::on('pgsql')
            ->where('tenant_id', $tenant->id)
            ->where(function ($q) {
                // Exclude any payment that also exists in messaging_credit_payments
                // (these were incorrectly saved as subscription payments due to the old type bug)
                $msgRefs = \App\Models\MessagingCreditPayment::on('pgsql')
                    ->whereNotNull('paystack_reference')
                    ->pluck('paystack_reference')
                    ->toArray();
                if (!empty($msgRefs)) {
                    $q->whereNotIn('paystack_reference', $msgRefs);
                }
            })
            ->orderByDesc('payment_date')
            ->limit(10)
            ->get();

        $creditPayments = \App\Models\MessagingCreditPayment::on('pgsql')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Get messaging credits from central DB
        // NOTE: Do NOT use ::on('pgsql') here — forTenant() is a custom static method
        // that returns a Builder via Model::__callStatic, and calling it on a Builder
        // via on() throws BadMethodCallException. The model already has
        // protected $connection = 'pgsql' so it always hits the central DB.
        $msgCredits = null;
        try {
            $msgCredits = \App\Models\MessagingCredit::forTenant($tenant->id);
        } catch (\Throwable $e) {
            \Log::error('Failed to load messaging credits for tenant ' . $tenant->id . ': ' . $e->getMessage());
        }

        // Get messaging packages — active() is a scope so on('pgsql') works here
        $msgPackages = collect();
        try {
            $msgPackages = \App\Models\MessagingCreditPackage::active()->get();
        } catch (\Throwable $e) {
            \Log::error('Failed to load messaging packages: ' . $e->getMessage());
        }

        return view('admin.subscription.show', compact('tenant', 'msgCredits', 'msgPackages', 'subscriptionPayments', 'creditPayments'));
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
