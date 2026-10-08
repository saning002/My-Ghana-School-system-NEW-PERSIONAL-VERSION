<?php

namespace App\Exports;

use App\Services\ReportCardService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamSheetExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    private $program;
    private $students;
    private $courses;
    private $attempt;

    public function __construct($program, $students, $courses, $attempt)
    {
        $this->program  = $program;
        $this->students = $students;
        $this->courses  = $courses;
        $this->attempt  = $attempt;
    }

    public function title(): string
    {
        return 'Exam Scores';
    }

    public function headings(): array
    {
        [$quizPct, $examPct] = ReportCardService::getPercentages();

        $headings = [
            'System Student ID',
            'Full Name',
        ];

        foreach ($this->courses as $course) {
            // Two columns per course: SBA and exam
            $headings[] = "Course ID {$course->id} - {$course->name} [SBA {$quizPct}%]";
            $headings[] = "Course ID {$course->id} - {$course->name} [EXAM {$examPct}%]";
        }

        return $headings;
    }

    public function collection()
    {
        $data = [];

        foreach ($this->students as $student) {
            $row = [
                $student->student_id,
                $student->user->full_name,
            ];

            // Two blank columns per course (quiz then exam)
            foreach ($this->courses as $course) {
                $row[] = ''; // quiz score placeholder
                $row[] = ''; // exam score placeholder
            }

            $data[] = $row;
        }

        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow    = $sheet->getHighestRow();

        // Style the header row
        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '151C2C']],
            ],
        ];

        // Alternate column shading: quiz columns get a soft blue tint, exam columns get a soft orange tint
        $colIndex = 3; // start after System Student ID and Full Name
        foreach ($this->courses as $course) {
            $quizCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $examCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

            // Color the header cell for quiz
            $sheet->getStyle("{$quizCol}1")->getFill()
                ->setFillType('solid')
                ->getStartColor()->setRGB('1e3a5f');
            $sheet->getStyle("{$quizCol}1")->getFont()->getColor()->setRGB('FFFFFF');

            // Color the header cell for exam
            $sheet->getStyle("{$examCol}1")->getFill()
                ->setFillType('solid')
                ->getStartColor()->setRGB('7c2d12');
            $sheet->getStyle("{$examCol}1")->getFont()->getColor()->setRGB('FFFFFF');

            // Light shading for data rows
            if ($highestRow > 1) {
                $sheet->getStyle("{$quizCol}2:{$quizCol}{$highestRow}")
                    ->getFill()->setFillType('solid')->getStartColor()->setRGB('EFF6FF');
                $sheet->getStyle("{$examCol}2:{$examCol}{$highestRow}")
                    ->getFill()->setFillType('solid')->getStartColor()->setRGB('FFF7ED');
            }

            $colIndex += 2;
        }

        return $styles;
    }
}
