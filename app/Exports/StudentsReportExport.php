<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsReportExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private Collection $students) {}

    public function collection(): Collection
    {
        return $this->students->map(fn($s) => [
            $s->student_id,
            $s->user->full_name ?? '—',
            $s->program->name ?? '—',
            $s->churchBranch->name ?? '—',
            $s->admission_date ? \Carbon\Carbon::parse($s->admission_date)->format('Y-m-d') : '—',
            ucfirst($s->status),
        ]);
    }

    public function headings(): array
    {
        return ['Student ID', 'Full Name', 'Program', 'Ministry Branch', 'Admission Date', 'Status'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
