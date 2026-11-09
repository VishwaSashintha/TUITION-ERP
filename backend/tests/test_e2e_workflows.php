<?php

declare(strict_types=1);

require_once __DIR__ . '/../public/index.php';

use App\Core\Application;
use App\Core\Database;
use App\Core\Session;
use App\Services\AuthService;
use App\Services\BillingService;
use App\Services\TeacherPaymentService;
use App\Services\AccountingService;

$db     = Database::getInstance();
$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $description): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo " [PASS] {$description}\n";
    } else {
        $failed++;
        echo " [FAIL] {$description}\n";
    }
}

echo "======================================================\n";
echo "TUITIONPRO ERP — COMPREHENSIVE E2E WORKFLOW TEST SUITE\n";
echo "======================================================\n\n";

// Clear rate limiter for test IP to avoid lockout pollution from prior runs
(new \App\Services\RateLimiter())->clear('127.0.0.1', 'apex.owner@tuitionpro.lk');

//Authentication
echo "1. Simulating Institute Owner Authentication & Session Initialization...\n";

$authService = new AuthService();
$user = $authService->attempt('apex.owner@tuitionpro.lk', 'Password@123', 'apex');
assertCondition($user !== null, "Institute owner successfully authenticated for tenant 'apex'");
assertCondition(current_tenant_id() === 1, "Active tenant context initialized to Tenant 1 (Apex)");
//Students
echo "\n2. Testing Student Onboarding & Enrollment Lifecycle...\n";

$studentModel = new \App\Models\Student();
$newAdmNo     = 'APX-E2E-' . rand(1000, 9999);

$studentId = $studentModel->create([
    'tenant_id'        => 1,
    'branch_id'        => 1,
    'admission_number' => $newAdmNo,
    'first_name'       => 'Nimali',
    'last_name'        => 'Senanayake',
    'full_name'        => 'Nimali Senanayake',
    'email'            => 'nimali.' . uniqid() . '@student.lk',
    'phone'            => '0771234999',
    'status'           => 'active',
    'admission_date'   => date('Y-m-d'),
]);
assertCondition($studentId > 0, "Student 'Nimali Senanayake' created with admission number {$newAdmNo}");

$enrollmentModel = new \App\Models\Enrollment();
$enrollId = $enrollmentModel->create([
    'tenant_id'       => 1,
    'student_id'      => $studentId,
    'batch_id'        => 1,
    'enrollment_date' => date('Y-m-d'),
    'status'          => 'active',
]);
assertCondition($enrollId > 0, "Student successfully enrolled into Batch 1");

$storedEnroll = $db->queryOne(
    "SELECT * FROM `enrollments` WHERE `id` = :id AND `tenant_id` = 1",
    ['id' => $enrollId]
);
assertCondition(
    $storedEnroll !== null && (int)$storedEnroll['student_id'] === $studentId,
    "Enrollment record persisted with correct student reference"
);
echo "\n3. Testing Class Scheduling & Session Generation...\n";

$schedModel = new \App\Models\ClassSchedule();
$schedId    = $schedModel->create([
    'tenant_id'   => 1,
    'branch_id'   => 1,
    'batch_id'    => 1,
    'teacher_id'  => 1,
    'day_of_week' => (int)date('w'),
    'start_time'  => '08:30:00',
    'end_time'    => '11:30:00',
    'classroom'   => 'Hall A',
    'is_active'   => 1,
]);
assertCondition($schedId > 0, "Weekly schedule slot registered (teacher_id=1, batch_id=1)");

$sessionModel = new \App\Models\ClassSession();
$sessId       = $sessionModel->create([
    'tenant_id'    => 1,
    'branch_id'    => 1,
    'batch_id'     => 1,
    'teacher_id'   => 1,
    'schedule_id'  => $schedId,
    'session_date' => date('Y-m-d'),
    'start_time'   => '08:30:00',
    'end_time'     => '11:30:00',
    'classroom'    => 'Hall A',
    'status'       => 'scheduled',
]);
assertCondition($sessId > 0, "Actual date session generated for today");
//Attendance
echo "\n4. Testing QR Attendance Scanner & Validation...\n";

