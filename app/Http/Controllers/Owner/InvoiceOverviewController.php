<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PerStudentInvoice;
use App\Models\Tenant;
use Illuminate\Http\Request;

class InvoiceOverviewController extends Controller
{
    /**
     * Manually mark an invoice as paid (owner override — no Paystack needed).
     */
    public function markPaid(Request $request, PerStudentInvoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice ' . $invoice->invoice_number . ' is already marked as paid.');
        }

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
            'paystack_reference' => 'MANUAL-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        ]);

        // Also make sure the tenant is not in suspended state due to this invoice
        $tenant = $invoice->tenant;
        if ($tenant && $tenant->status === 'suspended') {
            // Check if there are still other overdue invoices before unsuspending
            $stillOverdue = PerStudentInvoice::where('tenant_id', $tenant->id)
                ->where('id', '!=', $invoice->id)
                ->where(function($q) {
                    $q->where('status', 'overdue')
                      ->orWhere(function($q2) {
                          $q2->where('status', 'pending')
                             ->whereNotNull('due_date')
                             ->where('due_date', '<', now()->toDateString());
                      });
                })->exists();

            if (!$stillOverdue) {
                $tenant->update(['status' => 'active']);
            }
        }

        return back()->with('success',
            'Invoice ' . $invoice->invoice_number . ' for ' . ($tenant->name ?? 'school') . ' marked as paid. Access restored.'
        );
    }

    /**
     * Show all per-student invoices across all schools.
     */
    public function index(Request $request)
    {
        $query = PerStudentInvoice::with('tenant');

        // Filter by status
        $status = $request->get('status');
        if ($status && in_array($status, ['pending', 'paid', 'overdue'])) {
            if ($status === 'overdue') {
                $query->where('status', 'pending')
                      ->whereNotNull('due_date')
                      ->where('due_date', '<', now()->toDateString());
            } else {
                $query->where('status', $status);
            }
        }

        // Filter by school
        $schoolId = $request->get('school_id');
        if ($schoolId) {
            $query->where('tenant_id', $schoolId);
        }

        $invoices = $query->orderByDesc('created_at')->paginate(25);

        // Stats
        $allInvoices = PerStudentInvoice::all();
        $stats = [
            'total'     => $allInvoices->count(),
            'pending'   => $allInvoices->where('status', 'pending')->count(),
            'paid'      => $allInvoices->where('status', 'paid')->count(),
            'overdue'   => $allInvoices->where('status', 'pending')
                            ->filter(fn($i) => $i->due_date && $i->due_date < now()->toDateString())->count(),
            'total_amount_paid'   => $allInvoices->where('status', 'paid')->sum('amount'),
            'total_amount_pending' => $allInvoices->where('status', 'pending')->sum('amount'),
        ];

        // Schools on per-student billing for the filter dropdown
        $perStudentSchools = Tenant::where('billing_model', 'per_student')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('owner.invoices.index', compact(
            'invoices', 'stats', 'perStudentSchools', 'status', 'schoolId'
        ));
    }
}
