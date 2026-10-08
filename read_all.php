<?php
require __DIR__ . '/vendor/autoload.php';

$file = 'c:\\Users\\sammy\\Desktop\\extracted_exam_report.xlsx';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$worksheet = $spreadsheet->getActiveSheet();
$data = $worksheet->toArray();

echo json_encode($data, JSON_PRETTY_PRINT);
