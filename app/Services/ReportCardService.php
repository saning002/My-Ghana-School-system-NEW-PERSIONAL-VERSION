<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Program;
use App\Models\ExamScore;
use App\Models\Setting;
use App\Services\GradingScale;

class ReportCardService
{
    /**
     * Get the SBA sub-component weights from settings.
     * Each sub-weight is the raw max for that component (e.g. Test 1 = 20, Group Work = 10).
     * They do NOT need to sum to 100 — they sum to whatever the admin sets as total sub-weight.
     * Defaults: 25 / 25 / 25 / 25 (sums to 100)
     */
    public static function getSbaSubWeights(): array
    {
        return [
            'test1'     => (float) Setting::get('sba_test1_weight',     25),
            'groupwork' => (float) Setting::get('sba_groupwork_weight', 25),
            'test2'     => (float) Setting::get('sba_test2_weight',     25),
            'project'   => (float) Setting::get('sba_project_weight',   25),
        ];
    }

    /**
     * Get the total sub-weight (denominator for the SBA formula).
     * e.g. if Test1=20, GW=10, Test2=20, Project=10 → total = 60
     */
    public static function getTotalSubWeight(): float
    {
        $w = self::getSbaSubWeights();
        $total = $w['test1'] + $w['groupwork'] + $w['test2'] + $w['project'];
        return $total > 0 ? $total : 100.0;
    }

    /**
     * Get the sub-component labels from settings.
     */
    public static function getSbaSubLabels(): array
    {
        return [
            'test1'     => Setting::get('sba_test1_label',     'Test 1'),
            'groupwork' => Setting::get('sba_groupwork_label', 'Group Work'),
            'test2'     => Setting::get('sba_test2_label',     'Test 2'),
            'project'   => Setting::get('sba_project_label',   'Project Work'),
        ];
    }

    /**
     * Get the current quiz and exam percentages from settings.
     * Returns [quizPct, examPct] where both are 0–100 and sum to 100.
     *
     * Special case: Pre-College program (sequence=1) attempt=1 scores were
     * imported as already-final 100-point aggregates stored in exam_score with
     * quiz_score=null. We treat them as 100% exam weight to preserve the
     * original marks.
     */
    public static function getPercentages(?Program $program = null, int $attempt = 0): array
    {
        // Pre-College (sequence 1) attempt 1 — scores are already final aggregates
        if ($program && $program->sequence === 1 && $attempt === 1) {
            return [0, 100]; // 0% quiz, 100% exam → exam_score IS the aggregate
        }

        $quizPct = (float) Setting::get('quiz_percentage', 30);
        $examPct = (float) Setting::get('exam_percentage', 70);

        if (abs(($quizPct + $examPct) - 100) > 0.01) {
            $quizPct = 30;
            $examPct = 70;
        }

        return [$quizPct, $examPct];
    }

    /**
     * Calculate the weighted aggregate for a single score row.
     *
     * Formula:
     *   Class Score = (raw_sba_sum / total_sub_weight) × sba_pct
     *   Exam Score  = exam_score × (exam_pct / 100)
     *   Total       = Class Score + Exam Score
     *
     * If sub-scores are not used, raw_sba_sum is treated as already
     * normalised (legacy sba_score / quiz_score stored out of 100),
     * so the formula becomes: sba_score × (sba_pct / 100) + exam × (exam_pct / 100)
     *
     * @param float       $rawSbaSum    sum of sub-scores OR legacy sba/quiz value
     * @param float       $examScore    raw exam score (0–100)
     * @param bool        $hasSubScores true if sub-scores were used
     */
    public static function calcAggregate(
        float $rawSbaSum,
        float $examScore,
        ?Program $program = null,
        int $attempt = 0,
        bool $hasSubScores = false
    ): float {
        [$sbaPct, $examPct] = self::getPercentages($program, $attempt);

        if ($hasSubScores) {
            $totalSubWeight = self::getTotalSubWeight();
            // Class Score = (sum / total_weight) × sba_pct
            $classScore = $totalSubWeight > 0
                ? ($rawSbaSum / $totalSubWeight) * $sbaPct
                : 0;
        } else {
            // Legacy: sba_score is already out of 100
            $classScore = $rawSbaSum * $sbaPct / 100;
        }

        $examContribution = $examScore * $examPct / 100;
        return round($classScore + $examContribution, 2);
    }

