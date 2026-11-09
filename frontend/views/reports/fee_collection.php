<?php $pageTitle = 'Fee Collection Report'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
            <li class="breadcrumb-item active">Fee Collection</li>
        </ol></nav>
        <h4 class="fw-bold mb-0"><i class="bi bi-wallet2 me-2 text-success"></i>Fee Collection Report</h4>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i> Print
    </button>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Month</label>
                <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="0">All Branches</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $branchId == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-funnel me-1"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-success">Rs. <?= number_format($grandTotal, 2) ?></div>
                <div class="text-muted small">Total Collected</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-primary">Rs. <?= number_format($cashTotal, 2) ?></div>
                <div class="text-muted small">Cash Payments</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-info">Rs. <?= number_format($bankTotal, 2) ?></div>
                <div class="text-muted small">Bank Transfers</div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Collection Table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <span class="fw-semibold">Daily Collection — <?= date('F Y', strtotime($startDate)) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th class="text-center">Transactions</th>
                    <th class="text-end text-primary">Cash</th>
                    <th class="text-end text-info">Bank Transfer</th>
                    <th class="text-end text-secondary">Cheque</th>
                    <th class="text-end fw-bold">Daily Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dailyRows)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No collection data for the selected period.</td></tr>
                <?php else: ?>
                    <?php foreach ($dailyRows as $r): ?>
                        <tr>
                            <td class="fw-semibold"><?= date('D, d M Y', strtotime($r['pay_date'])) ?></td>
                            <td class="text-center"><span class="badge bg-primary"><?= (int)$r['payment_count'] ?></span></td>
                            <td class="text-end">Rs. <?= number_format($r['cash_total'], 2) ?></td>
                            <td class="text-end">Rs. <?= number_format($r['bank_total'], 2) ?></td>
                            <td class="text-end">Rs. <?= number_format($r['cheque_total'], 2) ?></td>
                            <td class="text-end fw-bold text-success">Rs. <?= number_format($r['daily_total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-success fw-bold">
                        <td>TOTAL</td>
                        <td class="text-center"><?= array_sum(array_column($dailyRows, 'payment_count')) ?></td>
                        <td class="text-end">Rs. <?= number_format($cashTotal, 2) ?></td>
                        <td class="text-end">Rs. <?= number_format($bankTotal, 2) ?></td>
                        <td class="text-end">Rs. <?= number_format(array_sum(array_column($dailyRows, 'cheque_total')), 2) ?></td>
                        <td class="text-end">Rs. <?= number_format($grandTotal, 2) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Student Collection Detail -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <span class="fw-semibold">Student-wise Collection</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Admission No.</th>
                    <th>Student Name</th>
                    <th>Method</th>
                    <th class="text-center">Payments</th>
                    <th class="text-end">Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($studentRows)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No student payment data found.</td></tr>
                <?php else: ?>
                    <?php foreach ($studentRows as $r): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($r['admission_number']) ?></code></td>
                            <td class="fw-semibold"><?= htmlspecialchars($r['full_name']) ?></td>
                            <td>
                                <span class="badge <?= $r['payment_method'] === 'cash' ? 'bg-success' : ($r['payment_method'] === 'bank_transfer' ? 'bg-info' : 'bg-secondary') ?>">
                                    <?= ucfirst(str_replace('_', ' ', $r['payment_method'])) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= (int)$r['payment_count'] ?></td>
                            <td class="text-end fw-semibold text-success">Rs. <?= number_format($r['paid_total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require_once VIEW_PATH . '/layouts/app.php'; ?>

