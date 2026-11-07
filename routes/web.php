<?php

declare(strict_types=1);

/** @var \App\Core\Router $router */
$router = $router ?? ($this->router ?? \App\Core\Application::getInstance()->getRouter());

// ─── Root & Redirect ─────────────────────────────────────────────────────────
$router->get('/', function ($request, $response) {
    if (auth_check()) {
        $role = auth_role();
        if ($role === 'super_admin') {
            redirect(url('platform/dashboard'));
        } elseif ($role === 'student') {
            redirect(url('portal/student'));
        } elseif ($role === 'parent') {
            redirect(url('portal/parent'));
        } else {
            redirect(url('dashboard'));
        }
    }
    redirect(url('login'));
});

// ─── Guest Authentication Routes ─────────────────────────────────────────────
$router->get('/login', [\App\Controllers\Auth\AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [\App\Controllers\Auth\AuthController::class, 'login'], ['guest', 'csrf']);


// ─── Authenticated System Routes ─────────────────────────────────────────────
$router->group(['middleware' => ['auth']], function ($router) {

    // Auth actions
    $router->post('/logout', [\App\Controllers\Auth\AuthController::class, 'logout'], ['csrf']);

    // Institute Dashboard
    $router->get('/dashboard', [\App\Controllers\Institute\DashboardController::class, 'index']);

    // ─── Student Management (Admin, Receptionist, Admissions Officer) ───────────────────────────
    $router->group(['middleware' => ['role:institute_owner,institute_admin,branch_manager,receptionist,admissions_officer']], function ($router) {
        // Enquiries & Lead Tracking
        $router->get('/students/enquiries', [\App\Controllers\Students\EnquiryController::class, 'index']);
        $router->get('/students/enquiries/create', [\App\Controllers\Students\EnquiryController::class, 'create']);
        $router->post('/students/enquiries/store', [\App\Controllers\Students\EnquiryController::class, 'store'], ['csrf']);
        $router->get('/students/enquiries/{id}', [\App\Controllers\Students\EnquiryController::class, 'show']);
        $router->post('/students/enquiries/followup', [\App\Controllers\Students\EnquiryController::class, 'storeFollowup'], ['csrf']);
        $router->post('/students/enquiries/convert', [\App\Controllers\Students\EnquiryController::class, 'convert'], ['csrf']);

        // Student Directory & Registration
        $router->get('/students', [\App\Controllers\Students\StudentController::class, 'index']);
        $router->get('/students/create', [\App\Controllers\Students\StudentController::class, 'create']);
        $router->post('/students', [\App\Controllers\Students\StudentController::class, 'store'], ['csrf']);
        $router->post('/students/store', [\App\Controllers\Students\StudentController::class, 'store'], ['csrf']);
        $router->get('/students/export/csv', [\App\Controllers\Students\StudentController::class, 'exportCsv']);
        $router->get('/students/show', [\App\Controllers\Students\StudentController::class, 'show']);
        $router->get('/students/{id}', [\App\Controllers\Students\StudentController::class, 'show']);
        $router->get('/students/{id}/id-card', [\App\Controllers\Students\StudentController::class, 'idCard']);
        $router->get('/students/id-card', [\App\Controllers\Students\StudentController::class, 'idCard']);
    });

    // ─── Teacher Management (Admin, HR Manager) ──────────────────────────────────────
    $router->group(['middleware' => ['role:institute_owner,institute_admin,hr_manager']], function ($router) {
        $router->get('/teachers', [\App\Controllers\Teachers\TeacherController::class, 'index']);
        $router->get('/teachers/create', [\App\Controllers\Teachers\TeacherController::class, 'create']);
        $router->post('/teachers', [\App\Controllers\Teachers\TeacherController::class, 'store'], ['csrf']);
        $router->post('/teachers/store', [\App\Controllers\Teachers\TeacherController::class, 'store'], ['csrf']);
        $router->get('/teachers/show', [\App\Controllers\Teachers\TeacherController::class, 'show']);
        $router->get('/teachers/{id}', [\App\Controllers\Teachers\TeacherController::class, 'show']);
    });

    // ─── Academic Structure & Batches ─────────────────────────────────────────
    $router->group(['middleware' => ['role:institute_owner,institute_admin,branch_manager,receptionist,teacher,academic_coordinator']], function ($router) {
        $router->get('/academic/subjects', [\App\Controllers\Academic\AcademicController::class, 'subjects']);
        $router->post('/academic/subjects', [\App\Controllers\Academic\AcademicController::class, 'storeSubject'], ['csrf']);
        $router->get('/academic/batches', [\App\Controllers\Academic\AcademicController::class, 'batches']);
        $router->get('/academic/batches/create', [\App\Controllers\Academic\AcademicController::class, 'createBatch']);
        $router->post('/academic/batches', [\App\Controllers\Academic\AcademicController::class, 'storeBatch'], ['csrf']);
        $router->post('/academic/batches/store', [\App\Controllers\Academic\AcademicController::class, 'storeBatch'], ['csrf']);
        $router->get('/academic/batches/show', [\App\Controllers\Academic\AcademicController::class, 'showBatch']);
        $router->get('/academic/batches/{id}', [\App\Controllers\Academic\AcademicController::class, 'showBatch']);
        $router->post('/academic/batches/enroll', [\App\Controllers\Academic\AcademicController::class, 'enrollStudent'], ['csrf']);

        // Learning Materials & Resources
        $router->get('/academic/materials', [\App\Controllers\Academic\MaterialController::class, 'index']);
        $router->get('/academic/materials/create', [\App\Controllers\Academic\MaterialController::class, 'create']);
        $router->post('/academic/materials/store', [\App\Controllers\Academic\MaterialController::class, 'store'], ['csrf']);
        $router->get('/academic/materials/download', [\App\Controllers\Academic\MaterialController::class, 'download']);
        $router->get('/academic/materials/download/{id}', [\App\Controllers\Academic\MaterialController::class, 'download']);

        // Assignments & Coursework
        $router->get('/academic/assignments', [\App\Controllers\Academic\AssignmentController::class, 'index']);
        $router->get('/academic/assignments/create', [\App\Controllers\Academic\AssignmentController::class, 'create']);
        $router->post('/academic/assignments/store', [\App\Controllers\Academic\AssignmentController::class, 'store'], ['csrf']);
        $router->get('/academic/assignments/show', [\App\Controllers\Academic\AssignmentController::class, 'show']);
        $router->get('/academic/assignments/{id}', [\App\Controllers\Academic\AssignmentController::class, 'show']);
        $router->get('/academic/assignments/download', [\App\Controllers\Academic\AssignmentController::class, 'download']);
        $router->get('/academic/assignments/download/{id}', [\App\Controllers\Academic\AssignmentController::class, 'download']);
        $router->post('/academic/assignments/submit', [\App\Controllers\Academic\AssignmentController::class, 'submit'], ['csrf']);
        $router->post('/academic/assignments/grade', [\App\Controllers\Academic\AssignmentController::class, 'grade'], ['csrf']);

        // Timetable & Scheduling
        $router->get('/timetable', [\App\Controllers\Timetable\TimetableController::class, 'index']);
        $router->post('/timetable/schedule', [\App\Controllers\Timetable\TimetableController::class, 'storeSchedule'], ['csrf']);
        $router->get('/timetable/sessions', [\App\Controllers\Timetable\TimetableController::class, 'sessions']);
        $router->post('/timetable/generate-sessions', [\App\Controllers\Timetable\TimetableController::class, 'generateSessions'], ['csrf']);

        // Attendance Module
        $router->get('/attendance', [\App\Controllers\Attendance\AttendanceController::class, 'take']);
        $router->get('/attendance/take', [\App\Controllers\Attendance\AttendanceController::class, 'take']);
        $router->post('/attendance/save', [\App\Controllers\Attendance\AttendanceController::class, 'save'], ['csrf']);
        $router->get('/attendance/qr-scanner', [\App\Controllers\Attendance\AttendanceController::class, 'qrScanner']);
        $router->post('/attendance/scan-qr', [\App\Controllers\Attendance\AttendanceController::class, 'scanQr'], ['csrf']);
        $router->get('/attendance/history', [\App\Controllers\Attendance\AttendanceController::class, 'history']);
        $router->post('/attendance/correct', [\App\Controllers\Attendance\AttendanceController::class, 'correct'], ['csrf']);

        // Examinations & Performance
        $router->get('/examinations', [\App\Controllers\Examinations\ExamController::class, 'index']);
        $router->post('/examinations/store', [\App\Controllers\Examinations\ExamController::class, 'store'], ['csrf']);
        $router->get('/examinations/marks-entry', [\App\Controllers\Examinations\ExamController::class, 'marksEntry']);
        $router->get('/examinations/marks-entry/{id}', [\App\Controllers\Examinations\ExamController::class, 'marksEntry']);
        $router->post('/examinations/save-marks', [\App\Controllers\Examinations\ExamController::class, 'saveMarks'], ['csrf']);
        $router->get('/examinations/results', [\App\Controllers\Examinations\ExamController::class, 'results']);
        $router->get('/examinations/results/{id}', [\App\Controllers\Examinations\ExamController::class, 'results']);
        $router->post('/examinations/publish', [\App\Controllers\Examinations\ExamController::class, 'publish'], ['csrf']);
    });

    // ─── Fee Invoicing & Receipts (Admin, Accountant, Receptionist, Cashier) ───────────
    $router->group(['middleware' => ['role:institute_owner,institute_admin,accountant,receptionist,cashier']], function ($router) {
        $router->get('/fees', [\App\Controllers\Fees\FeeController::class, 'invoices']);
        $router->get('/fees/invoices', [\App\Controllers\Fees\FeeController::class, 'invoices']);
        $router->get('/fees/create', [\App\Controllers\Fees\FeeController::class, 'createInvoice']);
        $router->get('/fees/invoices/create', [\App\Controllers\Fees\FeeController::class, 'createInvoice']);
        $router->post('/fees/store', [\App\Controllers\Fees\FeeController::class, 'storeInvoice'], ['csrf']);
        $router->get('/fees/invoice', [\App\Controllers\Fees\FeeController::class, 'showInvoice']);
        $router->get('/fees/invoice/{id}', [\App\Controllers\Fees\FeeController::class, 'showInvoice']);
        $router->post('/fees/collect-payment', [\App\Controllers\Fees\FeeController::class, 'collectPayment'], ['csrf']);
        $router->get('/fees/receipt', [\App\Controllers\Fees\FeeController::class, 'receipt']);
        $router->get('/fees/receipt/{id}', [\App\Controllers\Fees\FeeController::class, 'receipt']);
        $router->get('/fees/outstanding', [\App\Controllers\Fees\FeeController::class, 'outstanding']);
        $router->post('/fees/refund', [\App\Controllers\Fees\FeeController::class, 'refund'], ['csrf']);
    });

    // ─── Teacher Payments & Payroll (Owner, Accountant, HR Manager) ─────────────────
    $router->group(['middleware' => ['role:institute_owner,accountant,hr_manager']], function ($router) {
        $router->get('/teacher-payments', [\App\Controllers\TeacherPayments\TeacherPaymentController::class, 'index']);
        $router->get('/teacher-payments/calculate', [\App\Controllers\TeacherPayments\TeacherPaymentController::class, 'calculate']);
        $router->post('/teacher-payments/save-calculation', [\App\Controllers\TeacherPayments\TeacherPaymentController::class, 'saveCalculation'], ['csrf']);
        $router->get('/teacher-payments/statement', [\App\Controllers\TeacherPayments\TeacherPaymentController::class, 'statement']);
        $router->post('/teacher-payments/disburse', [\App\Controllers\TeacherPayments\TeacherPaymentController::class, 'disburse'], ['csrf']);
    });

    // ─── Operational Expenses (Owner, Admin, Accountant) ─────────────────────
    $router->group(['middleware' => ['role:institute_owner,institute_admin,accountant']], function ($router) {
        $router->get('/expenses', [\App\Controllers\Expenses\ExpenseController::class, 'index']);
        $router->get('/expenses/create', [\App\Controllers\Expenses\ExpenseController::class, 'create']);
        $router->post('/expenses/store', [\App\Controllers\Expenses\ExpenseController::class, 'store'], ['csrf']);
    });

    // ─── Double-Entry General Ledger & Bank (Owner & Accountant ONLY) ─────────
    $router->group(['middleware' => ['role:institute_owner,accountant']], function ($router) {
        $router->get('/expenses/trial-balance', [\App\Controllers\Expenses\ExpenseController::class, 'trialBalance']);
        $router->get('/accounting/trial-balance', [\App\Controllers\Expenses\ExpenseController::class, 'trialBalance']);
        $router->get('/accounting/profit-loss', [\App\Controllers\Expenses\ExpenseController::class, 'profitLoss']);
        $router->get('/accounting/balance-sheet', [\App\Controllers\Expenses\ExpenseController::class, 'balanceSheet']);
        $router->get('/expenses/balance-sheet', [\App\Controllers\Expenses\ExpenseController::class, 'balanceSheet']);
        $router->get('/accounting/cash-bank', [\App\Controllers\Expenses\ExpenseController::class, 'cashBank']);
    });

    // ─── Communication & Notices ─────────────────────────────────────────────
    $router->get('/communication/announcements', [\App\Controllers\Communication\CommunicationController::class, 'announcements']);
    $router->post('/communication/announcements', [\App\Controllers\Communication\CommunicationController::class, 'storeAnnouncement'], ['csrf']);
    $router->get('/notifications', [\App\Controllers\Communication\CommunicationController::class, 'notifications']);
    $router->post('/notifications/mark-read', [\App\Controllers\Communication\CommunicationController::class, 'markNotificationRead'], ['csrf']);

    // ─── Student & Parent Portals ────────────────────────────────────────────
    $router->group(['middleware' => ['role:student']], function ($router) {
        $router->get('/portal/student', [\App\Controllers\Portals\StudentPortalController::class, 'dashboard']);
        $router->get('/portal/student/dashboard', [\App\Controllers\Portals\StudentPortalController::class, 'dashboard']);
    });

    $router->group(['middleware' => ['role:parent']], function ($router) {
        $router->get('/portal/parent', [\App\Controllers\Portals\ParentPortalController::class, 'dashboard']);
        $router->get('/portal/parent/dashboard', [\App\Controllers\Portals\ParentPortalController::class, 'dashboard']);
    });

    // ─── Institute Settings & Branches (Owner & Admin ONLY) ───────────────────
    $router->group(['middleware' => ['role:institute_owner,institute_admin']], function ($router) {
        $router->get('/settings', [\App\Controllers\Settings\SettingsController::class, 'index']);
        $router->post('/settings', [\App\Controllers\Settings\SettingsController::class, 'updateSettings'], ['csrf']);
        $router->get('/settings/branches', [\App\Controllers\Settings\SettingsController::class, 'branches']);
        $router->post('/settings/branches', [\App\Controllers\Settings\SettingsController::class, 'storeBranch'], ['csrf']);
        $router->get('/settings/users', [\App\Controllers\Settings\SettingsController::class, 'users']);
        $router->post('/settings/users', [\App\Controllers\Settings\SettingsController::class, 'storeUser'], ['csrf']);
        $router->get('/settings/backups', [\App\Controllers\Settings\SettingsController::class, 'backups']);
        $router->post('/settings/backups/create', [\App\Controllers\Settings\SettingsController::class, 'createBackup'], ['csrf']);
        $router->get('/settings/backups/download', [\App\Controllers\Settings\SettingsController::class, 'downloadBackup']);
        $router->post('/settings/backups/restore', [\App\Controllers\Settings\SettingsController::class, 'restoreBackup'], ['csrf']);
    });

    // ─── Developer / System Super Admin (Installation & Admin Management) ───
    $router->get('/platform/dashboard', [\App\Controllers\Platform\PlatformController::class, 'dashboard'], ['platform']);
    $router->get('/platform/tenants', [\App\Controllers\Platform\PlatformController::class, 'tenants'], ['platform']);
    $router->post('/platform/tenants', [\App\Controllers\Platform\PlatformController::class, 'storeTenant'], ['platform', 'csrf']);
    $router->post('/platform/institute/update', [\App\Controllers\Platform\PlatformController::class, 'updateInstitute'], ['platform', 'csrf']);
    $router->post('/platform/institute-admin/reset', [\App\Controllers\Platform\PlatformController::class, 'resetAdminPassword'], ['platform', 'csrf']);

    // ─── Reports & Analytics (Owner, Admin, Accountant) ──────────────────────
    $router->group(['middleware' => ['role:institute_owner,institute_admin,accountant']], function ($router) {
        $router->get('/reports', [\App\Controllers\Reports\ReportController::class, 'index']);
        $router->get('/reports/attendance', [\App\Controllers\Reports\ReportController::class, 'attendance']);
        $router->get('/reports/fee-collection', [\App\Controllers\Reports\ReportController::class, 'feeCollection']);
        $router->get('/reports/outstanding', [\App\Controllers\Reports\ReportController::class, 'outstanding']);
        $router->get('/reports/teacher-payments', [\App\Controllers\Reports\ReportController::class, 'teacherPayments']);
        $router->get('/reports/expenses', [\App\Controllers\Reports\ReportController::class, 'expenses']);
        $router->get('/reports/student-performance', [\App\Controllers\Reports\ReportController::class, 'studentPerformance']);
    });
});