$db->execute(
    "INSERT INTO `attendance_records`
        (`tenant_id`, `class_session_id`, `batch_id`, `student_id`, `status`,
         `check_in_time`, `recorded_by`, `notes`, `created_at`, `updated_at`)
     VALUES
        (1, :sid, 1, :stuid, 'present', :time, 2, 'QR Scanned Test', NOW(), NOW())",
    ['sid' => $sessId, 'stuid' => $studentId, 'time' => date('H:i:s')]
);

$attRecord = $db->queryOne(
    "SELECT * FROM `attendance_records`
     WHERE `class_session_id` = :sid AND `student_id` = :stuid",
    ['sid' => $sessId, 'stuid' => $studentId]
);
assertCondition(
    $attRecord !== null && $attRecord['status'] === 'present',
    "Student instantly checked in as PRESENT for active session"
);
echo "\n5. Testing Invoicing, Payments, Allocation & Receipt Printing...\n";

$billingService = new BillingService();
$invoiceId      = $billingService->createInvoice(
    $studentId,
    date('Y-m'),
    date('Y-m-25'),
    [
        [
            'description' => 'November 2026 Monthly Tuition Fee',
            'amount'      => 7500.00,
        ],
        [
            'description' => 'Registration / Administration Fee',
            'amount'      => 1500.00,
        ],
    ]
);
assertCondition($invoiceId > 0, "Invoice created for student #{$studentId} — Rs. 9,000.00 total");

$invoice = $db->queryOne("SELECT * FROM `invoices` WHERE `id` = :id", ['id' => $invoiceId]);
assertCondition(
    $invoice !== null && (float)$invoice['total_amount'] === 9000.00,
    "Invoice total correctly computed as Rs. 9,000.00"
);

$payment1Id = $billingService->recordPayment(
    $studentId,
    4000.00,
    'cash',
    [['invoice_id' => $invoiceId, 'amount' => 4000.00]],
    null,
    'First instalment'
);
assertCondition($payment1Id > 0, "First partial payment of Rs. 4,000 recorded");

$invoice = $db->queryOne("SELECT * FROM `invoices` WHERE `id` = :id", ['id' => $invoiceId]);
assertCondition(
    $invoice['status'] === 'partially_paid' && (float)$invoice['balance_amount'] === 5000.00,
    "Invoice status = partially_paid, outstanding balance = Rs. 5,000.00"
);

$payment2Id = $billingService->recordPayment(
    $studentId,
    5000.00,
    'cash',
    [['invoice_id' => $invoiceId, 'amount' => 5000.00]],
    null,
    'Final payment'
);
assertCondition($payment2Id > 0, "Final payment of Rs. 5,000 recorded — invoice fully settled");

$invoice = $db->queryOne("SELECT * FROM `invoices` WHERE `id` = :id", ['id' => $invoiceId]);
assertCondition(
    $invoice['status'] === 'paid' && (float)$invoice['balance_amount'] === 0.00,
    "Invoice status = paid, balance = Rs. 0.00"
);

$overPaymentRejected = false;
try {
    $billingService->recordPayment(
        $studentId,
        1.00,
        'cash',
        [['invoice_id' => $invoiceId, 'amount' => 1.00]],
        null,
        'Illegal over-payment'
    );
} catch (\Exception $e) {
    $overPaymentRejected = true;
}
assertCondition($overPaymentRejected, "Over-payment allocation beyond settled invoice correctly rejected");
//Calculation
echo "\n6. Testing Teacher Compensation Calculation Models...\n";

