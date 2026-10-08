<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Models\Course;
use App\Models\Program;
use App\Models\ExamScore;
use PhpOffice\PhpSpreadsheet\IOFactory;

$programId = $_GET['program_id'] ?? null;
$force = isset($_GET['force']) && $_GET['force'] == 1;

if (!$programId) {
    die("Please provide ?program_id=X in the URL. Example: run-import.php?program_id=1");
}

$program = Program::find($programId);
if (!$program) {
    die("Program with ID $programId not found.");
}

$file = __DIR__.'/../extracted_exam_report.xlsx';
if (!file_exists($file)) {
    die("Excel file not found.");
}

echo "<pre style='background:#f4f4f4;padding:20px;font-size:16px;'>";
echo "Loading Excel file...\n\n";
$spreadsheet = IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$data = $worksheet->toArray();

$headers = $data[0];
$courseMap = []; 

for ($i = 2; $i < count($headers); $i++) {
    $header = trim($headers[$i] ?? '');
    if (!$header) continue;
    $searchName = trim(str_ireplace([' Exams', ' exams'], '', $header));
    $course = Course::where('name', 'ILIKE', "%{$searchName}%")->first();
    if ($course) {
        $courseMap[$i] = $course->id;
        echo "✅ Mapped Column '$header' to Course: {$course->name} (ID: {$course->id})\n";
    } else {
        echo "❌ WARNING: Could not map Column '$header' to any course.\n";
    }
}

$toInsert = [];
for ($r = 1; $r < count($data); $r++) {
    $row = $data[$r];
    $studentName = trim($row[1] ?? '');
    if (!$studentName) continue;

    $user = User::where('full_name', 'ILIKE', "%{$studentName}%")->first();
    if (!$user) {
        $parts = explode(' ', $studentName);
        if (count($parts) > 1) {
            $query = User::query();
            foreach ($parts as $part) {
                $query->where('full_name', 'ILIKE', "%{$part}%");
            }
            $user = $query->first();
        }
    }
    
    if (!$user || !$user->student) {
        echo "❌ WARNING: Student not found in system: $studentName\n";
        continue;
    }
    
    $student = $user->student;

    foreach ($courseMap as $colIndex => $courseId) {
        $score = trim($row[$colIndex] ?? '');
        if ($score !== '' && is_numeric($score)) {
            $toInsert[] = [
                'student_id' => $student->id,
                'course_id' => $courseId,
                'program_id' => $program->id,
                'attempt' => 1,
                'exam_score' => (float) $score
            ];
        }
    }
}

echo "\nFound " . count($toInsert) . " valid scores to import.\n";

if (!$force) {
    echo "\n<b>This was a dry run.</b> Click the button below to save to DB:\n\n";
    echo "<a href='?program_id={$programId}&force=1' style='padding:10px 20px;background:blue;color:white;text-decoration:none;border-radius:5px;'>Save Scores Now</a>";
} else {
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
    echo "\n✅ <b>SUCCESS! Scores have been imported into the database.</b>\n";
}
echo "</pre>";
