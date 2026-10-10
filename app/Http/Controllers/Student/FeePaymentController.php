<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaystackTransaction;
use App\Models\Student;
use App\Services\FeeCalculationService;
use App\Services\PaystackService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeePaymentController extends Controller
{
    public function __construct(
        private PaystackService      $paystack,
        private FeeCalculationService $feeService
    ) {}

    /**
     * Show the online fee payment page.
     * Accessible from the student portal dashboard.
     */
    public function showPayPage()
    {
        $student = Auth::guard('web') ->user()?->student
                ?? $this->getPortalStudent();

        if (!$student) {
            return redirect()->route('portal.login')
                ->with('error', 'Please log in to pay fees.');
        }

        $feeSummary = $this->feeService->calculate($student);
        $balance    = $feeSummary['balance'];

        return view('student.fee-payment', compact('student', 'feeSummary', 'balance'));
    }

    /**
     * Initialize Paystack transaction for school fee payment.
     */
    public function initialize(Request $request)
    {
        $student = $this->getPortalStudent();

        if (!$student) {
            return redirect()->route('portal.login')
                ->with('error', 'Session expired. Please log in again.');
        }

        $validated = $request->validate([
            'amount'      => 'required|numeric|min:1',
            'email'       => 'required|email',
            'payer_name'  => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $amount    = (float) $validated['amount'];
        $reference = PaystackService::generateReference('FEE');
        $tenant    = TenantService::current();

        // Store pending transaction in central DB
        PaystackTransaction::create([
            'type'         => 'school_fee',
            'tenant_id'    => $tenant?->id,
            'student_id'   => $student->id,
            'student_name' => $student->user?->full_name ?? 'Student',
            'payer_email'  => $validated['email'],
            'payer_name'   => $validated['payer_name'] ?? $student->user?->full_name,
            'reference'    => $reference,
            'amount'       => $amount,
            'description'  => $validated['description'] ?? 'School fee payment — ' . ($student->user?->full_name ?? $student->student_id),
            'status'       => 'pending',
        ]);

        $result = $this->paystack->initializeTransaction(
            email:       $validated['email'],
            amount:      $amount,
            reference:   $reference,
            callbackUrl: route('paystack.fee.callback', ['reference' => $reference]),
            metadata:    [
                'type'       => 'school_fee',
                'student_id' => $student->id,
                'tenant_id'  => $tenant?->id,
                'tenant_slug'=> $tenant?->slug,
                'student_no' => $student->student_id,
            ]
        );

        if (!$result['status']) {
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
            return view('paystack.status', [
                'status'  => 'failed',
                'message' => 'Invalid payment callback — no reference.',
                'type'    => 'school_fee',
            ]);
        }

        $transaction = PaystackTransaction::where('reference', $reference)->first();

        if (!$transaction) {
            return view('paystack.status', [
                'status'  => 'failed',
                'message' => 'Transaction not found.',
                'type'    => 'school_fee',
            ]);
        }

        // Already processed
        if ($transaction->isSuccess()) {
            return view('paystack.status', [
                'status'      => 'success',
                'message'     => 'Payment already confirmed.',
                'transaction' => $transaction,
                'type'        => 'school_fee',
            ]);
        }

        // Verify with Paystack
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
                'type'        => 'school_fee',
            ]);
        }

        // Payment confirmed — record in the tenant's DB
        $this->recordFeePayment($transaction, $verification);

        return view('paystack.status', [
            'status'      => 'success',
            'message'     => 'Fee payment confirmed! Your payment has been recorded.',
            'transaction' => $transaction->fresh(),
            'type'        => 'school_fee',
        ]);
    }

    // ─── Internal helpers ────────────────────────────────────────────────────────

    private function recordFeePayment(PaystackTransaction $tx, array $verification): void
    {
        try {
            // If tenant context is set, switch to tenant DB to record the payment
            $tenant = $tx->tenant;
            if ($tenant) {
                DB::purge('tenant');
                \Illuminate\Support\Facades\Config::set(
                    'database.connections.tenant',
                    $tenant->dbConfig()
                );
                DB::setDefaultConnection('tenant');
            }

            // Record payment in the school's payments table
            $payment = DB::connection('tenant')->table('payments')->insertGetId([
                'student_id'          => $tx->student_id,
                'amount_paid'         => $tx->amount,
                'payment_date'        => now()->toDateString(),
                'notes'               => 'Online payment via Paystack — Ref: ' . $tx->reference,
                'payment_method'      => 'paystack',
                'paystack_reference'  => $tx->reference,
                'paystack_status'     => 'success',
                'paystack_channel'    => $verification['channel'] ?? null,
                'transaction_id'      => $verification['data']['id'] ?? null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // Reset to central DB
            DB::setDefaultConnection('pgsql');
            DB::purge('tenant');

            // Update the central transaction record
            $tx->update([
                'status'             => 'success',
                'paystack_id'        => $verification['data']['id'] ?? null,
                'channel'            => $verification['channel'] ?? null,
                'paystack_response'  => json_encode($verification['data'] ?? []),
                'school_payment_id'  => $payment,
                'paid_at'            => now(),
            ]);

        } catch (\Throwable $e) {
            Log::error('recordFeePayment failed: ' . $e->getMessage());
            DB::setDefaultConnection('pgsql');
        }
    }

    private function getPortalStudent(): ?Student
    {
        // Student portal uses session-based auth stored in 'portal_student_id'
        $studentId = session('portal_student_id');
        if (!$studentId) return null;

        try {
            return Student::with('user')->find($studentId);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
