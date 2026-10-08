<?php

namespace App\Imports;

use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class FeeImport implements ToCollection, WithStartRow
{
    public function __construct(private ?int $branchId = null) {}

    public function startRow(): int
    {
        return 5;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $rowCollection) {
                $row = $rowCollection instanceof Collection ? $rowCollection->toArray() : (array) $rowCollection;
                $rowNumber = $index + 5;
                $studentName = trim($row[0] ?? '');

                $hasAnyPayment = false;
                for ($paymentIndex = 0; $paymentIndex < 4; $paymentIndex++) {
                    $amountValue = trim($row[3 + $paymentIndex * 3] ?? '');
                    $dateValue = trim($row[1 + $paymentIndex * 3] ?? '');
                    if ($amountValue !== '' || $dateValue !== '') {
                        $hasAnyPayment = true;
                        break;
                    }
                }

                if ($studentName === '' && ! $hasAnyPayment) {
                    continue;
                }

                $student = null;
                if ($studentName !== '') {
                    $student = Student::whereHas('user', fn($query) =>
                        $query->whereRaw('LOWER(full_name) = ?', [strtolower($studentName)])
                    )->first();
                }

                if (! $student) {
                    throw new \Exception("Row {$rowNumber}: Student not found for '{$studentName}'");
                }

                if ($this->branchId && $student->church_branch_id !== $this->branchId) {
                    throw new \Exception("Row {$rowNumber}: Student '{$studentName}' does not belong to the selected branch");
                }

                for ($paymentIndex = 0; $paymentIndex < 4; $paymentIndex++) {
                    $baseIndex = 1 + ($paymentIndex * 3);
                    $dateValue = trim($row[$baseIndex] ?? '');
                    $receipt = trim($row[$baseIndex + 1] ?? '');
                    $amountValue = trim($row[$baseIndex + 2] ?? '');

                    if ($dateValue === '' && $amountValue === '') {
                        continue;
                    }

                    if ($amountValue === '') {
                        throw new \Exception("Row {$rowNumber}: Missing amount for payment " . ($paymentIndex + 1));
                    }

                    if (! is_numeric($amountValue)) {
                        throw new \Exception("Row {$rowNumber}: Invalid amount '{$amountValue}' for payment " . ($paymentIndex + 1));
                    }

                    if ($amountValue <= 0) {
                        continue;
                    }

                    if ($dateValue === '') {
                        throw new \Exception("Row {$rowNumber}: Missing date for payment " . ($paymentIndex + 1));
                    }

                    try {
                        $paymentDate = Carbon::parse($dateValue)->toDateString();
                    } catch (\Throwable $e) {
                        throw new \Exception("Row {$rowNumber}: Invalid date '{$dateValue}' for payment " . ($paymentIndex + 1));
                    }

                    Payment::firstOrCreate(
                        [
                            'student_id' => $student->id,
                            'amount_paid' => $amountValue,
                            'payment_date' => $paymentDate,
                            'notes' => $receipt !== '' ? 'Receipt: ' . $receipt : null,
                        ]
                    );
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
