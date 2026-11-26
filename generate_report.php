<?php
/**
 * VAC Task - Project Implementation Report Generator
 * Generates: vac-barcode-task.xlsx
 */

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0); // Remove default sheet

// Style Palettes
$navyDark     = '1B365D';
$blueHeader   = '2B6CB0';
$blueSub      = '4299E1';
$lightBlueBg  = 'EBF8FF';
$zebraBg      = 'F7FAFC';
$white        = 'FFFFFF';
$borderGray   = 'CBD5E0';
$greenBg      = 'D4EDDA';
$greenText    = '155724';
$cardBg       = 'EDF2F7';

// Helper function to style a title block
function applyTitleBlock($sheet, $title, $subTitle, $lastCol = 'G') {
    global $navyDark, $white;
    $sheet->mergeCells("A1:{$lastCol}1");
    $sheet->setCellValue('A1', $title);
    $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
        'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $white], 'name' => 'Segoe UI'],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $navyDark]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(36);

    $sheet->mergeCells("A2:{$lastCol}2");
    $sheet->setCellValue('A2', $subTitle);
    $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
        'font' => ['italic' => true, 'size' => 11, 'color' => ['rgb' => 'E2E8F0'], 'name' => 'Segoe UI'],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D3748']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension(2)->setRowHeight(24);
}

// Helper to style table header
function applyTableHeader($sheet, $row, $headers, $lastCol) {
    global $blueHeader, $white;
    $colIndex = 1;
    foreach ($headers as $header) {
        $sheet->setCellValueByColumnAndRow($colIndex, $row, $header);
        $colIndex++;
    }
    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $white], 'name' => 'Segoe UI'],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $blueHeader]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    ]);
    $sheet->getRowDimension($row)->setRowHeight(28);
}

// Helper to style data rows
function styleDataRows($sheet, $startRow, $endRow, $lastCol, $statusCol = null, $centerCols = []) {
    global $borderGray, $zebraBg, $greenBg, $greenText;
    
    for ($r = $startRow; $r <= $endRow; $r++) {
        $sheet->getRowDimension($r)->setRowHeight(24);
        $isEven = ($r % 2 === 0);
        $rowBg = $isEven ? $zebraBg : 'FFFFFF';
        
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
            'font' => ['size' => 10, 'name' => 'Segoe UI'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => $borderGray],
                ],
            ],
        ]);

        foreach ($centerCols as $c) {
            $sheet->getStyle("{$c}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        if ($statusCol) {
            $sheet->getStyle("{$statusCol}{$r}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $greenText], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $greenBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }
    }
}

// Auto fit columns
function autoFitColumns($sheet, $minWidths = []) {
    foreach ($sheet->getColumnIterator() as $col) {
        $colId = $col->getColumnIndex();
        $sheet->getColumnDimension($colId)->setAutoSize(true);
    }
    foreach ($minWidths as $colId => $width) {
        $sheet->getColumnDimension($colId)->setAutoSize(false);
        $sheet->getColumnDimension($colId)->setWidth($width);
    }
}

// ==============================================================================
// 1. SHEET 1: PROJECT OVERVIEW
// ==============================================================================
$s1 = $spreadsheet->createSheet();
$s1->setTitle('Project Overview');
applyTitleBlock($s1, 'PROJECT SUMMARY REPORT - VAC TASK', 'Comprehensive Module-Wise Functional Report & System Architecture', 'E');

$s1->setCellValue('A4', '1. Project Information & Profile');
$s1->getStyle('A4:E4')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => $navyDark]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $lightBlueBg]],
]);

$info = [
    ['Project Name', 'VAC Task'],
    ['System Type', 'Enterprise Task Management, Barcode Scanning, Workflow Tracking & Confidential Complaint System'],
    ['Architecture', 'Laravel 11.x MVC Framework, Blade Template Engine, MySQL Relational Database'],
    ['Key Paradigms', 'Role-Based Access Control (RBAC), JSON-Configured Custom Dashboards, Visual Kanban Board, Anti-Bias Complaint Security, Industrial Barcode Processing'],
    ['Implementation Status', 'Fully Implemented, Tested & Working (100% Operational)'],
    ['Core Modules Implemented', '12 Comprehensive Functional Modules'],
    ['Total Sub-Modules Implemented', '48 Distinct Working Sub-Modules'],
    ['Granular Functionalities', '115+ Detailed Functional Capabilities'],
    ['Frontend Technologies', 'Bootstrap, jQuery, FontAwesome Icons, DataTables, HTML5 Canvas Charts'],
    ['Backend & Libraries', 'PHP 8.x, Maatwebsite Excel / PhpSpreadsheet, DomPDF Generator, Picqer Barcode, Simple QrCode'],
];

$r = 5;
foreach ($info as $item) {
    $s1->setCellValue("A{$r}", $item[0]);
    $s1->mergeCells("B{$r}:E{$r}");
    $s1->setCellValue("B{$r}", $item[1]);
    $s1->getStyle("A{$r}")->getFont()->setBold(true);
    $s1->getStyle("A{$r}:E{$r}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $borderGray]]],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    ]);
    if ($r % 2 === 0) {
        $s1->getStyle("A{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($zebraBg);
    }
    $s1->getRowDimension($r)->setRowHeight(24);
    $r++;
}

$r += 2;
$s1->setCellValue("A{$r}", '2. High-Level Module Summary & Operational Status');
$s1->getStyle("A{$r}:E{$r}")->applyFromArray([
    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => $navyDark]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $lightBlueBg]],
]);
$r++;

$modSummaryHeaders = ['S.No.', 'Core Module Name', 'Key Functionalities Covered', 'Access Level', 'Working Status'];
applyTableHeader($s1, $r, $modSummaryHeaders, 'E');
$tableStart = $r + 1;
$r++;

$modSummary = [
    [1, 'Authentication & User Management', 'Multi-role login, active/inactive verification, password reset, user CRUD, SQL database backup', 'Admin / Global', 'Fully Functional'],
    [2, 'Role-Based Access Control (RBAC)', 'Role management, module-wise permission matrix, fine-grained access assignment, data scoping', 'Super Admin', 'Fully Functional'],
    [3, 'Master Data Management', 'Locations, Departments, Employee multi-dashboard profiles, Groups with member join/remove dates', 'Admin / Permission', 'Fully Functional'],
    [4, 'Task Management & Assignment', 'Subtask stages, multi-mode assignment (Individual/Location/Group), Ajax filtering, pagination, Excel/CSV export', 'Admin / Employee', 'Fully Functional'],
    [5, 'Visual Kanban Pending Dashboard', 'Subtask swimlanes, 6-tier color-coded health indicators, 5-min auto-refresh, latest remarks preview', 'Admin / Employee', 'Fully Functional'],
    [6, 'Custom Dashboard Engine', 'JSON-driven dynamic dashboard builder, widget toggles, employee assignment, dynamic renderer', 'Super Admin', 'Fully Functional'],
    [7, 'Analytics & Main Dashboard', 'Global KPI cards, status breakdown charts, location/department multi-filters, periodic metrics', 'Admin / Employee', 'Fully Functional'],
    [8, 'Employee Complaints (Tickets)', 'Strict confidential anti-bias isolation, multi-stage status, real-time WhatsApp-style chat with attachments', 'Admin / Complainant', 'Fully Functional'],
    [9, 'Barcode Scanning & Production', 'Tilde-delimited CSV import, Work Order drilldown, OP_10 multi-page scanner, live scanning, packing slips', 'Admin / Production', 'Fully Functional'],
    [10, 'Real-Time Notification System', 'Database notifications, smart multi-party dispatcher, topbar bell badge, click-to-read drawer', 'All Users', 'Fully Functional'],
    [11, 'Audit & Activity Logging Ledger', 'Centralized activity logger tracking create, update, delete, remarks, status changes, assignments', 'System / Admins', 'Fully Functional'],
    [12, 'System Layout & Maintenance', 'Responsive sidebar with custom dashboard menus, topbar, route maintenance middleware, error views', 'Global System', 'Fully Functional'],
];

foreach ($modSummary as $row) {
    $s1->setCellValue("A{$r}", $row[0]);
    $s1->setCellValue("B{$r}", $row[1]);
    $s1->setCellValue("C{$r}", $row[2]);
    $s1->setCellValue("D{$r}", $row[3]);
    $s1->setCellValue("E{$r}", $row[4]);
    $r++;
}
$tableEnd = $r - 1;
styleDataRows($s1, $tableStart, $tableEnd, 'E', 'E', ['A', 'D']);
autoFitColumns($s1, ['A' => 8, 'B' => 32, 'C' => 55, 'D' => 20, 'E' => 18]);

// ==============================================================================
// 2. SHEET 2: DETAILED FEATURE MATRIX (Granular Breakdown)
// ==============================================================================
$s2 = $spreadsheet->createSheet();
$s2->setTitle('Full Feature Matrix');
applyTitleBlock($s2, 'VAC TASK - COMPREHENSIVE DETAILED FEATURE MATRIX', 'Exhaustive Breakdown of Every Implemented Module, Sub-Module, Component, and Functionality', 'H');

$headers2 = [
    'S.No.',
    'Module Name',
    'Sub-Module',
    'Sub-Sub-Module / Component',
    'Functionality / Feature Name',
    'Detailed Description of Work Done (Technical & Functional)',
    'User Role / Access',
    'Working Status'
];
applyTableHeader($s2, 4, $headers2, 'H');

