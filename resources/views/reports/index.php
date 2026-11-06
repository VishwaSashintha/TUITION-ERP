<?php $pageTitle = 'Reports & Analytics'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Reports & Analytics</h4>
        <p class="text-muted mb-0">Comprehensive management reports across all modules</p>
    </div>
</div>

<!-- KPI Summary Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3" style="background:#EDE9FB;">
                        <i class="bi bi-people-fill fs-4" style="color:#6C4EE3;"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold"><?= number_format($kpis['total_students']) ?></div>
                        <div class="text-muted small">Active Students</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3" style="background:#E6F9F0;">
                        <i class="bi bi-cash-stack fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">Rs. <?= number_format($kpis['total_collected'], 2) ?></div>
                        <div class="text-muted small">Total Collected</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3" style="background:#FFF3E0;">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">Rs. <?= number_format($kpis['outstanding'], 2) ?></div>
                        <div class="text-muted small">Outstanding</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3" style="background:#FDECEA;">
                        <i class="bi bi-receipt fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">Rs. <?= number_format($kpis['total_expenses'], 2) ?></div>
                        <div class="text-muted small">Total Expenses</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Cards Grid -->
<div class="row g-3">
    <!-- Attendance Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 p-3" style="background:#EDE9FB;">
                        <i class="bi bi-calendar-check-fill fs-3" style="color:#6C4EE3;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Attendance Report</h6>
                        <p class="text-muted small mb-0">Student attendance summary by batch, month, and branch</p>
                    </div>
                </div>
                <a href="<?= url('reports/attendance') ?>" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Fee Collection Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 p-3" style="background:#E6F9F0;">
                        <i class="bi bi-wallet2 fs-3 text-success"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Fee Collection</h6>
                        <p class="text-muted small mb-0">Daily & monthly fee collection breakdown by payment method</p>
                    </div>
                </div>
                <a href="<?= url('reports/fee-collection') ?>" class="btn btn-sm btn-success w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Outstanding Receivables -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 p-3" style="background:#FFF3E0;">
                        <i class="bi bi-exclamation-circle-fill fs-3 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Outstanding Receivables</h6>
                        <p class="text-muted small mb-0">Overdue and pending invoices with aging analysis</p>
                    </div>
                </div>
                <a href="<?= url('reports/outstanding') ?>" class="btn btn-sm btn-warning w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Teacher Payments Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 p-3" style="background:#E3F2FD;">
                        <i class="bi bi-person-badge-fill fs-3 text-info"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Teacher Payments</h6>
                        <p class="text-muted small mb-0">Monthly payroll summary by teacher and compensation model</p>
                    </div>
                </div>
                <a href="<?= url('reports/teacher-payments') ?>" class="btn btn-sm btn-info w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Expense Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 p-3" style="background:#FDECEA;">
                        <i class="bi bi-graph-down-arrow fs-3 text-danger"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Expense Summary</h6>
                        <p class="text-muted small mb-0">Operational expenses by category and branch</p>
                    </div>
                </div>
                <a href="<?= url('reports/expenses') ?>" class="btn btn-sm btn-danger w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Student Performance Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 p-3" style="background:#F3E5F5;">
                        <i class="bi bi-award-fill fs-3" style="color:#9C27B0;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Student Performance</h6>
                        <p class="text-muted small mb-0">Exam results, grades, and merit rankings by batch</p>
                    </div>
                </div>
                <a href="<?= url('reports/student-performance') ?>" class="btn btn-sm w-100" style="background:#9C27B0;color:white;">
                    <i class="bi bi-arrow-right-circle me-1"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- P&L / Trial Balance (links to accounting) -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 hover-lift">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 p-3" style="background:#E8F5E9;">
                        <i class="bi bi-journal-text fs-3 text-success"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Financial Statements</h6>
                        <p class="text-muted small mb-0">Trial Balance and Profit & Loss statement</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('accounting/trial-balance') ?>" class="btn btn-sm btn-outline-success flex-fill">Trial Balance</a>
                    <a href="<?= url('accounting/profit-loss') ?>" class="btn btn-sm btn-outline-success flex-fill">P&amp;L</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift { transition: transform 0.15s ease, box-shadow 0.15s ease; }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(108,78,227,0.12) !important; }
</style>

<?php $content = ob_get_clean(); ?>
<?php require_once VIEW_PATH . '/layouts/app.php'; ?>

