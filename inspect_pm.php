<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Project_Audit_Manager_Tracker.xlsx';
$reader = IOFactory::createReaderForFile($filePath);
$spreadsheet = $reader->load($filePath);

$sheet = $spreadsheet->getSheetByName('Project Master');
for ($c = 'A'; $c <= 'O'; $c++) {
    $h = $sheet->getCell($c . '4')->getValue();
    $v = $sheet->getCell($c . '5')->getValue();
    $f = $sheet->getCell($c . '5')->getFormattedValue();
    echo "$c: Header='$h' | Value='$v' | Formatted='$f'\n";
}
