<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Enroll every existing student in all courses belonging to their program.
     * Runs exactly once. Safe to run — uses updateOrInsert so no duplicates.
     */
    public function up(): void
    {
        $year = date('Y') . '/' . (date('Y') + 1);

        $students = DB::table('students')
            ->whereNotNull('program_id')
            ->select('id', 'program_id')
            ->get();

        $courses = DB::table('courses')
            ->select('id', 'program_id')
            ->get()
            ->groupBy('program_id');

        foreach ($students as $student) {
            $programCourses = $courses->get($student->program_id, collect());

            foreach ($programCourses as $course) {
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
        }
    }

    public function down(): void
    {
        // No rollback — we don't want to accidentally remove enrollments
    }
};
