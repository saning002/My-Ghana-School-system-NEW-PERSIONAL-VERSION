<?php

namespace App\Services;

use App\Models\Student;
use App\Models\ProgramFee;
use App\Models\StudentFee;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class FeeCalculationService
{
    /**
     * Calculate the fee summary for a student.
     *
     * If the student has a custom fee set, use that.
     * Otherwise, total fees are the sum of ProgramFee amounts for all programs
     * the student has been enrolled in (current + historical via promotion history).
     * Missing ProgramFee records are treated as zero.
     *
     * @param  Student  $student
     * @return array{total: float, paid: float, balance: float}
     */
    public function calculate(Student $student): array
    {
        try {
            // Check if student has a custom fee set (safely)
            try {
                $student->loadMissing('customFee');
                if ($student->customFee) {
                    $total = (float) $student->customFee->amount + (float) $student->customFee->exam_fee;
                    $paid = Payment::where('student_id', $student->id)->sum('amount_paid');
                    $balance = $total - $paid;

                    return [
                        'program_total' => (float) $student->customFee->amount,
                        'exam_total'    => (float) $student->customFee->exam_fee,
                        'total'         => (float) $total,
                        'paid'          => (float) $paid,
                        'balance'       => (float) $balance,
                    ];
                }
            } catch (\Throwable $e) {
                // student_fees table may not exist yet - continue with program fees calculation
            }

            // Collect all program IDs the student has been through
            $programIds = collect([$student->program_id]);

            try {
                if (DB::connection()->getSchemaBuilder()->hasTable('promotion_histories')) {
                    $student->loadMissing('promotionHistory');
                    if (isset($student->promotionHistory)) {
                        foreach ($student->promotionHistory as $history) {
                            $programIds->push($history->from_program_id);
                            if ($history->to_program_id) {
                                $programIds->push($history->to_program_id);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // promotionHistory table might not exist or DB connection may be unavailable
            }

            $programIds = $programIds->unique()->filter();

            try {
                $programFees = ProgramFee::whereIn('program_id', $programIds)
                    ->where(function ($query) use ($student) {
                        $query->where('church_branch_id', $student->church_branch_id)
                              ->orWhereNull('church_branch_id');
                    })
                    ->get()
                    ->groupBy('program_id');
            } catch (\Throwable $e) {
                // If we can't query ProgramFee, just return zero fees
                return [
                    'program_total' => 0.0,
                    'exam_total'    => 0.0,
                    'total'         => 0.0,
                    'paid'          => 0.0,
                    'balance'       => 0.0,
                ];
            }

            $programTotal = 0.0;
            $examTotal = 0.0;

            foreach ($programIds as $programId) {
                $feeSet = $programFees[$programId] ?? collect();
                $fee = $feeSet->firstWhere('church_branch_id', $student->church_branch_id)
                    ?? $feeSet->firstWhere('church_branch_id', null);

                if ($fee) {
                    $programTotal += (float) $fee->amount;
                    $examTotal += (float) $fee->exam_fee;
                }
            }

            $total = $programTotal + $examTotal;

            try {
                $paid  = Payment::where('student_id', $student->id)->sum('amount_paid');
            } catch (\Throwable $e) {
                $paid = 0.0;
            }

            $balance = $total - $paid;

            return [
                'program_total' => (float) $programTotal,
                'exam_total'    => (float) $examTotal,
                'total'         => (float) $total,
                'paid'          => (float) $paid,
                'balance'       => (float) $balance,
            ];
        } catch (\Throwable $e) {
            // Last resort - return zeros
            \Log::error('FeeCalculationService::calculate() failed: ' . $e->getMessage(), [
                'student_id' => $student->id ?? null,
                'exception' => $e,
            ]);
            return [
                'program_total' => 0.0,
                'exam_total'    => 0.0,
                'total'         => 0.0,
                'paid'          => 0.0,
                'balance'       => 0.0,
            ];
        }
    }

    /**
     * Get accurate fee summary for a specific branch or globally.
     * Uses aggregated DB queries instead of per-student loops to avoid timeouts.
     *
     * @param  int|null  $branchId
     * @return array{billed: float, collected: float, outstanding: float}
     */
    public function getSummary(?int $branchId = null): array
    {
        try {
            // Total collected — single SUM query
            $collectedQuery = \App\Models\Payment::query();
            if ($branchId !== null) {
                $collectedQuery->whereHas('student', fn($q) => $q->where('church_branch_id', $branchId));
            }
            $collected = (float) $collectedQuery->sum('amount_paid');

            // Total billed — join students → program_fees
            // For each student, billed = the program fee for their current program
            $billedQuery = \Illuminate\Support\Facades\DB::table('students')
                ->join('program_fees', function ($join) {
                    $join->on('program_fees.program_id', '=', 'students.program_id')
                         ->where(function ($q) {
                             $q->whereColumn('program_fees.church_branch_id', 'students.church_branch_id')
                               ->orWhereNull('program_fees.church_branch_id');
                         });
                })
                ->when($branchId !== null, fn($q) => $q->where('students.church_branch_id', $branchId))
                ->selectRaw('SUM(program_fees.amount) as total');

            $billed = (float) ($billedQuery->value('total') ?? 0);

            $outstanding = max(0.0, $billed - $collected);

            return [
                'billed'      => $billed,
                'collected'   => $collected,
                'outstanding' => $outstanding,
            ];
        } catch (\Throwable $e) {
            \Log::error('FeeCalculationService::getSummary() failed: ' . $e->getMessage());
            return ['billed' => 0.0, 'collected' => 0.0, 'outstanding' => 0.0];
        }
    }
}

