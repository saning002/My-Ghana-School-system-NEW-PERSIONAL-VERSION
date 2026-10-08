<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceReportExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private Collection $attendances) {}

    public function collection(): Collection
    {
        return $this->attendances->map(fn($a) => [
            $a->student->user->full_name ?? '—',
            $a->student->student_id ?? '—',
            $a->course->code ?? '—',
            $a->course->name ?? '—',
            $a->academicPeriod->month ?? '—',
            $a->date ? \Carbon\Carbon::parse($a->date)->format('Y-m-d') : '—',
            ucfirst($a->status),
        ]);
    }

    public function headings(): array
    {
        return ['Student Name', 'Student ID', 'Course Code', 'Course Name', 'Period', 'Date', 'Status'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