    /**
     * Returns [rawSbaSum, hasSubScores]
     * If sub-scores present: rawSbaSum = sum of sub-scores (e.g. 36 out of 60)
     * If legacy: rawSbaSum = sba_score or quiz_score (out of 100)
     */
    private static function resolveCaScore(?ExamScore $score): array
    {
        if (! $score) {
            return [0.0, false];
        }
        if ($score->hasSubScores()) {
            $sum = (float)($score->test1_score ?? 0)
                 + (float)($score->groupwork_score ?? 0)
                 + (float)($score->test2_score ?? 0)
                 + (float)($score->project_score ?? 0);
            return [$sum, true];
        }
        $legacy = $score->sba_score !== null ? (float)$score->sba_score : (float)($score->quiz_score ?? 0);
        return [$legacy, false];
    }

    /**
     * Calculate cumulative GPA for a student across all exam attempts.
     */
    public function calculateCgpa(Student $student): float
    {
        $scores = ExamScore::with(['course', 'program'])
            ->where('student_id', $student->id)
            ->get()
            ->filter(fn($score) => $score->course !== null && $score->program !== null);

        $totalCredits      = 0.0;
        $totalCreditPoints = 0.0;

        foreach ($scores as $score) {
            $credit    = $score->course->credit ?? 3;
            [$rawSba, $hasSub] = self::resolveCaScore($score);
            $aggregate = self::calcAggregate($rawSba, (float) $score->exam_score, $score->program, $score->attempt, $hasSub);
            $grade     = GradingScale::assign($aggregate);
            $point     = GradingScale::point($grade);

            $totalCredits      += $credit;
            $totalCreditPoints += $credit * $point;
        }

        return $totalCredits > 0 ? round($totalCreditPoints / $totalCredits, 2) : 0.0;
    }

