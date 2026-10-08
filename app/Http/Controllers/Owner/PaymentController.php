<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OwnerPayment;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = OwnerPayment::with('tenant')->orderByDesc('payment_date');

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }
        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->to);
        }

        $payments    = $query->paginate(25)->withQueryString();
        $totalAmount = $query->sum('amount');
        $schools     = Tenant::orderBy('name')->get(['id', 'name']);

        return view('owner.payments.index', compact('payments', 'totalAmount', 'schools'));
    }

    public function create(Request $request)
    {
        $schools       = Tenant::orderBy('name')->get(['id', 'name', 'plan_id']);
        $selectedTenant = $request->filled('tenant_id')
            ? Tenant::find($request->tenant_id)
            : null;

        return view('owner.payments.create', compact('schools', 'selectedTenant'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id'      => 'required|exists:tenants,id',
            'amount'         => 'required|numeric|min:0.01',
            'currency'       => 'required|string|max:10',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,mobile_money,bank_transfer,card,other',
            'reference'      => 'nullable|string|max:255',
            'description'    => 'nullable|string|max:500',
            'months_paid'    => 'required|integer|min:1',
            'notes'          => 'nullable|string',
        ]);

        $validated['receipt_number'] = OwnerPayment::generateReceiptNumber();

        $payment = OwnerPayment::create($validated);

        // Extend subscription automatically
        $this->extendSubscription($payment);

        // Generate PDF receipt
        $this->generateReceipt($payment);

        AuditLog::record(
            'payment.created',
            "Payment GHS {$payment->amount} recorded for {$payment->tenant->name} — {$payment->receipt_number}",
            $payment->tenant_id
        );

        return redirect()->route('owner.payments.show', $payment)
            ->with('success', "Payment recorded. Receipt: {$payment->receipt_number}");
    }

    public function show(OwnerPayment $payment)
    {
        $payment->load('tenant');
        return view('owner.payments.show', compact('payment'));
    }

    public function receipt(OwnerPayment $payment)
    {
        $payment->load('tenant');

        // Re-generate if missing
        if (!$payment->receipt_path || !Storage::exists($payment->receipt_path)) {
            $this->generateReceipt($payment);
            $payment->refresh();
        }

        if ($payment->receipt_path && Storage::exists($payment->receipt_path)) {
            return response()->file(
                Storage::path($payment->receipt_path),
                ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="' . $payment->receipt_number . '.pdf"']
            );
        }

        // Fallback: render as HTML
        return view('owner.payments.receipt-print', compact('payment'));
    }

    public function emailReceipt(Request $request, OwnerPayment $payment)
    {
        $payment->load('tenant');

        // Re-generate receipt if needed
        if (!$payment->receipt_path || !Storage::exists($payment->receipt_path)) {
            $this->generateReceipt($payment);
            $payment->refresh();
        }

        try {
            Mail::send('owner.emails.payment-receipt', compact('payment'), function ($mail) use ($payment) {
                $mail->to($payment->tenant->admin_email, $payment->tenant->admin_name)
                     ->subject("Payment Receipt {$payment->receipt_number} — {$payment->tenant->name}");

                if ($payment->receipt_path && Storage::exists($payment->receipt_path)) {
                    $mail->attach(Storage::path($payment->receipt_path), [
                        'as'   => $payment->receipt_number . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
                }
            });

            $payment->update([
                'receipt_emailed'    => true,
                'receipt_emailed_at' => now(),
            ]);

            AuditLog::record('payment.receipt_emailed', "Receipt {$payment->receipt_number} emailed to {$payment->tenant->admin_email}", $payment->tenant_id);

            return back()->with('success', "Receipt emailed to {$payment->tenant->admin_email}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function destroy(OwnerPayment $payment)
    {
        $receipt = $payment->receipt_number;
        $tenantId = $payment->tenant_id;

        if ($payment->receipt_path && Storage::exists($payment->receipt_path)) {
            Storage::delete($payment->receipt_path);
        }

        $payment->delete();

        AuditLog::record('payment.deleted', "Payment {$receipt} deleted", $tenantId);

        return redirect()->route('owner.payments.index')
            ->with('success', "Payment {$receipt} deleted.");
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function extendSubscription(OwnerPayment $payment): void
    {
        $tenant = $payment->tenant;
        $months = $payment->months_paid;

        // Base from today if expired/no subscription, or from existing end date
        $base = ($tenant->subscription_ends_at && $tenant->subscription_ends_at->isFuture())
            ? $tenant->subscription_ends_at
            : now();

        $tenant->update([
            'subscription_ends_at' => $base->addMonths($months),
            'status'               => 'active',
        ]);
    }

    private function generateReceipt(OwnerPayment $payment): void
    {
        try {
            $html = view('owner.payments.receipt-pdf', ['payment' => $payment])->render();

            // Use DomPDF if available
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf  = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a5', 'portrait');
                $path = 'owner/receipts/' . $payment->receipt_number . '.pdf';
                Storage::put($path, $pdf->output());
                $payment->update(['receipt_path' => $path]);
            }
            // Otherwise leave receipt_path null — fallback HTML view is used
        } catch (\Exception $e) {
            // Non-fatal — receipt can still be viewed as HTML
            \Log::warning("Receipt PDF generation failed: " . $e->getMessage());
        }
    }
}
