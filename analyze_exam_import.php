<?php
/**
 * Standalone Exam Score Import Analyzer
 * 
 * This script analyzes the exam report and generates import instructions
 * without requiring a database connection. The data can then be imported
 * on the Render deployment using the artisan command.
 */

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$file = 'c:\Users\sammy\Desktop\extracted_exam_report.xlsx';

if (!file_exists($file)) {
    die("Error: File not found: $file\n");
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         EXAM SCORE IMPORT ANALYZER                            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$reader = new Xlsx();
$spreadsheet = $reader->load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

echo "📋 FILE ANALYSIS:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "File: " . basename($file) . "\n";
echo "Sheet: " . $sheet->getTitle() . "\n";
echo "Total Rows: " . count($data) . " (including header)\n";
echo "Data Rows: " . (count($data) - 1) . "\n\n";

// Parse headers
$headers = $data[0];
echo "📊 COLUMN MAPPING:\n";
echo "─────────────────────────────────────────────────────────────────\n";
for ($i = 0; $i < count($headers); $i++) {
    echo "  [$i] " . ($headers[$i] ?? 'empty') . "\n";
}

// Analyze data
echo "\n📍 SAMPLE DATA:\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "Row#  | Student Name                      | Scores\n";
echo "─────────────────────────────────────────────────────────────────\n";

$students = [];
for ($r = 1; $r < count($data) && $r <= 10; $r++) {
    $row = $data[$r];
    $studentName = trim($row[1] ?? '');
    if (!$studentName) continue;
    
    $scores = [];
    for ($c = 2; $c < count($row); $c++) {
        $score = $row[$c];
        if ($score !== null && $score !== '') {
            $scores[] = trim($score);
        }
    }
    
    $students[] = [
        'row' => $r,
        'name' => $studentName,
        'scores' => $scores
    ];
    
    echo str_pad($r, 4) . "   | " . str_pad($studentName, 33) . " | " . implode(', ', $scores) . "\n";
}

echo "\n🎯 PROGRAM/COURSE MAPPING DETECTED:\n";
echo "─────────────────────────────────────────────────────────────────\n";
$programColumns = [];
for ($i = 2; $i < count($headers); $i++) {
    $header = trim($headers[$i] ?? '');
    if ($header) {
        // Clean up header to get program name
        $programName = trim(str_ireplace([' Exams', ' exams', 'course'], '', $header));
        $programColumns[$i] = $programName;
        echo "  Column " . ($i) . ": \"$header\" → Program: \"$programName\"\n";
    }
}

echo "\n\n📦 IMPORT INSTRUCTIONS:\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

echo "🔄 OPTION 1: Run on Render (RECOMMENDED)\n";
echo "─────────────────────────────────────────────────────────────────\n";
echo "1. Go to: https://dashboard.render.com\n";
echo "2. Find your college-database service\n";
echo "3. Click the 'Shell' tab\n";
echo "4. Upload the file to Render using the file manager or:\n";
echo "   scp extracted_exam_report.xlsx [your-service]:/tmp/\n";
echo "\n5. Run the import command with --force flag to save:\n\n";

// Generate commands for each program
$programNames = array_unique(array_values($programColumns));
echo "   For each program identified, run:\n";
foreach ($programNames as $progName) {
    $progId = '1'; // Default, may need adjustment
    echo "   php artisan import:extracted-scores \\\n";
    echo "     /path/to/extracted_exam_report.xlsx \\\n";
    echo "     {$progId} --force\n";
    echo "\n";
}

echo "═════════════════════════════════════════════════════════════════\n";
echo "\n✅ Analysis complete! Use the import instructions above.\n\n";

// Generate a detailed report file
$reportFile = 'c:\Users\sammy\Desktop\exam_import_report.txt';
$report = "EXAM SCORE IMPORT REPORT\n";
$report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
$report .= "File: " . $file . "\n";
$report .= "Students Found: " . count($students) . "\n";
$report .= "Columns: " . implode(', ', array_map(fn($h) => trim($h), $headers)) . "\n\n";
$report .= "STUDENTS AND SCORES:\n";
$report .= str_repeat("─", 80) . "\n";

$allStudents = [];
for ($r = 1; $r < count($data); $r++) {
    $row = $data[$r];
    $studentName = trim($row[1] ?? '');
    if (!$studentName) continue;
    
    $allStudents[] = $studentName;
    $report .= $studentName . ":\n";
    
    for ($c = 2; $c < count($headers); $c++) {
        $programName = $programColumns[$c] ?? "Program $c";
        $score = $row[$c];
        if ($score !== null && $score !== '') {
            $report .= "  • " . $programName . ": " . trim($score) . "\n";
        }
    }
}

file_put_contents($reportFile, $report);
echo "📄 Report saved to: $reportFile\n";

// Save student list
$studentListFile = 'c:\Users\sammy\Desktop\student_names_from_exam_report.txt';
file_put_contents($studentListFile, "STUDENT NAMES FROM EXAM REPORT\n" . 
    str_repeat("═", 50) . "\n" .
    implode("\n", array_map(fn($s) => "• " . $s, $allStudents)));

echo "👥 Student list saved to: $studentListFile\n\n";
