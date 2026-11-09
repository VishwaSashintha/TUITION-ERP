<?php $pageTitle = 'Teacher Payments Report'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
            <li class="breadcrumb-item active">Teacher Payments</li>
        </ol></nav>
        <h4 class="fw-bold mb-0"><i class="bi bi-person-badge me-2 text-info"></i>Teacher Payments Report</h4>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i> Print
    </button>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Month</label>
                <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-info text-white w-100">
                    <i class="bi bi-funnel me-1"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-info">Rs. <?= number_format($grandTotal, 2) ?></div>
                <div class="text-muted small">Total Disbursed — <?= date('F Y', strtotime($startDate)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-primary"><?= count($rows) ?></div>
                <div class="text-muted small">Active Teachers</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-success"><?= count(array_filter($rows, fn($r) => $r['payment_count'] > 0)) ?></div>
                <div class="text-muted small">Paid This Month</div>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3"><span class="fw-semibold">Teacher Payment Summary — <?= date('F Y', strtotime($startDate)) ?></span></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Employee ID</th>
                    <th>Teacher Name</th>
                    <th>Compensation Model</th>
                    <th class="text-center">Payments</th>
                    <th class="text-end">Amount Paid</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No teacher payment records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($r['employee_id'] ?? '—') ?></code></td>
                            <td class="fw-semibold"><?= htmlspecialchars($r['teacher_name']) ?></td>
                            <td>
                                <span class="badge bg-primary">
                                    <?= ucfirst(str_replace('_', ' ', $r['compensation_model'])) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= (int)$r['payment_count'] ?></td>
                            <td class="text-end fw-bold <?= $r['total_paid'] > 0 ? 'text-success' : 'text-muted' ?>">
                                Rs. <?= number_format($r['total_paid'], 2) ?>
                            </td>
                            <td class="text-center">
                                <?php if ($r['payment_count'] > 0): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-info fw-bold">
                        <td colspan="4" class="text-end">Grand Total:</td>
                        <td class="text-end">Rs. <?= number_format($grandTotal, 2) ?></td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require_once VIEW_PATH . '/layouts/app.php'; ?>

