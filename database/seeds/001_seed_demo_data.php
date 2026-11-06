<?php

declare(strict_types=1);

/**
 * TuitionPro ERP — Master Database Seeder
 * Populates standard plans, roles, permissions, platform admin,
 * and two independent, fully featured demo tuition institutes (Apex & Excel).
 */

require_once dirname(__DIR__, 2) . '/public/index.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

echo "Starting TuitionPro ERP Database Seeding...\n";

// Disable foreign key checks during seed
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// Truncate tables
$tables = [
    'role_permissions', 'permissions', 'roles', 'platform_users', 'subscriptions', 'subscription_plans',
    'journal_lines', 'journal_entries', 'account_transfers', 'financial_accounts', 'chart_of_accounts', 'accounting_periods',
    'expense_payments', 'expenses', 'expense_categories',
    'teacher_payments', 'teacher_payment_calculations', 'teacher_payment_periods', 'teacher_payment_rules',
    'refunds', 'payment_allocations', 'payments', 'invoice_items', 'invoices', 'fee_types',
    'attendance_corrections', 'attendance_records', 'class_sessions', 'class_schedules',
    'student_submissions', 'assignments', 'learning_materials', 'notifications', 'announcements',
    'exam_marks', 'examinations', 'grading_rules',
    'enrollments', 'batches', 'courses', 'subjects', 'teacher_availability', 'teachers',
    'student_documents', 'student_guardians', 'guardians', 'students',
    'academic_years', 'branches', 'users', 'audit_logs', 'system_settings', 'tenants'
];

