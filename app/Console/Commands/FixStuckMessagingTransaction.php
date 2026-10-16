<?php

namespace App\Console\Commands;

use App\Models\MessagingCredit;
use App\Models\MessagingCreditPackage;
use App\Models\MessagingCreditPayment;
use App\Models\PaystackTransaction;
use App\Services\PaystackService;
use Illuminate\Console\Command;

class FixStuckMessagingTransaction extends Command
{
    protected $signature   = 'messaging:fix-stuck {reference}';
    protected $description = 'Re-process a stuck messaging credit transaction by reference';

    public function __construct(private PaystackService $paystack)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $reference = $this->argument('reference');

        $tx = PaystackTransaction::where('reference', $reference)->first();
        if (!$tx) {
            $this->error("Transaction not found: {$reference}");
            return 1;
        }

        $this->info("Transaction: {$tx->reference}");
        $this->info("Status:      {$tx->status}");
        $this->info("Amount:      GHS {$tx->amount}");
        $this->info("Description: {$tx->description}");
        $this->info("Tenant ID:   {$tx->tenant_id}");

        if ($tx->status === 'success') {
            // Check if credits were actually applied
            $payment = MessagingCreditPayment::where('paystack_reference', $reference)->first();
            if ($payment) {
                $this->info("Credits already applied — payment record exists (receipt: {$payment->receipt_number}).");
                return 0;
            }
            $this->warn("Transaction marked success but no credit payment record found. Will apply credits.");
        }

        // Verify with Paystack
        $this->info("Verifying with Paystack...");
        $verification = $this->paystack->verifyTransaction($reference);

        if (!($verification['status'] ?? false) || !($verification['paid'] ?? false)) {
            $this->error("Paystack says NOT paid: " . ($verification['message'] ?? 'unknown'));
            $tx->update(['status' => 'failed']);
            return 1;
        }

        $this->info("Paystack confirmed: PAID.");

        // ── Package lookup (same 4 strategies as callback) ────────────────

        $package = null;

        // 1. Paystack metadata
        $paystackMeta = $verification['data']['metadata'] ?? [];
        if (is_string($paystackMeta)) {
            $paystackMeta = json_decode($paystackMeta, true) ?? [];
        }
        if (!empty($paystackMeta['package_id'])) {
            $package = MessagingCreditPackage::find((int) $paystackMeta['package_id']);
            if ($package) $this->info("Package found via Paystack metadata: {$package->name}");
        }

        // 2. Local transaction metadata
        if (!$package) {
            $localMeta = json_decode($tx->metadata ?? '{}', true) ?? [];
            if (!empty($localMeta['package_id'])) {
                $package = MessagingCreditPackage::find((int) $localMeta['package_id']);
                if ($package) $this->info("Package found via local metadata: {$package->name}");
            }
        }

        // 3. Description string
        if (!$package && !empty($tx->description)) {
            if (preg_match('/Messaging Credits:\s*(.+?)\s*[—\-]{1,3}\s*.+/u', $tx->description, $m)) {
                $package = MessagingCreditPackage::where('name', trim($m[1]))->first();
                if ($package) $this->info("Package found via description: {$package->name}");
            }
        }

        // 4. Price match
        if (!$package && $tx->amount) {
            $package = MessagingCreditPackage::where('price', $tx->amount)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first();
            if ($package) $this->info("Package found via price match (GHS {$tx->amount}): {$package->name}");
        }

        // 5. Manual selection as last resort
        if (!$package) {
            $this->warn("Could not auto-detect package.");
            $packages = MessagingCreditPackage::orderBy('sort_order')->get();
            $this->table(['ID', 'Name', 'SMS', 'WhatsApp', 'Price'], $packages->map(fn($p) => [
                $p->id, $p->name, $p->sms_credits, $p->whatsapp_credits, "GHS {$p->price}"
            ])->toArray());
            $packageId = $this->ask('Enter the package ID to apply (or press Enter to skip credits)');
            if ($packageId) {
                $package = MessagingCreditPackage::find((int) $packageId);
            }
        }

        if (!$package) {
            $this->warn("No package selected. Marking transaction as success without applying credits.");
            $tx->update([
                'status'            => 'success',
                'paystack_id'       => $verification['data']['id'] ?? null,
                'paystack_response' => json_encode($verification['data'] ?? []),
                'paid_at'           => now(),
            ]);
            return 0;
        }

        if (!$tx->tenant_id) {
            $this->error("Transaction has no tenant_id — cannot apply credits automatically.");
            return 1;
        }

        // Apply credits
        $credits = MessagingCredit::forTenant($tx->tenant_id);
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
            'channel'           => $verification['data']['channel'] ?? null,
            'paystack_response' => json_encode($verification['data'] ?? []),
            'paid_at'           => now(),
        ]);

        $this->info("✓ Credits applied: SMS +{$package->sms_credits} | WhatsApp +{$package->whatsapp_credits}");
        $this->info("✓ Transaction marked success.");

        return 0;
    }
}
