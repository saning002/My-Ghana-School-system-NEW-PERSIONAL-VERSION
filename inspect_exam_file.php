<?php
require 'vendor/autoload.php';

$reader = new PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$spreadsheet = $reader->load('c:\Users\sammy\Desktop\extracted_exam_report.xlsx');
$sheet = $spreadsheet->getActiveSheet();

echo "Sheet: " . $sheet->getTitle() . PHP_EOL;
echo "Max Row: " . $sheet->getHighestRow() . ", Max Col: " . $sheet->getHighestColumn() . PHP_EOL;
echo "\nHeaders:\n";

$headers = [];
foreach ($sheet->getRowIterator(1, 1) as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $headers[] = $cell->getValue();
    }
}
print_r($headers);

echo "\nFirst 3 data rows:\n";
$count = 0;
foreach ($sheet->getRowIterator(2) as $row) {
    if ($count >= 3) break;
    $rowData = [];
    foreach ($row->getCellIterator() as $cell) {
        $rowData[] = $cell->getValue();
    }
    print_r($rowData);
    $count++;
}
