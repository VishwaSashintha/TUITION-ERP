<?php $pageTitle = 'Outstanding Receivables'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
            <li class="breadcrumb-item active">Outstanding</li>
        </ol></nav>
        <h4 class="fw-bold mb-0"><i class="bi bi-exclamation-circle me-2 text-warning"></i>Outstanding Receivables</h4>
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
                <label class="form-label small fw-semibold">Status Filter</label>
                <select name="status" class="form-select">
                    <option value="all"     <?= $status === 'all'     ? 'selected' : '' ?>>All Outstanding</option>
                    <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>Overdue Only</option>
                    <option value="issued"  <?= $status === 'issued'  ? 'selected' : '' ?>>Issued (Unpaid)</option>
                    <option value="partially_paid" <?= $status === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                </select>
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
                <button type="submit" class="btn btn-warning w-100">
                    <i class="bi bi-funnel me-1"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-warning">Rs. <?= number_format($totalOutstanding, 2) ?></div>
                <div class="text-muted small">Total Outstanding</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-3 fw-bold text-danger"><?= number_format($overdueCount) ?></div>
                <div class="text-muted small">Overdue Invoices</div>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <span class="fw-semibold">Outstanding Invoices</span>
        <span class="badge bg-warning text-dark"><?= count($rows) ?> Records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Invoice No.</th>
                    <th>Period</th>
                    <th>Due Date</th>
                    <th class="text-end">Total</th>
                    <th class="text-end text-success">Paid</th>
                    <th class="text-end text-danger">Balance</th>
                    <th class="text-center">Days Overdue</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                        No outstanding invoices found.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <?php
                            $days = (int)$r['days_overdue'];
                            $urgency = $days > 30 ? 'danger' : ($days > 0 ? 'warning' : 'info');
                        ?>
                        <tr class="<?= $days > 30 ? 'table-danger' : ($days > 0 ? 'table-warning' : '') ?>">
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($r['full_name']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($r['phone'] ?? '') ?></div>
                            </td>
                            <td><code><?= htmlspecialchars($r['invoice_number']) ?></code></td>
                            <td><?= htmlspecialchars($r['fee_period']) ?></td>
                            <td><?= date('d M Y', strtotime($r['due_date'])) ?></td>
                            <td class="text-end">Rs. <?= number_format($r['total_amount'], 2) ?></td>
                            <td class="text-end text-success">Rs. <?= number_format($r['paid_amount'], 2) ?></td>
                            <td class="text-end fw-bold text-danger">Rs. <?= number_format($r['balance_amount'], 2) ?></td>
                            <td class="text-center">
                                <?php if ($days > 0): ?>
                                    <span class="badge bg-<?= $urgency ?>"><?= $days ?> days</span>
                                <?php else: ?>
                                    <span class="badge bg-info">Not due</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-<?= $r['status'] === 'overdue' ? 'danger' : ($r['status'] === 'partially_paid' ? 'warning text-dark' : 'secondary') ?>">
                                <?= ucfirst(str_replace('_', ' ', $r['status'])) ?>
                            </span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($rows)): ?>
            <tfoot class="table-warning fw-bold">
                <tr>
                    <td colspan="6" class="text-end">Grand Total Outstanding:</td>
                    <td class="text-end text-danger">Rs. <?= number_format($totalOutstanding, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require_once VIEW_PATH . '/layouts/app.php'; ?>