$matrixData = [
    // 1. AUTHENTICATION & SECURITY
    [1, 'Authentication & Security', 'User Authentication', 'Login Mechanism', 'Email & Password Verification', 'Authenticates users against credentials, validates email format and password, regenerates session tokens to prevent fixation attacks.', 'All Users', 'Completed'],
    [2, 'Authentication & Security', 'User Authentication', 'Account Status Check', 'Active / Inactive Account Gatekeeper', 'Checks is_active flag upon login attempt; immediately rejects and logs out inactive users with informative error notification.', 'All Users', 'Completed'],
    [3, 'Authentication & Security', 'User Authentication', 'Remember Me Feature', 'Persistent Login Session', 'Stores secure encrypted remember token in cookie for convenient persistence across browser sessions.', 'All Users', 'Completed'],
    [4, 'Authentication & Security', 'User Authentication', 'Role-Based Redirection', 'Dynamic Post-Login Redirection', 'Redirects Super Admin to main dashboard/pending tasks; redirects Employees directly to their assigned Custom Dashboard or Task board.', 'All Users', 'Completed'],
    [5, 'Authentication & Security', 'User Authentication', 'Logout Security', 'Secure Session Destruction', 'Clears authentication session, flushes CSRF token, and securely redirects user back to the login portal.', 'All Users', 'Completed'],
    [6, 'Authentication & Security', 'Password Recovery', 'Forgot Password', 'Email Identity Verification', 'Validates requested email in users database and initiates secure password reset journey.', 'All Users', 'Completed'],
    [7, 'Authentication & Security', 'Password Recovery', 'Password Reset Form', 'Dedicated Reset Interface', 'Renders password update view with email binding and CSRF token protection.', 'All Users', 'Completed'],
    [8, 'Authentication & Security', 'Password Recovery', 'Password Hashing & Sync', 'Secure Bcrypt Update & Masked Storage', 'Hashes new password via Bcrypt, updates show_password field for administrative support, and provides immediate success feedback.', 'All Users', 'Completed'],
    [9, 'Authentication & Security', 'User Administration', 'User Directory', 'System User Listing', 'Paginated table displaying registered users, their roles, mobile, addresses, excluding Super Admin from unauthorized lists.', 'Admin Only', 'Completed'],
    [10, 'Authentication & Security', 'User Administration', 'User Registration', 'Add New System User', 'Validates and creates new user with Name, Mobile, Email, Address, User Type, Role ID, and confirmed Password.', 'Admin Only', 'Completed'],
    [11, 'Authentication & Security', 'User Administration', 'User Modification', 'Edit User Profile', 'Allows updating user information, contact details, assigned role, and user category.', 'Admin Only', 'Completed'],
    [12, 'Authentication & Security', 'User Administration', 'User Deletion', 'Delete System User', 'Deletes user record safely with foreign key cascade handling and validation.', 'Admin Only', 'Completed'],
    [13, 'Authentication & Security', 'Database Backup', 'SQL Backup Downloader', 'Direct Database Dump Stream', 'Iterates all database tables, constructs CREATE TABLE and INSERT statements into valid SQL script, and streams as instant .sql download.', 'Admin Only', 'Completed'],

    // 2. RBAC & PERMISSIONS
    [14, 'RBAC & Access Control', 'Role Management', 'Role Directory', 'Role Listing Interface', 'Displays all created roles, user associations, and role slugs while protecting super-admin role from tampering.', 'Super Admin', 'Completed'],
    [15, 'RBAC & Access Control', 'Role Management', 'Create Role', 'Add New System Role', 'Creates custom role with unique name, auto-generated slug, and creator tracking.', 'Super Admin', 'Completed'],
    [16, 'RBAC & Access Control', 'Role Management', 'Edit Role', 'Role Update & Slug Synchronization', 'Modifies role name, re-syncs slug, and checks for naming collisions.', 'Super Admin', 'Completed'],
    [17, 'RBAC & Access Control', 'Role Management', 'Delete Role', 'Role Deletion & Permission Purge', 'Deletes role and automatically cascades removal of all associated role_permissions mappings.', 'Super Admin', 'Completed'],
    [18, 'RBAC & Access Control', 'Permission Registry', 'Permission Seeding', 'Module-Wise Permission Seeder', 'PermissionSeeder creates standardized permissions across 14 modules with actions: view, view_all, view_own, add, edit, delete, etc.', 'System / Admin', 'Completed'],
    [19, 'RBAC & Access Control', 'Permission Registry', 'Permission Slugs', 'Granular Action Slugs', 'Generates standard slugs such as task_view_all, task_change_status, ticket_change_status, department_add, etc.', 'System / Admin', 'Completed'],
    [20, 'RBAC & Access Control', 'Role-Permission Matrix', 'Assignment Interface', 'Module-Wise Permission Checkbox Grid', 'Visual card-based matrix organizing permissions by functional modules with clean multi-select checkboxes.', 'Super Admin', 'Completed'],
    [21, 'RBAC & Access Control', 'Role-Permission Matrix', 'Assign Permissions', 'Bulk Role Permission Store', 'Calculates diff between existing and newly selected permissions and performs optimized bulk database insert.', 'Super Admin', 'Completed'],
    [22, 'RBAC & Access Control', 'Role-Permission Matrix', 'View Assigned', 'Role Permission Inspection View', 'Read-only view showing all permissions assigned to a selected role with green indicator badges.', 'Super Admin', 'Completed'],
    [23, 'RBAC & Access Control', 'Role-Permission Matrix', 'Update Permissions', 'Incremental Permission Sync', 'Synchronizes updated checkboxes: inserts newly checked permissions and deletes unchecked items without disturbing unchanged rows.', 'Super Admin', 'Completed'],
    [24, 'RBAC & Access Control', 'Role-Permission Matrix', 'Revoke All', 'Remove All Role Permissions', 'Revokes all granted permissions from a specific role in a single secure operation with audit logging.', 'Super Admin', 'Completed'],
    [25, 'RBAC & Access Control', 'Security Engine', 'Permission Helpers', 'hasPermission() & hasAnyPermission()', 'Global helper functions inspecting logged-in user role, checking role_permissions table with automated Super Admin bypass.', 'Global System', 'Completed'],
    [26, 'RBAC & Access Control', 'Security Engine', 'Data Scoping Engine', 'view_all vs view_own Isolation', 'Restricts query scopes so users with view_own only see tasks/tickets created by them or assigned directly to them.', 'Global System', 'Completed'],

    // 3. MASTER DATA MANAGEMENT
    [27, 'Master Data Management', 'Location Module', 'Location Directory', 'Location Listing', 'Lists all operating locations with creator user info and management action buttons.', 'Admin / Permitted', 'Completed'],
    [28, 'Master Data Management', 'Location Module', 'Add Location', 'Create Location Entity', 'Validates unique location name, stores location in database, and logs activity record.', 'Admin / Permitted', 'Completed'],
    [29, 'Master Data Management', 'Location Module', 'Edit Location', 'Update Location Entity', 'Updates location details with duplicate name prevention.', 'Admin / Permitted', 'Completed'],
    [30, 'Master Data Management', 'Location Module', 'Delete Location', 'Delete Location Entity', 'Removes location record safely with activity audit trail.', 'Admin / Permitted', 'Completed'],
    [31, 'Master Data Management', 'Department Module', 'Department Directory', 'Department Listing with Location', 'Displays departments alongside their associated physical location and creator metadata.', 'Admin / Permitted', 'Completed'],
    [32, 'Master Data Management', 'Department Module', 'Add Department', 'Create Department Entity', 'Validates unique dept name and binds it to selected location ID.', 'Admin / Permitted', 'Completed'],
    [33, 'Master Data Management', 'Department Module', 'Edit Department', 'Update Department Entity', 'Updates department name and rebinds location association.', 'Admin / Permitted', 'Completed'],
    [34, 'Master Data Management', 'Department Module', 'Delete Department', 'Delete Department Entity', 'Deletes department entity with activity logging.', 'Admin / Permitted', 'Completed'],
    [35, 'Master Data Management', 'Department Module', 'Dynamic Cascading', 'Location-to-Department Ajax Fetch', 'Dynamic API route fetching departments based on selected location ID for cascading dropdowns.', 'Admin / Employee', 'Completed'],
    [36, 'Master Data Management', 'Employee Module', 'Employee Directory', 'Employee Master Table', 'Comprehensive table with user photo, name, email, phone, location, department, role, status toggle, and action buttons.', 'Admin / Permitted', 'Completed'],
    [37, 'Master Data Management', 'Employee Module', 'Create Employee', 'Dual User & Profile Provisioning', 'Simultaneously provisions `users` login record and `employees` profile with photo upload, role, gender, joining date.', 'Admin / Permitted', 'Completed'],
    [38, 'Master Data Management', 'Employee Module', 'Dashboard Assignment', 'Multi-Dashboard Mapping', 'Assigns one or more Custom Dashboards to employee during creation/editing stored in `employee_dashboards`.', 'Admin / Permitted', 'Completed'],
    [39, 'Master Data Management', 'Employee Module', 'Edit Employee', 'Profile & Credentials Update', 'Updates profile data, department, location, role, and replaces profile photo with disk cleanup.', 'Admin / Permitted', 'Completed'],
    [40, 'Master Data Management', 'Employee Module', 'Status Toggle', 'Real-Time Active/Inactive Switch', 'Ajax toggle updating is_active flag in database instantly without reloading page.', 'Admin / Permitted', 'Completed'],
    [41, 'Master Data Management', 'Employee Module', 'Show Profile', 'Employee Profile View', 'Detailed modal/page showing employee contact details, role, department, assigned dashboards, and account status.', 'Admin / Permitted', 'Completed'],
    [42, 'Master Data Management', 'Employee Module', 'Delete Employee', 'Remove Employee & User Account', 'Removes employee profile and linked user authentication account.', 'Admin / Permitted', 'Completed'],
    [43, 'Master Data Management', 'Group / Team Module', 'Group Directory', 'Team Listing Table', 'Lists groups with location, department, member count, and action controls.', 'Admin / Permitted', 'Completed'],
    [44, 'Master Data Management', 'Group / Team Module', 'Create Group', 'New Group with Dynamic Members', 'Creates team, binds location/department, and multi-selects employees with initial joined_at timestamp.', 'Admin / Permitted', 'Completed'],
    [45, 'Master Data Management', 'Group / Team Module', 'Membership Tracking', 'Audit-Safe Joined & Removed Dates', 'Tracks joined_at and removed_at dates in `group_employees` ensuring historical task queries reflect accurate past team members.', 'Admin / Permitted', 'Completed'],
    [46, 'Master Data Management', 'Group / Team Module', 'Edit Group', 'Member Modification & Soft Removal', 'Adds new members with current date, marks removed members with removed_at without corrupting past tasks.', 'Admin / Permitted', 'Completed'],
    [47, 'Master Data Management', 'Group / Team Module', 'Dynamic Employee Fetch', 'Cascading Location/Dept to Employees', 'Dynamic Ajax route returning active eligible employees based on selected location and department.', 'Admin / Permitted', 'Completed'],
    [48, 'Master Data Management', 'Group / Team Module', 'Delete Group', 'Delete Team & Member Links', 'Removes group entity and member associations with full activity logging.', 'Admin / Permitted', 'Completed'],

    // 4. TASK MANAGEMENT & WORKFLOW
    [49, 'Task Management', 'Subtask Stages', 'Subtask Directory', 'Subtask Master Listing', 'Lists workflow stages/subtasks (e.g. Cutting, Sewing, Quality, Finishing, Packing) with user tracking.', 'Admin Only', 'Completed'],
    [50, 'Task Management', 'Subtask Stages', 'Create Subtask', 'Add New Workflow Stage', 'Creates new subtask classification for grouping tasks on boards.', 'Admin Only', 'Completed'],
    [51, 'Task Management', 'Subtask Stages', 'Edit Subtask', 'Update Workflow Stage', 'Modifies subtask category title and logs activity change.', 'Admin Only', 'Completed'],
    [52, 'Task Management', 'Subtask Stages', 'Delete Subtask', 'Delete Workflow Stage', 'Deletes subtask classification with integrity verification.', 'Admin Only', 'Completed'],
    [53, 'Task Management', 'Task Creation', 'Basic Task Info', 'Task Title, Description & Dates', 'Captures title, description, subtask category, initial status, and validates To Date >= From Date.', 'Admin / Permitted', 'Completed'],
    [54, 'Task Management', 'Task Creation', 'Individual Assignment', 'Direct Multi-Employee Assignment', 'Assigns task to one or multiple specific employees across the organization.', 'Admin / Permitted', 'Completed'],
    [55, 'Task Management', 'Task Creation', 'Location Assignment', 'Location-Wise Employee Multi-Select', 'Filters employees by selected location and assigns task to selected branch staff.', 'Admin / Permitted', 'Completed'],
    [56, 'Task Management', 'Task Creation', 'Group / Team Assignment', 'Historical Date-Aware Team Assignment', 'Assigns task to entire group; inspects joined_at and removed_at dates to ensure only active members during task timeframe are attached.', 'Admin / Permitted', 'Completed'],
    [57, 'Task Management', 'Task Creation', 'File Attachments', 'Document & Image Upload', 'Accepts task reference files (PDF, images, documents up to 2MB) and stores in `uploads/tasks/`.', 'Admin / Permitted', 'Completed'],
    [58, 'Task Management', 'Task Listing & Search', 'Server-Side Pagination', 'Configurable Table Pagination', 'Provides server-side pagination with selectable page limits: 10, 25, 50, 100 entries.', 'Admin / Employee', 'Completed'],
    [59, 'Task Management', 'Task Listing & Search', 'Ajax Quick Filtering', 'Multi-Criteria Ajax Filter Bar', 'Filter tasks dynamically by Location, Group, Employee, and Status (Pending, In Progress, Completed, All, Pending & In Progress).', 'Admin / Employee', 'Completed'],
    [60, 'Task Management', 'Task Listing & Search', 'Global Search', 'Comprehensive Multi-Column Search', 'Real-time search querying Task Title, Subtask name, Location, Group, Assigned Employee names, Creator, and Dates.', 'Admin / Employee', 'Completed'],
    [61, 'Task Management', 'Task Listing & Search', 'Permission Isolation', 'Scoped Task Visibility', 'Users with task_view_all see all organization tasks; users with task_view_own see only tasks they created or are assigned to.', 'Admin / Employee', 'Completed'],
    [62, 'Task Management', 'Task Detail View', 'Task Overview Card', 'Comprehensive Task Summary', 'Displays Title, Status pill, Subtask badge, Timeline dates, Creator, Location/Group, Description, and File download link.', 'Admin / Employee', 'Completed'],
    [63, 'Task Management', 'Task Detail View', 'Assigned Members Grid', 'Visual Assignee Avatars', 'Renders profile picture avatars, employee names, and roles of all assigned staff.', 'Admin / Employee', 'Completed'],
    [64, 'Task Management', 'Task Detail View', 'Live Status Update', 'Inline Status Change Dropdown', 'Allows authorized users to switch status between Pending, In Progress, Completed with immediate Ajax feedback.', 'Admin / Permitted', 'Completed'],
    [65, 'Task Management', 'Task Detail View', 'Live Subtask Transition', 'Workflow Stage Switcher Dropdown', 'Allows switching task subtask/stage directly from task show page with automatic activity logging.', 'Admin / Permitted', 'Completed'],
    [66, 'Task Management', 'Task Detail View', 'Add Progress Remark', 'Remark Posting Modal & Form', 'Allows team members to post progress remarks with author attribution, activity logging, and notification alerts.', 'Admin / Employee', 'Completed'],
    [67, 'Task Management', 'Task Detail View', 'Activity Feed', 'Chronological Audit Timeline', 'Displays historical feed of remarks, status changes, and subtask transitions with user photo and relative timestamp.', 'Admin / Employee', 'Completed'],
    [68, 'Task Management', 'Task Modification', 'Task Edit Form', 'Complete Task Update', 'Modifies title, dates, mode, subtask, file attachment, and recalculates employee assignments.', 'Admin / Permitted', 'Completed'],
    [69, 'Task Management', 'Task Modification', 'Change Detection', 'Automatic Audit Diff Logging', 'Compares old and new values, automatically logging: "Subtask changed from A to B", "Status changed from A to B", "Task type changed".', 'System Auto', 'Completed'],
    [70, 'Task Management', 'Task Deletion', 'Transactional Delete', 'Clean Database Deletion', 'Wraps deletion in DB transaction: deletes task_users, dashboard links, file from disk, task record, and logs activity.', 'Admin / Permitted', 'Completed'],
    [71, 'Task Management', 'Data Export', 'Excel Export', 'Filtered Tasks Export to .xlsx', 'Exports currently filtered tasks directly to Excel spreadsheet using Maatwebsite Excel package.', 'Admin / Permitted', 'Completed'],
    [72, 'Task Management', 'Data Export', 'CSV Export', 'Filtered Tasks Export to .csv', 'Exports currently filtered tasks to CSV file with active search and filter preservation.', 'Admin / Permitted', 'Completed'],

    // 5. VISUAL KANBAN PENDING DASHBOARD
    [73, 'Kanban Pending Dashboard', 'Board Architecture', 'Subtask Column Swimlanes', 'Dynamic Workflow Swimlanes', 'Dynamic Kanban board where each subtask category forms a distinct workflow column.', 'All Users', 'Completed'],
    [74, 'Kanban Pending Dashboard', 'Board Architecture', 'Column Task Badges', 'Volume Counter Badges', 'Displays dynamic badge count of pending & in-progress tasks in each subtask header.', 'All Users', 'Completed'],
    [75, 'Kanban Pending Dashboard', 'Urgency Indicators', 'Red Indicator', 'Without Starting - Already Overdue', 'Highlights task in bold Red when status is Pending and due date has already passed.', 'All Users', 'Completed'],
    [76, 'Kanban Pending Dashboard', 'Urgency Indicators', 'Purple Indicator', 'In Progress - Already Overdue', 'Highlights task in Purple when status is In Progress but due date has expired.', 'All Users', 'Completed'],
    [77, 'Kanban Pending Dashboard', 'Urgency Indicators', 'Orange Indicator', 'Without Starting - Due Today/Tomorrow', 'Highlights task in Orange when status is Pending and due date is today or tomorrow.', 'All Users', 'Completed'],
    [78, 'Kanban Pending Dashboard', 'Urgency Indicators', 'Yellow Indicator', 'In Progress - Due Today/Tomorrow', 'Highlights task in Yellow when status is In Progress and due date is today or tomorrow.', 'All Users', 'Completed'],
    [79, 'Kanban Pending Dashboard', 'Urgency Indicators', 'Green Indicator', 'In Progress - Active On Track', 'Highlights task in Green when actively In Progress and well within schedule.', 'All Users', 'Completed'],
    [80, 'Kanban Pending Dashboard', 'Urgency Indicators', 'Blue Pulsing Animation', 'Recently Added Task Indicator', 'Applies glowing blue border and CSS keyframe blinking animation for tasks created in the last 24 hours.', 'All Users', 'Completed'],
    [81, 'Kanban Pending Dashboard', 'Card Micro-Content', 'Latest Remark Bubble', 'Direct Card Remark Snippet', 'Displays recent remark text, author badge, and humanized time tag directly inside the Kanban card.', 'All Users', 'Completed'],
    [82, 'Kanban Pending Dashboard', 'Card Micro-Content', 'Direct Link to Action', 'One-Click Task Navigation', 'Task title acts as direct link navigating straight to task details & remark form.', 'All Users', 'Completed'],
    [83, 'Kanban Pending Dashboard', 'Board Controls', 'Location / Dept Filter', 'Dynamic Header Filter Form', 'Toggle between Location and Department views with immediate board regeneration.', 'Admin Only', 'Completed'],
    [84, 'Kanban Pending Dashboard', 'Board Controls', 'Auto-Refresh Engine', '5-Minute Countdown Timer', 'Live ticking clock counting down from 05:00; triggers automatic page reload at 00:00 for live shop-floor monitoring.', 'All Users', 'Completed'],
    [85, 'Kanban Pending Dashboard', 'Board Controls', 'Manual Refresh', 'Instant Refresh Now Button', 'One-click refresh button allowing immediate on-demand board update.', 'All Users', 'Completed'],

    // 6. CUSTOM DASHBOARD BUILDER
    [86, 'Custom Dashboard Builder', 'Dashboard Creator', 'Dashboard Registration', 'Create Custom Dashboard Entity', 'Creates custom dashboard record with unique name and initializes JSON configuration structure.', 'Super Admin', 'Completed'],
    [87, 'Custom Dashboard Builder', 'Component Toggles', 'Task Module Configuration', 'Task Chart, Counter & Subtasks Toggle', 'Configure task analytics chart, redirect counter card, and individual subtask checkbox inclusion.', 'Super Admin', 'Completed'],
    [88, 'Custom Dashboard Builder', 'Component Toggles', 'Ticket Module Configuration', 'Complaint Chart, Counter & Table Toggle', 'Configures complaint chart, redirect count card, and summary table visibility.', 'Super Admin', 'Completed'],
    [89, 'Custom Dashboard Builder', 'Component Toggles', 'Master Modules Configuration', 'Master Redirect Cards & Tables Toggle', 'Individual toggles for Location, Department, Employee, and Group summary cards and tables.', 'Super Admin', 'Completed'],
    [90, 'Custom Dashboard Builder', 'Access Mapping', 'Employee Dashboard Linking', 'Assign Custom Dashboards to Employees', 'Maps dashboard IDs to employee IDs via `employee_dashboards` table for role-tailored view access.', 'Super Admin', 'Completed'],
    [91, 'Custom Dashboard Builder', 'Dynamic Renderer', 'Dashboard Dynamic Render Engine', 'JSON Parser & Dynamic View Generator', 'Parses `view_json`, checks employee authorization, and dynamically renders only enabled widgets and subtask cards.', 'Admin / Employee', 'Completed'],
    [92, 'Custom Dashboard Builder', 'Dynamic Renderer', 'Multi-Dashboard Aggregation', 'Consolidated Subtask Card View', 'Merges enabled subtasks across all assigned custom dashboards for an employee without duplicate cards.', 'Admin / Employee', 'Completed'],
    [93, 'Custom Dashboard Builder', 'Navigation Integration', 'Sidebar Submenu Generation', 'Dynamic Sidebar Dashboard Menu', 'Populates sidebar dashboard submenu with all custom dashboards accessible to the authenticated user.', 'All Users', 'Completed'],

    // 7. MAIN ANALYTICS DASHBOARD
    [94, 'Analytics & Main Dashboard', 'KPI Metric Cards', 'Global Counter Cards', 'Total Tasks, Tickets & Employees Counters', 'Live counter cards showing total users, tasks, tickets, barcodes, locations, departments, roles, and groups.', 'Admin / Permitted', 'Completed'],
    [95, 'Analytics & Main Dashboard', 'Analytics Filters', 'Multi-Dimensional Data Filters', 'Location, Department & Time Filters', 'Interactive filters recalculating metrics by location, department, or date range (day, month, year).', 'Admin / Permitted', 'Completed'],
    [96, 'Analytics & Main Dashboard', 'Visual Metrics', 'Status Breakdown Analytics', 'Task & Ticket Status Breakdowns', 'Computes real-time counts for Pending, In Progress, Completed, Hold, Close Request, Closed, Reopened.', 'Admin / Permitted', 'Completed'],

    // 8. EMPLOYEE COMPLAINTS & TICKETS
    [97, 'Employee Complaints Module', 'Confidentiality Engine', 'Accused Blind Rule', 'Strict Anti-Bias Isolation Scope', 'Strict database query rule ensuring an employee accused in a complaint can NEVER view or access that complaint.', 'All Users', 'Completed'],
    [98, 'Employee Complaints Module', 'Complaint Filing', 'File New Complaint', 'Complaint Registration Interface', 'Captures title, subject, priority (Low/Med/High), department, location, accused employee selection, and description.', 'Admin / Employee', 'Completed'],
    [99, 'Employee Complaints Module', 'Complaint Filing', 'Evidence Attachment', 'Multi-Format Evidence File Upload', 'Accepts photos, PDFs, documents, spreadsheets, ZIP archives up to 15MB stored in `uploads/tickets/`.', 'Admin / Employee', 'Completed'],
    [100, 'Employee Complaints Module', 'Workflow & Lifecycle', 'Status Progression', 'Multi-Stage Status Lifecycle', 'Lifecycle statuses: Pending -> Assigned -> In Progress -> Hold -> Close Request -> Closed / Reopen.', 'Admin / Complainant', 'Completed'],
    [101, 'Employee Complaints Module', 'Workflow & Lifecycle', 'Close Request & Approval', 'Two-Party Closure Verification', 'Handler requests closure (`close_request`); Complainant verifies resolution and confirms `closed` or triggers `reopen`.', 'Admin / Complainant', 'Completed'],
    [102, 'Employee Complaints Module', 'Interactive Chat', 'WhatsApp-Style Chat', 'Real-Time Complaint Discussion', 'Interactive message thread between complainant and admin/investigators without exposing accused employee.', 'Admin / Complainant', 'Completed'],
    [103, 'Employee Complaints Module', 'Interactive Chat', 'File Sharing in Chat', 'Media & Document Upload in Messages', 'Uploads and previews images, documents, and spreadsheets with formatted file sizes (KB/MB) and download links.', 'Admin / Complainant', 'Completed'],
    [104, 'Employee Complaints Module', 'Interactive Chat', 'Chat Bubble UI', 'Sender Alignment & Date Dividers', 'Formatted bubbles (Is_Me alignment, sender avatar, department badge, humanized time, and Today/Yesterday dividers).', 'Admin / Complainant', 'Completed'],
    [105, 'Employee Complaints Module', 'Assignment Engine', 'Investigation Assignment', 'Assign Accused Employee or Group', 'Assigns complaint to specific employee or entire team with activity audit tracking.', 'Admin Only', 'Completed'],

    // 9. BARCODE SCANNING & PRODUCTION
    [106, 'Barcode & Production', 'Barcode Import', 'Tilde CSV Parser', 'Custom Delimited CSV/TXT Import', 'Uploads and parses files separated by tilde (`~`) delimiter containing WO, Part No, SO, Line No, Operation Code, Quantity, and Page Count.', 'Admin / Production', 'Completed'],
    [107, 'Barcode & Production', 'Barcode Import', 'Duplicate Handling', 'Automatic Page & Quantity Sync', 'Detects duplicate rows, automatically updates page count (`no_of_pages`), and reports inserted vs skipped count.', 'Admin / Production', 'Completed'],
    [108, 'Barcode & Production', 'Work Order Reports', 'Work Order Directory', 'Work Order Master Summary List', 'Aggregated summary table grouped by Work Order No with latest timestamp and barcode count.', 'Admin / Production', 'Completed'],
    [109, 'Barcode & Production', 'Work Order Reports', 'Barcode Details Drilldown', 'Detailed Parts Breakdown by WO', 'Displays full details of all parts, sales orders, line numbers, operations, and quantities for selected Work Order.', 'Admin / Production', 'Completed'],
    [110, 'Barcode & Production', 'Work Order Reports', 'Deletion Protection', 'Active Production Deletion Safeguard', 'Inspects `scan_brcodes` before deleting; prevents deletion of any work order tied to existing production records.', 'Admin / Production', 'Completed'],
    [111, 'Barcode & Production', 'Work Order Reports', 'Bulk Deletion Safety', 'Bulk Delete with Safety Validation', 'Deletes selected safe records while preserving linked records and displaying specific non-deletable item report.', 'Admin / Production', 'Completed'],
    [112, 'Barcode & Production', 'Live Production Scanning', 'Hardware Scanner Reader', 'Auto-Focus Barcode Input Handler', 'Auto-focused input listening for scanner hardware; explodes scanned string `WO~Part~PO~SO~Line` in real time.', 'Production Team', 'Completed'],
    [113, 'Barcode & Production', 'Live Production Scanning', 'Validation & Quota Check', 'Real-Time Work Order & Quota Check', 'Validates scanned WO against selected WO; ensures scanned quantity does not exceed available quota in master barcode details.', 'Production Team', 'Completed'],
    [114, 'Barcode & Production', 'Live Production Scanning', 'OP_10 Multi-Page Support', 'Multi-Page Operation Handling', 'Supports multi-page documentation for OP_10 operations with dedicated page number selector and tracking.', 'Production Team', 'Completed'],
    [115, 'Barcode & Production', 'Live Production Scanning', 'Live Quantity Increment', 'Automatic Scan & History Logging', 'Increments quantity in `scan_brcodes` and logs individual user scan records in `histories` table.', 'Production Team', 'Completed'],
    [116, 'Barcode & Production', 'Live Production Scanning', 'Manual Entry Fallback', 'Manual Scan Entry Modal', 'Allows authorized manual entry of barcode data when scanner hardware is unavailable or barcode is damaged.', 'Production Team', 'Completed'],
    [117, 'Barcode & Production', 'Live Production Scanning', 'Inline Quantity Update', 'Direct Quantity Adjustment', 'Allows updating scanned quantity directly with validation against master quantity and audit logging.', 'Production Team', 'Completed'],
    [118, 'Barcode & Production', 'Packing Slip Module', 'Packing Barcode Scanning', 'Dedicated Packing Station Scanning', 'Barcode scanning interface specifically designed for shipping and packing stations.', 'Packing Team', 'Completed'],
    [119, 'Barcode & Production', 'Packing Slip Module', 'Packing File Groups', 'File-Based Packing Record Index', 'Organizes packing records by batch/file name (`packing-slip-index`) with latest update timestamps.', 'Packing Team', 'Completed'],
    [120, 'Barcode & Production', 'Packing Slip Module', 'Packing Quantity & History', 'Live Packing History Logging', 'Maintains live packing quantity in `packing_slips` and records user actions in `packing_histories`.', 'Packing Team', 'Completed'],
    [121, 'Barcode & Production', 'Export Engine', 'Scanned Records Excel', 'Export Scanned Records to .xlsx', 'Exports scanned production records filtered by Work Order, Operation Code, and Page number to Excel.', 'Admin / Production', 'Completed'],
    [122, 'Barcode & Production', 'Export Engine', 'Production Records Excel', 'Export Production Records to .xlsx', 'Exports aggregated production history filtered by Work Order and Operation to Excel.', 'Admin / Production', 'Completed'],
    [123, 'Barcode & Production', 'Export Engine', 'Packing Records Excel', 'Export Packing Records to .xlsx', 'Exports packing slip records filtered by batch file to Excel spreadsheet.', 'Admin / Packing', 'Completed'],
    [124, 'Barcode & Production', 'Export Engine', 'Production Records PDF', 'DomPDF Production Report with Separators', 'Generates professional PDF with dynamic blank row separators between different Sales Orders (SO).', 'Admin / Production', 'Completed'],
    [125, 'Barcode & Production', 'Export Engine', 'Packing Records PDF', 'DomPDF Packing Slip PDF Report', 'Generates formatted packing slip PDF report sorted by Sales Order and Part Number.', 'Admin / Packing', 'Completed'],

    // 10. NOTIFICATIONS, AUDIT & LAYOUT
    [126, 'Notifications & Alerts', 'Notification Dispatcher', 'Task Notification Engine', 'Multi-Party Smart Notification Dispatcher', 'Dispatches `TaskUpdatedNotification` to Task Creator, all Assigned Employees, and Admins while excluding trigger user.', 'System Auto', 'Completed'],
    [127, 'Notifications & Alerts', 'Topbar Bell & Dropdown', 'Unread Counter Badge', 'Live Red Unread Badge Counter', 'Displays live count of unread notifications on topbar bell icon; hidden when zero unread.', 'All Users', 'Completed'],
    [128, 'Notifications & Alerts', 'Topbar Bell & Dropdown', 'Two-Tier Notification List', 'Unread & Seen Notification Sections', 'Organizes notifications into separate Unread and Seen sections with message snippet and relative time.', 'All Users', 'Completed'],
    [129, 'Notifications & Alerts', 'Topbar Bell & Dropdown', 'Click-to-Read Action', 'Instant Ajax Mark-as-Read Action', 'Clicking an unread notification immediately calls read API, moves it to Seen section, and updates counter.', 'All Users', 'Completed'],
    [130, 'System Audit & Ledger', 'Activity Ledger', 'Centralized Audit Table', 'Activities Audit Trail (`activities`)', 'Centralized log recording user_id, table_name, table_id, operation type, and descriptive activity text.', 'System Auto', 'Completed'],
    [131, 'System Infrastructure', 'Layout & Navigation', 'Responsive Navigation', 'Dynamic Sidebar & Topbar Header', 'Sidebar with toggleable accordion submenus, dynamic custom dashboard links, and user profile drawer.', 'All Users', 'Completed'],
    [132, 'System Infrastructure', 'Route Maintenance', 'Maintenance Middleware', 'RouteUnderMaintenance Middleware', 'Middleware providing graceful maintenance toggling and rendering dedicated maintenance view when enabled.', 'Global System', 'Completed'],
    [133, 'System Infrastructure', 'Error Handling', 'HTTP Status Pages', 'Custom Error Screens (403, 404, 419, 500)', 'Custom branded error pages for 403 Forbidden, 404 Not Found, 419 CSRF Token Expired, and 500 Server Error.', 'Global System', 'Completed'],
];

