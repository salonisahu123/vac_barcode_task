<?php
require __DIR__ . '/vendor/autoload.php';

$tr = PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile('Project_Audit_Manager_Tracker.xlsx')->load('Project_Audit_Manager_Tracker.xlsx');

foreach (['Project Master', 'Module & Functionality', 'Completed Modules', 'Deployment & Approval', 'Weekly Progress'] as $sName) {
    echo "\n=== $sName ===\n";
    $s = $tr->getSheetByName($sName);
    for ($c = 'A'; $c <= $s->getHighestColumn(); $c++) {
        $h = $s->getCell($c . '4')->getValue();
        $v5 = $s->getCell($c . '5')->getValue();
        echo "$c: Header='$h' | Row5='$v5'\n";
    }
}
