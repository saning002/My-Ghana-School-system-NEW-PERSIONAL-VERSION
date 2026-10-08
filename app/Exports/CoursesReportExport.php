<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CoursesReportExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private Collection $courses) {}

    public function collection(): Collection
    {
        return $this->courses->map(fn($c) => [
            $c->code,
            $c->name,
            $c->program->name ?? '—',
            $c->enrollments_count,
        ]);
    }

    public function headings(): array
    {
        return ['Course Code', 'Course Name', 'Program', 'Enrolled Students'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
