<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Project_Audit_Manager_Tracker.xlsx';
$reader = IOFactory::createReaderForFile($filePath);
$spreadsheet = $reader->load($filePath);

foreach ($spreadsheet->getSheetNames() as $sheetName) {
    echo "\n=======================================================\n";
    echo "SHEET: $sheetName\n";
    echo "=======================================================\n";
    $sheet = $spreadsheet->getSheetByName($sheetName);
    $highestRow = $sheet->getHighestRow();
    $highestCol = $sheet->getHighestColumn();
    echo "Total rows: $highestRow, Highest Col: $highestCol\n";
    
    // Print row 1 to 6 (headers and first 2 data rows)
    for ($r = 1; $r <= min(6, $highestRow); $r++) {
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

    // Also print the last 2 data rows
    if ($highestRow > 6) {
        echo "---\n";
        for ($r = max(7, $highestRow - 2); $r <= $highestRow; $r++) {
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
    }
}