$r = 5;
foreach ($matrixData as $row) {
    $s2->setCellValue("A{$r}", $row[0]);
    $s2->setCellValue("B{$r}", $row[1]);
    $s2->setCellValue("C{$r}", $row[2]);
    $s2->setCellValue("D{$r}", $row[3]);
    $s2->setCellValue("E{$r}", $row[4]);
    $s2->setCellValue("F{$r}", $row[5]);
    $s2->setCellValue("G{$r}", $row[6]);
    $s2->setCellValue("H{$r}", $row[7]);
    $r++;
}
$tableEnd2 = $r - 1;
styleDataRows($s2, 5, $tableEnd2, 'H', 'H', ['A', 'G']);
$s2->getStyle("F5:F{$tableEnd2}")->getAlignment()->setWrapText(true);
autoFitColumns($s2, ['A' => 8, 'B' => 24, 'C' => 24, 'D' => 28, 'E' => 30, 'F' => 60, 'G' => 20, 'H' => 16]);

// ==============================================================================
// 3. SHEET 3: TASK & KANBAN MODULE (Deep Dive)
// ==============================================================================
$s3 = $spreadsheet->createSheet();
$s3->setTitle('Task & Kanban Module');
applyTitleBlock($s3, 'TASK MANAGEMENT & VISUAL KANBAN DASHBOARD MODULE', 'In-Depth Functional Specifications, Health Indicators & Workflow Architecture', 'G');