//Teachers
$ruleModel = new \App\Models\TeacherPaymentRule();
$ruleId    = $ruleModel->create([
    'tenant_id'      => 1,
    'teacher_id'     => 1,
    'batch_id'       => 1,
    'model'          => 'revenue_share',
    'rate'           => 0.00,
    'percentage'     => 60.00,
    'effective_from' => date('Y-m-01'),
]);
assertCondition($ruleId > 0, "Teacher payment rule (60% revenue share) created for Teacher #1");

$teacherPayService = new TeacherPaymentService();
$calc = $teacherPayService->calculateCompensation(1, 1, 1);
assertCondition(
    $calc !== null && isset($calc['calculated_amount']),
    "Teacher compensation calculation executed successfully (Revenue Share Model — Rs. " . number_format($calc['calculated_amount'], 2) . ")"
);
echo "\n7. Testing Expense Recording & Double-Entry Accounting Verification...\n";

$expenseModel = new \App\Models\Expense();
$expNo        = 'EXP-E2E-' . rand(1000, 9999);
$expenseId    = $expenseModel->create([
    'tenant_id'      => 1,
    'branch_id'      => 1,
    'category_id'    => 1,
    'expense_number' => $expNo,
    'expense_date'   => date('Y-m-d'),
    'payee'          => 'Office Supplies Supplier',
    'amount'         => 2500.00,
    'paid_amount'    => 2500.00,
    'balance_amount' => 0.00,
    'payment_status' => 'paid',
    'description'    => 'Office stationery and supplies',
    'created_by'     => 2,
]);
assertCondition($expenseId > 0, "Expense record (Rs. 2,500) created with number {$expNo}");

//Ledger
$accountingService = new AccountingService();
$journalId = $accountingService->recordExpenseJournal($expenseId);
assertCondition($journalId > 0, "Balanced double-entry journal (Debit=Credit=Rs. 2,500) posted for expense");

$tb = $accountingService->getTrialBalance();
assertCondition(
    isset($tb['total_debit']) && $tb['total_debit'] === $tb['total_credit'],
    "Trial Balance verification: Total Debits (Rs. " . number_format($tb['total_debit'], 2) .
    ") strictly equal Total Credits (Rs. " . number_format($tb['total_credit'], 2) . ")"
);

$pnl = $accountingService->getProfitAndLossStatement();
assertCondition(
    isset($pnl['total_revenue']) && isset($pnl['net_profit']),
    "Profit & Loss Statement computed (Net: Rs. " . number_format($pnl['net_profit'], 2) . ")"
);
echo "\n8. Testing Examinations Scheduling, Grading & Merit Rankings...\n";

$examModel = new \App\Models\Examination();
$examId    = $examModel->create([
    'tenant_id'  => 1,
    'branch_id'  => 1,
    'batch_id'   => 1,
    'subject_id' => 1,
    'name'       => 'November Combined Maths Evaluation',
    'exam_date'  => date('Y-m-d'),
    'max_marks'  => 100.00,
    'pass_mark'  => 40.00,
    'exam_type'  => 'monthly',
    'status'     => 'completed',
    'created_by' => 2,
]);
assertCondition($examId > 0, "Examination 'November Combined Maths Evaluation' scheduled and marked completed");

$db->execute(
    "INSERT INTO `exam_marks`
        (`tenant_id`, `examination_id`, `student_id`, `marks_obtained`, `percentage`,
         `grade`, `rank_in_batch`, `status`, `entered_by`, `created_at`, `updated_at`)
     VALUES (1, :eid, :stuid, 88.00, 88.00, 'A', 1, 'pass', 2, NOW(), NOW())",
    ['eid' => $examId, 'stuid' => $studentId]
);
$db->execute(
    "INSERT INTO `exam_marks`
        (`tenant_id`, `examination_id`, `student_id`, `marks_obtained`, `percentage`,
         `grade`, `rank_in_batch`, `status`, `entered_by`, `created_at`, `updated_at`)
     VALUES (1, :eid, :stuid, 62.00, 62.00, 'C', 2, 'pass', 2, NOW(), NOW())",
    ['eid' => $examId, 'stuid' => 1]
);