    /**
     * Generate report card data for a student in a given program.
     */
    public function generate(Student $student, Program $program, int $attempt = 1): array
    {
        $courses = $program->courses()->orderBy('name')->get();

        if ($courses->isEmpty()) {
            return $this->emptyCard($student, $program, $attempt);
        }

        // Load all exam scores for this program + attempt,
        // but exclude suspended/withdrawn students from the pool entirely
        // so they don't pollute rankings or appear in report cards.
        $excludedStudentIds = \App\Models\Student::whereIn('status', \App\Models\Student::EXAM_EXCLUDED_STATUSES)
            ->pluck('id');

        $allScores = ExamScore::where('program_id', $program->id)
            ->where('attempt', $attempt)
            ->whereIn('course_id', $courses->pluck('id'))
            ->whereNotIn('student_id', $excludedStudentIds)
            ->get();

        $studentScores = $allScores->where('student_id', $student->id);

        if ($studentScores->isEmpty()) {
            return $this->emptyCard($student, $program, $attempt);
        }

        [$sbaPct, $examPct] = self::getPercentages($program, $attempt);

        // Build per-course data with position ranking
        $courseData = $courses->map(function ($course) use ($allScores, $student, $sbaPct, $examPct, $program, $attempt) {
            $score = $allScores->where('student_id', $student->id)
                               ->where('course_id', $course->id)
                               ->first();

            [$rawSba, $hasSub] = self::resolveCaScore($score);
            $examScore  = $score ? (float) $score->exam_score : 0;
            $aggregate  = self::calcAggregate($rawSba, $examScore, $program, $attempt, $hasSub);

            // Class score (what appears in the CA/Class Score column on the report card)
            if ($hasSub) {
                $totalSubWeight = self::getTotalSubWeight();
                $classScore = $totalSubWeight > 0 ? round(($rawSba / $totalSubWeight) * $sbaPct, 2) : 0;
            } else {
                $classScore = round($rawSba * $sbaPct / 100, 2);
            }

            $credit     = $course->credit ?? 3;
            $grade      = GradingScale::assign($aggregate);
            $gradePoint = GradingScale::point($grade);

            // DENSE_RANK: count students with higher aggregate in same course
            $allCourseAggregates = $allScores->where('course_id', $course->id)
                ->map(function($s) use ($program, $attempt) {
                    [$rSba, $hSub] = self::resolveCaScore($s);
                    return self::calcAggregate($rSba, (float)$s->exam_score, $program, $attempt, $hSub);
                })
                ->sortDesc()->values();

            $higherCount = $allCourseAggregates->filter(fn($a) => $a > $aggregate)->count();
            $position    = $higherCount + 1;

            return [
                'course'       => $course,
                'credit'       => $credit,
                'grade'        => $grade,
                'grade_point'  => $gradePoint,
                'credit_point' => $credit * $gradePoint,
                'class_score'  => $classScore,   // renamed from quiz_score
                'quiz_score'   => $classScore,   // kept for backward compat with PDF
                'exam_score'   => $examScore,
                'aggregate'    => $aggregate,
                'average'      => round($aggregate, 2),
                'position'     => $position,
            ];
        });

        $overallAggregate  = $courseData->sum('aggregate');
        $totalCredits      = $courseData->sum('credit');
        $totalCreditPoints = $courseData->sum('credit_point');
        $overallAverage    = $courseData->count() > 0 ? $overallAggregate / $courseData->count() : 0;
        $overallSgpa       = $totalCredits > 0 ? round($totalCreditPoints / $totalCredits, 2) : 0;
        $courseCount       = $courseData->count();
        $overallGrade      = GradingScale::assign($overallAverage);
        $remarks           = $overallAverage >= 40 ? 'PROMOTED AND QUALIFIED TO NEXT SERIES' : 'FAILED — REPEAT PROGRAM';

        // Calculate overall position (rank) for this exam attempt
        $studentIdsWithScores = $allScores->pluck('student_id')->unique();

        // Scope ranking to students in the same branch as the current student
        $branchId = $student->church_branch_id;
        if ($branchId) {
            $studentIdsWithScores = \App\Models\Student::whereIn('id', $studentIdsWithScores)
                ->where('church_branch_id', $branchId)
                ->pluck('id');
        }

        $studentAverages = $studentIdsWithScores->mapWithKeys(function ($sid) use ($allScores, $courses, $program, $attempt) {
            $scoresForStudent = $allScores->where('student_id', $sid);
            if ($scoresForStudent->isEmpty()) {
                return [$sid => 0.0];
            }
            $sumAggregates = 0.0;
            foreach ($courses as $c) {
                $s = $scoresForStudent->where('course_id', $c->id)->first();
                if ($s) {
                    [$rSba, $hSub] = self::resolveCaScore($s);
                    $sumAggregates += self::calcAggregate($rSba, (float)$s->exam_score, $program, $attempt, $hSub);
                }
            }
            $avg = $courses->count() > 0 ? $sumAggregates / $courses->count() : 0.0;
            return [$sid => round($avg, 2)];
        });

        $currentStudentAverage = round($overallAverage, 2);
        $higherAverageCount    = $studentAverages->filter(fn($avg) => $avg > $currentStudentAverage)->count();
        $overallPosition       = $higherAverageCount + 1;
        $totalStudentsInExam   = $studentIdsWithScores->count();

        // Use the earliest score's created_at as the exam date so PDFs show
        // when the exam was taken, not when it is downloaded.
        $examDate = $allScores
            ->where('student_id', $student->id)
            ->sortBy('created_at')
            ->first()
            ?->created_at;

        return [
            'student'                   => $student,
            'program'                   => $program,
            'courses'                   => $courseData,
            'overall_aggregate'         => round($overallAggregate, 2),
            'overall_average'           => round($overallAverage, 2),
            'overall_grade'             => $overallGrade,
            'overall_grade_description' => GradingScale::description($overallGrade),
            'result'                    => GradingScale::result($overallAverage),
            'remarks'                   => $remarks,
            'overall_sgpa'              => $overallSgpa,
            'cgpa'                      => $this->calculateCgpa($student),
            'total_courses'             => $courseCount,
            'total_credits'             => $totalCredits,
            'total_credit_requirement'  => $totalCredits,
            'total_credit_taken'        => $totalCredits,
            'has_scores'                => true,
            'overall_position'          => $overallPosition,
            'total_students_in_exam'    => $totalStudentsInExam,
            'quiz_percentage'           => $sbaPct,
            'sba_percentage'            => $sbaPct,
            'exam_percentage'           => $examPct,
            'exam_date'                 => $examDate ?? now(),
        ];
    }

    /**
     * Return an empty report card structure when no scores exist.
     */
    private function emptyCard(Student $student, Program $program, int $attempt = 1): array
    {
        [$sbaPct, $examPct] = self::getPercentages($program, $attempt);

        return [
            'student'                   => $student,
            'program'                   => $program,
            'courses'                   => collect(),
            'overall_aggregate'         => 0,
            'overall_average'           => 0,
            'overall_grade'             => '—',
            'overall_grade_description' => '—',
            'result'                    => '—',
            'remarks'                   => '—',
            'has_scores'                => false,
            'overall_sgpa'              => 0,
            'cgpa'                      => $this->calculateCgpa($student),
            'total_courses'             => 0,
            'total_credits'             => 0,
            'total_credit_taken'        => 0,
            'total_credit_requirement'  => 0,
            'overall_position'          => '—',
            'total_students_in_exam'    => 0,
            'quiz_percentage'           => $sbaPct,
            'sba_percentage'            => $sbaPct,
            'exam_percentage'           => $examPct,
            'exam_date'                 => now(),
        ];
    }
}
