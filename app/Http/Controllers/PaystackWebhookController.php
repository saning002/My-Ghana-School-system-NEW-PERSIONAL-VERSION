<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\OwnerPayment;
use App\Models\PaystackTransaction;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PaystackWebhookController
 * --------------------------
 * Handles server-to-server webhook events from Paystack.
 * This is the most reliable way to confirm payments — it runs
 * independently of the browser redirect (callback), so even if
 * the user closes the browser, payments are still confirmed.
 *
 * Webhook URL to register in Paystack dashboard:
 *   https://yourdomain.com/paystack/webhook
 *
 * Paystack sends:
 *   - charge.success  → payment confirmed
 *   - charge.failed   → payment failed
 *   - transfer.success, transfer.failed (if you use transfers)
 */
class PaystackWebhookController extends Controller
{
    public function __construct(private PaystackService $paystack) {}

    public function handle(Request $request)
    {
        // 1. Verify the webhook signature
        $signature = $request->header('x-paystack-signature');
        $payload   = $request->getContent();

        if (!$this->paystack->verifyWebhook($payload, $signature ?? '')) {
            Log::warning('Paystack webhook: invalid signature');
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $data  = $request->input('data', []);

        Log::info("Paystack webhook received: {$event}", ['reference' => $data['reference'] ?? 'N/A']);

        // 2. Route to the correct handler
        match($event) {
            'charge.success'   => $this->handleChargeSuccess($data),
            'charge.failed'    => $this->handleChargeFailed($data),
            default            => null, // Ignore other events
        };

        // Always return 200 immediately so Paystack stops retrying
        return response()->json(['message' => 'Webhook received'], 200);
    }

    // ─── Event Handlers ─────────────────────────────────────────────────────────

    private function handleChargeSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;
        if (!$reference) return;

        $transaction = PaystackTransaction::where('reference', $reference)->first();

        if (!$transaction) {
            Log::warning("Paystack webhook: no transaction found for reference {$reference}");
            return;
        }

        // Idempotency — skip if already processed
        if ($transaction->isSuccess()) {
            Log::info("Paystack webhook: transaction {$reference} already processed");
            return;
        }

        $amountGhs = ($data['amount'] ?? 0) / 100;
        $channel   = $data['channel'] ?? null;
        $paystackId= $data['id'] ?? null;

        // Update transaction record
        $transaction->update([
            'status'            => 'success',
            'paystack_id'       => $paystackId,
            'channel'           => $channel,
            'paystack_response' => json_encode($data),
            'paid_at'           => now(),
        ]);

        // Route to the correct post-payment action
        if ($transaction->type === 'subscription') {
            $this->processSubscriptionPayment($transaction, $amountGhs, $channel, $data);
        } elseif ($transaction->type === 'school_fee') {
            $this->processFeePayment($transaction, $amountGhs, $channel, $data);
        }
    }

    private function handleChargeFailed(array $data): void
    {
        $reference = $data['reference'] ?? null;
        if (!$reference) return;

        PaystackTransaction::where('reference', $reference)
            ->where('status', 'pending')
            ->update([
                'status'            => 'failed',
                'paystack_response' => json_encode($data),
            ]);

        Log::info("Paystack webhook: charge failed for {$reference}");
    }

    // ─── Post-payment processors ─────────────────────────────────────────────────

    private function processSubscriptionPayment(
        PaystackTransaction $tx,
        float $amount,
        ?string $channel,
        array $data
    ): void {
        try {
            // Skip if already has an owner payment linked
            if ($tx->owner_payment_id) return;

            $payment = OwnerPayment::create([
                'tenant_id'          => $tx->tenant_id,
                'receipt_number'     => OwnerPayment::generateReceiptNumber(),
                'amount'             => $amount,
                'currency'           => 'GHS',
                'payment_date'       => now()->toDateString(),
                'payment_method'     => 'paystack',
                'reference'          => $tx->reference,
                'description'        => $tx->description,
                'months_paid'        => $tx->months_paid,
                'paystack_reference' => $tx->reference,
                'paystack_status'    => 'success',
                'paystack_channel'   => $channel,
                'paid_online'        => true,
            ]);

            // Extend tenant subscription
            $tenant = $tx->tenant;
            if ($tenant) {
                $base = ($tenant->subscription_ends_at && $tenant->subscription_ends_at->isFuture())
                    ? $tenant->subscription_ends_at
                    : now();

                $tenant->update([
                    'subscription_ends_at' => $base->addMonths($tx->months_paid),
                    'status'               => 'active',
                ]);
            }

            $tx->update(['owner_payment_id' => $payment->id]);

            AuditLog::record(
                'payment.webhook_subscription',
                "Webhook: subscription GHS {$amount} confirmed for " . ($tenant->name ?? 'unknown'),
                $tx->tenant_id
            );

        } catch (\Throwable $e) {
            Log::error('Webhook processSubscriptionPayment failed: ' . $e->getMessage());
        }
    }

    private function processFeePayment(
        PaystackTransaction $tx,
        float $amount,
        ?string $channel,
        array $data
    ): void {
        try {
            // Skip if already recorded
            if ($tx->school_payment_id) return;

            $tenant = $tx->tenant;
            if (!$tenant) {
                Log::warning("Webhook fee payment: tenant not found for tx {$tx->reference}");
                return;
            }

            // Switch to tenant's DB
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');

            $paymentId = DB::connection('tenant')->table('payments')->insertGetId([
                'student_id'         => $tx->student_id,
                'amount_paid'        => $amount,
                'payment_date'       => now()->toDateString(),
                'notes'              => 'Online payment via Paystack (webhook) — Ref: ' . $tx->reference,
                'payment_method'     => 'paystack',
                'paystack_reference' => $tx->reference,
                'paystack_status'    => 'success',
                'paystack_channel'   => $channel,
                'transaction_id'     => $data['id'] ?? null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // Reset DB
            DB::setDefaultConnection('pgsql');
            DB::purge('tenant');

            $tx->update(['school_payment_id' => $paymentId]);

            Log::info("Webhook: fee payment GHS {$amount} recorded for student {$tx->student_id} in tenant {$tenant->name}");

        } catch (\Throwable $e) {
            Log::error('Webhook processFeePayment failed: ' . $e->getMessage());
            DB::setDefaultConnection('pgsql');
        }
    }
}
