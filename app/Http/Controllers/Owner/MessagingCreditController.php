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
        ]);

        $credits = MessagingCredit::forTenant($tenant->id);
        $credits->addCredits($validated['sms_credits'], $validated['whatsapp_credits']);

        MessagingCreditPayment::create([
            'tenant_id'             => $tenant->id,
            'receipt_number'        => MessagingCreditPayment::generateReceiptNumber(),
            'amount'                => $validated['amount'],
            'sms_credits_added'     => $validated['sms_credits'],
            'whatsapp_credits_added'=> $validated['whatsapp_credits'],
            'payment_method'        => $validated['payment_method'],
            'status'                => 'completed',
            'notes'                 => $validated['notes'] ?? null,
        ]);

        AuditLog::record('messaging.credits_added', "Added {$validated['sms_credits']} SMS + {$validated['whatsapp_credits']} WA credits to {$tenant->name}", $tenant->id);

        return back()->with('success', "Credits added to {$tenant->name}.");
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
        ]);

        $package   = MessagingCreditPackage::findOrFail($validated['package_id']);
        $reference = PaystackService::generateReference('MSG');

        PaystackTransaction::create([
            'type'        => 'subscription', // reuse subscription type for central DB billing
            'tenant_id'   => $tenant->id,
            'payer_email' => $validated['payer_email'],
            'payer_name'  => $validated['payer_name'] ?? $tenant->admin_name,
            'reference'   => $reference,
            'amount'      => $package->price,
            'description' => "Messaging Credits: {$package->name} — {$tenant->name}",
            'status'      => 'pending',
            'metadata'    => json_encode([
                'type'       => 'messaging_credits',
                'package_id' => $package->id,
                'tenant_id'  => $tenant->id,
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
            return view('paystack.status', ['status' => 'failed', 'message' => 'Invalid callback.', 'type' => 'messaging']);
        }

        $tx = PaystackTransaction::where('reference', $reference)->first();
        if (!$tx) {
            return view('paystack.status', ['status' => 'failed', 'message' => 'Transaction not found.', 'type' => 'messaging']);
        }

        if ($tx->isSuccess()) {
            return view('paystack.status', ['status' => 'success', 'message' => 'Credits already applied.', 'transaction' => $tx, 'type' => 'messaging']);
        }

        $verification = $this->paystack->verifyTransaction($reference);

        if (!$verification['status'] || !$verification['paid']) {
            $tx->update(['status' => 'failed', 'paystack_response' => json_encode($verification['data'] ?? [])]);
            return view('paystack.status', ['status' => 'failed', 'message' => $verification['message'] ?? 'Payment not successful.', 'transaction' => $tx, 'type' => 'messaging']);
        }

        // Apply credits
        $meta    = $verification['data']['metadata'] ?? [];
        // Try local transaction metadata first, then Paystack response metadata
        if (is_string($meta)) {
            $meta = json_decode($meta, true) ?? [];
        }

        $packageId = $meta['package_id'] ?? null;
        $package   = $packageId ? MessagingCreditPackage::find($packageId) : null;

        // Fallback: try to find package from transaction description
        if (!$package && $tx->description) {
            // Description format: "Messaging Credits: {package name} — {tenant name}"
            preg_match('/Messaging Credits: (.+?) —/', $tx->description, $matches);
            if (!empty($matches[1])) {
                $package = MessagingCreditPackage::where('name', trim($matches[1]))->first();
            }
        }

        if (!$package) {
            // Last resort: find cheapest active package that matches the amount paid
            $package = MessagingCreditPackage::where('price', $tx->amount)->first();
        }

        if ($package && $tx->tenant_id) {
            $credits = MessagingCredit::on('pgsql')->forTenant($tx->tenant_id);
            $credits->addCredits($package->sms_credits, $package->whatsapp_credits);

            MessagingCreditPayment::create([
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
            ]);

            $tx->update([
                'status'            => 'success',
                'paystack_id'       => $verification['data']['id'] ?? null,
                'channel'           => $verification['channel'] ?? null,
                'paystack_response' => json_encode($verification['data'] ?? []),
                'paid_at'           => now(),
            ]);

            AuditLog::record('messaging.credits_purchased', "Paystack: {$package->name} purchased by {$tx->tenant->name}", $tx->tenant_id);

            return view('paystack.status', [
                'status'      => 'success',
                'message'     => "Credits added! SMS: +{$package->sms_credits} | WhatsApp: +{$package->whatsapp_credits}",
                'transaction' => $tx->fresh(),
                'type'        => 'messaging',
            ]);
        }

        // Package not found — still mark as success but flag for manual review
        $tx->update([
            'status'            => 'success',
            'paystack_id'       => $verification['data']['id'] ?? null,
            'paystack_response' => json_encode($verification['data'] ?? []),
            'paid_at'           => now(),
        ]);

        AuditLog::record('messaging.credits_paid_no_package', "Payment received GHS {$tx->amount} but package not found — manual credit needed", $tx->tenant_id);

        return view('paystack.status', [
            'status'      => 'success',
            'message'     => "Payment received (GHS {$tx->amount}). Credits will be applied within 24 hours. Contact support if not resolved.",
            'transaction' => $tx->fresh(),
            'type'        => 'messaging',
        ]);
    }

    // ── Receipt ────────────────────────────────────────────────────────────

    public function receipt(MessagingCreditPayment $payment)
    {
        $payment->load('tenant', 'package');
        return view('owner.messaging.receipt', compact('payment'));
    }
}
