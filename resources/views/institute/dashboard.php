<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Executive Dashboard</h4>
        <p class="text-muted mb-0">Overview of academic operations, fee collections, and schedules</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('students/create') ?>" class="btn btn-primary-custom">
            <i class="bi bi-person-plus-fill"></i> New Admission
        </a>
        <a href="<?= url('fees/invoices/create') ?>" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-plus"></i> Issue Invoice
        </a>
        <a href="<?= url('attendance') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-calendar-check"></i> Take Attendance
        </a>
    </div>
</div>

<!-- ─── STAT KPI CARDS ──────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($studentCount) ?></div>
                <div class="stat-label">Active Students</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon accent">
                <i class="bi bi-collection-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($batchCount) ?></div>
                <div class="stat-label">Active Batches</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="stat-value"><?= format_currency($financials['total_collected'] ?? 0) ?></div>
                <div class="stat-label">Fees Collected</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="bi bi-exclamation-diamond-fill"></i>
            </div>
            <div>
                <div class="stat-value"><?= format_currency($financials['total_outstanding'] ?? 0) ?></div>
                <div class="stat-label">Outstanding Fees</div>
            </div>
        </div>
    </div>
</div>

<!-- ─── FINANCIAL PERFORMANCE SUMMARY ────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card-custom">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary"></i> Financial Performance (LKR)</h6>
                <span class="badge bg-light text-dark border">Real-Time Data</span>
            </div>
            <div class="p-3">
                <canvas id="financialChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-custom h-100">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart-fill me-2 text-primary"></i> Income & Surplus</h6>
            </div>
            <div class="p-3 d-flex flex-column justify-content-around">
                <div class="p-3 rounded bg-light mb-3">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Total Fees Billed</small>
                    <h5 class="fw-bold text-dark mb-0"><?= format_currency($financials['total_billed'] ?? 0) ?></h5>
                </div>
                <div class="p-3 rounded bg-light mb-3">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Operating Expenses Paid</small>
                    <h5 class="fw-bold text-danger mb-0"><?= format_currency($financials['total_expenses'] ?? 0) ?></h5>
                </div>
                <div class="p-3 rounded" style="background-color: var(--primary-light);">
                    <small class="text-primary text-uppercase fw-bold" style="font-size: 0.75rem;">Net Cash Surplus</small>
                    <h4 class="fw-extrabold text-primary mb-0"><?= format_currency($financials['net_profit'] ?? 0) ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── RECENT ADMISSIONS & TODAY'S SESSIONS ──────────────────────────────── -->
<div class="row g-3">
    <!-- Today's Sessions -->
    <div class="col-lg-6">
        <div class="card-custom">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-calendar-event me-2 text-primary"></i> Today's Class Sessions</h6>
                <a href="<?= url('timetable') ?>" class="btn btn-sm btn-link p-0">Full Timetable &rarr;</a>
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
                                    <i class="bi bi-calendar-check fs-3 d-block mb-1"></i>
                                    No classes scheduled for today.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($todaySessions as $s): ?>
                                <tr>
                                    <td class="fw-semibold" style="font-size: 0.825rem;">
                                        <?= substr($s['start_time'], 0, 5) ?> - <?= substr($s['end_time'], 0, 5) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= e($s['batch_name']) ?></div>
                                        <small class="text-muted"><?= e($s['subject_name']) ?></small>
                                    </td>
                                    <td><?= e($s['teacher_name']) ?></td>
                                    <td>
                                        <span class="badge badge-custom badge-<?= $s['status'] === 'completed' ? 'success' : 'warning' ?>">
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
        <div class="card-custom">
            <div class="card-header-custom">
                <h6 class="fw-bold mb-0"><i class="bi bi-cash me-2 text-success"></i> Recent Collections</h6>
                <a href="<?= url('fees/invoices') ?>" class="btn btn-sm btn-link p-0">All Receipts &rarr;</a>
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
                                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                    No payments recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentPayments as $p): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace"><?= e($p['receipt_number']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= e($p['student_name']) ?></div>
                                        <small class="text-muted"><?= e($p['admission_number']) ?></small>
                                    </td>
                                    <td class="fw-bold text-success">
                                        <?= format_currency($p['amount']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('financialChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Total Billed', 'Collections (Cash In)', 'Operating Costs (Cash Out)', 'Net Surplus'],
            datasets: [{
                label: 'LKR (Rs.)',
                data: [
                    <?= (float)($financials['total_billed'] ?? 0) ?>,
                    <?= (float)($financials['total_collected'] ?? 0) ?>,
                    <?= (float)($financials['total_expenses'] ?? 0) ?>,
                    <?= (float)($financials['net_profit'] ?? 0) ?>
                ],
                backgroundColor: [
                    '#6C4EE3',
                    '#10B981',
                    '#EF4444',
                    '#FF7A59'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (val) {
                            return 'Rs. ' + val.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>

