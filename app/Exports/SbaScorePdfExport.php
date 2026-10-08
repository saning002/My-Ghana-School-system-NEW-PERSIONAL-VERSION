<?php

namespace App\Exports;

use App\Models\ExamScore;
use App\Services\ReportCardService;
use App\Services\GradingScale;

/**
 * Generates a PDF of SBA scores using DomPDF (same engine as report cards).
 */
class SbaScorePdfExport
{
    private array $data = [];

    public function __construct(
        private $program,
        private $courses,
        private $students,
        private int  $attempt,
        private bool $includeExam = false,
        private bool $splitSba    = true
    ) {}

    public function download(string $filename)
    {
        try {
            $this->build();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sba_scores', $this->data)
                ->setPaper('a4', 'landscape');
            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Log::error('SBA PDF export failed: '.$e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Failed to generate SBA PDF: '.$e->getMessage());
        }
    }

    private function build(): void
    {
        $labels     = ReportCardService::getSbaSubLabels();
        $subWeights = ReportCardService::getSbaSubWeights();
        $totalSubW  = ReportCardService::getTotalSubWeight();
        [$sbaPct, $examPct] = ReportCardService::getPercentages($this->program, $this->attempt);

        $courseIds  = $this->courses->pluck('id');
        $studentIds = $this->students->pluck('id');

        $allScores = ExamScore::where('program_id', $this->program->id)
            ->where('attempt', $this->attempt)
            ->whereIn('student_id', $studentIds)
            ->whereIn('course_id', $courseIds)
            ->get()
            ->groupBy('student_id');

        $rows = [];
        foreach ($this->students as $student) {
            $studentScores = $allScores->get($student->id, collect());
            $row = [
                'name'       => optional($student->user)->full_name ?? '—',
                'student_id' => $student->student_id,
                'courses'    => [],
                'total_agg'  => 0.0,
                'avg'        => 0.0,
                'position'   => '',
            ];

            $courseCount = 0;
            foreach ($this->courses as $course) {
                $score = $studentScores->firstWhere('course_id', $course->id);

                $t1  = $score ? $score->test1_score     : null;
                $gw  = $score ? $score->groupwork_score : null;
                $t2  = $score ? $score->test2_score     : null;
                $pw  = $score ? $score->project_score   : null;

                $hasSubScores = ($t1 !== null || $gw !== null || $t2 !== null || $pw !== null);
                $subSum = (float)($t1 ?? 0) + (float)($gw ?? 0) + (float)($t2 ?? 0) + (float)($pw ?? 0);

                if ($hasSubScores && $totalSubW > 0) {
                    $classScore = round(($subSum / $totalSubW) * $sbaPct, 2);
                } else {
                    $legacySba  = $score ? ((float)($score->sba_score ?? $score->quiz_score ?? 0)) : 0;
                    $classScore = round($legacySba * $sbaPct / 100, 2);
                }

                $examScore = ($score && $score->exam_score !== null) ? (float)$score->exam_score : null;

                $agg = null;
                if ($this->includeExam && $examScore !== null) {
                    $agg = ReportCardService::calcAggregate(
                        $hasSubScores ? $subSum : ($classScore * 100 / max($sbaPct, 1)),
                        $examScore,
                        $this->program,
                        $this->attempt,
                        $hasSubScores
                    );
                }

                $row['courses'][] = [
                    'name'        => $course->name,
                    'code'        => $course->code,
                    'test1'       => $t1,
                    'groupwork'   => $gw,
                    'test2'       => $t2,
                    'project'     => $pw,
                    'class_score' => $classScore,
                    'exam_score'  => $examScore,
                    'aggregate'   => $agg,
                    'grade'       => $agg !== null ? GradingScale::assign((float)$agg) : '',
                ];

                if ($agg !== null) {
                    $row['total_agg'] += $agg;
                    $courseCount++;
                }
            }

            $row['avg'] = $courseCount > 0 ? round($row['total_agg'] / $courseCount, 2) : 0;
            $rows[] = $row;
        }

        // Sort by average descending and assign positions
        usort($rows, function($a, $b) { return $b['avg'] <=> $a['avg']; });
        $pos = 0; $last = null; $idx = 0;
        foreach ($rows as &$r) {
            $idx++;
            if ($r['avg'] !== $last) { $pos = $idx; $last = $r['avg']; }
            $r['position'] = $this->includeExam ? $pos : '';
        }
        unset($r);

        $this->data = [
            'rows'         => $rows,
            'program'      => $this->program,
            'attempt'      => $this->attempt,
            'courses'      => $this->courses,
            'labels'       => $labels,
            'sub_weights'  => $subWeights,
            'total_sub_w'  => $totalSubW,
            'sba_pct'      => $sbaPct,
            'exam_pct'     => $examPct,
            'include_exam' => $this->includeExam,
            'split_sba'    => $this->splitSba,
            'generated_at' => now()->format('d M Y, g:i A'),
        ];
    }
}