foreach ($tables as $tbl) {
    $pdo->exec("TRUNCATE TABLE `{$tbl}`");
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "Tables cleared.\n";

// ─── 1. SUBSCRIPTION PLANS ───────────────────────────────────────────────────
$plans = [
    [
        'name' => 'Starter Plan',
        'code' => 'starter',
        'price' => 3500.00,
        'billing_period' => 'monthly',
        'student_limit' => 150,
        'branch_limit' => 1,
        'user_limit' => 5,
        'features_json' => json_encode(['students', 'teachers', 'attendance', 'fees', 'reports_basic'])
    ],
    [
        'name' => 'Professional Plan',
        'code' => 'professional',
        'price' => 7500.00,
        'billing_period' => 'monthly',
        'student_limit' => 750,
        'branch_limit' => 3,
        'user_limit' => 15,
        'features_json' => json_encode(['students', 'teachers', 'attendance', 'fees', 'teacher_payments', 'exams', 'portals', 'reports_advanced'])
    ],
    [
        'name' => 'Enterprise Plan',
        'code' => 'enterprise',
        'price' => 15000.00,
        'billing_period' => 'monthly',
        'student_limit' => 2500,
        'branch_limit' => 10,
        'user_limit' => 50,
        'features_json' => json_encode(['all', 'multi_branch', 'custom_branding', 'advanced_accounting', 'priority_support'])
    ]
];

$stmt = $pdo->prepare("INSERT INTO subscription_plans (name, code, price, billing_period, student_limit, branch_limit, user_limit, features_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($plans as $p) {
    $stmt->execute([$p['name'], $p['code'], $p['price'], $p['billing_period'], $p['student_limit'], $p['branch_limit'], $p['user_limit'], $p['features_json']]);
}
echo "Subscription plans seeded.\n";

// ─── 2. PLATFORM USERS ───────────────────────────────────────────────────────
$defaultPassword = password_hash('Password@123', PASSWORD_BCRYPT);
$pdo->prepare("INSERT INTO platform_users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'super_admin', 'active')")
    ->execute(['TuitionPro Super Admin', 'superadmin@tuitionpro.lk', $defaultPassword]);
$pdo->prepare("INSERT INTO platform_users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'support_admin', 'active')")
    ->execute(['TuitionPro Support Desk', 'support@tuitionpro.lk', $defaultPassword]);
echo "Platform users seeded.\n";

// ─── 3. SYSTEM ROLES & PERMISSIONS ───────────────────────────────────────────
$roles = [
    ['super_admin', 'Platform Super Administrator', 'Universal platform control', 1],
    ['support_admin', 'Platform Support Administrator', 'Customer support access', 1],
    ['institute_owner', 'Institute Owner', 'Complete control over institute operations and finances', 1],
    ['institute_admin', 'Institute Administrator', 'Daily operational and academic management', 1],
    ['branch_manager', 'Branch Manager', 'Restricted management of assigned branch', 1],
    ['accountant', 'Accountant', 'Financial, billing, fee collection, and accounting records', 1],
    ['receptionist', 'Receptionist', 'Front desk student registration and fee collection', 1],
    ['teacher', 'Teacher', 'Academic class management, marks entry, and attendance', 1],
    ['student', 'Student', 'Personal student portal access', 1],
    ['parent', 'Parent / Guardian', 'Linked children portal access', 1],
];

$roleIds = [];
$rStmt = $pdo->prepare("INSERT INTO roles (tenant_id, name, display_name, description, is_system) VALUES (NULL, ?, ?, ?, ?)");
foreach ($roles as $r) {
    $rStmt->execute([$r[0], $r[1], $r[2], $r[3]]);
    $roleIds[$r[0]] = (int)$pdo->lastInsertId();
}

$permissions = [
    // Students
    ['students', 'students.view', 'View Students', 'View student directory and profiles'],
    ['students', 'students.create', 'Create Students', 'Register new students'],
    ['students', 'students.edit', 'Edit Students', 'Update student details'],
    ['students', 'students.delete', 'Delete Students', 'Archive or delete students'],
    // Fees & Payments
    ['fees', 'fees.view', 'View Fees', 'View fee structures, invoices, and payments'],
    ['fees', 'fees.create_invoice', 'Generate Invoices', 'Issue fee invoices to students'],
    ['fees', 'fees.collect_payment', 'Collect Payments', 'Record student payments and issue receipts'],
    ['fees', 'fees.refund', 'Refund Payments', 'Authorize payment refunds and reversals'],
    // Attendance
    ['attendance', 'attendance.view', 'View Attendance', 'View class attendance records'],
    ['attendance', 'attendance.record', 'Record Attendance', 'Take attendance for class sessions'],
    ['attendance', 'attendance.correct', 'Correct Attendance', 'Amend historical attendance with audit reason'],
    // Academics
    ['academic', 'academic.manage', 'Manage Academics', 'Manage subjects, courses, and batches'],
    ['academic', 'timetable.manage', 'Manage Timetable', 'Create schedules and generate class sessions'],
    // Teachers & Staff
    ['teachers', 'teachers.manage', 'Manage Teachers', 'Create and assign teachers'],
    ['teachers', 'teacher_payments.view', 'View Teacher Payments', 'View teacher payment calculations'],
    ['teachers', 'teacher_payments.manage', 'Process Teacher Payments', 'Approve and disburse teacher payments'],
    // Expenses & Accounting
    ['accounting', 'expenses.manage', 'Manage Expenses', 'Record and pay institute expenses'],
    ['accounting', 'accounts.view', 'View Financial Accounts', 'View cash, bank balances, and ledgers'],
    ['accounting', 'journals.post', 'Post Journals', 'Post double-entry journal entries'],
    // Reports
    ['reports', 'reports.academic', 'Academic Reports', 'View enrollment and attendance reports'],
    ['reports', 'reports.financial', 'Financial Reports', 'View collections, P&L, and balance reports'],
    // Settings
    ['settings', 'settings.manage', 'Manage Institute Settings', 'Update institute profile, branches, and users'],
];

$pStmt = $pdo->prepare("INSERT INTO permissions (module, name, display_name, description) VALUES (?, ?, ?, ?)");
foreach ($permissions as $p) {
    $pStmt->execute($p);
}
echo "Roles and permissions seeded.\n";

// ─── 4. HELPER FUNCTION TO SETUP TENANT ──────────────────────────────────────
function seedTenant(
    PDO $pdo,
    string $name,
    string $code,
    string $email,
    string $phone,
    string $address,
    string $planCode,
    array $roleIds,
    string $defaultPassword
): int {
    // 1. Create Tenant
    $planId = $pdo->query("SELECT id FROM subscription_plans WHERE code = '{$planCode}'")->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO tenants (uuid, name, code, email, phone, address, currency, currency_symbol, timezone, status, subscription_plan_id, trial_ends_at, subscription_ends_at) VALUES (UUID(), ?, ?, ?, ?, ?, 'LKR', 'Rs.', 'Asia/Colombo', 'active', ?, DATE_ADD(NOW(), INTERVAL 14 DAY), DATE_ADD(NOW(), INTERVAL 365 DAY))");
    $stmt->execute([$name, $code, $email, $phone, $address, $planId]);
    $tenantId = (int)$pdo->lastInsertId();

    // 2. Default Branches
    $bStmt = $pdo->prepare("INSERT INTO branches (tenant_id, name, code, address, phone, email, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $bStmt->execute([$tenantId, "{$name} - Main Branch", 'MAIN', $address, $phone, $email]);
    $branchMainId = (int)$pdo->lastInsertId();

    $branchSecondId = null;
    if ($planCode === 'professional') {
        $bStmt->execute([$tenantId, "{$name} - Kandy Branch", 'KDY', '45 Dalada Veediya, Kandy', '0812233445', 'kandy@' . parse_url($email, PHP_URL_HOST)]);
        $branchSecondId = (int)$pdo->lastInsertId();
    }

    // 3. Academic Year
    $ayStmt = $pdo->prepare("INSERT INTO academic_years (tenant_id, name, start_date, end_date, is_active) VALUES (?, '2026 Academic Year', '2026-01-01', '2026-12-31', 1)");
    $ayStmt->execute([$tenantId]);
    $acadYearId = (int)$pdo->lastInsertId();

    // 4. Chart of Accounts
    $coa = [
        ['1010', 'Cash in Hand', 'asset', 'debit', 1],
        ['1020', 'Commercial Bank Current Account', 'asset', 'debit', 1],
        ['1030', 'Sampath Bank Savings Account', 'asset', 'debit', 1],
        ['1100', 'Accounts Receivable (Student Fees)', 'asset', 'debit', 1],
        ['2010', 'Accounts Payable (Expenses)', 'liability', 'credit', 1],
        ['2020', 'Teacher Compensation Payable', 'liability', 'credit', 1],
        ['3010', 'Owner Capital', 'equity', 'credit', 1],
        ['3020', 'Retained Earnings', 'equity', 'credit', 1],
        ['4010', 'Tuition Fee Revenue', 'revenue', 'credit', 1],
        ['4020', 'Admission & Registration Revenue', 'revenue', 'credit', 1],
        ['4030', 'Examination Fee Revenue', 'revenue', 'credit', 1],
        ['5010', 'Teacher Compensation Expense', 'expense', 'debit', 1],
        ['5020', 'Classroom Rent Expense', 'expense', 'debit', 1],
        ['5030', 'Electricity & Water Utilities', 'expense', 'debit', 1],
        ['5040', 'Printing, Stationery & Notes', 'expense', 'debit', 1],
        ['5050', 'Staff Salaries', 'expense', 'debit', 1],
        ['5060', 'Marketing & Advertising', 'expense', 'debit', 1],
    ];
    $coaStmt = $pdo->prepare("INSERT INTO chart_of_accounts (tenant_id, account_code, account_name, account_type, normal_balance, is_system, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($coa as $c) {
        $coaStmt->execute([$tenantId, $c[0], $c[1], $c[2], $c[3], $c[4], 1]);
    }

    // 5. Financial Accounts
    $faStmt = $pdo->prepare("INSERT INTO financial_accounts (tenant_id, branch_id, account_name, account_type, account_number, bank_name, current_balance, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
    $faStmt->execute([$tenantId, $branchMainId, 'Cash in Hand (Reception)', 'cash', 'CASH-01', 'Tuition Cash Drawer', 45000.00]);
    $faStmt->execute([$tenantId, $branchMainId, 'Commercial Bank Operating A/C', 'bank', '8001234567', 'Commercial Bank PLC', 350000.00]);

    // 6. Expense Categories
    $expCats = [
        ['RENT', 'Premises & Classroom Rent', 'Monthly lease of tuition center buildings'],
        ['UTIL', 'Electricity & Water', 'CEB and NWSDB monthly utility bills'],
        ['PRINT', 'Printing & Stationery', 'Class tutes, exam papers, paper reams'],
        ['SALARY', 'Non-Academic Staff Salaries', 'Office assistants and cleaning staff'],
        ['MAINT', 'Repairs & Maintenance', 'AC repairs, multimedia projector servicing'],
        ['MKT', 'Social Media & Banners', 'Facebook boosting and banner printing'],
    ];
    $ecStmt = $pdo->prepare("INSERT INTO expense_categories (tenant_id, code, name, description, is_active) VALUES (?, ?, ?, ?, 1)");
    foreach ($expCats as $ec) {
        $ecStmt->execute([$tenantId, $ec[0], $ec[1], $ec[2]]);
    }

    // 7. Fee Types
    $ftStmt = $pdo->prepare("INSERT INTO fee_types (tenant_id, code, name, is_recurring, default_amount, description) VALUES (?, ?, ?, ?, ?, ?)");
    $ftStmt->execute([$tenantId, 'TUIT_MONTHLY', 'Monthly Tuition Fee', 1, 3500.00, 'Standard monthly class fee']);
    $ftStmt->execute([$tenantId, 'ADMIS_ONE_OFF', 'Admission & Registration Fee', 0, 1500.00, 'One-time registration fee']);
    $ftStmt->execute([$tenantId, 'EXAM_FEE', 'Term Examination Fee', 0, 500.00, 'Model paper & marking fee']);

    // 8. Grading Rules
    $grStmt = $pdo->prepare("INSERT INTO grading_rules (tenant_id, grade_name, min_percentage, max_percentage, grade_point, description) VALUES (?, ?, ?, ?, ?, ?)");
    $grStmt->execute([$tenantId, 'A', 75.00, 100.00, 4.00, 'Distinction Pass']);
    $grStmt->execute([$tenantId, 'B', 65.00, 74.99, 3.00, 'Very Good Pass']);
    $grStmt->execute([$tenantId, 'C', 55.00, 64.99, 2.00, 'Credit Pass']);
    $grStmt->execute([$tenantId, 'S', 35.00, 54.99, 1.00, 'Simple Pass']);
    $grStmt->execute([$tenantId, 'F', 0.00, 34.99, 0.00, 'Fail']);

    // 9. Institute Users
    $uStmt = $pdo->prepare("INSERT INTO users (tenant_id, branch_id, role_id, name, username, email, phone, password_hash, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");

    // Owner
    $uStmt->execute([$tenantId, $branchMainId, $roleIds['institute_owner'], "{$name} Director", "{$code}.owner", "{$code}.owner@tuitionpro.lk", $phone, $defaultPassword]);
    $ownerUserId = (int)$pdo->lastInsertId();

    // Admin
    $uStmt->execute([$tenantId, $branchMainId, $roleIds['institute_admin'], "{$name} Manager", "{$code}.admin", "{$code}.admin@tuitionpro.lk", '0771122334', $defaultPassword]);

    // Accountant
    $uStmt->execute([$tenantId, $branchMainId, $roleIds['accountant'], "{$name} Accountant", "{$code}.accountant", "{$code}.accountant@tuitionpro.lk", '0772233445', $defaultPassword]);
    $accountantUserId = (int)$pdo->lastInsertId();

    // Receptionist
    $uStmt->execute([$tenantId, $branchMainId, $roleIds['receptionist'], "{$name} Receptionist", "{$code}.reception", "{$code}.reception@tuitionpro.lk", '0773344556', $defaultPassword]);
    $receptionistUserId = (int)$pdo->lastInsertId();

    // Teachers (User accounts + Teacher records)
    $teachersData = [
        ['Prof. Sunimal Fernando', '0714455667', "{$code}.fernando@tuitionpro.lk", 'B.Sc (Hons), Ph.D (Maths)', 'Combined Mathematics', 'revenue_share', 40.00],
        ['Dr. Chandana Jayasuriya', '0715566778', "{$code}.jayasuriya@tuitionpro.lk", 'M.Sc (Physics), Ph.D', 'Physics (A/L)', 'revenue_share', 45.00],
    ];

    $teacherIds = [];
    $tRecordStmt = $pdo->prepare("INSERT INTO teachers (tenant_id, user_id, branch_id, employee_id, first_name, last_name, full_name, phone, email, qualifications, specialization, join_date, status, compensation_model, default_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '2024-01-01', 'active', ?, ?)");

    foreach ($teachersData as $idx => $t) {
        $uStmt->execute([$tenantId, $branchMainId, $roleIds['teacher'], $t[0], "{$code}.teacher" . ($idx + 1), $t[2], $t[1], $defaultPassword]);
        $tUserId = (int)$pdo->lastInsertId();

        $parts = explode(' ', $t[0]);
        $empId = strtoupper($code) . "-T" . str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT);
        $tRecordStmt->execute([$tenantId, $tUserId, $branchMainId, $empId, $parts[0] . ' ' . $parts[1], $parts[2] ?? '', $t[0], $t[1], $t[2], $t[3], $t[4], $t[5], $t[6]]);
        $teacherIds[] = (int)$pdo->lastInsertId();
    }

    // 10. Subjects & Courses
    $sStmt = $pdo->prepare("INSERT INTO subjects (tenant_id, name, code, grade_level, description, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    $sStmt->execute([$tenantId, 'Combined Mathematics', 'CMATH_AL', 'Grade 12-13 (A/L)', 'Pure & Applied Mathematics']);
    $subMathId = (int)$pdo->lastInsertId();

    $sStmt->execute([$tenantId, 'Physics', 'PHY_AL', 'Grade 12-13 (A/L)', 'Theoretical and Practical Physics']);
    $subPhyId = (int)$pdo->lastInsertId();

    $cStmt = $pdo->prepare("INSERT INTO courses (tenant_id, subject_id, academic_year_id, name, code, default_fee, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $cStmt->execute([$tenantId, $subMathId, $acadYearId, '2026 A/L Combined Maths Theory', 'CM26_TH', 3500.00]);
    $courseMathId = (int)$pdo->lastInsertId();

    $cStmt->execute([$tenantId, $subPhyId, $acadYearId, '2026 A/L Physics Theory & Revision', 'PH26_TR', 3500.00]);
    $coursePhyId = (int)$pdo->lastInsertId();

    // 11. Batches
    $bchStmt = $pdo->prepare("INSERT INTO batches (tenant_id, branch_id, course_id, subject_id, teacher_id, academic_year_id, name, code, classroom, max_capacity, monthly_fee, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '2026-01-01', '2026-12-31', 'active')");
    $bchStmt->execute([$tenantId, $branchMainId, $courseMathId, $subMathId, $teacherIds[0], $acadYearId, '2026 Combined Maths (Saturday Theory)', strtoupper($code) . '-MAT-26', 'Auditorium A', 150, 3500.00]);
    $batchMathId = (int)$pdo->lastInsertId();

    $bchStmt->execute([$tenantId, $branchMainId, $coursePhyId, $subPhyId, $teacherIds[1], $acadYearId, '2026 Physics (Sunday Theory)', strtoupper($code) . '-PHY-26', 'Hall B', 120, 3500.00]);
    $batchPhyId = (int)$pdo->lastInsertId();

    // 12. Class Schedules (Maths: Saturday 08:00 - 12:30; Physics: Sunday 08:00 - 12:30)
    $csStmt = $pdo->prepare("INSERT INTO class_schedules (tenant_id, branch_id, batch_id, teacher_id, day_of_week, start_time, end_time, classroom, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $csStmt->execute([$tenantId, $branchMainId, $batchMathId, $teacherIds[0], 6, '08:00:00', '12:30:00', 'Auditorium A']);
    $schedMathId = (int)$pdo->lastInsertId();

    $csStmt->execute([$tenantId, $branchMainId, $batchPhyId, $teacherIds[1], 0, '08:00:00', '12:30:00', 'Hall B']);
    $schedPhyId = (int)$pdo->lastInsertId();

    // 13. Class Sessions for November 2026
    $sessionDates = ['2026-11-01', '2026-11-07', '2026-11-08', '2026-11-14'];
    $sessStmt = $pdo->prepare("INSERT INTO class_sessions (tenant_id, branch_id, batch_id, teacher_id, schedule_id, session_date, start_time, end_time, topic, classroom, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')");
    
    // Nov 7 (Sat) Maths
    $sessStmt->execute([$tenantId, $branchMainId, $batchMathId, $teacherIds[0], $schedMathId, '2026-11-07', '08:00:00', '12:30:00', 'Integration by Parts & Definite Integrals', 'Auditorium A']);
    $sessionMath1 = (int)$pdo->lastInsertId();

    // Nov 1 (Sun) Physics
    $sessStmt->execute([$tenantId, $branchMainId, $batchPhyId, $teacherIds[1], $schedPhyId, '2026-11-01', '08:00:00', '12:30:00', 'Magnetic Fields & Electromagnetic Induction', 'Hall B']);
    $sessionPhy1 = (int)$pdo->lastInsertId();

    // 14. Students & Guardians
    $studentsData = [
        ['Kasun', 'Perera', 'Kasun Chamara Perera', '2008-05-14', 'male', '0771234567', 'kasun@gmail.com', '12 Temple Rd, Nugegoda', 'Ananda College', 'Grade 12', 'Nihal Perera', '0712345678', 'Father', 'Civil Engineer'],
        ['Nethmi', 'Silva', 'Nethmi Dilhara Silva', '2008-08-22', 'female', '0772345678', 'nethmi@gmail.com', '45 Galle Rd, Dehiwala', 'Visakha Vidyalaya', 'Grade 12', 'Sarath Silva', '0713456789', 'Father', 'Bank Manager'],
        ['Ravindu', 'Jayawardena', 'Ravindu Janith Jayawardena', '2008-03-10', 'male', '0773456789', 'ravindu@gmail.com', '78 Kandy Rd, Kiribathgoda', 'Nalanda College', 'Grade 12', 'Chitra Jayawardena', '0714567890', 'Mother', 'Teacher'],
        ['Tharushi', 'Fernando', 'Tharushi Hansika Fernando', '2008-11-05', 'female', '0774567890', 'tharushi@gmail.com', '101 High Level Rd, Maharagama', 'Musaeus College', 'Grade 12', 'Rohan Fernando', '0715678901', 'Father', 'Business Owner'],
        ['Sahan', 'Wickramasinghe', 'Sahan Thisara Wickramasinghe', '2008-01-30', 'male', '0775678901', 'sahan@gmail.com', '56 Station Rd, Moratuwa', 'Royal College', 'Grade 12', 'Kumari Wickramasinghe', '0716789012', 'Mother', 'Accountant'],
    ];

    $stuStmt = $pdo->prepare("INSERT INTO students (tenant_id, branch_id, user_id, admission_number, first_name, last_name, full_name, preferred_name, dob, gender, phone, email, address, school, grade_level, status, admission_date, emergency_contact_name, emergency_contact_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', '2026-01-10', ?, ?)");
    $gStmt = $pdo->prepare("INSERT INTO guardians (tenant_id, user_id, first_name, last_name, full_name, relationship, phone, email, occupation, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $sgStmt = $pdo->prepare("INSERT INTO student_guardians (tenant_id, student_id, guardian_id, relationship, is_primary, is_emergency) VALUES (?, ?, ?, ?, 1, 1)");
    $enrStmt = $pdo->prepare("INSERT INTO enrollments (tenant_id, student_id, batch_id, academic_year_id, enrollment_date, status) VALUES (?, ?, ?, ?, '2026-01-15', 'active')");

    $studentIds = [];
    foreach ($studentsData as $idx => $s) {
        $admNo = strtoupper($code) . "-2026-" . str_pad((string)($idx + 1), 4, '0', STR_PAD_LEFT);
        
        // Student user account
        $uStmt->execute([$tenantId, $branchMainId, $roleIds['student'], $s[2], "{$code}.student" . ($idx + 1), "{$code}.student" . ($idx + 1) . "@tuitionpro.lk", $s[5], $defaultPassword]);
        $sUserId = (int)$pdo->lastInsertId();

        // Guardian user account
        $uStmt->execute([$tenantId, $branchMainId, $roleIds['parent'], $s[10], "{$code}.parent" . ($idx + 1), "{$code}.parent" . ($idx + 1) . "@tuitionpro.lk", $s[11], $defaultPassword]);
        $gUserId = (int)$pdo->lastInsertId();

        // Guardian record
        $gParts = explode(' ', $s[10]);
        $gStmt->execute([$tenantId, $gUserId, $gParts[0], $gParts[1] ?? '', $s[10], $s[12], $s[11], "parent" . ($idx + 1) . "@example.com", $s[13], $s[7]]);
        $guardianId = (int)$pdo->lastInsertId();

        // Student record
        $stuStmt->execute([$tenantId, $branchMainId, $sUserId, $admNo, $s[0], $s[1], $s[2], $s[0], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $s[10], $s[11]]);
        $studentId = (int)$pdo->lastInsertId();
        $studentIds[] = $studentId;

        // Link Guardian to Student
        $sgStmt->execute([$tenantId, $studentId, $guardianId, $s[12]]);

        // Enroll in Maths
        $enrStmt->execute([$tenantId, $studentId, $batchMathId, $acadYearId]);
        // Enroll in Physics
        $enrStmt->execute([$tenantId, $studentId, $batchPhyId, $acadYearId]);
    }

    // 15. Attendance Records for Maths Session Nov 7
    $attStmt = $pdo->prepare("INSERT INTO attendance_records (tenant_id, class_session_id, batch_id, student_id, status, check_in_time, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $attStatuses = ['present', 'present', 'present', 'late', 'absent'];
    foreach ($studentIds as $idx => $sId) {
        $st = $attStatuses[$idx];
        $time = $st === 'present' ? '07:55:00' : ($st === 'late' ? '08:20:00' : null);
        $attStmt->execute([$tenantId, $sessionMath1, $batchMathId, $sId, $st, $time, $receptionistUserId]);
    }

    // 16. INVOICES, PAYMENTS & ALLOCATIONS for November 2026
    $invStmt = $pdo->prepare("INSERT INTO invoices (tenant_id, branch_id, student_id, invoice_number, fee_period, issue_date, due_date, subtotal, discount_amount, total_amount, paid_amount, balance_amount, status, created_by) VALUES (?, ?, ?, ?, '2026-11', '2026-11-01', '2026-11-10', ?, 0.00, ?, ?, ?, ?, ?)");
    $iiStmt = $pdo->prepare("INSERT INTO invoice_items (tenant_id, invoice_id, fee_type_id, batch_id, description, amount, discount, net_amount) VALUES (?, ?, 1, ?, ?, ?, 0.00, ?)");
    $payStmt = $pdo->prepare("INSERT INTO payments (tenant_id, branch_id, student_id, payment_number, payment_date, amount, payment_method, receipt_number, collected_by, status) VALUES (?, ?, ?, ?, '2026-11-05', ?, 'cash', ?, ?, 'confirmed')");
    $paStmt = $pdo->prepare("INSERT INTO payment_allocations (tenant_id, payment_id, invoice_id, amount) VALUES (?, ?, ?, ?)");

    // Student 1: Paid in Full (Maths Rs. 3500 + Physics Rs. 3500 = Rs. 7000)
    $invNo1 = strtoupper($code) . "-INV-202611-0001";
    $invStmt->execute([$tenantId, $branchMainId, $studentIds[0], $invNo1, 7000.00, 7000.00, 7000.00, 0.00, 'paid', $accountantUserId]);
    $invId1 = (int)$pdo->lastInsertId();
    $iiStmt->execute([$tenantId, $invId1, $batchMathId, 'Combined Maths - Nov 2026', 3500.00, 3500.00]);
    $iiStmt->execute([$tenantId, $invId1, $batchPhyId, 'Physics - Nov 2026', 3500.00, 3500.00]);

    $recNo1 = strtoupper($code) . "-REC-202611-0001";
    $payStmt->execute([$tenantId, $branchMainId, $studentIds[0], strtoupper($code) . "-PAY-0001", 7000.00, $recNo1, $receptionistUserId]);
    $payId1 = (int)$pdo->lastInsertId();
    $paStmt->execute([$tenantId, $payId1, $invId1, 7000.00]);

    // Student 2: Partially Paid (Billed Rs. 7000, Paid Rs. 4000, Balance Rs. 3000)
    $invNo2 = strtoupper($code) . "-INV-202611-0002";
    $invStmt->execute([$tenantId, $branchMainId, $studentIds[1], $invNo2, 7000.00, 7000.00, 4000.00, 3000.00, 'partially_paid', $accountantUserId]);
    $invId2 = (int)$pdo->lastInsertId();
    $iiStmt->execute([$tenantId, $invId2, $batchMathId, 'Combined Maths - Nov 2026', 3500.00, 3500.00]);
    $iiStmt->execute([$tenantId, $invId2, $batchPhyId, 'Physics - Nov 2026', 3500.00, 3500.00]);

    $recNo2 = strtoupper($code) . "-REC-202611-0002";
    $payStmt->execute([$tenantId, $branchMainId, $studentIds[1], strtoupper($code) . "-PAY-0002", 4000.00, $recNo2, $receptionistUserId]);
    $payId2 = (int)$pdo->lastInsertId();
    $paStmt->execute([$tenantId, $payId2, $invId2, 4000.00]);

    // Student 3: Issued / Overdue (Billed Rs. 7000, Paid Rs. 0, Balance Rs. 7000)
    $invNo3 = strtoupper($code) . "-INV-202611-0003";
    $invStmt->execute([$tenantId, $branchMainId, $studentIds[2], $invNo3, 7000.00, 7000.00, 0.00, 7000.00, 'overdue', $accountantUserId]);
    $invId3 = (int)$pdo->lastInsertId();
    $iiStmt->execute([$tenantId, $invId3, $batchMathId, 'Combined Maths - Nov 2026', 3500.00, 3500.00]);
    $iiStmt->execute([$tenantId, $invId3, $batchPhyId, 'Physics - Nov 2026', 3500.00, 3500.00]);

    // 17. Teacher Payment Rules & Calculation
    $tprStmt = $pdo->prepare("INSERT INTO teacher_payment_rules (tenant_id, teacher_id, batch_id, model, percentage, effective_from) VALUES (?, ?, ?, 'revenue_share', ?, '2026-01-01')");
    $tprStmt->execute([$tenantId, $teacherIds[0], $batchMathId, 40.00]);
    $tprStmt->execute([$tenantId, $teacherIds[1], $batchPhyId, 45.00]);

    $tppStmt = $pdo->prepare("INSERT INTO teacher_payment_periods (tenant_id, name, start_date, end_date, is_closed) VALUES (?, 'November 2026', '2026-11-01', '2026-11-30', 0)");
    $tppStmt->execute([$tenantId]);
    $periodId = (int)$pdo->lastInsertId();

    // 18. Expenses
    $expStmt = $pdo->prepare("INSERT INTO expenses (tenant_id, branch_id, category_id, expense_number, expense_date, payee, amount, paid_amount, balance_amount, payment_status, description, created_by) VALUES (?, ?, 1, ?, '2026-11-02', 'Lanka Properties Ltd', 45000.00, 45000.00, 0.00, 'paid', 'Monthly classroom building rental for November', ?)");
    $expStmt->execute([$tenantId, $branchMainId, strtoupper($code) . "-EXP-001", $accountantUserId]);

    // 19. Balanced Double-Entry Journal Entry
    // Example: Recording Student 1 Payment of Rs. 7,000 (Cash Debit, Tuition Fee Revenue Credit)
    $jeStmt = $pdo->prepare("INSERT INTO journal_entries (tenant_id, branch_id, journal_number, entry_date, description, source_module, source_id, status, created_by) VALUES (?, ?, ?, '2026-11-05', 'Student Fee Collection - Receipt " . $recNo1 . "', 'payment', ?, 'posted', ?)");
    $jeStmt->execute([$tenantId, $branchMainId, strtoupper($code) . "-JRN-0001", $payId1, $accountantUserId]);
    $jeId = (int)$pdo->lastInsertId();

    $cashAccId = $pdo->query("SELECT id FROM chart_of_accounts WHERE tenant_id = {$tenantId} AND account_code = '1010'")->fetchColumn();
    $revAccId = $pdo->query("SELECT id FROM chart_of_accounts WHERE tenant_id = {$tenantId} AND account_code = '4010'")->fetchColumn();

    $jlStmt = $pdo->prepare("INSERT INTO journal_lines (tenant_id, journal_entry_id, account_id, line_description, debit, credit) VALUES (?, ?, ?, ?, ?, ?)");
    $jlStmt->execute([$tenantId, $jeId, $cashAccId, 'Cash collected from student', 7000.00, 0.00]);
    $jlStmt->execute([$tenantId, $jeId, $revAccId, 'Tuition fee income recognized', 0.00, 7000.00]);

    // 20. Examinations & Marks
    $examStmt = $pdo->prepare("INSERT INTO examinations (tenant_id, branch_id, batch_id, subject_id, academic_year_id, name, exam_date, max_marks, pass_mark, exam_type, status, created_by) VALUES (?, ?, ?, ?, ?, 'Monthly Assessment - Calculus', '2026-11-06', 100.00, 40.00, 'monthly', 'published', ?)");
    $examStmt->execute([$tenantId, $branchMainId, $batchMathId, $subMathId, $acadYearId, $teacherIds[0]]);
    $examId = (int)$pdo->lastInsertId();

    $emStmt = $pdo->prepare("INSERT INTO exam_marks (tenant_id, examination_id, student_id, marks_obtained, percentage, grade, rank_in_batch, status, remarks, entered_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $emStmt->execute([$tenantId, $examId, $studentIds[0], 88.00, 88.00, 'A', 1, 'pass', 'Excellent performance', $teacherIds[0]]);
    $emStmt->execute([$tenantId, $examId, $studentIds[1], 72.00, 72.00, 'B', 2, 'pass', 'Good effort, improve speed', $teacherIds[0]]);
    $emStmt->execute([$tenantId, $examId, $studentIds[2], 58.00, 58.00, 'C', 3, 'pass', 'Satisfactory pass', $teacherIds[0]]);

    // 21. Announcements
    $annStmt = $pdo->prepare("INSERT INTO announcements (tenant_id, branch_id, title, content, target_audience, is_published, created_by) VALUES (?, ?, ?, ?, 'all', 1, ?)");
    $annStmt->execute([$tenantId, $branchMainId, "Welcome to {$name} Academic Year 2026", "Classes have commenced for the 2026 A/L batch. Please ensure your tuition fees are settled by the 10th of each month.", $ownerUserId]);

    echo "Tenant '{$name}' (code: {$code}) seeded successfully with full test datasets.\n";
    return $tenantId;
}

// ─── 5. EXECUTE SEEDING FOR TWO INDEPENDENT TENANTS ──────────────────────────

// Tenant 1: Apex Higher Education Institute (Colombo)
$apexTenantId = seedTenant(
    $pdo,
    'Apex Higher Education Institute',
    'apex',
    'info@apexeducation.lk',
    '0112345678',
    '154 Galle Road, Colombo 04',
    'professional',
    $roleIds,
    $defaultPassword
);

// Tenant 2: Excel Tuition Academy (Gampaha)
$excelTenantId = seedTenant(
    $pdo,
    'Excel Tuition Academy',
    'excel',
    'contact@excelacademy.lk',
    '0332244668',
    '88 Colombo Road, Gampaha',
    'starter',
    $roleIds,
    $defaultPassword
);

echo "---------------------------------------------------------\n";
echo "SEEDING COMPLETE!\n";
echo "Platform Super Admin: superadmin@tuitionpro.lk / Password@123\n";
echo "Apex Institute Owner: apex.owner@tuitionpro.lk / Password@123\n";
echo "Excel Institute Owner: excel.owner@tuitionpro.lk / Password@123\n";
echo "---------------------------------------------------------\n";

