<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\ExamScore;
use App\Services\ReportCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReportCardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReportCardService();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function makeCourse($program, string $code = 'TST101'): Course
    {
        return Course::create([
            'name'       => 'Test Course ' . $code,
            'code'       => $code,
            'program_id' => $program->id,
        ]);
    }

    private function addScore($student, $course, $program, float $quiz, float $exam): ExamScore
    {
        return ExamScore::create([
            'student_id' => $student->id,
            'course_id'  => $course->id,
            'program_id' => $program->id,
            'quiz_score' => $quiz,
            'exam_score' => $exam,
        ]);
    }

    // ── Test: empty state ─────────────────────────────────────────────────────

    public function test_returns_has_scores_false_when_no_scores_exist(): void
    {
        $program = $this->makeProgram();
        $this->makeCourse($program);
        $student = $this->makeStudent($program);

        $result = $this->service->generate($student, $program);

        $this->assertFalse($result['has_scores']);
    }

    public function test_returns_has_scores_false_when_program_has_no_courses(): void
    {
        $program = $this->makeProgram();
        $student = $this->makeStudent($program);

        $result = $this->service->generate($student, $program);

        $this->assertFalse($result['has_scores']);
    }

    public function test_empty_state_returns_zero_aggregate_and_average(): void
    {
        $program = $this->makeProgram();
        $this->makeCourse($program);
        $student = $this->makeStudent($program);

        $result = $this->service->generate($student, $program);

        $this->assertEquals(0, $result['overall_aggregate']);
        $this->assertEquals(0, $result['overall_average']);
    }

    // ── Test: aggregate computation ───────────────────────────────────────────

    public function test_course_aggregate_equals_exam_score(): void
    {
        $program = $this->makeProgram();
        $course  = $this->makeCourse($program);
        $student = $this->makeStudent($program);
        $this->addScore($student, $course, $program, 0.0, 75.0);

        $result = $this->service->generate($student, $program);

        $this->assertEquals(75.0, $result['courses']->first()['aggregate']);
    }

    public function test_aggregate_with_zero_quiz_score(): void
    {
        $program = $this->makeProgram();
        $course  = $this->makeCourse($program);
        $student = $this->makeStudent($program);
        $this->addScore($student, $course, $program, 0.0, 60.0);

        $result = $this->service->generate($student, $program);

        $this->assertEquals(60.0, $result['courses']->first()['aggregate']);
    }

    public function test_aggregate_with_zero_exam_score(): void
    {
        $program = $this->makeProgram();
        $course  = $this->makeCourse($program);
        $student = $this->makeStudent($program);
        $this->addScore($student, $course, $program, 0.0, 0.0);

        $result = $this->service->generate($student, $program);

        $this->assertEquals(0.0, $result['courses']->first()['aggregate']);
    }

    // ── Test: overall aggregate and average ───────────────────────────────────

    public function test_overall_aggregate_equals_sum_of_course_aggregates(): void
    {
        $program = $this->makeProgram();
        $course1 = $this->makeCourse($program, 'C01');
        $course2 = $this->makeCourse($program, 'C02');
        $student = $this->makeStudent($program);
        $this->addScore($student, $course1, $program, 0.0, 70.0); // 70
        $this->addScore($student, $course2, $program, 0.0, 70.0); // 70

        $result = $this->service->generate($student, $program);

        $this->assertEquals(140.0, $result['overall_aggregate']);
    }

    public function test_overall_average_equals_aggregate_divided_by_course_count(): void
    {
        $program = $this->makeProgram();
        $course1 = $this->makeCourse($program, 'C01');
        $course2 = $this->makeCourse($program, 'C02');
        $student = $this->makeStudent($program);
        $this->addScore($student, $course1, $program, 0.0, 80.0); // 80
        $this->addScore($student, $course2, $program, 0.0, 40.0); // 40

        $result = $this->service->generate($student, $program);

        // (80 + 40) / 2 = 60
        $this->assertEquals(60.0, $result['overall_average']);
    }

    // ── Test: grade assignment ────────────────────────────────────────────────

    public function test_overall_grade_is_A1_when_average_is_80_or_above(): void
    {
        $program = $this->makeProgram();
        $course  = $this->makeCourse($program);
        $student = $this->makeStudent($program);
        $this->addScore($student, $course, $program, 0.0, 90.0); // 90

        $result = $this->service->generate($student, $program);

        $this->assertSame('A1', $result['overall_grade']);
    }

    public function test_overall_grade_is_F_when_average_is_below_40(): void
    {
        $program = $this->makeProgram();
        $course  = $this->makeCourse($program);
        $student = $this->makeStudent($program);
        $this->addScore($student, $course, $program, 0.0, 30.0); // 30

        $result = $this->service->generate($student, $program);

        $this->assertSame('F', $result['overall_grade']);
    }

    // ── Test: remarks determination ───────────────────────────────────────────

    public function test_remarks_is_promoted_when_average_is_40_or_above(): void
    {
        $program = $this->makeProgram();
        $course  = $this->makeCourse($program);
        $student = $this->makeStudent($program);
        $this->addScore($student, $course, $program, 0.0, 40.0); // 40

        $result = $this->service->generate($student, $program);

        $this->assertStringContainsString('PROMOTED', $result['remarks']);
    }

    public function test_remarks_is_failed_when_average_is_below_40(): void
    {
        $program = $this->makeProgram();
        $course  = $this->makeCourse($program);
        $student = $this->makeStudent($program);
        $this->addScore($student, $course, $program, 0.0, 35.0); // 35

        $result = $this->service->generate($student, $program);

        $this->assertStringContainsString('FAILED', $result['remarks']);
    }

    public function test_remarks_boundary_exactly_40_is_promoted(): void
    {
        $program = $this->makeProgram();
        $course  = $this->makeCourse($program);
        $student = $this->makeStudent($program);
        $this->addScore($student, $course, $program, 0.0, 40.0); // exactly 40

        $result = $this->service->generate($student, $program);

        $this->assertStringContainsString('PROMOTED', $result['remarks']);
    }

    // ── Test: return shape ────────────────────────────────────────────────────

    public function test_generate_returns_required_keys(): void
    {
        $program = $this->makeProgram();
        $course  = $this->makeCourse($program);
        $student = $this->makeStudent($program);
        $this->addScore($student, $course, $program, 0.0, 70.0);

        $result = $this->service->generate($student, $program);

        foreach (['student', 'program', 'courses', 'overall_aggregate',
                  'overall_average', 'overall_grade', 'remarks', 'has_scores'] as $key) {
            $this->assertArrayHasKey($key, $result, "Missing key: {$key}");
        }
    }

    public function test_has_scores_is_true_when_scores_exist(): void
    {
        $program = $this->makeProgram();
        $course  = $this->makeCourse($program);
        $student = $this->makeStudent($program);
        $this->addScore($student, $course, $program, 0.0, 70.0);

        $result = $this->service->generate($student, $program);

        $this->assertTrue($result['has_scores']);
    }

    public function test_overall_position_is_calculated_correctly(): void
    {
        $program = $this->makeProgram();
        $course1 = $this->makeCourse($program, 'C1');
        $course2 = $this->makeCourse($program, 'C2');

        // We create 3 students
        $student1 = $this->makeStudent($program);
        $student2 = $this->makeStudent($program);
        $student3 = $this->makeStudent($program);

        // Student 1 average: (90 + 90) / 2 = 90
        $this->addScore($student1, $course1, $program, 0.0, 90.0);
        $this->addScore($student1, $course2, $program, 0.0, 90.0);

        // Student 2 average: (80 + 80) / 2 = 80
        $this->addScore($student2, $course1, $program, 0.0, 80.0);
        $this->addScore($student2, $course2, $program, 0.0, 80.0);

        // Student 3 average: (95 + 95) / 2 = 95
        $this->addScore($student3, $course1, $program, 0.0, 95.0);
        $this->addScore($student3, $course2, $program, 0.0, 95.0);

        // Generate for Student 1 (Average = 90, ranked 2nd behind Student 3 who has 95)
        $result1 = $this->service->generate($student1, $program);
        $this->assertEquals(2, $result1['overall_position']);
        $this->assertEquals(3, $result1['total_students_in_exam']);

        // Generate for Student 2 (Average = 80, ranked 3rd behind 95 and 90)
        $result2 = $this->service->generate($student2, $program);
        $this->assertEquals(3, $result2['overall_position']);
        $this->assertEquals(3, $result2['total_students_in_exam']);

        // Generate for Student 3 (Average = 95, ranked 1st)
        $result3 = $this->service->generate($student3, $program);
        $this->assertEquals(1, $result3['overall_position']);
        $this->assertEquals(3, $result3['total_students_in_exam']);
    }
}
