<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;
use App\Services\TermFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Handles online fee payment via Paystack from the student portal.
 * Uses the school's own Paystack keys stored in the tenant settings table.
 * Money goes directly to the school's Paystack account — not the platform.
 */
class FeePaymentController extends Controller
{
    public function __construct(private TermFeeService $feeService) {}

    /**
     * Initiate a Paystack payment for outstanding fees.
     */
    public function initiate(Request $request)
    {
        $student = $this->getPortalStudent();
        if (!$student) {
            return redirect()->route('portal.login')->with('error', 'Please log in first.');
        }

        $request->validate([
            'amount'     => 'required|numeric|min:1',
            'session_id' => 'required|exists:academic_sessions,id',
        ]);

        $amount    = (float) $request->amount;
        $sessionId = (int) $request->session_id;
        $tenantId  = session('tenant_id');

        $secretKey = Setting::get('school_paystack_secret_key', '');
        if (empty($secretKey)) {
            return back()->with('error', 'Online payment is not configured for this school. Please pay at the accounts office.');
        }

        $email     = $student->user?->email ?? $student->guardian_email ?? 'student@school.com';
        $reference = 'FEE-' . strtoupper(Str::random(8)) . '-' . time();
        $amountKobo = (int) round($amount * 100);

        // ── DO NOT insert into payments yet ─────────────────────────────────
        // A pending DB record would show in payment history even if the student
        // cancels or closes the browser. Store in session instead — the actual
        // payment record is only written in callback() after Paystack confirms.
        session([
            'pending_fee_payment' => [
                'reference'  => $reference,
                'student_id' => $student->id,
                'amount'     => $amount,
                'session_id' => $sessionId,
                'tenant_id'  => $tenantId,
                'payer_name' => $student->user?->full_name ?? 'Student',
            ]
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type'  => 'application/json',
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email'        => $email,
                'amount'       => $amountKobo,
                'reference'    => $reference,
                'currency'     => 'GHS',
                'callback_url' => route('portal.fees.callback', ['reference' => $reference]),
                'metadata'     => [
                    'student_id'   => $student->id,
                    'student_name' => $student->user?->full_name,
                    'student_no'   => $student->student_id,
                    'session_id'   => $sessionId,
                    'tenant_id'    => $tenantId,
                    'cancel_action'=> route('portal.dashboard'),
                ],
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? false)) {
                return redirect($data['data']['authorization_url']);
            }

            // Paystack rejected — clear session
            session()->forget('pending_fee_payment');
            return back()->with('error', 'Could not connect to payment gateway. Please try again.');

        } catch (\Throwable $e) {
            session()->forget('pending_fee_payment');
            Log::error('Student fee payment initiation failed: ' . $e->getMessage());
            return back()->with('error', 'Payment could not be initiated. Please try again or pay at the office.');
        }
    }

    /**
     * Handle Paystack callback after student fee payment.
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (!$reference) {
            return redirect()->route('portal.dashboard')->with('error', 'Invalid payment reference.');
        }

        // Get pending payment details from session (not from DB — not inserted yet)
        $pending = session('pending_fee_payment');

        // If session expired, try to find it in DB (edge case: session lost after redirect)
        $existingPayment = DB::table('payments')->where('paystack_reference', $reference)->first();
        if ($existingPayment && $existingPayment->paystack_status === 'success') {
            session()->forget('pending_fee_payment');
            return redirect()->route('portal.dashboard')
                ->with('success', 'Payment already confirmed. Receipt: ' . $existingPayment->receipt_number);
        }

        // Verify with school's Paystack key
        $secretKey = Setting::get('school_paystack_secret_key', '');
        if (empty($secretKey)) {
            return redirect()->route('portal.dashboard')
                ->with('error', 'Payment verification failed — contact the school office.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
            ])->get('https://api.paystack.co/transaction/verify/' . $reference);

            $data   = $response->json();
            $txData = $data['data'] ?? [];
            $status = $txData['status'] ?? 'failed';

            if (!$response->successful() || !($data['status'] ?? false) || $status !== 'success') {
                session()->forget('pending_fee_payment');
                return redirect()->route('portal.dashboard')
                    ->with('error', 'Payment was not completed. Status: ' . $status . '. Reference: ' . $reference);
            }

            // ── Payment confirmed by Paystack — NOW insert the record ────────
            $receiptNumber = 'RCP-' . date('Y') . '-' . strtoupper(Str::random(6));

            // Use session data if available, otherwise fall back to Paystack metadata
            $meta      = $txData['metadata'] ?? [];
            $studentId = $pending['student_id'] ?? ($meta['student_id'] ?? null);
            $sessionId = $pending['session_id'] ?? ($meta['session_id'] ?? null);
            $amount    = $pending['amount']     ?? ($txData['amount'] / 100);
            $payerName = $pending['payer_name'] ?? ($meta['student_name'] ?? 'Student');

            if ($studentId) {
                DB::table('payments')->insert([
                    'student_id'          => $studentId,
                    'amount_paid'         => $amount,
                    'payment_date'        => now()->toDateString(),
                    'payment_method'      => 'paystack',
                    'academic_session_id' => $sessionId,
                    'paystack_reference'  => $reference,
                    'paystack_status'     => 'success',
                    'paystack_channel'    => $txData['channel'] ?? null,
                    'receipt_number'      => $receiptNumber,
                    'payer_name'          => $payerName,
                    'notes'               => 'Online payment via ' . ($txData['channel'] ?? 'Paystack'),
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            session()->forget('pending_fee_payment');

            return redirect()->route('portal.payment-confirmed', [
                'receipt'   => $receiptNumber,
                'amount'    => number_format($amount, 2),
                'channel'   => $txData['channel'] ?? 'Paystack',
                'reference' => $reference,
            ]);

        } catch (\Throwable $e) {
            Log::error('Student fee payment callback failed: ' . $e->getMessage());
            return redirect()->route('portal.dashboard')->with('error', 'Verification error. Contact the school with reference: ' . $reference);
        }
    }

    /**
     * Show the payment confirmation/success page.
     */
    public function confirmed(Request $request)
    {
        $student = $this->getPortalStudent();
        if (!$student) {
            return redirect()->route('portal.login');
        }

        // If arrived here without params (direct URL), redirect to dashboard
        if (!$request->filled('receipt') && !$request->filled('reference')) {
            return redirect()->route('portal.dashboard');
        }

        $receipt   = $request->query('receipt');
        $amount    = $request->query('amount');
        $channel   = $request->query('channel', 'Paystack');
        $reference = $request->query('reference');

        // Load school branding for the page
        $schoolName    = \App\Models\Setting::get('school_name', config('app.name'));
        $schoolSubtitle= \App\Models\Setting::get('school_subtitle', '');
        $siteLogoUrl   = \App\Models\Setting::get('site_logo_url', '');
        $logoSrc       = $siteLogoUrl
            ? (filter_var($siteLogoUrl, FILTER_VALIDATE_URL) ? $siteLogoUrl : Storage::disk('public')->url($siteLogoUrl))
            : null;
        $portalTheme   = \App\Models\Setting::get('portal_theme', 'gold');

        return view('portal.payment-confirmed', compact(
            'student', 'receipt', 'amount', 'channel', 'reference',
            'schoolName', 'schoolSubtitle', 'logoSrc', 'portalTheme'
        ));
    }

    private function getPortalStudent(): ?Student
    {
        $id = Session::get('portal_student_id');
        if (!$id) return null;
        return Student::with('user')->find($id);
    }
}
