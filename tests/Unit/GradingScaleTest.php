<?php

namespace Tests\Unit;

use App\Services\GradingScale;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for GradingScale::assign()
 *
 * Grade bands:
 *   A1 → score >= 80
 *   A2 → score >= 70 and < 80
 *   A3 → score >= 60 and < 70
 *   B1 → score >= 50 and < 60
 *   B2 → score >= 40 and < 50
 *   F  → score < 40
 */
class GradingScaleTest extends TestCase
{
    // ── A1 band ──────────────────────────────────────────────────────────────

    public function test_score_of_100_returns_A1(): void
    {
        $this->assertSame('A1', GradingScale::assign(100.0));
    }

    public function test_score_of_80_returns_A1(): void
    {
        $this->assertSame('A1', GradingScale::assign(80.0));
    }

    public function test_score_of_90_returns_A1(): void
    {
        $this->assertSame('A1', GradingScale::assign(90.0));
    }

    // ── A2 band ──────────────────────────────────────────────────────────────

    public function test_score_of_79_point_9_returns_A2(): void
    {
        $this->assertSame('A2', GradingScale::assign(79.9));
    }

    public function test_score_of_70_returns_A2(): void
    {
        $this->assertSame('A2', GradingScale::assign(70.0));
    }

    public function test_score_of_75_returns_A2(): void
    {
        $this->assertSame('A2', GradingScale::assign(75.0));
    }

    // ── A3 band ──────────────────────────────────────────────────────────────

    public function test_score_of_69_point_9_returns_A3(): void
    {
        $this->assertSame('A3', GradingScale::assign(69.9));
    }

    public function test_score_of_60_returns_A3(): void
    {
        $this->assertSame('A3', GradingScale::assign(60.0));
    }

    public function test_score_of_65_returns_A3(): void
    {
        $this->assertSame('A3', GradingScale::assign(65.0));
    }

    // ── B1 band ──────────────────────────────────────────────────────────────

    public function test_score_of_59_point_9_returns_B1(): void
    {
        $this->assertSame('B1', GradingScale::assign(59.9));
    }

    public function test_score_of_50_returns_B1(): void
    {
        $this->assertSame('B1', GradingScale::assign(50.0));
    }

    public function test_score_of_55_returns_B1(): void
    {
        $this->assertSame('B1', GradingScale::assign(55.0));
    }

    // ── B2 band ──────────────────────────────────────────────────────────────

    public function test_score_of_49_point_9_returns_B2(): void
    {
        $this->assertSame('B2', GradingScale::assign(49.9));
    }

    public function test_score_of_40_returns_B2(): void
    {
        $this->assertSame('B2', GradingScale::assign(40.0));
    }

    public function test_score_of_45_returns_B2(): void
    {
        $this->assertSame('B2', GradingScale::assign(45.0));
    }

    // ── F band ───────────────────────────────────────────────────────────────

    public function test_score_of_39_point_9_returns_F(): void
    {
        $this->assertSame('F', GradingScale::assign(39.9));
    }

    public function test_score_of_0_returns_F(): void
    {
        $this->assertSame('F', GradingScale::assign(0.0));
    }

    public function test_score_of_20_returns_F(): void
    {
        $this->assertSame('F', GradingScale::assign(20.0));
    }

    // ── Return value is always a valid grade ─────────────────────────────────

    #[\PHPUnit\Framework\Attributes\DataProvider('scoreProvider')]
    public function test_assign_always_returns_valid_grade(float $score): void
    {
        $valid = ['A1', 'A2', 'A3', 'B1', 'B2', 'F'];
        $this->assertContains(GradingScale::assign($score), $valid);
    }

    public static function scoreProvider(): array
    {
        return array_map(
            fn(int $i) => [(float) $i],
            range(0, 100)
        );
    }
}
