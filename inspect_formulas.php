<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Project_Audit_Manager_Tracker.xlsx';
$reader = IOFactory::createReaderForFile($filePath);
$spreadsheet = $reader->load($filePath);

foreach ($spreadsheet->getSheetNames() as $sheetName) {
    $sheet = $spreadsheet->getSheetByName($sheetName);
    for ($r = 1; $r <= $sheet->getHighestRow(); $r++) {
        for ($c = 'A'; $c <= $sheet->getHighestColumn(); $c++) {
            $val = $sheet->getCell($c . $r)->getValue();
            if (is_string($val) && strpos($val, '=') === 0) {
                echo "[$sheetName] $c$r: $val\n";
            }
        }
    }
}
