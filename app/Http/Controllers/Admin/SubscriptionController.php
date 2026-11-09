<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\PerStudentInvoice;
use App\Models\PaystackTransaction;
use App\Services\TenantService;
use App\Services\PaystackService;
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

        // Get messaging credits
        $msgCredits       = null;
        $allBranchCredits = collect();
        try {
            $msgCredits = \App\Models\MessagingCredit::forTenant($tenant->id);
            $allBranchCredits = \App\Models\MessagingCredit::allForTenant($tenant->id)
                ->filter(fn($c) => !$c->isShared());
        } catch (\Throwable $e) {}

        $branches = collect();
        try { $branches = \App\Models\ChurchBranch::orderBy('name')->get(); } catch (\Throwable $e) {}

        $currentBranchId = auth()->user()?->church_branch_id ?? null;
        $branchMsgCredits = null;
        if ($currentBranchId) {
            $branchMsgCredits = \App\Models\MessagingCredit::forBranch($tenant->id, $currentBranchId);
        }

        $msgPackages = collect();
        try { $msgPackages = \App\Models\MessagingCreditPackage::active()->get(); } catch (\Throwable $e) {}

        // ── Per-student invoices ──────────────────────────────────────────
        $perStudentInvoices = collect();
        $currentInvoice     = null;
        try {
            $perStudentInvoices = PerStudentInvoice::forTenant($tenant->id)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            // Find the most recent pending invoice (the one to pay now)
            $currentInvoice = $perStudentInvoices->where('status', 'pending')->first();
        } catch (\Throwable $e) {}

        return view('admin.subscription.show', compact(
            'tenant', 'msgCredits', 'msgPackages',
            'subscriptionPayments', 'creditPayments',
            'allBranchCredits', 'branches', 'branchMsgCredits', 'currentBranchId',
            'perStudentInvoices', 'currentInvoice'
        ));
    }

    /**
     * Redirect the super admin to the Paystack payment page (flat rate).
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

    // ── Per-Student Invoice: Generate ──────────────────────────────────────

    /**
     * Generate a new invoice for the current term based on active student count.
     */
    public function generateInvoice()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $tenant = TenantService::current();

        if (!$tenant || !$tenant->isPerStudentBilling()) {
            return back()->with('error', 'This school is not on per-student billing.');
        }

        $invoice = PerStudentInvoice::generateForTerm($tenant);

        if (!$invoice) {
            return back()->with('error', 'Could not generate invoice. Ensure there are active students enrolled.');
        }

        return back()->with('success',
            "Invoice {$invoice->invoice_number} generated for {$invoice->student_count} students at GHS " .
            number_format($invoice->rate_per_student, 2) . "/student = GHS " .
            number_format($invoice->amount, 2)
        );
    }

    // ── Per-Student Invoice: Pay via Paystack ─────────────────────────────

    /**
     * Initialize Paystack payment for a per-student invoice.
     */
    public function payInvoice(PerStudentInvoice $invoice)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $tenant = TenantService::current();

        if (!$tenant || $invoice->tenant_id !== $tenant->id) {
            abort(403, 'This invoice does not belong to your school.');
        }

        if ($invoice->status === 'paid') {
            return back()->with('info', 'This invoice has already been paid.');
        }

        $paystack = app(PaystackService::class);
        $reference = PaystackService::generateReference('PSI');
        $callbackUrl = route('paystack.per-student.callback', ['reference' => $reference]);

        // Store the transaction
        PaystackTransaction::create([
            'type'        => 'per_student_invoice',
            'tenant_id'   => $tenant->id,
            'payer_email' => $tenant->admin_email,
            'payer_name'  => $tenant->admin_name,
            'reference'   => $reference,
            'amount'      => $invoice->amount,
            'description'  => "Per-Student Invoice {$invoice->invoice_number} — {$tenant->name} ({$invoice->student_count} students × GHS {$invoice->rate_per_student})",
            'status'      => 'pending',
            'metadata'    => json_encode([
                'type'           => 'per_student_invoice',
                'invoice_id'     => $invoice->id,
                'tenant_id'      => $tenant->id,
                'invoice_number' => $invoice->invoice_number,
                'student_count'  => $invoice->student_count,
                'rate'           => $invoice->rate_per_student,
            ]),
        ]);

        $result = $paystack->initializeTransaction(
            email:       $tenant->admin_email,
            amount:      $invoice->amount,
            reference:   $reference,
            callbackUrl: $callbackUrl,
            metadata: [
                'type'           => 'per_student_invoice',
                'invoice_id'     => $invoice->id,
                'tenant_id'      => $tenant->id,
                'invoice_number' => $invoice->invoice_number,
            ]
        );

        if (!$result['status']) {
            return back()->with('error', 'Could not connect to Paystack: ' . $result['message']);
        }

        return redirect($result['authorization_url']);
    }

    /**
     * Paystack callback after per-student invoice payment.
     */
    public function invoiceCallback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');
        if (!$reference) {
            return view('paystack.status', [
                'status'  => 'failed',
                'message' => 'Invalid callback — no reference provided.',
                'type'    => 'subscription',
            ]);
        }

        $tx = PaystackTransaction::where('reference', $reference)->first();
        if (!$tx) {
            return view('paystack.status', [
                'status'  => 'failed',
                'message' => 'Transaction not found.',
                'type'    => 'subscription',
            ]);
        }

        if ($tx->status === 'success') {
            return view('paystack.status', [
                'status'  => 'success',
                'message' => 'Invoice already paid.',
                'transaction' => $tx,
                'type'    => 'subscription',
            ]);
        }

        $paystack = app(PaystackService::class);
        $verification = $paystack->verifyTransaction($reference);

        if (!($verification['status'] ?? false) || !($verification['paid'] ?? false)) {
            $tx->update([
                'status'            => 'failed',
                'paystack_response' => json_encode($verification['data'] ?? []),
            ]);
            return view('paystack.status', [
                'status'  => 'failed',
                'message' => $verification['message'] ?? 'Payment was not successful.',
                'transaction' => $tx,
                'type'    => 'subscription',
            ]);
        }

        // Find the invoice from metadata
        $meta = [];
        try { $meta = json_decode($tx->metadata ?? '{}', true) ?? []; } catch (\Throwable $e) {}

        $invoiceId = $meta['invoice_id'] ?? null;
        $invoice = $invoiceId ? PerStudentInvoice::find($invoiceId) : null;

        if ($invoice) {
            // Mark invoice as paid
            $invoice->markAsPaid($reference);

            // Extend the school's subscription by one term
            $tenant = $invoice->tenant;
            if ($tenant) {
                $termsPerYear = $tenant->billing_terms_per_year ?? 3;
                $daysPerTerm  = (int) round(365 / $termsPerYear);

                $currentExpiry = $tenant->subscription_ends_at ?? now();
                $newExpiry = $currentExpiry > now()
                    ? $currentExpiry->addDays($daysPerTerm)
                    : now()->addDays($daysPerTerm);

                $tenant->update([
                    'subscription_ends_at' => $newExpiry,
                    'status'               => 'active',
                ]);

                // Record the payment
                \App\Models\OwnerPayment::on('pgsql')->create([
                    'tenant_id'    => $tenant->id,
                    'amount'       => $tx->amount,
                    'payment_date' => now(),
                    'description'  => "Per-Student Invoice {$invoice->invoice_number} ({$invoice->student_count} students × GHS {$invoice->rate_per_student})",
                ]);

                \App\Models\AuditLog::record(
                    'per_student_invoice_paid',
                    "Invoice {$invoice->invoice_number} paid: GHS {$tx->amount} — {$invoice->student_count} students × GHS {$invoice->rate_per_student}. Subscription extended to {$newExpiry->format('d M Y')}.",
                    $tenant->id
                );
            }
        }

        $tx->update([
            'status'            => 'success',
            'paystack_id'       => $verification['data']['id'] ?? null,
            'channel'           => $verification['data']['channel'] ?? null,
            'paystack_response' => json_encode($verification['data'] ?? []),
            'paid_at'           => now(),
        ]);

        return view('paystack.status', [
            'status'      => 'success',
            'message'     => $invoice
                ? "Invoice {$invoice->invoice_number} paid successfully! GHS " . number_format($tx->amount, 2) . " for {$invoice->student_count} students. Your subscription has been extended."
                : "Payment of GHS " . number_format($tx->amount, 2) . " received.",
            'transaction' => $tx->fresh(),
            'type'        => 'subscription',
        ]);
    }
}