applyTableHeader($s3, 4, ['S.No.', 'Component / Sub-Module', 'Feature Name', 'Detailed Functional & Technical Capabilities', 'Business Rules & Logic', 'Access Scope', 'Status'], 'G');

$taskDetails = [
    [1, 'Subtask Categories', 'Workflow Stages Master', 'Creates, edits, lists, and deletes subtask categories representing manufacturing/process stages (e.g. Cutting, Sewing, QC, Finishing, Packing).', 'Acts as the column swimlanes for visual boards; tracks creator user_id and logs subtask activity.', 'Super Admin', 'Completed'],
    [2, 'Task Creation', 'Multi-Mode Assignment Engine', 'Supports 3 distinct assignment modes: Individual (direct employee multi-select), Location-Wise (location branch staff), Group/Team (entire group members).', 'When assigning groups, checks group_employees joined_at <= to_date and removed_at >= to_date to guarantee accurate team rosters.', 'Admin / Permitted', 'Completed'],
    [3, 'Task Creation', 'Date & Integrity Validation', 'Enforces required task title, valid task_type, subtask_id, status, from_date, and validates that to_date is strictly greater than or equal to from_date.', 'Rejects invalid dates with explicit validation error messages.', 'Admin / Permitted', 'Completed'],
    [4, 'Task Creation', 'File Attachment Upload', 'Uploads supporting documents, specifications, or images to public/uploads/tasks/ with timestamp-prefixed filenames.', 'Max file size 2MB; handles automatic file replacement and old file unlinking during updates.', 'Admin / Permitted', 'Completed'],
    [5, 'Task Listing', 'Server-Side Pagination', 'Server-side pagination supporting 10, 25, 50, 100 items per page with preservation of query string parameters.', 'Reduces server memory overhead and ensures snappy response times even with thousands of tasks.', 'All Staff', 'Completed'],
    [6, 'Task Listing', 'Dynamic Ajax Filter Bar', 'Instant Ajax filtering by Location, Group, Employee, and Status without full page reload.', 'Status options include All, Pending, In Progress, Completed, or Pending & In Progress combo.', 'All Staff', 'Completed'],
    [7, 'Task Listing', 'Global Search Engine', 'Multi-field LIKE search across task title, description, subtask name, location name, group name, assigned employee names, creator name, and dates.', 'Combines with active dropdown filters seamlessly.', 'All Staff', 'Completed'],
    [8, 'Task Listing', 'Permission Isolation (RBAC)', 'Enforces data scoping based on permissions: task_view_all grants global visibility; task_view_own restricts visibility to tasks created by or assigned to the user.', 'Prevents employees from viewing tasks from other departments or unrelated personnel.', 'All Staff', 'Completed'],
    [9, 'Task Show Page', 'Visual Assignee Avatars', 'Renders profile picture avatars, full names, and departments of all assigned employees.', 'Fallbacks to default user image if employee image is not present.', 'All Staff', 'Completed'],
    [10, 'Task Show Page', 'Inline Status & Subtask Change', 'Dropdowns for authorized users to transition task status or change workflow subtask directly via Ajax.', 'Automatically records detailed activity entry and dispatches notification to all involved parties.', 'Permitted Users', 'Completed'],
    [11, 'Task Show Page', 'Remark Posting & Feed', 'Modal form allowing users to submit progress remarks up to 1000 characters.', 'Appends remark to activity stream, updates latest remark preview, and alerts team members.', 'Assigned / Admin', 'Completed'],
    [12, 'Task Show Page', 'Activity Audit Stream', 'Displays chronological list of all remarks, subtask transitions, and status changes with user avatars and human-readable time.', 'Provides transparent progress verification for management.', 'Assigned / Admin', 'Completed'],
    [13, 'Visual Kanban Board', 'Dynamic Subtask Columns', 'Horizontal scrolling Kanban board displaying active tasks (pending/in progress) grouped under their respective subtask headers.', 'Header badges show real-time count of active tasks in that stage.', 'All Staff', 'Completed'],
    [14, 'Visual Kanban Board', 'Urgency Color Indicator: Red', 'Without Starting - Already Overdue: Highlights card in Red with bold border when task status is pending and to_date has already passed.', 'Alerts management to tasks that have not started and are past deadline.', 'All Staff', 'Completed'],
    [15, 'Visual Kanban Board', 'Urgency Color Indicator: Purple', 'In Progress - Already Overdue: Highlights card in Purple when task is currently in progress but due date has passed.', 'Identifies delayed active jobs that require escalation or support.', 'All Staff', 'Completed'],
    [16, 'Visual Kanban Board', 'Urgency Color Indicator: Orange', 'Without Starting - Due Today/Tomorrow: Highlights card in Orange when pending task is due today or tomorrow.', 'Warns staff of impending deadlines before they become overdue.', 'All Staff', 'Completed'],
    [17, 'Visual Kanban Board', 'Urgency Color Indicator: Yellow', 'In Progress - Due Today/Tomorrow: Highlights card in Yellow when in-progress task is due today or tomorrow.', 'Ensures team prioritizes finishing current-day commitments.', 'All Staff', 'Completed'],
    [18, 'Visual Kanban Board', 'Urgency Color Indicator: Green', 'In Progress: Highlights card in Green when task is in progress and on schedule.', 'Standard operational state.', 'All Staff', 'Completed'],
    [19, 'Visual Kanban Board', 'Urgency Color Indicator: Blue', 'Recently Added: Blinking animation and blue border for tasks created within the last 24 hours.', 'Keyframe animation draws immediate attention to fresh work requests.', 'All Staff', 'Completed'],
    [20, 'Visual Kanban Board', 'Auto-Refresh Timer', '5-minute countdown clock (05:00) that ticks down every second and reloads page at 00:00.', 'Can also be manually triggered via "Refresh Now" button.', 'All Staff', 'Completed'],
    [21, 'Data Export Engine', 'Excel & CSV Export', 'Exports current filtered task view to Excel (.xlsx) or CSV (.csv) using Maatwebsite Excel package.', 'Preserves all active search keywords and dropdown filters.', 'Admin / Permitted', 'Completed'],
];

