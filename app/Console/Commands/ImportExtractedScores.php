<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Course;
use App\Models\Program;
use App\Models\ExamScore;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportExtractedScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:extracted-scores {file} {program_id} {--attempt=1} {--force : Actually import the scores}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import scores from an extracted excel report using student names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $programId = $this->argument('program_id');
        $attempt = $this->option('attempt');
        $force = $this->option('force');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $program = Program::find($programId);
        if (!$program) {
            $this->error("Program with ID $programId not found.");
            return 1;
        }

        $this->info("Loading Excel file...");
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();

        if (count($data) < 2) {
            $this->error("The Excel file seems to be empty or missing data.");
            return 1;
        }

        $headers = $data[0];
        
        $courseMap = []; // index => course_id
        for ($i = 2; $i < count($headers); $i++) {
            $header = trim($headers[$i] ?? '');
            if (!$header) continue;

            $searchName = trim(str_ireplace([' Exams', ' exams'], '', $header));
            
            // Try to find course
            $course = Course::where('name', 'ILIKE', "%{$searchName}%")->first();
            if ($course) {
                $courseMap[$i] = $course->id;
                $this->info("Mapped Column '$header' to Course: {$course->name} (ID: {$course->id})");
            } else {
                $this->warn("Could not map Column '$header' to any course. Scores for this column will be ignored.");
            }
        }

        if (empty($courseMap)) {
            $this->error("No courses could be mapped. Aborting.");
            return 1;
        }

        $this->info("Processing students...");
        $toInsert = [];

        for ($r = 1; $r < count($data); $r++) {
            $row = $data[$r];
            $studentName = trim($row[1] ?? '');
            if (!$studentName) continue;

            // Simple match using ILIKE
            $user = User::where('full_name', 'ILIKE', "%{$studentName}%")->first();
            if (!$user) {
                // Try splitting by space and matching parts as a fallback
                $parts = explode(' ', $studentName);
                if (count($parts) > 1) {
                    $query = User::query();
                    foreach ($parts as $part) {
                        $query->where('full_name', 'ILIKE', "%{$part}%");
                    }
                    $user = $query->first();
                }

                if (!$user) {
                    $this->warn("Student not found in system: $studentName");
                    continue;
                }
            }

            $student = $user->student;
            if (!$student) {
                $this->warn("User $studentName found but is not a registered student.");
                continue;
            }

            foreach ($courseMap as $colIndex => $courseId) {
                $score = trim($row[$colIndex] ?? '');
                if ($score !== '' && is_numeric($score)) {
                    $toInsert[] = [
                        'student_id' => $student->id,
                        'student_name' => $studentName, // for debug display
                        'course_id' => $courseId,
                        'program_id' => $program->id,
                        'attempt' => $attempt,
                        'exam_score' => (float) $score
                    ];
                }
            }
        }

        $this->info("Found " . count($toInsert) . " valid scores to import.");

        if (!$force) {
            foreach ($toInsert as $record) {
                $this->line(" - Will import score {$record['exam_score']} for {$record['student_name']} (Course ID: {$record['course_id']})");
            }
            $this->warn("\nThis was a dry run. Use --force to actually save the scores to the database.");
            return 0;
        }

        $this->info("Saving to database...");
        foreach ($toInsert as $record) {
            ExamScore::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'course_id'  => $record['course_id'],
                    'program_id' => $record['program_id'],
                    'attempt'    => $record['attempt'],
                ],
                [
                    'quiz_score' => null,
                    'exam_score' => $record['exam_score'],
                ]
            );
        }

        $this->info("Successfully imported scores!");
        return 0;
    }
}
