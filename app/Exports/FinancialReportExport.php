<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private Collection $studentFees) {}

    public function collection(): Collection
    {
        return $this->studentFees->map(fn($row) => [
            $row['student']->student_id,
            $row['student']->user->full_name ?? '—',
            $row['student']->program->name ?? '—',
            number_format($row['total'], 2),
            number_format($row['paid'], 2),
            number_format($row['balance'], 2),
        ]);
    }

    public function headings(): array
    {
        return ['Student ID', 'Full Name', 'Program', 'Total Fees (GH₵)', 'Amount Paid (GH₵)', 'Balance (GH₵)'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
