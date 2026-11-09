<?php $pageTitle = 'Reports & Analytics'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-tp-black"><i class="bi bi-bar-chart-line me-2 text-tp-green"></i>Reports & Analytics</h4>
        <p class="text-muted mb-0">Comprehensive management reports across all modules</p>
    </div>
</div>

<!-- KPI Summary Row (Standard 4 per row | Tabular Figures) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon tp-purple">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($kpis['total_students']) ?></div>
                <div class="stat-label">Active Students</div>
                <small class="text-muted" style="font-size: 0.75rem;">Total student body</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon tp-green">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green">Rs. <?= number_format($kpis['total_collected'], 2) ?></div>
                <div class="stat-label">Total Collected</div>
                <small class="text-muted" style="font-size: 0.75rem;">Net collections to date</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon tp-orange">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <div class="stat-value text-tp-orange">Rs. <?= number_format($kpis['outstanding'], 2) ?></div>
                <div class="stat-label">Outstanding</div>
                <small class="text-muted" style="font-size: 0.75rem;">Uncollected receivables</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon tp-coral">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <div class="stat-value text-tp-coral">Rs. <?= number_format($kpis['total_expenses'], 2) ?></div>
                <div class="stat-label">Total Expenses</div>
                <small class="text-muted" style="font-size: 0.75rem;">Disbursed overheads</small>
            </div>
        </div>
    </div>
</div>

<!-- Report Cards Grid -->
<div class="row g-3">
    <!-- Attendance Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon tp-purple">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-tp-black">Attendance Report</h6>
                        <p class="text-muted small mb-0">Student attendance summary by batch, month, and branch</p>
                    </div>
                </div>
                <a href="<?= url('reports/attendance') ?>" class="btn btn-sm btn-secondary w-100">
                    <i class="bi bi-arrow-right-circle me-1 text-tp-green"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Fee Collection Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon tp-green">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-tp-black">Fee Collection</h6>
                        <p class="text-muted small mb-0">Daily & monthly fee collection breakdown by payment method</p>
                    </div>
                </div>
                <a href="<?= url('reports/fee-collection') ?>" class="btn btn-sm btn-secondary w-100">
                    <i class="bi bi-arrow-right-circle me-1 text-tp-green"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Outstanding Receivables -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon tp-orange">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-tp-black">Outstanding Receivables</h6>
                        <p class="text-muted small mb-0">Overdue and pending invoices with aging analysis</p>
                    </div>
                </div>
                <a href="<?= url('reports/outstanding') ?>" class="btn btn-sm btn-secondary w-100">
                    <i class="bi bi-arrow-right-circle me-1 text-tp-orange"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Teacher Payments Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon tp-sky-blue">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-tp-black">Teacher Payments</h6>
                        <p class="text-muted small mb-0">Monthly payroll summary by teacher and compensation model</p>
                    </div>
                </div>
                <a href="<?= url('reports/teacher-payments') ?>" class="btn btn-sm btn-secondary w-100">
                    <i class="bi bi-arrow-right-circle me-1 text-tp-sky-blue"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Expense Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon tp-coral">
                        <i class="bi bi-graph-down-arrow"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-tp-black">Expense Summary</h6>
                        <p class="text-muted small mb-0">Operational expenses by category and branch</p>
                    </div>
                </div>
                <a href="<?= url('reports/expenses') ?>" class="btn btn-sm btn-secondary w-100">
                    <i class="bi bi-arrow-right-circle me-1 text-tp-coral"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Student Performance Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon tp-purple">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-tp-black">Student Performance</h6>
                        <p class="text-muted small mb-0">Exam results, grades, and merit rankings by batch</p>
                    </div>
                </div>
                <a href="<?= url('reports/student-performance') ?>" class="btn btn-sm btn-secondary w-100">
                    <i class="bi bi-arrow-right-circle me-1 text-tp-purple"></i> View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Financial Statements -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon tp-green">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-tp-black">Financial Statements</h6>
                        <p class="text-muted small mb-0">Trial Balance and Profit & Loss statement</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('accounting/trial-balance') ?>" class="btn btn-sm btn-secondary flex-fill">Trial Balance</a>
                    <a href="<?= url('accounting/profit-loss') ?>" class="btn btn-sm btn-secondary flex-fill">P&amp;L</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require_once VIEW_PATH . '/layouts/app.php'; ?>
