<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Student;
use App\Services\TermFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TermFeeBillController extends Controller
{
    public function __construct(private TermFeeService $feeService) {}

    /**
     * Term fee ledger — show all students and their fee status for a session.
     */
    public function index(Request $request)
    {
        $sessions       = AcademicSession::orderByDesc('year')->get();
        $selectedSession = $request->filled('session_id')
            ? AcademicSession::find($request->session_id)
            : $sessions->first();

        $summaries = collect();
        if ($selectedSession) {
            $owingOnly = $request->boolean('owing_only', false);
            $programId = $request->filled('program_id') ? (int) $request->program_id : null;

            $students = Student::with(['user', 'program'])
                ->where('status', 'active')
                ->when($programId, fn($q) => $q->where('program_id', $programId))
                ->get();

            $summaries = $students->map(fn($s) => $this->feeService->getStudentTermSummary($s, $selectedSession->id));

            if ($owingOnly) {
                $summaries = $summaries->filter(fn($s) => $s['is_owing'])->values();
            }
        }

        $programs = \App\Models\Program::orderBy('sequence')->get();

        return view('admin.fees.term-ledger', compact('sessions', 'selectedSession', 'summaries', 'programs'));
    }

    /**
     * Show fee history for a single student across all sessions.
     */
    public function studentHistory(Student $student)
    {
        $history = $this->feeService->getStudentFullHistory($student);
        return view('admin.fees.student-history', compact('student', 'history'));
    }

    /**
     * Bulk bill all students in the selected session from program fees.
     */
    public function bulkBill(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        $count = $this->feeService->bulkBillStudents(
            (int) $request->session_id,
            $request->filled('program_id') ? (int) $request->program_id : null
        );

        return back()->with('success', "{$count} students billed for this term.");
    }

    /**
     * Manually set fees for a specific student for a term.
     */
    public function storeBill(Request $request)
    {
        $validated = $request->validate([
            'student_id'       => 'required|exists:students,id',
            'session_id'       => 'required|exists:academic_sessions,id',
            'program_fee'      => 'required|numeric|min:0',
            'exam_fee'         => 'required|numeric|min:0',
            'other_fees'       => 'nullable|numeric|min:0',
            'other_fees_label' => 'nullable|string|max:100',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $this->feeService->billStudent(
            $student,
            $validated['session_id'],
            $validated['program_fee'],
            $validated['exam_fee'],
            $validated['other_fees'] ?? 0,
            $validated['other_fees_label'] ?? null
        );

        return back()->with('success', 'Fee bill saved successfully.');
    }

    /**
     * Record a payment for a student linked to a specific term.
     */
    public function recordPayment(Request $request)
    {
        $validated = $request->validate([
            'student_id'         => 'required|exists:students,id',
            'session_id'         => 'nullable|exists:academic_sessions,id',
            'amount_paid'        => 'required|numeric|min:0.01',
            'payment_date'       => 'required|date',
            'payment_method'     => 'required|string',
            'payer_name'         => 'nullable|string|max:255',
            'payer_phone'        => 'nullable|string|max:30',
            'notes'              => 'nullable|string',
        ]);

        $receiptNumber = TermFeeService::generateReceiptNumber();

        $paymentId = DB::table('payments')->insertGetId([
            'student_id'          => $validated['student_id'],
            'academic_session_id' => $validated['session_id'] ?? null,
            'receipt_number'      => $receiptNumber,
            'amount_paid'         => $validated['amount_paid'],
            'payment_date'        => $validated['payment_date'],
            'payment_method'      => $validated['payment_method'],
            'payer_name'          => $validated['payer_name'] ?? null,
            'payer_phone'         => $validated['payer_phone'] ?? null,
            'notes'               => $validated['notes'] ?? null,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return redirect()->route('admin.fees.receipt', ['paymentId' => $paymentId])
            ->with('success', "Payment recorded. Receipt: {$receiptNumber}");
    }

    /**
     * Show/download payment receipt.
     */
    public function receipt(int $paymentId)
    {
        $payment = DB::table('payments')->where('id', $paymentId)->first();
        if (!$payment) abort(404);

        $student  = Student::with(['user', 'program'])->find($payment->student_id);
        $session  = $payment->academic_session_id
            ? AcademicSession::find($payment->academic_session_id)
            : null;

        $schoolName = \App\Models\Setting::get('school_name', 'School');
        $schoolLogo = \App\Models\Setting::get('site_logo_url', null);

        return view('admin.fees.receipt', compact('payment', 'student', 'session', 'schoolName', 'schoolLogo'));
    }
}
