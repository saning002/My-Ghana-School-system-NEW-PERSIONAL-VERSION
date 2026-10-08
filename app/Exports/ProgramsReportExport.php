<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProgramsReportExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private Collection $programs) {}

    public function collection(): Collection
    {
        return $this->programs->map(fn($p) => [
            $p->name,
            $p->sequence,
            $p->students_count,
            $p->students->where('status', 'active')->count(),
            $p->students->where('status', 'graduated')->count(),
            $p->students->where('status', 'suspended')->count(),
            $p->courses->count(),
        ]);
    }

    public function headings(): array
    {
        return ['Program', 'Sequence', 'Total Students', 'Active', 'Graduated', 'Suspended', 'Courses'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
