<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceTemplateExport implements FromCollection, WithHeadings, WithStyles, WithEvents, WithCustomStartCell
{
    private const WEEKS = 5;
    private const NAME_COLUMN_COUNT = 4;
    private const WEEK_DAY_KEYS = ['thu' => 'Thu', 'sun' => 'Sun', 'other' => 'Other'];

    public function __construct(
        private Collection $students,
        private $course,
        private $program,
        private $period,
        private string $date
    ) {}

    public function collection(): Collection
    {
        return $this->students->map(fn($student) => array_merge([
            'student_name' => $student->user->full_name ?? '—',
        ], array_fill(0, self::NAME_COLUMN_COUNT - 1, ''), $this->emptyWeekCells()));
    }

    public function headings(): array
    {
        $headings = array_fill(0, self::NAME_COLUMN_COUNT, '');

        for ($week = 1; $week <= self::WEEKS; $week++) {
            foreach (self::WEEK_DAY_KEYS as $heading) {
                $headings[] = $heading;
            }
            $headings[] = 'Count';
        }

        return $headings;
    }

    public function startCell(): string
    {
        return 'B6';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            6 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->applyHeaderLayout($sheet);
                $this->applyColumnWidths($sheet);
                $this->applyRowFormulas($sheet);
                $this->applyBorders($sheet);
            },
        ];
    }

    private function emptyWeekCells(): array
    {
        $cells = [];
        for ($week = 1; $week <= self::WEEKS; $week++) {
            foreach (self::WEEK_DAY_KEYS as $key => $label) {
                $cells["week{$week}_{$key}"] = '';
            }
        }
        return $cells;
    }

    private function applyHeaderLayout($sheet): void
    {
        $lastColumn = $sheet->getHighestColumn();
        $titleRange = "B1:{$lastColumn}1";
        $subtitleRange = "B2:{$lastColumn}2";
        $infoRange = "B3:{$lastColumn}3";

        $sheet->mergeCells($titleRange);
        $sheet->mergeCells($subtitleRange);
        $sheet->mergeCells($infoRange);

        $sheet->setCellValue('B1', 'STUDENT ATTENDANCE BOOK');
        $sheet->setCellValue('B2', 'BULK WEEKLY ATTENDANCE TEMPLATE');
        $sheet->setCellValue('B3', sprintf('Course: %s | Program: %s | Period: %s | Template date: %s',
            $this->course->name ?? '—',
            $this->program->name ?? '—',
            $this->period ? ($this->period->month . ' (' . ($this->period->session->year ?? '') . ')') : '—',
            Carbon::parse($this->date)->format('Y-m-d')
        ));

        $sheet->getStyle($titleRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '013A63']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getStyle($subtitleRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getStyle($infoRange)->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(18);

        $sheet->mergeCells('B5:E6');
        $sheet->setCellValue('B5', 'Name');

        for ($week = 1; $week <= self::WEEKS; $week++) {
            $start = 6 + (($week - 1) * 4);
            $startColumn = Coordinate::stringFromColumnIndex($start);
            $endColumn = Coordinate::stringFromColumnIndex($start + 2);
            $attendanceColumn = Coordinate::stringFromColumnIndex($start + 3);

            $sheet->mergeCells("{$startColumn}5:{$endColumn}5");
            $sheet->setCellValue("{$startColumn}5", sprintf('Week %d (%s)', $week, $this->getWeekLabel($week)));
            $sheet->setCellValue("{$attendanceColumn}5", 'Attendance');
        }

        $sheet->getStyle('B5:' . $lastColumn . '5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2E75B6');
        $sheet->getStyle('B6:' . $lastColumn . '6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('8DB4E2');
        $sheet->getStyle('B5:' . $lastColumn . '6')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
    }

    private function applyColumnWidths($sheet): void
    {
        $sheet->getColumnDimension('A')->setWidth(32);
        $sheet->getColumnDimension('B')->setWidth(28);

        $letter = 'C';
        while ($letter <= 'V') {
            $sheet->getColumnDimension($letter)->setWidth(12);
            $letter++;
        }
    }

    private function applyRowFormulas($sheet): void
    {
        $startRow = 7;
        $endRow = $startRow + max(0, $this->students->count() - 1);

        for ($row = $startRow; $row <= $endRow; $row++) {
            for ($week = 0; $week < self::WEEKS; $week++) {
                $startColumn = 6 + ($week * 4);
                $startColumnLetter = Coordinate::stringFromColumnIndex($startColumn);
                $endColumnLetter = Coordinate::stringFromColumnIndex($startColumn + 2);
                $countColumnLetter = Coordinate::stringFromColumnIndex($startColumn + 3);
                $sheet->setCellValue("{$countColumnLetter}{$row}", "=COUNTIF({$startColumnLetter}{$row}:{$endColumnLetter}{$row},\"✓\")");
            }
        }
    }

    private function applyBorders($sheet): void
    {
        $startRow = 4;
        $endRow = 5 + max(0, $this->students->count());
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A{$startRow}:{$lastColumn}{$endRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFBFBF']]],
        ]);
    }

    private function getWeekLabel(int $week): string
    {
        $start = Carbon::parse($this->date)->startOfWeek(Carbon::MONDAY)->addWeeks($week - 1);
        return $start->format('M, Y');
    }
}
