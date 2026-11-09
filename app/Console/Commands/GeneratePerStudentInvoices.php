<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\PerStudentInvoice;
use Illuminate\Support\Facades\Mail;

class GeneratePerStudentInvoices extends Command
{
    protected $signature = 'billing:generate-invoices';
    protected $description = 'Generate per-student invoices for all schools on per-student billing at term start. Also marks overdue invoices.';

    public function handle(): int
    {
        $this->info('Checking schools on per-student billing...');

        // Get all schools on per_student billing
        $schools = Tenant::where('billing_model', 'per_student')
            ->where('status', 'active')
            ->get();

        if ($schools->isEmpty()) {
            $this->info('No schools on per-student billing. Done.');
            return self::SUCCESS;
        }

        $generated = 0;
        $skipped = 0;

        foreach ($schools as $tenant) {
            $this->line("  Checking: {$tenant->name}");

            try {
                // Check if there's already a pending or paid invoice for this term
                // (same student count + same rate = likely same term)
                $existing = PerStudentInvoice::forTenant($tenant->id)
                    ->whereIn('status', ['pending', 'paid'])
                    ->latest()
                    ->first();

                if ($existing) {
                    // Check if the active student count has changed significantly (more than 10%)
                    // If it hasn't changed much, don't generate a new invoice
                    $currentCount = $tenant->countActiveStudents();
                    $lastCount = $existing->student_count;

                    if ($currentCount > 0 && abs($currentCount - $lastCount) <= max(5, round($lastCount * 0.1))) {
                        $this->line("    ↳ Skipped — invoice already exists for this term ({$lastCount} students, now {$currentCount})");
                        $skipped++;
                        continue;
                    }
                }

                // Generate new invoice
                $invoice = PerStudentInvoice::generateForTerm($tenant);

                if ($invoice) {
                    $this->line("    ✅ Generated: {$invoice->invoice_number} — {$invoice->student_count} students × GHS {$invoice->rate_per_student} = GHS {$invoice->amount}");
                    $generated++;

                    // Send email notification to the school admin
                    $this->notifySchool($tenant, $invoice);
                } else {
                    $this->line("    ↳ No invoice generated (no active students or billing not configured)");
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $this->error("    ❌ Error: {$e->getMessage()}");
                \Log::error("Invoice generation failed for {$tenant->name}: " . $e->getMessage());
            }
        }

        // Mark overdue invoices
        $this->info('Checking for overdue invoices...');
        try {
            $overdue = PerStudentInvoice::where('status', 'pending')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now()->toDateString())
                ->get();

            foreach ($overdue as $inv) {
                $inv->update(['status' => 'overdue']);
                $this->line("  ⚠️ Marked overdue: {$inv->invoice_number} ({$inv->tenant?->name}) — was due {$inv->due_date?->format('d M Y')}");
            }

            if ($overdue->isEmpty()) {
                $this->info('  No overdue invoices found.');
            }
        } catch (\Throwable $e) {
            $this->error("  Could not check overdue: {$e->getMessage()}");
        }

        $this->info("Done! Generated: {$generated}, Skipped: {$skipped}");
        return self::SUCCESS;
    }

    /**
     * Send email notification to the school admin when invoice is generated.
     */
    private function notifySchool(Tenant $tenant, PerStudentInvoice $invoice): void
    {
        try {
            if (empty($tenant->admin_email)) {
                return;
            }

            $payUrl = url('/admin/subscription');

            $html = "
            <html><body style='font-family:Arial,sans-serif;font-size:14px;color:#1e293b;max-width:600px;margin:0 auto;padding:24px;'>
                <h2 style='color:#0f172a;'>New Term Invoice — {$tenant->name}</h2>
                <p>Your term invoice has been generated and is ready for payment.</p>

                <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                    <tr style='background:#f8fafc;'>
                        <td style='padding:10px 14px;font-weight:700;color:#64748b;font-size:12px;text-transform:uppercase;'>Invoice Number</td>
                        <td style='padding:10px 14px;font-weight:700;color:#0f172a;'>{$invoice->invoice_number}</td>
                    </tr>
                    <tr>
                        <td style='padding:10px 14px;font-weight:700;color:#64748b;font-size:12px;text-transform:uppercase;'>Term</td>
                        <td style='padding:10px 14px;'>{$invoice->term_label}</td>
                    </tr>
                    <tr style='background:#f8fafc;'>
                        <td style='padding:10px 14px;font-weight:700;color:#64748b;font-size:12px;text-transform:uppercase;'>Active Students</td>
                        <td style='padding:10px 14px;font-weight:700;'>{$invoice->student_count}</td>
                    </tr>
                    <tr>
                        <td style='padding:10px 14px;font-weight:700;color:#64748b;font-size:12px;text-transform:uppercase;'>Rate per Student</td>
                        <td style='padding:10px 14px;'>GHS " . number_format($invoice->rate_per_student, 2) . "</td>
                    </tr>
                    <tr style='background:#f0fdf4;'>
                        <td style='padding:12px 14px;font-weight:800;color:#16a34a;font-size:14px;text-transform:uppercase;'>Amount Due</td>
                        <td style='padding:12px 14px;font-weight:900;color:#16a34a;font-size:18px;'>GHS " . number_format($invoice->amount, 2) . "</td>
                    </tr>
                    <tr>
                        <td style='padding:10px 14px;font-weight:700;color:#64748b;font-size:12px;text-transform:uppercase;'>Due Date</td>
                        <td style='padding:10px 14px;color:#d97706;font-weight:700;'>" . ($invoice->due_date?->format('d M Y') ?? '14 days from now') . "</td>
                    </tr>
                </table>

                <div style='text-align:center;margin:24px 0;'>
                    <a href='{$payUrl}' style='display:inline-block;background:#16a34a;color:#fff;padding:14px 32px;border-radius:10px;text-decoration:none;font-weight:800;font-size:15px;'>
                        Pay Now via Paystack
                    </a>
                </div>

                <p style='font-size:12px;color:#94a3b8;margin-top:24px;'>
                    Log in to your school admin panel and go to <strong>Subscription</strong> to view and pay this invoice.
                    Payment can be made via Card, Mobile Money, or Bank Transfer.
                </p>
            </body></html>";

            Mail::html($html, function ($mail) use ($tenant, $invoice) {
                $mail->to($tenant->admin_email, $tenant->admin_name)
                     ->subject("New Invoice {$invoice->invoice_number} — {$tenant->name} — GHS " . number_format($invoice->amount, 2));
            });

            $this->line("    📧 Email sent to {$tenant->admin_email}");
        } catch (\Throwable $e) {
            $this->error("    Could not send email: {$e->getMessage()}");
        }
    }
}
