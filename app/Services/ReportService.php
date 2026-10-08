<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Enrollment;
use App\Models\ExamScore;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\GradingScale;
use App\Services\ReportCardService;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(
        private ReportCardService $reportCard
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // CLASS PERFORMANCE REPORT
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate a class-level performance summary for a program+period.
     *
     * Returns:
     *  total_students, class_average, highest, lowest, pass_rate, fail_rate,
     *  subject_stats (array per course), grade_distribution, ranked_students
     */
    public function classPerformance(Program $program, ?AcademicPeriod $period, int $attempt = 1): array
    {
        $courses = $program->courses()->orderBy('name')->get();

        $students = Student::where('program_id', $program->id)
            ->forExams()
            ->with('user')
            ->get();

        if ($students->isEmpty() || $courses->isEmpty()) {
            return $this->emptyClassPerformance($program, $period);
        }

        try {
            $allScores = ExamScore::where('program_id', $program->id)
                ->where('attempt', $attempt)
                ->when($period, function($q) use ($period) {
                    // Only filter by period_id if the column exists
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('exam_scores', 'academic_period_id')) {
                            $q->where('academic_period_id', $period->id);
                        }
                    } catch (\Throwable $e) { /* column missing — skip filter */ }
                })
                ->whereIn('student_id', $students->pluck('id'))
                ->whereIn('course_id', $courses->pluck('id'))
                ->get();
        } catch (\Throwable $e) {
            \Log::error('ReportService::classPerformance() query failed: '.$e->getMessage());
            return $this->emptyClassPerformance($program, $period);
        }

        [$sbaPct, $examPct] = ReportCardService::getPercentages($program, $attempt);

        // Build per-student aggregates
        $studentAggregates = $students->map(function ($student) use ($allScores, $courses, $program, $attempt) {
            $studentScores = $allScores->where('student_id', $student->id);
            $subjectData = [];
            $totalAgg    = 0;
            $subjectCount = 0;

            foreach ($courses as $course) {
                $score = $studentScores->where('course_id', $course->id)->first();
                if (!$score) continue;
                [$rawSba, $hasSub] = $this->resolveCa($score);
                $agg = ReportCardService::calcAggregate($rawSba, (float)$score->exam_score, $program, $attempt, $hasSub);
                $subjectData[$course->id] = $agg;
                $totalAgg += $agg;
                $subjectCount++;
            }

            $avg = $subjectCount > 0 ? $totalAgg / $subjectCount : 0;
            return [
                'student'  => $student,
                'average'  => round($avg, 2),
                'grade'    => GradingScale::assign($avg),
                'subjects' => $subjectData,
                'passed'   => $avg >= 50,
            ];
        })->sortByDesc('average')->values();

        // Assign ranks (dense rank — ties get same rank, next rank skips)
        $ranked = $this->assignRanks($studentAggregates, 'average');

        // Subject stats
        $subjectStats = $courses->map(function ($course) use ($allScores, $students, $program, $attempt) {
            $courseScores = $allScores->where('course_id', $course->id);
            $aggregates   = $courseScores->map(function ($s) use ($program, $attempt) {
                [$rSba, $hSub] = $this->resolveCa($s);
                return ReportCardService::calcAggregate($rSba, (float)$s->exam_score, $program, $attempt, $hSub);
            })->values();

            if ($aggregates->isEmpty()) {
                return ['course' => $course, 'average'=>0,'highest'=>0,'lowest'=>0,'pass_rate'=>0,'fail_rate'=>100,'count'=>0];
            }

            $passed   = $aggregates->filter(fn($a) => $a >= 50)->count();
            $total    = $aggregates->count();
            return [
                'course'    => $course,
                'average'   => round($aggregates->avg(), 1),
                'highest'   => round($aggregates->max(), 1),
                'lowest'    => round($aggregates->min(), 1),
                'pass_rate' => $total > 0 ? round($passed / $total * 100, 1) : 0,
                'fail_rate' => $total > 0 ? round(($total - $passed) / $total * 100, 1) : 0,
                'count'     => $total,
            ];
        });

        // Grade distribution
        $gradeDist = collect(['A1'=>0,'A2'=>0,'A3'=>0,'B1'=>0,'B2'=>0,'B3'=>0,'C'=>0,'F'=>0]);
        foreach ($ranked as $r) {
            $g = $r['grade'];
            if (isset($gradeDist[$g])) $gradeDist[$g]++;
        }

        $allAvgs   = $ranked->pluck('average');
        $passCount = $ranked->where('passed', true)->count();
        $total     = $ranked->count();

        return [
            'program'           => $program,
            'period'            => $period,
            'attempt'           => $attempt,
            'total_students'    => $total,
            'class_average'     => $total > 0 ? round($allAvgs->avg(), 2) : 0,
            'highest_score'     => $total > 0 ? round($allAvgs->max(), 2) : 0,
            'lowest_score'      => $total > 0 ? round($allAvgs->min(), 2) : 0,
            'pass_rate'         => $total > 0 ? round($passCount / $total * 100, 1) : 0,
            'fail_rate'         => $total > 0 ? round(($total - $passCount) / $total * 100, 1) : 0,
            'subject_stats'     => $subjectStats->values()->all(),
            'grade_distribution'=> $gradeDist->toArray(),
            'ranked_students'   => $ranked->all(),  // keep Eloquent models intact — no toArray()
            'has_data'          => $total > 0,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ATTENDANCE SUMMARY
    // ──────────────────────────────────────────────────────────────────────────

    public function attendanceSummary(Program $program, ?AcademicPeriod $period): array
    {
        $students = Student::where('program_id', $program->id)
            ->forExams()->with('user')->get();

        $query = Attendance::where('program_id', $program->id)
            ->when($period, fn($q) => $q->where('academic_period_id', $period->id));

        $records = $query->get();

        // Days the school opened (distinct dates)
        $schoolDays = $records->pluck('date')->unique()->count();

        $studentSummaries = $students->map(function ($student) use ($records, $schoolDays) {
            $myRecords = $records->where('student_id', $student->id);
            $present   = $myRecords->where('status', 'present')->count();
            $absent    = $myRecords->where('status', 'absent')->count();
            $total     = $present + $absent;
            $pct       = $total > 0 ? round($present / $total * 100, 1) : 0;
            return [
                'student'        => $student,
                'school_days'    => $schoolDays,
                'present'        => $present,
                'absent'         => $absent,
                'total_recorded' => $total,
                'percentage'     => $pct,
                'status'         => $pct >= 75 ? 'good' : ($pct >= 50 ? 'average' : 'poor'),
            ];
        })->sortBy('percentage')->values();

        return [
            'program'           => $program,
            'period'            => $period,
            'school_days'       => $schoolDays,
            'student_summaries' => $studentSummaries,
            'overall_rate'      => $studentSummaries->avg('percentage') ?? 0,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TEACHER MARK SUBMISSION STATUS
    // ──────────────────────────────────────────────────────────────────────────

    public function markSubmissionStatus(?AcademicPeriod $period, ?int $programId = null): array
    {
        $assignments = CourseAssignment::with(['lecturer', 'course', 'program'])->get();

        if ($programId) {
            $assignments = $assignments->where('program_id', $programId);
        }

        $rows = $assignments->map(function ($a) use ($period) {
            $studentCount = Student::where('program_id', $a->program_id)
                ->whereHas('enrollments', fn($q) => $q->where('course_id', $a->course_id))
                ->forExams()->count();

            $submitted = ExamScore::where('course_id', $a->course_id)
                ->where('program_id', $a->program_id)
                ->when($period, fn($q) => $q->where('academic_period_id', $period->id))
                ->distinct('student_id')->count('student_id');

            $pct = $studentCount > 0 ? round($submitted / $studentCount * 100, 1) : 0;

            return [
                'lecturer'    => $a->lecturer,
                'course'      => $a->course,
                'program'     => $a->program,
                'expected'    => $studentCount,
                'submitted'   => $submitted,
                'missing'     => max(0, $studentCount - $submitted),
                'percentage'  => $pct,
                'complete'    => $submitted >= $studentCount && $studentCount > 0,
            ];
        })->sortBy('percentage')->values();

        return [
            'period' => $period,
            'rows'   => $rows,
            'total_complete'  => $rows->where('complete', true)->count(),
            'total_pending'   => $rows->where('complete', false)->count(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ACADEMIC TRANSCRIPT
    // ──────────────────────────────────────────────────────────────────────────

    public function transcript(Student $student): array
    {
        $programs = Program::orderBy('sequence')->get();
        $history  = [];

        foreach ($programs as $program) {
            $attempts = ExamScore::where('student_id', $student->id)
                ->where('program_id', $program->id)
                ->distinct()->orderBy('attempt')->pluck('attempt');

            if ($attempts->isEmpty()) continue;

            foreach ($attempts as $attempt) {
                $card = $this->reportCard->generate($student, $program, $attempt);
                if (!$card['has_scores']) continue;
                $history[] = [
                    'program' => $program,
                    'attempt' => $attempt,
                    'card'    => $card,
                ];
            }
        }

        return [
            'student' => $student,
            'history' => $history,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    private function resolveCa(?ExamScore $score): array
    {
        if (!$score) return [0.0, false];
        if ($score->hasSubScores()) {
            $sum = (float)($score->test1_score ?? 0) + (float)($score->groupwork_score ?? 0)
                 + (float)($score->test2_score ?? 0) + (float)($score->project_score ?? 0);
            return [$sum, true];
        }
        $legacy = $score->sba_score !== null ? (float)$score->sba_score : (float)($score->quiz_score ?? 0);
        return [$legacy, false];
    }

    private function assignRanks(Collection $items, string $key): Collection
    {
        $rank = 1;
        $prev = null;
        $skip = 0;
        return $items->map(function ($item) use (&$rank, &$prev, &$skip, $key) {
            if ($prev !== null && $item[$key] == $prev) {
                $skip++;
                $item['rank'] = $rank;
            } else {
                $rank += $skip;
                $item['rank'] = $rank;
                $rank++;
                $skip = 0;
            }
            $prev = $item[$key];
            return $item;
        });
    }

    private function emptyClassPerformance(Program $program, ?AcademicPeriod $period): array
    {
        return [
            'program'=>$program,'period'=>$period,'attempt'=>1,
            'total_students'=>0,'class_average'=>0,'highest_score'=>0,'lowest_score'=>0,
            'pass_rate'=>0,'fail_rate'=>0,'subject_stats'=>[],'grade_distribution'=>[],
            'ranked_students'=>[],'has_data'=>false,
        ];
    }
}
