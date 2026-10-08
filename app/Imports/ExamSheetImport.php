<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\ExamScore;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExamSheetImport implements ToCollection, WithHeadingRow
{
    private $programId;
    private $attempt;

    public function __construct($programId, $attempt)
    {
        $this->programId = $programId;
        $this->attempt   = $attempt;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Heading row is slugified by Maatwebsite Excel
            $studentId = $row['system_student_id'] ?? null;
            if (!$studentId) continue;

            $student = Student::where('student_id', $studentId)->first();
            if (!$student) continue;

            // Collect all SBA/Exam values keyed by course ID
            // SBA column slug looks like: course_id_14_some_name_sba_30
            // Exam column slug looks like: course_id_14_some_name_exam_70
            $sbaScores  = [];
            $examScores = [];

            foreach ($row as $key => $value) {
                // Match course ID from column slug (e.g. course_id_14_...)
                if (preg_match('/course_id_(\d+)/', $key, $matches)) {
                    $courseId = (int) $matches[1];

                    if (str_contains($key, '_quiz_') || str_contains($key, '_sba_')) {
                        $sbaScores[$courseId] = ($value !== null && $value !== '') ? (float) $value : null;
                    } elseif (str_contains($key, '_exam_')) {
                        $examScores[$courseId] = ($value !== null && $value !== '') ? (float) $value : null;
                    }
                }
            }

            // Merge all course IDs that appear in either column
            $allCourseIds = array_unique(array_merge(array_keys($sbaScores), array_keys($examScores)));

            foreach ($allCourseIds as $courseId) {
                $sba  = $sbaScores[$courseId] ?? null;
                $exam = $examScores[$courseId] ?? null;

                // Skip if both are empty
                if ($sba === null && $exam === null) continue;

                ExamScore::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'course_id'  => $courseId,
                        'program_id' => $this->programId,
                        'attempt'    => $this->attempt,
                    ],
                    [
                        'quiz_score' => $sba,
                        'sba_score'  => $sba,
                        'exam_score' => $exam,
                    ]
                );
            }
        }
    }
}
