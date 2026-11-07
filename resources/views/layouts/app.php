<?php
$user = auth_user() ?? [];
$role = $user['role_name'] ?? 'guest';
$tenantName = $user['tenant_name'] ?? 'TuitionPro ERP';
$branchName = $user['branch_name'] ?? 'Main Branch';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Dashboard') ?> — <?= e($tenantName) ?> | TuitionPro ERP</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom SaaS Theme -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>

<div id="app-wrapper">
    <!-- Mobile Backdrop -->
    <div id="sidebar-backdrop" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index: 999;"></div>

    <!-- ─── SIDEBAR NAVIGATION ───────────────────────────────────────────── -->
    <aside id="sidebar">
        <div class="sidebar-brand">
            <a href="<?= in_array($role, ['super_admin', 'support_admin'], true) ? url('platform/dashboard') : url('dashboard') ?>" class="d-flex align-items-center gap-2 text-decoration-none">
                <img src="<?= institute_logo_white() ?>" alt="<?= e(institute_name()) ?>" height="36" class="img-fluid">
            </a>
        </div>

        <nav class="sidebar-nav">
            <!-- ─── Developer / Platform Super Admin ────────────────────── -->
            <?php if (in_array($role, ['super_admin', 'support_admin'], true)): ?>
                <div class="nav-section-title">System Administration</div>
                <a href="<?= url('platform/dashboard') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'platform_dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> System Overview
                </a>
                <a href="<?= url('platform/tenants') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'platform_tenants' ? 'active' : '' ?>">
                    <i class="bi bi-person-gear"></i> Institute Admin Setup
                </a>
                <a href="<?= url('settings/backups') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'backups' ? 'active' : '' ?>">
                    <i class="bi bi-database-down"></i> System Backups
                </a>
            <?php endif; ?>

            <!-- ─── Institute Admin / Owner / Branch Manager ─────────────── -->
            <?php if (in_array($role, ['institute_owner', 'institute_admin', 'branch_manager'], true)): ?>
                <div class="nav-section-title">Main</div>
                <a href="<?= url('dashboard') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i> Executive Dashboard
                </a>

                <div class="nav-section-title">Academic & Students</div>
                <a href="<?= url('students/enquiries') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'enquiries' ? 'active' : '' ?>">
                    <i class="bi bi-chat-left-dots-fill"></i> Inquiries & Admissions
                </a>
                <a href="<?= url('students') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'students' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> Students Directory
                </a>
                <a href="<?= url('academic/batches') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'batches' ? 'active' : '' ?>">
                    <i class="bi bi-collection-fill"></i> Classes & Batches
                </a>
                <a href="<?= url('academic/subjects') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'subjects' ? 'active' : '' ?>">
                    <i class="bi bi-book-half"></i> Subjects
                </a>
                <a href="<?= url('academic/materials') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'materials' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-pdf"></i> Study Materials
                </a>
                <a href="<?= url('academic/assignments') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'assignments' ? 'active' : '' ?>">
                    <i class="bi bi-pencil-square"></i> Assignments
                </a>
                <a href="<?= url('teachers') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'teachers' ? 'active' : '' ?>">
                    <i class="bi bi-person-video3"></i> Teachers
                </a>

                <div class="nav-section-title">Scheduling & Exams</div>
                <a href="<?= url('timetable') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'timetable' ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i> Timetable
                </a>
                <a href="<?= url('attendance') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'attendance' ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i> Attendance
                </a>
                <a href="<?= url('examinations') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'examinations' ? 'active' : '' ?>">
                    <i class="bi bi-journal-check"></i> Examinations
                </a>

                <div class="nav-section-title">Finance & Billing</div>
                <a href="<?= url('fees/invoices') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'invoices' ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i> Student Invoices
                </a>
                <a href="<?= url('fees/outstanding') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'outstanding' ? 'active' : '' ?>">
                    <i class="bi bi-exclamation-circle"></i> Outstanding Fees
                </a>
                <?php if (in_array($role, ['institute_owner', 'accountant'], true)): ?>
                    <a href="<?= url('teacher-payments') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'teacher_payments' ? 'active' : '' ?>">
                        <i class="bi bi-wallet2"></i> Teacher Payroll
                    </a>
                    <a href="<?= url('expenses') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'expenses' ? 'active' : '' ?>">
                        <i class="bi bi-cash-stack"></i> Expenses
                    </a>
                    <a href="<?= url('accounting/cash-bank') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'cash_bank' ? 'active' : '' ?>">
                        <i class="bi bi-bank"></i> Cash & Bank
                    </a>
                    <a href="<?= url('expenses/trial-balance') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'trial_balance' ? 'active' : '' ?>">
                        <i class="bi bi-calculator"></i> Accounting Ledger
                    </a>
                <?php endif; ?>

                <div class="nav-section-title">Analytics & Config</div>
                <a href="<?= url('reports') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'reports' ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-line-fill"></i> Analytics & Reports
                </a>
                <a href="<?= url('communication/announcements') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'announcements' ? 'active' : '' ?>">
                    <i class="bi bi-megaphone-fill"></i> Announcements
                </a>
                <a href="<?= url('settings/branches') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'branches' ? 'active' : '' ?>">
                    <i class="bi bi-diagram-3"></i> Branches
                </a>
                <a href="<?= url('settings/users') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'users' ? 'active' : '' ?>">
                    <i class="bi bi-person-gear"></i> Staff Users
                </a>
                <a href="<?= url('settings') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>">
                    <i class="bi bi-sliders"></i> Institute Settings
                </a>
                <a href="<?= url('settings/backups') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'backups' ? 'active' : '' ?>">
                    <i class="bi bi-database-down"></i> Backups
                </a>
            <?php endif; ?>

            <!-- ─── Accountant Navigation ─────────────────────────────────── -->
            <?php if ($role === 'accountant'): ?>
                <div class="nav-section-title">Main</div>
                <a href="<?= url('dashboard') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>

                <div class="nav-section-title">Fee Management</div>
                <a href="<?= url('fees/invoices') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'invoices' ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i> Student Invoices
                </a>
                <a href="<?= url('fees/outstanding') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'outstanding' ? 'active' : '' ?>">
                    <i class="bi bi-exclamation-circle"></i> Outstanding Fees
                </a>

                <div class="nav-section-title">Financial Accounting</div>
                <a href="<?= url('teacher-payments') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'teacher_payments' ? 'active' : '' ?>">
                    <i class="bi bi-wallet2"></i> Teacher Payroll
                </a>
                <a href="<?= url('expenses') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'expenses' ? 'active' : '' ?>">
                    <i class="bi bi-cash-stack"></i> Expenses
                </a>
                <a href="<?= url('accounting/cash-bank') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'cash_bank' ? 'active' : '' ?>">
                    <i class="bi bi-bank"></i> Cash & Bank
                </a>
                <a href="<?= url('expenses/trial-balance') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'trial_balance' ? 'active' : '' ?>">
                    <i class="bi bi-calculator"></i> Accounting Ledger
                </a>

                <div class="nav-section-title">Reports</div>
                <a href="<?= url('reports') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'reports' ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-line-fill"></i> Financial Reports
                </a>
                <a href="<?= url('communication/announcements') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'announcements' ? 'active' : '' ?>">
                    <i class="bi bi-megaphone-fill"></i> Announcements
                </a>
            <?php endif; ?>

            <!-- ─── Receptionist Navigation ───────────────────────────────── -->
            <?php if ($role === 'receptionist'): ?>
                <div class="nav-section-title">Main</div>
                <a href="<?= url('dashboard') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i> Front-Desk Dashboard
                </a>

                <div class="nav-section-title">Front Desk Operations</div>
                <a href="<?= url('students/enquiries') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'enquiries' ? 'active' : '' ?>">
                    <i class="bi bi-chat-left-dots-fill"></i> Inquiries & Leads
                </a>
                <a href="<?= url('students') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'students' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> Student Directory
                </a>
                <a href="<?= url('academic/batches') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'batches' ? 'active' : '' ?>">
                    <i class="bi bi-collection-fill"></i> Enrolments & Batches
                </a>
                <a href="<?= url('timetable') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'timetable' ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i> Class Timetable
                </a>
                <a href="<?= url('attendance') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'attendance' ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i> Record Attendance
                </a>
                <a href="<?= url('attendance/qr-scanner') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'qr_scanner' ? 'active' : '' ?>">
                    <i class="bi bi-qr-code-scan"></i> QR Fast Scanner
                </a>

                <div class="nav-section-title">Fee Collections</div>
                <a href="<?= url('fees/invoices') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'invoices' ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i> Invoices & Receipts
                </a>
                <a href="<?= url('fees/outstanding') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'outstanding' ? 'active' : '' ?>">
                    <i class="bi bi-exclamation-circle"></i> Fee Follow-ups
                </a>

                <div class="nav-section-title">Communication</div>
                <a href="<?= url('communication/announcements') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'announcements' ? 'active' : '' ?>">
                    <i class="bi bi-megaphone-fill"></i> Announcements
                </a>
            <?php endif; ?>

            <!-- ─── Teacher Navigation ────────────────────────────────────── -->
            <?php if ($role === 'teacher'): ?>
                <div class="nav-section-title">Main</div>
                <a href="<?= url('dashboard') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i> Teacher Dashboard
                </a>

                <div class="nav-section-title">My Classroom</div>
                <a href="<?= url('academic/batches') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'batches' ? 'active' : '' ?>">
                    <i class="bi bi-collection-fill"></i> My Assigned Batches
                </a>
                <a href="<?= url('timetable') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'timetable' ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i> Class Schedule
                </a>
                <a href="<?= url('attendance') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'attendance' ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i> Take Attendance
                </a>

                <div class="nav-section-title">Coursework & Exams</div>
                <a href="<?= url('academic/materials') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'materials' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-pdf"></i> Study Materials
                </a>
                <a href="<?= url('academic/assignments') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'assignments' ? 'active' : '' ?>">
                    <i class="bi bi-pencil-square"></i> Assignments
                </a>
                <a href="<?= url('examinations') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'examinations' ? 'active' : '' ?>">
                    <i class="bi bi-journal-check"></i> Examinations & Marks
                </a>

                <div class="nav-section-title">Communication</div>
                <a href="<?= url('communication/announcements') ?>" class="sidebar-link <?= ($activeMenu ?? '') === 'announcements' ? 'active' : '' ?>">
                    <i class="bi bi-megaphone-fill"></i> Institute Notices
                </a>
            <?php endif; ?>

            <!-- ─── Student Portal Navigation ─────────────────────────────── -->
            <?php if ($role === 'student'): ?>
                <div class="nav-section-title">Student Portal</div>
                <a href="<?= url('portal/student/dashboard') ?>" class="sidebar-link active">
                    <i class="bi bi-person-badge"></i> My Academic Portal
                </a>
                <a href="<?= url('communication/announcements') ?>" class="sidebar-link">
                    <i class="bi bi-megaphone"></i> Announcements
                </a>
            <?php endif; ?>

            <!-- ─── Parent Portal Navigation ──────────────────────────────── -->
            <?php if ($role === 'parent'): ?>
                <div class="nav-section-title">Parent Portal</div>
                <a href="<?= url('portal/parent/dashboard') ?>" class="sidebar-link active">
                    <i class="bi bi-people"></i> My Children
                </a>
                <a href="<?= url('communication/announcements') ?>" class="sidebar-link">
                    <i class="bi bi-megaphone"></i> Announcements
                </a>
            <?php endif; ?>
        </nav>
    </aside>

    <!-- ─── CONTENT WRAPPER ─────────────────────────────────────────────── -->
    <div id="content-wrapper">
        <!-- Topbar -->
        <header id="topbar">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebar-toggle" class="btn btn-light d-lg-none" type="button">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h6 class="mb-0 fw-bold"><?= e($tenantName) ?></h6>
                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= e($branchName) ?></small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 rounded-pill px-3 py-1 border" type="button" data-bs-toggle="dropdown">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="text-start d-none d-md-block">
                            <div class="fw-semibold text-truncate" style="max-width: 140px; font-size: 0.85rem;"><?= e($user['name'] ?? 'User') ?></div>
                            <div class="text-muted" style="font-size: 0.7rem;"><?= e($user['role_display'] ?? 'Staff') ?></div>
                        </div>
                        <i class="bi bi-chevron-down text-muted" style="font-size: 0.75rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                        <li><a class="dropdown-item" href="<?= url('dashboard') ?>"><i class="bi bi-person me-2"></i>My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="<?= url('logout') ?>" method="POST" class="no-disable m-0">
                                <?= csrf_field() ?>
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main id="main-content">
            <!-- Flash Message Alerts -->
            <?php if ($msg = flash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div><?= e($msg) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($msg = flash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div><?= e($msg) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($msg = flash('warning')): ?>
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                    <div><?= e($msg) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- View Content Injection -->
            <?= $content ?>
        </main>

        <!-- Footer -->
        <footer class="py-3 px-4 text-center text-muted border-top bg-white" style="font-size: 0.8rem;">
            TuitionPro ERP &copy; <?= date('Y') ?> — Professional Tuition Institute Management System
        </footer>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Client App Scripts -->
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>

