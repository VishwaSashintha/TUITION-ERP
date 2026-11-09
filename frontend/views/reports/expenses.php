<?php $pageTitle = 'Expense Summary Report'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
            <li class="breadcrumb-item active">Expense Summary</li>
        </ol></nav>
        <h4 class="fw-bold mb-0"><i class="bi bi-graph-down-arrow me-2 text-danger"></i>Expense Summary Report</h4>
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
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-funnel me-1"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Category Breakdown & Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body d-flex flex-column justify-content-center py-4">
                <div class="fs-2 fw-bold text-danger">Rs. <?= number_format($grandTotal, 2) ?></div>
                <div class="text-muted small">Total Operating Expenses — <?= date('F Y', strtotime($startDate)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 fw-semibold">Expenses by Category</div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th class="text-center">Count</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($byCat)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">No categorized expenses recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($byCat as $cat): ?>
                                <?php $share = $grandTotal > 0 ? round(((float)$cat['total'] / $grandTotal) * 100, 1) : 0; ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($cat['category']) ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= (int)$cat['count'] ?></span></td>
                                    <td class="text-end fw-semibold">Rs. <?= number_format($cat['total'], 2) ?></td>
                                    <td class="text-end text-muted small"><?= $share ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <span class="fw-semibold">All Expense Items</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Expense #</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Payee</th>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No expense items found for the selected period.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($r['expense_number']) ?></code></td>
                            <td><?= date('d M Y', strtotime($r['expense_date'])) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($r['category']) ?></span></td>
                            <td class="fw-semibold"><?= htmlspecialchars($r['payee']) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($r['description'] ?? '—') ?></td>
                            <td class="text-end fw-bold text-danger">Rs. <?= number_format($r['amount'], 2) ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $r['payment_status'] === 'paid' ? 'success' : ($r['payment_status'] === 'partially_paid' ? 'warning text-dark' : 'danger') ?>">
                                    <?= ucfirst(str_replace('_', ' ', $r['payment_status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require_once VIEW_PATH . '/layouts/app.php'; ?>

