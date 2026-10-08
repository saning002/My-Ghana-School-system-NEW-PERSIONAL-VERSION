<?php

namespace Tests\Unit;

use App\Models\Program;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PromotionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PromotionService();
    }

    // ── Test: advance to next program ────────────────────────────────────────

    public function test_active_student_is_moved_to_next_program(): void
    {
        $prog1   = $this->makeProgram('First Semester', 1);
        $prog2   = $this->makeProgram('Second Semester', 2);
        $student = $this->makeStudent($prog1);

        $this->service->promote($student->fresh());

        $this->assertDatabaseHas('students', [
            'id'         => $student->id,
            'program_id' => $prog2->id,
            'status'     => 'active',
        ]);
    }

    public function test_next_program_is_the_one_with_minimum_higher_sequence(): void
    {
        $prog1   = $this->makeProgram('First Semester', 1);
        $prog2   = $this->makeProgram('Second Semester', 2);
        $prog3   = $this->makeProgram('Third Semester', 3);
        $student = $this->makeStudent($prog1);

        $this->service->promote($student->fresh());

        // Should land on sequence 2, not 3
        $this->assertDatabaseHas('students', [
            'id'         => $student->id,
            'program_id' => $prog2->id,
        ]);
    }

    // ── Test: graduation from final program ──────────────────────────────────

    public function test_student_in_final_program_is_graduated(): void
    {
        $prog    = $this->makeProgram('Final Program', 5);
        $student = $this->makeStudent($prog);

        $this->service->promote($student->fresh());

        $this->assertDatabaseHas('students', [
            'id'     => $student->id,
            'status' => 'graduated',
        ]);
    }

    public function test_graduated_student_program_id_is_unchanged(): void
    {
        $prog    = $this->makeProgram('Final Program', 5);
        $student = $this->makeStudent($prog);

        $this->service->promote($student->fresh());

        $this->assertDatabaseHas('students', [
            'id'         => $student->id,
            'program_id' => $prog->id,
        ]);
    }

    // ── Test: promotion history is recorded ──────────────────────────────────

    public function test_promotion_creates_history_record(): void
    {
        $prog1   = $this->makeProgram('First Semester', 1);
        $prog2   = $this->makeProgram('Second Semester', 2);
        $student = $this->makeStudent($prog1);

        $this->service->promote($student->fresh());

        $this->assertDatabaseHas('promotion_histories', [
            'student_id'      => $student->id,
            'from_program_id' => $prog1->id,
            'to_program_id'   => $prog2->id,
        ]);
    }

    public function test_graduation_creates_history_with_null_to_program(): void
    {
        $prog    = $this->makeProgram('Final Program', 5);
        $student = $this->makeStudent($prog);

        $this->service->promote($student->fresh());

        $this->assertDatabaseHas('promotion_histories', [
            'student_id'      => $student->id,
            'from_program_id' => $prog->id,
            'to_program_id'   => null,
        ]);
    }

    // ── Test: inactive student is rejected ───────────────────────────────────

    public function test_suspended_student_throws_exception(): void
    {
        $prog    = $this->makeProgram('First Semester', 1);
        $student = $this->makeStudent($prog, 'suspended');

        $this->expectException(InvalidArgumentException::class);
        $this->service->promote($student->fresh());
    }

    public function test_graduated_student_throws_exception(): void
    {
        $prog    = $this->makeProgram('First Semester', 1);
        $student = $this->makeStudent($prog, 'graduated');

        $this->expectException(InvalidArgumentException::class);
        $this->service->promote($student->fresh());
    }

    public function test_inactive_student_does_not_create_history_record(): void
    {
        $prog    = $this->makeProgram('First Semester', 1);
        $student = $this->makeStudent($prog, 'suspended');

        try {
            $this->service->promote($student->fresh());
        } catch (InvalidArgumentException) {
            // expected
        }

        $this->assertDatabaseMissing('promotion_histories', [
            'student_id' => $student->id,
        ]);
    }
}
