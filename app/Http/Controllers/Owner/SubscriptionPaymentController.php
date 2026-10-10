<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OwnerPayment;
use App\Models\PaystackTransaction;
use App\Models\Tenant;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class SubscriptionPaymentController extends Controller
{
    public function __construct(private PaystackService $paystack) {}

    /**
     * Show the subscription payment page for a tenant.
     * Owner sends this link to the school: /owner/schools/{tenant}/pay
     */
    public function showPaymentPage(Tenant $tenant)
    {
        $plan = $tenant->plan;
        return view('owner.payments.subscription-pay', compact('tenant', 'plan'));
    }

    /**
     * Initialize Paystack transaction for a subscription payment.
     * Called when school admin clicks "Pay Now" on their invoice.
     */
    public function initialize(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'months'      => 'required|integer|min:1|max:24',
            'payer_email' => 'required|email',
            'payer_name'  => 'nullable|string|max:255',
        ]);

        $months      = (int) $validated['months'];
        $planPrice   = $tenant->plan?->price ?? 0;
        $amount      = $planPrice * $months;

        if ($amount <= 0) {
            return back()->with('error', 'Could not calculate payment amount. Make sure the school has a plan assigned.');
        }

        $reference = PaystackService::generateReference('SUB');

        // Store pending transaction in central DB
        $transaction = PaystackTransaction::create([
            'type'        => 'subscription',
            'tenant_id'   => $tenant->id,
            'payer_email' => $validated['payer_email'],
            'payer_name'  => $validated['payer_name'] ?? $tenant->admin_name,
            'reference'   => $reference,
            'amount'      => $amount,
            'months_paid' => $months,
            'description' => "Subscription payment — {$tenant->name} — {$months} month(s)",
            'status'      => 'pending',
        ]);

        $result = $this->paystack->initializeTransaction(
            email:       $validated['payer_email'],
            amount:      $amount,
            reference:   $reference,
            callbackUrl: route('paystack.subscription.callback', ['reference' => $reference]),
            metadata:    [
                'type'      => 'subscription',
                'tenant_id' => $tenant->id,
                'school'    => $tenant->name,
                'months'    => $months,
                'tx_id'     => $transaction->id,
            ]
        );

        if (!$result['status']) {
            $transaction->update(['status' => 'failed']);
            return back()->with('error', 'Could not connect to Paystack: ' . $result['message']);
        }

        return redirect($result['authorization_url']);
    }

    /**
     * Paystack redirects here after payment attempt.
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (!$reference) {
            return redirect()->route('owner.dashboard')
                ->with('error', 'Invalid payment callback.');
        }

        $transaction = PaystackTransaction::where('reference', $reference)->first();

        if (!$transaction) {
            return view('paystack.status', [
                'status'  => 'failed',
                'message' => 'Transaction not found.',
                'type'    => 'subscription',
            ]);
        }

        // Already processed — don't double-process
        if ($transaction->isSuccess()) {
            return view('paystack.status', [
                'status'      => 'success',
                'message'     => 'Payment already confirmed.',
                'transaction' => $transaction,
                'type'        => 'subscription',
            ]);
        }

        // Verify with Paystack API
        $verification = $this->paystack->verifyTransaction($reference);

        if (!$verification['status'] || !$verification['paid']) {
            $transaction->update([
                'status'            => 'failed',
                'paystack_response' => json_encode($verification['data'] ?? []),
            ]);

            return view('paystack.status', [
                'status'      => 'failed',
                'message'     => $verification['message'] ?? 'Payment was not successful.',
                'transaction' => $transaction,
                'type'        => 'subscription',
            ]);
        }

        // Payment confirmed — record it
        $this->recordSubscriptionPayment($transaction, $verification);

        return view('paystack.status', [
            'status'      => 'success',
            'message'     => 'Subscription payment confirmed! Your access has been extended.',
            'transaction' => $transaction->fresh(),
            'type'        => 'subscription',
        ]);
    }

    // ─── Internal helpers ────────────────────────────────────────────────────────

    private function recordSubscriptionPayment(PaystackTransaction $tx, array $verification): void
    {
        // Create owner payment record
        $payment = OwnerPayment::create([
            'tenant_id'       => $tx->tenant_id,
            'receipt_number'  => OwnerPayment::generateReceiptNumber(),
            'amount'          => $tx->amount,
            'currency'        => 'GHS',
            'payment_date'    => now()->toDateString(),
            'payment_method'  => 'paystack',
            'reference'       => $tx->reference,
            'description'     => $tx->description,
            'months_paid'     => $tx->months_paid,
            'paystack_reference' => $tx->reference,
            'paystack_status' => 'success',
            'paystack_channel'=> $verification['channel'] ?? null,
            'paid_online'     => true,
        ]);

        // Extend subscription on tenant
        $tenant = $tx->tenant;
        $base   = ($tenant->subscription_ends_at && $tenant->subscription_ends_at->isFuture())
            ? $tenant->subscription_ends_at
            : now();

        $tenant->update([
            'subscription_ends_at' => $base->addMonths($tx->months_paid),
            'status'               => 'active',
        ]);

        // Mark transaction as done
        $tx->update([
            'status'           => 'success',
            'paystack_id'      => $verification['data']['id'] ?? null,
            'channel'          => $verification['channel'] ?? null,
            'paystack_response'=> json_encode($verification['data'] ?? []),
            'owner_payment_id' => $payment->id,
            'paid_at'          => now(),
        ]);

        AuditLog::record(
            'payment.paystack_subscription',
            "Online subscription payment GHS {$tx->amount} received from {$tenant->name}",
            $tenant->id
        );
    }
}
