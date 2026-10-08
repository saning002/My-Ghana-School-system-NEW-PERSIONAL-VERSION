<?php

namespace App\Services;

use App\Models\Program;
use App\Models\ChurchBranch;
use App\Models\Student;

/**
 * Service for handling student ID generation and regeneration
 */
class StudentIdService
{
    /**
     * Extract the program code from a program name.
     *
     * Program codes are derived as follows:
     * - Pre-College → PC
     * - First Semester → FS
     * - Second Semester → SS
     * - Third Semester → TS
     * - Third Semester Practical / TP → TP
     * - Otherwise → first 2 letters of program name
     *
     * @param  Program|string  $program  Program object or program name
     * @return string  The 2-letter program code
     */
    public function getProgramCode($program): string
    {
        $programName = is_string($program) ? $program : ($program?->name ?? 'Default');
        $name = strtolower($programName);

        return match(true) {
            str_contains($name, 'pre')                                   => 'PC',
            str_contains($name, 'first')                                 => 'FS',
            str_contains($name, 'second')                                => 'SS',
            str_contains($name, 'practical') || str_contains($name, 'tp') => 'TP',
            str_contains($name, 'third')                                 => 'TS',
            default => strtoupper(substr(preg_replace('/[^a-z]/i', '', $programName ?: 'XX'), 0, 2)),
        };
    }

    /**
     * Generate a new student ID — PERMANENT format.
     *
     * Format: RFIS/{8 UNIQUE DIGITS}
     * Example: RFIS/20264231
     *
     * This ID NEVER changes when a student advances to the next class/program.
     * It is assigned once at admission and stays for life.
     * The 8 digits are fully random but guaranteed to be unique.
     */
    public function generateStudentId(Student $student, Program $program): string
    {
        // Prefix comes from admin settings — never hardcoded
        $prefix = rtrim(\App\Models\Setting::get('school_id_prefix', 'STU'), '/') . '/';

        do {
            $digits = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            $candidateId = $prefix . $digits;
        } while (Student::where('student_id', $candidateId)->exists());

        return $candidateId;
    }

    /**
     * Check if a student's ID matches their current program.
     *
     * @param  Student  $student  The student to check
     * @return bool  True if ID matches program, false otherwise
     */
    public function studentIdMatchesProgram(Student $student): bool
    {
        $student->loadMissing('program', 'churchBranch');
        if (! $student->program) {
            return false;
        }
        $expectedProgCode = $this->getProgramCode($student->program);
        
        // Extract program code from student ID (KMC/{branch}/{program}/{seq})
        $parts = explode('/', $student->student_id ?? '');
        if (count($parts) < 3) {
            return false;
        }
        
        $currentProgCode = $parts[2];
        return strtoupper($currentProgCode) === strtoupper($expectedProgCode);
    }
}
