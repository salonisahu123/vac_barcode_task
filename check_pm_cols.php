<?php
require __DIR__ . '/vendor/autoload.php';

$tr = PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile('Project_Audit_Manager_Tracker.xlsx')->load('Project_Audit_Manager_Tracker.xlsx');
$s = $tr->getSheetByName('Project Master');
for ($c = 'A'; $c <= 'O'; $c++) {
    echo $c . ' (' . $s->getCell($c . '4')->getValue() . ') => ' . $s->getCell($c . '6')->getValue() . "\n";
}
