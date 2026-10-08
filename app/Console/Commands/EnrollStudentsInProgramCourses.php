<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnrollStudentsInProgramCourses extends Command
{
    protected $signature = 'students:enroll-program-courses
                            {--dry-run : Show what would be enrolled without making changes}';

    protected $description = 'Enroll all existing students in every course belonging to their assigned program (backfill + sync)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info($dryRun
            ? 'DRY RUN — no changes will be made'
            : 'Enrolling all students in their program courses...'
        );

        $year        = date('Y') . '/' . (date('Y') + 1);
        $students    = Student::whereNotNull('program_id')->with('program')->get();
        $enrolled    = 0;
        $skipped     = 0;

        if ($students->isEmpty()) {
            $this->warn('No students found.');
            return 0;
        }

        // Pre-load all courses grouped by program_id for efficiency
        $coursesByProgram = Course::all()->groupBy('program_id');

        foreach ($students as $student) {
            $courses = $coursesByProgram->get($student->program_id, collect());

            if ($courses->isEmpty()) {
                $this->line("  [SKIP] {$student->student_id} — program has no courses");
                $skipped++;
                continue;
            }

            foreach ($courses as $course) {
                $alreadyEnrolled = DB::table('enrollments')
                    ->where('student_id', $student->id)
                    ->where('course_id', $course->id)
                    ->exists();

                if ($alreadyEnrolled) {
                    continue;
                }

                $this->line("  [ENROLL] {$student->student_id} → {$course->code}: {$course->name}");

                if (!$dryRun) {
                    DB::table('enrollments')->updateOrInsert(
                        [
                            'student_id' => $student->id,
                            'course_id'  => $course->id,
                            'program_id' => $student->program_id,
                        ],
                        [
                            'year'       => $year,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }

                $enrolled++;
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("Dry run complete. {$enrolled} enrollment(s) would be created. {$skipped} student(s) skipped (no courses in program).");
            $this->warn('Run without --dry-run to apply changes.');
        } else {
            $this->info("Done. {$enrolled} new enrollment(s) created. {$skipped} student(s) skipped.");
        }

        return 0;
    }
}
