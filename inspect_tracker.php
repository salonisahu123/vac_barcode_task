<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Project_Audit_Manager_Tracker.xlsx';
if (!file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit(1);
}

$reader = IOFactory::createReaderForFile($filePath);
$sheetNames = $reader->listWorksheetNames($filePath);
echo "Sheet names:\n";
print_r($sheetNames);

$spreadsheet = $reader->load($filePath);

foreach ($sheetNames as $sheetName) {
    echo "\n==============================\n";
    echo "Sheet: $sheetName\n";
    echo "==============================\n";
    $sheet = $spreadsheet->getSheetByName($sheetName);
    $highestRow = min(30, $sheet->getHighestRow());
    $highestCol = $sheet->getHighestColumn();
    echo "Dimensions: up to row $highestRow, col $highestCol (Total rows: " . $sheet->getHighestRow() . ")\n";
    
    for ($r = 1; $r <= min(15, $highestRow); $r++) {
        $rowVals = [];
        for ($c = 'A'; $c <= $highestCol; $c++) {
            $val = $sheet->getCell($c . $r)->getFormattedValue();
            if ($val !== '' && $val !== null) {
                $rowVals[$c] = substr(str_replace("\n", " ", $val), 0, 40);
            }
        }
        if (!empty($rowVals)) {
            echo "Row $r: " . json_encode($rowVals) . "\n";
        }
    }
}
