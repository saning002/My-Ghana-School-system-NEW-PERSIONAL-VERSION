<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class TermFeeService
{
    /**
     * Get full fee summary for a single student for a specific session.
     * Returns billed, paid, balance, and payment history for that term.
     */
    public function getStudentTermSummary(Student $student, int $sessionId): array
    {
        // Get the bill for this term
        $bill = DB::table('term_fee_bills')
            ->where('student_id', $student->id)
            ->where('academic_session_id', $sessionId)
            ->first();

        // If no specific bill, fall back to program fees
        $programFee = 0;
        $examFee    = 0;
        $otherFees  = 0;
        $otherLabel = null;
        $totalBilled = 0;

        if ($bill) {
            $programFee  = (float) $bill->program_fee;
            $examFee     = (float) $bill->exam_fee;
            $otherFees   = (float) $bill->other_fees;
            $otherLabel  = $bill->other_fees_label;
            $feedingFee  = (float) ($bill->feeding_fee ?? 0);
            $totalBilled = (float) $bill->total_billed;
        } else {
            // Auto-calculate from program fees
            $programFeeRow = DB::table('program_fees')
                ->where('program_id', $student->program_id)
                ->where(function($q) use ($student) {
                    $q->where('church_branch_id', $student->church_branch_id)
                      ->orWhereNull('church_branch_id');
                })
                ->orderByDesc('church_branch_id')
                ->first();

            if ($programFeeRow) {
                $programFee  = (float) $programFeeRow->amount;
                $examFee     = (float) $programFeeRow->exam_fee;
                $feedingFee  = (float) ($programFeeRow->feeding_fee ?? 0);
                $totalBilled = $programFee + $examFee + $feedingFee;
            }
        }

        // Get payments for this term
        $payments = DB::table('payments')
            ->where('student_id', $student->id)
            ->where(function($q) use ($sessionId) {
                $q->where('academic_session_id', $sessionId)
                  ->orWhereNull('academic_session_id'); // include unlinked payments
            })
            ->orderBy('payment_date')
            ->get();

        $totalPaid = $payments->sum('amount_paid');
        $balance   = $totalBilled - $totalPaid;

        return [
            'student'        => $student,
            'session_id'     => $sessionId,
            'program_fee'    => $programFee,
            'exam_fee'       => $examFee,
            'feeding_fee'    => $feedingFee,
            'other_fees'     => $otherFees,
            'other_label'    => $otherLabel,
            'total_billed'   => $totalBilled,
            'total_paid'     => $totalPaid,
            'balance'        => $balance,
            'is_owing'       => $balance > 0,
            'is_paid'        => $balance <= 0,
            'payments'       => $payments,
            'has_bill'       => $bill !== null,
        ];
    }

    /**
     * Get fee summaries for ALL students for a specific session.
     * Optionally filter to only owing students.
     */
    public function getAllStudentsSummary(int $sessionId, bool $owingOnly = false): \Illuminate\Support\Collection
    {
        $students = Student::with(['user', 'program'])
            ->where('status', 'active')
            ->get();

        $summaries = $students->map(function (Student $student) use ($sessionId) {
            return $this->getStudentTermSummary($student, $sessionId);
        });

        if ($owingOnly) {
            $summaries = $summaries->filter(fn($s) => $s['is_owing']);
        }

        return $summaries->values();
    }

    /**
     * Get ALL historical fee data for a student across ALL sessions.
     * Used for the full fee history view.
     */
    public function getStudentFullHistory(Student $student): array
    {
        $sessions = AcademicSession::orderByDesc('year')->get();
        $history  = [];
        $carryForward = 0;

        foreach ($sessions as $session) {
            $summary = $this->getStudentTermSummary($student, $session->id);
            $summary['carry_forward'] = $carryForward;
            $summary['net_balance']   = $summary['balance'] + $carryForward;
            $summary['session']       = $session;
            $history[] = $summary;
            $carryForward = max(0, $summary['net_balance']); // carry forward unpaid balance
        }

        return $history;
    }

    /**
     * Bill a student for a specific term.
     * Creates or updates the term_fee_bills record.
     */
    public function billStudent(Student $student, int $sessionId, float $programFee, float $examFee, float $otherFees = 0, ?string $otherLabel = null, float $feedingFee = 0): object
    {
        $total = $programFee + $examFee + $otherFees + $feedingFee;

        return (object) DB::table('term_fee_bills')->updateOrInsert(
            ['student_id' => $student->id, 'academic_session_id' => $sessionId],
            [
                'program_fee'      => $programFee,
                'exam_fee'         => $examFee,
                'feeding_fee'      => $feedingFee,
                'other_fees'       => $otherFees,
                'other_fees_label' => $otherLabel,
                'total_billed'     => $total,
                'updated_at'       => now(),
                'created_at'       => now(),
            ]
        );
    }

    /**
     * Bulk bill all students in a program/branch for a term.
     */
    public function bulkBillStudents(int $sessionId, ?int $programId = null, ?int $branchId = null): int
    {
        $query = Student::with(['program'])->where('status', 'active');
        if ($programId) $query->where('program_id', $programId);
        if ($branchId)  $query->where('church_branch_id', $branchId);

        $students = $query->get();
        $count    = 0;

        foreach ($students as $student) {
            $programFeeRow = DB::table('program_fees')
                ->where('program_id', $student->program_id)
                ->where(function($q) use ($student) {
                    $q->where('church_branch_id', $student->church_branch_id)
                      ->orWhereNull('church_branch_id');
                })
                ->orderByDesc('church_branch_id')
                ->first();

            if ($programFeeRow) {
                $this->billStudent(
                    $student,
                    $sessionId,
                    (float) $programFeeRow->amount,
                    (float) $programFeeRow->exam_fee,
                    0,
                    null,
                    (float) ($programFeeRow->feeding_fee ?? 0)
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generate a receipt number for a payment.
     */
    public static function generateReceiptNumber(): string
    {
        $year   = date('Y');
        $latest = DB::table('payments')
            ->whereYear('payment_date', $year)
            ->whereNotNull('receipt_number')
            ->max(DB::raw("CAST(SPLIT_PART(receipt_number, '-', 3) AS INTEGER)")) ?? 0;

        return 'RCP-' . $year . '-' . str_pad($latest + 1, 5, '0', STR_PAD_LEFT);
    }
}
