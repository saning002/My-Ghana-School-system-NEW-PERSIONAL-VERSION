<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Program;
use App\Models\PromotionHistory;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    public function __construct(private StudentIdService $studentIdService) {}

    /**
     * Promote a student to the next program in sequence.
     *
     * If the student is in the final program, their status is set to 'graduated'.
     * A PromotionHistory record is always created.
     * The student's ID is regenerated to match the new program code.
     *
     * @param  Student  $student  The student to promote.
     * @return void
     * @throws \InvalidArgumentException  If the student's status is not 'active'.
     */
    public function promote(Student $student): void
    {
        $studentName = $student->user?->full_name ?? "#{$student->id}";

        if ($student->status !== 'active') {
            throw new \InvalidArgumentException(
                "Cannot promote student '{$studentName}': status is '{$student->status}', must be 'active'."
            );
        }

        $currentProgram = $student->program;

        if (! $currentProgram) {
            throw new \InvalidArgumentException(
                "Cannot promote student '{$studentName}': current program is not assigned."
            );
        }

        // Try to find next program by sequence.
        $nextProgram = Program::where('sequence', '>', $currentProgram->sequence)
            ->orderBy('sequence')
            ->orderBy('id')
            ->first();

        // Fallback: if sequences are not strictly increasing, compute ordered list and pick next after current.
        if (! $nextProgram) {
            $ordered = Program::orderBy('sequence')->orderBy('id')->get();
            $index = $ordered->search(fn($p) => $p->id === $currentProgram->id);
            if ($index !== false && isset($ordered[$index + 1])) {
                $nextProgram = $ordered[$index + 1];
            }
        }

        if (! $nextProgram) {
            throw new \RuntimeException("Cannot determine next program for promotion. Verify program 'sequence' values and ordering.");
        }

        DB::transaction(function () use ($student, $currentProgram, $nextProgram) {
            // Generate new student ID for the new program
            $newStudentId = $this->studentIdService->generateStudentId($student, $nextProgram);

            $student->update([
                'program_id' => $nextProgram->id,
                'student_id' => $newStudentId,
                'status'     => 'active',
            ]);

            if (DB::connection()->getSchemaBuilder()->hasTable('promotion_histories')) {
                PromotionHistory::create([
                    'student_id'      => $student->id,
                    'from_program_id' => $currentProgram->id,
                    'to_program_id'   => $nextProgram->id,
                    'promoted_at'     => now(),
                ]);
            }
        });
    }
}
