<?php

namespace App\Exports;

use App\Models\ExamScore;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderOfMeritExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    private array $rows;
    private array $headings;
    private string $title;

    public function __construct($program, $students, $courses, int $attempt)
    {
        $this->title = 'Order of Merit';
        $this->headings = ['Student ID', 'Full Name'];

        foreach ($courses as $course) {
            $this->headings[] = sprintf('%s (%s)', $course->name, $course->code);
        }

        $this->headings[] = 'Total Aggregate';
        $this->headings[] = 'Average';
        $this->headings[] = 'Overall Position';

        $courseIds = $courses->pluck('id')->toArray();
        $studentIds = $students->pluck('id')->toArray();

        $scores = ExamScore::where('program_id', $program->id)
            ->where('attempt', $attempt)
            ->whereIn('student_id', $studentIds)
            ->whereIn('course_id', $courseIds)
            ->get()
            ->groupBy('student_id');

        $rows = [];

        foreach ($students as $student) {
            $studentScores = $scores->get($student->id, collect());

            $totalAggregate = 0.0;
            $scoresByCourse = [];

            foreach ($courses as $course) {
                $score = $studentScores->firstWhere('course_id', $course->id);
                $scoreValue = $score ? (float) $score->exam_score : null;
                $scoresByCourse[] = $scoreValue;
                $totalAggregate += $scoreValue ?? 0.0;
            }

            $average = count($courses) > 0 ? round($totalAggregate / count($courses), 2) : 0.0;

            $rows[] = [
                'student_id' => $student->student_id,
                'name' => $student->user->full_name,
                'course_scores' => $scoresByCourse,
                'total' => round($totalAggregate, 2),
                'average' => $average,
            ];
        }

        $sorted = collect($rows)->sortByDesc('total')->values();
        $lastTotal = null;
        $currentPosition = 0;
        $rowIndex = 0;

        $this->rows = $sorted->map(function ($row) use (&$lastTotal, &$currentPosition, &$rowIndex) {
            $rowIndex++;

            if ($lastTotal === null || $row['total'] !== $lastTotal) {
                $currentPosition = $rowIndex;
                $lastTotal = $row['total'];
            }

            return array_merge(
                [
                    $row['student_id'],
                    $row['name'],
                ],
                array_map(fn($score) => $score === null ? '' : $score, $row['course_scores']),
                [
                    $row['total'],
                    $row['average'],
                    $currentPosition,
                ]
            );
        })->toArray();
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F4E79']],
            ],
        ];
    }
}
