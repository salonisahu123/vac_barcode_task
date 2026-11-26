<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

echo "=======================================================\n";
echo "VERIFYING vac-barcode-task.xlsx\n";
echo "=======================================================\n";
$vacReader = IOFactory::createReaderForFile('vac-barcode-task.xlsx');
$vac = $vacReader->load('vac-barcode-task.xlsx');

foreach ($vac->getSheetNames() as $sName) {
    $sheet = $vac->getSheetByName($sName);
    echo "Sheet: '{$sName}' | Total Rows: " . $sheet->getHighestRow() . " | Cols: " . $sheet->getHighestColumn() . "\n";
}

echo "\n=======================================================\n";
echo "VERIFYING Project_Audit_Manager_Tracker.xlsx\n";
echo "=======================================================\n";
$trReader = IOFactory::createReaderForFile('Project_Audit_Manager_Tracker.xlsx');
$tr = $trReader->load('Project_Audit_Manager_Tracker.xlsx');

foreach ($tr->getSheetNames() as $sName) {
    $sheet = $tr->getSheetByName($sName);
    echo "Sheet: '{$sName}' | Total Rows: " . $sheet->getHighestRow() . " | Cols: " . $sheet->getHighestColumn() . "\n";
}

echo "\n--- Dashboard KPI Check ---\n";
$dSheet = $tr->getSheetByName('Dashboard');
echo "Total Projects (A5): " . $dSheet->getCell('A5')->getCalculatedValue() . "\n";
echo "In Progress (C5): " . $dSheet->getCell('C5')->getCalculatedValue() . "\n";
echo "Completed (E5): " . $dSheet->getCell('E5')->getCalculatedValue() . "\n";
echo "Avg Progress (E8): " . $dSheet->getCell('E8')->getFormattedValue() . "\n";

echo "\n--- Project Master Check ---\n";
$pmSheet = $tr->getSheetByName('Project Master');
echo "Row 5 Project: " . $pmSheet->getCell('A5')->getValue() . " | " . $pmSheet->getCell('B5')->getValue() . " | " . $pmSheet->getCell('J5')->getFormattedValue() . " | " . $pmSheet->getCell('H5')->getValue() . "\n";
echo "Row 6 Project: " . $pmSheet->getCell('A6')->getValue() . " | " . $pmSheet->getCell('B6')->getValue() . " | " . $pmSheet->getCell('J6')->getFormattedValue() . " | " . $pmSheet->getCell('H6')->getValue() . "\n";

echo "\n--- Module & Functionality Check ---\n";
$mfSheet = $tr->getSheetByName('Module & Functionality');
echo "Row 5 (Runtime HRMS): " . $mfSheet->getCell('A5')->getValue() . " | " . $mfSheet->getCell('B5')->getValue() . " | " . $mfSheet->getCell('C5')->getValue() . "\n";
echo "Row 144 (Runtime HRMS): " . $mfSheet->getCell('A144')->getValue() . " | " . $mfSheet->getCell('B144')->getValue() . "\n";
echo "Row 145 (VAC Task): " . $mfSheet->getCell('A145')->getValue() . " | " . $mfSheet->getCell('B145')->getValue() . " | " . $mfSheet->getCell('C145')->getValue() . "\n";
echo "Row 279 (VAC Task Last): " . $mfSheet->getCell('A279')->getValue() . " | " . $mfSheet->getCell('B279')->getValue() . " | " . $mfSheet->getCell('C279')->getValue() . "\n";

echo "\n--- Completed Modules Check ---\n";
$cmSheet = $tr->getSheetByName('Completed Modules');
echo "Row 5 (Runtime HRMS): " . $cmSheet->getCell('A5')->getValue() . " | " . $cmSheet->getCell('B5')->getValue() . "\n";
echo "Row 135 (Runtime HRMS): " . $cmSheet->getCell('A135')->getValue() . " | " . $cmSheet->getCell('B135')->getValue() . "\n";
echo "Row 136 (VAC Task): " . $cmSheet->getCell('A136')->getValue() . " | " . $cmSheet->getCell('B136')->getValue() . "\n";
echo "Row 270 (VAC Task Last): " . $cmSheet->getCell('A270')->getValue() . " | " . $cmSheet->getCell('B270')->getValue() . "\n";

echo "\n--- Deployment & Approval Check ---\n";
$daSheet = $tr->getSheetByName('Deployment & Approval');
echo "Row 5 (Runtime HRMS): " . $daSheet->getCell('A5')->getValue() . " | " . $daSheet->getCell('B5')->getValue() . "\n";
echo "Row 21 (Runtime HRMS): " . $daSheet->getCell('A21')->getValue() . " | " . $daSheet->getCell('B21')->getValue() . "\n";
echo "Row 22 (VAC Task): " . $daSheet->getCell('A22')->getValue() . " | " . $daSheet->getCell('B22')->getValue() . "\n";
echo "Row 38 (VAC Task Last): " . $daSheet->getCell('A38')->getValue() . " | " . $daSheet->getCell('B38')->getValue() . "\n";
