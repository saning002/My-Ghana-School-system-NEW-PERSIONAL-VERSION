<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportCardExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function array(): array
    {
        if (! $this->data['has_scores']) {
            return [['No scores recorded yet.']];
        }

        return $this->data['courses']->map(fn($row) => [
            $row['course']->name,
            $row['course']->code,
            $row['quiz_score'],
            $row['exam_score'],
            $row['aggregate'],
            $row['position'],
            $row['average'] ?? $row['aggregate'],
            $row['grade'],
        ])->toArray();
    }

    public function headings(): array
    {
        return ['Course', 'Code', 'Quiz Score', 'Exam Score', 'Aggregate', 'Position', '% Average', 'Grade'];
    }

    public function title(): string
    {
        return 'Report Card';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D4A017']]],
        ];
    }
}