$r = 5;
foreach ($taskDetails as $row) {
    $s3->setCellValue("A{$r}", $row[0]);
    $s3->setCellValue("B{$r}", $row[1]);
    $s3->setCellValue("C{$r}", $row[2]);
    $s3->setCellValue("D{$r}", $row[3]);
    $s3->setCellValue("E{$r}", $row[4]);
    $s3->setCellValue("F{$r}", $row[5]);
    $s3->setCellValue("G{$r}", $row[6]);
    $r++;
}
$tableEnd3 = $r - 1;
styleDataRows($s3, 5, $tableEnd3, 'G', 'G', ['A', 'F']);
$s3->getStyle("D5:E{$tableEnd3}")->getAlignment()->setWrapText(true);
autoFitColumns($s3, ['A' => 8, 'B' => 24, 'C' => 28, 'D' => 45, 'E' => 45, 'F' => 18, 'G' => 16]);

// ==============================================================================
// 4. SHEET 4: CUSTOM DASHBOARD ENGINE (Deep Dive)
// ==============================================================================
$s4 = $spreadsheet->createSheet();
$s4->setTitle('Custom Dashboard Engine');
applyTitleBlock($s4, 'CUSTOM DASHBOARD BUILDER & DYNAMIC RENDERER', 'JSON-Driven Dynamic Dashboard Architecture & Employee Personalization', 'G');

applyTableHeader($s4, 4, ['S.No.', 'Sub-Module', 'Feature / Component Name', 'Technical Implementation & JSON Architecture', 'Operational Impact & UI Experience', 'Access Level', 'Working Status'], 'G');

