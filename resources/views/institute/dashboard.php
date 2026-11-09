<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0 text-tp-black">Executive Dashboard</h4>
            <span class="badge badge-purple px-2 py-1">Institute Management</span>
        </div>
        <p class="text-muted mb-0">Real-time overview of academic operations, fee collections, attendance, and financial health.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('students/create') ?>" class="btn btn-primary">
            <i class="bi bi-person-plus-fill"></i> New Admission
        </a>
        <a href="<?= url('fees/invoices/create') ?>" class="btn btn-secondary">
            <i class="bi bi-file-earmark-plus"></i> Issue Invoice
        </a>
        <a href="<?= url('attendance') ?>" class="btn btn-secondary">
            <i class="bi bi-calendar-check"></i> Take Attendance
        </a>
        <a href="<?= url('expenses/create') ?>" class="btn btn-danger">
            <i class="bi bi-cash-stack"></i> Record Expense
        </a>
    </div>
</div>

<!-- ─── 1. OVERVIEW KPI CARDS (Standard 4 per row | Roboto Tabular Numbers) ─── -->
<div class="row g-3 mb-4">
    <!-- Active Students (Purple) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-purple">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($studentCount) ?></div>
                <div class="stat-label">Active Students</div>
                <small class="text-muted" style="font-size: 0.75rem;">+<?= number_format($newStudentsThisMonth ?? 0) ?> enrolled this month</small>
            </div>
        </div>
    </div>

    <!-- Active Batches (Orange) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-orange">
                <i class="bi bi-collection-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($batchCount) ?></div>
                <div class="stat-label">Active Batches</div>
                <small class="text-muted" style="font-size: 0.75rem;"><?= number_format($teacherCount) ?> appointed teachers</small>
            </div>
        </div>
    </div>

    <!-- Fee Collections (Green) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-green">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green"><?= format_currency($financials['total_collected'] ?? 0) ?></div>
                <div class="stat-label">Total Collections</div>
                <small class="text-muted" style="font-size: 0.75rem;">Billed: <?= format_currency($financials['total_billed'] ?? 0) ?></small>
            </div>
        </div>
    </div>

    <!-- Outstanding Fees (Coral) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-coral">
                <i class="bi bi-exclamation-octagon-fill"></i>
            </div>
            <div>
                <div class="stat-value text-tp-coral"><?= format_currency($financials['total_outstanding'] ?? 0) ?></div>
                <div class="stat-label">Outstanding Fees</div>
                <small class="text-muted" style="font-size: 0.75rem;">Pending fee recovery</small>
            </div>
        </div>
    </div>

    <!-- Today's Attendance Rate (Sky Blue) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-sky-blue">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($attendanceRate, 1) ?>%</div>
                <div class="stat-label">Today's Attendance</div>
                <small class="text-muted" style="font-size: 0.75rem;"><?= (int)($attendanceStats['present_count'] ?? 0) ?> Present / <?= (int)($attendanceStats['total_marked'] ?? 0) ?> Marked</small>
            </div>
        </div>
    </div>

    <!-- Today's Classes (Orange) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-orange">
                <i class="bi bi-calendar3-event"></i>
            </div>
            <div>
                <div class="stat-value"><?= count($todaySessions) ?></div>
                <div class="stat-label">Classes Today</div>
                <small class="text-muted" style="font-size: 0.75rem;">Scheduled across rooms</small>
            </div>
        </div>
    </div>

    <!-- Operating Expenses (Coral) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-coral">
                <i class="bi bi-wallet-fill"></i>
            </div>
            <div>
                <div class="stat-value text-tp-coral"><?= format_currency($financials['total_expenses'] ?? 0) ?></div>
                <div class="stat-label">Operating Expenses</div>
                <small class="text-muted" style="font-size: 0.75rem;">Paid overhead & utilities</small>
            </div>
        </div>
    </div>

    <!-- Cash & Bank / Net Surplus (Charcoal) -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon tp-black">
                <i class="bi bi-bank2"></i>
            </div>
            <div>
                <div class="stat-value text-tp-green"><?= format_currency($financials['net_profit'] ?? 0) ?></div>
                <div class="stat-label">Net Cash Surplus</div>
                <small class="text-muted" style="font-size: 0.75rem;">Bank Balances: <?= format_currency($bankBalance ?? 0) ?></small>
            </div>
        </div>
    </div>
