<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FeeTemplateExport implements FromCollection, WithHeadings, WithStyles, WithEvents, WithCustomStartCell
{
    public function __construct(
        private Collection $students,
        private ?string $branchName = null
    ) {}

    public function collection(): Collection
    {
        return $this->students->map(fn($student) => [
            'student_name' => $student->user->full_name ?? '—',
            'payment_1_date' => '',
            'payment_1_receipt_no' => '',
            'payment_1_amount' => '',
            'payment_2_date' => '',
            'payment_2_receipt_no' => '',
            'payment_2_amount' => '',
            'payment_3_date' => '',
            'payment_3_receipt_no' => '',
            'payment_3_amount' => '',
            'payment_4_date' => '',
            'payment_4_receipt_no' => '',
            'payment_4_amount' => '',
            'payment_1' => '',
            'payment_2' => '',
            'payment_3' => '',
            'payment_4' => '',
            'total_payment' => '',
            'actual_fees' => $this->getActualFees($student),
            'balance' => '',
            'paid_owing' => 'OWING',
        ]);
    }

    public function headings(): array
    {
        return [
            'NAMES OF STUDENTS',
            'DATE',
            'RECEIPT NO',
            'AMOUNT',
            'DATE',
            'RECEIPT NO',
            'AMOUNT',
            'DATE',
            'RECEIPT NO',
            'AMOUNT',
            'DATE',
            'RECEIPT NO',
            'AMOUNT',
            'PAYMENT 1',
            'PAYMENT 2',
            'PAYMENT 3',
            'PAYMENT 4',
            'TOTAL PAYMENT',
            'ACTUAL FEES',
            'BALANCE',
            'PAID/OWING',
        ];
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            4 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $columnCount = 23;
                $lastColumn = $sheet->getHighestColumn();
                $titleRange = "A1:{$lastColumn}1";
                $subtitleRange = "A2:{$lastColumn}2";

                $sheet->mergeCells($titleRange);
                $sheet->setCellValue('A1', 'KINGDOM MINISTERIAL UNIVERSITY COLLEGE');
                $sheet->mergeCells($subtitleRange);
                $sheet->setCellValue('A2', 'SEMESTER SCHOOL FEES LIST');

                $sheet->getStyle($titleRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00B050']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);
                $sheet->getStyle($subtitleRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFD966']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(22);

                $sheet->mergeCells('A3:A4');
                $sheet->mergeCells('B3:B4');
                $sheet->mergeCells('C3:E3');
                $sheet->mergeCells('F3:H3');
                $sheet->mergeCells('I3:K3');
                $sheet->mergeCells('L3:N3');
                $sheet->mergeCells('O3:R3');
                $sheet->mergeCells('S3:S4');
                $sheet->mergeCells('T3:T4');
                $sheet->mergeCells('U3:U4');
                $sheet->mergeCells('V3:V4');
                $sheet->getStyle('A3:V4')->getAlignment()->setHorizontal('center')->setVertical('center');
                $sheet->getStyle('A3:V4')->getFont()->setBold(true);

                $sheet->setCellValue('A3', 'NAMES OF STUDENTS');
                $sheet->setCellValue('B3', 'DATE');
                $sheet->setCellValue('C3', 'PAYMENT 1');
                $sheet->setCellValue('F3', 'PAYMENT 2');
                $sheet->setCellValue('I3', 'PAYMENT 3');
                $sheet->setCellValue('L3', 'PAYMENT 4');
                $sheet->setCellValue('O3', 'ALL PAYMENTS');
                $sheet->setCellValue('S3', 'TOTAL PAYMENT');
                $sheet->setCellValue('T3', 'ACTUAL FEES');
                $sheet->setCellValue('U3', 'BALANCE');
                $sheet->setCellValue('V3', 'PAID/OWING');

                $sheet->getStyle('A3:W3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('00B050');
                $sheet->getStyle('A4:W4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FDE9D9');

                $sheet->getColumnDimension('A')->setWidth(36);
                foreach (range('B', 'V') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(14);
                }

                $rowStart = 5;
                $rowEnd = $rowStart + max(0, $this->students->count() - 1);
                for ($row = $rowStart; $row <= $rowEnd; $row++) {
                    $sheet->setCellValue("S{$row}", "=SUM(E{$row},H{$row},K{$row},N{$row})");
                    $sheet->setCellValue("U{$row}", "=IF(T{$row}='',0,T{$row})-S{$row}");
                    $sheet->setCellValue("V{$row}", "=IF(U{$row}>0,'OWING','PAID')");
                }

                $sheet->getStyle("A4:W{$rowEnd}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFBFBF']]],
                ]);
            },
        ];
    }

    private function getActualFees($student): string
    {
        $fee = null;
        if ($student->program?->programFees) {
            $fee = $student->program->programFees->firstWhere('church_branch_id', auth()->user()?->church_branch_id) ?? $student->program->programFees->firstWhere('church_branch_id', null);
        }
        if (! $fee) {
            return '';
        }
        $amount = (float) $fee->amount + (float) $fee->exam_fee;
        return number_format($amount, 2, '.', '');
    }
}