$dashData = [
    [1, 'Dashboard Builder', 'Dashboard Creator Form', 'Captures unique dashboard title and initializes structured default JSON schema in view_json database field.', 'Enables limitless custom dashboards tailored to specific departments, stations, or supervisor roles.', 'Super Admin', 'Completed'],
    [2, 'Task Module Config', 'Task Analytics Chart Toggle', 'JSON key `task_module.chart` (boolean). When enabled, renders graphical status breakdown charts for tasks.', 'Provides instant high-level visibility of task health.', 'Super Admin', 'Completed'],
    [3, 'Task Module Config', 'Task Redirect Card Toggle', 'JSON key `task_module.redirect_div` (boolean). Renders clickable KPI counter card linking to task index.', 'Enables quick drilldown to underlying task records.', 'Super Admin', 'Completed'],
    [4, 'Task Module Config', 'Subtask Swimlane Selection', 'JSON key `task_module.subtasks` array. Multi-checkbox UI where admin selects specifically which subtasks appear as Kanban columns.', 'A sewing supervisor only sees Sewing tasks, cutting supervisor only sees Cutting tasks.', 'Super Admin', 'Completed'],
    [5, 'Complaint Module Config', 'Complaint Chart Toggle', 'JSON key `ticket_module.chart` (boolean). Renders complaint status charts for authorized personnel.', 'Helps HR and managers track complaint resolution velocity.', 'Super Admin', 'Completed'],
    [6, 'Complaint Module Config', 'Complaint Redirect Card Toggle', 'JSON key `ticket_module.redirect_div` (boolean). Renders KPI card linking to ticket index.', 'Quick shortcut to employee complaints repository.', 'Super Admin', 'Completed'],
    [7, 'Complaint Module Config', 'Complaint Summary Table Toggle', 'JSON key `ticket_module.table` (boolean). Renders live recent complaints data table directly on dashboard.', 'Surfaces urgent employee grievances without needing to leave dashboard.', 'Super Admin', 'Completed'],
    [8, 'Master Modules Config', 'Location Widgets Toggle', 'JSON key `modules.Location Module` (redirect_div, table). Renders location count card and location data table.', 'Provides direct branch location management widgets.', 'Super Admin', 'Completed'],
    [9, 'Master Modules Config', 'Department Widgets Toggle', 'JSON key `modules.Department Module` (redirect_div, table). Renders department metric cards and table.', 'Department statistics visible on custom homepages.', 'Super Admin', 'Completed'],
    [10, 'Master Modules Config', 'Employee Widgets Toggle', 'JSON key `modules.Employee Module` (redirect_div, table). Renders employee count and active staff table.', 'Employee directory widgets embedded into dashboard.', 'Super Admin', 'Completed'],
    [11, 'Master Modules Config', 'Group / Team Widgets Toggle', 'JSON key `modules.Group Module` (redirect_div, table). Renders team metric cards and group roster table.', 'Team overview embedded directly on dashboard view.', 'Super Admin', 'Completed'],
    [12, 'Employee Access Mapping', 'Employee Dashboard Assignment', 'Stores mappings in `employee_dashboards` table linking employee_id to dashboard_id.', 'Allows assigning multiple custom dashboards to a single employee profile.', 'Super Admin', 'Completed'],
    [13, 'Dynamic Renderer', 'Dashboard Render Controller', 'Parses view_json; validates employee access via employee_dashboards table; aborts 403 if unauthorized.', 'Guarantees that staff can only view dashboards explicitly provisioned to them.', 'Admin / Staff', 'Completed'],
    [14, 'Dynamic Renderer', 'Multi-Dashboard Aggregation', 'Iterates all dashboards assigned to user, extracts all enabled subtasks, and merges into unified Kanban view without duplicate columns.', 'Employee sees unified work streams across all assigned responsibilities.', 'Staff Users', 'Completed'],
    [15, 'Dynamic Navigation', 'Dynamic Sidebar Submenu', 'Sidebar blade component queries assigned dashboards and dynamically lists them under Dashboard accordion menu.', 'Provides clean 1-click navigation between assigned dashboards.', 'All Staff', 'Completed'],
];

$r = 5;
foreach ($dashData as $row) {
    $s4->setCellValue("A{$r}", $row[0]);
    $s4->setCellValue("B{$r}", $row[1]);
    $s4->setCellValue("C{$r}", $row[2]);
    $s4->setCellValue("D{$r}", $row[3]);
    $s4->setCellValue("E{$r}", $row[4]);
    $s4->setCellValue("F{$r}", $row[5]);
    $s4->setCellValue("G{$r}", $row[6]);
    $r++;
}
$tableEnd4 = $r - 1;
styleDataRows($s4, 5, $tableEnd4, 'G', 'G', ['A', 'F']);
$s4->getStyle("D5:E{$tableEnd4}")->getAlignment()->setWrapText(true);
autoFitColumns($s4, ['A' => 8, 'B' => 24, 'C' => 28, 'D' => 45, 'E' => 45, 'F' => 16, 'G' => 16]);

// ==============================================================================
// 5. SHEET 5: EMPLOYEE COMPLAINTS MODULE (Deep Dive)
// ==============================================================================
$s5 = $spreadsheet->createSheet();
$s5->setTitle('Employee Complaints Module');
applyTitleBlock($s5, 'EMPLOYEE COMPLAINTS & CONFIDENTIAL TICKET SYSTEM', 'Confidential Anti-Bias Isolation, WhatsApp-Style Interactive Chat & Workflow Lifecycle', 'G');

applyTableHeader($s5, 4, ['S.No.', 'Functional Area', 'Feature / Component', 'Technical Architecture & Security Rules', 'Operational Description & User Benefit', 'Access Scope', 'Status'], 'G');

$ticketData = [
    [1, 'Confidentiality Security', 'Accused User Blind Rule', 'Private helper `isComplainedAgainst()` and query scope `applyComplaintScope()` query `ticket_assignments`. An employee accused in a complaint can NEVER see it.', 'Protects whistleblowers and complainants from workplace retaliation or bias.', 'Strict System Rule', 'Completed'],
    [2, 'Confidentiality Security', 'Complainant Access Scope', 'Complainant (ticket user_id) can view and follow complaints they submitted if they possess ticket_view_own or ticket_add permissions.', 'Gives staff full visibility into the investigation of their concerns.', 'Complainant Staff', 'Completed'],
    [3, 'Confidentiality Security', 'Super Admin Oversight', 'Super Admin and users with ticket_view_all possess global visibility to manage and resolve all organization complaints.', 'Centralizes grievance management with HR and leadership.', 'Super Admin / HR', 'Completed'],
    [4, 'Complaint Creation', 'Complaint Filing Form', 'Captures title, subject, priority (low, medium, high), department_id, location_id, ticket_type (individual / location), description.', 'Structured form ensuring all necessary grievance details are captured.', 'Admin / Permitted', 'Completed'],
    [5, 'Complaint Creation', 'Accused Employee Assignment', 'Multi-select dropdown allowing complainant/admin to specify individual employees or entire team against whom complaint is filed.', 'Automatically creates records in `ticket_assignments` and updates ticket status to `assigned`.', 'Admin / Permitted', 'Completed'],
    [6, 'Complaint Creation', 'Evidence Attachment Upload', 'Uploads supporting evidence files (JPG, PNG, PDF, DOC, XLSX, TXT, ZIP up to 15MB) into public/uploads/tickets/ with sanitized filenames.', 'Permits concrete documentation and evidence submission.', 'Admin / Permitted', 'Completed'],
    [7, 'Complaint Listing', 'Ajax Filter & Tabbed Status', 'Filters complaints by active status (excludes closed by default), specific status (pending, assigned, in_progress, hold, close_request, closed, reopen), location, and group.', 'Fast, responsive filtering without page reload.', 'Admin / Complainant', 'Completed'],
    [8, 'Status Lifecycle', 'Lifecycle State Machine', 'Statuses: Pending -> Assigned -> In Progress -> Hold -> Close Request -> Closed / Reopen. Validated via `getTicketStatusOptions()`.', 'Ensures complaints follow formal procedural steps towards resolution.', 'Admin / Permitted', 'Completed'],
    [9, 'Status Lifecycle', 'Two-Party Close Verification', 'When investigator finishes, they set status to `close_request`. The creator then verifies resolution and chooses `closed` or `reopen`.', 'Prevents investigators from unilaterally closing complaints without complainant satisfaction.', 'Creator & Admin', 'Completed'],
    [10, 'Status Lifecycle', 'Reopen Flow', 'If complainant is dissatisfied, selecting `reopen` resets status back to `pending`, logging a reopening activity entry.', 'Ensures unresolved grievances are reinvestigated promptly.', 'Creator & Admin', 'Completed'],
    [11, 'Interactive Chat', 'WhatsApp-Style Realtime Chat', 'Asynchronous Ajax chat box allowing back-and-forth discussion between complainant and investigators.', 'Replaces slow external emails with an auditable in-app communication thread.', 'Admin & Complainant', 'Completed'],
    [12, 'Interactive Chat', 'Chat File & Document Sharing', 'Uploads images, PDFs, office docs, archives up to 15MB directly in chat messages stored in `uploads/comments/`.', 'Calculates formatted file size (KB/MB) and provides instant preview/download links.', 'Admin & Complainant', 'Completed'],
    [13, 'Interactive Chat', 'Formatted Message Bubbles', 'Custom styled chat bubbles: sender messages on right, receiver messages on left, avatar photo, sender department tag, humanized time.', 'Clean modern messaging experience familiar to users.', 'Admin & Complainant', 'Completed'],
    [14, 'Interactive Chat', 'Smart Date Dividers', 'Separates message stream with clean dividers: "Today", "Yesterday", or "DD Mon YYYY".', 'Improves chronological readability of long discussions.', 'Admin & Complainant', 'Completed'],
    [15, 'Assignment Management', 'Reassign Accused Staff/Team', 'Admin can reassign complaint to different employee or entire group/team (`ticket_assignments`).', 'Enables dynamic team-level complaint delegation.', 'Super Admin / HR', 'Completed'],
    [16, 'Audit Logging', 'Investigation Audit Trail', 'Chronological feed combining ticket creation, accused assignments, status updates, and chat messages.', 'Provides indisputable audit evidence for HR and legal compliance.', 'Admin & Complainant', 'Completed'],
];

$r = 5;
foreach ($ticketData as $row) {
    $s5->setCellValue("A{$r}", $row[0]);
    $s5->setCellValue("B{$r}", $row[1]);
    $s5->setCellValue("C{$r}", $row[2]);
    $s5->setCellValue("D{$r}", $row[3]);
    $s5->setCellValue("E{$r}", $row[4]);
    $s5->setCellValue("F{$r}", $row[5]);
    $s5->setCellValue("G{$r}", $row[6]);
    $r++;
}
$tableEnd5 = $r - 1;
styleDataRows($s5, 5, $tableEnd5, 'G', 'G', ['A', 'F']);
$s5->getStyle("D5:E{$tableEnd5}")->getAlignment()->setWrapText(true);
autoFitColumns($s5, ['A' => 8, 'B' => 24, 'C' => 28, 'D' => 45, 'E' => 45, 'F' => 18, 'G' => 16]);

// ==============================================================================
// 6. SHEET 6: BARCODE & PRODUCTION MODULE (Deep Dive)
// ==============================================================================
$s6 = $spreadsheet->createSheet();
$s6->setTitle('Barcode & Production');
applyTitleBlock($s6, 'INDUSTRIAL BARCODE SCANNING, CSV IMPORT & PRODUCTION PROCESS', 'Automated Tilde CSV Parser, Live Barcode Scanner, OP_10 Multi-Page Engine & Packing Slips', 'G');

applyTableHeader($s6, 4, ['S.No.', 'Functional Area', 'Component / Feature', 'Technical Architecture & Implementation', 'Shop Floor Operational Workflow', 'Access Level', 'Working Status'], 'G');

