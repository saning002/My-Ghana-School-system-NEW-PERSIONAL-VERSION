<?php
// scripts/generate_sample_report.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application like artisan does
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Str;

// Build dummy data matching ReportCardService->generate output
$student = (object) [
    'student_id' => 'SAMP/001',
    'user' => (object) ['full_name' => 'John Sample'],
    'churchBranch' => (object) ['name' => 'MAIN CAMPUS'],
];
$program = (object) ['name' => 'PRE-COLLEGE PROGRAM'];

$courses = [];
$subjects = ['Mathematics','English Language','Integrated Science','Social Studies','ICT','Economics'];
foreach ($subjects as $i => $name) {
    $quiz = rand(10,30);
    $exam = rand(40,80);
    $total = $quiz + $exam;
    $courses[] = [
        'course' => (object)['name' => $name],
        'quiz_score' => $quiz,
        'exam_score' => $exam,
        'aggregate' => $total,
        'position' => $i + 1,
        'grade' => null,
    ];
}

$overall_aggregate = array_sum(array_column($courses, 'aggregate'));
$overall_average = $overall_aggregate / count($courses);
$result = $overall_average >= 40 ? 'Pass' : 'Fail';
$overall_grade_description = ''; // left blank; view will use GradingScale for per-course
$remarks = $result === 'Pass' ? 'Promoted' : 'Not promoted';

$logoBase64 = \App\Support\ReportCardPdfAssets::logoBase64();
$photoBase64 = null;

$data = [
    'student' => $student,
    'program' => $program,
    'courses' => $courses,
    'overall_aggregate' => $overall_aggregate,
    'overall_average' => $overall_average,
    'result' => $result,
    'overall_grade_description' => $overall_grade_description,
    'remarks' => $remarks,
    'logoBase64' => $logoBase64,
    'photoBase64' => $photoBase64,
    'exam_date' => now()->toDateString(),
    'total_courses' => count($courses),
];

$html = view('pdf.report_card', $data)->render();
$storagePath = storage_path('app/report_card_sample');
if (!is_dir($storagePath)) mkdir($storagePath, 0755, true);
file_put_contents($storagePath . '/report_card_sample.html', $html);

// Attempt PDF generation via Dompdf (same options as controller)
try {
    $fontDir = sys_get_temp_dir() . '/dompdf_fonts';
    if (!is_dir($fontDir)) mkdir($fontDir, 0755, true);
    $options = new Dompdf\Options();
    $options->setIsHtml5ParserEnabled(true);
    $options->setIsRemoteEnabled(false);
    $options->setDefaultFont('DejaVu Sans');
    $options->setChroot([base_path(), public_path()]);
    $options->setFontDir($fontDir);
    $options->setFontCache($fontDir);
    $options->setTempDir(sys_get_temp_dir());

    $dompdf = new Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('a4', 'portrait');
    $dompdf->render();
    $pdfOutput = $dompdf->output();
    file_put_contents($storagePath . '/report_card_sample.pdf', $pdfOutput);
    echo "Wrote HTML and PDF to: $storagePath\n";
} catch (Throwable $e) {
    echo "Generated HTML at: $storagePath/report_card_sample.html\n";
    echo "PDF generation failed: " . $e->getMessage() . "\n";
}

return 0;
