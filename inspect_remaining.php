<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Project_Audit_Manager_Tracker.xlsx';
$reader = IOFactory::createReaderForFile($filePath);
$spreadsheet = $reader->load($filePath);

foreach (['Pending Work', 'Weekly Lineup', 'Weekly Progress'] as $sName) {
    echo "\n=== $sName ===\n";
    $sheet = $spreadsheet->getSheetByName($sName);
    $row = [];
    for ($c = 'A'; $c <= $sheet->getHighestColumn(); $c++) {
        $row[$c] = (string)$sheet->getCell($c . '4')->getValue();
    }
    echo "Headers (Row 4): " . json_encode($row) . "\n";
    
    // Sample Row 5
    $row5 = [];
    for ($c = 'A'; $c <= $sheet->getHighestColumn(); $c++) {
        $row5[$c] = (string)$sheet->getCell($c . '5')->getValue();
    }
    echo "Row 5: " . json_encode($row5) . "\n";
}