$prodData = [
    [1, 'CSV Barcode Import', 'Tilde Delimiter Parser', 'Uploads CSV/TXT files and parses lines using tilde (`~`) delimiter into: WO No, Part No, SO No, Line No, Operation Code, Quantity, Page Count.', 'Converts industrial engineering cutting & production orders into system barcode details.', 'Admin / Production', 'Completed'],
    [2, 'CSV Barcode Import', 'Duplicate Detection & Page Sync', 'Detects if matching record already exists; if found, automatically updates page number (`no_of_pages`) and skips duplicate insert.', 'Prevents duplicate work orders while keeping page references up to date.', 'Admin / Production', 'Completed'],
    [3, 'CSV Barcode Import', 'Import Statistics & Feedback', 'Validates 6-field row integrity and returns JSON reporting total inserted entries vs skipped entries.', 'Gives operators clear confirmation of successful batch imports.', 'Admin / Production', 'Completed'],
    [4, 'Work Order Inventory', 'Work Order Master Directory', 'Aggregates barcode details grouped by Work Order No (`wo_no`) with latest update timestamps.', 'Provides single-view inventory of all uploaded manufacturing orders.', 'Admin / Production', 'Completed'],
    [5, 'Work Order Inventory', 'Barcode Details Drilldown', 'Drilldown view displaying all parts, sales orders, line numbers, operations, and quantities for selected Work Order.', 'Enables detailed inspection of order bill of materials.', 'Admin / Production', 'Completed'],
    [6, 'Work Order Inventory', 'Production Delete Protection', 'Before deleting a barcode detail, checks `scan_brcodes` for matching WO, SO, Part, Line, and Operation.', 'Blocks deletion of work orders actively linked to live production records with explicit warning.', 'Admin Only', 'Completed'],
    [7, 'Work Order Inventory', 'Bulk Delete Safety Audit', 'Bulk deletion handles arrays of selected IDs; deletes safe records and generates report of non-deletable active records.', 'Ensures batch cleanup does not damage live production records.', 'Admin Only', 'Completed'],
    [8, 'Live Production Scanning', 'Hardware Scanner Auto-Focus', 'Auto-focused text input listening for USB / Bluetooth laser barcode scanner input.', 'Factory workers scan physical barcode tickets consecutively without touching keyboard.', 'Production Staff', 'Completed'],
    [9, 'Live Production Scanning', 'Barcode String Deconstruction', 'Explodes scanned string formatted as `wo_no~part_no~po_no~so_no~line_no` into individual parameters.', 'Instantly validates extracted WO against selected Work Order on screen.', 'Production Staff', 'Completed'],
    [10, 'Live Production Scanning', 'Available Quota Validation', 'Checks master `barcode_details` table; verifies available quantity and prevents scanning beyond available quota.', 'Prevents over-production or duplicate scanning of finished batches.', 'Production Staff', 'Completed'],
    [11, 'Live Production Scanning', 'OP_10 Multi-Page Support', 'Supports multi-page documentation for OP_10 operation with dedicated page number selector and tracking.', 'Handles multi-page engineering drawing batches seamlessly.', 'Production Staff', 'Completed'],
    [12, 'Live Production Scanning', 'Automatic Count Increment', 'Increments quantity in `scan_brcodes` and logs individual user scan records in `histories` table with timestamp.', 'Maintains real-time stock tally and worker productivity logs.', 'Production Staff', 'Completed'],
    [13, 'Live Production Scanning', 'Manual Scan Entry Modal', 'Fallback form permitting manual entry of WO, Part No, PO, SO, Line, and Operation Code when scanner is offline.', 'Ensures factory operations continue uninterrupted during scanner maintenance.', 'Production Staff', 'Completed'],
    [14, 'Live Production Scanning', 'Inline Quantity Editing', 'Permits authorized adjustments to scanned record quantities with master stock quota validation.', 'Allows supervisors to rectify operator scanning errors.', 'Supervisors', 'Completed'],
    [15, 'Packing Slip Module', 'Packing Station Scanning', 'Dedicated barcode scanning interface designed specifically for packing stations.', 'Scans finished goods into packing slips and updates packing history.', 'Packing Staff', 'Completed'],
    [16, 'Packing Slip Module', 'Batch File Organization', 'Groups packing records by batch/file name (`packing-slip-index`) with latest update timestamps.', 'Organizes dispatches by customer shipment batch.', 'Packing Staff', 'Completed'],
    [17, 'Packing Slip Module', 'Packing Quantity & History', 'Maintains live packing quantity in `packing_slips` and records user actions in `packing_histories`.', 'Tracks who packed which item and in what quantity.', 'Packing Staff', 'Completed'],
    [18, 'Data Export Engine', 'Scanned Records Excel Export', 'Exports scanned production records filtered by Work Order, Operation Code, and Page number to Excel.', 'Generates production verification spreadsheets.', 'Production Admin', 'Completed'],
    [19, 'Data Export Engine', 'Production Records Excel Export', 'Exports aggregated production history filtered by Work Order and Operation to Excel.', 'Generates comprehensive manufacturing history exports.', 'Production Admin', 'Completed'],
    [20, 'Data Export Engine', 'Packing Records Excel Export', 'Exports packing slip records filtered by batch file to Excel spreadsheet.', 'Provides shipment verification spreadsheets.', 'Packing Admin', 'Completed'],
    [21, 'Data Export Engine', 'Production Records PDF Report', 'Generates DomPDF formatted report with automatic blank row separators between different Sales Orders (SO).', 'Formatted PDF documentation ready for printing and physical archiving.', 'Production Admin', 'Completed'],
    [22, 'Data Export Engine', 'Packing Records PDF Report', 'Generates formatted packing slip PDF report sorted by Sales Order and Part Number with separators.', 'Official physical packing documentation for outgoing shipments.', 'Packing Admin', 'Completed'],
];

$r = 5;
foreach ($prodData as $row) {
    $s6->setCellValue("A{$r}", $row[0]);
    $s6->setCellValue("B{$r}", $row[1]);
    $s6->setCellValue("C{$r}", $row[2]);
    $s6->setCellValue("D{$r}", $row[3]);
    $s6->setCellValue("E{$r}", $row[4]);
    $s6->setCellValue("F{$r}", $row[5]);
    $s6->setCellValue("G{$r}", $row[6]);
    $r++;
}
$tableEnd6 = $r - 1;
styleDataRows($s6, 5, $tableEnd6, 'G', 'G', ['A', 'F']);
$s6->getStyle("D5:E{$tableEnd6}")->getAlignment()->setWrapText(true);
autoFitColumns($s6, ['A' => 8, 'B' => 24, 'C' => 28, 'D' => 45, 'E' => 45, 'F' => 18, 'G' => 16]);

// ==============================================================================
// 7. SHEET 7: RBAC & SECURITY STRUCTURE
// ==============================================================================
$s7 = $spreadsheet->createSheet();
$s7->setTitle('RBAC & Security Structure');
applyTitleBlock($s7, 'ROLE-BASED ACCESS CONTROL (RBAC) & PERMISSION MATRIX', 'Complete Inventory of Roles, Permissions, Module Slugs & Data Scoping', 'F');

applyTableHeader($s7, 4, ['S.No.', 'Permission Module', 'Action / Capability', 'Unique Permission Slug', 'Description & Scope of Authority', 'Status'], 'F');

$rbacData = [
    [1, 'Dashboard', 'View Dashboard', 'dashboard_view', 'Allows viewing main KPI analytics dashboard.', 'Implemented'],
    [2, 'Dashboard', 'View Own Data Only', 'dashboard_view_own', 'Restricts dashboard metrics strictly to user own assigned tasks and activities.', 'Implemented'],
    [3, 'Dashboard', 'View All Organization Data', 'dashboard_view_all', 'Allows viewing organization-wide aggregated metrics across all branches.', 'Implemented'],
    [4, 'Report', 'View Barcode Reports', 'report_view', 'Grants access to view imported barcode detail reports.', 'Implemented'],
    [5, 'Report', 'Import Barcode Data', 'report_add', 'Grants permission to upload and parse CSV barcode files.', 'Implemented'],
    [6, 'Users', 'View User List', 'users_view', 'Grants access to system users directory.', 'Implemented'],
    [7, 'Users', 'Create User', 'users_add', 'Allows registering new users and setting initial credentials.', 'Implemented'],
    [8, 'Users', 'Edit User', 'users_edit', 'Allows updating user information, mobile, address, and role.', 'Implemented'],
    [9, 'Users', 'Delete User', 'users_delete', 'Allows removing user accounts from system.', 'Implemented'],
    [10, 'Employee', 'View Employees', 'employee_view', 'Base permission to view employee master directory.', 'Implemented'],
    [11, 'Employee', 'View Own Employee Profile', 'employee_view_own', 'Restricts employee directory visibility to logged-in user own profile.', 'Implemented'],
    [12, 'Employee', 'View All Employees', 'employee_view_all', 'Allows viewing all employee records across all locations.', 'Implemented'],
    [13, 'Employee', 'Create Employee', 'employee_add', 'Allows creating dual user/employee profile with photo and role.', 'Implemented'],
    [14, 'Employee', 'Edit Employee', 'employee_edit', 'Allows editing employee details and toggling active status.', 'Implemented'],
    [15, 'Employee', 'Delete Employee', 'employee_delete', 'Allows removing employee profiles.', 'Implemented'],
    [16, 'Location', 'Create Location', 'location_add', 'Allows creating new operating branch locations.', 'Implemented'],
    [17, 'Location', 'Edit Location', 'location_edit', 'Allows editing existing location names.', 'Implemented'],
    [18, 'Location', 'Delete Location', 'location_delete', 'Allows deleting branch locations.', 'Implemented'],
    [19, 'Department', 'Create Department', 'department_add', 'Allows creating departments within locations.', 'Implemented'],
    [20, 'Department', 'Edit Department', 'department_edit', 'Allows editing department names and location bindings.', 'Implemented'],
    [21, 'Department', 'Delete Department', 'department_delete', 'Allows removing departments.', 'Implemented'],
    [22, 'Role', 'Create Role', 'role_add', 'Allows defining new system roles.', 'Implemented'],
    [23, 'Role', 'Edit Role', 'role_edit', 'Allows renaming roles and updating role slugs.', 'Implemented'],
    [24, 'Role', 'Delete Role', 'role_delete', 'Allows deleting roles.', 'Implemented'],
    [25, 'Permission', 'Create Permission', 'permission_add', 'Allows registering new permission capabilities.', 'Implemented'],
    [26, 'Permission', 'Edit Permission', 'permission_edit', 'Allows modifying permission definitions.', 'Implemented'],
    [27, 'Permission', 'Delete Permission', 'permission_delete', 'Allows deleting permission entries.', 'Implemented'],
    [28, 'RolePermission', 'View Assignments', 'role_permission_view', 'Allows viewing the role-permission assignment matrix.', 'Implemented'],
    [29, 'RolePermission', 'Assign Permissions', 'role_permission_add', 'Allows assigning permissions to roles.', 'Implemented'],
    [30, 'RolePermission', 'Edit Assignments', 'role_permission_edit', 'Allows synchronizing permissions for a role.', 'Implemented'],
    [31, 'RolePermission', 'Revoke Assignments', 'role_permission_delete', 'Allows revoking all permissions from a role.', 'Implemented'],
    [32, 'Group', 'View Groups', 'group_view', 'Allows viewing team / group rosters.', 'Implemented'],
    [33, 'Group', 'Create Group', 'group_add', 'Allows creating new teams with location and members.', 'Implemented'],
    [34, 'Group', 'Edit Group', 'group_edit', 'Allows editing groups and managing join/remove member dates.', 'Implemented'],
    [35, 'Group', 'Delete Group', 'group_delete', 'Allows deleting groups.', 'Implemented'],
    [36, 'Task', 'View Tasks', 'task_view', 'Base permission to access task module.', 'Implemented'],
    [37, 'Task', 'View Own Tasks', 'task_view_own', 'Restricts task visibility strictly to tasks created by or assigned to user.', 'Implemented'],
    [38, 'Task', 'View All Tasks', 'task_view_all', 'Allows viewing all organization tasks regardless of assignment.', 'Implemented'],
    [39, 'Task', 'Create Task', 'task_add', 'Allows creating tasks with individual, location, or group assignments.', 'Implemented'],
    [40, 'Task', 'Edit Task', 'task_edit', 'Allows modifying task parameters, dates, and attachments.', 'Implemented'],
    [41, 'Task', 'Delete Task', 'task_delete', 'Allows deleting tasks.', 'Implemented'],
    [42, 'Task', 'Change Task Status', 'task_change_status', 'Allows changing status between Pending, In Progress, Completed.', 'Implemented'],
    [43, 'Task', 'Change Subtask Stage', 'task_change_subtask_status', 'Allows moving tasks between subtask workflow stages.', 'Implemented'],
    [44, 'Ticket', 'View Complaints', 'ticket_view', 'Base permission to access employee complaints module.', 'Implemented'],
    [45, 'Ticket', 'View Own Complaints', 'ticket_view_own', 'Allows user to view complaints they personally submitted (strict anti-bias rule).', 'Implemented'],
    [46, 'Ticket', 'View All Complaints', 'ticket_view_all', 'Allows management to view all complaints across the organization.', 'Implemented'],
    [47, 'Ticket', 'File Complaint', 'ticket_add', 'Allows filing a new confidential employee complaint with evidence.', 'Implemented'],
    [48, 'Ticket', 'Edit Complaint', 'ticket_edit', 'Allows modifying complaint details and evidence files.', 'Implemented'],
    [49, 'Ticket', 'Delete Complaint', 'ticket_delete', 'Allows removing complaint records.', 'Implemented'],
    [50, 'Ticket', 'Update Ticket Status', 'ticket_change_status', 'Allows advancing status through Pending, Assigned, In Progress, Hold, Close Request, Closed, Reopened.', 'Implemented'],
    [51, 'Production', 'View Production Module', 'production_view', 'Allows access to live barcode production scanning and reporting.', 'Implemented'],
    [52, 'Packing', 'View Packing Module', 'packing_view', 'Allows access to packing slip scanning and batch dispatch records.', 'Implemented'],
];