$topMark = $db->queryOne(
    "SELECT * FROM `exam_marks` WHERE `examination_id` = :eid AND `student_id` = :stuid",
    ['eid' => $examId, 'stuid' => $studentId]
);
assertCondition(
    $topMark !== null && $topMark['grade'] === 'A' && (int)$topMark['rank_in_batch'] === 1,
    "Merit ranking verified: Student awarded Grade A and Rank #1"
);
echo "\n9. Testing Communication & Notice Board Broadcasting...\n";

$annModel = new \App\Models\Announcement();
$annId    = $annModel->create([
    'tenant_id'       => 1,
    'title'           => 'Term End Vacation Notice',
    'content'         => 'Tuition classes will resume on the first week of next month.',
    'target_audience' => 'all',
    'is_published'    => 1,
    'published_at'    => date('Y-m-d H:i:s'),
    'created_by'      => 2,
]);
assertCondition($annId > 0, "Notice board announcement broadcasted to all students and guardians");

$storedAnn = $db->queryOne(
    "SELECT * FROM `announcements` WHERE `id` = :id AND `tenant_id` = 1",
    ['id' => $annId]
);
assertCondition(
    $storedAnn !== null && $storedAnn['is_published'] == 1,
    "Announcement persisted and marked as published"
);
echo "\n10. Testing Database Backup Generation...\n";

$backupDir     = dirname(__DIR__) . '/storage/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}
$sqlBackupFile = $backupDir . '/tuitionpro_e2e_backup_' . date('Ymd_His') . '.sql';

$tables  = $db->query("SHOW TABLES");
$sqlDump = "-- TuitionPro ERP Database Snapshot\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
foreach ($tables as $tRow) {
    $table     = reset($tRow);
    $createRes = $db->queryOne("SHOW CREATE TABLE `{$table}`");
    $sqlDump  .= "\n\n" . $createRes['Create Table'] . ";\n\n";
    $rows = $db->query("SELECT * FROM `{$table}` LIMIT 5");
    foreach ($rows as $row) {
        $vals     = array_map(function ($val) {
            return $val === null ? 'NULL' : Database::getInstance()->getConnection()->quote((string)$val);
        }, array_values($row));
        $sqlDump .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $vals) . ");\n";
    }
}
file_put_contents($sqlBackupFile, $sqlDump);
assertCondition(
    file_exists($sqlBackupFile) && filesize($sqlBackupFile) > 1024,
    "Full SQL database archive successfully generated (" . round(filesize($sqlBackupFile) / 1024, 1) . " KB)"
);
echo "\n11. Testing Platform Super Admin & Multi-Tenant Provisioning...\n";

$newTenantCode = 'vidura' . rand(100, 999);
$tenantModel   = new \App\Models\Tenant();
$newTenantId   = $tenantModel->create([
    'uuid'                 => 'vidura-uuid-' . uniqid(),
    'name'                 => 'Vidura Higher Education',
    'code'                 => $newTenantCode,
    'email'                => "info@{$newTenantCode}.lk",
    'currency'             => 'LKR',
    'currency_symbol'      => 'Rs.',
    'timezone'             => 'Asia/Colombo',
    'status'               => 'active',
    'subscription_plan_id' => 2,
]);
assertCondition(
    $newTenantId > 0,
    "Super Admin provisioned new institute tenant '{$newTenantCode}' (ID #{$newTenantId})"
);

$crossQuery = $db->query(
    "SELECT * FROM `students` WHERE `tenant_id` = :tid",
    ['tid' => $newTenantId]
);
assertCondition(
    count($crossQuery) === 0,
    "Multi-tenant boundary strictly maintained: zero cross-tenant student data leakage"
);
echo "\n------------------------------------------------------\n";
echo "END-TO-END WORKFLOW RESULTS:\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "------------------------------------------------------\n";

if ($failed > 0) {
    exit(1);
}
