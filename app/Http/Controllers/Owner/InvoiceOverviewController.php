<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PerStudentInvoice;
use App\Models\Tenant;
use Illuminate\Http\Request;

class InvoiceOverviewController extends Controller
{
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
