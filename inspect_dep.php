<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Project_Audit_Manager_Tracker.xlsx';
$reader = IOFactory::createReaderForFile($filePath);
$spreadsheet = $reader->load($filePath);

$sheet = $spreadsheet->getSheetByName('Deployment & Approval');
$highestRow = $sheet->getHighestRow();
$highestCol = $sheet->getHighestColumn();
echo "Deployment & Approval total rows: $highestRow, col: $highestCol\n";

for ($r = 1; $r <= $highestRow; $r++) {
    $rowVals = [];
    for ($c = 'A'; $c <= $highestCol; $c++) {
        $val = $sheet->getCell($c . $r)->getFormattedValue();
        if ($val !== '' && $val !== null) {
            $rowVals[$c] = substr(str_replace("\n", " ", $val), 0, 50);
        }
    }
    if (!empty($rowVals)) {
        echo "Row $r: " . json_encode($rowVals) . "\n";
    }
}