$r = 5;
foreach ($rbacData as $row) {
    $s7->setCellValue("A{$r}", $row[0]);
    $s7->setCellValue("B{$r}", $row[1]);
    $s7->setCellValue("C{$r}", $row[2]);
    $s7->setCellValue("D{$r}", $row[3]);
    $s7->setCellValue("E{$r}", $row[4]);
    $s7->setCellValue("F{$r}", $row[5]);
    $r++;
}
$tableEnd7 = $r - 1;
styleDataRows($s7, 5, $tableEnd7, 'F', 'F', ['A', 'C', 'D']);
$s7->getStyle("E5:E{$tableEnd7}")->getAlignment()->setWrapText(true);
autoFitColumns($s7, ['A' => 8, 'B' => 20, 'C' => 24, 'D' => 28, 'E' => 50, 'F' => 16]);

// ==============================================================================
// 8. SHEET 8: DATABASE ENTITIES & MODELS
// ==============================================================================
$s8 = $spreadsheet->createSheet();
$s8->setTitle('Database Entities');
applyTitleBlock($s8, 'DATABASE SCHEMA & ELOQUENT ENTITY MODELS', 'Inventory of All 24 Database Tables, Primary Keys, Foreign Keys & Architecture', 'F');

applyTableHeader($s8, 4, ['S.No.', 'Table Name', 'Eloquent Model Name', 'Key Database Columns', 'Relationships & Operational Purpose', 'Status'], 'F');

$dbData = [
    [1, 'users', 'User', 'id, name, email, password, show_password, mobile, address, user_type, role_id, is_active', 'Core authentication table; links to employee profile, role, notifications, and activity logs.', 'Active Table'],
    [2, 'employees', 'Employee', 'id, user_id, name, email, mobile, address, location_id, department_id, role_id, gender, image, joining_date', 'Employee profile details; connects User to Location, Department, Role, Groups, and Tasks.', 'Active Table'],
    [3, 'locations', 'Location', 'id, user_id, name', 'Physical plant/branch locations; foreign key in departments, employees, groups, tasks, and tickets.', 'Active Table'],
    [4, 'departments', 'Department', 'id, user_id, location_id, dept_name', 'Functional departments within locations; linked to employees, groups, tasks, and tickets.', 'Active Table'],
    [5, 'roles', 'Role', 'id, user_id, name, slug', 'System roles (e.g. Admin, Manager, Supervisor, Employee); foreign key in users and role_permissions.', 'Active Table'],
    [6, 'permissions', 'Permission', 'id, user_id, name, slug, module, action', 'System capabilities registry; linked via role_permissions to roles.', 'Active Table'],
    [7, 'role_permissions', 'RolePermission', 'id, user_id, role_id, permission_id', 'Pivot table assigning permissions to roles for RBAC enforcement.', 'Active Table'],
    [8, 'groups', 'Group', 'id, user_id, group_name, location_id, department_id', 'Operational teams/groups; foreign key in group_employees, tasks, and tickets.', 'Active Table'],
    [9, 'group_employees', 'GroupEmployee', 'id, group_id, employee_id, joined_at, removed_at', 'Pivot tracking team membership with joined_at and removed_at dates for historical integrity.', 'Active Table'],
    [10, 'subtasks', 'Subtask', 'id, user_id, subtask', 'Workflow stage classifications (e.g. Cutting, Sewing, QC, Finishing, Packing) used in Kanban.', 'Active Table'],
    [11, 'tasks', 'Task', 'id, user_id, title, task_type, subtask_id, mode, status, from_date, to_date, description, file, location_id, group_id', 'Core tasks table; tracks lifecycle status, dates, assignment mode, file attachment, and subtask stage.', 'Active Table'],
    [12, 'task_users', 'TaskUser', 'id, task_id, assigned_to', 'Pivot assigning individual employees to tasks (used in individual, location, and group modes).', 'Active Table'],
    [13, 'tickets', 'Ticket', 'id, user_id, title, ticket_type, location_id, department_id, subject, priority, description, attachment, status', 'Confidential employee complaints table; tracks status, priority, department, and evidence files.', 'Active Table'],
    [14, 'ticket_assignments', 'TicketAssignment', 'id, ticket_id, assigned_by, assign_type, assigned_to, location_id, group_id, assigned_at', 'Tracks employees or groups against whom complaint is filed; powers anti-bias visibility blocker.', 'Active Table'],
    [15, 'ticket_comments', 'TicketComment', 'id, ticket_id, user_id, comment, attachment', 'WhatsApp-style chat messages and shared files for complaints between complainant and admin.', 'Active Table'],
    [16, 'custom_dashboards', 'CustomDashboard', 'id, user_id, dash_name, view_json', 'Custom dashboard definitions storing modular layout settings and subtask inclusion in view_json.', 'Active Table'],
    [17, 'employee_dashboards', 'DB Table', 'id, employee_id, dashboard_id', 'Pivot table mapping employees to their allowed custom dashboards.', 'Active Table'],
    [18, 'barcode_details', 'BarcodeDetail', 'id, wo_no, part_no, so_no, line_no, operation_code, quantity, no_of_pages', 'Master inventory table parsed from tilde-delimited CSV imports; enforces available stock quota.', 'Active Table'],
    [19, 'scan_brcodes', 'ScanBarcode', 'id, wo_no, part_no, po_no, so_no, line_no, operation_no, quantity, page_no, user_id, user_name, timestamp', 'Live production records tracking total scanned quantities per WO, Part, SO, Operation, Page.', 'Active Table'],
    [20, 'histories', 'History', 'id, wo_no, part_no, po_no, so_no, line_no, operation_no, quantity, page_no, user_id, user_name', 'Audit log tracking individual user barcode scans and timestamps for operator accountability.', 'Active Table'],
    [21, 'packing_slips', 'PackingSlip', 'id, wo_no, part_no, po_no, so_no, line_no, quantity, file_name, user_id, user_name', 'Live packing station records grouped by batch file name.', 'Active Table'],
    [22, 'packing_histories', 'PackingHistory', 'id, wo_no, part_no, po_no, so_no, line_no, quantity, file_name, user_id, user_name', 'Historical ledger tracking individual user packing actions per batch.', 'Active Table'],
    [23, 'activities', 'Activity', 'id, user_id, table_name, table_id, operation, description', 'Central audit ledger recording all system events (create, update, delete, remark, status_change, etc.).', 'Active Table'],
    [24, 'notifications', 'DatabaseNotification', 'id, type, notifiable_type, notifiable_id, data, read_at', 'Laravel database notifications powering topbar bell badge and unread/seen notification drawer.', 'Active Table'],
];

$r = 5;
foreach ($dbData as $row) {
    $s8->setCellValue("A{$r}", $row[0]);
    $s8->setCellValue("B{$r}", $row[1]);
    $s8->setCellValue("C{$r}", $row[2]);
    $s8->setCellValue("D{$r}", $row[3]);
    $s8->setCellValue("E{$r}", $row[4]);
    $s8->setCellValue("F{$r}", $row[5]);
    $r++;
}
$tableEnd8 = $r - 1;
styleDataRows($s8, 5, $tableEnd8, 'F', 'F', ['A', 'B', 'C']);
$s8->getStyle("D5:E{$tableEnd8}")->getAlignment()->setWrapText(true);
autoFitColumns($s8, ['A' => 8, 'B' => 22, 'C' => 24, 'D' => 45, 'E' => 45, 'F' => 16]);

// Set active sheet to Sheet 1
$spreadsheet->setActiveSheetIndex(0);

// Save Workbook
$outputFilePath = __DIR__ . '/vac-barcode-task.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($outputFilePath);

echo file_exists($outputFilePath) ? "EXCEL_GENERATED_SUCCESSFULLY: " . filesize($outputFilePath) . " bytes\n" : "EXCEL_GENERATION_FAILED\n";
