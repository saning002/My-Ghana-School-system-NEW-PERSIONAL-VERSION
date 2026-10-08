<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'Full Name',           // A — REQUIRED
            'Email',               // B — auto-generated if blank
            'Password',            // C — auto-generated if blank
            'Student ID',          // D — auto-generated if blank
            'Admission Date',      // E — YYYY-MM-DD, today if blank
            'Program',             // F — name or ID, uses selected default if blank
            'Church Branch',       // G — name or ID, uses first branch if blank
            'Phone',               // H
            'Date of Birth',       // I — YYYY-MM-DD
            'Address',             // J
            'Gender',              // K — male|female|other
            'Enrollment Year',     // L — e.g. 2026
            'Study Mode',          // M — full_time|part_time
            'Photo Filename',      // N — for ZIP upload only
            'Background Photo',    // O — for ZIP upload only
            'Portal Password',     // P — auto-generated if blank
        ];
    }

    public function collection()
    {
        // Get the real school prefix from settings
        $prefix = rtrim(\App\Models\Setting::get('school_id_prefix', 'RFIS'), '/');
        $year   = date('Y');
        $exampleId = $prefix . '/' . $year . '0001';

        // Try to get a real program and branch name for the example
        $programName = \App\Models\Program::orderBy('sequence')->value('name') ?? 'Your Program Name';
        $branchName  = \App\Models\ChurchBranch::first()?->name ?? 'Your Branch Name';

        return collect([
            [
                'Jane Doe',                 // Full Name (REQUIRED — everything else is optional)
                '',                         // Email — leave blank to auto-generate
                '',                         // Password — leave blank to auto-generate
                '',                         // Student ID — leave blank for auto: ' . $exampleId
                date('Y-m-d'),             // Admission Date
                $programName,              // Program — must match a program name in the system
                $branchName,              // Church Branch — must match a branch in the system
                '+233000000000',           // Phone
                '2000-01-15',             // Date of Birth
                '123 Main Street, Accra', // Address
                'female',                 // Gender
                $year,                    // Enrollment Year
                'full_time',              // Study Mode
                '',                       // Photo Filename (ZIP only)
                '',                       // Background Photo (ZIP only)
                '',                       // Portal Password
            ],
            [
                'John Smith',
                '',
                '',
                '',
                date('Y-m-d'),
                $programName,
                $branchName,
                '',
                '',
                '',
                'male',
                $year,
                'full_time',
                '',
                '',
                '',
            ],
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row: bold, gold background
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F59E0B'],
            ],
        ]);

        // Column A header: red to indicate required
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DC2626'],
            ],
        ]);

        // Freeze the header row
        $sheet->freezePane('A2');

        return [];
    }
}