</div>

<!-- ─── 2. CHARTS & FINANCIAL ANALYTICS ───────────────────────────────────── -->
<div class="row g-3 mb-4">
    <!-- Income vs Expense Bar Chart -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-purple px-2 py-1"><i class="bi bi-bar-chart-fill"></i></span>
                    <h6 class="fw-bold mb-0">Financial Performance & Cash Flow (LKR)</h6>
                </div>
                <span class="badge bg-light text-dark border">Audited System Ledgers</span>
            </div>
            <div class="card-body">
                <canvas id="financialChart" style="max-height: 270px; width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Attendance Breakdown Doughnut Chart -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-sky-blue px-2 py-1"><i class="bi bi-pie-chart-fill"></i></span>
                    <h6 class="fw-bold mb-0">Today's Attendance Breakdown</h6>
                </div>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="width: 180px; height: 180px;">
                    <canvas id="attendanceDoughnut"></canvas>
                </div>
                <div class="d-flex justify-content-around w-100 mt-3 pt-2 border-top text-center" style="font-size: 0.8rem;">
                    <div>
                        <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: var(--tp-green);"></span>
                        <div class="fw-bold fw-number"><?= (int)($attendanceStats['present_count'] ?? 0) ?></div>
                        <small class="text-muted">Present</small>
                    </div>
                    <div>
                        <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: var(--tp-orange);"></span>
                        <div class="fw-bold fw-number"><?= (int)($attendanceStats['late_count'] ?? 0) ?></div>
                        <small class="text-muted">Late</small>
                    </div>
                    <div>
                        <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: var(--tp-coral);"></span>
                        <div class="fw-bold fw-number"><?= (int)($attendanceStats['absent_count'] ?? 0) ?></div>
                        <small class="text-muted">Absent</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── 3. OPERATIONAL SCHEDULE & COLLECTIONS ─────────────────────────────── -->
