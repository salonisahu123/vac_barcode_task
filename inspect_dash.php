<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Project_Audit_Manager_Tracker.xlsx';
$reader = IOFactory::createReaderForFile($filePath);
$spreadsheet = $reader->load($filePath);

$sheet = $spreadsheet->getSheetByName('Dashboard');
for ($r = 1; $r <= $sheet->getHighestRow(); $r++) {
    $rowVals = [];
    for ($c = 'A'; $c <= $sheet->getHighestColumn(); $c++) {
        $cell = $sheet->getCell($c . $r);
        $val = $cell->getValue();
        $calc = $cell->getCalculatedValue();
        if ($val !== null && $val !== '') {
            $rowVals[$c] = ($val !== $calc) ? "FORMULA: $val => $calc" : $val;
        }
    }
    if (!empty($rowVals)) {
        echo "Row $r: " . json_encode($rowVals) . "\n";
    }
}
