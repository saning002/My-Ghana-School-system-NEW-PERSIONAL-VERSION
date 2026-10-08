<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\Program;
use App\Models\ProgramFee;
use App\Models\PromotionHistory;
use App\Services\FeeCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private FeeCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FeeCalculationService();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function makeProgramWithFee(string $name, int $sequence, float $fee = 0): Program
    {
        $program = $this->makeProgram($name, $sequence);

        if ($fee > 0) {
            ProgramFee::create(['program_id' => $program->id, 'amount' => $fee]);
        }

        return $program;
    }

    private function addPayment($student, float $amount): Payment
    {
        return Payment::create([
            'student_id'   => $student->id,
            'amount_paid'  => $amount,
            'payment_date' => now()->toDateString(),
        ]);
    }

    // ── Test: balance = total - paid ─────────────────────────────────────────

    public function test_balance_equals_total_minus_paid(): void
    {
        $program = $this->makeProgramWithFee('First Semester', 1, 500.00);
        $student = $this->makeStudent($program);
        $this->addPayment($student, 200.00);

        $result = $this->service->calculate($student->fresh());

        $this->assertEquals(500.00, $result['total']);
        $this->assertEquals(200.00, $result['paid']);
        $this->assertEquals(300.00, $result['balance']);
    }

    public function test_balance_is_zero_when_fully_paid(): void
    {
        $program = $this->makeProgramWithFee('First Semester', 1, 500.00);
        $student = $this->makeStudent($program);
        $this->addPayment($student, 500.00);

        $result = $this->service->calculate($student->fresh());

        $this->assertEquals(0.00, $result['balance']);
    }

    public function test_balance_is_negative_when_overpaid(): void
    {
        $program = $this->makeProgramWithFee('First Semester', 1, 300.00);
        $student = $this->makeStudent($program);
        $this->addPayment($student, 400.00);

        $result = $this->service->calculate($student->fresh());

        $this->assertEquals(-100.00, $result['balance']);
    }

    public function test_multiple_payments_are_summed(): void
    {
        $program = $this->makeProgramWithFee('First Semester', 1, 600.00);
        $student = $this->makeStudent($program);
        $this->addPayment($student, 100.00);
        $this->addPayment($student, 150.00);
        $this->addPayment($student, 200.00);

        $result = $this->service->calculate($student->fresh());

        $this->assertEquals(450.00, $result['paid']);
        $this->assertEquals(150.00, $result['balance']);
    }

    // ── Test: missing ProgramFee defaults to zero ─────────────────────────────

    public function test_missing_program_fee_contributes_zero(): void
    {
        $program = $this->makeProgramWithFee('No Fee Program', 1, 0);
        $student = $this->makeStudent($program);

        $result = $this->service->calculate($student->fresh());

        $this->assertEquals(0.00, $result['total']);
        $this->assertEquals(0.00, $result['balance']);
    }

    public function test_missing_program_fee_does_not_cause_error(): void
    {
        $program = $this->makeProgramWithFee('No Fee Program', 1, 0);
        $student = $this->makeStudent($program);
        $this->addPayment($student, 50.00);

        $result = $this->service->calculate($student->fresh());

        $this->assertIsFloat($result['total']);
        $this->assertIsFloat($result['paid']);
        $this->assertIsFloat($result['balance']);
    }

    public function test_branch_specific_program_fee_is_used_for_student_branch(): void
    {
        $program = $this->makeProgramWithFee('First Semester', 1, 0);
        $branchA = $this->makeChurchBranch();
        $branchB = $this->makeChurchBranch();

        ProgramFee::create([
            'program_id' => $program->id,
            'church_branch_id' => $branchA->id,
            'amount' => 300.00,
            'exam_fee' => 20.00,
        ]);

        ProgramFee::create([
            'program_id' => $program->id,
            'church_branch_id' => $branchB->id,
            'amount' => 500.00,
            'exam_fee' => 30.00,
        ]);

        $studentA = $this->makeStudent($program);
        $studentA->update(['church_branch_id' => $branchA->id]);

        $studentB = $this->makeStudent($program);
        $studentB->update(['church_branch_id' => $branchB->id]);

        $resultA = $this->service->calculate($studentA->fresh());
        $resultB = $this->service->calculate($studentB->fresh());

        $this->assertEquals(320.00, $resultA['total']);
        $this->assertEquals(530.00, $resultB['total']);
    }

    // ── Test: payment deletion recomputes balance ─────────────────────────────

    public function test_deleting_payment_increases_balance(): void
    {
        $program = $this->makeProgramWithFee('First Semester', 1, 500.00);
        $student = $this->makeStudent($program);
        $payment = $this->addPayment($student, 200.00);

        $before = $this->service->calculate($student->fresh());
        $this->assertEquals(300.00, $before['balance']);

        $payment->delete();

        $after = $this->service->calculate($student->fresh());
        $this->assertEquals(500.00, $after['balance']);
    }

    // ── Test: promotion history programs are included in total ────────────────

    public function test_total_includes_fees_from_previous_programs(): void
    {
        $prog1   = $this->makeProgramWithFee('First Semester', 1, 300.00);
        $prog2   = $this->makeProgramWithFee('Second Semester', 2, 400.00);
        $student = $this->makeStudent($prog2); // currently in prog2

        PromotionHistory::create([
            'student_id'      => $student->id,
            'from_program_id' => $prog1->id,
            'to_program_id'   => $prog2->id,
            'promoted_at'     => now(),
        ]);

        $result = $this->service->calculate($student->fresh());

        $this->assertEquals(700.00, $result['total']);
    }

    // ── Test: return shape ────────────────────────────────────────────────────

    public function test_calculate_returns_required_keys(): void
    {
        $program = $this->makeProgramWithFee('First Semester', 1, 100.00);
        $student = $this->makeStudent($program);

        $result = $this->service->calculate($student->fresh());

        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('paid', $result);
        $this->assertArrayHasKey('balance', $result);
    }

    public function test_calculate_returns_floats(): void
    {
        $program = $this->makeProgramWithFee('First Semester', 1, 100.00);
        $student = $this->makeStudent($program);

        $result = $this->service->calculate($student->fresh());

        $this->assertIsFloat($result['total']);
        $this->assertIsFloat($result['paid']);
        $this->assertIsFloat($result['balance']);
    }
}