<div class="row g-3 mb-4">
    <!-- Today's Sessions -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-orange px-2 py-1"><i class="bi bi-calendar-event"></i></span>
                    <h6 class="fw-bold mb-0">Today's Class Sessions</h6>
                </div>
                <a href="<?= url('timetable') ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-tp-green">View All &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Batch & Subject</th>
                            <th>Teacher</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($todaySessions)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-calendar-check fs-3 d-block mb-1 text-muted"></i>
                                    No classes scheduled for today.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($todaySessions as $s): ?>
                                <tr>
                                    <td class="fw-number fw-semibold" style="font-size: 0.85rem;">
                                        <?= substr($s['start_time'], 0, 5) ?> - <?= substr($s['end_time'], 0, 5) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-tp-black"><?= e($s['batch_name']) ?></div>
                                        <small class="text-muted"><?= e($s['subject_name']) ?></small>
                                    </td>
                                    <td><?= e($s['teacher_name']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $s['status'] === 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($s['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-green px-2 py-1"><i class="bi bi-cash"></i></span>
                    <h6 class="fw-bold mb-0">Recent Fee Receipts</h6>
                </div>
                <a href="<?= url('fees/invoices') ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-tp-green">All Receipts &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Student</th>
                            <th>Amount</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentPayments)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-1 text-muted"></i>
                                    No payments recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentPayments as $p): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-dark"><?= e($p['receipt_number']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-tp-black"><?= e($p['student_name']) ?></div>
                                        <small class="text-muted"><?= e($p['admission_number']) ?></small>
                                    </td>
                                    <td class="fw-number fw-bold text-tp-green">
                                        <?= format_currency($p['amount']) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-dark text-uppercase" style="font-size: 0.7rem;">
                                            <?= e($p['payment_method']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ─── 4. RECENT ADMISSIONS & DEFAULTERS & ACTIVITY ──────────────────────── -->
<div class="row g-3">
    <!-- Recent Student Admissions -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-purple px-2 py-1"><i class="bi bi-person-check-fill"></i></span>
                    <h6 class="fw-bold mb-0">Recent Admissions</h6>
                </div>
                <a href="<?= url('students') ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-tp-green">Directory &rarr;</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentAdmissions)): ?>
                    <p class="text-muted text-center py-3 mb-0">No recent student admissions.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentAdmissions as $adm): ?>
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                                <div>
                                    <div class="fw-semibold text-tp-black"><?= e($adm['full_name']) ?></div>
                                    <small class="text-muted"><?= e($adm['admission_number']) ?> &bull; <?= e($adm['branch_name'] ?? 'Main') ?></small>
                                </div>
                                <span class="badge badge-purple" style="font-size: 0.75rem;">
                                    <?= format_date($adm['admission_date'] ?? $adm['created_at']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Outstanding Fee Alerts -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-coral px-2 py-1"><i class="bi bi-exclamation-triangle-fill"></i></span>
                    <h6 class="fw-bold mb-0">Outstanding Fee Alerts</h6>
                </div>
                <a href="<?= url('fees/outstanding') ?>" class="btn btn-sm btn-link text-decoration-none p-0 text-tp-coral">Follow-up &rarr;</a>
            </div>
            <div class="card-body">
                <?php if (empty($defaulters)): ?>
                    <p class="text-muted text-center py-3 mb-0">No outstanding fee balances.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($defaulters as $def): ?>
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                                <div>
                                    <div class="fw-semibold text-truncate text-tp-black" style="max-width: 170px;"><?= e($def['student_name']) ?></div>
                                    <small class="text-muted"><?= e($def['invoice_number']) ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-number fw-bold text-tp-coral"><?= format_currency($def['balance_amount']) ?></div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Due: <?= format_date($def['due_date']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Institute Activities / Audit Logs -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-tp-black px-2 py-1"><i class="bi bi-clock-history"></i></span>
                    <h6 class="fw-bold mb-0">Recent Activities</h6>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivities)): ?>
                    <p class="text-muted text-center py-3 mb-0">No recent system activity recorded.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentActivities as $act): ?>
                            <div class="list-group-item px-0 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-dark text-uppercase" style="font-size: 0.65rem;"><?= e($act['action']) ?></span>
                                    <small class="text-muted" style="font-size: 0.7rem;"><?= format_datetime($act['created_at']) ?></small>
                                </div>
                                <div class="text-truncate mt-1 text-tp-black" style="font-size: 0.8rem;"><?= e($act['details'] ?? 'System activity') ?></div>
                                <small class="text-muted" style="font-size: 0.7rem;">By <?= e($act['user_name']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const finCtx = document.getElementById('financialChart');
    if (finCtx) {
        new Chart(finCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Total Billed', 'Collections (Cash In)', 'Expenses (Cash Out)', 'Net Surplus'],
                datasets: [{
                    label: 'Amount (LKR)',
                    data: [
                        <?= (float)($financials['total_billed'] ?? 0) ?>,
                        <?= (float)($financials['total_collected'] ?? 0) ?>,
                        <?= (float)($financials['total_expenses'] ?? 0) ?>,
                        <?= (float)($financials['net_profit'] ?? 0) ?>
                    ],
                    backgroundColor: [
                        '#9985FB', // Purple
                        '#4BA95F', // Green
                        '#FF908D', // Coral
                        '#FEA339'  // Orange
                    ],
                    borderRadius: 8,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return 'Rs. ' + Number(ctx.raw).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { family: 'Roboto', size: 11 },
                            callback: function (val) {
                                return 'Rs. ' + Number(val).toLocaleString();
                            }
                        },
                        grid: { color: '#F1F5F9' }
                    },
                    x: {
                        ticks: { font: { family: 'Roboto', size: 12 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    const attCtx = document.getElementById('attendanceDoughnut');
    if (attCtx) {
        new Chart(attCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Late', 'Absent'],
                datasets: [{
                    data: [
                        <?= (int)($attendanceStats['present_count'] ?? 0) ?>,
                        <?= (int)($attendanceStats['late_count'] ?? 0) ?>,
                        <?= (int)($attendanceStats['absent_count'] ?? 0) ?>
                    ],
                    backgroundColor: [
                        '#4BA95F', // Green
                        '#FEA339', // Orange
                        '#FF908D'  // Coral
                    ],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
