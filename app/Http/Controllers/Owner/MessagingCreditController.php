<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MessagingCredit;
use App\Models\MessagingCreditPackage;
use App\Models\MessagingCreditPayment;
use App\Models\PaystackTransaction;
use App\Models\Tenant;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class MessagingCreditController extends Controller
{
    public function __construct(private PaystackService $paystack) {}

    // ── Owner panel: manage packages ────────────────────────────────────────

    public function packages()
    {
        $packages = MessagingCreditPackage::orderBy('sort_order')->get();
        return view('owner.messaging.packages', compact('packages'));
    }

    public function storePackage(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'sms_credits'      => 'required|integer|min:0',
            'whatsapp_credits' => 'required|integer|min:0',
            'price'            => 'required|numeric|min:0',
            'description'      => 'nullable|string',
            'is_active'        => 'nullable|boolean',
            'sort_order'       => 'nullable|integer',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        MessagingCreditPackage::create($validated);
        AuditLog::record('messaging.package_created', "Package '{$validated['name']}' created");
        return back()->with('success', "Package '{$validated['name']}' created.");
    }

    public function updatePackage(Request $request, MessagingCreditPackage $package)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'sms_credits'      => 'required|integer|min:0',
            'whatsapp_credits' => 'required|integer|min:0',
            'price'            => 'required|numeric|min:0',
            'description'      => 'nullable|string',
            'is_active'        => 'nullable|boolean',
            'sort_order'       => 'nullable|integer',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $package->update($validated);
        return back()->with('success', "Package updated.");
    }

    public function destroyPackage(MessagingCreditPackage $package)
    {
        $package->delete();
        return back()->with('success', 'Package deleted.');
    }

    // ── Owner panel: view all schools' credit balances ──────────────────────

    public function allCredits()
    {
        $tenants  = Tenant::with('messagingCredit')->orderBy('name')->get();
        $packages = MessagingCreditPackage::active()->get();
        return view('owner.messaging.credits', compact('tenants', 'packages'));
    }

    // ── Owner panel: manually add credits to a school ──────────────────────

    public function addCredits(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'sms_credits'      => 'required|integer|min:0',
            'whatsapp_credits' => 'required|integer|min:0',
            'notes'            => 'nullable|string',
            'payment_method'   => 'required|in:cash,mobile_money,bank_transfer,card,paystack,other',
            'amount'           => 'required|numeric|min:0',
            'branch_id'        => 'nullable|integer', // null = school-wide shared pool
        ]);

        // Get the right credit pool — branch-specific or shared
        $branchId = $validated['branch_id'] ? (int) $validated['branch_id'] : null;
        if ($branchId) {
            $credits = \App\Models\MessagingCredit::forBranchDedicated($tenant->id, $branchId);
        } else {
            $credits = \App\Models\MessagingCredit::forTenant($tenant->id);
        }

        $credits->addCredits($validated['sms_credits'], $validated['whatsapp_credits']);

        // Get branch name for audit log
        $branchLabel = 'shared pool';
        if ($branchId) {
            try {
                $b = \App\Models\ChurchBranch::find($branchId);
                $branchLabel = $b?->name ?? "branch #{$branchId}";
            } catch (\Throwable $e) {}
        }

        MessagingCreditPayment::create([
            'tenant_id'              => $tenant->id,
            'receipt_number'         => MessagingCreditPayment::generateReceiptNumber(),
            'amount'                 => $validated['amount'],
            'sms_credits_added'      => $validated['sms_credits'],
            'whatsapp_credits_added' => $validated['whatsapp_credits'],
            'payment_method'         => $validated['payment_method'],
            'status'                 => 'completed',
            'notes'                  => ($validated['notes'] ?? '') . ($branchId ? " [Branch: {$branchLabel}]" : ''),
        ]);

        AuditLog::record(
            'messaging.credits_added',
            "Added {$validated['sms_credits']} SMS + {$validated['whatsapp_credits']} WA credits to {$tenant->name} ({$branchLabel})",
            $tenant->id
        );

        // Alert owner if Arkesel balance is low relative to what was just allocated
        $this->checkAndAlertArkeselBalance((int) $validated['sms_credits'], $tenant->name);

        return back()->with('success', "Credits added to {$tenant->name} ({$branchLabel}).");
    }

    // ── School-facing: buy credits page ────────────────────────────────────

    public function buyCredits(Tenant $tenant)
    {
        $packages = MessagingCreditPackage::active()->get();
        $credits  = MessagingCredit::forTenant($tenant->id);
        $history  = MessagingCreditPayment::where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')->limit(10)->get();

        return view('owner.messaging.buy-credits', compact('tenant', 'packages', 'credits', 'history'));
    }

    // ── Paystack: initialize credit purchase ───────────────────────────────

    public function initializePurchase(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'package_id'  => 'required|exists:messaging_credit_packages,id',
            'payer_email' => 'required|email',
            'payer_name'  => 'nullable|string|max:255',
            'branch_id'   => 'nullable|integer',
        ]);

        $package   = MessagingCreditPackage::findOrFail($validated['package_id']);
        $reference = PaystackService::generateReference('MSG');
        $branchId  = $validated['branch_id'] ?? null;

        PaystackTransaction::create([
            'type'        => 'messaging_credits',
            'tenant_id'   => $tenant->id,
            'payer_email' => $validated['payer_email'],
            'payer_name'  => $validated['payer_name'] ?? $tenant->admin_name,
            'reference'   => $reference,
            'amount'      => $package->price,
            'description' => "Messaging Credits: {$package->name} — {$tenant->name}",
            'status'      => 'pending',
            'metadata'    => json_encode([
                'type'             => 'messaging_credits',
                'package_id'       => $package->id,
                'tenant_id'        => $tenant->id,
                'branch_id'        => $branchId,
                'sms_credits'      => $package->sms_credits,
                'whatsapp_credits' => $package->whatsapp_credits,
            ]),
        ]);

        $result = $this->paystack->initializeTransaction(
            email:       $validated['payer_email'],
            amount:      $package->price,
            reference:   $reference,
            callbackUrl: route('paystack.messaging.callback', ['reference' => $reference]),
            metadata:    [
                'type'             => 'messaging_credits',
                'package_id'       => $package->id,
                'tenant_id'        => $tenant->id,
                'sms_credits'      => $package->sms_credits,
                'whatsapp_credits' => $package->whatsapp_credits,
            ]
        );

        if (!$result['status']) {
            return back()->with('error', 'Could not connect to Paystack: ' . $result['message']);
        }

        return redirect($result['authorization_url']);
    }

    // ── Paystack: callback after credit purchase ───────────────────────────

    public function callback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');
        if (!$reference) {
            return view('paystack.status', [
                'status'  => 'failed',
                'message' => 'Invalid callback — no reference provided.',
                'type'    => 'messaging',
            ]);
        }

        $tx = PaystackTransaction::where('reference', $reference)->first();
        if (!$tx) {
            return view('paystack.status', [
                'status'  => 'failed',
                'message' => 'Transaction not found.',
                'type'    => 'messaging',
            ]);
        }

        // Already processed — show success without re-running
        if ($tx->status === 'success') {
            return view('paystack.status', [
                'status'      => 'success',
                'message'     => 'Credits already applied to your account.',
                'transaction' => $tx,
                'type'        => 'messaging',
            ]);
        }

        // Verify with Paystack
        $verification = $this->paystack->verifyTransaction($reference);

        if (!($verification['status'] ?? false) || !($verification['paid'] ?? false)) {
            $tx->update([
                'status'            => 'failed',
                'paystack_response' => json_encode($verification['data'] ?? []),
            ]);
            return view('paystack.status', [
                'status'      => 'failed',
                'message'     => $verification['message'] ?? 'Payment was not successful.',
                'transaction' => $tx,
                'type'        => 'messaging',
            ]);
        }

        // ── Package lookup: 4 strategies, most reliable first ─────────────

        $package = null;

        // 1. From Paystack's own metadata in the verification response
        $paystackMeta = $verification['data']['metadata'] ?? [];
        if (is_string($paystackMeta)) {
            $paystackMeta = json_decode($paystackMeta, true) ?? [];
        }
        if (!empty($paystackMeta['package_id'])) {
            $package = MessagingCreditPackage::find((int) $paystackMeta['package_id']);
        }

        // 2. From the local transaction's stored metadata column
        if (!$package) {
            $localMeta = [];
            try {
                $localMeta = json_decode($tx->metadata ?? '{}', true) ?? [];
            } catch (\Throwable $e) {
                $localMeta = [];
            }
            if (!empty($localMeta['package_id'])) {
                $package = MessagingCreditPackage::find((int) $localMeta['package_id']);
            }
        }

        // 3. Parse from transaction description: "Messaging Credits: {Name} — {School}"
        if (!$package && !empty($tx->description)) {
            // handle both em-dash (—) and regular dash (-)
            if (preg_match('/Messaging Credits:\s*(.+?)\s*[—\-]{1,3}\s*.+/u', $tx->description, $m)) {
                $package = MessagingCreditPackage::where('name', trim($m[1]))->first();
            }
        }

        // 4. Match by exact price paid
        if (!$package && $tx->amount) {
            $package = MessagingCreditPackage::where('price', $tx->amount)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first();
        }

        // ── Apply credits if package found ────────────────────────────────

        if ($package && $tx->tenant_id) {
            // Get branch_id from metadata — apply to branch pool if specified
            $branchId = (int) ($localMeta['branch_id'] ?? $paystackMeta['branch_id'] ?? 0) ?: null;

            // ── Check Arkesel balance before activating ───────────────────
            $arkeselBalance   = $this->fetchArkeselBalance();
            $hasEnoughBalance = $arkeselBalance === null // Can't check — activate anyway
                || $arkeselBalance >= $package->sms_credits;

            if ($hasEnoughBalance) {
                // ── INSTANT ACTIVATION — balance is sufficient ────────────
                if ($branchId) {
                    $credits = MessagingCredit::forBranchDedicated($tx->tenant_id, $branchId);
                } else {
                    $credits = MessagingCredit::forTenant($tx->tenant_id);
                }
                $credits->addCredits($package->sms_credits, $package->whatsapp_credits);

                $payment = MessagingCreditPayment::create([
                    'tenant_id'              => $tx->tenant_id,
                    'package_id'             => $package->id,
                    'receipt_number'         => MessagingCreditPayment::generateReceiptNumber(),
                    'amount'                 => $tx->amount,
                    'sms_credits_added'      => $package->sms_credits,
                    'whatsapp_credits_added' => $package->whatsapp_credits,
                    'payment_method'         => 'paystack',
                    'paystack_reference'     => $reference,
                    'paystack_status'        => 'success',
                    'status'                 => 'completed',
                    'credits_pending'        => false,
                    'activated_at'           => now(),
                ]);

                $tx->update([
                    'status'            => 'success',
                    'paystack_id'       => $verification['data']['id'] ?? null,
                    'channel'           => $verification['data']['channel'] ?? null,
                    'paystack_response' => json_encode($verification['data'] ?? []),
                    'paid_at'           => now(),
                ]);

                AuditLog::record(
                    'messaging.credits_purchased',
                    "Paystack: {$package->name} purchased by {$tx->tenant->name} — SMS: +{$package->sms_credits}, WA: +{$package->whatsapp_credits} [INSTANT]",
                    $tx->tenant_id
                );

                // Still alert owner if balance is now running low after this allocation
                $this->checkAndAlertArkeselBalance(
                    (int) $package->sms_credits,
                    $tx->tenant->name ?? 'Unknown School'
                );

                return view('paystack.status', [
                    'status'      => 'success',
                    'message'     => "Credits added successfully! SMS: +{$package->sms_credits} | WhatsApp: +{$package->whatsapp_credits}",
                    'transaction' => $tx->fresh(),
                    'type'        => 'messaging',
                    'pending'     => false,
                ]);

            } else {
                // ── PENDING ACTIVATION — Arkesel balance too low ─────────
                // Payment is recorded fully. Credits are NOT added yet.
                // Owner is alerted urgently. School sees a warm message.

                $payment = MessagingCreditPayment::create([
                    'tenant_id'              => $tx->tenant_id,
                    'package_id'             => $package->id,
                    'receipt_number'         => MessagingCreditPayment::generateReceiptNumber(),
                    'amount'                 => $tx->amount,
                    'sms_credits_added'      => $package->sms_credits,
                    'whatsapp_credits_added' => $package->whatsapp_credits,
                    'payment_method'         => 'paystack',
                    'paystack_reference'     => $reference,
                    'paystack_status'        => 'success',
                    'status'                 => 'pending_activation',
                    'credits_pending'        => true,
                    'activated_at'           => null,
                ]);

                $tx->update([
                    'status'            => 'success',
                    'paystack_id'       => $verification['data']['id'] ?? null,
                    'channel'           => $verification['data']['channel'] ?? null,
                    'paystack_response' => json_encode($verification['data'] ?? []),
                    'paid_at'           => now(),
                ]);

                AuditLog::record(
                    'messaging.credits_pending',
                    "Paystack: {$package->name} purchased by {$tx->tenant->name} — PENDING ACTIVATION (Arkesel balance: {$arkeselBalance}, needed: {$package->sms_credits})",
                    $tx->tenant_id
                );

                // Send URGENT alert to owner
                $this->sendUrgentPendingAlert(
                    $tx->tenant->name ?? 'Unknown School',
                    $package->sms_credits,
                    (float) $arkeselBalance,
                    $payment->id,
                    $tx->amount
                );

                // Warm message to school — no mention of Arkesel at all
                return view('paystack.status', [
                    'status'      => 'success',
                    'message'     => "Thank you! Your payment of GHS " . number_format($tx->amount, 2) . " has been received successfully. Your messaging credits are being set up and will be ready within a few hours. We will notify you as soon as they are active.",
                    'transaction' => $tx->fresh(),
                    'type'        => 'messaging',
                    'pending'     => true,
                ]);
            }
        }

        // ── Package not found — mark paid, flag for manual review ─────────

        $tx->update([
            'status'            => 'success',
            'paystack_id'       => $verification['data']['id'] ?? null,
            'paystack_response' => json_encode($verification['data'] ?? []),
            'paid_at'           => now(),
        ]);

        AuditLog::record(
            'messaging.credits_paid_no_package',
            "Payment GHS {$tx->amount} received (ref: {$reference}) but package not found — manual credit needed",
            $tx->tenant_id
        );

        return view('paystack.status', [
            'status'      => 'success',
            'message'     => "Payment of GHS {$tx->amount} received. Credits will be applied to your account within 24 hours. If not resolved, contact support with reference: {$reference}",
            'transaction' => $tx->fresh(),
            'type'        => 'messaging',
        ]);
    }

    // ── Private: fetch Arkesel balance ────────────────────────────────────

    /**
     * Returns the current Arkesel SMS credit balance as a float,
     * or null if the API key is not configured or the request fails.
     */
    private function fetchArkeselBalance(): ?float
    {
        $apiKey = env('ARKESEL_API_KEY', '');
        if (empty($apiKey)) {
            return null; // Not configured — treat as sufficient
        }

        try {
            $res = \Illuminate\Support\Facades\Http::timeout(8)
                ->withHeaders(['api-key' => $apiKey])
                ->get('https://sms.arkesel.com/api/v2/clients/balance-details');

            if (!$res->successful()) {
                return null;
            }

            $data = $res->json();
            $bal  = $data['data']['sms_balance']
                 ?? $data['data']['balance']
                 ?? $data['sms_balance']
                 ?? null;

            return $bal !== null ? (float) $bal : null;

        } catch (\Throwable $e) {
            \Log::warning('Arkesel balance fetch failed: ' . $e->getMessage());
            return null; // Can't check — don't block the sale
        }
    }

    // ── Private: send urgent pending alert to owner ────────────────────────

    private function sendUrgentPendingAlert(
        string $schoolName,
        int    $smsRequested,
        float  $arkeselBalance,
        int    $paymentId,
        float  $amountPaid
    ): void {
        try {
            $owner = \App\Models\OwnerUser::first();
            if (!$owner?->email) {
                return;
            }

            $activateUrl = url('/owner/messaging/credits/activate/' . $paymentId);

            $subject = '🚨 URGENT: School paid for SMS credits — your Arkesel balance is insufficient';

            $html = "
            <html><body style='font-family:Arial,sans-serif;font-size:14px;color:#1e293b;max-width:600px;margin:0 auto;padding:24px;'>
                <div style='background:#dc2626;color:#fff;border-radius:12px;padding:20px 24px;margin-bottom:24px;'>
                    <h2 style='margin:0;font-size:20px;'>🚨 Action Required — SMS Credits Pending</h2>
                    <p style='margin:8px 0 0;opacity:.85;font-size:13px;'>A school has paid for credits but they cannot be activated yet.</p>
                </div>

                <table style='width:100%;border-collapse:collapse;margin-bottom:24px;'>
                    <tr style='background:#f8fafc;'>
                        <td style='padding:10px 14px;font-weight:700;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:1px;width:40%;'>School</td>
                        <td style='padding:10px 14px;font-weight:700;color:#0f172a;'>{$schoolName}</td>
                    </tr>
                    <tr>
                        <td style='padding:10px 14px;font-weight:700;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:1px;'>Amount Paid</td>
                        <td style='padding:10px 14px;font-weight:700;color:#16a34a;'>GHS " . number_format($amountPaid, 2) . "</td>
                    </tr>
                    <tr style='background:#f8fafc;'>
                        <td style='padding:10px 14px;font-weight:700;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:1px;'>SMS Credits Purchased</td>
                        <td style='padding:10px 14px;font-weight:700;color:#0f172a;'>" . number_format($smsRequested) . " credits</td>
                    </tr>
                    <tr>
                        <td style='padding:10px 14px;font-weight:700;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:1px;'>Your Arkesel Balance</td>
                        <td style='padding:10px 14px;font-weight:700;color:#dc2626;'>" . number_format($arkeselBalance) . " credits (insufficient)</td>
                    </tr>
                </table>

                <div style='background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;padding:16px 20px;margin-bottom:24px;'>
                    <p style='margin:0;font-size:13px;color:#92400e;'>
                        <strong>What to do:</strong><br>
                        1. Go to <a href='https://arkesel.com/login' style='color:#d97706;font-weight:bold;'>Arkesel.com</a> and top up your account with at least <strong>" . number_format($smsRequested) . " credits</strong>.<br>
                        2. Once topped up, come back and click the button below to activate the school's credits.<br>
                        3. The school will receive their credits and can start sending immediately.
                    </p>
                </div>

                <div style='text-align:center;margin-bottom:24px;'>
                    <a href='{$activateUrl}'
                       style='display:inline-block;background:#16a34a;color:#fff;padding:14px 32px;border-radius:10px;text-decoration:none;font-weight:800;font-size:15px;'>
                        ✅ Activate Credits for {$schoolName}
                    </a>
                </div>

                <p style='font-size:11px;color:#94a3b8;text-align:center;border-top:1px solid #f1f5f9;padding-top:16px;'>
                    The school has been told their credits will be ready within a few hours.<br>
                    Payment ID: #{$paymentId} · Sent automatically by your School Management System.
                </p>
            </body></html>";

            \Illuminate\Support\Facades\Mail::html(
                $html,
                fn($m) => $m->to($owner->email, $owner->name ?? 'Owner')
                            ->subject($subject)
            );

            \Log::info("Urgent pending alert sent to {$owner->email} for {$schoolName} payment #{$paymentId}");

        } catch (\Throwable $e) {
            \Log::warning('Urgent pending alert email failed: ' . $e->getMessage());
        }
    }

    // ── Private: check Arkesel balance and alert owner if low ─────────────

    private function checkAndAlertArkeselBalance(int $smsCreditsJustAllocated, string $schoolName): void
    {
        try {
            $balance = $this->fetchArkeselBalance();
            if ($balance === null) return;

            $threshold        = (int) env('LOW_SMS_BALANCE_THRESHOLD', 200);
            $isBelowRequest   = $balance < $smsCreditsJustAllocated;
            $isBelowThreshold = $balance <= $threshold;

            if (!$isBelowRequest && !$isBelowThreshold) return;

            $owner = \App\Models\OwnerUser::first();
            if (!$owner?->email) return;

            $subject = $isBelowRequest
                ? "⚠️ URGENT: Arkesel balance too low to cover {$schoolName}'s request"
                : "⚠️ Low Arkesel SMS Balance — Top Up Required";

            $colour = $isBelowRequest ? '#dc2626' : '#d97706';
            $topupBtn = "<a href='https://arkesel.com/login' style='background:{$colour};color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>→ Top Up on Arkesel</a>";

            $body = $isBelowRequest
                ? "Credits just allocated: <strong>{$smsCreditsJustAllocated}</strong> to <strong>{$schoolName}</strong>.<br>"
                  . "Your Arkesel balance: <strong>" . number_format($balance) . "</strong> credits.<br><br>"
                  . "⚠️ Balance is lower than what was allocated. Top up immediately.<br><br>{$topupBtn}"
                : "Your Arkesel balance has dropped to <strong>" . number_format($balance) . "</strong> credits "
                  . "(threshold: {$threshold}).<br>Last allocation: <strong>{$smsCreditsJustAllocated}</strong> to {$schoolName}.<br><br>{$topupBtn}";

            \Illuminate\Support\Facades\Mail::html(
                "<html><body style='font-family:Arial,sans-serif;font-size:14px;color:#1e293b;padding:24px;'>"
                . "<h2 style='color:{$colour};'>{$subject}</h2><p>{$body}</p>"
                . "<p style='font-size:11px;color:#94a3b8;margin-top:24px;'>Sent automatically by your School Management System.</p>"
                . "</body></html>",
                fn($m) => $m->to($owner->email, $owner->name ?? 'Owner')->subject($subject)
            );

        } catch (\Throwable $e) {
            \Log::warning('Arkesel balance alert failed: ' . $e->getMessage());
        }
    }

    // ── Owner panel: activate pending credits ─────────────────────────────

    /**
     * Manually activate a pending credit payment.
     * Called from the owner panel after topping up Arkesel.
     */
    public function activatePending(MessagingCreditPayment $payment)
    {
        if (!$payment->credits_pending) {
            return back()->with('info', 'These credits are already active.');
        }

        try {
            $credits = \App\Models\MessagingCredit::forTenant($payment->tenant_id);
            $credits->addCredits($payment->sms_credits_added, $payment->whatsapp_credits_added);

            $payment->update([
                'credits_pending' => false,
                'status'          => 'completed',
                'activated_at'    => now(),
                'activated_by'    => auth()->id(),
            ]);

            AuditLog::record(
                'messaging.credits_activated',
                "Manually activated {$payment->sms_credits_added} SMS credits for {$payment->tenant->name} (payment #{$payment->id})"
            );

            return back()->with('success',
                "✅ {$payment->sms_credits_added} SMS credits activated for {$payment->tenant->name}. They can now send messages."
            );

        } catch (\Throwable $e) {
            \Log::error('Credit activation failed: ' . $e->getMessage());
            return back()->with('error', 'Activation failed: ' . $e->getMessage());
        }
    }

    public function receipt(MessagingCreditPayment $payment)
    {
        $payment->load('tenant', 'package');
        return view('owner.messaging.receipt', compact('payment'));
    }
}
