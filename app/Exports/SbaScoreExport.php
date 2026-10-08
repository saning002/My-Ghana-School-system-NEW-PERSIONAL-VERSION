<?php

namespace App\Exports;

use App\Models\ExamScore;
use App\Services\ReportCardService;
use App\Services\GradingScale;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export recorded SBA (and optionally exam) scores for a program/attempt.
 *
 * Options:
 *   include_exam   — add exam score and weighted aggregate columns
 *   student_ids    — limit to specific student IDs (null = all)
 *   split_sba      — show the 4 SBA sub-component columns if they exist
 */
class SbaScoreExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    private array  $rows;
    private array  $headings;
    private string $titleStr;

    public function __construct(
        private $program,
        private $courses,
        private $students,
        private int    $attempt,
        private bool   $includeExam  = false,
        private bool   $splitSba     = true
    ) {
        $this->titleStr = 'SBA Scores';
        $this->build();
    }

    private function build(): void
    {
        $labels      = ReportCardService::getSbaSubLabels();
        $subWeights  = ReportCardService::getSbaSubWeights();
        [$quizPct, $examPct] = ReportCardService::getPercentages($this->program, $this->attempt);

        // ── Build headings ───────────────────────────────────────────
        $this->headings = ['#', 'Student ID', 'Full Name'];

        foreach ($this->courses as $course) {
            $cName = "{$course->name} ({$course->code})";

            if ($this->splitSba) {
                $this->headings[] = "{$cName} — {$labels['test1']} ({$subWeights['test1']}%)";
                $this->headings[] = "{$cName} — {$labels['groupwork']} ({$subWeights['groupwork']}%)";
                $this->headings[] = "{$cName} — {$labels['test2']} ({$subWeights['test2']}%)";
                $this->headings[] = "{$cName} — {$labels['project']} ({$subWeights['project']}%)";
                $this->headings[] = "{$cName} — SBA Total ({$quizPct}%)";
            } else {
                $this->headings[] = "{$cName} — SBA ({$quizPct}%)";
            }

            if ($this->includeExam) {
                $this->headings[] = "{$cName} — Exam ({$examPct}%)";
                $this->headings[] = "{$cName} — Aggregate";
                $this->headings[] = "{$cName} — Grade";
            }
        }

        if ($this->includeExam) {
            $this->headings[] = 'Total Aggregate';
            $this->headings[] = 'Overall Average';
            $this->headings[] = 'Position';
        }

        // ── Fetch scores ─────────────────────────────────────────────
        $studentIds = $this->students->pluck('id');
        $courseIds  = $this->courses->pluck('id');

        $allScores = ExamScore::where('program_id', $this->program->id)
            ->where('attempt', $this->attempt)
            ->whereIn('student_id', $studentIds)
            ->whereIn('course_id', $courseIds)
            ->get()
            ->groupBy('student_id');

        // ── Build rows ───────────────────────────────────────────────
        $tempRows = [];
        $rowNum   = 1;

        foreach ($this->students as $student) {
            $studentScores = $allScores->get($student->id, collect());
            $row = [
                $rowNum++,
                $student->student_id,
                $student->user->full_name ?? '—',
            ];

            $totalAggregate = 0.0;
            $courseCount    = 0;

            foreach ($this->courses as $course) {
                $score = $studentScores->firstWhere('course_id', $course->id);

                $t1  = $score?->test1_score;
                $gw  = $score?->groupwork_score;
                $t2  = $score?->test2_score;
                $pw  = $score?->project_score;
                $sba = $score ? $score->getEffectiveSbaScore() : null;

                if ($this->splitSba) {
                    $row[] = $t1  !== null ? (float)$t1  : '';
                    $row[] = $gw  !== null ? (float)$gw  : '';
                    $row[] = $t2  !== null ? (float)$t2  : '';
                    $row[] = $pw  !== null ? (float)$pw  : '';
                    $row[] = $sba !== null ? round($sba, 2) : '';
                } else {
                    $row[] = $sba !== null ? round($sba, 2) : '';
                }

                if ($this->includeExam) {
                    $examScore = $score?->exam_score !== null ? (float)$score->exam_score : null;
                    $agg = ($sba !== null && $examScore !== null)
                        ? ReportCardService::calcAggregate($sba, $examScore, $this->program, $this->attempt)
                        : null;
                    $grade = $agg !== null ? GradingScale::assign($agg) : '';

                    $row[] = $examScore !== null ? $examScore : '';
                    $row[] = $agg       !== null ? round($agg, 2) : '';
                    $row[] = $grade;

                    $totalAggregate += $agg ?? 0;
                    if ($agg !== null) $courseCount++;
                }
            }

            if ($this->includeExam) {
                $avg = $courseCount > 0 ? round($totalAggregate / $courseCount, 2) : 0;
                $row[] = round($totalAggregate, 2);
                $row[] = $avg;
                $row[] = ''; // position filled after sorting
            }

            $tempRows[] = ['data' => $row, 'total' => $totalAggregate];
        }

        // ── Sort by total and assign position ─────────────────────────
        if ($this->includeExam) {
            usort($tempRows, fn($a, $b) => $b['total'] <=> $a['total']);
            $pos   = 0;
            $last  = null;
            $idx   = 0;
            foreach ($tempRows as &$r) {
                $idx++;
                if ($r['total'] !== $last) { $pos = $idx; $last = $r['total']; }
                $r['data'][count($r['data']) - 1] = $pos; // fill position column
            }
        }

        $this->rows = array_column($tempRows, 'data');
    }

    public function array(): array  { return $this->rows; }
    public function headings(): array { return $this->headings; }
    public function title(): string   { return $this->titleStr; }

    public function styles(Worksheet $sheet): array
    {
        $highest = $sheet->getHighestRow();

        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F4E79']],
            ],
        ];

        // Freeze first 3 columns (row number, ID, name)
        $sheet->freezePane('D2');

        // Zebra-stripe body rows
        for ($r = 2; $r <= max($highest, 2); $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:" . $sheet->getHighestColumn() . "{$r}")
                    ->getFill()->setFillType('solid')->getStartColor()->setRGB('F0F4FF');
            }
        }

        return $styles;
    }
}
