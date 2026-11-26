<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/Project_Audit_Manager_Tracker.xlsx';
$reader = IOFactory::createReaderForFile($filePath);
$spreadsheet = $reader->load($filePath);

$sheet = $spreadsheet->getSheetByName('Module & Functionality');
$styleA5 = $sheet->getStyle('A5');
echo "Border left style: " . $styleA5->getBorders()->getLeft()->getBorderStyle() . "\n";
echo "Border color: " . $styleA5->getBorders()->getLeft()->getColor()->getRGB() . "\n";
