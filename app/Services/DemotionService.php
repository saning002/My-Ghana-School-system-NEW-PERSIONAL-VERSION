<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Program;
use App\Models\PromotionHistory;
use Illuminate\Support\Facades\DB;

/**
 * Service for handling student demotion to previous programs
 */
class DemotionService
{
    public function __construct(private StudentIdService $studentIdService) {}

    /**
     * Demote a student to the previous program in sequence.
     *
     * The student must have promotion history to be demoted.
     * The new status is set to 'active'.
     * A new PromotionHistory record is created with to_program_id as the previous program.
     *
     * @param  Student  $student  The student to demote.
     * @return void
     * @throws \InvalidArgumentException  If the student cannot be demoted.
     */
    public function demote(Student $student): void
    {
        // Load related data
        $student->loadMissing('user', 'program', 'churchBranch');
        $studentName = $student->user?->full_name ?? "#{$student->id}";

        if (! DB::connection()->getSchemaBuilder()->hasTable('promotion_histories')) {
            throw new \RuntimeException("Promotion histories feature is not available.");
        }

        // Get the promotion history to find the previous program
        $promotionHistory = PromotionHistory::where('to_program_id', $student->program_id)
            ->orderByDesc('promoted_at')
            ->first();

        if (!$promotionHistory) {
            throw new \InvalidArgumentException(
                "Cannot demote student '{$studentName}': no promotion history found. "
                . "Student must have been promoted from another program."
            );
        }

        $previousProgram = Program::find($promotionHistory->from_program_id);
        
        if (!$previousProgram) {
            throw new \RuntimeException(
                "Previous program not found. Promotion history may be corrupted."
            );
        }

        DB::transaction(function () use ($student, $previousProgram, $promotionHistory) {
            // Generate new student ID for the previous program
            $newStudentId = $this->studentIdService->generateStudentId($student, $previousProgram);

            $student->update([
                'program_id' => $previousProgram->id,
                'student_id' => $newStudentId,
                'status'     => 'active',
            ]);

            // Create a demotion record (to_program_id points to the demoted-to program)
            PromotionHistory::create([
                'student_id'      => $student->id,
                'from_program_id' => $promotionHistory->to_program_id,
                'to_program_id'   => $previousProgram->id,
                'promoted_at'     => now(),
            ]);
        });
    }

    /**
     * Get the program a student can be demoted to.
     *
     * Returns the previous program based on promotion history, or null if none exists.
     *
     * @param  Student  $student  The student to check
     * @return Program|null  The program to demote to, or null
     */
    public function getPreviousProgram(Student $student): ?Program
    {
        if (! DB::connection()->getSchemaBuilder()->hasTable('promotion_histories')) {
            return null;
        }

        $promotionHistory = PromotionHistory::where('to_program_id', $student->program_id)
            ->orderByDesc('promoted_at')
            ->first();

        return $promotionHistory ? Program::find($promotionHistory->from_program_id) : null;
    }
}
