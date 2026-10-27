<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;
use App\Services\TermFeeService;
use Illuminate\Http\Request;
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

        // Get the school's own Paystack secret key
        $secretKey = Setting::get('school_paystack_secret_key', '');
        if (empty($secretKey)) {
            return back()->with('error', 'Online payment is not configured for this school. Please pay at the accounts office.');
        }

        $email    = $student->user?->email ?? $student->guardian_email ?? 'student@school.com';
        $reference = 'FEE-' . strtoupper(Str::random(8)) . '-' . time();
        $amountKobo = (int) round($amount * 100); // Paystack uses kobo (smallest unit)

        // Store pending transaction in tenant DB
        DB::table('payments')->insert([
            'student_id'          => $student->id,
            'amount_paid'         => $amount,
            'payment_date'        => now()->toDateString(),
            'payment_method'      => 'paystack',
            'academic_session_id' => $sessionId,
            'paystack_reference'  => $reference,
            'paystack_status'     => 'pending',
            'receipt_number'      => null, // will be set on success
            'notes'               => 'Online payment — pending Paystack confirmation',
            'payer_name'          => $student->user?->full_name ?? 'Student',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // Initialize Paystack transaction using the school's key
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

            // Failed — delete the pending record
            DB::table('payments')->where('paystack_reference', $reference)->delete();
            return back()->with('error', 'Could not connect to payment gateway. Please try again.');

        } catch (\Throwable $e) {
            DB::table('payments')->where('paystack_reference', $reference)->delete();
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

        // Find the pending payment
        $payment = DB::table('payments')
            ->where('paystack_reference', $reference)
            ->first();

        if (!$payment) {
            return redirect()->route('portal.dashboard')->with('error', 'Payment record not found.');
        }

        // Already processed
        if ($payment->paystack_status === 'success') {
            return redirect()->route('portal.dashboard')->with('success', 'Payment already confirmed. Receipt: ' . $payment->receipt_number);
        }

        // Verify with school's Paystack key
        $secretKey = Setting::get('school_paystack_secret_key', '');
        if (empty($secretKey)) {
            return redirect()->route('portal.dashboard')->with('error', 'Payment verification failed — contact the school office.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
            ])->get('https://api.paystack.co/transaction/verify/' . $reference);

            $data = $response->json();

            if (!$response->successful() || !($data['status'] ?? false)) {
                DB::table('payments')->where('paystack_reference', $reference)
                    ->update(['paystack_status' => 'failed', 'notes' => 'Verification failed', 'updated_at' => now()]);
                return redirect()->route('portal.dashboard')->with('error', 'Payment verification failed. Contact the school office with reference: ' . $reference);
            }

            $txData = $data['data'] ?? [];
            $status = $txData['status'] ?? 'failed';

            if ($status !== 'success') {
                DB::table('payments')->where('paystack_reference', $reference)
                    ->update(['paystack_status' => $status, 'notes' => 'Paystack status: ' . $status, 'updated_at' => now()]);
                return redirect()->route('portal.dashboard')->with('error', 'Payment was not successful. Status: ' . $status . '. Reference: ' . $reference);
            }

            // Generate receipt number
            $receiptNumber = 'RCP-' . date('Y') . '-' . strtoupper(Str::random(6));

            // Mark payment as successful
            DB::table('payments')->where('paystack_reference', $reference)->update([
                'paystack_status' => 'success',
                'paystack_channel'=> $txData['channel'] ?? null,
                'receipt_number'  => $receiptNumber,
                'notes'           => 'Online payment via ' . ($txData['channel'] ?? 'Paystack'),
                'updated_at'      => now(),
            ]);

            return redirect()->route('portal.dashboard')
                ->with('success', "Payment of GHS " . number_format($payment->amount_paid, 2) . " confirmed! Receipt: {$receiptNumber}");

        } catch (\Throwable $e) {
            Log::error('Student fee payment callback failed: ' . $e->getMessage());
            return redirect()->route('portal.dashboard')->with('error', 'Verification error. Contact the school with reference: ' . $reference);
        }
    }

    private function getPortalStudent(): ?Student
    {
        $id = Session::get('portal_student_id');
        if (!$id) return null;
        return Student::with('user')->find($id);
    }
}
